import paramiko
import getpass
import time

hostname = "203.194.115.228"
username = "root"
project_path = "/var/www/motorcycle_management"

print("=" * 60)
print("   DIAGNOSA & PERBAIKI: Suhu Mesin (engine_temp_c) VPS")
print("=" * 60)
password = getpass.getpass(prompt="Masukkan Password ROOT VPS: ")

def ssh_exec(client, command, show_output=True):
    print(f"\n[CMD] {command}")
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    out = stdout.read().decode().strip()
    err = stderr.read().decode().strip()
    if show_output and out:
        print(f"[OUT] {out}")
    if err and "WARNING" not in err and "warning" not in err:
        print(f"[ERR] {err}")
    return out

def section(title):
    print(f"\n{'='*60}")
    print(f"  {title}")
    print(f"{'='*60}")

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(
        hostname,
        username=username,
        password=password,
        disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']}
    )
    print("\n✅ Terhubung ke VPS!")

    # ─────────────────────────────────────────────
    # LANGKAH 1: Cek apakah kolom last_engine_temp_c sudah ada
    # ─────────────────────────────────────────────
    section("LANGKAH 1: Cek Kolom last_engine_temp_c di Database")

    check_col = ssh_exec(
        client,
        f"cd {project_path} && php artisan tinker --execute=\""
        f"echo count(\\\\DB::select(\\\"SHOW COLUMNS FROM vehicles LIKE 'last_engine_temp_c'\\\")) > 0 ? 'ADA' : 'TIDAK ADA';\""
    )

    if "ADA" in check_col and "TIDAK ADA" not in check_col:
        print("\n✅ Kolom last_engine_temp_c sudah ADA di database.")
        col_exists = True
    else:
        print("\n❌ Kolom last_engine_temp_c BELUM ADA — perlu jalankan migration!")
        col_exists = False

    # ─────────────────────────────────────────────
    # LANGKAH 2: Jalankan migration jika belum ada
    # ─────────────────────────────────────────────
    if not col_exists:
        section("LANGKAH 2: Menjalankan Migration engine_temp")
        migration_result = ssh_exec(
            client,
            f"cd {project_path} && php artisan migrate --force --path=database/migrations/2026_08_07_000001_add_engine_temp_to_vehicles_table.php"
        )
        if "Migrated" in migration_result or "migrated" in migration_result:
            print("\n✅ Migration berhasil dijalankan!")
        elif "Nothing to migrate" in migration_result:
            print("\n⚠️  Migration sudah pernah dijalankan (nothing to migrate).")
        else:
            print("\n⚠️  Hasil migration tidak dikenali, cek manual.")
    else:
        print("\n[SKIP] Migration tidak perlu — kolom sudah ada.")

    # ─────────────────────────────────────────────
    # LANGKAH 3: Cek nilai last_engine_temp_c kendaraan IoT
    # ─────────────────────────────────────────────
    section("LANGKAH 3: Cek Data Suhu di Vehicles (device_id tidak null)")

    temp_val = ssh_exec(
        client,
        f"cd {project_path} && php artisan tinker --execute=\""
        f"\\\\App\\\\Models\\\\Vehicle::whereNotNull('device_id')->get(['id','title','device_id','last_engine_temp_c','last_engine_temp_at'])->each(function(\\$v){{echo \\$v->id.'|'.\\$v->title.'|'.\\$v->device_id.'|'.\\$v->last_engine_temp_c.'|'.\\$v->last_engine_temp_at.PHP_EOL;}});\""
    )

    if not temp_val.strip():
        print("\n⚠️  Tidak ada vehicle dengan device_id — pastikan device_id sudah di-set di database.")
    else:
        for line in temp_val.split('\n'):
            parts = line.split('|')
            if len(parts) >= 4:
                vid, name, dev_id, temp, temp_at = parts[0], parts[1], parts[2], parts[3], parts[4] if len(parts) > 4 else '-'
                status = "✅ ADA" if temp and temp != '' else "❌ NULL"
                print(f"  [{status}] ID={vid} | {name} | device_id={dev_id} | suhu={temp or 'NULL'} | at={temp_at or '-'}")

    # ─────────────────────────────────────────────
    # LANGKAH 4: Cek status MQTT subscriber (supervisor/process)
    # ─────────────────────────────────────────────
    section("LANGKAH 4: Cek Status MQTT Subscriber")

    sup_status = ssh_exec(client, "supervisorctl status 2>/dev/null | grep mqtt || echo 'supervisor: tidak ditemukan mqtt'")
    proc_status = ssh_exec(client, "ps aux | grep 'mqtt:subscribe' | grep -v grep || echo 'process: tidak berjalan'")

    has_supervisor = "tidak ditemukan" not in sup_status and "tidak berjalan" not in sup_status
    has_process    = "tidak berjalan" not in proc_status and "grep" not in proc_status

    print(f"\nSupervisor: {sup_status}")
    print(f"Process:    {proc_status}")

    # ─────────────────────────────────────────────
    # LANGKAH 5: Restart MQTT subscriber
    # ─────────────────────────────────────────────
    section("LANGKAH 5: Restart MQTT Subscriber")

    if has_supervisor:
        print("[+] Merestart via supervisorctl...")
        restart_result = ssh_exec(client, "supervisorctl restart mqtt-subscriber 2>/dev/null || supervisorctl restart all 2>/dev/null")
        print(f"Hasil: {restart_result}")
    else:
        print("[+] Supervisor tidak ada / tidak aktif untuk MQTT.")
        print("[+] Mencoba kill proses lama dan restart manual...")
        ssh_exec(client, "pkill -f 'mqtt:subscribe' 2>/dev/null || true")
        time.sleep(2)

        # Restart sebagai background process dengan nohup
        ssh_exec(
            client,
            f"nohup php {project_path}/artisan mqtt:subscribe >> /var/log/mqtt_subscriber.log 2>&1 &"
        )
        time.sleep(3)

        # Verifikasi berjalan
        new_proc = ssh_exec(client, "ps aux | grep 'mqtt:subscribe' | grep -v grep")
        if new_proc:
            print(f"\n✅ MQTT subscriber berhasil direstart!\n   {new_proc}")
        else:
            print("\n❌ MQTT subscriber gagal start. Cek log:")
            ssh_exec(client, "tail -20 /var/log/mqtt_subscriber.log 2>/dev/null || echo 'Log tidak ditemukan'")

    # ─────────────────────────────────────────────
    # LANGKAH 6: Simulasi ingest payload suhu untuk tes
    # ─────────────────────────────────────────────
    section("LANGKAH 6: Simulasi Ingest Payload Suhu (Test)")

    # Cari device_id dari vehicle yang ada
    device_id_raw = ssh_exec(
        client,
        f"cd {project_path} && php artisan tinker --execute=\"echo \\\\App\\\\Models\\\\Vehicle::whereNotNull('device_id')->value('device_id');\""
    )
    device_id = device_id_raw.strip()

    if device_id and device_id != '':
        print(f"\n[+] Menggunakan device_id: {device_id}")
        topic = f"vehicle/{device_id}/telemetry"
        php_test = (
            f"cd {project_path} && php artisan tinker --execute=\""
            f"\\$svc = app(\\\\App\\\\Services\\\\TelemetryIngestService::class);"
            f"\\$payload = json_encode(['device_id'=>'{device_id}','timestamp'=>time(),'has_fix'=>true,'lat'=>-7.057695,'lng'=>110.4296615,'speed_kmh'=>0.4,'sat'=>4,'hdop'=>1.98,'engine_temp_c'=>34.1,'engine_overheat'=>false]);"
            f"\\$svc->ingest('{topic}', \\$payload);"
            f"echo \\\\App\\\\Models\\\\Vehicle::where('device_id','{device_id}')->value('last_engine_temp_c');\""
        )
        sim_result = ssh_exec(client, php_test)

        if "34.1" in sim_result or "34" in sim_result:
            print(f"\n✅ SIMULASI SUKSES! last_engine_temp_c tersimpan: {sim_result.strip()}")
        else:
            print(f"\n⚠️  Hasil simulasi: {sim_result.strip() or '(kosong)'}")
    else:
        print("\n⚠️  Tidak ada vehicle dengan device_id — skip simulasi.")

    # ─────────────────────────────────────────────
    # LANGKAH 7: Cek log error Laravel terbaru
    # ─────────────────────────────────────────────
    section("LANGKAH 7: Log Error Laravel Terbaru (10 baris terakhir)")

    ssh_exec(client, f"tail -30 {project_path}/storage/logs/laravel.log 2>/dev/null | grep -i 'telemetry\\|engine\\|mqtt\\|error' | tail -10 || echo 'Tidak ada log relevan'")

    # ─────────────────────────────────────────────
    # SELESAI
    # ─────────────────────────────────────────────
    print("\n" + "=" * 60)
    print("  ✅ DIAGNOSIS & PERBAIKAN SELESAI")
    print("=" * 60)
    print("\nSelanjutnya:")
    print("  1. Pastikan ESP32 mengirim payload dengan field 'engine_temp_c'")
    print("  2. Buka aplikasi Flutter → halaman tracking → cek chip suhu muncul")
    print("  3. Jika masih null, cek log: tail -f /var/log/mqtt_subscriber.log")
    print()

except Exception as e:
    print(f"\n❌ TERJADI KESALAHAN: {str(e)}")
    import traceback
    traceback.print_exc()
finally:
    try:
        client.close()
    except:
        pass
