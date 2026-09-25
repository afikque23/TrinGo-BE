"""
Ambil data GPS nyata dari database VPS untuk Tabel 4.2 Laporan TA
Sumber: tabel trip_points (data rekaman perjalanan ESP32 nyata)
"""

import paramiko
import getpass
import math

hostname = "203.194.115.228"
username = "root"
remote_base = "/var/www/motorcycle_management"

def haversine(lat1, lon1, lat2, lon2):
    R = 6371000
    phi1, phi2 = math.radians(lat1), math.radians(lat2)
    dphi = math.radians(lat2 - lat1)
    dlambda = math.radians(lon2 - lon1)
    a = math.sin(dphi/2)**2 + math.cos(phi1)*math.cos(phi2)*math.sin(dlambda/2)**2
    return round(R * 2 * math.atan2(math.sqrt(a), math.sqrt(1-a)), 2)

password = getpass.getpass("Masukkan Password ROOT VPS Anda: ")

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    client.connect(hostname, username=username, password=password,
                   disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
except TypeError:
    client.connect(hostname, username=username, password=password)

# Baca kredensial database dari .env VPS
def ssh_exec(cmd):
    stdin, stdout, stderr = client.exec_command(cmd)
    return stdout.read().decode('utf-8', errors='replace').strip()

db_host     = ssh_exec(f"grep '^DB_HOST=' {remote_base}/.env | cut -d= -f2")
db_name     = ssh_exec(f"grep '^DB_DATABASE=' {remote_base}/.env | cut -d= -f2")
db_user     = ssh_exec(f"grep '^DB_USERNAME=' {remote_base}/.env | cut -d= -f2")
db_pass     = ssh_exec(f"grep '^DB_PASSWORD=' {remote_base}/.env | cut -d= -f2")

print(f"\nKoneksi DB: {db_user}@{db_host}/{db_name}")

def query(sql):
    cmd = f'mysql -h {db_host} -u {db_user} -p"{db_pass}" {db_name} -se "{sql}" 2>/dev/null'
    return ssh_exec(cmd)

print("\n" + "="*70)
print("  DATA GPS DARI DATABASE (trip_points)")
print("="*70)

# 1. Kondisi diam — ambil titik pertama dari trip manapun dengan has_fix=1
print("\n[1] Kondisi Diam (5 data pertama dengan has_fix=1, speed=0):")
hasil = query("SELECT tp.latitude, tp.longitude, tp.speed_kph, tp.accuracy_meters, tp.recorded_at FROM trip_points tp WHERE tp.has_fix=1 AND tp.speed_kph=0 AND tp.latitude IS NOT NULL LIMIT 5;")
print(hasil if hasil else "  (tidak ada data)")

# 2. Bergerak lurus — speed rendah-menengah
print("\n[2] Bergerak Lurus (speed 5-20 kph, has_fix=1):")
hasil = query("SELECT tp.latitude, tp.longitude, tp.speed_kph, tp.accuracy_meters, tp.recorded_at FROM trip_points tp WHERE tp.has_fix=1 AND tp.speed_kph BETWEEN 5 AND 20 LIMIT 5;")
print(hasil if hasil else "  (tidak ada data)")

# 3. Bergerak tikungan — cari perubahan arah (est_distance berubah)
print("\n[3] Bergerak Tikungan (speed 10-30 kph):")
hasil = query("SELECT tp.latitude, tp.longitude, tp.speed_kph, tp.accuracy_meters, tp.recorded_at FROM trip_points tp WHERE tp.has_fix=1 AND tp.speed_kph BETWEEN 10 AND 30 LIMIT 5;")
print(hasil if hasil else "  (tidak ada data)")

# 4. Area penghalang bangunan — has_fix=0 atau accuracy besar
print("\n[4] Area Penghalang / No Fix:")
hasil = query("SELECT tp.latitude, tp.longitude, tp.speed_kph, tp.accuracy_meters, tp.has_fix, tp.recorded_at FROM trip_points tp WHERE tp.has_fix=0 LIMIT 5;")
print(hasil if hasil else "  (tidak ada data — semua data sudah fix)")

# 5. Kecepatan tinggi (>40 kph)
print("\n[5] Kecepatan Tinggi (>40 kph, has_fix=1):")
hasil = query("SELECT tp.latitude, tp.longitude, tp.speed_kph, tp.accuracy_meters, tp.recorded_at FROM trip_points tp WHERE tp.has_fix=1 AND tp.speed_kph > 40 LIMIT 5;")
print(hasil if hasil else "  (tidak ada data kecepatan tinggi)")

# Ringkasan statistik
print("\n" + "="*70)
print("  STATISTIK UMUM GPS")
print("="*70)
print(query("""
    SELECT
        COUNT(*) as total_titik,
        SUM(has_fix) as total_fix,
        ROUND(AVG(accuracy_meters),2) as avg_accuracy_m,
        MAX(speed_kph) as max_speed_kph,
        MIN(speed_kph) as min_speed_kph
    FROM trip_points
    WHERE latitude IS NOT NULL
"""))

print("\n" + "="*70)
print("  CONTOH PERHITUNGAN SELISIH (Salin koordinat ke hitung_selisih_gps.py)")
print("="*70)
print("""
  Cara lanjut:
  1. Salin nilai latitude & longitude dari data di atas
  2. Buka Google Maps → klik kanan pada koordinat tersebut
     → pilih "What's here?" / "Ada apa di sini?" → catat koordinat referensi
  3. Masukkan keduanya ke hitung_selisih_gps.py
  4. Jalankan: python hitung_selisih_gps.py
""")

client.close()
