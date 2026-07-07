import paramiko
import getpass
import os

hostname = "203.194.115.228"
username = "root"
local_file = r"c:\laragon\www\motorcycle_management\resources\views\auth\login.blade.php"
remote_file = "/var/www/motorcycle_management/resources/views/auth/login.blade.php"

print("=====================================================")
print(" MEMPERBARUI TAMPILAN LOGIN TRINGGO ")
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
    
    print("\n[+] Mengunggah file desain tampilan yang baru ke server...")
    sftp = client.open_sftp()
    sftp.put(local_file, remote_file)
    sftp.close()
    
    print("\n[+] Memperbarui cache agar perubahan langsung terlihat...")
    ssh_exec(client, "cd /var/www/motorcycle_management && php artisan view:clear")
    
    print("\n=====================================================")
    print(" 🎉 TAMPILAN BERHASIL DIPERBARUI! 🎉")
    print(" Silakan refresh halaman login admin Anda di browser.")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
