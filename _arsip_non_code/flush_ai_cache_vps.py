import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print("  RESETS AI CACHE DI VPS (PAKSA GEMINI GENERATE BARU)")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Mengosongkan tabel cache AI & cache aplikasi di VPS...")
    cmd = 'mysql -u tringgo_db -p"$(grep \'^DB_PASSWORD=\' /var/www/motorcycle_management/.env | cut -d= -f2)" motorcyclemanagement -e "TRUNCATE TABLE ai_recommendation_caches;" && cd /var/www/motorcycle_management && php artisan cache:clear'
    
    stdin, stdout, stderr = client.exec_command(cmd, get_pty=True)
    out = stdout.read().decode()
    print(out)
    
    print("\n=====================================================")
    print("  🎉 BERHASIL! Cache AI di VPS berhasil dikosongkan.")
    print("=====================================================")
    print("Sekarang saat Anda klik Send di Postman, Gemini API akan")
    print("langsung menghasilkan narasi baru 3-4 kalimat!")

    client.close()
except Exception as e:
    print(f"\n[ERROR] Gagal: {e}")
