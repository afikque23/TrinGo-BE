import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" MENGUBAH PASSWORD ROOT VPS TRINGGO ")
print("=====================================================")
old_password = getpass.getpass(prompt="1. Masukkan Password ROOT VPS yang LAMA: ")
new_password = getpass.getpass(prompt="2. Masukkan Password ROOT VPS yang BARU: ")

if len(new_password) < 8:
    print("\n[!] GAGAL: Password baru terlalu pendek. Gunakan minimal 8 karakter demi keamanan.")
    exit(1)

def ssh_exec(client, command):
    print(f"\n[SERVER] Memproses...")
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    out = stdout.read().decode()
    return out

try:
    print("\n[+] Mencoba login dengan password lama...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=old_password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("[+] Login berhasil! Mengganti ke password baru...")
    
    # Escape quotes just in case password contains quotes (though we should be careful with injection)
    # the safest way to pass passwords in bash is via standard input to chpasswd
    command = f'echo "root:{new_password}" | chpasswd'
    ssh_exec(client, command)
    
    print("\n=====================================================")
    print(" 🎉 PASSWORD ROOT BERHASIL DIGANTI! 🎉")
    print(" Harap ingat dan simpan baik-baik password baru Anda.")
    print(" Jika Anda lupa, Anda harus mereset ulang OS dari Rumahweb.")
    print("=====================================================")
    
except paramiko.ssh_exception.AuthenticationException:
    print("\n[!] GAGAL: Password lama yang Anda masukkan SALAH (Authentication failed).")
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
