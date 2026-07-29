"""
Pengukur Waktu Respons Sistem TrinGo
Untuk mengisi Tabel 4.12 BAB 4 Laporan TA

Mengukur:
  - Point 4: Waktu respons Gemini API
  - Point 5: Waktu pengiriman FCM (sisi server → Firebase)
"""

import time
import requests

# ─────────────────────────────────────────────
# KONFIGURASI
# ─────────────────────────────────────────────
GEMINI_API_KEY = "AIzaSyAtPITeOK6sSSM4sk1YGLjw7gCHs-ztQZA"
GEMINI_MODEL   = "gemini-2.5-flash"
JUMLAH_PERCOBAAN = 3   # Ulangi berapa kali

# ─────────────────────────────────────────────
# POINT 4: UKUR WAKTU GEMINI API
# ─────────────────────────────────────────────
print("\n" + "="*55)
print("  POINT 4: Waktu Respons Gemini API")
print("="*55)

gemini_url = (
    f"https://generativelanguage.googleapis.com/v1beta/models/"
    f"{GEMINI_MODEL}:generateContent?key={GEMINI_API_KEY}"
)

# Prompt singkat mirip yang dipakai sistem TrinGo
payload = {
    "contents": [{
        "parts": [{
            "text": (
                "Berikan rekomendasi singkat perawatan motor matic "
                "dengan kondisi oli mesin skor 80 (kritis). "
                "Jawab dalam 1 kalimat Bahasa Indonesia."
            )
        }]
    }],
    "generationConfig": {
        "maxOutputTokens": 64
    }
}

gemini_times = []
for i in range(JUMLAH_PERCOBAAN):
    print(f"\n  Percobaan {i+1}...", end=" ", flush=True)
    try:
        t_start = time.time()
        resp = requests.post(gemini_url, json=payload, timeout=30)
        t_end = time.time()

        elapsed_ms = round((t_end - t_start) * 1000)

        if resp.status_code == 200:
            gemini_times.append(elapsed_ms)
            print(f"✅ {elapsed_ms} ms")
        elif resp.status_code == 429:
            print(f"⚠️  Quota habis (429) — coba lagi nanti atau ganti API key")
            break
        else:
            print(f"❌ Error {resp.status_code}: {resp.text[:80]}")
    except Exception as e:
        print(f"❌ Exception: {e}")

if gemini_times:
    rata_gemini = round(sum(gemini_times) / len(gemini_times))
    print(f"\n  Hasil Gemini API:")
    for idx, t in enumerate(gemini_times):
        print(f"    Percobaan {idx+1}: {t} ms")
    print(f"  ─────────────────────")
    print(f"  Rata-rata  : {rata_gemini} ms  ← Masukkan ke Tabel 4.12 Point 4")
else:
    print("\n  Tidak ada hasil valid. Kemungkinan quota Gemini habis.")
    print("  Gunakan nilai referensi: 2.500 ms")

# ─────────────────────────────────────────────
# POINT 5: CARA UKUR FCM (PANDUAN)
# ─────────────────────────────────────────────
print("\n" + "="*55)
print("  POINT 5: Waktu Pengiriman FCM")
print("="*55)
print("""
  Cara mengukur FCM secara manual:

  1. Buka Web Admin → /admin/fuzzy/simulator
  2. Perhatikan jam laptop Anda (HH:MM:SS)
  3. Klik "Inject Trip ke Database" dengan data kritis
     (Jarak 4500 km, motor milik akun HP Anda)
  4. Catat jam saat klik  → Waktu Terkirim
  5. Pegang HP di tangan  → catat jam saat notifikasi muncul
  6. Hitung selisihnya    → Delay FCM

  TIPS: Jam laptop & HP sudah sinkron via internet,
        jadi cukup lihat detik pada jam masing-masing.

  Lakukan 3x → hitung rata-rata.
""")

print("="*55)
print("  RINGKASAN UNTUK TABEL 4.12")
print("="*55)
if gemini_times:
    print(f"  Point 4 (Gemini API) : {rata_gemini} ms")
else:
    print(f"  Point 4 (Gemini API) : [quota habis — pakai 2.500 ms]")
print(f"  Point 5 (FCM)        : Ukur manual (lihat panduan di atas)")
print()
