import paramiko
import sys
import getpass

hostname = "203.194.115.228"
username = "root"
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")
remote_base = "/var/www/motorcycle_management"

try:
    print(f"\nMenghubungkan ke VPS ({hostname}) untuk mengambil Log...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Perintah Linux untuk mengecek 100 baris terakhir dari laravel.log dan mencari kata Notification
    command = f"cd {remote_base} && tail -n 100 storage/logs/laravel.log | grep -E -i 'FcmNotificationService|NotificationService|Push notification'"
    
    stdin, stdout, stderr = client.exec_command(command)
    out = stdout.read().decode('utf-8').strip()
    err = stderr.read().decode('utf-8').strip()
    
    print("\n" + "="*50)
    print("HASIL LOG NOTIFIKASI DARI VPS (100 Baris Terakhir):")
    print("="*50)
    
    if out:
        print(out)
    else:
        print("Tidak ada log tentang kegagalan/keberhasilan notifikasi di 100 baris terakhir.")
        if err:
            print("\nError (jika ada):", err)
            
    print("="*50)
    client.close()

except Exception as e:
    print(f"Gagal: {str(e)}")
    sys.exit(1)
