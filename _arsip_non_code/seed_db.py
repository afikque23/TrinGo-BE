import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" MENGISI DATA AWAL (SEEDING) - TRINGGO VPS ")
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
    
    print("\n[+] Menjalankan Seeder Laravel (membuat akun admin & data bawaan)...")
    ssh_exec(client, "cd /var/www/motorcycle_management && php artisan db:seed --force")
    
    print("\n=====================================================")
    print(" 🎉 SEEDING SELESAI! 🎉")
    print(" Akun Admin sekarang sudah bisa digunakan.")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
