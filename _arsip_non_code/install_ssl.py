import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" INSTALASI SSL (HTTPS) - TRINGGO VPS ")
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
    
    print("\n[+] Memasang Certbot (Let's Encrypt)...")
    ssh_exec(client, "apt-get update --allow-releaseinfo-change -y")
    ssh_exec(client, "apt-get install -y certbot python3-certbot-nginx")
    
    print("\n[+] Menyesuaikan konfigurasi domain di Nginx...")
    ssh_exec(client, 'sed -i "s/server_name vps.tringgo.site/server_name tringgo.site www.tringgo.site vps.tringgo.site/g" /etc/nginx/sites-available/tringgo')
    ssh_exec(client, "systemctl reload nginx")
    
    print("\n[+] Meminta sertifikat SSL dari Let's Encrypt (mohon tunggu)...")
    # --redirect akan otomatis mengubah trafik HTTP menjadi HTTPS
    ssh_exec(client, "certbot --nginx -d tringgo.site -d www.tringgo.site --non-interactive --agree-tos -m admin@tringgo.site --redirect --expand")
    
    print("\n[+] Memperbarui URL Aplikasi Laravel Anda ke HTTPS...")
    ssh_exec(client, 'sed -i "s/APP_URL=http:\\/\\/tringgo.site/APP_URL=https:\\/\\/tringgo.site/g" /var/www/motorcycle_management/.env')
    ssh_exec(client, "cd /var/www/motorcycle_management && php artisan config:clear && php artisan view:clear && php artisan cache:clear")
    
    print("\n=====================================================")
    print(" 🎉 INSTALASI SSL (HTTPS) SELESAI! 🎉")
    print(" Silakan tutup tab website Anda, lalu buka ulang https://tringgo.site")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
