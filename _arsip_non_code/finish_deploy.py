import paramiko
import os
import getpass

hostname = "203.194.115.228"
username = "root"
remote_base = "/var/www/motorcycle_management"

print("=====================================================")
print(" MELANJUTKAN DEPLOY TRINGGO - ALMALINUX/UBUNTU VPS ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS baru Anda: ")

def ssh_exec(client, command):
    print(f"\n[SERVER] Mengerjakan: {command}")
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    out = stdout.read().decode()
    print(out)
    return out

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Menjalankan instalasi paket Laravel (Composer)...")
    # Tambahkan --no-interaction agar tidak nyangkut minta token github
    ssh_exec(client, f"cd {remote_base} && composer install --no-dev --optimize-autoloader --no-interaction")
    
    print("\n[+] Konfigurasi Laravel...")
    ssh_exec(client, f"cd {remote_base} && php artisan key:generate --force")
    ssh_exec(client, f"cd {remote_base} && php artisan migrate --force")
    ssh_exec(client, f"cd {remote_base} && php artisan storage:link || true")
    
    print("\n[+] Mengatur hak akses keamanan (Permissions)...")
    ssh_exec(client, f"chown -R www-data:www-data {remote_base}")
    ssh_exec(client, f"chmod -R 775 {remote_base}/storage {remote_base}/bootstrap/cache")
    
    print("\n[+] Merestart layanan server...")
    ssh_exec(client, "systemctl restart supervisor nginx php8.2-fpm")
    
    client.close()
    print("\n=====================================================")
    print(" 🎉 PROSES LANJUTAN SELESAI! 🎉")
    print(f" Silakan cek ulang website Anda di: http://tringgo.site")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
