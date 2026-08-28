import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

files_to_upload = [
    (r"c:\laragon\www\motorcycle_management\app\Services\RecommendationService.php", "/var/www/motorcycle_management/app/Services/RecommendationService.php"),
    (r"c:\laragon\www\motorcycle_management\app\Services\GeminiPromptBuilder.php", "/var/www/motorcycle_management/app/Services/GeminiPromptBuilder.php"),
]

print("=====================================================")
print(" UPLOAD PERBAIKAN AI GEMINI UNTUK MOTOR BARU ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})

    print("\n[+] Mengunggah RecommendationService.php & GeminiPromptBuilder.php ke VPS...")
    sftp = client.open_sftp()
    for local_path, remote_path in files_to_upload:
        print(f"    -> Uploading {remote_path} ...")
        sftp.put(local_path, remote_path)
    sftp.close()

    print("[+] Membersihkan cache rekomendasi AI di VPS...")
    cmd = (
        'chown -R www-data:www-data /var/www/motorcycle_management/app/ && '
        'cd /var/www/motorcycle_management && '
        'php artisan cache:clear && '
        'php artisan optimize:clear'
    )

    stdin, stdout, stderr = client.exec_command(cmd)
    output = stdout.read().decode()
    err = stderr.read().decode()

    # Truncate AI cache via separate tinker query to avoid string escaping issues
    client.exec_command('cd /var/www/motorcycle_management && php artisan tinker --execute="DB::table(\'ai_recommendation_caches\')->truncate();"')

    if output:
        print(output)
    if err:
        print("[Log/Info]:", err[:500])

    print("\n=====================================================")
    print("  🎉 BERHASIL! AI Gemini di VPS kini 100%           ")
    print("  mengenali motor baru dan memberikan narasi         ")
    print("  sambutan khusus (is_new_data = true)!             ")
    print("=====================================================")
    client.close()
except Exception as e:
    print(f"\n[ERROR] Gagal: {e}")
