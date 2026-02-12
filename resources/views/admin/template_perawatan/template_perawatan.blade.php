@extends('layouts.sidebar')

@section('title', 'Template Perawatan - MotoTracker')
@section('page-title', 'Template Perawatan')

@push('styles')
<style>
    /* Custom scrollbar for modal */
    #templateModal ::-webkit-scrollbar {
        width: 8px;
    }
    #templateModal ::-webkit-scrollbar-track {
        background: #0A0A0A;
    }
    #templateModal ::-webkit-scrollbar-thumb {
        background: #364153;
        border-radius: 4px;
    }
    #templateModal ::-webkit-scrollbar-thumb:hover {
        background: #4A5565;
    }
</style>
@endpush

@section('content')
<!-- Description -->
<p class="text-sm text-[#99A1AF] mb-6" style="font-family: Arial, sans-serif;">Kelola template perawatan sebagai referensi sistem</p>

<!-- Filters & Add Button -->
<div class="flex items-center justify-between mb-6">
    <!-- Filters -->
    <div class="flex items-center gap-3">
        <!-- Jenis Motor Dropdown -->
        <select class="h-[38px] px-4 bg-[#111111] border border-[#1E2939] rounded-[10px] text-sm text-white focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
            <option>Semua Jenis Motor</option>
            <option>Sport</option>
            <option>Matic</option>
            <option>Bebek</option>
            <option>LORIK M/KURIS</option>
        </select>

        <!-- Status Dropdown -->
        <select class="h-[38px] px-4 bg-[#111111] border border-[#1E2939] rounded-[10px] text-sm text-white focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
            <option>Semua Status</option>
            <option>Aktif</option>
            <option>Draft</option>
        </select>
    </div>

    <!-- Add Button -->
    <button onclick="openModal()" class="h-9 px-4 bg-[#6B7C4F] rounded-[10px] text-sm text-white flex items-center gap-2 hover:bg-[#7a8d5c] transition-colors" style="font-family: Arial, sans-serif;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 4v16m8-8H4"></path>
        </svg>
        Tambah Template
    </button>
</div>

