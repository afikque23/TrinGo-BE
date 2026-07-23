@extends('layouts.sidebar')

@section('title', 'Tambah Template - TringGo')
@section('page-title', 'Form Template Perawatan')

@section('content')
<!-- Form Card -->
<div class="bg-[#111111] border border-[#1E2939] rounded-[14px] overflow-hidden w-full">
    <div class="max-w-[1115px] mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 h-[61px] border-b border-[#1E2939]">
        <h3 class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">Form Template Perawatan</h3>
    </div>

    <form id="templateForm" action="{{ route('admin.templates.store') }}" method="POST" class="px-6 pt-6 pb-6">
        @csrf
        
        <div class="space-y-5">
            <!-- Nama Template -->
            <div>
                <label class="block text-sm text-[#99A1AF] mb-2" style="font-family: Arial, sans-serif;">Nama Template</label>
                <input 
                    type="text" 
                    name="nama_template" 
                    required
                    class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]" 
                    style="font-family: Arial, sans-serif;"
                    placeholder="Contoh: Ganti Oli Rutin Harian"
                >
            </div>

            <!-- Deskripsi Teknis -->
            <div>
                <label class="block text-sm text-[#99A1AF] mb-2" style="font-family: Arial, sans-serif;">Deskripsi Teknis</label>
                <textarea 
                    name="deskripsi_teknis" 
                    rows="3"
                    class="w-full px-4 py-2.5 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] resize-none" 
                    style="font-family: Arial, sans-serif; height: 94px;"
                    placeholder="Deskripsi detail untuk referensi sistem..."
                ></textarea>
            </div>

            <!-- Langkah-langkah Perawatan -->
            <div>
                <label class="block text-sm text-[#99A1AF] mb-2" style="font-family: Arial, sans-serif;">Langkah-langkah Perawatan</label>
                <div id="stepsContainer" class="space-y-2">
                    <!-- Step 1 -->
                    <div class="flex items-center gap-2 step-item">
                        <input 
                            type="text" 
                            name="langkah[]" 
                            class="flex-1 h-[38px] px-3 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-sm text-white focus:outline-none focus:border-[#6B7C4F]" 
                            style="font-family: Arial, sans-serif;"
                            placeholder="Panaskan mesin terlebih dahulu"
                        >
                        <button type="button" onclick="removeStep(this)" class="w-8 h-8 bg-[#FB2C36] rounded-[10px] flex items-center justify-center hover:bg-[#dc2730] transition-colors">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addStep()" class="w-full h-[38px] mt-2 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-sm text-[#99A1AF] hover:bg-[#2A2A2A] transition-colors" style="font-family: Arial, sans-serif;">
                    + Tambah Langkah
                </button>
            </div>

            <!-- Interval KM & Waktu (Side by side) -->
            <div class="flex gap-4">
                <div class="flex-1">
                    <label class="block text-sm text-[#99A1AF] mb-2" style="font-family: Arial, sans-serif;">Interval KM</label>
                    <input 
                        type="number" 
                        name="interval_km" 
                        required
                        min="0"
                        class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]" 
                        style="font-family: Arial, sans-serif;"
                        placeholder="2000"
                    >
                </div>
                <div class="flex-1">
                    <label class="block text-sm text-[#99A1AF] mb-2" style="font-family: Arial, sans-serif;">Interval Waktu</label>
                    <input 
                        type="text" 
                        name="interval_waktu" 
                        required
                        class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]" 
                        style="font-family: Arial, sans-serif;"
                        placeholder="1 bulan"
                    >
                </div>
            </div>

            <!-- Tingkat Kesulitan & Jenis Motor (Side by side) -->
            <div class="flex gap-4">
                <div class="flex-1">
                    <label class="block text-sm text-[#99A1AF] mb-2" style="font-family: Arial, sans-serif;">Tingkat Kesulitan</label>
                    <select 
                        name="tingkat_kesulitan" 
                        required
                        class="w-full h-[45.5px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-base text-white focus:outline-none focus:border-[#6B7C4F]" 
                        style="font-family: Arial, sans-serif;"
                    >
                        <option value="">Pilih Tingkat</option>
                        <option value="Mudah">Mudah</option>
                        <option value="Sedang">Sedang</option>
                        <option value="Sulit">Sulit</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-sm text-[#99A1AF] mb-2" style="font-family: Arial, sans-serif;">Jenis Motor</label>
                    <select 
                        name="jenis_motor" 
                        required
                        class="w-full h-[45.5px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-base text-white focus:outline-none focus:border-[#6B7C4F]" 
                        style="font-family: Arial, sans-serif;"
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
                <span class="text-sm text-white" style="font-family: Arial, sans-serif;">Digunakan sebagai Referensi AI</span>
                <button 
                    type="button" 
                    id="aiToggle"
                    class="relative w-12 h-6 bg-[#6B7C4F] rounded-full transition-colors"
                    data-active="true"
                >
                    <span id="aiToggleCircle" class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-200"></span>
                </button>
                <input type="hidden" name="digunakan_ai" id="aiToggleInput" value="1">
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center gap-3 mt-5">
            <button 
                type="submit"
                class="flex-1 h-12 bg-[#6B7C4F] rounded-[10px] text-base text-white hover:bg-[#7a8d5c] transition-colors" 
                style="font-family: Arial, sans-serif;"
            >
                Simpan Template
            </button>
            <a 
                href="{{ route('admin.templates') }}"
                class="flex-1 h-[50px] bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-base text-white hover:bg-[#2A2A2A] transition-colors flex items-center justify-center" 
                style="font-family: Arial, sans-serif;"
            >
                Batal
            </a>
        </div>
    </form>
    </div>
</div>

<!-- Scripts -->
<script>
function addStep() {
    const container = document.getElementById('stepsContainer');
    const stepDiv = document.createElement('div');
    stepDiv.className = 'flex items-center gap-2 step-item';
    stepDiv.innerHTML = `
        <input 
            type="text" 
            name="langkah[]" 
            class="flex-1 h-[38px] px-3 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-sm text-white focus:outline-none focus:border-[#6B7C4F]" 
            style="font-family: Arial, sans-serif;"
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

document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('aiToggle');
    const toggleCircle = document.getElementById('aiToggleCircle');
    const toggleInput = document.getElementById('aiToggleInput');

    toggle.addEventListener('click', function() {
        const isActive = toggle.dataset.active === 'true';
        
        if (isActive) {
            // Turn OFF
            toggle.dataset.active = 'false';
            toggle.classList.remove('bg-[#6B7C4F]');
            toggle.classList.add('bg-[#364153]');
            toggleCircle.classList.remove('right-1');
            toggleCircle.classList.add('left-1');
            toggleInput.value = '0';
        } else {
            // Turn ON
            toggle.dataset.active = 'true';
            toggle.classList.remove('bg-[#364153]');
            toggle.classList.add('bg-[#6B7C4F]');
            toggleCircle.classList.remove('left-1');
            toggleCircle.classList.add('right-1');
            toggleInput.value = '1';
        }
    });
});
</script>
@endsection
