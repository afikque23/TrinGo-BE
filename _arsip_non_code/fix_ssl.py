import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" MEMPERBAIKI INSTALASI SSL (HTTPS) ")
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
    
    print("\n[+] Menghapus domain yang tidak valid dari Nginx...")
    ssh_exec(client, 'sed -i "s/server_name.*/server_name tringgo.site www.tringgo.site vps.tringgo.site;/g" /etc/nginx/sites-available/tringgo')
    ssh_exec(client, "systemctl reload nginx")
    
    print("\n[+] Meminta ulang sertifikat SSL hanya untuk domain utama...")
    # Hanya request untuk tringgo.site dan www.tringgo.site
    ssh_exec(client, "certbot --nginx -d tringgo.site -d www.tringgo.site --non-interactive --agree-tos -m admin@tringgo.site --redirect")
    
    print("\n=====================================================")
    print(" 🎉 PERBAIKAN SSL SELESAI! 🎉")
    print(" Silakan cek ulang website https://tringgo.site")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
