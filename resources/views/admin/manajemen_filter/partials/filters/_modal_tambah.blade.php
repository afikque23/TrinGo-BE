<!-- Modal Tambah Filter -->
<div x-show="showAddModal" 
     x-cloak
     @click.away="showAddModal = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px);">
    <div @click.stop class="bg-[#111111] border border-[#1E2939] rounded-[14px] w-full max-w-[896px] max-h-[90vh] overflow-y-auto relative">
        <!-- Modal Header -->
        <div class="sticky top-0 z-30 flex items-center justify-between px-6 py-4 bg-[#0A0A0A] border-b border-[#1E2939]">
            <h3 class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">Tambah Filter Baru</h3>
            <button @click="showAddModal = false" class="text-[#99A1AF] hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Content -->
        <form action="{{ route('admin.filters.store') }}" method="POST" class="p-6">
            @csrf
            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Informasi Dasar -->
                <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-[17px]">
                    <h4 class="text-sm font-bold text-white mb-4" style="font-family: Arial, sans-serif;">Informasi Dasar</h4>
                    
                    <div class="space-y-3">
                        <!-- Nama Filter -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Nama Filter</label>
                            <input type="text" name="name" placeholder="Contoh: Jenis Motor" class="h-[38px] bg-[#111111] border border-[#364153] rounded-[10px] px-3 text-sm text-white placeholder:text-white/50 focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                        </div>

                        <!-- Deskripsi -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Deskripsi</label>
                            <textarea name="description" rows="3" placeholder="Jelaskan fungsi filter ini..." class="bg-[#111111] border border-[#364153] rounded-[10px] px-3 py-2 text-sm text-white placeholder:text-white/50 focus:outline-none focus:border-[#6B7C4F] resize-none" style="font-family: Arial, sans-serif;"></textarea>
                        </div>

                        <!-- Modul Terkait -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Modul Terkait</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="modules[]" value="template" class="w-4 h-4 bg-[#111111] border border-[#364153] rounded text-[#6B7C4F] focus:ring-[#6B7C4F]">
                                    <span class="text-sm text-white" style="font-family: Arial, sans-serif;">Template</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="modules[]" value="komunitas" class="w-4 h-4 bg-[#111111] border border-[#364153] rounded text-[#6B7C4F] focus:ring-[#6B7C4F]">
                                    <span class="text-sm text-white" style="font-family: Arial, sans-serif;">Tips Perawatan</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="modules[]" value="ai" class="w-4 h-4 bg-[#111111] border border-[#364153] rounded text-[#6B7C4F] focus:ring-[#6B7C4F]">
                                    <span class="text-sm text-white" style="font-family: Arial, sans-serif;">AI</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Konfigurasi Input -->
                <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-[17px]">
                    <h4 class="text-sm font-bold text-white mb-4" style="font-family: Arial, sans-serif;">Konfigurasi Input</h4>
                    
                    <div class="space-y-3">
                        <!-- Jenis Input -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Jenis Input</label>
                            <select name="type" class="h-[38px] bg-[#111111] border border-[#364153] rounded-[10px] px-3 text-sm text-white focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                                <option value="">Pilih jenis input</option>
                                <option value="dropdown">Dropdown</option>
                                <option value="multi-select">Multi-select</option>
                                <option value="range">Range</option>
                                <option value="toggle">Toggle</option>
                            </select>
                        </div>

                        <!-- Placeholder -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Placeholder</label>
                            <input type="text" name="placeholder" placeholder="Teks placeholder" class="h-[38px] bg-[#111111] border border-[#364153] rounded-[10px] px-3 text-sm text-white placeholder:text-white/50 focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                        </div>

                        <!-- Urutan Tampilan -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Urutan Tampilan</label>
                            <input type="number" name="order" placeholder="1" class="h-[38px] bg-[#111111] border border-[#364153] rounded-[10px] px-3 text-sm text-white placeholder:text-white/50 focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                        </div>

                        <!-- Status Default -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Status Default</label>
                            <select name="status" class="h-[38px] bg-[#111111] border border-[#364153] rounded-[10px] px-3 text-sm text-white focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Opsi Nilai Section -->
            <div x-data="{ options: [{ label: '', value: '', order: 1 }], addOption() { this.options.push({ label: '', value: '', order: this.options.length + 1 }) }, removeOption(i) { this.options.splice(i,1); this.options.forEach((o,idx)=> o.order = idx+1) } }" class="bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-[17px] mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-white" style="font-family: Arial, sans-serif;">Opsi Nilai</h4>
                    <button type="button" @click.prevent="addOption()" class="h-7 px-3 bg-[#6B7C4F] text-white text-xs rounded-[10px] hover:bg-[#5A6A40] transition-colors flex items-center gap-1.5" style="font-family: Arial, sans-serif;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Opsi
                    </button>
                </div>

                <template x-for="(opt, idx) in options" :key="idx">
                    <div class="flex items-center gap-2 mb-2">
                        <input :name="`options[${idx}][label]`" x-model="opt.label" type="text" placeholder="Label" class="flex-1 h-[38px] bg-[#111111] border border-[#364153] rounded-[10px] px-3 text-sm text-white placeholder:text-white/50 focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                        <input :name="`options[${idx}][value]`" x-model="opt.value" type="text" placeholder="Value" class="flex-1 h-[38px] bg-[#111111] border border-[#364153] rounded-[10px] px-3 text-sm text-white placeholder:text-white/50 focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                        <input :name="`options[${idx}][order]`" x-model.number="opt.order" type="number" min="1" placeholder="1" class="w-20 h-[38px] bg-[#111111] border border-[#364153] rounded-[10px] px-3 text-sm text-white placeholder:text-white/50 focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                        <button type="button" @click.prevent="removeOption(idx)" class="p-2 bg-[#FB2C36] rounded-[10px] hover:bg-[#E01B25] transition-colors">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#1E2939]">
                <button type="button" @click="showAddModal = false" class="px-4 h-[38px] bg-[#1A1A1A] border border-[#364153] text-white text-sm rounded-[10px] hover:bg-[#2A2A2A] transition-colors" style="font-family: Arial, sans-serif;">
                    Batal
                </button>
                <button type="submit" class="px-4 h-9 bg-[#6B7C4F] text-white text-sm rounded-[10px] hover:bg-[#5A6A40] transition-colors flex items-center gap-2" style="font-family: Arial, sans-serif;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
