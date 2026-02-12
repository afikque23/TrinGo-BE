<!-- Tab Template Pesan Content -->
<div class="flex flex-col gap-4 w-full">
    <!-- Header Section -->
    <div class="flex flex-row justify-between items-center gap-4">
        <p class="text-sm text-[#99A1AF]">Edit template pesan AI dengan variabel dinamis untuk personalisasi</p>
        
        <button onclick="openModal('modalTambahTemplate')" class="flex items-center gap-2 px-5 py-2.5 bg-[#6B7C4F] hover:bg-[#5a6a42] rounded-lg font-bold text-white shadow-lg transition-colors">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 5V15M5 10H15" stroke="currentColor" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Tambah Template
        </button>
    </div>

    <!-- Template Cards List -->
    <div class="flex flex-col gap-3">
        
        <!-- Template 1: Pesan Servis Mendesak -->
        <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-xl p-5 flex flex-col gap-3">
            <!-- Card Header -->
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-1 flex-1">
                    <h4 class="text-base font-bold text-white">Pesan Servis Mendesak</h4>
                    <p class="text-xs text-[#6A7282]">Kategori: Service • Untuk: Servis Mendesak</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 bg-[rgba(0,201,80,0.2)] border border-[rgba(0,201,80,0.3)] rounded text-xs text-[#05DF72]">Aktif</span>
                    
                    <button onclick="openModalEditTemplate('Pesan Servis Mendesak')" class="w-8 h-8 bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-[#364153] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-[#99A1AF]" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.3337 2.00004C11.5089 1.82494 11.7169 1.68605 11.9457 1.59129C12.1745 1.49653 12.4197 1.44775 12.667 1.44775C12.9144 1.44775 13.1596 1.49653 13.3884 1.59129C13.6172 1.68605 13.8252 1.82494 14.0003 2.00004C14.1754 2.17513 14.3143 2.38311 14.4091 2.61195C14.5038 2.84079 14.5526 3.08595 14.5526 3.33337C14.5526 3.5808 14.5038 3.82596 14.4091 4.0548C14.3143 4.28364 14.1754 4.49162 14.0003 4.66671L5.00033 13.6667L1.33366 14.6667L2.33366 11L11.3337 2.00004Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    
                    <button onclick="openModalHapusTemplate('Pesan Servis Mendesak')" class="w-8 h-8 bg-[#E7000B] hover:bg-[#c50009] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 4H3.33333H14" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.33301 3.99996V2.66663C5.33301 2.31296 5.47348 1.97382 5.72353 1.72378C5.97358 1.47373 6.31272 1.33325 6.66634 1.33325H9.33301C9.68663 1.33325 10.0258 1.47373 10.2758 1.72378C10.5259 1.97382 10.6663 2.31296 10.6663 2.66663V3.99996M12.6663 3.99996V13.3333C12.6663 13.6869 12.5259 14.0261 12.2758 14.2761C12.0258 14.5262 11.6866 14.6666 11.333 14.6666H4.66634C4.31272 14.6666 3.97358 14.5262 3.72353 14.2761C3.47348 14.0261 3.33301 13.6869 3.33301 13.3333V3.99996H12.6663Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Template Message -->
            <div class="bg-[#111111] border border-[#1E2939] rounded-lg p-3">
                <p class="text-sm text-[#D1D5DC] leading-5">Motor Anda sudah melewati interval servis yang direkomendasikan</p>
            </div>
            
            <!-- Variables -->
            <div class="flex flex-wrap gap-2">
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{current_km}</code>
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{km_remaining}</code>
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{last_service_km}</code>
            </div>
        </div>

        <!-- Template 2: Pesan Waktu untuk Servis -->
        <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-xl p-5 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-1 flex-1">
                    <h4 class="text-base font-bold text-white">Pesan Waktu untuk Servis</h4>
                    <p class="text-xs text-[#6A7282]">Kategori: Service • Untuk: Waktu untuk Servis</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 bg-[rgba(0,201,80,0.2)] border border-[rgba(0,201,80,0.3)] rounded text-xs text-[#05DF72]">Aktif</span>
                    
                    <button onclick="openModalEditTemplate('Pesan Waktu untuk Servis')" class="w-8 h-8 bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-[#364153] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-[#99A1AF]" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.3337 2.00004C11.5089 1.82494 11.7169 1.68605 11.9457 1.59129C12.1745 1.49653 12.4197 1.44775 12.667 1.44775C12.9144 1.44775 13.1596 1.49653 13.3884 1.59129C13.6172 1.68605 13.8252 1.82494 14.0003 2.00004C14.1754 2.17513 14.3143 2.38311 14.4091 2.61195C14.5038 2.84079 14.5526 3.08595 14.5526 3.33337C14.5526 3.5808 14.5038 3.82596 14.4091 4.0548C14.3143 4.28364 14.1754 4.49162 14.0003 4.66671L5.00033 13.6667L1.33366 14.6667L2.33366 11L11.3337 2.00004Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    
                    <button onclick="openModalHapusTemplate('Pesan Waktu untuk Servis')" class="w-8 h-8 bg-[#E7000B] hover:bg-[#c50009] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 4H3.33333H14" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.33301 3.99996V2.66663C5.33301 2.31296 5.47348 1.97382 5.72353 1.72378C5.97358 1.47373 6.31272 1.33325 6.66634 1.33325H9.33301C9.68663 1.33325 10.0258 1.47373 10.2758 1.72378C10.5259 1.97382 10.6663 2.31296 10.6663 2.66663V3.99996M12.6663 3.99996V13.3333C12.6663 13.6869 12.5259 14.0261 12.2758 14.2761C12.0258 14.5262 11.6866 14.6666 11.333 14.6666H4.66634C4.31272 14.6666 3.97358 14.5262 3.72353 14.2761C3.47348 14.0261 3.33301 13.6869 3.33301 13.3333V3.99996H12.6663Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="bg-[#111111] border border-[#1E2939] rounded-lg p-3">
                <p class="text-sm text-[#D1D5DC] leading-5">Hanya {km_remaining} km lagi menuju servis berikutnya. Rencanakan kunjungan Anda!</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{km_remaining}</code>
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{current_km}</code>
            </div>
        </div>

        <!-- Template 3: Pesan Penggunaan Intensif -->
        <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-xl p-5 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-1 flex-1">
                    <h4 class="text-base font-bold text-white">Pesan Penggunaan Intensif</h4>
                    <p class="text-xs text-[#6A7282]">Kategori: Riding Pattern • Untuk: Penggunaan Intensif</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 bg-[rgba(0,201,80,0.2)] border border-[rgba(0,201,80,0.3)] rounded text-xs text-[#05DF72]">Aktif</span>
                    
                    <button onclick="openModalEditTemplate('Pesan Penggunaan Intensif')" class="w-8 h-8 bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-[#364153] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-[#99A1AF]" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.3337 2.00004C11.5089 1.82494 11.7169 1.68605 11.9457 1.59129C12.1745 1.49653 12.4197 1.44775 12.667 1.44775C12.9144 1.44775 13.1596 1.49653 13.3884 1.59129C13.6172 1.68605 13.8252 1.82494 14.0003 2.00004C14.1754 2.17513 14.3143 2.38311 14.4091 2.61195C14.5038 2.84079 14.5526 3.08595 14.5526 3.33337C14.5526 3.5808 14.5038 3.82596 14.4091 4.0548C14.3143 4.28364 14.1754 4.49162 14.0003 4.66671L5.00033 13.6667L1.33366 14.6667L2.33366 11L11.3337 2.00004Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    
                    <button onclick="openModalHapusTemplate('Pesan Penggunaan Intensif')" class="w-8 h-8 bg-[#E7000B] hover:bg-[#c50009] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 4H3.33333H14" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.33301 3.99996V2.66663C5.33301 2.31296 5.47348 1.97382 5.72353 1.72378C5.97358 1.47373 6.31272 1.33325 6.66634 1.33325H9.33301C9.68663 1.33325 10.0258 1.47373 10.2758 1.72378C10.5259 1.97382 10.6663 2.31296 10.6663 2.66663V3.99996M12.6663 3.99996V13.3333C12.6663 13.6869 12.5259 14.0261 12.2758 14.2761C12.0258 14.5262 11.6866 14.6666 11.333 14.6666H4.66634C4.31272 14.6666 3.97358 14.5262 3.72353 14.2761C3.47348 14.0261 3.33301 13.6869 3.33301 13.3333V3.99996H12.6663Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="bg-[#111111] border border-[#1E2939] rounded-lg p-3">
                <p class="text-sm text-[#D1D5DC] leading-5">Anda berkendara rata-rata {avg_daily_km} km/hari. Cek komponen motor lebih sering!</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{avg_daily_km}</code>
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{total_week_km}</code>
            </div>
        </div>

        <!-- Template 4: Pesan Aktivitas Rendah -->
        <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-xl p-5 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-1 flex-1">
                    <h4 class="text-base font-bold text-white">Pesan Aktivitas Rendah</h4>
                    <p class="text-xs text-[#6A7282]">Kategori: Riding Pattern • Untuk: Aktivitas Rendah</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 bg-[rgba(0,201,80,0.2)] border border-[rgba(0,201,80,0.3)] rounded text-xs text-[#05DF72]">Aktif</span>
                    
                    <button onclick="openModalEditTemplate('Pesan Aktivitas Rendah')" class="w-8 h-8 bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-[#364153] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-[#99A1AF]" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.3337 2.00004C11.5089 1.82494 11.7169 1.68605 11.9457 1.59129C12.1745 1.49653 12.4197 1.44775 12.667 1.44775C12.9144 1.44775 13.1596 1.49653 13.3884 1.59129C13.6172 1.68605 13.8252 1.82494 14.0003 2.00004C14.1754 2.17513 14.3143 2.38311 14.4091 2.61195C14.5038 2.84079 14.5526 3.08595 14.5526 3.33337C14.5526 3.5808 14.5038 3.82596 14.4091 4.0548C14.3143 4.28364 14.1754 4.49162 14.0003 4.66671L5.00033 13.6667L1.33366 14.6667L2.33366 11L11.3337 2.00004Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    
                    <button onclick="openModalHapusTemplate('Pesan Aktivitas Rendah')" class="w-8 h-8 bg-[#E7000B] hover:bg-[#c50009] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 4H3.33333H14" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.33301 3.99996V2.66663C5.33301 2.31296 5.47348 1.97382 5.72353 1.72378C5.97358 1.47373 6.31272 1.33325 6.66634 1.33325H9.33301C9.68663 1.33325 10.0258 1.47373 10.2758 1.72378C10.5259 1.97382 10.6663 2.31296 10.6663 2.66663V3.99996M12.6663 3.99996V13.3333C12.6663 13.6869 12.5259 14.0261 12.2758 14.2761C12.0258 14.5262 11.6866 14.6666 11.333 14.6666H4.66634C4.31272 14.6666 3.97358 14.5262 3.72353 14.2761C3.47348 14.0261 3.33301 13.6869 3.33301 13.3333V3.99996H12.6663Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="bg-[#111111] border border-[#1E2939] rounded-lg p-3">
                <p class="text-sm text-[#D1D5DC] leading-5">Motor Anda hanya digunakan {avg_daily_km} km/hari selama {days_inactive} hari</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{avg_daily_km}</code>
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{days_inactive}</code>
            </div>
        </div>

        <!-- Template 5: Pesan Cek Level Oli -->
        <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-xl p-5 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-1 flex-1">
                    <h4 class="text-base font-bold text-white">Pesan Cek Level Oli</h4>
                    <p class="text-xs text-[#6A7282]">Kategori: Service • Untuk: Cek Level Oli</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 bg-[rgba(0,201,80,0.2)] border border-[rgba(0,201,80,0.3)] rounded text-xs text-[#05DF72]">Aktif</span>
                    
                    <button onclick="openModalEditTemplate('Pesan Cek Level Oli')" class="w-8 h-8 bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-[#364153] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-[#99A1AF]" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.3337 2.00004C11.5089 1.82494 11.7169 1.68605 11.9457 1.59129C12.1745 1.49653 12.4197 1.44775 12.667 1.44775C12.9144 1.44775 13.1596 1.49653 13.3884 1.59129C13.6172 1.68605 13.8252 1.82494 14.0003 2.00004C14.1754 2.17513 14.3143 2.38311 14.4091 2.61195C14.5038 2.84079 14.5526 3.08595 14.5526 3.33337C14.5526 3.5808 14.5038 3.82596 14.4091 4.0548C14.3143 4.28364 14.1754 4.49162 14.0003 4.66671L5.00033 13.6667L1.33366 14.6667L2.33366 11L11.3337 2.00004Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    
                    <button onclick="openModalHapusTemplate('Pesan Cek Level Oli')" class="w-8 h-8 bg-[#E7000B] hover:bg-[#c50009] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 4H3.33333H14" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.33301 3.99996V2.66663C5.33301 2.31296 5.47348 1.97382 5.72353 1.72378C5.97358 1.47373 6.31272 1.33325 6.66634 1.33325H9.33301C9.68663 1.33325 10.0258 1.47373 10.2758 1.72378C10.5259 1.97382 10.6663 2.31296 10.6663 2.66663V3.99996M12.6663 3.99996V13.3333C12.6663 13.6869 12.5259 14.0261 12.2758 14.2761C12.0258 14.5262 11.6866 14.6666 11.333 14.6666H4.66634C4.31272 14.6666 3.97358 14.5262 3.72353 14.2761C3.47348 14.0261 3.33301 13.6869 3.33301 13.3333V3.99996H12.6663Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="bg-[#111111] border border-[#1E2939] rounded-lg p-3">
                <p class="text-sm text-[#D1D5DC] leading-5">Sudah {days_since_service} hari sejak servis terakhir pada {last_service_date}</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{days_since_service}</code>
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{last_service_date}</code>
            </div>
        </div>

        <!-- Template 6: Rekomendasi AI Komunitas -->
        <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-xl p-5 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-1 flex-1">
                    <h4 class="text-base font-bold text-white">Rekomendasi AI Komunitas</h4>
                    <p class="text-xs text-[#6A7282]">Kategori: Community • Untuk: Personalisasi Template</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 bg-[rgba(0,201,80,0.2)] border border-[rgba(0,201,80,0.3)] rounded text-xs text-[#05DF72]">Aktif</span>
                    
                    <button onclick="openModalEditTemplate('Rekomendasi AI Komunitas')" class="w-8 h-8 bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-[#364153] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-[#99A1AF]" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.3337 2.00004C11.5089 1.82494 11.7169 1.68605 11.9457 1.59129C12.1745 1.49653 12.4197 1.44775 12.667 1.44775C12.9144 1.44775 13.1596 1.49653 13.3884 1.59129C13.6172 1.68605 13.8252 1.82494 14.0003 2.00004C14.1754 2.17513 14.3143 2.38311 14.4091 2.61195C14.5038 2.84079 14.5526 3.08595 14.5526 3.33337C14.5526 3.5808 14.5038 3.82596 14.4091 4.0548C14.3143 4.28364 14.1754 4.49162 14.0003 4.66671L5.00033 13.6667L1.33366 14.6667L2.33366 11L11.3337 2.00004Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    
                    <button onclick="openModalHapusTemplate('Rekomendasi AI Komunitas')" class="w-8 h-8 bg-[#E7000B] hover:bg-[#c50009] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 4H3.33333H14" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.33301 3.99996V2.66663C5.33301 2.31296 5.47348 1.97382 5.72353 1.72378C5.97358 1.47373 6.31272 1.33325 6.66634 1.33325H9.33301C9.68663 1.33325 10.0258 1.47373 10.2758 1.72378C10.5259 1.97382 10.6663 2.31296 10.6663 2.66663V3.99996M12.6663 3.99996V13.3333C12.6663 13.6869 12.5259 14.0261 12.2758 14.2761C12.0258 14.5262 11.6866 14.6666 11.333 14.6666H4.66634C4.31272 14.6666 3.97358 14.5262 3.72353 14.2761C3.47348 14.0261 3.33301 13.6869 3.33301 13.3333V3.99996H12.6663Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="bg-[#111111] border border-[#1E2939] rounded-lg p-3">
                <p class="text-sm text-[#D1D5DC] leading-5">Berdasarkan pola berkendara Anda ({riding_style}), sistem AI merekomendasikan servis preventif untuk {motor_type}</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{riding_style}</code>
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{avg_daily_km}</code>
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{motor_type}</code>
            </div>
        </div>

        <!-- Template 7: Tren Mingguan Meningkat -->
        <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-xl p-5 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-1 flex-1">
                    <h4 class="text-base font-bold text-white">Tren Mingguan Meningkat</h4>
                    <p class="text-xs text-[#6A7282]">Kategori: System Insight • Untuk: Analisis Mingguan</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 bg-[rgba(0,201,80,0.2)] border border-[rgba(0,201,80,0.3)] rounded text-xs text-[#05DF72]">Aktif</span>
                    
                    <button onclick="openModalEditTemplate('Tren Mingguan Meningkat')" class="w-8 h-8 bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-[#364153] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-[#99A1AF]" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.3337 2.00004C11.5089 1.82494 11.7169 1.68605 11.9457 1.59129C12.1745 1.49653 12.4197 1.44775 12.667 1.44775C12.9144 1.44775 13.1596 1.49653 13.3884 1.59129C13.6172 1.68605 13.8252 1.82494 14.0003 2.00004C14.1754 2.17513 14.3143 2.38311 14.4091 2.61195C14.5038 2.84079 14.5526 3.08595 14.5526 3.33337C14.5526 3.5808 14.5038 3.82596 14.4091 4.0548C14.3143 4.28364 14.1754 4.49162 14.0003 4.66671L5.00033 13.6667L1.33366 14.6667L2.33366 11L11.3337 2.00004Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    
                    <button onclick="openModalHapusTemplate('Tren Mingguan Meningkat')" class="w-8 h-8 bg-[#E7000B] hover:bg-[#c50009] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 4H3.33333H14" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.33301 3.99996V2.66663C5.33301 2.31296 5.47348 1.97382 5.72353 1.72378C5.97358 1.47373 6.31272 1.33325 6.66634 1.33325H9.33301C9.68663 1.33325 10.0258 1.47373 10.2758 1.72378C10.5259 1.97382 10.6663 2.31296 10.6663 2.66663V3.99996M12.6663 3.99996V13.3333C12.6663 13.6869 12.5259 14.0261 12.2758 14.2761C12.0258 14.5262 11.6866 14.6666 11.333 14.6666H4.66634C4.31272 14.6666 3.97358 14.5262 3.72353 14.2761C3.47348 14.0261 3.33301 13.6869 3.33301 13.3333V3.99996H12.6663Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="bg-[#111111] border border-[#1E2939] rounded-lg p-3">
                <p class="text-sm text-[#D1D5DC] leading-5">Minggu ini Anda berkendara {total_week_km} km, naik {percentage_change}% dari minggu lalu ({prev_week_km} km)</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{total_week_km}</code>
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{percentage_change}</code>
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{prev_week_km}</code>
            </div>
        </div>

        <!-- Template 8: Penggunaan Moderat -->
        <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-xl p-5 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-1 flex-1">
                    <h4 class="text-base font-bold text-white">Penggunaan Moderat</h4>
                    <p class="text-xs text-[#6A7282]">Kategori: System Insight • Untuk: Penggunaan Moderat</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 bg-[rgba(0,201,80,0.2)] border border-[rgba(0,201,80,0.3)] rounded text-xs text-[#05DF72]">Aktif</span>
                    
                    <button onclick="openModalEditTemplate('Penggunaan Moderat')" class="w-8 h-8 bg-[#2A2A2A] hover:bg-[#3A3A3A] border border-[#364153] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-[#99A1AF]" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.3337 2.00004C11.5089 1.82494 11.7169 1.68605 11.9457 1.59129C12.1745 1.49653 12.4197 1.44775 12.667 1.44775C12.9144 1.44775 13.1596 1.49653 13.3884 1.59129C13.6172 1.68605 13.8252 1.82494 14.0003 2.00004C14.1754 2.17513 14.3143 2.38311 14.4091 2.61195C14.5038 2.84079 14.5526 3.08595 14.5526 3.33337C14.5526 3.5808 14.5038 3.82596 14.4091 4.0548C14.3143 4.28364 14.1754 4.49162 14.0003 4.66671L5.00033 13.6667L1.33366 14.6667L2.33366 11L11.3337 2.00004Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    
                    <button onclick="openModalHapusTemplate('Penggunaan Moderat')" class="w-8 h-8 bg-[#E7000B] hover:bg-[#c50009] rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 4H3.33333H14" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.33301 3.99996V2.66663C5.33301 2.31296 5.47348 1.97382 5.72353 1.72378C5.97358 1.47373 6.31272 1.33325 6.66634 1.33325H9.33301C9.68663 1.33325 10.0258 1.47373 10.2758 1.72378C10.5259 1.97382 10.6663 2.31296 10.6663 2.66663V3.99996M12.6663 3.99996V13.3333C12.6663 13.6869 12.5259 14.0261 12.2758 14.2761C12.0258 14.5262 11.6866 14.6666 11.333 14.6666H4.66634C4.31272 14.6666 3.97358 14.5262 3.72353 14.2761C3.47348 14.0261 3.33301 13.6869 3.33301 13.3333V3.99996H12.6663Z" stroke="currentColor" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="bg-[#111111] border border-[#1E2939] rounded-lg p-3">
                <p class="text-sm text-[#D1D5DC] leading-5">Pola penggunaan stabil. Sistem merekomendasikan pemeriksaan rutin setiap {total_week_km} km</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{avg_daily_km}</code>
                <code class="px-2 py-1 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-xs font-mono text-[#6B7C4F]">{total_week_km}</code>
            </div>
        </div>

    </div>

    <!-- Save Button -->
    <div class="flex justify-end pt-6">
        <button class="flex items-center gap-2 px-6 py-3 bg-[#6B7C4F] hover:bg-[#5a6a42] rounded-lg font-bold text-white shadow-lg transition-colors">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16.6667 5L7.50004 14.1667L3.33337 10" stroke="currentColor" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Simpan Semua Perubahan
        </button>
    </div>
</div>
