<!-- Info Banner -->
<div class="mb-4 p-4 bg-[#6B7C4F]/10 border border-[#6B7C4F]/30 rounded-lg flex items-start gap-3">
    <svg class="w-5 h-5 text-[#6B7C4F] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <div>
        <p class="text-white text-sm font-medium">Sistem Rule-Based</p>
        <p class="text-gray-300 text-xs mt-1">Sistem menggunakan metode rule-based. Insight akan muncul otomatis saat kondisi terpenuhi</p>
    </div>
</div>

<!-- Action Bar -->
<div class="flex items-center justify-between mb-4">
    <p class="text-gray-400 text-sm">Kelola aturan yang menentukan kapan insight AI ditampilkan kepada pengguna</p>
    <button onclick="openModal('modalTambahAturan')" class="flex items-center gap-2 px-5 py-2.5 bg-[#6B7C4F] hover:bg-[#5a6a42] text-white rounded-lg font-bold text-sm shadow-lg transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Tambah Aturan
    </button>
</div>

<!-- Rules Table -->
<div class="bg-black border border-gray-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-[#111111] border-b border-gray-800">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Nama Aturan</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Parameter</th>
                    <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Operator</th>
                    <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Nilai</th>
                    <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Level</th>
                    <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Kategori</th>
                    <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-[#111111] divide-y divide-gray-800">
                <!-- Row 1 -->
                <tr class="hover:bg-[#6B7C4F]/20">
                    <td class="px-6 py-4">
                        <p class="text-white text-sm">Servis Mendesak</p>
                    </td>
                    <td class="px-6 py-4">
                        <code class="px-2.5 py-1.5 bg-[#1A1A1A] border border-gray-700 rounded text-xs text-gray-400 font-mono">km_remaining</code>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-[#6B7C4F] font-bold text-lg">&lt;</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-white font-bold">500</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30">
                            Critical
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-gray-400 text-sm capitalize">service</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-12 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#6B7C4F]"></div>
                            </label>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button class="w-8 h-8 flex items-center justify-center bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                            <button class="w-8 h-8 flex items-center justify-center bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2 -->
                <tr class="hover:bg-[#6B7C4F]/20">
                    <td class="px-6 py-4">
                        <p class="text-white text-sm">Waktu untuk Servis</p>
                    </td>
                    <td class="px-6 py-4">
                        <code class="px-2.5 py-1.5 bg-[#1A1A1A] border border-gray-700 rounded text-xs text-gray-400 font-mono">km_remaining</code>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-[#6B7C4F] font-bold text-lg">&lt;</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-white font-bold">1000</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                            Warning
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-gray-400 text-sm capitalize">service</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-12 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#6B7C4F]"></div>
                            </label>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="openModal('modalEditAturan')" class="w-8 h-8 flex items-center justify-center bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                            <button onclick="openModalHapus('Waktu untuk Servis')" class="w-8 h-8 flex items-center justify-center bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Row 3 -->
                <tr class="hover:bg-[#6B7C4F]/20">
                    <td class="px-6 py-4">
                        <p class="text-white text-sm">Penggunaan Intensif</p>
                    </td>
                    <td class="px-6 py-4">
                        <code class="px-2.5 py-1.5 bg-[#1A1A1A] border border-gray-700 rounded text-xs text-gray-400 font-mono">avg_daily_km</code>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-[#6B7C4F] font-bold text-lg">&gt;</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-white font-bold">50</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                            Info
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-gray-400 text-sm capitalize">riding</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-12 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#6B7C4F]"></div>
                            </label>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="openModal('modalEditAturan')" class="w-8 h-8 flex items-center justify-center bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                            <button onclick="openModalHapus('Penggunaan Intensif')" class="w-8 h-8 flex items-center justify-center bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Row 4 -->
                <tr class="hover:bg-[#6B7C4F]/20">
                    <td class="px-6 py-4">
                        <p class="text-white text-sm">Aktivitas Rendah</p>
                    </td>
                    <td class="px-6 py-4">
                        <code class="px-2.5 py-1.5 bg-[#1A1A1A] border border-gray-700 rounded text-xs text-gray-400 font-mono">avg_daily_km</code>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-[#6B7C4F] font-bold text-lg">&lt;</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-white font-bold">10</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                            Info
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-gray-400 text-sm capitalize">riding</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-12 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#6B7C4F]"></div>
                            </label>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="openModal('modalEditAturan')" class="w-8 h-8 flex items-center justify-center bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                            <button onclick="openModalHapus('Aktivitas Rendah')" class="w-8 h-8 flex items-center justify-center bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Row 5 -->
                <tr class="hover:bg-[#6B7C4F]/20">
                    <td class="px-6 py-4">
                        <p class="text-white text-sm">Cek Level Oli</p>
                    </td>
                    <td class="px-6 py-4">
                        <code class="px-2.5 py-1.5 bg-[#1A1A1A] border border-gray-700 rounded text-xs text-gray-400 font-mono">days_since_service</code>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-[#6B7C4F] font-bold text-lg">&gt;</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-white font-bold">60</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                            Warning
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-gray-400 text-sm capitalize">service</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-12 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#6B7C4F]"></div>
                            </label>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="openModal('modalEditAturan')" class="w-8 h-8 flex items-center justify-center bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                            <button onclick="openModalHapus('Cek Level Oli')" class="w-8 h-8 flex items-center justify-center bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Row 6 -->
                <tr class="hover:bg-[#6B7C4F]/20">
                    <td class="px-6 py-4">
                        <p class="text-white text-sm">Penggunaan Moderat</p>
                    </td>
                    <td class="px-6 py-4">
                        <code class="px-2.5 py-1.5 bg-[#1A1A1A] border border-gray-700 rounded text-xs text-gray-400 font-mono">avg_daily_km</code>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-[#6B7C4F] font-bold text-lg">&lt;</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-white font-bold">15</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                            Info
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-gray-400 text-sm capitalize">system</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-12 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#6B7C4F]"></div>
                            </label>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="openModal('modalEditAturan')" class="w-8 h-8 flex items-center justify-center bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                            <button onclick="openModalHapus('Penggunaan Moderat')" class="w-8 h-8 flex items-center justify-center bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Save Button -->
<div class="flex justify-end mt-6">
    <button class="flex items-center gap-2 px-8 py-3 bg-[#6B7C4F] hover:bg-[#5a6a42] text-white rounded-lg font-bold shadow-lg transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
        </svg>
        Simpan Semua Perubahan
    </button>
</div>
