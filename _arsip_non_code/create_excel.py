import openpyxl
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from openpyxl.utils import get_column_letter

wb = openpyxl.Workbook()

# Sheet 1: Dynamic Formula Calculator
ws = wb.active
ws.title = 'Kalkulator Rumus Fuzzy'

# Title
ws['A1'] = 'KALKULATOR & PERHITUNGAN MANUAL FUZZY MAMDANI (SISTEM TRINGO)'
ws['A1'].font = Font(name='Calibri', size=14, bold=True, color='1F4E78')
ws['A2'] = 'File ini berisi RUMUS LIVE EXCEL (=SUMPRODUCT, =IF, dll). Silakan klik sel K12, K13, K14 untuk melihat rumusnya!'
ws['A2'].font = Font(name='Calibri', size=11, italic=True)

# Master Data Centroid
ws['A4'] = '1. TITIK REPRESENTATIF OUTPUT (SINGLETON)'
ws['A4'].font = Font(name='Calibri', size=11, bold=True)

ws['A5'] = 'Kategori'
ws['B5'] = 'Nilai Singleton (z)'
ws['A6'] = 'Baik'
ws['B6'] = 20
ws['A7'] = 'Perlu Servis'
ws['B7'] = 60
ws['A8'] = 'Kritis'
ws['B8'] = 90

# Format master
border_thin = Border(left=Side(style='thin'), right=Side(style='thin'), top=Side(style='thin'), bottom=Side(style='thin'))
for row in ws['A5:B8']:
    for cell in row:
        cell.border = border_thin

ws['A5'].font = Font(bold=True)
ws['B5'].font = Font(bold=True)

# Table 2: Input & Formulas
ws['A10'] = '2. SIMULASI KELOMPOK SKENARIO (DENGAN RUMUS EXCEL DINAMIS)'
ws['A10'].font = Font(name='Calibri', size=11, bold=True)

headers = ['No', 'Skenario Uji', 'Jarak (km)', 'Durasi (hari)', 'Speed (kph)', 'Intensitas (km/hr)', 'Alpha Baik', 'Alpha Perlu Servis', 'Alpha Kritis', 'Output Sistem', 'Rumus Output Manual (Excel)', 'Selisih', 'Status']
ws.append(headers)

# Apply header style
header_row = ws[11]
for cell in header_row:
    cell.font = Font(name='Calibri', size=11, bold=True, color='FFFFFF')
    cell.fill = PatternFill(start_color='1F4E78', end_color='1F4E78', fill_type='solid')
    cell.alignment = Alignment(horizontal='center', vertical='center')

# Scenarios with LIVE EXCEL FORMULAS
# Row 12: Skenario 1
ws.append([1, 'Skenario 1 (Normal)', 500, 30, 40, 5, 1.0, 0.0, 0.0, 20, '=SUMPRODUCT(G12:I12, $B$6:$B$8)/SUM(G12:I12)', '=J12-K12', '=IF(ABS(L12)<0.01, "Sesuai", "Tidak Sesuai")'])

# Row 13: Skenario 2
ws.append([2, 'Skenario 2 (Perlu Servis)', 2000, 90, 55, 15, 0.0, 1.0, 0.0, 60, '=SUMPRODUCT(G13:I13, $B$6:$B$8)/SUM(G13:I13)', '=J13-K13', '=IF(ABS(L13)<0.01, "Sesuai", "Tidak Sesuai")'])

# Row 14: Skenario 3
ws.append([3, 'Skenario 3 (Kritis)', 4500, 180, 70, 25, 0.0, 0.0, 1.0, 90, '=SUMPRODUCT(G14:I14, $B$6:$B$8)/SUM(G14:I14)', '=J14-K14', '=IF(ABS(L14)<0.01, "Sesuai", "Tidak Sesuai")'])

# Format data rows
for row_idx in range(12, 15):
    for col_idx in range(1, 14):
        cell = ws.cell(row=row_idx, column=col_idx)
        cell.border = border_thin
        if col_idx in [1, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]:
            cell.alignment = Alignment(horizontal='center')

# Auto adjust column widths
for col in ws.columns:
    max_len = 0
    col_letter = get_column_letter(col[0].column)
    for cell in col:
        val_str = str(cell.value or '')
        if len(val_str) > max_len:
            max_len = len(val_str)
    ws.column_dimensions[col_letter].width = max(max_len + 4, 12)

wb.save(r'c:\laragon\www\motorcycle_management\Hitung_Manual_Fuzzy_TrinGo_v2.xlsx')
print('Dynamic Excel file generated successfully with LIVE FORMULAS!')
