import paramiko
import sys
import getpass
import time

hostname = "203.194.115.228"
username = "root"
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

try:
    print(f"\n[+] Menghubungkan ke VPS ({hostname})...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # 1. Cek waktu VPS sebelum diperbaiki
    print("\n[INFO] Waktu VPS SEBELUM sinkronisasi:")
    stdin, stdout, stderr = client.exec_command("date")
    print(stdout.read().decode().strip())
    
    # 2. Sinkronisasi paksa waktu VPS menggunakan chrony atau systemd-timesyncd atau ntpdate
    print("\n[+] Memaksa sinkronisasi jam dengan server Google NTP (Tunggu sebentar)...")
    
    commands = [
        "timedatectl set-ntp true",
        "systemctl restart systemd-timesyncd",
        "apt-get install -y ntpdate && ntpdate pool.ntp.org",
    ]
    
    for cmd in commands:
        client.exec_command(cmd)
        time.sleep(2)
        
    # 3. Cek waktu VPS sesudah diperbaiki
    print("\n[INFO] Waktu VPS SESUDAH sinkronisasi:")
    stdin, stdout, stderr = client.exec_command("date")
    print(stdout.read().decode().strip())
    
    # 4. Mengecek kondisi file firebase_credentials.json di VPS
    remote_base = "/var/www/motorcycle_management"
    stdin, stdout, stderr = client.exec_command(f"cat {remote_base}/storage/app/firebase_credentials.json")
    json_content = stdout.read().decode().strip()
    
    if json_content:
        if "\\n" not in json_content and "PRIVATE KEY" in json_content:
             print("\n[PERINGATAN KRITIS] Format private_key di firebase_credentials.json VPS Anda sepertinya RUSAK! Tidak ada karakter enter (\\n).")
             print("Silakan upload ulang file aslinya lewat SFTP (Jangan di-copas manual).")
        else:
             print("\n[OK] Format firebase_credentials.json terlihat aman. Kemungkinan besar tadi hanya masalah sinkronisasi jam.")
    else:
        print("\n[!] File firebase_credentials.json TIDAK DITEMUKAN di VPS!")
        
    client.close()
    print("\nSelesai! Silakan coba kirim ulang Notifikasi Test dari Web Admin.")

except Exception as e:
    print(f"Gagal: {str(e)}")
    sys.exit(1)
