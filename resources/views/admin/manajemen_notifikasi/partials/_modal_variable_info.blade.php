<!-- Modal Info Variabel -->
<div x-cloak
     x-show="showVariableInfoModal" 
     @click.away="showVariableInfoModal = false"
    class="fixed inset-0 z-[60] flex items-center justify-center p-4"
    style="background: rgba(0, 0, 0, 0.45); backdrop-filter: blur(4px);">
    <div @click.stop class="bg-[#111111] border border-[#1E2939] rounded-[14px] w-full max-w-[700px] max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#1E2939]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#6B7C4F]/20 rounded-[10px] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#6B7C4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">Variabel Yang Tersedia</h3>
                    <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Gunakan variabel ini dalam template pesan notifikasi</p>
                </div>
            </div>
            <button @click="showVariableInfoModal = false" class="text-[#99A1AF] hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Content -->
        <div class="px-6 py-6">
            <!-- Info Card -->
            <div class="bg-[#6B7C4F]/10 border border-[#6B7C4F]/30 rounded-[10px] p-4 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-[#6B7C4F] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-white font-semibold mb-1" style="font-family: Arial, sans-serif;">Cara Penggunaan</p>
                        <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                            Salin variabel di bawah dan tempelkan ke dalam template pesan. Variabel akan otomatis diganti dengan data sebenarnya saat notifikasi dikirim ke pengguna Flutter.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Variables Grid -->
            <div class="space-y-3">
                <!-- Section: Kendaraan -->
                <div>
                    <h4 class="text-xs font-bold text-[#6B7C4F] uppercase tracking-wider mb-3" style="font-family: Arial, sans-serif; letter-spacing: 0.5px;">🏍️ Kendaraan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @php
                        $vehicleVars = [
                            ['{vehicle_name}', 'Nama kendaraan (contoh: Honda Beat 2023)'],
                            ['{vehicle_plate}', 'Plat nomor kendaraan'],
                            ['{vehicle_type}', 'Tipe motor (matic/manual/sport)'],
                            ['{current_km}', 'Kilometer odometer saat ini'],
                            ['{vehicle_color}', 'Warna kendaraan'],
                            ['{vehicle_year}', 'Tahun kendaraan'],
                        ];
                        @endphp
                        @foreach($vehicleVars as $var)
                        <div class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] p-3 hover:border-[#6B7C4F] transition-colors">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <code class="text-sm text-[#6B7C4F] font-mono bg-[#1A1A1A] px-2 py-1 rounded" style="font-family: Consolas, monospace;">{{ $var[0] }}</code>
                                <button @click="navigator.clipboard.writeText('{{ $var[0] }}'); $el.innerHTML = '✓'; setTimeout(() => $el.innerHTML = '📋', 1000)" class="text-xs hover:scale-110 transition-transform" title="Salin">📋</button>
                            </div>
                            <p class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">{{ $var[1] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Section: Servis -->
                <div class="pt-3 border-t border-[#1E2939]">
                    <h4 class="text-xs font-bold text-[#6B7C4F] uppercase tracking-wider mb-3" style="font-family: Arial, sans-serif; letter-spacing: 0.5px;">🔧 Servis & Jadwal</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @php
                        $serviceVars = [
                            ['{service_type}', 'Jenis servis (contoh: Ganti Oli)'],
                            ['{service_name}', 'Nama jadwal servis'],
                            ['{km_remaining}', 'KM tersisa sebelum waktunya servis'],
                            ['{km_overdue}', 'KM terlambat dari jadwal servis'],
                            ['{target_km}', 'Target KM untuk servis berikutnya'],
                            ['{target_date}', 'Tanggal target servis berikutnya'],
                            ['{days_remaining}', 'Hari tersisa sebelum jadwal servis'],
                            ['{last_service}', 'Tanggal servis terakhir'],
                            ['{workshop_name}', 'Nama bengkel terakhir'],
                        ];
                        @endphp
                        @foreach($serviceVars as $var)
                        <div class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] p-3 hover:border-[#6B7C4F] transition-colors">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <code class="text-sm text-[#6B7C4F] font-mono bg-[#1A1A1A] px-2 py-1 rounded" style="font-family: Consolas, monospace;">{{ $var[0] }}</code>
                                <button @click="navigator.clipboard.writeText('{{ $var[0] }}'); $el.innerHTML = '✓'; setTimeout(() => $el.innerHTML = '📋', 1000)" class="text-xs hover:scale-110 transition-transform" title="Salin">📋</button>
                            </div>
                            <p class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">{{ $var[1] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Section: Perjalanan -->
                <div class="pt-3 border-t border-[#1E2939]">
                    <h4 class="text-xs font-bold text-[#6B7C4F] uppercase tracking-wider mb-3" style="font-family: Arial, sans-serif; letter-spacing: 0.5px;">🗺️ Perjalanan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @php
                        $tripVars = [
                            ['{distance}', 'Jarak perjalanan (km)'],
                            ['{duration}', 'Durasi perjalanan'],
                            ['{avg_speed}', 'Kecepatan rata-rata (km/h)'],
                        ];
                        @endphp
                        @foreach($tripVars as $var)
                        <div class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] p-3 hover:border-[#6B7C4F] transition-colors">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <code class="text-sm text-[#6B7C4F] font-mono bg-[#1A1A1A] px-2 py-1 rounded" style="font-family: Consolas, monospace;">{{ $var[0] }}</code>
                                <button @click="navigator.clipboard.writeText('{{ $var[0] }}'); $el.innerHTML = '✓'; setTimeout(() => $el.innerHTML = '📋', 1000)" class="text-xs hover:scale-110 transition-transform" title="Salin">📋</button>
                            </div>
                            <p class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">{{ $var[1] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Section: Pengguna & Umum -->
                <div class="pt-3 border-t border-[#1E2939]">
                    <h4 class="text-xs font-bold text-[#6B7C4F] uppercase tracking-wider mb-3" style="font-family: Arial, sans-serif; letter-spacing: 0.5px;">👤 Pengguna & Umum</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @php
                        $generalVars = [
                            ['{user_name}', 'Nama pengguna'],
                            ['{app_name}', 'Nama aplikasi (MotoTracker)'],
                            ['{date_now}', 'Tanggal saat ini'],
                        ];
                        @endphp
                        @foreach($generalVars as $var)
                        <div class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] p-3 hover:border-[#6B7C4F] transition-colors">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <code class="text-sm text-[#6B7C4F] font-mono bg-[#1A1A1A] px-2 py-1 rounded" style="font-family: Consolas, monospace;">{{ $var[0] }}</code>
                                <button @click="navigator.clipboard.writeText('{{ $var[0] }}'); $el.innerHTML = '✓'; setTimeout(() => $el.innerHTML = '📋', 1000)" class="text-xs hover:scale-110 transition-transform" title="Salin">📋</button>
                            </div>
                            <p class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">{{ $var[1] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Example Section -->
            <div class="mt-6 pt-6 border-t border-[#1E2939]">
                <h4 class="text-sm font-bold text-white mb-3" style="font-family: Arial, sans-serif;">💡 Contoh Penggunaan</h4>
                <div class="space-y-3">
                    <div class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] p-4">
                        <p class="text-xs text-[#6A7282] mb-2" style="font-family: Arial, sans-serif;">Template Pengingat Servis:</p>
                        <code class="text-xs text-white block bg-[#1A1A1A] p-3 rounded-lg mb-3" style="font-family: Consolas, monospace; white-space: pre-wrap;">🔧 Halo {user_name}, {vehicle_name} perlu {service_type} dalam {km_remaining} km lagi (target: {target_km} km). Jadwalkan sekarang!</code>
                        <p class="text-xs text-[#6A7282] mb-2" style="font-family: Arial, sans-serif;">Hasil yang dikirim ke Flutter:</p>
                        <code class="text-xs text-[#6B7C4F] block bg-[#1A1A1A] p-3 rounded-lg" style="font-family: Consolas, monospace; white-space: pre-wrap;">🔧 Halo Budi Santoso, Honda Beat 2023 perlu Ganti Oli dalam 500 km lagi (target: 16.000 km). Jadwalkan sekarang!</code>
                    </div>
                    <div class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] p-4">
                        <p class="text-xs text-[#6A7282] mb-2" style="font-family: Arial, sans-serif;">Template Servis Terlambat:</p>
                        <code class="text-xs text-white block bg-[#1A1A1A] p-3 rounded-lg mb-3" style="font-family: Consolas, monospace; white-space: pre-wrap;">⚠️ {vehicle_name} sudah melewati jadwal {service_type} sebanyak {km_overdue} km! Segera servis di {workshop_name}.</code>
                        <p class="text-xs text-[#6A7282] mb-2" style="font-family: Arial, sans-serif;">Hasil:</p>
                        <code class="text-xs text-[#6B7C4F] block bg-[#1A1A1A] p-3 rounded-lg" style="font-family: Consolas, monospace; white-space: pre-wrap;">⚠️ Honda Beat 2023 sudah melewati jadwal Ganti Oli sebanyak 200 km! Segera servis di Bengkel AHASS Maju Jaya.</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-[#1E2939]">
            <button @click="showVariableInfoModal = false" class="w-full h-11 px-4 bg-[#6B7C4F] text-white text-sm rounded-[10px] hover:bg-[#5A6A40] transition-colors" style="font-family: Arial, sans-serif;">
                Mengerti
            </button>
        </div>
    </div>
</div>
