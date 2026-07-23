import paramiko
import sys
import getpass

hostname = "203.194.115.228"
username = "root"
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")
remote_base = "/var/www/motorcycle_management"

try:
    print(f"\n[+] Menghubungkan ke VPS ({hostname})...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Mengaktifkan kembali semua Token HP di Database VPS...")
    # Paksa semua token menjadi is_active = true menggunakan tinker
    tinker_cmd = f"cd {remote_base} && php artisan tinker --execute=\"\\App\\Models\\DeviceToken::query()->update(['is_active' => true]);\""
    client.exec_command(tinker_cmd)
    
    print("\nBERHASIL! Semua token HP Anda sudah aktif kembali tanpa perlu Relog.")
    client.close()

except Exception as e:
    print(f"Gagal: {str(e)}")
    sys.exit(1)
