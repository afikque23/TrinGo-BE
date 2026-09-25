"""
Ambil data Trip nyata dari database VPS untuk Tabel 4.9 Laporan TA
Versi fix: kolom start_at, end_at, distance_meters
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

print("\n" + "="*70)
print("  TRIP DENGAN DATA JARAK (distance_meters > 0)")
print("="*70)
print(q("""
    SELECT
        id,
        DATE_FORMAT(start_at, '%Y-%m-%d %H:%i') as waktu_mulai,
        CASE
            WHEN TIME(start_at) BETWEEN '05:00:00' AND '11:59:59' THEN 'PAGI'
            WHEN TIME(start_at) BETWEEN '12:00:00' AND '17:59:59' THEN 'SIANG'
            ELSE 'MALAM'
        END as waktu,
        ROUND(distance_meters / 1000, 2) as jarak_km,
        duration_minutes,
        avg_speed_kph,
        kondisi_lalu_lintas,
        status
    FROM trips
    WHERE distance_meters > 0
    ORDER BY start_at DESC
    LIMIT 20
"""))

print("\n" + "="*70)
print("  RINGKASAN PER WAKTU (trip dengan distance_meters > 0)")
print("="*70)
print(q("""
    SELECT
        CASE
            WHEN TIME(start_at) BETWEEN '05:00:00' AND '11:59:59' THEN 'Pagi (05-11)'
            WHEN TIME(start_at) BETWEEN '12:00:00' AND '17:59:59' THEN 'Siang (12-17)'
            ELSE 'Malam (18-23)'
        END as waktu,
        COUNT(*) as jumlah_trip,
        ROUND(AVG(distance_meters/1000), 2) as rata_jarak_km,
        ROUND(MIN(distance_meters/1000), 2) as min_km,
        ROUND(MAX(distance_meters/1000), 2) as max_km,
        ROUND(AVG(avg_speed_kph), 1) as rata_speed
    FROM trips
    WHERE distance_meters > 0
    GROUP BY waktu
"""))

print("\n" + "="*70)
print("  SEMUA TRIP (termasuk distance=0) — cek data lengkap")
print("="*70)
print(q("""
    SELECT
        id,
        DATE_FORMAT(start_at, '%Y-%m-%d %H:%i') as mulai,
        CASE
            WHEN TIME(start_at) BETWEEN '05:00:00' AND '11:59:59' THEN 'PAGI'
            WHEN TIME(start_at) BETWEEN '12:00:00' AND '17:59:59' THEN 'SIANG'
            ELSE 'MALAM'
        END as waktu,
        distance_meters,
        ROUND(distance_meters/1000, 2) as km,
        duration_minutes as menit,
        avg_speed_kph,
        status
    FROM trips
    WHERE deleted_at IS NULL
    ORDER BY start_at DESC
    LIMIT 20
"""))

client.close()
