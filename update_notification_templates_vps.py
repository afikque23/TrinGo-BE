import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

files_to_upload = [
    (r"c:\laragon\www\motorcycle_management\app\Listeners\CheckCriticalFuzzyStatusListener.php", "/var/www/motorcycle_management/app/Listeners/CheckCriticalFuzzyStatusListener.php"),
    (r"c:\laragon\www\motorcycle_management\app\Jobs\CheckFuzzyWarningJob.php", "/var/www/motorcycle_management/app/Jobs/CheckFuzzyWarningJob.php"),
    (r"c:\laragon\www\motorcycle_management\database\seeders\NotificationTemplateSeederUpdate.php", "/var/www/motorcycle_management/database/seeders/NotificationTemplateSeederUpdate.php"),
]

print("=====================================================")
print(" UPLOAD PERBAIKAN NOTIFIKASI KE VPS ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Mengunggah file listener & seeder ke VPS...")
    sftp = client.open_sftp()
    for local_path, remote_path in files_to_upload:
        print(f"    -> Uploading {remote_path} ...")
        sftp.put(local_path, remote_path)
    sftp.close()
    
    print("[+] Menjalankan Seeder & Update Database Notifikasi di VPS...")
    
    # Tambahkan --force agar artisan db:seed bisa berjalan di lingkungan production VPS
    cmd = (
        'chown -R www-data:www-data /var/www/motorcycle_management/app/ /var/www/motorcycle_management/database/ && '
        'DB_PASS=$(grep "^DB_PASSWORD=" /var/www/motorcycle_management/.env | cut -d= -f2) && '
        'mysql -u tringgo_db -p"$DB_PASS" motorcyclemanagement -e "'
        'UPDATE notification_templates SET name=\'Kondisi Kritis\' WHERE trigger_type=\'fuzzy_critical\'; '
        'UPDATE notification_templates SET name=\'Peringatan Servis\', message_template=\'Halo {user_name}, komponen {service_name} motor {vehicle_name} Anda memerlukan pengecekan. Jadwalkan servis segera!\' WHERE trigger_type=\'fuzzy_warning\'; '
        'UPDATE notifications SET title = REPLACE(title, \' (Fuzzy Critical)\', \'\'); '
        'UPDATE notifications SET title = REPLACE(title, \' (Fuzzy Warning)\', \'\'); '
        'UPDATE notifications SET message = REPLACE(message, \' (Skor: )\', \'\');'
        '" && cd /var/www/motorcycle_management && php artisan db:seed --class=NotificationTemplateSeederUpdate --force && php artisan cache:clear'
    )
    
    stdin, stdout, stderr = client.exec_command(cmd)
    output = stdout.read().decode()
    err = stderr.read().decode()
    
    if output:
        print(output)
    if err:
        print("[Log/Info]:", err)
    
    print("\n=====================================================")
    print("  🎉 BERHASIL! Notifikasi di VPS kini:               ")
    print("  1. Menampilkan SEMUA komponen kritis (gabungan)    ")
    print("  2. Kata 'Fuzzy' telah dihapus dari judul & isi!   ")
    print("=====================================================")
    client.close()
except Exception as e:
    print(f"\n[ERROR] Gagal: {e}")
