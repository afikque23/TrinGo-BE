
# -*- coding: utf-8 -*-
"""
Script revisi jurnal JTET - Format standar IEEE/JTET 2024
- Margin: Top/Bottom/Left 2.5cm, Right 2.0cm
- Font: Times New Roman
- Title: 16pt Bold Center
- Body: 10pt, single spacing, justified
- Tables: Open style (no vertical lines, no gray), caption ABOVE in 8pt Roman numerals
- Figures: Caption BELOW in 8pt Arabic numerals
- References: IEEE, 8pt, hanging indent
- White table background
"""

from docx import Document
from docx.shared import Pt, Cm, RGBColor, Inches
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_LINE_SPACING
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml.ns import qn
from docx.oxml import OxmlElement
import os

IOT_IMAGE_PATH = r"C:\Users\ASUS\.gemini\antigravity-ide\brain\d13587f7-5f70-4369-84db-95f5bc45fe2d\iot_device_tringo_1787899850516.jpg"

# ─────────────────────────────────────────────
# HELPER FUNCTIONS
# ─────────────────────────────────────────────

def font(run, name='Times New Roman', size=10, bold=False, italic=False):
    run.font.name = name
    run.font.size = Pt(size)
    run.font.bold = bold
    run.font.italic = italic

def fmt(para, align=WD_ALIGN_PARAGRAPH.JUSTIFY,
        sb=0, sa=0, ls=12, fli=None, li=None):
    """Set paragraph format with exact line spacing."""
    pf = para.paragraph_format
    pf.alignment = align
    pf.space_before = Pt(sb)
    pf.space_after = Pt(sa)
    pf.line_spacing_rule = WD_LINE_SPACING.EXACTLY
    pf.line_spacing = Pt(ls)
    if fli is not None:
        pf.first_line_indent = Cm(fli)
    if li is not None:
        pf.left_indent = Cm(li)

def body_para(doc, text, indent=True):
    p = doc.add_paragraph()
    r = p.add_run(text)
    font(r, size=10)
    fmt(p, sb=0, sa=0, ls=12, fli=0.65 if indent else 0)
    return p

def h1(doc, text):
    """Heading Level 1: Roman numeral, ALL CAPS, Bold, Center"""
    p = doc.add_paragraph()
    r = p.add_run(text)
    font(r, size=10, bold=True)
    fmt(p, align=WD_ALIGN_PARAGRAPH.CENTER, sb=6, sa=3, ls=12)
    return p

def h2(doc, text):
    """Heading Level 2: Letter + Bold Italic, Left"""
    p = doc.add_paragraph()
    r = p.add_run(text)
    font(r, size=10, bold=True, italic=True)
    fmt(p, align=WD_ALIGN_PARAGRAPH.LEFT, sb=4, sa=2, ls=12)
    return p

def h3(doc, text):
    """Heading Level 3: Arabic number + Italic, Left"""
    p = doc.add_paragraph()
    r = p.add_run(text)
    font(r, size=10, italic=True)
    fmt(p, align=WD_ALIGN_PARAGRAPH.LEFT, sb=3, sa=1, ls=12)
    return p

def table_caption(doc, roman_num, title):
    """Table caption ABOVE: 8pt, Center, ALL CAPS"""
    p = doc.add_paragraph()
    r = p.add_run(f"TABEL {roman_num}\n{title.upper()}")
    font(r, size=8)
    fmt(p, align=WD_ALIGN_PARAGRAPH.CENTER, sb=6, sa=2, ls=10)
    return p

def fig_caption(doc, num, title):
    """Figure caption BELOW: 8pt, Center"""
    p = doc.add_paragraph()
    r = p.add_run(f"Gambar {num}. {title}")
    font(r, size=8)
    fmt(p, align=WD_ALIGN_PARAGRAPH.CENTER, sb=2, sa=4, ls=10)
    return p

def open_table(doc, headers, rows, col_widths=None):
    """
    Create open-style academic table:
    - Top border (thick)
    - Header bottom border (thin)
    - Bottom border (thick)
    - NO vertical lines
    - NO gray shading (white background)
    """
    tbl = doc.add_table(rows=len(rows)+1, cols=len(headers))
    tbl.alignment = WD_TABLE_ALIGNMENT.CENTER

    # Set table-level borders: top thick, bottom thick, no verticals, no insideH
    tbl_xml = tbl._tbl
    tblPr = tbl_xml.find(qn('w:tblPr'))
    if tblPr is None:
        tblPr = OxmlElement('w:tblPr')
        tbl_xml.insert(0, tblPr)
    # Remove existing tblBorders if present
    for existing in tblPr.findall(qn('w:tblBorders')):
        tblPr.remove(existing)
    tbl_borders = OxmlElement('w:tblBorders')
    border_defs = {
        'top':     ('single', '12', '000000'),
        'left':    ('none',   '0',  'auto'),
        'bottom':  ('single', '12', '000000'),
        'right':   ('none',   '0',  'auto'),
        'insideH': ('none',   '0',  'auto'),
        'insideV': ('none',   '0',  'auto'),
    }
    for side, (val, sz, color) in border_defs.items():
        b = OxmlElement(f'w:{side}')
        b.set(qn('w:val'), val)
        b.set(qn('w:sz'), sz)
        b.set(qn('w:color'), color)
        tbl_borders.append(b)
    tblPr.append(tbl_borders)

    # Header row
    hdr_row = tbl.rows[0]
    for i, h in enumerate(headers):
        cell = hdr_row.cells[i]
        # White background
        _set_cell_bg(cell, 'FFFFFF')
        # Bottom border for header (thin rule)
        _set_cell_bottom_border(cell, '6')
        p = cell.paragraphs[0]
        r = p.add_run(h)
        font(r, size=9, bold=True)
        fmt(p, align=WD_ALIGN_PARAGRAPH.CENTER, sb=2, sa=2, ls=10)

    # Data rows
    for r_idx, row_data in enumerate(rows):
        row = tbl.rows[r_idx+1]
        is_last = (r_idx == len(rows)-1)
        for c_idx, val in enumerate(row_data):
            cell = row.cells[c_idx]
            _set_cell_bg(cell, 'FFFFFF')
            if is_last:
                _set_cell_bottom_border(cell, '12')  # thick bottom on last row
            p = cell.paragraphs[0]
            r = p.add_run(str(val))
            font(r, size=9)
            a = WD_ALIGN_PARAGRAPH.LEFT if c_idx == 0 else WD_ALIGN_PARAGRAPH.CENTER
            fmt(p, align=a, sb=1, sa=1, ls=10)

    return tbl

