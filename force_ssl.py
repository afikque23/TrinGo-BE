import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" FORCE FIX SSL (HTTPS) TRINGGO ")
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
    
    print("\n[+] Memperbaiki konfigurasi Nginx secara paksa...")
    # Menghapus baris server_name yang ada dan menggantinya dengan yang benar
    ssh_exec(client, 'sed -i "/server_name/c\\    server_name tringgo.site www.tringgo.site 203.194.115.228;" /etc/nginx/sites-available/tringgo')
    ssh_exec(client, "systemctl reload nginx")
    
    print("\n[+] Memasang sertifikat SSL yang sudah berhasil didownload ke Nginx...")
    ssh_exec(client, "certbot install --cert-name tringgo.site --nginx --redirect")
    
    print("\n=====================================================")
    print(" 🎉 PERBAIKAN SSL BENAR-BENAR SELESAI! 🎉")
    print(" Silakan cek ulang website https://tringgo.site")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
