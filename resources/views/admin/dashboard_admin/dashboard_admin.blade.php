@extends('layouts.sidebar')

@section('title', 'Dashboard Admin - MotoTracker')
@section('page-title', 'Dashboard Admin')

@section('content')
<!-- Header Section -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-white mb-1" style="font-family: Arial, sans-serif;">Dashboard Admin MotoTracker</h1>
    <p class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Pusat Pengelolaan Konten dan Konfigurasi Aplikasi</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-5 gap-4 mb-6">
    <!-- Pengguna Terdaftar -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-[21px]">
        <div class="flex items-center mb-3">
            <div class="w-10 h-10 bg-[#1A1A1A] rounded-[10px] flex items-center justify-center">
                <svg class="w-5 h-5 text-[#99A1AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
        <p class="text-[30px] font-bold text-white leading-9 mb-3" style="font-family: Arial, sans-serif;">{{ $totalUsers ?? 2847 }}</p>
        <p class="text-xs text-[#6A7282] uppercase tracking-wide" style="font-family: Arial, sans-serif; letter-spacing: 0.3px;">Pengguna Terdaftar</p>
    </div>

    <!-- Kendaraan Aktif -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-[21px]">
        <div class="flex items-center mb-3">
            <div class="w-10 h-10 bg-[#1A1A1A] rounded-[10px] flex items-center justify-center">
                <svg class="w-5 h-5 text-[#99A1AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
        </div>
        <p class="text-[30px] font-bold text-white leading-9 mb-3" style="font-family: Arial, sans-serif;">{{ $totalVehicles ?? 3421 }}</p>
        <p class="text-xs text-[#6A7282] uppercase tracking-wide" style="font-family: Arial, sans-serif; letter-spacing: 0.3px;">Kendaraan Aktif</p>
    </div>

    <!-- Trip Mingguan -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-[21px]">
        <div class="flex items-center mb-3">
            <div class="w-10 h-10 bg-[#1A1A1A] rounded-[10px] flex items-center justify-center">
                <svg class="w-5 h-5 text-[#99A1AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
            </div>
        </div>
        <p class="text-[30px] font-bold text-white leading-9 mb-3" style="font-family: Arial, sans-serif;">{{ $totalTrips ?? 1234 }}</p>
        <p class="text-xs text-[#6A7282] uppercase tracking-wide" style="font-family: Arial, sans-serif; letter-spacing: 0.3px;">Trip Mingguan</p>
    </div>

    <!-- Template Perawatan -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-[21px]">
        <div class="flex items-center mb-3">
            <div class="w-10 h-10 bg-[#1A1A1A] rounded-[10px] flex items-center justify-center">
                <svg class="w-5 h-5 text-[#99A1AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>
        <p class="text-[30px] font-bold text-white leading-9 mb-3" style="font-family: Arial, sans-serif;">156</p>
        <p class="text-xs text-[#6A7282] uppercase tracking-wide" style="font-family: Arial, sans-serif; letter-spacing: 0.3px;">Template Perawatan</p>
    </div>

</div>

<!-- Charts Section -->
<div class="grid grid-cols-3 gap-6 mb-6">
    <!-- Aktivitas Pengguna Mingguan -->
    <div class="col-span-2 bg-[#111111] border border-[#1E2939] rounded-[14px] p-[21px]">
        <h3 class="text-base font-bold text-white mb-4" style="font-family: Arial, sans-serif;">Aktivitas Pengguna Mingguan</h3>
        <div class="flex items-end justify-between gap-2 h-48">
            <div class="flex flex-col items-center flex-1 gap-2">
                <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
                <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Sen</span>
            </div>
            <div class="flex flex-col items-center flex-1 gap-2">
                <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
                <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Sel</span>
            </div>
            <div class="flex flex-col items-center flex-1 gap-2">
                <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
                <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Rab</span>
            </div>
            <div class="flex flex-col items-center flex-1 gap-2">
                <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
                <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Kam</span>
            </div>
            <div class="flex flex-col items-center flex-1 gap-2">
                <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
                <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Jum</span>
            </div>
            <div class="flex flex-col items-center flex-1 gap-2">
                <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
                <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Sab</span>
            </div>
            <div class="flex flex-col items-center flex-1 gap-2">
                <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
                <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Min</span>
            </div>
        </div>
    </div>

    <!-- Status & Kontrol Cepat -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-[21px]">
        <h3 class="text-base font-bold text-white mb-4" style="font-family: Arial, sans-serif;">Status & Kontrol Cepat</h3>
        
        <!-- Status AI -->
        <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-[13px] mb-4">
            <div class="flex items-center justify-between">
                <span class="text-xs text-[#6A7282] uppercase tracking-wide" style="font-family: Arial, sans-serif; letter-spacing: 0.3px;">Status AI</span>
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 bg-[#6B7C4F] rounded-full"></div>
                    <span class="text-xs text-[#6B7C4F]" style="font-family: Arial, sans-serif;">Aktif</span>
                </div>
            </div>
        </div>

        <!-- Notifikasi Aktif -->
        <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-[13px] mb-2">
            <span class="text-xs text-[#6A7282] uppercase tracking-wide block mb-1" style="font-family: Arial, sans-serif; letter-spacing: 0.3px;">Notifikasi Aktif</span>
            <p class="text-2xl font-bold text-white" style="font-family: Arial, sans-serif;">8</p>
        </div>

        <!-- Action Buttons -->
        <div class="mt-2 space-y-2">
            <a href="{{ route('admin.ai-config') }}" class="block w-full h-10 bg-[#6B7C4F] rounded-[10px] flex items-center justify-center text-sm text-white" style="font-family: Arial, sans-serif;">
                Konfigurasi AI
            </a>
            <a href="{{ route('admin.templates') }}" class="block w-full h-10 bg-[#1A1A1A] border border-[#364153] rounded-[10px] flex items-center justify-center text-sm text-white" style="font-family: Arial, sans-serif;">
                Monitoring Tips Perawatan
            </a>
        </div>
    </div>
</div>

<!-- Pertumbuhan Tips Perawatan -->
<div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-[21px]">
    <h3 class="text-base font-bold text-white mb-4" style="font-family: Arial, sans-serif;">Pertumbuhan Tips Perawatan</h3>
    <div class="flex items-end justify-between gap-3 h-48">
        <div class="flex flex-col items-center flex-1 gap-2">
            <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
            <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Minggu 1</span>
        </div>
        <div class="flex flex-col items-center flex-1 gap-2">
            <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
            <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Minggu 2</span>
        </div>
        <div class="flex flex-col items-center flex-1 gap-2">
            <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
            <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Minggu 3</span>
        </div>
        <div class="flex flex-col items-center flex-1 gap-2">
            <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
            <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Minggu 4</span>
        </div>
        <div class="flex flex-col items-center flex-1 gap-2">
            <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
            <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Minggu 5</span>
        </div>
        <div class="flex flex-col items-center flex-1 gap-2">
            <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
            <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Minggu 6</span>
        </div>
        <div class="flex flex-col items-center flex-1 gap-2">
            <div class="w-full h-0 bg-[#6B7C4F] rounded-t"></div>
            <span class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Minggu 7</span>
        </div>
    </div>
</div>
@endsection
