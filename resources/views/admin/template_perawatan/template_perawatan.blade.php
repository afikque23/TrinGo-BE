@extends('layouts.sidebar')

@section('title', 'Monitoring Tips Perawatan - MotoTracker')
@section('page-title', 'Monitoring Tips Perawatan')

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
<div class="mb-6">
    <h1 class="text-3xl font-bold text-white mb-2" style="font-family: Arial, sans-serif;">Monitoring Tips Perawatan</h1>
    <p class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Supervisi tips perawatan yang dipublikasikan otomatis</p>
</div>

@php
    $stats = $stats ?? ['uploaded_today' => 0, 'auto_published' => 0, 'flagged' => 0, 'deleted' => 0];
    $filters = $filters ?? ['status' => 'all', 'brand' => 'all', 'range' => 'all', 'q' => ''];
@endphp

<!-- Monitoring Cards -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide mb-1 text-[#6A7282]" style="font-family: Arial, sans-serif;">Upload Hari Ini</p>
                <p class="text-2xl font-bold text-white" style="font-family: Arial, sans-serif;">{{ $stats['uploaded_today'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-[#1A1A1A] rounded-[10px] flex items-center justify-center border border-[#1E2939]">
                <svg class="w-5 h-5 text-[#99A1AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide mb-1 text-[#6A7282]" style="font-family: Arial, sans-serif;">Auto Published</p>
                <p class="text-2xl font-bold text-white" style="font-family: Arial, sans-serif;">{{ $stats['auto_published'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-[#1A1A1A] rounded-[10px] flex items-center justify-center border border-[#1E2939]">
                <svg class="w-5 h-5 text-[#6B7C4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide mb-1 text-[#6A7282]" style="font-family: Arial, sans-serif;">Flagged oleh Sistem</p>
                <p class="text-2xl font-bold text-white" style="font-family: Arial, sans-serif;">{{ $stats['flagged'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-[#1A1A1A] rounded-[10px] flex items-center justify-center border border-[#1E2939]">
                <svg class="w-5 h-5 text-[#EAB308]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide mb-1 text-[#6A7282]" style="font-family: Arial, sans-serif;">Konten Dihapus</p>
                <p class="text-2xl font-bold text-white" style="font-family: Arial, sans-serif;">{{ $stats['deleted'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-[#1A1A1A] rounded-[10px] flex items-center justify-center border border-[#1E2939]">
                <svg class="w-5 h-5 text-[#FB2C36]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-4 mb-6">
    <form method="GET" action="{{ route('admin.templates') }}">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs uppercase tracking-wide text-[#6A7282] mb-2" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Status Konten</label>
                <select name="status" onchange="this.form.submit()" class="w-full h-[46px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-4 text-white focus:outline-none focus:border-[#6B7C4F]">
                    <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Flagged</option>
                </select>
            </div>

            <div>
                <label class="block text-xs uppercase tracking-wide text-[#6A7282] mb-2" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Motor</label>
                <select name="brand" onchange="this.form.submit()" class="w-full h-[46px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-4 text-white focus:outline-none focus:border-[#6B7C4F]">
                    <option value="all" {{ ($filters['brand'] ?? 'all') === 'all' ? 'selected' : '' }}>Semua Brand</option>
                    @foreach(($brandOptions ?? []) as $brandOption)
                        <option value="{{ $brandOption }}" {{ ($filters['brand'] ?? '') === $brandOption ? 'selected' : '' }}>{{ $brandOption }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs uppercase tracking-wide text-[#6A7282] mb-2" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Rentang Tanggal</label>
                <select name="range" onchange="this.form.submit()" class="w-full h-[46px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-4 text-white focus:outline-none focus:border-[#6B7C4F]">
                    <option value="all" {{ ($filters['range'] ?? 'all') === 'all' ? 'selected' : '' }}>Semua Waktu</option>
                    <option value="7d" {{ ($filters['range'] ?? '') === '7d' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="30d" {{ ($filters['range'] ?? '') === '30d' ? 'selected' : '' }}>30 Hari Terakhir</option>
                    <option value="90d" {{ ($filters['range'] ?? '') === '90d' ? 'selected' : '' }}>90 Hari Terakhir</option>
                </select>
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-xs uppercase tracking-wide text-[#6A7282] mb-2" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Cari</label>
            <div class="flex gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="Judul / deskripsi / hashtag"
                    class="w-full h-[46px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-4 text-white placeholder-white/40 focus:outline-none focus:border-[#6B7C4F]"
                />
                <button type="submit" class="h-[46px] px-4 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-sm text-white hover:bg-[#2A2A2A] transition-colors" style="font-family: Arial, sans-serif;">
                    Cari
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Add Button -->
<div class="flex items-center justify-end mb-6">
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
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Judul Tips</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Uploader</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Kendaraan</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Gaya Berkendara</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Interval</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Boleh Disalin</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Status</th>
                <th class="px-5 py-5 text-left text-xs font-normal text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.6px;">Aksi</th>
            </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
            @forelse($tips as $tip)
                <tr class="border-b border-[#1E2939] hover:bg-[#0A0A0A]/50 transition-colors">
                    <td class="px-5 py-5 text-sm text-white" style="font-family: Arial, sans-serif;">{{ $tip->title }}</td>
                    <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">{{ $tip->user?->name ?? '-' }}</td>
                    <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                        @php
                            $vehicleParts = array_values(array_filter([
                                $tip->vehicle_brand,
                                $tip->vehicle_model,
                                $tip->vehicle_year,
                            ], fn ($v) => $v !== null && $v !== ''));
                        @endphp
                        {{ empty($vehicleParts) ? '-' : implode(' • ', $vehicleParts) }}
                    </td>
                    <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">{{ $tip->riding_style }}</td>
                    <td class="px-5 py-5 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                        @php
                            $intervalParts = [];
                            if ($tip->interval_distance_km) $intervalParts[] = $tip->interval_distance_km . ' km';
                            if ($tip->interval_time_months) $intervalParts[] = $tip->interval_time_months . ' bulan';
                        @endphp
                        {{ empty($intervalParts) ? '-' : implode(' / ', $intervalParts) }}
                    </td>
                    <td class="px-5 py-5">
                        <span class="text-xs {{ $tip->is_copyable ? 'text-[#6B7C4F]' : 'text-[#6A7282]' }}" style="font-family: Arial, sans-serif;">
                            {{ $tip->is_copyable ? 'Ya' : 'Tidak' }}
                        </span>
                    </td>
                    <td class="px-5 py-5">
                        <span class="text-xs {{ $tip->status === 'published' ? 'text-[#6B7C4F]' : 'text-[#6A7282]' }}" style="font-family: Arial, sans-serif;">
                            {{ $tip->status === 'published' ? 'Aktif' : 'Draft' }}
                        </span>
                    </td>
                    <td class="px-5 py-5">
                        <div class="flex items-center gap-2">
                            <button onclick="openEditModal({{ $tip->id }})" class="w-8 h-8 bg-[#1A1A1A] border border-[#364153] rounded-[10px] flex items-center justify-center hover:bg-[#2A2A2A] transition-colors">
                                <svg class="w-4 h-4 text-[#99A1AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <form action="{{ route('admin.templates.destroy', $tip->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?');">
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
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-8 text-sm text-[#6A7282]" style="font-family: Arial, sans-serif;">Belum ada template. Klik "Tambah Template" untuk membuat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($tips, 'links'))
    <div class="mt-4 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">
        {{ $tips->links() }}
    </div>
@endif

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
            <form id="templateForm" class="space-y-4">
                @csrf
                <input type="hidden" id="templateId" name="template_id">
                <input type="hidden" id="formMethod" value="POST">

                <!-- Informasi Dasar (mobile-like) -->
                <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-[14px] p-5">
                    <h4 class="text-white font-bold mb-4" style="font-family: Arial, sans-serif;">Informasi Dasar</h4>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-[#99A1AF] mb-2">Judul Tips *</label>
                            <input
                                type="text"
                                name="title"
                                id="title"
                                required
                                class="w-full h-[46px] px-4 bg-[#111111] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]"
                                placeholder="Contoh: Cara Efisien Ganti Oli"
                            >
                        </div>

                        <div>
                            <label class="block text-sm text-[#99A1AF] mb-2">Merek Motor *</label>
                            <input
                                type="text"
                                list="brandOptions"
                                name="vehicle_brand"
                                id="vehicleBrand"
                                required
                                class="w-full h-[46px] px-4 bg-[#111111] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]"
                                placeholder="Contoh: Honda"
                            >
                            <datalist id="brandOptions">
                                <option value="Honda"></option>
                                <option value="Yamaha"></option>
                                <option value="Suzuki"></option>
                                <option value="Kawasaki"></option>
                                <option value="Vespa"></option>
                            </datalist>
                        </div>

                        <div>
                            <label class="block text-sm text-[#99A1AF] mb-2">Tipe / Model *</label>
                            <input
                                type="text"
                                name="vehicle_model"
                                id="vehicleModel"
                                required
                                class="w-full h-[46px] px-4 bg-[#111111] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]"
                                placeholder="Contoh: PCX 160"
                            >
                        </div>

                        <div>
                            <label class="block text-sm text-[#99A1AF] mb-2">Tahun *</label>
                            <input
                                type="number"
                                name="vehicle_year"
                                id="vehicleYear"
                                required
                                min="1990"
                                max="{{ date('Y') + 1 }}"
                                class="w-full h-[46px] px-4 bg-[#111111] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]"
                                placeholder="2023"
                            >
                        </div>

                        <div>
                            <label class="block text-sm text-[#99A1AF] mb-2">Gaya Berkendara *</label>
                            <select
                                name="riding_style"
                                id="ridingStyle"
                                required
                                class="w-full h-[46px] px-4 bg-[#111111] border border-[#364153] rounded-[10px] text-base text-white focus:outline-none focus:border-[#6B7C4F]"
                            >
                                <option value="" disabled selected>Pilih Gaya Berkendara</option>
                                <option value="Harian / Commuter">Harian / Commuter</option>
                                <option value="Touring">Touring</option>
                                <option value="Sport / Track">Sport / Track</option>
                                <option value="Off-road">Off-road</option>
                                <option value="Urban / City">Urban / City</option>
                                <option value="Kombinasi">Kombinasi</option>
                            </select>
                        </div>

                        <div class="bg-[#111111] border border-[#364153] rounded-[10px] p-4 flex items-center justify-between">
                            <div>
                                <div class="text-sm text-white">Boleh Disalin Pengguna Lain</div>
                                <div class="text-xs text-[#99A1AF]">Izinkan komunitas mengadaptasi template ini</div>
                            </div>
                            <button
                                type="button"
                                id="copyableToggle"
                                class="relative w-12 h-6 bg-[#6B7C4F] rounded-full transition-colors"
                                data-active="true"
                            >
                                <span id="copyableToggleCircle" class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-200"></span>
                            </button>
                            <input type="hidden" name="is_copyable" id="isCopyable" value="1">
                        </div>
                    </div>
                </div>

                <!-- Detail Perawatan (mobile-like) -->
                <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-[14px] p-5">
                    <h4 class="text-white font-bold mb-4" style="font-family: Arial, sans-serif;">Detail Perawatan</h4>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-[#99A1AF] mb-2">Deskripsi Singkat *</label>
                            <textarea
                                name="description"
                                id="description"
                                rows="3"
                                required
                                class="w-full px-4 py-2.5 bg-[#111111] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] resize-none"
                                style="height: 94px;"
                                placeholder="Jelaskan secara singkat tentang tips perawatan..."
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-[#99A1AF] mb-2">Hashtag / Kata Kunci</label>
                            <input
                                type="text"
                                name="hashtags"
                                id="hashtags"
                                class="w-full h-[46px] px-4 bg-[#111111] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]"
                                placeholder="#Oli Mesin, #Perawatan Rutin, #Daily Rider"
                            >
                            <div class="text-xs text-[#6A7282] mt-1">Pisahkan dengan koma. Maksimal 10 hashtag.</div>
                        </div>

                        <div>
                            <label class="block text-sm text-[#99A1AF] mb-2">Langkah-langkah Perawatan *</label>
                            <div id="stepsContainer" class="space-y-2"></div>
                            <button type="button" onclick="addStep()" class="w-full h-[38px] mt-2 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-sm text-[#99A1AF] hover:bg-[#2A2A2A] transition-colors">
                                + Tambah Langkah
                            </button>
                        </div>

                        <div>
                            <label class="block text-sm text-[#99A1AF] mb-2">Alat yang Dibutuhkan *</label>
                            <div id="toolsContainer" class="space-y-2"></div>
                            <button type="button" onclick="addTool()" class="w-full h-[38px] mt-2 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-sm text-[#99A1AF] hover:bg-[#2A2A2A] transition-colors">
                                + Tambah Alat
                            </button>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-sm text-[#99A1AF] mb-2">Interval Jarak (km)</label>
                                <input
                                    type="number"
                                    name="interval_distance_km"
                                    id="intervalDistanceKm"
                                    min="100"
                                    class="w-full h-[46px] px-4 bg-[#111111] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]"
                                    placeholder="2000"
                                >
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm text-[#99A1AF] mb-2">Interval Waktu (bulan)</label>
                                <input
                                    type="number"
                                    name="interval_time_months"
                                    id="intervalTimeMonths"
                                    min="1"
                                    class="w-full h-[46px] px-4 bg-[#111111] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F]"
                                    placeholder="3"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-[#99A1AF] mb-2">Catatan Tambahan</label>
                            <textarea
                                name="important_notes"
                                id="importantNotes"
                                rows="3"
                                class="w-full px-4 py-2.5 bg-[#111111] border border-[#364153] rounded-[10px] text-base text-white placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] resize-none"
                                style="height: 94px;"
                                placeholder="Tips khusus, peringatan, atau informasi tambahan yang perlu diperhatikan..."
                            ></textarea>
                        </div>
                    </div>
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

<script>
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
    document.getElementById('toolsContainer').innerHTML = '';

    // Reset copyable toggle to ON
    setCopyableToggle(true);

    // Default items to satisfy validation (min 3 steps, min 1 tool)
    addStep();
    addStep();
    addStep();
    addTool();
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
            document.getElementById('title').value = data.title || '';
            document.getElementById('vehicleBrand').value = data.vehicle_brand || '';
            document.getElementById('vehicleModel').value = data.vehicle_model || '';
            document.getElementById('vehicleYear').value = data.vehicle_year || '';
            document.getElementById('ridingStyle').value = data.riding_style || '';
            document.getElementById('description').value = data.description || '';
            document.getElementById('hashtags').value = Array.isArray(data.hashtags) ? data.hashtags.map(h => `#${h}`).join(', ') : '';
            document.getElementById('intervalDistanceKm').value = data.interval_distance_km ?? '';
            document.getElementById('intervalTimeMonths').value = data.interval_time_months ?? '';
            document.getElementById('importantNotes').value = data.important_notes || '';

            setCopyableToggle(!!data.is_copyable);

            // Fill steps
            document.getElementById('stepsContainer').innerHTML = '';
            if (Array.isArray(data.steps) && data.steps.length > 0) {
                data.steps.forEach(step => addStep(step));
            } else {
                addStep();
                addStep();
                addStep();
            }

            // Fill tools
            document.getElementById('toolsContainer').innerHTML = '';
            if (Array.isArray(data.tools) && data.tools.length > 0) {
                data.tools.forEach(tool => addTool(tool));
            } else {
                addTool();
            }
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
    stepDiv.className = 'flex items-center gap-3 step-item';

    const stepNumber = container.querySelectorAll('.step-item').length + 1;
    stepDiv.innerHTML = `
        <div class="w-8 h-8 rounded-[10px] bg-[#1A1A1A] border border-[#364153] flex items-center justify-center text-xs text-[#99A1AF]">${stepNumber}</div>
        <input
            type="text"
            name="steps[]"
            value="${escapeHtml(value)}"
            class="flex-1 h-[38px] px-3 bg-[#111111] border border-[#364153] rounded-[10px] text-sm text-white focus:outline-none focus:border-[#6B7C4F]"
            placeholder="Langkah ${stepNumber}"
        >
        <button type="button" onclick="removeStep(this)" class="w-8 h-8 bg-[#FB2C36] rounded-[10px] flex items-center justify-center hover:bg-[#dc2730] transition-colors">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    container.appendChild(stepDiv);
    renumberSteps();
}

function removeStep(button) {
    const steps = document.querySelectorAll('.step-item');
    if (steps.length > 3) {
        button.closest('.step-item').remove();
        renumberSteps();
    }
}

function renumberSteps() {
    const container = document.getElementById('stepsContainer');
    const items = container.querySelectorAll('.step-item');
    items.forEach((item, index) => {
        const badge = item.querySelector('div');
        const input = item.querySelector('input');
        if (badge) badge.textContent = String(index + 1);
        if (input) input.placeholder = `Langkah ${index + 1}`;
    });
}

// Tools Management
function addTool(value = '') {
    const container = document.getElementById('toolsContainer');
    const toolDiv = document.createElement('div');
    toolDiv.className = 'flex items-center gap-3 tool-item';
    toolDiv.innerHTML = `
        <div class="w-8 h-8 rounded-[10px] bg-[#1A1A1A] border border-[#364153] flex items-center justify-center">
            <svg class="w-4 h-4 text-[#99A1AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M14.7 6.3a1 1 0 00-1.4 0l-7 7a1 1 0 000 1.4l2 2a1 1 0 001.4 0l7-7a1 1 0 000-1.4l-2-2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M8 12l4 4"></path>
            </svg>
        </div>
        <input
            type="text"
            name="tools[]"
            value="${escapeHtml(value)}"
            class="flex-1 h-[38px] px-3 bg-[#111111] border border-[#364153] rounded-[10px] text-sm text-white focus:outline-none focus:border-[#6B7C4F]"
            placeholder="Nama alat"
        >
        <button type="button" onclick="removeTool(this)" class="w-8 h-8 bg-[#FB2C36] rounded-[10px] flex items-center justify-center hover:bg-[#dc2730] transition-colors">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    container.appendChild(toolDiv);
}

function removeTool(button) {
    const tools = document.querySelectorAll('.tool-item');
    if (tools.length > 1) {
        button.closest('.tool-item').remove();
    }
}

function setCopyableToggle(isActive) {
    const toggle = document.getElementById('copyableToggle');
    const circle = document.getElementById('copyableToggleCircle');
    const input = document.getElementById('isCopyable');

    toggle.dataset.active = isActive ? 'true' : 'false';
    if (isActive) {
        toggle.classList.remove('bg-[#364153]');
        toggle.classList.add('bg-[#6B7C4F]');
        circle.classList.remove('left-1');
        circle.classList.add('right-1');
        input.value = '1';
    } else {
        toggle.classList.remove('bg-[#6B7C4F]');
        toggle.classList.add('bg-[#364153]');
        circle.classList.remove('right-1');
        circle.classList.add('left-1');
        input.value = '0';
    }
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// AI Toggle in Modal
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('copyableToggle');
    if (toggle) {
        toggle.addEventListener('click', function() {
            const isActive = toggle.dataset.active === 'true';
            setCopyableToggle(!isActive);
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

        if (response.ok && result.success) {
            alert(result.message);
            closeModal();
            location.reload();
            return;
        }

        alert(result.message || 'Gagal menyimpan template');
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
