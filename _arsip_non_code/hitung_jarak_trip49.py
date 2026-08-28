"""
Ambil koordinat trip_points untuk trip 28, 35, 39
Hitung jarak referensi dari jalur GPS (Haversine kumulatif)
Bandingkan dengan distance_meters sistem
"""
import paramiko
import getpass
import math

hostname = "203.194.115.228"
username = "root"

def haversine(lat1, lon1, lat2, lon2):
    R = 6371000
    phi1, phi2 = math.radians(lat1), math.radians(lat2)
    dphi = math.radians(lat2 - lat1)
    dlambda = math.radians(lon2 - lon1)
    a = math.sin(dphi/2)**2 + math.cos(phi1)*math.cos(phi2)*math.sin(dlambda/2)**2
    return R * 2 * math.atan2(math.sqrt(a), math.sqrt(1-a))

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

TARGET_TRIPS = [
    {"id": 28, "label": "Pagi  (09:07) — Trip 28", "kondisi": "Jalan perumahan"},
    {"id": 35, "label": "Pagi  (09:45) — Trip 35", "kondisi": "Jalan raya"},
    {"id": 39, "label": "Siang (16:27) — Trip 39", "kondisi": "Jalan campuran"},
]

print("\n" + "="*70)
print("  ANALISIS JARAK PERJALANAN — TABEL 4.9")
print("="*70)

results = []
for trip in TARGET_TRIPS:
    tid = trip["id"]

    # Ambil info trip
    info = q(f"SELECT distance_meters, duration_minutes, avg_speed_kph, start_at FROM trips WHERE id={tid}")
    parts = info.split("\t") if info else []
    dist_sistem_m = int(parts[0]) if len(parts) > 0 and parts[0].isdigit() else 0
    durasi = parts[1] if len(parts) > 1 else "?"
    speed = parts[2] if len(parts) > 2 else "?"
    start_at = parts[3] if len(parts) > 3 else "?"

    # Ambil semua titik GPS trip ini (berurutan)
    raw = q(f"SELECT latitude, longitude FROM trip_points WHERE trip_id={tid} AND has_fix=1 AND latitude IS NOT NULL ORDER BY sequence ASC")
    
    titik = []
    if raw:
        for baris in raw.split("\n"):
            kol = baris.split("\t")
            if len(kol) == 2:
                try:
                    titik.append((float(kol[0]), float(kol[1])))
                except:
                    pass

    # Hitung jarak kumulatif dari semua titik GPS
    dist_gps_m = 0.0
    for i in range(1, len(titik)):
        dist_gps_m += haversine(titik[i-1][0], titik[i-1][1], titik[i][0], titik[i][1])

    dist_sistem_km = dist_sistem_m / 1000
    dist_gps_km    = round(dist_gps_m / 1000, 3)
    
    if dist_gps_km > 0:
        selisih_pct = round(abs(dist_sistem_km - dist_gps_km) / dist_gps_km * 100, 2)
    else:
        selisih_pct = 0.0

    # Koordinat start dan end untuk link Google Maps
    start_coord = titik[0]  if titik else None
    end_coord   = titik[-1] if titik else None

    gmaps_link = ""
    if start_coord and end_coord:
        gmaps_link = (f"https://maps.google.com/maps/dir/"
                      f"{start_coord[0]},{start_coord[1]}/"
                      f"{end_coord[0]},{end_coord[1]}")

    results.append({
        "trip_id":       tid,
        "label":         trip["label"],
        "kondisi":       trip["kondisi"],
        "start_at":      start_at,
        "jumlah_titik":  len(titik),
        "dist_sistem_km": round(dist_sistem_km, 3),
        "dist_gps_km":   dist_gps_km,
        "selisih_pct":   selisih_pct,
        "durasi":        durasi,
        "speed":         speed,
        "gmaps_link":    gmaps_link,
    })

    print(f"\n  ── {trip['label']} ──")
    print(f"  Mulai           : {start_at}")
    print(f"  Jumlah titik GPS: {len(titik)} titik")
    print(f"  Jarak Sistem    : {round(dist_sistem_km, 3)} km  ({dist_sistem_m} m)")
    print(f"  Jarak GPS Path  : {dist_gps_km} km  (referensi Haversine kumulatif)")
    print(f"  Selisih         : {selisih_pct} %")
    print(f"  Durasi          : {durasi} menit | Speed rata-rata: {speed} kph")
    if gmaps_link:
        print(f"  Google Maps     : {gmaps_link}")

print("\n\n" + "="*70)
print("  TABEL 4.9 — SIAP MASUK LAPORAN")
print("="*70)
print(f"{'No':<4} {'Waktu':<20} {'Sistem (km)':<15} {'Referensi (km)':<16} {'Selisih (%)':<14} {'Keterangan'}")
print("-"*70)
for i, r in enumerate(results, 1):
    print(f"{i:<4} {r['label']:<20} {r['dist_sistem_km']:<15} {r['dist_gps_km']:<16} {r['selisih_pct']:<14} {r['kondisi']}")

print("\n  *Referensi = jarak kumulatif Haversine dari seluruh titik GPS trip_points")

client.close()
