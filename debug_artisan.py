import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" MENDIAGNOSA DAN MEMPERBAIKI PHP CLI ")
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
    
    # Check default PHP version
    print("\n[+] Mengecek versi PHP default di server...")
    ssh_exec(client, "php -v")
    
    # Set default PHP to 8.2 (since that's what we installed extensions for)
    print("\n[+] Memastikan PHP 8.2 digunakan sebagai default...")
    ssh_exec(client, "update-alternatives --set php /usr/bin/php8.2")
    
    # Enable XML explicitly just in case
    print("\n[+] Mengaktifkan modul XML secara paksa...")
    ssh_exec(client, "phpenmod -v 8.2 xml")
    
    print("\n[+] Menjalankan ulang cache Laravel menggunakan PHP 8.2...")
    ssh_exec(client, "cd /var/www/motorcycle_management && php artisan config:clear -v")
    
    print("\n=====================================================")
    print(" 🎉 PERBAIKAN TAHAP 2 SELESAI! 🎉")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