def _set_cell_bg(cell, hex_color):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    for existing in tcPr.findall(qn('w:shd')):
        tcPr.remove(existing)
    shd = OxmlElement('w:shd')
    shd.set(qn('w:val'), 'clear')
    shd.set(qn('w:color'), 'auto')
    shd.set(qn('w:fill'), hex_color)
    tcPr.append(shd)

def _set_cell_bottom_border(cell, sz='6'):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    for existing in tcPr.findall(qn('w:tcBorders')):
        tcPr.remove(existing)
    tcBdr = OxmlElement('w:tcBorders')
    for side in ['top', 'left', 'bottom', 'right', 'insideH', 'insideV']:
        b = OxmlElement(f'w:{side}')
        if side == 'bottom':
            b.set(qn('w:val'), 'single')
            b.set(qn('w:sz'), sz)
            b.set(qn('w:color'), '000000')
        else:
            b.set(qn('w:val'), 'none')
            b.set(qn('w:sz'), '0')
            b.set(qn('w:color'), 'auto')
        tcBdr.append(b)
    tcPr.append(tcBdr)

def add_eq(doc, eq_text, num):
    """Add equation paragraph"""
    p = doc.add_paragraph()
    # Left part: equation text, right part: number
    r1 = p.add_run(f"    {eq_text}")
    font(r1, size=10, italic=True)
    # Tab stop for number at right
    r2 = p.add_run(f"\t({num})")
    font(r2, size=10)
    pf = p.paragraph_format
    pf.alignment = WD_ALIGN_PARAGRAPH.LEFT
    pf.space_before = Pt(3)
    pf.space_after = Pt(3)
    pf.line_spacing_rule = WD_LINE_SPACING.EXACTLY
    pf.line_spacing = Pt(12)
    return p

def spacer(doc, pts=3):
    p = doc.add_paragraph()
    fmt(p, sb=0, sa=0, ls=pts)

def divider_line(doc):
    p = doc.add_paragraph()
    pPr = p._p.get_or_add_pPr()
    pBdr = OxmlElement('w:pBdr')
    bot = OxmlElement('w:bottom')
    bot.set(qn('w:val'), 'single'); bot.set(qn('w:sz'), '4')
    bot.set(qn('w:space'), '1'); bot.set(qn('w:color'), '000000')
    pBdr.append(bot); pPr.append(pBdr)
    fmt(p, sb=0, sa=0, ls=4)

# ─────────────────────────────────────────────
# BUILD DOCUMENT
# ─────────────────────────────────────────────
doc = Document()

# PAGE SETUP
sec = doc.sections[0]
sec.page_width  = Cm(21.0)
sec.page_height = Cm(29.7)
sec.top_margin    = Cm(2.5)
sec.bottom_margin = Cm(2.5)
sec.left_margin   = Cm(2.5)
sec.right_margin  = Cm(2.0)

# ═══════════════════════════════════════════
# HEADER JURNAL (1 column)
# ═══════════════════════════════════════════
p = doc.add_paragraph()
r = p.add_run("JURNAL TEKNIK ELEKTRO TERAPAN (JTET)")
font(r, size=11, bold=True)
fmt(p, align=WD_ALIGN_PARAGRAPH.CENTER, sb=0, sa=1, ls=14)

p = doc.add_paragraph()
r = p.add_run("Politeknik Negeri Semarang | Vol. XX, No. X, Agustus 2026 | e-ISSN: XXXX-XXXX")
font(r, size=8)
fmt(p, align=WD_ALIGN_PARAGRAPH.CENTER, sb=0, sa=4, ls=10)

divider_line(doc)

# ═══════════════════════════════════════════
# JUDUL (1 column) -- 16pt Bold Center Title Case
# ═══════════════════════════════════════════
p = doc.add_paragraph()
r = p.add_run(
    "Rancang Bangun Sistem Monitoring Perawatan dan Rekomendasi\n"
    "pada Sepeda Motor Menggunakan Teknologi IoT Berbasis Mobile"
)
font(r, size=16, bold=True)
fmt(p, align=WD_ALIGN_PARAGRAPH.CENTER, sb=8, sa=4, ls=20)

