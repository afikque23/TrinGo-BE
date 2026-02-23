<!-- Modal Tambah Template Notifikasi -->
<div x-cloak
     x-show="showAddModal" 
     @click.away="showAddModal = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px);">
    <div @click.stop class="bg-[#111111] border border-[#1E2939] rounded-[14px] w-full max-w-[672px] max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#1E2939]">
            <h3 class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">Tambah Template Notifikasi Baru</h3>
            <div class="flex items-center gap-2">
                <button type="button" @click="showVariableInfoModal = true" class="text-[#6B7C4F] hover:text-[#5A6A40] transition-colors" title="Info Variabel">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </button>
                <button type="button" @click="showAddModal = false" class="text-[#99A1AF] hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Content -->
        <form action="{{ route('admin.notifications.store') }}" method="POST" class="px-6 py-6 space-y-5">
            @csrf
            
            <!-- Nama Template Notifikasi -->
            <div class="flex flex-col gap-2">
                <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Nama Template Notifikasi</label>
                <input 
                    type="text" 
                    name="name"
                    placeholder="Contoh: Pengingat Ganti Oli"
                    required
                    class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-base placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] transition-colors"
                    style="font-family: Arial, sans-serif;">
            </div>

            <!-- Kategori -->
            <div class="flex flex-col gap-2">
                <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Kategori</label>
                <select 
                    name="category_key"
                    required
                    class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-base focus:outline-none focus:border-[#6B7C4F] transition-colors"
                    style="font-family: Arial, sans-serif;">
                    <option value="">Pilih Kategori</option>
                    <template x-for="category in categories" :key="category.key">
                        <option :value="category.key" x-text="category.name"></option>
                    </template>
                </select>
            </div>

            <!-- Trigger Type -->
            <div class="flex flex-col gap-2">
                <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Jenis Trigger</label>
                <input 
                    type="text" 
                    name="trigger_type"
                    placeholder="Contoh: km_before_interval, overdue, monthly_summary"
                    class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-base placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] transition-colors"
                    style="font-family: Arial, sans-serif;">
            </div>

            <!-- Prioritas & Channel (2 Columns) -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Prioritas -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Prioritas</label>
                    <select 
                        name="priority"
                        required
                        class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-base focus:outline-none focus:border-[#6B7C4F] transition-colors"
                        style="font-family: Arial, sans-serif;">
                        <option value="low">Rendah</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">Tinggi</option>
                        <option value="critical">Kritikal</option>
                    </select>
                </div>

                <!-- Channel -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Channel</label>
                    <select 
                        name="channel"
                        required
                        class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-base focus:outline-none focus:border-[#6B7C4F] transition-colors"
                        style="font-family: Arial, sans-serif;">
                        <option value="in_app" selected>In-App</option>
                        <option value="push">Push</option>
                        <option value="email">Email</option>
                    </select>
                </div>
            </div>

            <!-- Threshold (km) -->
            <div class="flex flex-col gap-2">
                <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Threshold (km atau hari)</label>
                <input 
                    type="number" 
                    name="threshold_value"
                    placeholder="0"
                    min="0"
                    class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-base placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] transition-colors"
                    style="font-family: Arial, sans-serif;">
            </div>

            <!-- Variabel yang Tersedia -->
            <div class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] p-[17px]">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-[6px] h-[6px] bg-[#6B7C4F] rounded-full"></div>
                    <span class="text-xs text-[#99A1AF] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.35px;">Variabel yang Tersedia (klik untuk salin):</span>
                </div>
                
                <div class="grid grid-cols-3 gap-x-4 gap-y-2">
                    @php
                    $quickVars = [
                        '{vehicle_name}', '{vehicle_plate}', '{vehicle_type}',
                        '{current_km}', '{vehicle_year}', '{vehicle_color}',
                        '{service_type}', '{service_name}', '{km_remaining}',
                        '{km_overdue}', '{target_km}', '{target_date}',
                        '{days_remaining}', '{last_service}', '{workshop_name}',
                        '{distance}', '{duration}', '{avg_speed}',
                        '{user_name}', '{app_name}', '{date_now}',
                    ];
                    @endphp
                    @foreach($quickVars as $qv)
                    <button type="button" @click="navigator.clipboard.writeText('{{ $qv }}'); $el.classList.add('text-[#6B7C4F]'); setTimeout(() => $el.classList.remove('text-[#6B7C4F]'), 1000)" class="text-xs text-[#6A7282] hover:text-white transition-colors text-left cursor-pointer" style="font-family: Consolas, monospace;" title="Klik untuk salin">{{ $qv }}</button>
                    @endforeach
                </div>
            </div>

            <!-- Isi Template Pesan -->
            <div class="flex flex-col gap-2">
                <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Isi Template Pesan</label>
                <textarea 
                    name="message_template"
                    rows="4" 
                    placeholder="Ketik template pesan dengan variabel di atas... Contoh: 🔧 Servis {service_type} dalam {km_remaining} km lagi!"
                    required
                    class="w-full px-4 py-3 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-base placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] transition-colors resize-none"
                    style="font-family: Arial, sans-serif;"></textarea>
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active_add" value="1" checked class="w-4 h-4 rounded border-[#364153] bg-[#0A0A0A] text-[#6B7C4F] focus:ring-[#6B7C4F]">
                <label for="is_active_add" class="text-sm text-white" style="font-family: Arial, sans-serif;">Aktifkan template ini</label>
            </div>

            <!-- Modal Footer - Buttons -->
            <div class="flex items-center gap-3 pt-1">
                <button 
                    type="submit"
                    class="flex-1 h-[46px] bg-[#6B7C4F] text-white text-base font-medium rounded-[10px] hover:bg-[#5A6A40] transition-colors flex items-center justify-center gap-2"
                    style="font-family: Arial, sans-serif;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Template
                </button>
                <button 
                    type="button"
                    @click="showAddModal = false"
                    class="px-6 h-[46px] bg-[#1A1A1A] border border-[#364153] text-white text-base rounded-[10px] hover:bg-[#2A2A2A] transition-colors"
                    style="font-family: Arial, sans-serif;">
                    Batal
                </button>
            </div>

        </form>
    </div>
</div>
