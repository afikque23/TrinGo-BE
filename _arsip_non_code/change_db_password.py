import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" MENGGANTI PASSWORD DATABASE TRINGGO ")
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
    
    # 1. Update MariaDB password
    print("\n[+] Mengubah password database di sistem MariaDB (MySQL)...")
    ssh_exec(client, 'mysql -u root -e "ALTER USER \'tringgo_db\'@\'localhost\' IDENTIFIED BY \'praupos1\'; FLUSH PRIVILEGES;"')
    
    # 2. Update Laravel .env
    print("\n[+] Menghubungkan ulang website Laravel dengan password baru...")
    ssh_exec(client, 'sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=praupos1/g" /var/www/motorcycle_management/.env')
    
    # 3. Clear laravel cache and restart supervisor
    print("\n[+] Membersihkan cache dan merestart background worker...")
    ssh_exec(client, "cd /var/www/motorcycle_management && php artisan config:clear")
    ssh_exec(client, "systemctl restart supervisor")
    
    print("\n=====================================================")
    print(" 🎉 PASSWORD DATABASE BERHASIL DIGANTI! 🎉")
    print(" Password baru Anda sekarang: praupos1")
    print(" Anda sudah bisa login di phpMyAdmin dengan password tersebut.")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