# ─ Penulis 11pt
p = doc.add_paragraph()
r = p.add_run("Aji Saka, Arya Yusufa Agnil Fikri")
font(r, size=11)
fmt(p, align=WD_ALIGN_PARAGRAPH.CENTER, sb=0, sa=2, ls=14)

# ─ Afiliasi 9pt
p = doc.add_paragraph()
r = p.add_run(
    "Program Studi D-III Teknik Informatika, Jurusan Teknik Elektro\n"
    "Politeknik Negeri Semarang, Jl. Prof. H. Sudarto, S.H., Tembalang, Semarang 50275\n"
    "ajisaka@student.polines.ac.id | arya.yusufa@student.polines.ac.id"
)
font(r, size=9)
fmt(p, align=WD_ALIGN_PARAGRAPH.CENTER, sb=0, sa=2, ls=11)

p = doc.add_paragraph()
r = p.add_run("Pembimbing: Dr. Sukamto, S.Kom., M.T. dan Wiktasari, S.T., M.Kom.")
font(r, size=9, italic=True)
fmt(p, align=WD_ALIGN_PARAGRAPH.CENTER, sb=0, sa=6, ls=11)

divider_line(doc)

# ═══════════════════════════════════════════
# ABSTRAK (1 column, 9pt italic body)
# ═══════════════════════════════════════════
p = doc.add_paragraph()
r_lbl = p.add_run("Abstrak")
font(r_lbl, size=9, bold=True, italic=True)
r_dash = p.add_run(" — ")
font(r_dash, size=9, italic=True)
r_txt = p.add_run(
    "Sepeda motor merupakan salah satu moda transportasi utama bagi masyarakat Indonesia, dengan jumlah lebih "
    "dari 139 juta unit terdaftar. Tingginya frekuensi pemakaian harian belum diimbangi dengan kedisiplinan "
    "pemilik dalam melakukan perawatan berkala. Penelitian ini merancang dan membangun TrinGo, sistem monitoring "
    "dan rekomendasi perawatan sepeda motor terpadu berbasis Internet of Things (IoT) yang terintegrasi dengan "
    "aplikasi mobile Android. Perangkat IoT terdiri atas mikrokontroler ESP32, modul GPS NEO-7M, sensor inersia "
    "MPU6050, dan sensor suhu DS18B20 yang memantau data penggunaan kendaraan secara otomatis melalui protokol "
    "MQTT ke server backend Laravel dan basis data MySQL. Data diolah menggunakan Logika Fuzzy Mamdani dengan "
    "tiga variabel input untuk menghasilkan health score komponen, dan narasi rekomendasi dihasilkan via "
    "Google Gemini API. Hasil pengujian menunjukkan GPS NEO-7M mencapai akurasi rata-rata 2,48 m; mesin "
    "inferensi Fuzzy Mamdani konsisten 100% terhadap perhitungan manual; dan UAT terhadap 10 responden "
    "menghasilkan nilai rata-rata 4,16/5 (kategori Baik). Total waktu respons end-to-end tercatat 3,6 detik."
)
font(r_txt, size=9, italic=True)
fmt(p, align=WD_ALIGN_PARAGRAPH.JUSTIFY, sb=4, sa=2, ls=11)

p = doc.add_paragraph()
r_k = p.add_run("Kata Kunci")
font(r_k, size=9, bold=True, italic=True)
r_kd = p.add_run(" — ")
font(r_kd, size=9, italic=True)
r_kv = p.add_run("Internet of Things; Logika Fuzzy Mamdani; Monitoring Kendaraan; ESP32; GPS NEO-7M; Flutter; Gemini API.")
font(r_kv, size=9, italic=True)
fmt(p, align=WD_ALIGN_PARAGRAPH.JUSTIFY, sb=0, sa=2, ls=11)

p = doc.add_paragraph()
r_lbl2 = p.add_run("Abstract")
font(r_lbl2, size=9, bold=True, italic=True)
r_d2 = p.add_run(" — ")
font(r_d2, size=9, italic=True)
r_en = p.add_run(
    "Motorcycles are one of the primary modes of transportation in Indonesia, with more than 139 million "
    "registered units. This study designs and builds TrinGo, an integrated IoT-based motorcycle maintenance "
    "monitoring and recommendation system integrated with an Android mobile application. The IoT hardware "
    "consists of an ESP32 microcontroller, NEO-7M GPS module, MPU6050 inertial sensor, and DS18B20 "
    "temperature sensor that automatically monitors vehicle usage data via the MQTT protocol. Collected data "
    "is processed using Mamdani Fuzzy Logic to generate component health scores, and maintenance narratives "
    "are generated via Google Gemini API. Test results show GPS NEO-7M achieves 2.48 m average accuracy; "
    "the Fuzzy Mamdani engine is 100% consistent with manual calculations; and UAT with 10 respondents "
    "yields an average score of 4.16/5 (Good). Total end-to-end response time is 3.6 seconds."
)
font(r_en, size=9, italic=True)
fmt(p, align=WD_ALIGN_PARAGRAPH.JUSTIFY, sb=0, sa=2, ls=11)

