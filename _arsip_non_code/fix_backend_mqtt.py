import paramiko
import getpass
import sys

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" FIX MQTT PASSWORD & RESTART SERVICE DI VPS ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

def ssh_exec(client, command):
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    return stdout.read().decode()

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Mengupdate file .env di Laravel...")
    ssh_exec(client, "sed -i 's/^MQTT_PASSWORD=.*/MQTT_PASSWORD=erenvsreiner/g' /var/www/motorcycle_management/.env")
    
    print("\n[+] Membersihkan Cache Laravel & PHP-FPM...")
    ssh_exec(client, "cd /var/www/motorcycle_management && php artisan config:clear && php artisan cache:clear")
    ssh_exec(client, "systemctl restart php8.2-fpm")
    
    print("\n[+] Me-restart layanan MQTT (Supervisor)...")
    ssh_exec(client, "supervisorctl restart all")
    
    print("\n=====================================================")
    print(" ✅ SELESAI! BACKEND SEKARANG BISA CONNECT KE MQTT.")
    print("=====================================================")
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
