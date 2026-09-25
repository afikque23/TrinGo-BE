# Laporan Detail Konfigurasi Fuzzy Logic Motor Bebek / Manual
*Referensi berdasarkan Manual Book Sepeda Motor Honda Tipe Cub (Revo X / Revo Fit) dan Standar Servis Otomotif.*

> [!NOTE]
> - **[a]** = Batas Awal (Mulai grafik naik/turun)
> - **[b]** = Puncak (Nilai keanggotaan tertinggi = 1.0)
> - **[c]** = Batas Akhir. *Khusus untuk inputan **HIGH**, gunakan angka **999** (atau angka sangat besar lainnya) pada kolom `[c]`.*

---

## 1. Variabel Global: Intensitas Berkendara
*Hanya dinyalakan (Aktif) untuk komponen: Oli Mesin, Filter Udara, dan Rantai.*

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

## 3. Filter Udara
**Variabel Input Aktif:** Jarak Tempuh, Intensitas Berkendara *(Durasi Hari OFF)*
* **Threshold Jarak:** Warn = 12.000 km, Critical = 16.000 km

### Membership Function Jarak Tempuh
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 7000 |
| **MEDIUM** | 5000 | 12000 | 16000 |
| **HIGH** | 12000 | 16000 | 99999 |

---

## 4. Rantai Roda
**Variabel Input Aktif:** Jarak Tempuh, Intensitas Berkendara *(Durasi Hari OFF)*
* **Threshold Jarak:** Warn = 15.000 km, Critical = 20.000 km

### Membership Function Jarak Tempuh
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 8000 |
| **MEDIUM** | 5000 | 15000 | 20000 |
| **HIGH** | 15000 | 20000 | 99999 |

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

## 6. Kampas Kopling
**Variabel Input Aktif:** Jarak Tempuh *(Durasi Hari OFF, Intensitas OFF)*
* **Threshold Jarak:** Warn = 20.000 km, Critical = 24.000 km

### Membership Function Jarak Tempuh
| Himpunan | [a] Batas Awal | [b] Puncak | [c] Batas Akhir |
| :--- | :--- | :--- | :--- |
| **LOW** | 0 | 0 | 12000 |
| **MEDIUM** | 8000 | 20000 | 24000 |
| **HIGH** | 20000 | 24000 | 99999 |

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

## Konfigurasi Aturan (Rule Base) Beserta Bobot (Weight)

Berikut adalah panduan kombinasi matriks Rule Base beserta bobot kepercayaannya (*Confidence Weight*). 

### Kelompok A: Aturan 2 Variabel (Contoh: Jarak Tempuh `AND` Durasi Hari)
*(Digunakan untuk **Busi**).*

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

### Kelompok B: Aturan Khusus Intensitas (Oli Mesin, Filter Udara, Rantai)
*Untuk komponen yang rentan terhadap siksaan pemakaian berat/terus-menerus.* 

| Kombinasi Logika (Kondisi IF) | Output (THEN) | Bobot | Alasan Umum |
| :--- | :--- | :--- | :--- |
| Jarak = `high` **AND** Intensitas = `high` | **Kritis** | **1.0** | Mutlak wajib ganti / aus berat. |
| Jarak = `medium` **AND** Intensitas = `high` | **Kritis** | **0.9** | Intensitas berat membuat rantai/oli aus lebih dini meski kilometer belum maksimal. |
| Jarak = `low` **AND** Intensitas = `high` | **Perlu Servis** | **0.7** | Pemakaian ekstrem terdeteksi. |
| Waktu = `high` **AND** Intensitas = `high` | **Kritis** | **1.0** | *(Hanya Oli Mesin)* Kadaluwarsa. |
| Jarak = `medium` **AND** Intensitas = `medium` | **Perlu Servis** | **1.0** | Pemakaian normal, batas peringatan normal. |

---

### Kelompok C: Aturan 1 Variabel Tunggal (Kampas Kopling, Ban, Kampas Rem, Aki)
*(Jika hanya 1 variabel yang aktif di panel).*

| Kombinasi Logika (Kondisi IF) | Output (THEN) | Bobot |
| :--- | :--- | :--- |
| Jarak/Waktu = `high` | **Kritis** (Bad) | **1.0** |
| Jarak/Waktu = `medium` | **Perlu Servis** (Fair) | **1.0** |
| Jarak/Waktu = `low` | **Baik** (Good) | **1.0** |