p = doc.add_paragraph()
r_kw2 = p.add_run("Keywords")
font(r_kw2, size=9, bold=True, italic=True)
r_kd2 = p.add_run(" — ")
font(r_kd2, size=9, italic=True)
r_kv2 = p.add_run("Internet of Things; Mamdani Fuzzy Logic; Vehicle Monitoring; ESP32; GPS NEO-7M; Flutter; Gemini API.")
font(r_kv2, size=9, italic=True)
fmt(p, align=WD_ALIGN_PARAGRAPH.JUSTIFY, sb=0, sa=6, ls=11)

divider_line(doc)

# ═══════════════════════════════════════════
# SECTION BREAK → 2 KOLOM (body article)
# ═══════════════════════════════════════════
new_sec = doc.add_section(1)  # CONTINUOUS
new_sec.top_margin    = Cm(2.5)
new_sec.bottom_margin = Cm(2.5)
new_sec.left_margin   = Cm(2.5)
new_sec.right_margin  = Cm(2.0)
new_sec.page_width    = Cm(21.0)
new_sec.page_height   = Cm(29.7)

spr = new_sec._sectPr
for ec in spr.findall(qn('w:cols')): spr.remove(ec)
cols = OxmlElement('w:cols')
cols.set(qn('w:num'), '2')
cols.set(qn('w:space'), '567')   # 1.0 cm = 567 twips ≈ 0.5-0.6cm range
cols.set(qn('w:equalWidth'), '1')
spr.append(cols)

# ═══════════════════════════════════════════
# I. PENDAHULUAN
# ═══════════════════════════════════════════
h1(doc, "I. PENDAHULUAN")

body_para(doc,
    "Sepeda motor merupakan sarana mobilitas utama bagi masyarakat Indonesia. Berdasarkan data "
    "Badan Pusat Statistik tahun 2023, jumlah sepeda motor terdaftar di Indonesia telah melampaui "
    "139 juta unit [1]. Namun, tingginya pemakaian harian jarang diimbangi kedisiplinan pemilik "
    "dalam melakukan perawatan berkala. Sebagian besar pengendara masih berpatokan pada ingatan "
    "atau catatan manual yang rentan terlewat, sehingga kerap mengabaikan penggantian pelumas "
    "maupun pengecekan suku cadang vital [2]."
)
body_para(doc,
    "Panduan servis resmi Honda menetapkan jadwal inspeksi berjenjang mulai dari 1.000 km pertama, "
    "kemudian setiap kelipatan 4.000 km [3]. Keterlambatan servis tidak hanya menurunkan performa "
    "mesin, tetapi juga meningkatkan risiko kerusakan mekanis mendadak yang berbahaya bagi "
    "keselamatan berkendara."
)
body_para(doc,
    "Penelitian terdahulu telah mengeksplorasi sistem pelacak GPS [4], pengingat servis berbasis "
    "SMS Gateway [5], dan monitoring kondisi komponen parsial [6]. Namun, solusi tersebut masih "
    "bekerja secara terpisah dan membebankan input data manual kepada pengguna. Belum ada solusi "
    "yang mengintegrasikan akuisisi data IoT otomatis dengan rekomendasi perawatan cerdas dalam "
    "satu ekosistem aplikasi mobile yang utuh."
)
body_para(doc,
    "Penelitian ini mengembangkan TrinGo, sistem monitoring dan rekomendasi perawatan sepeda motor "
    "terpadu berbasis IoT yang mengintegrasikan: (1) perangkat IoT untuk akuisisi data penggunaan "
    "kendaraan secara otomatis via MQTT; (2) Logika Fuzzy Mamdani untuk menilai kondisi komponen "
    "secara adaptif per tipe motor; (3) Google Gemini API untuk menghasilkan narasi rekomendasi "
    "berbahasa natural; dan (4) aplikasi mobile Flutter Android sebagai antarmuka pengguna."
)

# ═══════════════════════════════════════════
# II. TINJAUAN PUSTAKA
# ═══════════════════════════════════════════
h1(doc, "II. TINJAUAN PUSTAKA")

h2(doc, "A. Penelitian Terdahulu")
body_para(doc,
    "Pamungkas dan Yahya [7] mengembangkan sistem IoT pemantau voltase aki kendaraan, namun "
    "terbatas pada satu komponen listrik statis tanpa rekomendasi perawatan holistik. Nugroho [4] "
    "mengintegrasikan GPS pada NodeMCU untuk pelacakan keamanan motor, tetapi tidak memproses log "
    "perjalanan menjadi rekomendasi perawatan. Pramudita [5] mengembangkan pengingat servis via "
    "SMS Gateway yang masih memerlukan input manual dari pengguna."
)
body_para(doc,
    "Wicaksono [8] menerapkan Logika Fuzzy sederhana pada Arduino untuk monitoring suhu radiator "
    "motor, namun belum menggunakan inferensi Mamdani multi-variabel. Pratiwi [9] membangun "
    "monitoring IoT berbasis Flutter-MQTT untuk sensor lingkungan, membuktikan sistem mampu "
    "mengirim notifikasi real-time, namun tanpa algoritma cerdas untuk perawatan kendaraan. "
    "Rahmawati dan Susilo [10] membuktikan MQTT lebih efisien dibandingkan HTTP pada sistem IoT."
)

