{{-- Modal Tambah Opsi Pengingat --}}
<div x-show="showAddReminderModal"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true"
     @click.away="showAddReminderModal = false"
     style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);">
    <div @click.stop class="bg-transparent w-full max-w-lg">
        <!-- Modal panel (ensure it's above overlay) -->
        <div class="inline-block relative z-60 overflow-hidden text-left align-bottom transition-all transform bg-[#0A0A0A] border border-[#1E2939] rounded-[14px] shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <!-- Modal Header -->
            <div class="px-6 pt-5 pb-4 border-b border-[#1E2939]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white" style="font-family: Arial, sans-serif;">
                        Tambah Opsi Pengingat
                    </h3>
                    <button @click="showAddReminderModal = false" class="text-[#99A1AF] hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form method="POST" action="{{ route('admin.filters.reminder-options.store') }}">
                @csrf
                <div class="px-6 py-5">
                    <div class="space-y-4">
                        <!-- Label -->
                        <div>
                            <label for="label" class="block text-sm font-medium text-white mb-2" style="font-family: Arial, sans-serif;">
                                Label <span class="text-[#FB2C36]">*</span>
                            </label>
                            <input type="text" 
                                   name="label" 
                                   id="label" 
                                   required
                                   placeholder="Contoh: 100 km sebelum servis"
                                   class="w-full px-3 py-2.5 bg-[#111111] border border-[#364153] rounded-[10px] text-white placeholder-[#4A5565] focus:outline-none focus:border-[#6B7C4F] text-sm" 
                                   style="font-family: Arial, sans-serif;">
                            <p class="mt-1 text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                                Label yang akan ditampilkan ke user
                            </p>
                        </div>

                        <!-- Value -->
                        <div>
                            <label for="value" class="block text-sm font-medium text-white mb-2" style="font-family: Arial, sans-serif;">
                                Nilai <span class="text-[#FB2C36]">*</span>
                            </label>
                            <input type="number" 
                                   name="value" 
                                   id="value" 
                                   required
                                   min="1"
                                   max="999999"
                                   placeholder="Contoh: 100"
                                   class="w-full px-3 py-2.5 bg-[#111111] border border-[#364153] rounded-[10px] text-white placeholder-[#4A5565] focus:outline-none focus:border-[#6B7C4F] text-sm" 
                                   style="font-family: Arial, sans-serif;">
                            <p class="mt-1 text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                                Nilai numerik (1-999999)
                            </p>
                        </div>

                        <!-- Unit -->
                        <div>
                            <label for="unit" class="block text-sm font-medium text-white mb-2" style="font-family: Arial, sans-serif;">
                                Unit <span class="text-[#FB2C36]">*</span>
                            </label>
                            <select name="unit" 
                                    id="unit" 
                                    required
                                    class="w-full px-3 py-2.5 bg-[#111111] border border-[#364153] rounded-[10px] text-white focus:outline-none focus:border-[#6B7C4F] text-sm" 
                                    style="font-family: Arial, sans-serif;">
                                <option value="">-- Pilih Unit --</option>
                                <optgroup label="Jarak">
                                    <option value="km">Kilometer (km)</option>
                                </optgroup>
                                <optgroup label="Waktu">
                                    <option value="days">Hari</option>
                                    <option value="weeks">Minggu</option>
                                    <option value="months">Bulan</option>
                                    <option value="years">Tahun</option>
                                </optgroup>
                            </select>
                            <p class="mt-1 text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                                Pilih unit pengingat (jarak atau waktu)
                            </p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" 
                                       name="is_active" 
                                       checked
                                       class="w-4 h-4 text-[#6B7C4F] bg-[#111111] border-[#364153] rounded focus:ring-[#6B7C4F] focus:ring-2">
                                <span class="text-sm text-white" style="font-family: Arial, sans-serif;">Aktifkan opsi pengingat ini</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-[#111111] border-t border-[#1E2939] flex justify-end gap-3">
                    <button type="button" 
                            @click="showAddReminderModal = false"
                            class="px-4 py-2 text-sm font-medium text-[#99A1AF] bg-[#1A1A1A] border border-[#364153] rounded-[10px] hover:bg-[#2A2A2A] transition-colors" 
                            style="font-family: Arial, sans-serif;">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-[#6B7C4F] rounded-[10px] hover:bg-[#5A6A40] transition-colors" 
                            style="font-family: Arial, sans-serif;">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
