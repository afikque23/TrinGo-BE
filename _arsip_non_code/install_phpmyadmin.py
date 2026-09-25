import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" INSTALASI PHPMYADMIN DI VPS TRINGGO ")
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
    
    print("\n[+] Mendownload dan menginstal phpMyAdmin...")
    # noninteractive prevents the package manager from asking which web server to configure (since we use nginx, we configure it manually)
    ssh_exec(client, "DEBIAN_FRONTEND=noninteractive apt-get install -y -q phpmyadmin")
    
    print("\n[+] Menghubungkan phpMyAdmin ke website Tringgo...")
    # Create a symlink in Laravel's public directory
    ssh_exec(client, "ln -sfn /usr/share/phpmyadmin /var/www/motorcycle_management/public/phpmyadmin")
    ssh_exec(client, "ln -sfn /usr/share/phpmyadmin /var/www/motorcycle_management/public/db-admin")
    
    # Fix permissions
    ssh_exec(client, "chown -R www-data:www-data /usr/share/phpmyadmin")
    
    print("\n=====================================================")
    print(" 🎉 INSTALASI PHPMYADMIN SELESAI! 🎉")
    print(" Silakan refresh halaman https://tringgo.site/phpmyadmin")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
