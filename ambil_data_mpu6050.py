"""
Ambil data MPU6050 dari database VPS untuk Tabel 4.3 Laporan TA
Sumber: kolom mpu_g_force dan mpu_is_moving di tabel trip_points
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
print("  DATA MPU6050 — TABEL 4.3")
print("="*70)

# Cek ketersediaan data mpu
print("\n[INFO] Cek ketersediaan data MPU6050:")
print(q("""
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN mpu_g_force IS NOT NULL THEN 1 ELSE 0 END) as ada_gforce,
        SUM(CASE WHEN mpu_is_moving IS NOT NULL THEN 1 ELSE 0 END) as ada_ismoving,
        ROUND(MIN(mpu_g_force),4) as min_g,
        ROUND(MAX(mpu_g_force),4) as max_g,
        ROUND(AVG(mpu_g_force),4) as avg_g
    FROM trip_points
    WHERE mpu_g_force IS NOT NULL
"""))

# No 1: Motor diam, mesin mati — speed=0 & mpu_is_moving=0 dari trip awal
print("\n[1] Motor diam, mesin mati (speed=0, mpu_is_moving=0, 10 baris pertama):")
print(q("""
    SELECT
        tp.latitude, tp.speed_kph,
        ROUND(tp.mpu_g_force,4) as g_force,
        tp.mpu_is_moving,
        tp.recorded_at
    FROM trip_points tp
    JOIN trips t ON tp.trip_id = t.id
    WHERE tp.speed_kph = 0
      AND tp.mpu_is_moving = 0
      AND tp.mpu_g_force IS NOT NULL
    ORDER BY tp.recorded_at ASC
    LIMIT 10
"""))
print("Rata-rata g-force kondisi ini:")
print(q("""
    SELECT ROUND(AVG(mpu_g_force),4) as rata_g, COUNT(*) as n_sampel
    FROM trip_points
    WHERE speed_kph = 0 AND mpu_is_moving = 0 AND mpu_g_force IS NOT NULL
"""))

# No 2: Berjalan pelan, jalan rata — speed 5-20 kph
print("\n[2] Motor berjalan pelan, jalan rata (speed 5-20 kph):")
print(q("""
    SELECT speed_kph, ROUND(mpu_g_force,4) as g_force, mpu_is_moving, recorded_at
    FROM trip_points
    WHERE speed_kph BETWEEN 5 AND 20 AND mpu_g_force IS NOT NULL
    LIMIT 10
"""))
print("Rata-rata g-force kondisi ini:")
print(q("""
    SELECT ROUND(AVG(mpu_g_force),4) as rata_g, COUNT(*) as n_sampel
    FROM trip_points
    WHERE speed_kph BETWEEN 5 AND 20 AND mpu_g_force IS NOT NULL
"""))

# No 3: Berjalan, jalan bergelombang — g_force tinggi di kecepatan sedang
print("\n[3] Motor berjalan, jalan bergelombang (speed 10-40 kph, g_force tinggi):")
print(q("""
    SELECT speed_kph, ROUND(mpu_g_force,4) as g_force, mpu_is_moving, recorded_at
    FROM trip_points
    WHERE speed_kph BETWEEN 10 AND 40
      AND mpu_g_force IS NOT NULL
    ORDER BY mpu_g_force DESC
    LIMIT 10
"""))
print("Rata-rata g-force kondisi ini:")
print(q("""
    SELECT ROUND(AVG(mpu_g_force),4) as rata_g, COUNT(*) as n_sampel
    FROM trip_points
    WHERE speed_kph BETWEEN 10 AND 40 AND mpu_g_force IS NOT NULL
"""))

# No 4: Kecepatan tinggi > 40 kph
print("\n[4] Motor berjalan kecepatan tinggi (speed > 40 kph):")
print(q("""
    SELECT speed_kph, ROUND(mpu_g_force,4) as g_force, mpu_is_moving, recorded_at
    FROM trip_points
    WHERE speed_kph > 40 AND mpu_g_force IS NOT NULL
    LIMIT 10
"""))
print("Rata-rata g-force kondisi ini:")
print(q("""
    SELECT ROUND(AVG(mpu_g_force),4) as rata_g, COUNT(*) as n_sampel
    FROM trip_points
    WHERE speed_kph > 40 AND mpu_g_force IS NOT NULL
"""))

print("\n" + "="*70)
print("  STATISTIK LENGKAP (mpu_is_moving distribution)")
print("="*70)
print(q("""
    SELECT
        mpu_is_moving,
        COUNT(*) as jumlah,
        ROUND(AVG(mpu_g_force),4) as avg_g,
        ROUND(MIN(mpu_g_force),4) as min_g,
        ROUND(MAX(mpu_g_force),4) as max_g
    FROM trip_points
    WHERE mpu_g_force IS NOT NULL
    GROUP BY mpu_is_moving
"""))

client.close()
