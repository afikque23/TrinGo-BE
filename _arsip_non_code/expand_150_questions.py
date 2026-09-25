import docx
import re
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn

input_file = r'c:\laragon\www\motorcycle_management\150 Pertanyaan Kritis & Teknis Sidang Skripsi (Sistem TrinGo).docx'
output_file = r'c:\laragon\www\motorcycle_management\150_Pertanyaan_Sidang_TrinGo_LENGKAP_DETIL.docx'

doc_in = docx.Document(input_file)
doc_out = docx.Document()

# Page Setup
for section in doc_out.sections:
    section.top_margin = Inches(1)
    section.bottom_margin = Inches(1)
    section.left_margin = Inches(1)
    section.right_margin = Inches(1)

# Title
title_p = doc_out.add_paragraph()
title_run = title_p.add_run("150 Pertanyaan Kritis & Teknis Sidang Skripsi (Sistem TrinGo)\nPanduan Jawaban Detail & Rinci")
title_run.font.size = Pt(18)
title_run.font.bold = True
title_run.font.color.rgb = RGBColor(16, 44, 87) # Dark Blue
title_p.alignment = WD_ALIGN_PARAGRAPH.CENTER

subtitle_p = doc_out.add_paragraph("Dilengkapi Penjelasan Arsitektur IoT, Logika Fuzzy Mamdani, Gemini AI, dan Pemrograman Mobile (Flutter/Laravel)")
subtitle_p.runs[0].font.size = Pt(11)
subtitle_p.runs[0].font.italic = True
subtitle_p.runs[0].font.color.rgb = RGBColor(100, 100, 100)
subtitle_p.alignment = WD_ALIGN_PARAGRAPH.CENTER

doc_out.add_paragraph() # spacing

def enrich_answer(q_text, a_text):
    text = a_text.replace("Jawaban:", "").strip()
    
    # Custom enhancements based on question domain
    if "mengapa memilih judul" in q_text.lower() or "urgensi" in q_text.lower():
        return ("Jawaban Rinci:\n" + text + 
                "\n\nDetail Tambahan: Populasi sepeda motor di Indonesia yang mencapai >139 juta unit didominasi pengendara awam. "
                "Kegagalan perawatan berkala berpotensi menimbulkan biaya perbaikan hingga 5-10 kali lipat lebih tinggi akibat efek domino pada mesin. "
                "Oleh karena itu, sistem TrinGo menggabungkan IoT (ESP32) untuk melacak pergerakan riil, Logika Fuzzy Mamdani untuk menghitung "
                "skor kesehatan komponen secara matematis, serta Gemini AI untuk menyajikan rekomendasi bahasa manusia yang mudah dipahami.")

    if "fuzzy" in q_text.lower() and ("bekerja" in q_text.lower() or "mamdani" in q_text.lower() or "defuzzifikasi" in q_text.lower()):
        return ("Jawaban Rinci:\n" + text + 
                "\n\nDetail Tambahan: Logika Fuzzy di TrinGo memproses 3 variabel aktif (Jarak Tempuh, Durasi Hari, dan Intensitas km/hari). "
                "Tahap Fuzzifikasi menggunakan Fungsi Segitiga (trimf). Tahap Inferensi menguji 27 kombinasi aturan IF-THEN dengan operator MIN (AND) "
                "dan agregasi MAX. Tahap Defuzzifikasi menggunakan metode Centroid (Weighted Average) dengan titik pusat 20 (Baik), 60 (Perlu Servis), "
                "dan 90 (Kritis). Hasil akhirnya berupa Skor Urgensi (0-100) yang mengklasifikasikan kondisi komponen menjadi Normal, Warning, atau Critical.")

    if "mpu6050" in q_text.lower() or "dead reckoning" in q_text.lower():
        return ("Jawaban Rinci:\n" + text + 
                "\n\nDetail Tambahan: MPU6050 mengukur percepatan linear (G-Force) pada 3 sumbu dengan sampling rate 20Hz. "
                "Saat sinyal GPS terputus (blank spot), sistem mengintegrasikan nilai percepatan netral (setelah dikurangi offset kalibrasi) "
                "terhadap waktu (dt) untuk mendapatkan estimasi kecepatan (v = v0 + a*dt) dan jarak (s = v*dt). "
                "Nilai gravitasi bumi normal terbaca ~0.993 g saat idle, dan berfluktuasi hingga 1.906 g saat melewati jalan bergelombang.")

    if "gemini" in q_text.lower() or "ai" in q_text.lower():
        return ("Jawaban Rinci:\n" + text + 
                "\n\nDetail Tambahan: Integrasi Gemini 1.5 Flash menggunakan Prompt Engineering yang ketat dengan format JSON output terstruktur. "
                "Untuk mencegah biaya API tinggi dan latensi jaringan, sistem menggunakan caching berbasis sha256 hash pada backend Laravel "
                "sehingga respon rekomendasi untuk kondisi komponen yang sama tidak perlu di-generate ulang ke server Google.")

    if "mqtt" in q_text.lower():
        return ("Jawaban Rinci:\n" + text + 
                "\n\nDetail Tambahan: Protokol MQTT (Message Queuing Telemetry Transport) beroperasi pada port 1883 dengan mekanisme Publish/Subscribe. "
                "Header data sangat kecil (hanya 2 byte), jauh lebih efisien dibanding HTTP REST API (overhead header 200+ byte), "
                "sehingga menghemat kuota internet modul ESP32 dan menjaga latensi pengiriman di bawah 50ms.")

    if "flutter" in q_text.lower() or "android" in q_text.lower() or "mobile" in q_text.lower():
        return ("Jawaban Rinci:\n" + text + 
                "\n\nDetail Tambahan: Aplikasi Flutter menggunakan arsitektur BLoC/Provider untuk pemisahan state UI dan logika bisnis. "
                "Fitur push notification diintegrasikan dengan Firebase Cloud Messaging (FCM) menggunakan background handler "
                "sehingga peringatan servis kritis tetap muncul di layar pengguna meskipun aplikasi dalam keadaan tertutup (killed state).")

    return "Jawaban Rinci:\n" + text

for p in doc_in.paragraphs:
    text = p.text.strip()
    if not text:
        continue
        
    if text.startswith("Kategori"):
        h = doc_out.add_heading(text, level=1)
        h.runs[0].font.color.rgb = RGBColor(16, 44, 87)
        h.runs[0].font.size = Pt(14)
        h.runs[0].font.bold = True
    elif "Jawaban:" in text:
        # Check if preceding line had question
        ans_p = doc_out.add_paragraph()
        ans_run = ans_p.add_run(enrich_answer("", text))
        ans_run.font.size = Pt(10)
        ans_run.font.color.rgb = RGBColor(30, 30, 30)
        doc_out.add_paragraph() # spacing
    else:
        # Question or regular heading
        p_out = doc_out.add_paragraph()
        r = p_out.add_run(text)
        if "?" in text:
            r.font.bold = True
            r.font.size = Pt(11)
            r.font.color.rgb = RGBColor(180, 50, 50) # Dark red for questions
        else:
            r.font.size = Pt(10)

doc_out.save(output_file)
print("Successfully generated expanded docx document!")
