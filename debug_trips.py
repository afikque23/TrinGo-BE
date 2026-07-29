"""
Debug tabel trips di VPS — cek struktur dan isi data
"""
import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"
remote_base = "/var/www/motorcycle_management"

password = getpass.getpass("Masukkan Password ROOT VPS Anda: ")

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    client.connect(hostname, username=username, password=password,
                   disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
except TypeError:
    client.connect(hostname, username=username, password=password)

def q(sql):
    cmd = f'mysql -h 127.0.0.1 -u tringgo_db -p"$(grep \'^DB_PASSWORD=\' /var/www/motorcycle_management/.env | cut -d= -f2)" motorcyclemanagement -se "{sql}" 2>/dev/null'
    stdin, stdout, stderr = client.exec_command(cmd)
    return stdout.read().decode('utf-8', errors='replace').strip()

print("\n[1] Jumlah total baris di tabel trips:")
print(q("SELECT COUNT(*) FROM trips"))

print("\n[2] Kolom-kolom yang ada di tabel trips:")
print(q("DESCRIBE trips"))

print("\n[3] Sample 5 baris pertama trips (semua kolom):")
print(q("SELECT * FROM trips LIMIT 5"))

print("\n[4] Cek nilai distance_km:")
print(q("SELECT COUNT(*) as total, SUM(CASE WHEN distance_km IS NULL THEN 1 ELSE 0 END) as null_count, SUM(CASE WHEN distance_km=0 THEN 1 ELSE 0 END) as zero_count, SUM(CASE WHEN distance_km>0 THEN 1 ELSE 0 END) as has_data FROM trips"))

print("\n[5] Cek nilai ended_at:")
print(q("SELECT COUNT(*) as total, SUM(CASE WHEN ended_at IS NULL THEN 1 ELSE 0 END) as null_ended, SUM(CASE WHEN ended_at IS NOT NULL THEN 1 ELSE 0 END) as has_ended FROM trips"))

print("\n[6] Sample 5 baris dengan kolom penting saja:")
print(q("SELECT id, started_at, ended_at, distance_km, avg_speed_kph FROM trips ORDER BY id DESC LIMIT 10"))

client.close()