h2(doc, "B. Logika Fuzzy Mamdani")
body_para(doc,
    "Logika fuzzy menangani informasi tidak pasti dengan derajat keanggotaan pada rentang [0,1], "
    "menyerupai cara berpikir manusia [11]. Metode Mamdani [12] dipilih karena basis aturannya "
    "intuitif dan dapat dibangun dari pengetahuan pakar. Proses inferensi terdiri dari tiga tahap: "
    "fuzzifikasi (konversi crisp → derajat keanggotaan), evaluasi rule base (MIN-MAX), dan "
    "defuzzifikasi Centroid untuk menghasilkan nilai crisp output."
)

# ═══════════════════════════════════════════
# III. METODE PENELITIAN
# ═══════════════════════════════════════════
h1(doc, "III. METODE PENELITIAN")

h2(doc, "A. Arsitektur Sistem")
body_para(doc,
    "Sistem TrinGo dibangun pada arsitektur tiga lapisan: (1) Layer IoT — ESP32 mengakuisisi data "
    "dari GPS NEO-7M, MPU6050, dan DS18B20, lalu mengirimkan payload JSON via MQTT setiap 5 detik; "
    "(2) Layer Backend — Laravel memproses telemetri, menjalankan inferensi Fuzzy Mamdani, dan "
    "menyediakan RESTful API; dan (3) Layer Mobile — aplikasi Flutter Android sebagai antarmuka "
    "pengguna dengan push notification via Firebase Cloud Messaging (FCM)."
)

# ── TABLE I: Komponen Hardware
table_caption(doc, "I", "Spesifikasi Komponen Perangkat Keras IoT TrinGo")
open_table(doc,
    ["Komponen", "Spesifikasi", "Fungsi"],
    [
        ["ESP32",       "Dual-core 240 MHz, WiFi+BT, 4MB Flash",         "Mikrokontroler utama, MQTT publisher"],
        ["GPS NEO-7M",  "56 kanal, UART 9600 bps, NMEA output",          "Akuisisi koordinat dan kecepatan"],
        ["MPU6050",     "IMU 6-DoF, I2C 400kHz, ±16g akselerometer",     "Deteksi gerak, kalkulasi G-force"],
        ["DS18B20",     "-55~+125°C, akurasi ±0,5°C, 1-Wire",           "Monitoring suhu kerja mesin"],
        ["PCB Custom",  "Single layer, soket modular 2.54mm",             "Interkoneksi dan proteksi komponen"],
    ]
)
spacer(doc, 4)

h2(doc, "B. Perangkat IoT")
body_para(doc,
    "Perangkat IoT TrinGo dirakit pada PCB custom dengan komponen modular. ESP32 berperan sebagai "
    "pusat kendali yang mengintegrasikan seluruh sensor. Firmware diprogram menggunakan PlatformIO "
    "(C++) dengan mekanisme non-blocking millis() untuk pengiriman data setiap 5 detik. Ketika "
    "sinyal GPS tidak tersedia (blank spot), sistem beralih ke mode Dead Reckoning berbasis "
    "komputasi akselerometer MPU6050. Realisasi fisik perangkat IoT TrinGo ditunjukkan pada Gambar 1."
)

# ── Gambar 1: IoT Device
if os.path.exists(IOT_IMAGE_PATH):
    p_img = doc.add_paragraph()
    p_img.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run_img = p_img.add_run()
    run_img.add_picture(IOT_IMAGE_PATH, width=Cm(7.5))
    fmt(p_img, align=WD_ALIGN_PARAGRAPH.CENTER, sb=4, sa=0, ls=12)
else:
    body_para(doc, "[Gambar 1 -- Foto perangkat IoT TrinGo tidak ditemukan]")

fig_caption(doc, "1", "Realisasi fisik perangkat IoT TrinGo (ESP32 + GPS NEO-7M + MPU6050 + DS18B20 pada PCB custom).")

h2(doc, "C. Perancangan Sistem Fuzzy Mamdani")
body_para(doc,
    "Sistem Fuzzy Mamdani TrinGo menggunakan tiga variabel input: X₁ (Jarak Tempuh, 0--9.999 km), "
    "X₂ (Durasi Hari, 0--999 hari), dan X₃ (Intensitas Berkendara, 0--99,9 km/hari). Setiap variabel "
    "dipetakan ke tiga himpunan linguistik (Rendah, Sedang, Tinggi) menggunakan fungsi keanggotaan "
    "bahu (shoulder) dan segitiga (triangular). Sistem mendukung tiga tipe motor: Matic, "
    "Manual/Bebek, dan Sport, dengan parameter yang dikonfigurasi berdasarkan buku pedoman "
    "pemilik resmi Honda [3]."
)
body_para(doc,
    "Defuzzifikasi menggunakan metode Centroid (Center of Gravity) sebagaimana Persamaan (1):"
)
add_eq(doc, "z* = Σ(μ(zᵢ) × zᵢ) / Σμ(zᵢ)", "1")
body_para(doc,
    "di mana z* adalah health score crisp (0--100), μ(zᵢ) adalah derajat keanggotaan output, "
    "dan zᵢ adalah titik diskrit pada domain output. Skor dikategorikan: Baik (0--39), "
    "Perlu Servis (40--69), dan Kritis (70--100)."
)

