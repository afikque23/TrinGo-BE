import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" MENGGANTI KREDENSIAL ADMIN TRINGGO ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

def ssh_exec(client, command):
    print(f"\n[SERVER] Mengerjakan: {command}")
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    out = stdout.read().decode()
    print(out)
    return out

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Mengubah Email dan Password Admin di database VPS...")
    php_code = """
    $user = \\App\\Models\\User::where('role', 'admin')->first();
    if($user) {
        $user->email = 'tugasakhir@tringgo.com';
        $user->password = \\Illuminate\\Support\\Facades\\Hash::make('nengmolencantik');
        $user->save();
        echo "BERHASIL DIUBAH!\\n";
    } else {
        echo "TIDAK DITEMUKAN!\\n";
    }
    """
    ssh_exec(client, f"cd /var/www/motorcycle_management && php artisan tinker << 'EOF'{php_code}\\nEOF")
    
    print("\n=====================================================")
    print(" 🎉 KREDENSIAL ADMIN BERHASIL DIGANTI! 🎉")
    print(" Email Baru   : tugasakhir@tringgo.com")
    print(" Password Baru: nengmolencantik")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()
