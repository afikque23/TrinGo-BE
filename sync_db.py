import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" FIX DATABASE PASSWORD SINKRONISASI ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    # Ambil password dari .env
    print("\n[+] Mengambil password terbaru dari .env...")
    stdin, stdout, stderr = client.exec_command("grep DB_PASSWORD /var/www/motorcycle_management/.env | cut -d '=' -f2")
    db_pass = stdout.read().decode().strip()
    print(f"Password .env ditemukan: {db_pass}")
    
    # Update MariaDB
    print("\n[+] Memperbarui password database MariaDB agar cocok dengan .env...")
    client.exec_command(f"mysql -u root -e \"ALTER USER 'tringgo_db'@'localhost' IDENTIFIED BY '{db_pass}'; FLUSH PRIVILEGES;\"")
    
    # Jalankan ulang konfigurasi akhir
    print("\n[+] Menjalankan ulang perintah migrate (karena sebelumnya gagal)...")
    stdin, stdout, stderr = client.exec_command("cd /var/www/motorcycle_management && php artisan migrate --force", get_pty=True)
    print(stdout.read().decode())
    
    client.close()
    print("\n[+] Berhasil! Sinkronisasi database selesai.")
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