h2(doc, "D. Metode Pengembangan")
body_para(doc,
    "Pengembangan menggunakan pendekatan hibrida: model Waterfall untuk perangkat lunak (backend "
    "Laravel dan aplikasi Flutter), dan metode Prototyping untuk modul perangkat keras IoT yang "
    "memerlukan evaluasi iteratif terhadap interaksi kelistrikan fisik."
)

# ─── TABLE II: Rancangan Pengujian
table_caption(doc, "II", "Rancangan Pengujian Sistem TrinGo")
open_table(doc,
    ["No", "Jenis Pengujian", "Metode"],
    [
        ["1", "Akurasi GPS NEO-7M",        "Perbandingan vs. koordinat Google Maps (Haversine)"],
        ["2", "Sensitivitas MPU6050",       "Pengukuran G-force kondisi diam vs. bergerak"],
        ["3", "Komunikasi MQTT",            "Pengukuran latensi dan validasi payload JSON"],
        ["4", "RESTful API Backend",        "Black-box: 12 endpoint, input valid/invalid"],
        ["5", "Akurasi Fuzzy Mamdani",      "Perbandingan output sistem vs. perhitungan manual"],
        ["6", "Integrasi Gemini API",       "Evaluasi relevansi narasi pada 10 skenario"],
        ["7", "Aplikasi Mobile",            "Black-box: 12 fitur utama skenario normal/edge case"],
        ["8", "Penerimaan Pengguna (UAT)",  "Kuesioner Likert 1--5, 31 butir, 10 responden"],
    ]
)
spacer(doc, 4)

# ═══════════════════════════════════════════
# IV. HASIL DAN PEMBAHASAN
# ═══════════════════════════════════════════
h1(doc, "IV. HASIL DAN PEMBAHASAN")

h2(doc, "A. Pengujian Perangkat IoT")
body_para(doc,
    "Perangkat IoT berhasil dirakit dan terhubung ke jaringan WiFi serta MQTT Broker dalam "
    "waktu rata-rata 8,3 detik sejak power-on. Hasil pengujian akurasi GPS NEO-7M pada lima "
    "kondisi disajikan pada Tabel III."
)

# ─── TABLE III: GPS
table_caption(doc, "III", "Hasil Pengujian Akurasi Modul GPS NEO-7M")
open_table(doc,
    ["Kondisi Pengujian", "Error (m)", "Satelit"],
    [
        ["Diam, langit terbuka",      "0,50",  "12"],
        ["Diam, semi-tertutup",       "3,20",  "7"],
        ["Bergerak < 20 km/jam",      "1,80",  "10"],
        ["Bergerak 20--40 km/jam",     "2,90",  "9"],
        ["Bergerak > 40 km/jam",      "3,90",  "8"],
        ["Rata-rata",                 "2,48",  "≈9,2"],
    ]
)
spacer(doc, 4)

body_para(doc,
    "GPS NEO-7M menghasilkan akurasi rata-rata 2,48 m — memadai untuk tracking perjalanan "
    "motor. Sensor MPU6050 berhasil membedakan status diam (0,994 g) dan bergerak "
    "(1,05--1,38 g normal, puncak 2,97 g saat pengereman keras). DS18B20 merekam suhu mesin "
    "28,5--61,25°C selama 15 menit sesi berkendara, konsisten dengan karakteristik mesin Honda BeAT."
)

h2(doc, "B. Pengujian Komunikasi MQTT dan Backend")
body_para(doc,
    "Transmisi data MQTT tercatat stabil dengan latensi 40--45 ms pada seluruh skenario, "
    "membuktikan efisiensi protokol MQTT untuk telemetri IoT berkala. Dari 12 endpoint "
    "RESTful API Laravel yang diuji, seluruhnya merespons sesuai spesifikasi, termasuk "
    "penolakan akses tanpa token (HTTP 401) yang memvalidasi keamanan berbasis Laravel Sanctum."
)

h2(doc, "C. Pengujian Sistem Fuzzy Mamdani")
body_para(doc,
    "Validasi FuzzyEngine dilakukan dengan membandingkan output sistem terhadap perhitungan "
    "manual pada tiga skenario uji (Tabel IV). Ketiga skenario menghasilkan kesesuaian 100%, "
    "membuktikan implementasi algoritma fuzzifikasi, evaluasi rule base, dan defuzzifikasi "
    "Centroid berjalan secara matematis benar."
)

# ─── TABLE IV: Fuzzy
table_caption(doc, "IV", "Hasil Validasi Sistem Inferensi Fuzzy Mamdani")
open_table(doc,
    ["Kategori", "X₁ (km)", "X₂ (hr)", "X₃ (km/hr)", "Skor Sistem", "Skor Manual", "Status"],
    [
        ["Baik",         "1.200", "30",  "40,0", "20", "20", "✓ Sesuai"],
        ["Perlu Servis", "3.200", "65",  "49,2", "60", "60", "✓ Sesuai"],
        ["Kritis",       "4.800", "130", "36,9", "90", "90", "✓ Sesuai"],
    ]
)
spacer(doc, 4)

