<!-- Modal Tambah Aturan -->
<div id="modalTambahAturan" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-[#111111] border border-gray-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <!-- Header -->
            <h3 class="text-xl font-bold text-white mb-5">Tambah Aturan Insight Baru</h3>

            <!-- Form -->
            <form class="space-y-4">
                <!-- Nama Aturan -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-300">Nama Aturan</label>
                    <input type="text" placeholder="Contoh: Servis Sangat Mendesak" class="w-full px-4 py-3 bg-black border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-[#6B7C4F]">
                </div>

                <!-- Parameter -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-300">Parameter</label>
                    <div class="relative">
                        <select style="-webkit-appearance:none; -moz-appearance:none; appearance:none;" class="w-full px-4 py-3 pr-10 bg-black border border-gray-700 rounded-lg text-white focus:outline-none focus:border-[#6B7C4F]">
                        <option value="">Pilih Parameter</option>
                        <option value="km_remaining">km_remaining (Sisa KM ke Servis)</option>
                        <option value="avg_daily_km">avg_daily_km (Rata-rata KM Harian)</option>
                        <option value="avg_speed">avg_speed (Kecepatan Rata-rata)</option>
                        <option value="traffic_level">traffic_level (Tingkat Kemacetan)</option>
                        <option value="road_condition">road_condition (Kondisi Jalan)</option>
                        <option value="days_since_service">days_since_service (Hari sejak servis)</option>
                        <option value="total_week_km">total_week_km (Total KM Minggu Ini)</option>
                        <option value="current_km">current_km (Odometer Saat Ini)</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>

                <!-- Operator & Nilai -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-300">Operator</label>
                        <div class="relative">
                            <select style="-webkit-appearance:none; -moz-appearance:none; appearance:none;" class="w-full px-4 py-3 pr-10 bg-black border border-gray-700 rounded-lg text-white focus:outline-none focus:border-[#6B7C4F]">
                            <option value="">Pilih Operator</option>
                            <option value="<">&lt; Lebih Kecil</option>
                            <option value=">">&gt; Lebih Besar</option>
                            <option value="<=">&lt;= Lebih Kecil Sama Dengan</option>
                            <option value=">=">&gt;= Lebih Besar Sama Dengan</option>
                            <option value="==">== Sama Dengan</option>
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-300">Nilai</label>
                        <input type="number" placeholder="0" class="w-full px-4 py-3 bg-black border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-[#6B7C4F]">
                    </div>
                </div>

                <!-- Level & Kategori -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-300">Level</label>
                        <div class="relative">
                            <select style="-webkit-appearance:none; -moz-appearance:none; appearance:none;" class="w-full px-4 py-3 pr-10 bg-black border border-gray-700 rounded-lg text-white focus:outline-none focus:border-[#6B7C4F]">
                            <option value="">Pilih Level</option>
                            <option value="info">Info (Saran/Rekomendasi)</option>
                            <option value="warning">Warning (Peringatan)</option>
                            <option value="critical">Critical (Urgent)</option>
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-300">Kategori</label>
                        <div class="relative">
                            <select style="-webkit-appearance:none; -moz-appearance:none; appearance:none;" class="w-full px-4 py-3 pr-10 bg-black border border-gray-700 rounded-lg text-white focus:outline-none focus:border-[#6B7C4F]">
                            <option value="">Pilih Kategori</option>
                            <option value="service">Service</option>
                            <option value="riding">Riding</option>
                            <option value="system">System</option>
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Toggle Aktifkan -->
                <div class="flex items-center justify-between px-4 py-3.5 bg-black border border-gray-700 rounded-lg">
                    <span class="text-sm font-bold text-gray-300">Aktifkan Aturan</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-12 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#6B7C4F]"></div>
                    </label>
                </div>

                <!-- Contoh Logika -->
                <div class="p-4 bg-[#6B7C4F]/10 border border-[#6B7C4F]/30 rounded-lg space-y-2">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#6B7C4F]">Contoh Logik:</p>
                    <p class="text-sm font-mono text-white">IF Sisa KM ke Servis &lt; 0 → tampilkan Insight '[Nama Aturan]'</p>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('modalTambahAturan')" class="flex-1 px-6 py-3 bg-[#2A2A2A] hover:bg-[#3A3A3A] text-white rounded-lg font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 px-6 py-3 bg-[#6B7C4F] hover:bg-[#5a6a42] text-white rounded-lg font-bold shadow-lg transition-colors">
                        Simpan Aturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
