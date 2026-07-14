import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" MEMPERBAIKI ERROR 'DOMDocument' DI LARAVEL ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

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
    
    print("\n[+] Menginstal ekstensi PHP XML (DOMDocument) yang hilang...")
    ssh_exec(client, "DEBIAN_FRONTEND=noninteractive apt-get install -y -q php8.2-xml")
    
    print("\n[+] Merestart layanan PHP...")
    ssh_exec(client, "systemctl restart php8.2-fpm")
    
    print("\n[+] Menjalankan ulang perintah cache Laravel...")
    ssh_exec(client, "cd /var/www/motorcycle_management && php artisan config:clear")
    
    print("\n=====================================================")
    print(" 🎉 PERBAIKAN SELESAI! 🎉")
    print(" Ekstensi PHP XML sudah terpasang dan error tidak akan muncul lagi.")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
