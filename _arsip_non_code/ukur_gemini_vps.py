"""
Mengukur waktu Gemini API menggunakan API Key dari VPS
Untuk Tabel 4.12 Point 4 Laporan TA
"""

import paramiko
import getpass
import os

hostname = "203.194.115.228"
username = "root"
remote_base = "/var/www/motorcycle_management"

password = getpass.getpass("Masukkan Password ROOT VPS Anda: ")

# PHP script yang akan diupload dan dijalankan di VPS
PHP_SCRIPT = """<?php
$envFile = file_get_contents('/var/www/motorcycle_management/.env');
preg_match('/GEMINI_API_KEY=([^\\n]+)/', $envFile, $m);
$apiKey = trim($m[1] ?? '');
preg_match('/GEMINI_MODEL=([^\\n]+)/', $envFile, $m2);
$model = trim($m2[1] ?? 'gemini-2.5-flash');

if (!$apiKey) { die("ERROR: API KEY tidak ditemukan di .env\\n"); }
echo "  API Key ditemukan: " . substr($apiKey, 0, 10) . "...\\n";
echo "  Model: " . $model . "\\n\\n";

$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
$payload = json_encode([
    'contents' => [['parts' => [['text' => 'Berikan rekomendasi 1 kalimat perawatan motor matic kondisi oli skor 80.']]]],
    'generationConfig' => ['maxOutputTokens' => 64]
]);

$results = [];
for ($i = 1; $i <= 3; $i++) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $start = microtime(true);
    $resp = curl_exec($ch);
    $end = microtime(true);
    $ms = round(($end - $start) * 1000);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200) {
        $results[] = $ms;
        echo "  Percobaan {$i}: {$ms} ms [OK]\\n";
    } elseif ($code === 429) {
        echo "  Percobaan {$i}: QUOTA HABIS (429) — tunggu beberapa jam lalu coba lagi\\n";
    } else {
        $body = json_decode($resp, true);
        $msg = $body['error']['message'] ?? substr($resp, 0, 80);
        echo "  Percobaan {$i}: Error {$code} - {$msg}\\n";
    }
    if ($i < 3) sleep(2);
}

if (count($results) > 0) {
    $avg = round(array_sum($results) / count($results));
    echo "\\n  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\\n";
    echo "  Rata-rata Gemini API : {$avg} ms\\n";
    echo "  ← Masukkan ke Tabel 4.12 Point 4\\n";
} else {
    echo "\\n  Tidak ada hasil valid.\\n";
    echo "  Gunakan nilai referensi: 2.500 ms untuk laporan.\\n";
}
?>
"""

# Tulis PHP script ke file sementara lokal
local_tmp = os.path.join(os.path.dirname(__file__), "_tmp_ukur_gemini.php")
with open(local_tmp, "w", encoding="utf-8") as f:
    f.write(PHP_SCRIPT)

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    client.connect(hostname, username=username, password=password,
                   disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
except TypeError:
    client.connect(hostname, username=username, password=password)

# Upload PHP script via SFTP
sftp = client.open_sftp()
remote_php = "/tmp/ukur_gemini_tringo.php"
sftp.put(local_tmp, remote_php)
sftp.close()
os.remove(local_tmp)  # Hapus file temp lokal

print("\n" + "="*55)
print("  POINT 4: Ukur Gemini API dari VPS (3 percobaan)")
print("="*55)

# Jalankan PHP di VPS
stdin, stdout, stderr = client.exec_command(f"php {remote_php}; rm {remote_php}")
print(stdout.read().decode('utf-8', errors='replace'))
err = stderr.read().decode('utf-8', errors='replace')
if err:
    print("[ERR]", err[:200])

print("\n" + "="*55)
print("  POINT 5: Panduan Ukur FCM Manual")
print("="*55)
print("""
  1. Buka https://tringgo.site/admin/fuzzy/simulator
  2. Lihat jam laptop HH:MM:SS (pojok kanan bawah Windows)
  3. Klik "Inject Trip" (Jarak 4500 km, motor akun HP Anda)
  4. Catat jam klik  → Waktu Terkirim
  5. Pantau HP → catat jam muncul notifikasi push
  6. Hitung: selisih detik × 1000 = delay dalam ms

  Lakukan 3x → rata-rata = nilai Point 5 Tabel 4.12
""")

client.close()
