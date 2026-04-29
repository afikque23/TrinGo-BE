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
                    <h3 class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">Panduan Template Notifikasi</h3>
                    <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Variabel dan trigger type yang tersedia</p>
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
                        <p class="text-sm text-white font-semibold mb-1" style="font-family: Arial, sans-serif;">Panduan Lengkap</p>
                        <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                            <strong>Variabel</strong>: Klik untuk salin, lalu tempelkan ke template pesan. Otomatis diganti dengan data real saat notifikasi dikirim.
                            <br><strong>Trigger Type</strong>: Kode unik yang menentukan kapan notifikasi dipicu dari backend secara otomatis.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Variables Grid -->
            <div class="space-y-3">
                <!-- Header -->
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex-1 h-[1px] bg-[#1E2939]"></div>
                    <h3 class="text-sm font-bold text-white px-3" style="font-family: Arial, sans-serif;">📝 Variabel Template</h3>
                    <div class="flex-1 h-[1px] bg-[#1E2939]"></div>
                </div>
                
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

                <!-- Section: Jenis Trigger -->
                <div class="pt-3 border-t-2 border-[#1E2939]">
                    <!-- Header -->
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex-1 h-[1px] bg-[#1E2939]"></div>
                        <h3 class="text-sm font-bold text-white px-3" style="font-family: Arial, sans-serif;">⚡ Jenis Trigger</h3>
                        <div class="flex-1 h-[1px] bg-[#1E2939]"></div>
                    </div>
                    
                    <p class="text-xs text-[#6A7282] mb-4" style="font-family: Arial, sans-serif;">
                        Trigger type adalah kode unik untuk memicu notifikasi otomatis dari backend. Pilih yang sesuai dengan kebutuhan template Anda.
                    </p>
                    
                    <!-- Service Triggers -->
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-semibold text-white px-2 py-1 bg-[#6B7C4F]/20 rounded" style="font-family: Arial, sans-serif;">Service</span>
                        </div>
                        <div class="grid grid-cols-1 gap-2">
                            @php
                            $serviceTriggers = [
                                ['km_before_interval', 'Pengingat otomatis X km sebelum jadwal servis'],
                                ['time_before_interval', 'Pengingat otomatis X hari sebelum tanggal servis'],
                                ['service_completed', 'Konfirmasi setelah user mencatat servis selesai'],
                                ['schedule_reminder_km', 'Custom reminder manual yang diset user berdasarkan KM'],
                                ['schedule_reminder_time', 'Custom reminder manual yang diset user berdasarkan hari'],
                            ];
                            @endphp
                            @foreach($serviceTriggers as $trigger)
                            <div class="bg-[#0A0A0A] border border-[#364153] rounded-[8px] p-3 hover:border-[#6B7C4F] transition-colors">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <code class="text-xs text-[#6B7C4F] font-mono bg-[#1A1A1A] px-2 py-0.5 rounded" style="font-family: Consolas, monospace;">{{ $trigger[0] }}</code>
                                    <button @click="navigator.clipboard.writeText('{{ $trigger[0] }}'); $el.innerHTML = '✓'; setTimeout(() => $el.innerHTML = '📋', 1000)" class="text-xs hover:scale-110 transition-transform" title="Salin">📋</button>
                                </div>
                                <p class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">{{ $trigger[1] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Alert Triggers -->
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-semibold text-white px-2 py-1 bg-[#FB2C36]/20 rounded" style="font-family: Arial, sans-serif;">Alert</span>
                        </div>
                        <div class="grid grid-cols-1 gap-2">
                            @php
                            $alertTriggers = [
                                ['overdue', 'Peringatan saat motor melewati jadwal servis'],
                                ['high_odometer', 'Peringatan saat odometer mencapai angka tertentu (misal 50.000 km)'],
                            ];
                            @endphp
                            @foreach($alertTriggers as $trigger)
                            <div class="bg-[#0A0A0A] border border-[#364153] rounded-[8px] p-3 hover:border-[#6B7C4F] transition-colors">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <code class="text-xs text-[#6B7C4F] font-mono bg-[#1A1A1A] px-2 py-0.5 rounded" style="font-family: Consolas, monospace;">{{ $trigger[0] }}</code>
                                    <button @click="navigator.clipboard.writeText('{{ $trigger[0] }}'); $el.innerHTML = '✓'; setTimeout(() => $el.innerHTML = '📋', 1000)" class="text-xs hover:scale-110 transition-transform" title="Salin">📋</button>
                                </div>
                                <p class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">{{ $trigger[1] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Trip Triggers -->
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-semibold text-white px-2 py-1 bg-[#3B82F6]/20 rounded" style="font-family: Arial, sans-serif;">Trip</span>
                        </div>
                        <div class="grid grid-cols-1 gap-2">
                            @php
                            $tripTriggers = [
                                ['trip_completed', 'Notifikasi setelah user menyelesaikan perjalanan'],
                            ];
                            @endphp
                            @foreach($tripTriggers as $trigger)
                            <div class="bg-[#0A0A0A] border border-[#364153] rounded-[8px] p-3 hover:border-[#6B7C4F] transition-colors">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <code class="text-xs text-[#6B7C4F] font-mono bg-[#1A1A1A] px-2 py-0.5 rounded" style="font-family: Consolas, monospace;">{{ $trigger[0] }}</code>
                                    <button @click="navigator.clipboard.writeText('{{ $trigger[0] }}'); $el.innerHTML = '✓'; setTimeout(() => $el.innerHTML = '📋', 1000)" class="text-xs hover:scale-110 transition-transform" title="Salin">📋</button>
                                </div>
                                <p class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">{{ $trigger[1] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Insight Triggers -->
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-semibold text-white px-2 py-1 bg-[#8B5CF6]/20 rounded" style="font-family: Arial, sans-serif;">Insight</span>
                        </div>
                        <div class="grid grid-cols-1 gap-2">
                            @php
                            $insightTriggers = [
                                ['service_insight', 'Rekomendasi servis berdasarkan pola berkendara user'],
                            ];
                            @endphp
                            @foreach($insightTriggers as $trigger)
                            <div class="bg-[#0A0A0A] border border-[#364153] rounded-[8px] p-3 hover:border-[#6B7C4F] transition-colors">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <code class="text-xs text-[#6B7C4F] font-mono bg-[#1A1A1A] px-2 py-0.5 rounded" style="font-family: Consolas, monospace;">{{ $trigger[0] }}</code>
                                    <button @click="navigator.clipboard.writeText('{{ $trigger[0] }}'); $el.innerHTML = '✓'; setTimeout(() => $el.innerHTML = '📋', 1000)" class="text-xs hover:scale-110 transition-transform" title="Salin">📋</button>
                                </div>
                                <p class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">{{ $trigger[1] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Custom Trigger Info -->
                    <div class="bg-[#0A0A0A] border border-[#6B7C4F]/30 rounded-[10px] p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#6B7C4F] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <div>
                                <p class="text-xs text-white font-semibold mb-1" style="font-family: Arial, sans-serif;">Membuat Trigger Custom</p>
                                <p class="text-xs text-[#6A7282] mb-2" style="font-family: Arial, sans-serif;">
                                    Anda bisa membuat trigger type baru dengan format: <code class="text-[#6B7C4F] bg-[#1A1A1A] px-1 rounded" style="font-family: Consolas, monospace;">kategori_aksi</code>
                                </p>
                                <p class="text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">
                                    Contoh: <code class="text-[#6B7C4F] bg-[#1A1A1A] px-1 rounded" style="font-family: Consolas, monospace;">tire_change_reminder</code>, <code class="text-[#6B7C4F] bg-[#1A1A1A] px-1 rounded" style="font-family: Consolas, monospace;">battery_check</code>, <code class="text-[#6B7C4F] bg-[#1A1A1A] px-1 rounded" style="font-family: Consolas, monospace;">monthly_summary</code>
                                </p>
                                <p class="text-xs text-[#FB2C36] mt-2" style="font-family: Arial, sans-serif;">
                                    ⚠️ Pastikan backend sudah dikonfigurasi untuk mengenali trigger custom Anda!
                                </p>
                            </div>
                        </div>
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
