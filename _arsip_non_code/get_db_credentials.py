import paramiko
import getpass
import re

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" MENGAMBIL KREDENSIAL DATABASE TRINGGO ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

def ssh_exec(client, command):
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    out = stdout.read().decode()
    return out

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Mengambil password database langsung dari server...")
    env_content = ssh_exec(client, "cat /var/www/motorcycle_management/.env")
    
    db_user_match = re.search(r"DB_USERNAME=(.+)", env_content)
    db_pass_match = re.search(r"DB_PASSWORD=(.+)", env_content)
    
    if db_user_match and db_pass_match:
        db_user = db_user_match.group(1).strip()
        db_pass = db_pass_match.group(1).strip()
        
        print("\n=====================================================")
        print(" ✅ BERHASIL! Ini adalah kredensial yang benar:")
        print(f" Nama Pengguna (Username) : {db_user}")
        print(f" Kata Sandi (Password)    : {db_pass}")
        print("=====================================================")
        print("\nSilakan copy-paste persis (jangan ada spasi berlebih) ke form login phpMyAdmin Anda.")
    else:
        print("[!] Gagal menemukan DB_USERNAME atau DB_PASSWORD di file .env server.")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
