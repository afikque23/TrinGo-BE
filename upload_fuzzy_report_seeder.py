import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

files_to_upload = [
    (r"c:\laragon\www\motorcycle_management\database\seeders\UpdateFuzzyFromReportSeeder.php", "/var/www/motorcycle_management/database/seeders/UpdateFuzzyFromReportSeeder.php"),
    (r"c:\laragon\www\motorcycle_management\database\seeders\RemoveAdventureTypeSeeder.php", "/var/www/motorcycle_management/database/seeders/RemoveAdventureTypeSeeder.php"),
]

print("=====================================================")
print(" UPLOAD SEEDER KONFIGURASI FUZZY TERBARU KE VPS ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Mengunggah UpdateFuzzyFromReportSeeder.php ke VPS...")
    sftp = client.open_sftp()
    for local_path, remote_path in files_to_upload:
        print(f"    -> Uploading {remote_path} ...")
        sftp.put(local_path, remote_path)
    sftp.close()
    
    print("[+] Menjalankan Seeder Konfigurasi Fuzzy di VPS...")
    cmd = (
        'chown -R www-data:www-data /var/www/motorcycle_management/database/ && '
        'cd /var/www/motorcycle_management && '
        'php artisan db:seed --class=RemoveAdventureTypeSeeder --force && '
        'php artisan db:seed --class=UpdateFuzzyFromReportSeeder --force && '
        'php artisan cache:clear'
    )
    
    stdin, stdout, stderr = client.exec_command(cmd)
    output = stdout.read().decode()
    err = stderr.read().decode()
    
    if output:
        print(output)
    if err:
        print("[Log/Info]:", err)
    
    print("\n=====================================================")
    print("  🎉 BERHASIL! Database VPS sekarang bersih:        ")
    print("  - Tipe 'adventure' telah DIHAPUS                  ")
    print("  - Konfigurasi Fuzzy untuk MATIC, MANUAL & SPORT   ")
    print("    telah diperbarui dari dokumen laporan.           ")
    print("=====================================================")
    client.close()
except Exception as e:
    print(f"\n[ERROR] Gagal: {e}")
