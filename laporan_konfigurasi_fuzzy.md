# Laporan Detail Konfigurasi Fuzzy Logic Motor Matic
*Dokumen ini dapat digunakan sebagai referensi untuk pengisian form konfigurasi pada Admin Panel TringGo maupun lampiran pada Laporan Tugas Akhir.*

> [!NOTE]
> - **[a]** = Batas Awal (Mulai grafik naik/turun)
> - **[b]** = Puncak (Nilai keanggotaan tertinggi = 1.0)
> - **[c]** = Batas Akhir. *Khusus untuk inputan **HIGH**, gunakan angka **999** (atau angka sangat besar lainnya) pada kolom `[c]`.*

---

## 1. Variabel Global: Intensitas Berkendara
*Hanya dinyalakan (Aktif) untuk komponen: Oli Mesin, Oli Gardan, dan Filter Udara.*

| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 20 |
| **MEDIUM** | 15 | 30 | 50 |
| **HIGH** | 40 | 60 | 999 |

---

## 2. Oli Mesin
**Variabel Input Aktif:** Jarak Tempuh, Durasi Hari, Intensitas Berkendara
* **Threshold Jarak:** Warn = 2.500 km, Critical = 4.000 km
* **Threshold Waktu:** Warn = 90 hari, Critical = 120 hari

### Membership Function Jarak Tempuh
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 1500 |
| **MEDIUM** | 1000 | 2500 | 4000 |
| **HIGH** | 2500 | 4000 | 9999 |

### Membership Function Durasi Hari
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 60 |
| **MEDIUM** | 45 | 90 | 120 |
| **HIGH** | 90 | 120 | 999 |

---

## 3. Oli Gardan (Final Drive)
**Variabel Input Aktif:** Jarak Tempuh, Durasi Hari, Intensitas Berkendara
* **Threshold Jarak:** Warn = 6.000 km, Critical = 8.000 km
* **Threshold Waktu:** Warn = 540 hari, Critical = 730 hari

### Membership Function Jarak Tempuh
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 3500 |
| **MEDIUM** | 2500 | 6000 | 8000 |
| **HIGH** | 6000 | 8000 | 9999 |

### Membership Function Durasi Hari
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 300 |
| **MEDIUM** | 250 | 540 | 730 |
| **HIGH** | 540 | 730 | 999 |

---

## 4. Filter Udara
**Variabel Input Aktif:** Jarak Tempuh, Intensitas Berkendara *(Durasi Hari OFF)*
* **Threshold Jarak:** Warn = 12.000 km, Critical = 16.000 km

### Membership Function Jarak Tempuh
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 7000 |
| **MEDIUM** | 5000 | 12000 | 16000 |
| **HIGH** | 12000 | 16000 | 99999 |

---

## 5. Busi
**Variabel Input Aktif:** Jarak Tempuh, Durasi Hari *(Intensitas OFF)*
* **Threshold Jarak:** Warn = 6.000 km, Critical = 8.000 km
* **Threshold Waktu:** Warn = 180 hari, Critical = 240 hari

### Membership Function Jarak Tempuh
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 3500 |
| **MEDIUM** | 2500 | 6000 | 8000 |
| **HIGH** | 6000 | 8000 | 9999 |

### Membership Function Durasi Hari
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 100 |
| **MEDIUM** | 80 | 180 | 240 |
| **HIGH** | 180 | 240 | 999 |

---

## 6. CVT / Belt & Roller CVT
*(Konfigurasi untuk komponen Belt dan Roller disamakan berdasarkan interval bongkar blok CVT)*
**Variabel Input Aktif:** Jarak Tempuh, Durasi Hari *(Intensitas OFF)*
* **Threshold Jarak:** Warn = 20.000 km, Critical = 24.000 km
* **Threshold Waktu:** Warn = 540 hari, Critical = 720 hari

### Membership Function Jarak Tempuh
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 12000 |
| **MEDIUM** | 8000 | 20000 | 24000 |
| **HIGH** | 20000 | 24000 | 99999 |

### Membership Function Durasi Hari
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 300 |
| **MEDIUM** | 250 | 540 | 720 |
| **HIGH** | 540 | 720 | 999 |

---

## 7. Rem (Kampas Rem)
**Variabel Input Aktif:** Jarak Tempuh *(Durasi Hari OFF, Intensitas OFF)*
* **Threshold Jarak:** Warn = 8.000 km, Critical = 12.000 km

### Membership Function Jarak Tempuh
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 5000 |
| **MEDIUM** | 3000 | 8000 | 12000 |
| **HIGH** | 8000 | 12000 | 99999 |

---

## 8. Ban
**Variabel Input Aktif:** Jarak Tempuh *(Durasi Hari OFF, Intensitas OFF)*
* **Threshold Jarak:** Warn = 12.000 km, Critical = 15.000 km

### Membership Function Jarak Tempuh
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 7000 |
| **MEDIUM** | 5000 | 12000 | 15000 |
| **HIGH** | 12000 | 15000 | 99999 |

---

