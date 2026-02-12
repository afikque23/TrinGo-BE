@extends('layouts.sidebar')

@section('title', 'Detail Konten - MotoTracker')
@section('page-title', 'Detail Konten Komunitas')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('admin.konten.komunitas') }}" class="text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-white">Detail Konten</h1>
    </div>
</div>

<!-- Content Header -->
<div class="bg-[#111111] border border-gray-800 rounded-xl overflow-hidden mb-6">
    <div class="p-6 bg-black border-b border-gray-800">
        <div class="flex items-start justify-between mb-4">
            <h2 class="text-2xl font-bold text-white">Cara Ganti Oli Motor Matic Honda</h2>
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                Published
            </span>
        </div>
        <div class="flex items-center gap-4 text-sm text-gray-400">
            <span>Budi Santoso</span>
            <span>•</span>
            <span>Honda PCX 160</span>
            <span>•</span>
            <span>2024-02-06 14:23</span>
        </div>
    </div>
    
    <div class="p-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-black border border-gray-800 rounded-lg p-4">
                <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Kategori</p>
                <p class="text-white">Perawatan Rutin</p>
            </div>
            <div class="bg-black border border-gray-800 rounded-lg p-4">
                <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Kesulitan</p>
                <p class="text-white">Mudah</p>
            </div>
            <div class="bg-black border border-gray-800 rounded-lg p-4 flex items-center gap-3">
                <div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <p class="text-white mt-1">234</p>
                </div>
                <div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                    <p class="text-white mt-1">45</p>
                </div>
            </div>
        </div>

        <!-- Content Body -->
        <div class="bg-black border border-gray-800 rounded-lg p-6">
            <p class="text-xs uppercase tracking-wide text-gray-400 mb-3">Isi Konten</p>
            <div class="prose prose-invert max-w-none">
                <p class="text-gray-300 leading-relaxed mb-4">
                    Halo teman-teman! Hari ini saya mau share cara ganti oli motor matic sendiri di rumah. Pertama, siapkan oli baru berkualitas, kunci pas, dan wadah penampung oli bekas.
                </p>
                <p class="text-gray-300 leading-relaxed mb-4">
                    <strong>Langkah 1:</strong> Panaskan motor selama 2-3 menit agar oli mengalir dengan baik<br>
                    <strong>Langkah 2:</strong> Matikan mesin dan tunggu sebentar hingga tidak terlalu panas<br>
                    <strong>Langkah 3:</strong> Buka baut pembuangan oli menggunakan kunci pas<br>
                    <strong>Langkah 4:</strong> Tampung oli bekas dengan wadah yang sudah disiapkan<br>
                    <strong>Langkah 5:</strong> Tutup kembali baut pembuangan dengan rapat<br>
                    <strong>Langkah 6:</strong> Isi oli baru melalui lubang pengisian sesuai spesifikasi<br>
                    <strong>Langkah 7:</strong> Cek level oli dengan dipstick hingga mencapai batas ideal
                </p>
                <p class="text-gray-300 leading-relaxed">
                    Selesai! Mudah kan? Total waktu cuma 15-20 menit. Pastikan gunakan oli berkualitas untuk performa optimal.
                </p>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="p-6 bg-black border-t border-gray-800">
        <div class="flex items-center gap-3">
            <button class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-gray-800 hover:bg-gray-700 text-white font-bold rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                </svg>
                Hide Konten
            </button>
            <button class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-green-700 hover:bg-green-600 text-white font-bold rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Publish Manual
            </button>
            <button class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-gray-800 hover:bg-gray-700 text-white font-bold rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Konten
            </button>
            <button class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-red-600 hover:bg-red-500 text-white font-bold rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Hapus Permanen
            </button>
        </div>
    </div>
</div>
@endsection