h2(doc, "D. Pengujian Integrasi Gemini API")
body_para(doc,
    "Pengujian pada 10 skenario kondisi komponen menunjukkan 8 skenario (80%) menghasilkan "
    "narasi yang relevan dan informatif. Dua skenario dengan data input minimal menghasilkan "
    "narasi kurang spesifik, yang dapat diatasi dengan pengayaan konteks prompt pada versi "
    "pengembangan selanjutnya."
)

h2(doc, "E. Pengujian Penerimaan Pengguna (UAT)")
body_para(doc,
    "UAT dilakukan menggunakan kuesioner Skala Likert 1--5 (31 butir pernyataan) terhadap "
    "10 responden (usia 17--25 tahun, 50% laki-laki, 70% pengguna motor matic, 80% belum "
    "pernah menggunakan aplikasi monitoring kendaraan sejenis). Hasil rekapitulasi "
    "per seksi disajikan pada Tabel V."
)

# ─── TABLE V: UAT
table_caption(doc, "V", "Rekapitulasi Hasil UAT Per Seksi Penilaian")
open_table(doc,
    ["Seksi", "Aspek Evaluasi", "Mean", "Kategori"],
    [
        ["A", "Antarmuka & Kemudahan Penggunaan", "4,15", "Baik"],
        ["B", "Monitoring IoT & Tracking GPS",    "4,10", "Baik"],
        ["C", "Akurasi Fuzzy Mamdani",            "3,95", "Baik"],
        ["D", "Kualitas Narasi AI (Gemini)",      "4,20", "Baik"],
        ["E", "Notifikasi & Jadwal Servis",       "4,30", "Sangat Baik"],
        ["F", "Penilaian Umum & Kepuasan",        "4,24", "Sangat Baik"],
        ["",  "Rata-rata Keseluruhan",            "4,16", "Baik"],
    ]
)
spacer(doc, 4)

body_para(doc,
    "Rata-rata keseluruhan 4,16/5 (Baik) dengan persentase respons positif 74,8%. Seksi E "
    "(Notifikasi & Jadwal Servis) mendapat nilai tertinggi (4,30 — Sangat Baik), "
    "menunjukkan fitur pengingat servis otomatis merupakan fitur paling dirasakan manfaatnya. "
    "Nilai terendah pada butir C1 (3,80) mengindikasikan perlunya kalibrasi ulang parameter "
    "Fuzzy menggunakan data riwayat servis empiris yang lebih besar."
)

h2(doc, "F. Analisis Waktu Respons End-to-End")
body_para(doc,
    "Total waktu respons sistem end-to-end dari akuisisi sensor hingga notifikasi push "
    "diterima di aplikasi mobile tercatat 3.585 ms (≈3,6 detik). Rincian waktu respons "
    "ditunjukkan pada Tabel VI. Tahapan generasi narasi via Gemini API (layanan eksternal) "
    "menyumbang 83,7% dari total waktu respons, sedangkan layer IoT dan komputasi backend "
    "secara kumulatif hanya menyumbang kurang dari 1%."
)

# ─── TABLE VI: Response Time
table_caption(doc, "VI", "Rekapitulasi Waktu Respons Sistem End-to-End")
open_table(doc,
    ["Tahapan", "Komponen", "Waktu (ms)"],
    [
        ["Akuisisi Sensor",    "ESP32 + Semua Sensor",     "0,011"],
        ["Transmisi MQTT",     "ESP32 → MQTT Broker",      "4"],
        ["Proses Backend",     "Laravel + MySQL",           "~262"],
        ["Inferensi Fuzzy",    "FuzzyEngine (PHP)",         "3"],
        ["Generasi Narasi AI", "Gemini API (eksternal)",    "~3.000"],
        ["Push Notifikasi",    "Firebase FCM → Android",   "~316"],
        ["Total E2E",          "—",                        "3.585"],
    ]
)
spacer(doc, 4)

# ═══════════════════════════════════════════
# V. KESIMPULAN
# ═══════════════════════════════════════════
h1(doc, "V. KESIMPULAN")

body_para(doc,
    "Penelitian ini berhasil merancang dan membangun sistem TrinGo yang mengintegrasikan "
    "perangkat IoT (ESP32, GPS NEO-7M, MPU6050, DS18B20), Logika Fuzzy Mamdani, dan "
    "Generative AI (Gemini API) dalam satu ekosistem monitoring perawatan motor berbasis "
    "mobile. Kesimpulan yang dapat ditarik adalah sebagai berikut."
)
body_para(doc,
    "Pertama, perangkat IoT berhasil menyalurkan data telemetri secara real-time melalui "
    "protokol MQTT dengan latensi 40--45 ms, dan GPS NEO-7M mencapai akurasi rata-rata 2,48 m."
)
body_para(doc,
    "Kedua, mesin inferensi Fuzzy Mamdani menghasilkan health score komponen yang konsisten "
    "100% terhadap perhitungan manual pada tiga kategori kondisi (Baik, Perlu Servis, Kritis), "
    "mendukung tiga tipe motor secara adaptif."
)
body_para(doc,
    "Ketiga, integrasi Gemini API menghasilkan narasi rekomendasi perawatan berbahasa natural "
    "dengan akseptabilitas 80%, dan aplikasi mobile Flutter berhasil menampilkan seluruh 12 "
    "fitur monitoring dengan nilai UAT rata-rata 4,16/5 (kategori Baik)."
)
body_para(doc,
    "Saran pengembangan: penambahan modul GSM mandiri, integrasi catu daya dari aki kendaraan, "
    "kalibrasi ulang parameter Fuzzy dengan data empiris, dan perluasan ke platform iOS."
)

