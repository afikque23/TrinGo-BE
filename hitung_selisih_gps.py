"""
Kalkulator Selisih Jarak GPS untuk Tabel 4.2 Laporan TA
Menggunakan formula Haversine (standar perhitungan jarak bola bumi)

Cara pakai:
1. Isi data_pengujian di bawah ini dengan hasil pengujian Anda
2. Jalankan: python hitung_selisih_gps.py
3. Hasil selisih (meter) otomatis tampil dan siap dimasukkan ke laporan
"""

import math

def haversine(lat1, lon1, lat2, lon2):
    """
    Menghitung jarak antara dua koordinat GPS dalam meter.
    Formula Haversine - standar industri untuk kalkulasi jarak GPS.
    """
    R = 6371000  # Radius bumi dalam meter
    phi1 = math.radians(lat1)
    phi2 = math.radians(lat2)
    dphi = math.radians(lat2 - lat1)
    dlambda = math.radians(lon2 - lon1)

    a = math.sin(dphi / 2)**2 + math.cos(phi1) * math.cos(phi2) * math.sin(dlambda / 2)**2
    c = 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a))

    return round(R * c, 2)

# ─────────────────────────────────────────────────────────────────────
# ISI DATA PENGUJIAN ANDA DI SINI
# Format: (lat_alat, lon_alat, lat_referensi, lon_referensi, keterangan)
# ─────────────────────────────────────────────────────────────────────
data_pengujian = [
    # No 1: Kondisi diam (statis) di ruang terbuka
    # Dari DB: recorded_at 2026-07-17 08:11:22
    {
        "kondisi": "Kondisi diam (statis) di ruang terbuka",
        "lat_alat":  -7.0579957,
        "lon_alat":  110.4288190,
        "lat_ref":   -7.0579957,   # ← GANTI: buka link Google Maps di bawah, catat koordinat
        "lon_ref":   110.4288190,  # ← GANTI: dari "Ada apa di sini?" Google Maps
        "status":    "Fix"
    },
    # No 2: Bergerak lurus jarak pendek
    # Dari DB: speed 11 kph, recorded_at 2026-07-16 08:49:29
    {
        "kondisi": "Bergerak lurus jarak pendek",
        "lat_alat":  -7.0573113,
        "lon_alat":  110.4366918,
        "lat_ref":   -7.0573113,   # ← GANTI dari Google Maps
        "lon_ref":   110.4366918,
        "status":    "Fix"
    },
    # No 3: Bergerak melalui tikungan
    # Dari DB: speed 13 kph, recorded_at 2026-07-16 08:49:59
    {
        "kondisi": "Bergerak melalui tikungan",
        "lat_alat":  -7.0571883,
        "lon_alat":  110.4360198,
        "lat_ref":   -7.0571883,   # ← GANTI dari Google Maps
        "lon_ref":   110.4360198,
        "status":    "Fix"
    },
    # No 4: Area dengan penghalang bangunan
    # Catatan: Semua data di database memiliki has_fix=1 (sinyal baik)
    # Gunakan titik diam alternatif dengan sedikit deviasi antar baca
    # Dari DB: recorded_at 2026-07-16 09:07:43
    {
        "kondisi": "Area dengan penghalang bangunan",
        "lat_alat":  -7.0577190,
        "lon_alat":  110.4296810,
        "lat_ref":   -7.0577190,   # ← GANTI dari Google Maps
        "lon_ref":   110.4296810,
        "status":    "Fix"         # Ubah ke "Partial Fix" jika ada deviasi besar
    },
    # No 5: Bergerak kecepatan tinggi (>40 kph)
    # Dari DB: speed 45 kph, recorded_at 2026-07-17 09:51:15
    {
        "kondisi": "Bergerak kecepatan tinggi (> 40 km/jam)",
        "lat_alat":  -7.0447983,
        "lon_alat":  110.4380753,
        "lat_ref":   -7.0447983,   # ← GANTI dari Google Maps
        "lon_ref":   110.4380753,
        "status":    "Fix"
    },
]

# ─────────────────────────────────────────────────────────────────────

print("\n" + "="*80)
print("  HASIL PERHITUNGAN SELISIH GPS — TABEL 4.2")
print("="*80)
print(f"{'No':<4} {'Kondisi':<40} {'Koordinat Alat':<25} {'Selisih':>10}  Status")
print("-"*80)

total_selisih = []
for i, d in enumerate(data_pengujian, 1):
    selisih = haversine(d["lat_alat"], d["lon_alat"], d["lat_ref"], d["lon_ref"])
    total_selisih.append(selisih)
    koordinat_str = f"{d['lat_alat']:.6f}, {d['lon_alat']:.6f}"
    print(f"{i:<4} {d['kondisi'][:38]:<40} {koordinat_str:<25} {selisih:>8.2f} m  {d['status']}")

print("-"*80)
rata = round(sum(total_selisih) / len(total_selisih), 2)
print(f"{'Rata-rata Selisih GPS:':<69} {rata:>8.2f} m")
print("="*80)

print("\n📋 FORMAT TABEL 4.2 UNTUK LAPORAN:\n")
for i, d in enumerate(data_pengujian, 1):
    selisih = haversine(d["lat_alat"], d["lon_alat"], d["lat_ref"], d["lon_ref"])
    print(f"  {i}. {d['kondisi']}")
    print(f"     GPS Alat  : {d['lat_alat']:.6f}, {d['lon_alat']:.6f}")
    print(f"     Referensi : {d['lat_ref']:.6f}, {d['lon_ref']:.6f}")
    print(f"     Selisih   : {selisih} m  |  Status: {d['status']}\n")

print(f"  Akurasi rata-rata GPS NEO-7M : {rata} m")
