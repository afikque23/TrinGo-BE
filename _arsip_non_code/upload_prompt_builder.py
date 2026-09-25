import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

files_to_upload = [
    (r"c:\laragon\www\motorcycle_management\app\Services\GeminiPromptBuilder.php", "/var/www/motorcycle_management/app/Services/GeminiPromptBuilder.php"),
    (r"c:\laragon\www\motorcycle_management\app\Services\GeminiService.php", "/var/www/motorcycle_management/app/Services/GeminiService.php"),
    (r"c:\laragon\www\motorcycle_management\app\Http\Controllers\Api\RecommendationController.php", "/var/www/motorcycle_management/app/Http/Controllers/Api/RecommendationController.php"),
]

print("=====================================================")
print(" UPLOAD GEMINI SERVICES TERBARU KE VPS ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Mengunggah file ke VPS...")
    sftp = client.open_sftp()
    for local_path, remote_path in files_to_upload:
        print(f"    -> Uploading {remote_path} ...")
        sftp.put(local_path, remote_path)
    sftp.close()
    
    print("[+] Mengatur izin file, mengosongkan cache AI & cache aplikasi di VPS...")
    cmd = 'chown -R www-data:www-data /var/www/motorcycle_management/app/Services/ && mysql -u tringgo_db -p"$(grep \'^DB_PASSWORD=\' /var/www/motorcycle_management/.env | cut -d= -f2)" motorcyclemanagement -e "TRUNCATE TABLE ai_recommendation_caches;" && cd /var/www/motorcycle_management && php artisan cache:clear'
    
    stdin, stdout, stderr = client.exec_command(cmd, get_pty=True)
    print(stdout.read().decode())
    
    print("\n=====================================================")
    print("  🎉 BERHASIL! Seluruh file AI & Fallback di VPS     ")
    print("      sudah menggunakan narasi 3-4 kalimat!        ")
    print("=====================================================")
    client.close()
except Exception as e:
    print(f"\n[ERROR] Gagal: {e}")