## 9. Aki (Baterai)
**Variabel Input Aktif:** Durasi Hari *(Jarak Tempuh OFF, Intensitas OFF)*
* **Threshold Waktu:** Warn = 540 hari, Critical = 730 hari

### Membership Function Durasi Hari
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 300 |
| **MEDIUM** | 250 | 540 | 730 |
| **HIGH** | 540 | 730 | 999 |

---

## 10. Cairan Pendingin (Radiator Coolant)
**Variabel Input Aktif:** Jarak Tempuh, Durasi Hari *(Intensitas OFF)*
* **Threshold Jarak:** Warn = 24.000 km, Critical = 36.000 km
* **Threshold Waktu:** Warn = 730 hari, Critical = 1.095 hari

### Membership Function Jarak Tempuh
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 14000 |
| **MEDIUM** | 10000 | 24000 | 36000 |
| **HIGH** | 24000 | 36000 | 99999 |

### Membership Function Durasi Hari
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 400 |
| **MEDIUM** | 300 | 730 | 1095 |
| **HIGH** | 730 | 1095 | 9999 |

---

## Konfigurasi Aturan (Rule Base) Beserta Bobot (Weight)

Berikut adalah panduan kombinasi matriks Rule Base beserta bobot kepercayaannya (*Confidence Weight*). 
- Bobot **1.0** berarti kepastian mutlak (sangat berisiko jika dilanggar). 
- Bobot **0.7 - 0.9** berarti kondisi yang perlu perhatian tapi belum sekritis bobot mutlak.

### Kelompok A: Aturan 2 Variabel (Contoh: Jarak Tempuh `AND` Durasi Hari)
*(Digunakan untuk Busi, CVT/Belt, Roller CVT, dan Cairan Pendingin).*

| No | Kombinasi Logika (Kondisi IF) | Output (THEN) | Bobot |
| :--- | :--- | :--- | :--- |
| 1 | Jarak = `high` **AND** Waktu = `high` | **Kritis** (Bad) | **1.0** |
| 2 | Jarak = `high` **AND** Waktu = `medium` | **Kritis** (Bad) | **0.9** |
| 3 | Jarak = `high` **AND** Waktu = `low` | **Kritis** (Bad) | **0.8** |
| 4 | Jarak = `medium` **AND** Waktu = `high` | **Kritis** (Bad) | **0.9** |
| 5 | Jarak = `medium` **AND** Waktu = `medium` | **Perlu Servis** (Fair) | **1.0** |
| 6 | Jarak = `medium` **AND** Waktu = `low` | **Perlu Servis** (Fair) | **0.8** |
| 7 | Jarak = `low` **AND** Waktu = `high` | **Kritis** (Bad) | **0.8** |
| 8 | Jarak = `low` **AND** Waktu = `medium` | **Perlu Servis** (Fair) | **0.8** |
| 9 | Jarak = `low` **AND** Waktu = `low` | **Baik** (Good) | **1.0** |

---

### Kelompok B: Aturan Khusus Intensitas (Contoh: Oli Mesin, Oli Gardan, Filter Udara)
*Untuk komponen yang terpengaruh pemakaian ekstrem (Ojol), kita menambahkan aturan (Rules) yang melibatkan Intensitas Berkendara.* 

| Kombinasi Logika (Kondisi IF) | Output (THEN) | Bobot | Alasan |
| :--- | :--- | :--- | :--- |
| Jarak = `high` **AND** Intensitas = `high` | **Kritis** | **1.0** | Sudah waktunya ganti + sering disiksa. Sangat Kritis. |
| Jarak = `medium` **AND** Intensitas = `high` | **Kritis** | **0.9** | Meskipun jarak belum maksimal, intensitas pemakaian yang berat memaksa komponen aus lebih cepat. |
| Jarak = `low` **AND** Intensitas = `high` | **Perlu Servis** | **0.7** | Baru diganti tapi langsung dipakai ngojek tiap hari, perlu bersiap-siap. |
| Waktu = `high` **AND** Intensitas = `high` | **Kritis** | **1.0** | Oli sudah berumur lama di dalam mesin motor yang sering dipakai. |
| Jarak = `medium` **AND** Intensitas = `medium` | **Perlu Servis** | **1.0** | Pemakaian normal, batas peringatan normal. |

*(Anda dapat mengombinasikan rule intensitas ini ke dalam daftar rule komponen di Admin Panel untuk memberikan "Kecerdasan Buatan" yang mendeteksi Ojol).*

---

### Kelompok C: Aturan 1 Variabel Tunggal (Contoh: Ban, Kampas Rem, Aki)
*(Jika hanya 1 variabel yang aktif di panel, konfigurasinya paling sederhana).*

| Kombinasi Logika (Kondisi IF) | Output (THEN) | Bobot |
| :--- | :--- | :--- |
| Jarak/Waktu = `high` | **Kritis** (Bad) | **1.0** |
| Jarak/Waktu = `medium` | **Perlu Servis** (Fair) | **1.0** |
| Jarak/Waktu = `low` | **Baik** (Good) | **1.0** |
