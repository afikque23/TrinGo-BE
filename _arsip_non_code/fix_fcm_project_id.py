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
    try:
        client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    except TypeError:
        client.connect(hostname, username=username, password=password)
    
    print("\n[+] Menambahkan FCM_PROJECT_ID ke file .env di VPS...")
    # Menambahkan konfigurasi ke baris paling bawah .env
    add_env_cmd = f"echo 'FCM_PROJECT_ID=mototracker-76e10' >> {remote_base}/.env"
    client.exec_command(add_env_cmd)
    
    print("[+] Membersihkan cache konfigurasi Laravel...")
    client.exec_command(f"cd {remote_base} && php artisan config:clear && php artisan cache:clear")
    
    print("\nBERHASIL! Konfigurasi Project ID Firebase telah diperbaiki di VPS.")
    client.close()

except Exception as e:
    print(f"Gagal: {str(e)}")
    sys.exit(1)