# ═══════════════════════════════════════════
# UCAPAN TERIMA KASIH
# ═══════════════════════════════════════════
h1(doc, "UCAPAN TERIMA KASIH")
body_para(doc,
    "Penulis mengucapkan terima kasih kepada Dr. Sukamto, S.Kom., M.T. selaku Dosen "
    "Pembimbing I dan Wiktasari, S.T., M.Kom. selaku Dosen Pembimbing II atas bimbingan "
    "selama pelaksanaan penelitian. Terima kasih juga kepada Program Studi D-III Teknik "
    "Informatika, Jurusan Teknik Elektro, Politeknik Negeri Semarang."
)

# ═══════════════════════════════════════════
# DAFTAR PUSTAKA
# ═══════════════════════════════════════════
h1(doc, "DAFTAR PUSTAKA")

refs = [
    '[1]  Badan Pusat Statistik, Perkembangan Jumlah Kendaraan Bermotor Menurut Jenis, BPS, Jakarta, 2023.',
    '[2]  PT Astra Honda Motor, Panduan Perawatan Berkala Sepeda Motor Honda, Jakarta, 2022.',
    '[3]  PT Astra Honda Motor, Buku Pemilik Honda BeAT Series, Jakarta, 2019.',
    '[4]  M. Nugroho, Sistem Pelacak GPS Kendaraan Berbasis NodeMCU ESP8266 dan Aplikasi Android, Jurnal Teknik Elektro Terapan, vol. 8, no. 1, hal. 23-30, 2022.',
    '[5]  R. Pramudita, Sistem Pengingat Jadwal Servis Motor Berbasis SMS Gateway dan Arduino, Jurnal Ilmiah Teknik Informatika, vol. 6, no. 3, hal. 78-85, 2022.',
    '[6]  D. Wicaksono, Sistem Monitoring Suhu Radiator Motor Menggunakan Logika Fuzzy, Jurnal Teknologi Informatika, vol. 7, no. 1, hal. 34-41, 2022.',
    '[7]  A. Pamungkas dan M. Yahya, Sistem IoT Pemantau Voltase dan Arus Aki Kendaraan, Jurnal Teknologi Informasi, vol. 12, no. 2, hal. 45-52, 2023.',
    '[8]  S. Kusumadewi dan H. Purnomo, Aplikasi Logika Fuzzy untuk Pendukung Keputusan, Edisi ke-2. Yogyakarta: Graha Ilmu, 2010.',
    '[9]  I. Pratiwi, Monitoring Telemetri IoT Berbasis Flutter dan MQTT dengan Notifikasi Real-Time, Jurnal Teknik Informatika, vol. 10, no. 3, hal. 156-163, 2023.',
    '[10] A. Rahmawati dan B. Susilo, Analisis Komparatif Protokol MQTT dan HTTP pada Sistem IoT, Jurnal Komputasi, vol. 5, no. 2, hal. 89-97, 2022.',
    '[11] L. A. Zadeh, Fuzzy Sets, Information and Control, vol. 8, no. 3, hal. 338-353, 1965.',
    '[12] E. H. Mamdani dan S. Assilian, An Experiment in Linguistic Synthesis with a Fuzzy Logic Controller, Int. J. Man-Machine Studies, vol. 7, no. 1, hal. 1-13, 1975.',
    '[13] OASIS, MQTT Version 3.1.1 OASIS Standard, 2014. [Online]. Tersedia: https://docs.oasis-open.org/mqtt/mqtt/v3.1.1/',
    '[14] T. Otwell, Laravel - The PHP Framework for Web Artisans, 2024. [Online]. Tersedia: https://laravel.com/docs',
    '[15] Google LLC, Flutter - Build apps for any screen, 2024. [Online]. Tersedia: https://flutter.dev/docs',
    '[16] Google DeepMind, Gemini API Documentation, 2024. [Online]. Tersedia: https://ai.google.dev/docs',
]

for ref in refs:
    p = doc.add_paragraph()
    r = p.add_run(ref)
    font(r, size=8)
    pf = p.paragraph_format
    pf.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    pf.space_before = Pt(1)
    pf.space_after  = Pt(1)
    pf.line_spacing_rule = WD_LINE_SPACING.EXACTLY
    pf.line_spacing = Pt(10)
    pf.left_indent       = Cm(0.7)
    pf.first_line_indent = Cm(-0.7)

# ─────────────────────────────────────────────
doc.save("Jurnal_JTET_Arya_dan_Aji.docx")
print("OK Jurnal berhasil disimpan: Jurnal_JTET_Arya_dan_Aji.docx")
import os
sz = os.path.getsize("Jurnal_JTET_Arya_dan_Aji.docx")/1024
from docx import Document as D2
d2 = D2("Jurnal_JTET_Arya_dan_Aji.docx")
wc = sum(len(p.text.split()) for p in d2.paragraphs)
print(f"   Ukuran file   : {sz:.1f} KB")
print(f"   Total kata    : {wc:,}")
print(f"   Tabel         : {len(d2.tables)}")
print(f"   Est. halaman  : ~{wc//380}--{wc//300} hal (2-kolom)")
