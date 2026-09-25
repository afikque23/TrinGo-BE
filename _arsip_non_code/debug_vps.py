import paramiko
import getpass
import sys

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" DEBUG VPS ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

def ssh_exec(client, command):
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    return stdout.read().decode()

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Isi .env untuk MQTT:")
    print(ssh_exec(client, "grep '^MQTT_' /var/www/motorcycle_management/.env"))
    
    print("\n[+] Mengubah password ke erenvsreiner HANYA jika masih Tringgo123...")
    ssh_exec(client, "sed -i 's/^MQTT_PASSWORD=.*/MQTT_PASSWORD=erenvsreiner/g' /var/www/motorcycle_management/.env")
    
    print("\n[+] Fix Permissions...")
    ssh_exec(client, "chown -R www-data:www-data /var/www/motorcycle_management/storage /var/www/motorcycle_management/bootstrap/cache")
    
    print("\n[+] Restart Services...")
    ssh_exec(client, "cd /var/www/motorcycle_management && php artisan config:clear && php artisan cache:clear")
    ssh_exec(client, "systemctl restart php8.2-fpm")
    ssh_exec(client, "supervisorctl restart all")
    
    print("\n[+] Done! Cek .env sekarang:")
    print(ssh_exec(client, "grep '^MQTT_' /var/www/motorcycle_management/.env"))
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
