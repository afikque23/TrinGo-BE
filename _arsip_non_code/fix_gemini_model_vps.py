import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" SWITCH GEMINI MODEL KE gemini-2.0-flash (1500 RPD)  ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Memeriksa dan memperbarui GEMINI_MODEL di VPS...")
    cmd = """
    sed -i 's/GEMINI_MODEL=.*/GEMINI_MODEL=gemini-2.0-flash/' /var/www/motorcycle_management/.env || echo "GEMINI_MODEL=gemini-2.0-flash" >> /var/www/motorcycle_management/.env
    if ! grep -q "GEMINI_MODEL" /var/www/motorcycle_management/.env; then
        echo "GEMINI_MODEL=gemini-2.0-flash" >> /var/www/motorcycle_management/.env
    fi
    cd /var/www/motorcycle_management && php artisan config:clear && php artisan cache:clear
    """
    
    stdin, stdout, stderr = client.exec_command(cmd, get_pty=True)
    out = stdout.read().decode()
    print(out)
    
    print("\n=====================================================")
    print("  🎉 BERHASIL! Model Gemini di VPS telah diubah ke  ")
    print("      'gemini-2.0-flash' dengan kuota 1.500 RPD!    ")
    print("=====================================================")

    client.close()
except Exception as e:
    print(f"\n[ERROR] Gagal: {e}")
