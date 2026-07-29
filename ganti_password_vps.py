import paramiko
import getpass
import sys

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print("          GANTI PASSWORD ROOT VPS UBUNTU             ")
print("=====================================================")

# Input Password Lama & Baru
current_password = getpass.getpass(prompt="Masukkan Password ROOT VPS Saat Ini: ")
new_password = getpass.getpass(prompt="Masukkan Password ROOT VPS Baru     : ")
confirm_password = getpass.getpass(prompt="Konfirmasi Password ROOT Baru       : ")

if new_password != confirm_password:
    print("\n[ERROR] Password baru dan konfirmasi tidak cocok! Batalkan.")
    sys.exit(1)

if len(new_password) < 6:
    print("\n[ERROR] Password terlalu pendek (minimal 6 karakter)! Batalkan.")
    sys.exit(1)

print("\n[+] Menghubungkan ke VPS server (203.194.115.228)...")

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    
    # Coba koneksi dengan password lama
    try:
        client.connect(
            hostname=hostname,
            username=username,
            password=current_password,
            disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']}
        )
    except TypeError:
        client.connect(
            hostname=hostname,
            username=username,
            password=current_password
        )

    print("[+] Terhubung! Mengubah password root di Linux...")
    
    # Perintah Linux untuk mengubah password pengguna root
    cmd = f'echo "root:{new_password}" | chpasswd'
    stdin, stdout, stderr = client.exec_command(cmd, get_pty=True)
    
    err = stderr.read().decode().strip()
    out = stdout.read().decode().strip()
    
    if "error" in err.lower() or "failed" in err.lower():
        print(f"\n[ERROR] Gagal mengubah password: {err}")
    else:
        print("\n=====================================================")
        print("  🎉 BERHASIL! Password ROOT VPS berhasil diubah.   ")
        print("=====================================================")
        print("Gunakan password baru ini untuk SSH atau akses script ke depan.")

    client.close()

except paramiko.AuthenticationException:
    print("\n[ERROR] Gagal login! Password ROOT saat ini salah.")
except Exception as e:
    print(f"\n[ERROR] Terjadi kesalahan koneksi: {e}")
