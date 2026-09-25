import paramiko
import os
from getpass import getpass

VPS_IP = '203.194.115.228'
VPS_USER = 'root'
VPS_PATH = '/var/www/motorcycle_management'

def main():
    print("============================================================")
    print("   RESTART MQTT WORKER VPS")
    print("============================================================")
    
    password = getpass(prompt='Masukkan Password ROOT VPS: ')
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    
    try:
        print("\n[+] Menghubungkan ke VPS...")
        ssh.connect(hostname=VPS_IP, username=VPS_USER, password=password)
        print("✅ Terhubung ke VPS!\n")
        
        print("[+] Mencari proses MQTT...")
        command = "ps aux | grep 'artisan mqtt:subscribe' | grep -v grep"
        print(f"    [CMD] {command}")
        stdin, stdout, stderr = ssh.exec_command(command)
        out = stdout.read().decode('utf-8')
        
        if out:
            print("[+] Proses ditemukan. Menghentikan proses (Supervisor akan otomatis restart)...")
            command = "pkill -f 'artisan mqtt:subscribe'"
            ssh.exec_command(command)
            print("✅ Proses MQTT berhasil direstart!")
        else:
            print("[-] Tidak ada proses MQTT yang berjalan.")
            
        print("✅ PROSES SELESAI!")
        
    except Exception as e:
        print(f"❌ ERROR: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    main()
