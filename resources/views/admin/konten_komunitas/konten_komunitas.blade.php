@extends('layouts.sidebar')

@section('title', 'Konten Komunitas - MotoTracker')
@section('page-title', 'Konten Komunitas')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-white mb-2">Monitoring Konten Komunitas</h1>
    <p class="text-gray-400">Supervisi konten publik yang dipublikasikan otomatis</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-[#111111] border border-gray-800 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">Upload Hari Ini</p>
                <p class="text-2xl font-bold text-white">47</p>
            </div>
            <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-[#111111] border border-gray-800 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">Auto Published</p>
                <p class="text-2xl font-bold text-white">42</p>
            </div>
            <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-[#111111] border border-gray-800 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">Flagged oleh Sistem</p>
                <p class="text-2xl font-bold text-white">2</p>
            </div>
            <div class="w-10 h-10 bg-yellow-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-[#111111] border border-gray-800 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">Konten Dihapus</p>
                <p class="text-2xl font-bold text-white">1</p>
            </div>
            <div class="w-10 h-10 bg-red-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="bg-[#111111] border border-gray-800 rounded-xl p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs uppercase tracking-wide text-gray-400 mb-2">Status Konten</label>
            <select class="w-full bg-black border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-green-600">
                <option>Semua Status</option>
                <option>Published</option>
                <option>Flagged</option>
                <option>Hidden</option>
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide text-gray-400 mb-2">Motor</label>
            <select class="w-full bg-black border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-green-600">
                <option>Semua Brand</option>
                <option>Honda</option>
                <option>Yamaha</option>
                <option>Suzuki</option>
                <option>Kawasaki</option>
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide text-gray-400 mb-2">Rentang Tanggal</label>
            <select class="w-full bg-black border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-green-600">
                <option>7 Hari Terakhir</option>
                <option>30 Hari Terakhir</option>
                <option>90 Hari Terakhir</option>
                <option>Semua Waktu</option>
            </select>
        </div>
    </div>
</div>

<!-- Content Table -->
<div class="bg-[#111111] border border-gray-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-black border-b border-gray-800">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Judul Konten</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Author</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Motor Terkait</th>
                    <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Like</th>
                    <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Rule Triggered</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Tanggal Upload</th>
                </tr>
            </thead>
            <tbody class="bg-[#111111] divide-y divide-gray-800">
                <tr class="hover:bg-[#6B7C4F]/20 cursor-pointer" data-href="{{ route('admin.konten.detail', 1) }}">
                    <td class="px-6 py-4">
                        <p class="text-white font-medium mb-1">Cara Ganti Oli Motor Matic Honda</p>
                        <p class="text-xs text-gray-400">Perawatan Rutin</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-white">Budi Santoso</p>
                        <p class="text-xs text-gray-400">USR-2024-001</p>
                    </td>
                    <td class="px-6 py-4 text-white">Honda PCX 160</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span class="text-white">234</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                            Published
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">-</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1.5 text-gray-400 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>2024-02-06 14:23</span>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-[#6B7C4F]/20 cursor-pointer" data-href="{{ route('admin.konten.detail', 2) }}">
                    <td class="px-6 py-4">
                        <p class="text-white font-medium mb-1">Jual Spare Part Murah Kualitas Original!!!</p>
                        <p class="text-xs text-gray-400">Tips & Trik</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-white">Toko Jaya Motor</p>
                        <p class="text-xs text-gray-400">USR-2024-045</p>
                    </td>
                    <td class="px-6 py-4 text-white">Yamaha NMAX</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span class="text-white">12</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                            Flagged
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-xs text-gray-400">Spam Detection - Excessive Links & Promotional Key</p>
                        <p class="text-xs text-red-400">Severity: high</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1.5 text-gray-400 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>2024-02-07 08:45</span>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-[#6B7C4F]/20 cursor-pointer" data-href="{{ route('admin.konten.detail', 3) }}">
                    <td class="px-6 py-4">
                        <p class="text-white font-medium mb-1">Modifikasi ECU untuk Tenaga Maksimal</p>
                        <p class="text-xs text-gray-400">Modifikasi</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-white">Speed Lover</p>
                        <p class="text-xs text-gray-400">USR-2024-078</p>
                    </td>
                    <td class="px-6 py-4 text-white">Kawasaki Ninja 250</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span class="text-white">89</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                            Flagged
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-xs text-gray-400">Content Warning - Risky Modification</p>
                        <p class="text-xs text-yellow-400">Severity: medium</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1.5 text-gray-400 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>2024-02-06 16:50</span>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-[#6B7C4F]/20 cursor-pointer" data-href="{{ route('admin.konten.detail', 4) }}">
                    <td class="px-6 py-4">
                        <p class="text-white font-medium mb-1">Settingan Karburator untuk Irit BBM</p>
                        <p class="text-xs text-gray-400">Tips & Trik</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-white">Mekanik Pro</p>
                        <p class="text-xs text-gray-400">USR-2024-112</p>
                    </td>
                    <td class="px-6 py-4 text-white">Honda Beat</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span class="text-white">156</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                            Published
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">-</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1.5 text-gray-400 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>2024-02-05 11:20</span>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-[#6B7C4F]/20 cursor-pointer" data-href="{{ route('admin.konten.detail', 5) }}">
                    <td class="px-6 py-4">
                        <p class="text-white font-medium mb-1">Tips Merawat Rantai Motor Agar Awet</p>
                        <p class="text-xs text-gray-400">Perawatan Rutin</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-white">Motor Enthusiast</p>
                        <p class="text-xs text-gray-400">USR-2024-134</p>
                    </td>
                    <td class="px-6 py-4 text-white">Suzuki GSX-R150</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span class="text-white">189</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                            Published
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">-</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1.5 text-gray-400 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>2024-02-07 10:15</span>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-[#6B7C4F]/20 cursor-pointer" data-href="{{ route('admin.konten.detail', 6) }}">
                    <td class="px-6 py-4">
                        <p class="text-white font-medium mb-1">jual helm murah cek link</p>
                        <p class="text-xs text-gray-400">Tips & Trik</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-white">Helm Store</p>
                        <p class="text-xs text-gray-400">USR-2024-167</p>
                    </td>
                    <td class="px-6 py-4 text-white">Honda Vario</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span class="text-white">3</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-500/20 text-red-400 border border-red-500/30">
                            Hidden
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-xs text-gray-400">Auto-Hide - Multiple Links & Low Quality Content</p>
                        <p class="text-xs text-red-400">Severity: high</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1.5 text-gray-400 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>2024-02-08 09:30</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Handle clickable table rows
    document.querySelectorAll('tr[data-href]').forEach(row => {
        row.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
    });
</script>
@endsection
