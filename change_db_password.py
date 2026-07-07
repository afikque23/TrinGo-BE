import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"
new_password = "horemaunyamnyam"

print("=====================================================")
print(" UBAH PASSWORD DATABASE (ADMINER/PHPMYADMIN) ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    # Update MariaDB
    print(f"\n[+] Memperbarui password database MariaDB menjadi: {new_password}")
    client.exec_command(f"mysql -u root -e \"ALTER USER 'tringgo_db'@'localhost' IDENTIFIED BY '{new_password}'; FLUSH PRIVILEGES;\"")
    
    # Update .env Laravel
    print("\n[+] Memperbarui konfigurasi password di file .env Laravel...")
    client.exec_command(f"sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD={new_password}/' /var/www/motorcycle_management/.env")
    
    # Membersihkan Cache Laravel agar mengenali password baru
    print("\n[+] Membersihkan cache Laravel...")
    client.exec_command("cd /var/www/motorcycle_management && php artisan config:clear && php artisan cache:clear")
    
    client.close()
    print("\n=====================================================")
    print(" 🎉 BERHASIL! Password database telah diganti! 🎉")
    print(f" Silakan login ke https://tringgo.site/db-admin dengan:")
    print(f" Username : tringgo_db")
    print(f" Password : {new_password}")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