<!-- Table -->
<div class="bg-[#111111] border border-[#1E2939] rounded-[14px] overflow-hidden">
    <table class="w-full">
        <!-- Table Header -->
        <thead>
            <tr class="border-b border-[#1E2939]">
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Nama Template</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Jenis Motor</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Interval KM</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Interval Waktu</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Digunakan AI</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Status</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Aksi</th>
            </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
            <!-- Row 1 -->
            <tr class="border-b border-[#1E2939] hover:bg-[#0A0A0A]/50 transition-colors">
                <td class="px-5 py-5 text-sm text-white" style="font-family: Arial, sans-serif;">Ganti Oli Rutin Harian</td>
                <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Matic</td>
                <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">2000 km</td>
                <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">1 bulan</td>
                <td class="px-5 py-5">
                    <!-- Toggle Switch -->
                    <button type="button" onclick="toggleAI(this, 1)" class="relative w-12 h-6 bg-[#6B7C4F] rounded-full transition-colors" data-active="true">
                        <span class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-200"></span>
                    </button>
                </td>
                <td class="px-5 py-5">
                    <span class="text-xs text-[#6B7C4F]" style="font-family: Arial, sans-serif;">Aktif</span>
                </td>
                <td class="px-5 py-5">
                    <div class="flex items-center gap-2">
                        <button onclick="openEditModal(1)" class="w-8 h-8 bg-[#1A1A1A] border border-[#364153] rounded-[10px] flex items-center justify-center hover:bg-[#2A2A2A] transition-colors">
                            <svg class="w-4 h-4 text-[#99A1AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <form action="{{ route('admin.templates.destroy', 1) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-8 h-8 bg-[#FB2C36] rounded-[10px] flex items-center justify-center hover:bg-[#dc2730] transition-colors">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Row 2 -->
            <tr class="border-b border-[#1E2939] hover:bg-[#0A0A0A]/50 transition-colors">
                <td class="px-5 py-5 text-sm text-white" style="font-family: Arial, sans-serif;">Perawatan Rantai Long Trip</td>
                <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Sport</td>
                <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">500 km</td>
                <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">2 minggu</td>
                <td class="px-5 py-5">
                    <button type="button" onclick="toggleAI(this, 2)" class="relative w-12 h-6 bg-[#6B7C4F] rounded-full transition-colors" data-active="true">
                        <span class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-200"></span>
                    </button>
                </td>
                <td class="px-5 py-5">
                    <span class="text-xs text-[#6B7C4F]" style="font-family: Arial, sans-serif;">Aktif</span>
                </td>
                <td class="px-5 py-5">
                    <div class="flex items-center gap-2">
                        <button onclick="openEditModal(2)" class="w-8 h-8 bg-[#1A1A1A] border border-[#364153] rounded-[10px] flex items-center justify-center hover:bg-[#2A2A2A] transition-colors">
                            <svg class="w-4 h-4 text-[#99A1AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <form action="{{ route('admin.templates.destroy', 2) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-8 h-8 bg-[#FB2C36] rounded-[10px] flex items-center justify-center hover:bg-[#dc2730] transition-colors">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Row 3 -->
            <tr class="border-b border-[#1E2939] hover:bg-[#0A0A0A]/50 transition-colors">
                <td class="px-5 py-5 text-sm text-white" style="font-family: Arial, sans-serif;">Cek Rem & Ban</td>
                <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Semua</td>
                <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">1000 km</td>
                <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">1 bulan</td>
                <td class="px-5 py-5">
                    <button type="button" onclick="toggleAI(this, 3)" class="relative w-12 h-6 bg-[#6B7C4F] rounded-full transition-colors" data-active="true">
                        <span class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-200"></span>
                    </button>
                </td>
                <td class="px-5 py-5">
                    <span class="text-xs text-[#6B7C4F]" style="font-family: Arial, sans-serif;">Aktif</span>
                </td>
                <td class="px-5 py-5">
                    <div class="flex items-center gap-2">
                        <button onclick="openEditModal(3)" class="w-8 h-8 bg-[#1A1A1A] border border-[#364153] rounded-[10px] flex items-center justify-center hover:bg-[#2A2A2A] transition-colors">
                            <svg class="w-4 h-4 text-[#99A1AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <form action="{{ route('admin.templates.destroy', 3) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-8 h-8 bg-[#FB2C36] rounded-[10px] flex items-center justify-center hover:bg-[#dc2730] transition-colors">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Row 4 (Draft) -->
            <tr class="hover:bg-[#0A0A0A]/50 transition-colors">
                <td class="px-5 py-5 text-sm text-white" style="font-family: Arial, sans-serif;">Service Filter Udara</td>
                <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Bebek</td>
                <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">3000 km</td>
                <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">3 bulan</td>
                <td class="px-5 py-5">
                    <!-- Toggle Switch (Off) -->
                    <button type="button" onclick="toggleAI(this, 4)" class="relative w-12 h-6 bg-[#364153] rounded-full transition-colors" data-active="false">
                        <span class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-200"></span>
                    </button>
                </td>
                <td class="px-5 py-5">
                    <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Draft</span>
                </td>
                <td class="px-5 py-5">
                    <div class="flex items-center gap-2">
                        <button onclick="openEditModal(4)" class="w-8 h-8 bg-[#1A1A1A] border border-[#364153] rounded-[10px] flex items-center justify-center hover:bg-[#2A2A2A] transition-colors">
                            <svg class="w-4 h-4 text-[#99A1AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <form action="{{ route('admin.templates.destroy', 4) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-8 h-8 bg-[#FB2C36] rounded-[10px] flex items-center justify-center hover:bg-[#dc2730] transition-colors">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal Template -->
<div id="templateModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" style="font-family: Arial, sans-serif;">
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col m-4">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 h-[61px] border-b border-[#1E2939] flex-shrink-0">
            <h3 id="modalTitle" class="text-lg font-bold text-white">Tambah Template</h3>
            <button onclick="closeModal()" class="text-[#99A1AF] hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="flex-1 overflow-y-auto px-6 py-6">
            <form id="templateForm" class="space-y-5">
                @csrf
                <input type="hidden" id="templateId" name="template_id">
                <input type="hidden" id="formMethod" value="POST">
                
                <!-- Nama Template -->
                <div>
                    <label class="block text-sm text-[#99A1AF] mb-2">Nama Template</label>
                    <input 
                        type="text" 
                        name="nama_template" 
                        id="namaTemplate"
                        required
                        class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]" 
                        placeholder="Contoh: Ganti Oli Rutin Harian"
                    >
                </div>

                <!-- Deskripsi Teknis -->
                <div>
                    <label class="block text-sm text-[#99A1AF] mb-2">Deskripsi Teknis</label>
                    <textarea 
                        name="deskripsi_teknis" 
                        id="deskripsiTeknis"
                        rows="3"
                        class="w-full px-4 py-2.5 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] resize-none" 
                        style="height: 94px;"
                        placeholder="Deskripsi detail untuk referensi sistem..."
                    ></textarea>
                </div>

                <!-- Langkah-langkah Perawatan -->
                <div>
                    <label class="block text-sm text-[#99A1AF] mb-2">Langkah-langkah Perawatan</label>
                    <div id="stepsContainer" class="space-y-2"></div>
                    <button type="button" onclick="addStep()" class="w-full h-[38px] mt-2 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-sm text-[#99A1AF] hover:bg-[#2A2A2A] transition-colors">
                        + Tambah Langkah
                    </button>
                </div>

                <!-- Interval KM & Waktu -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm text-[#99A1AF] mb-2">Interval KM</label>
                        <input 
                            type="number" 
                            name="interval_km" 
                            id="intervalKm"
                            required
                            min="0"
                            class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]" 
                            placeholder="2000"
                        >
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm text-[#99A1AF] mb-2">Interval Waktu</label>
                        <input 
                            type="text" 
                            name="interval_waktu" 
                            id="intervalWaktu"
                            required
                            class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]" 
                            placeholder="1 bulan"
                        >
                    </div>
                </div>

                <!-- Tingkat Kesulitan & Jenis Motor -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm text-[#99A1AF] mb-2">Tingkat Kesulitan</label>
                        <select 
                            name="tingkat_kesulitan" 
                            id="tingkatKesulitan"
                            required
                            class="w-full h-[45.5px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-base text-white focus:outline-none focus:border-[#6B7C4F]" 
                        >
                            <option value="">Pilih Tingkat</option>
                            <option value="Mudah">Mudah</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Sulit">Sulit</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm text-[#99A1AF] mb-2">Jenis Motor</label>
                        <select 
                            name="jenis_motor" 
                            id="jenisMotor"
                            required
                            class="w-full h-[45.5px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-base text-white focus:outline-none focus:border-[#6B7C4F]" 
                        >
                            <option value="">Pilih Jenis Motor</option>
                            <option value="Sport">Sport</option>
                            <option value="Matic">Matic</option>
                            <option value="Bebek">Bebek</option>
                            <option value="Semua">Semua</option>
                        </select>
                    </div>
                </div>

                <!-- Digunakan sebagai Referensi AI -->
                <div class="flex items-center justify-between h-[50px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px]">
                    <span class="text-sm text-white">Digunakan sebagai Referensi AI</span>
                    <button 
                        type="button" 
                        id="aiToggleModal"
                        class="relative w-12 h-6 bg-[#6B7C4F] rounded-full transition-colors"
                        data-active="true"
                    >
                        <span id="aiToggleCircleModal" class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-200"></span>
                    </button>
                    <input type="hidden" name="digunakan_ai" id="digunakanAi" value="1">
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center gap-3 px-6 py-4 border-t border-[#1E2939] flex-shrink-0">
            <button 
                type="button"
                onclick="submitForm()"
                id="submitBtn"
                class="flex-1 h-12 bg-[#6B7C4F] rounded-[10px] text-base text-white hover:bg-[#7a8d5c] transition-colors" 
            >
                Simpan Template
            </button>
            <button 
                type="button"
                onclick="closeModal()"
                class="flex-1 h-[50px] bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-base text-white hover:bg-[#2A2A2A] transition-colors" 
            >
                Batal
            </button>
        </div>
    </div>
</div>

<!-- Toggle Script -->
<script>
// Toggle AI for table rows
function toggleAI(button, templateId) {
    const isActive = button.dataset.active === 'true';
    const circle = button.querySelector('span');
    const row = button.closest('tr');
    const statusCell = row.querySelector('td:nth-child(6) span');
    
    if (isActive) {
        button.dataset.active = 'false';
        button.classList.remove('bg-[#6B7C4F]');
        button.classList.add('bg-[#364153]');
        circle.classList.remove('right-1');
        circle.classList.add('left-1');
        statusCell.textContent = 'Draft';
        statusCell.classList.remove('text-[#6B7C4F]');
        statusCell.classList.add('text-[#6A7282]');
    } else {
        button.dataset.active = 'true';
        button.classList.remove('bg-[#364153]');
        button.classList.add('bg-[#6B7C4F]');
        circle.classList.remove('left-1');
        circle.classList.add('right-1');
        statusCell.textContent = 'Aktif';
        statusCell.classList.remove('text-[#6A7282]');
        statusCell.classList.add('text-[#6B7C4F]');
    }
}

// Modal Functions
function openModal() {
    document.getElementById('templateModal').classList.remove('hidden');
    document.getElementById('templateModal').classList.add('flex');
    document.getElementById('modalTitle').textContent = 'Tambah Template';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('templateId').value = '';
    resetForm();
}

function closeModal() {
    document.getElementById('templateModal').classList.add('hidden');
    document.getElementById('templateModal').classList.remove('flex');
    resetForm();
}

function resetForm() {
    document.getElementById('templateForm').reset();
    document.getElementById('stepsContainer').innerHTML = '';
    addStep(); // Add one empty step
    
    // Reset AI toggle to ON
    const toggle = document.getElementById('aiToggleModal');
    const circle = document.getElementById('aiToggleCircleModal');
    toggle.dataset.active = 'true';
    toggle.classList.remove('bg-[#364153]');
    toggle.classList.add('bg-[#6B7C4F]');
    circle.classList.remove('left-1');
    circle.classList.add('right-1');
    document.getElementById('digunakanAi').value = '1';
}

async function openEditModal(templateId) {
    try {
        const response = await fetch(`/admin/templates/${templateId}/edit`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            
            document.getElementById('templateModal').classList.remove('hidden');
            document.getElementById('templateModal').classList.add('flex');
            document.getElementById('modalTitle').textContent = 'Edit Template';
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('templateId').value = data.id;
            
            // Fill form
            document.getElementById('namaTemplate').value = data.nama_template || '';
            document.getElementById('deskripsiTeknis').value = data.deskripsi_teknis || '';
            document.getElementById('intervalKm').value = data.interval_km || '';
            document.getElementById('intervalWaktu').value = data.interval_waktu || '';
            document.getElementById('tingkatKesulitan').value = data.tingkat_kesulitan || '';
            document.getElementById('jenisMotor').value = data.jenis_motor || '';
            
            // Fill steps
            document.getElementById('stepsContainer').innerHTML = '';
            if (data.langkah && data.langkah.length > 0) {
                data.langkah.forEach(step => addStep(step));
            } else {
                addStep();
            }
            
            // Set AI toggle
            const toggle = document.getElementById('aiToggleModal');
            const circle = document.getElementById('aiToggleCircleModal');
            const isActive = data.digunakan_ai == 1;
            
            toggle.dataset.active = isActive ? 'true' : 'false';
            if (isActive) {
                toggle.classList.remove('bg-[#364153]');
                toggle.classList.add('bg-[#6B7C4F]');
                circle.classList.remove('left-1');
                circle.classList.add('right-1');
            } else {
                toggle.classList.remove('bg-[#6B7C4F]');
                toggle.classList.add('bg-[#364153]');
                circle.classList.remove('right-1');
                circle.classList.add('left-1');
            }
            document.getElementById('digunakanAi').value = isActive ? '1' : '0';
        } else {
            alert('Gagal mengambil data template');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengambil data');
    }
}

// Steps Management
function addStep(value = '') {
    const container = document.getElementById('stepsContainer');
    const stepDiv = document.createElement('div');
    stepDiv.className = 'flex items-center gap-2 step-item';
    stepDiv.innerHTML = `
        <input 
            type="text" 
            name="langkah[]" 
            value="${value}"
            class="flex-1 h-[38px] px-3 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-sm text-white focus:outline-none focus:border-[#6B7C4F]" 
            placeholder="Masukkan langkah perawatan"
        >
        <button type="button" onclick="removeStep(this)" class="w-8 h-8 bg-[#FB2C36] rounded-[10px] flex items-center justify-center hover:bg-[#dc2730] transition-colors">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    container.appendChild(stepDiv);
}

function removeStep(button) {
    const steps = document.querySelectorAll('.step-item');
    if (steps.length > 1) {
        button.closest('.step-item').remove();
    }
}

// AI Toggle in Modal
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('aiToggleModal');
    const toggleCircle = document.getElementById('aiToggleCircleModal');
    const toggleInput = document.getElementById('digunakanAi');

    if (toggle) {
        toggle.addEventListener('click', function() {
            const isActive = toggle.dataset.active === 'true';
            
            if (isActive) {
                toggle.dataset.active = 'false';
                toggle.classList.remove('bg-[#6B7C4F]');
                toggle.classList.add('bg-[#364153]');
                toggleCircle.classList.remove('right-1');
                toggleCircle.classList.add('left-1');
                toggleInput.value = '0';
            } else {
                toggle.dataset.active = 'true';
                toggle.classList.remove('bg-[#364153]');
                toggle.classList.add('bg-[#6B7C4F]');
                toggleCircle.classList.remove('left-1');
                toggleCircle.classList.add('right-1');
                toggleInput.value = '1';
            }
        });
    }
});

// Form Submission
async function submitForm() {
    const form = document.getElementById('templateForm');
    const submitBtn = document.getElementById('submitBtn');
    const formData = new FormData(form);
    
    const method = document.getElementById('formMethod').value;
    const templateId = document.getElementById('templateId').value;
    
    const url = method === 'PUT' 
        ? `/admin/templates/${templateId}`
        : '/admin/templates';
    
    // Add _method for PUT request (Laravel method spoofing)
    if (method === 'PUT') {
        formData.append('_method', 'PUT');
    }
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            closeModal();
            location.reload();
        } else {
            alert(result.message || 'Gagal menyimpan template');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Simpan Template';
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Close modal on backdrop click
document.getElementById('templateModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection
