import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"
remote_base = "/var/www/motorcycle_management"

print("=====================================================")
print(" MENGHAPUS ADMINER & MENGINSTAL PHPMYADMIN ASLI ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

def ssh_exec(client, command):
    print(f"\n[SERVER] Mengerjakan: {command}")
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    out = stdout.read().decode()
    if out.strip():
        print(out)
    return out

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Menghapus Adminer lama (db-admin)...")
    ssh_exec(client, f"rm -rf {remote_base}/public/db-admin")
    
    print("\n[+] Mendownload phpMyAdmin versi terbaru...")
    ssh_exec(client, "wget -qO phpmyadmin.zip https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip")
    
    print("\n[+] Mengekstrak phpMyAdmin ke dalam folder public server...")
    ssh_exec(client, f"unzip -o -q phpmyadmin.zip -d {remote_base}/public")
    
    print("\n[+] Merapikan nama folder menjadi 'phpmyadmin'...")
    ssh_exec(client, f"rm -rf {remote_base}/public/phpmyadmin") # Hapus jika sudah ada
    ssh_exec(client, f"mv {remote_base}/public/phpMyAdmin-*-all-languages {remote_base}/public/phpmyadmin")
    
    print("\n[+] Mengatur hak akses agar aman...")
    ssh_exec(client, f"chown -R www-data:www-data {remote_base}/public/phpmyadmin")
    ssh_exec(client, "rm phpmyadmin.zip") # Hapus file zip sementara
    
    client.close()
    print("\n=====================================================")
    print(" 🎉 INSTALASI PHPMYADMIN BERHASIL! 🎉")
    print(" Silakan buka di browser Anda: https://tringgo.site/phpmyadmin")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
