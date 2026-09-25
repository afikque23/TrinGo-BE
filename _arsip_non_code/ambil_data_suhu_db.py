"""
Ambil data Suhu Mesin DS18B20 nyata dari database VPS untuk Subbab 4.2.3 Laporan TA
Menggunakan Base64 encoding agar terbebas dari masalah bash variable expansion.
"""
import paramiko
import getpass
import sys
import base64

hostname = "203.194.115.228"
username = "root"
remote_base = "/var/www/motorcycle_management"

print("=" * 70)
print("  PENGAMBILAN DATA SUHU DS18B20 DARI DATABASE VPS (TRINGGO)")
print("=" * 70)

if len(sys.argv) > 1:
    password = sys.argv[1]
else:
    password = getpass.getpass("Masukkan Password ROOT VPS Anda: ")

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    client.connect(hostname, username=username, password=password,
                   disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    print("\n✅ Berhasil terhubung ke VPS!")
except Exception as e:
    print(f"\n❌ Gagal terhubung ke VPS: {e}")
    sys.exit(1)

def run_tinker(php_code):
    b64 = base64.b64encode(php_code.encode('utf-8')).decode('utf-8')
    cmd = f"php {remote_base}/artisan tinker --execute=\"eval(base64_decode('{b64}'));\""
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    if err and "warning" not in err.lower() and "deprecated" not in err.lower():
        print(f"[ERR] {err}")
    return out

# 1. Ringkasan Statistik
print("\n" + "=" * 70)
print(" [1] RINGKASAN STATISTIK SUHU MESIN (Tabel trip_points)")
print("=" * 70)
php_stats = """
$total = \\App\\Models\\TripPoint::count();
$valid = \\App\\Models\\TripPoint::whereNotNull('engine_temp_c')->count();
$min = \\App\\Models\\TripPoint::whereNotNull('engine_temp_c')->min('engine_temp_c');
$max = \\App\\Models\\TripPoint::whereNotNull('engine_temp_c')->max('engine_temp_c');
$avg = round(\\App\\Models\\TripPoint::whereNotNull('engine_temp_c')->avg('engine_temp_c'), 2);
echo json_encode([
    'Total_Titik_Trip' => $total,
    'Titik_Ada_Suhu' => $valid,
    'Suhu_Min' => $min !== null ? $min . ' °C' : 'NULL',
    'Suhu_Max' => $max !== null ? $max . ' °C' : 'NULL',
    'Suhu_Rata2' => $avg !== null ? $avg . ' °C' : 'NULL'
], JSON_PRETTY_PRINT);
"""
print(run_tinker(php_stats))

# 2. Contoh Titik Data Aktual
print("\n" + "=" * 70)
print(" [2] CONTOH DATA TITIK TELEMETRI SUHU (15 Sampel Aktual)")
print("=" * 70)
php_points = """
$pts = \\App\\Models\\TripPoint::whereNotNull('engine_temp_c')
    ->orderBy('id', 'asc')
    ->take(15)
    ->get(['id', 'trip_id', 'sequence', 'speed_kph', 'engine_temp_c', 'recorded_at'])
    ->toArray();
echo json_encode($pts, JSON_PRETTY_PRINT);
"""
print(run_tinker(php_points))

# 3. Rekap Suhu pada Sesi Perjalanan (Tabel trips)
print("\n" + "=" * 70)
print(" [3] REKAP SUHU PER SESI PERJALANAN (Tabel trips)")
print("=" * 70)
php_trips = """
$trps = \\App\\Models\\Trip::whereNotNull('avg_temperature_c')
    ->orWhereNotNull('min_temperature_c')
    ->orWhereNotNull('max_temperature_c')
    ->orderBy('id', 'desc')
    ->take(10)
    ->get(['id', 'vehicle_id', 'start_at', 'duration_minutes', 'distance_meters', 'avg_speed_kph', 'min_temperature_c', 'max_temperature_c', 'avg_temperature_c'])
    ->toArray();
echo json_encode($trps, JSON_PRETTY_PRINT);
"""
print(run_tinker(php_trips))

# 4. Status Terakhir Kendaraan (Tabel vehicles)
print("\n" + "=" * 70)
print(" [4] STATUS SUHU TERAKHIR KENDARAAN (Tabel vehicles)")
print("=" * 70)
php_veh = """
$vehs = \\App\\Models\\Vehicle::whereNotNull('last_engine_temp_c')
    ->get(['id', 'title', 'device_id', 'last_engine_temp_c', 'last_engine_overheat', 'last_engine_temp_at'])
    ->toArray();
echo json_encode($vehs, JSON_PRETTY_PRINT);
"""
print(run_tinker(php_veh))

client.close()
print("\n" + "=" * 70)
print("  SELESAI. Silakan copy hasil di atas ke chat jika ingin saya masukkan ke Word!")
print("=" * 70)
