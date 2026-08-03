import paramiko
import getpass
import sys

hostname = "203.194.115.228"
username = "root"

print("="*50)
print("ALAT PERBAIKAN KONFIGURASI GEMINI AI DI VPS")
print("="*50)

password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

try:
    print(f"\nMenghubungkan ke {hostname}...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    
    # Try connecting
    try:
        client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    except TypeError:
        client.connect(hostname, username=username, password=password)
        
    print("Berhasil terhubung ke VPS!")
    print("Memperbaiki konfigurasi .env untuk Gemini...")
    
    # Command to fix GEMINI_MODEL
    cmd1 = "sed -i 's/GEMINI_MODEL=.*/GEMINI_MODEL=gemini-2.0-flash/' /var/www/motorcycle_management/.env"
    stdin, stdout, stderr = client.exec_command(cmd1)
    stdout.read()
    
    # Command to fix GEMINI_MAX_OUTPUT_TOKENS
    cmd2 = "sed -i 's/GEMINI_MAX_OUTPUT_TOKENS=.*/GEMINI_MAX_OUTPUT_TOKENS=1000/' /var/www/motorcycle_management/.env"
    stdin, stdout, stderr = client.exec_command(cmd2)
    stdout.read()
    
    print("Konfigurasi di .env berhasil diubah!")
    print("Membersihkan cache aplikasi Laravel...")
    
    # Clear config cache
    cmd3 = "cd /var/www/motorcycle_management && php artisan config:clear && php artisan cache:clear"
    stdin, stdout, stderr = client.exec_command(cmd3)
    
    print(stdout.read().decode())
    
    err = stderr.read().decode()
    if err:
        print("Error/Warning:", err)
    
    print("="*50)
    print("SELESAI! Konfigurasi VPS sudah berhasil diperbaiki.")
    print("Silakan coba hapus dan tambahkan ulang motor di aplikasi Anda.")
    
    client.close()
    
except paramiko.AuthenticationException:
    print("GAGAL: Password VPS salah. Silakan coba lagi.")
except Exception as e:
    print(f"GAGAL: Terjadi kesalahan: {str(e)}")
