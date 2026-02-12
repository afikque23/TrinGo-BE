<!-- Modal Edit Jenis Service -->
<div x-show="showEditServiceModal" 
     x-cloak
     @click.away="showEditServiceModal = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px);">
    <div @click.stop class="bg-[#111111] border border-[#1E2939] rounded-[14px] w-full max-w-md">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-[#1E2939]">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">Edit Jenis Service</h3>
                <button @click="showEditServiceModal = false" class="text-[#99A1AF] hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Content -->
        <form :action="`{{ url('admin/filters/service-types') }}/${selectedType ? selectedType.id : ''}`" method="POST" class="p-6">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <!-- Nama Jenis Service -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Nama Jenis Service <span class="text-[#FB2C36]">*</span></label>
                    <input type="text" name="name" :value="selectedType ? selectedType.name : ''" required placeholder="Contoh: Ganti Oli Mesin" class="h-[38px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 text-sm text-white placeholder:text-white/50 focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                </div>

                <!-- Deskripsi -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Deskripsi</label>
                    <textarea name="description" x-text="selectedType ? selectedType.description : ''" rows="3" placeholder="Jelaskan jenis service ini..." class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 py-2 text-sm text-white placeholder:text-white/50 focus:outline-none focus:border-[#6B7C4F] resize-none" style="font-family: Arial, sans-serif;"></textarea>
                </div>

                <!-- Status Aktif -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active_edit" :checked="selectedType && selectedType.is_active" class="w-4 h-4 bg-[#0A0A0A] border border-[#364153] rounded text-[#6B7C4F] focus:ring-[#6B7C4F]">
                    <label for="is_active_edit" class="text-sm text-white cursor-pointer" style="font-family: Arial, sans-serif;">Aktif</label>
                </div>

                <!-- Warning if used -->
                <template x-if="selectedType && selectedType.services_count > 0">
                    <div class="bg-[#3D2A1A] border border-[#5C4A2D] rounded-[10px] p-3 flex gap-2">
                        <svg class="w-5 h-5 text-[#F59E0B] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="text-xs text-[#F59E0B]" style="font-family: Arial, sans-serif;">
                            Jenis service ini digunakan oleh <span class="font-bold" x-text="selectedType.services_count"></span> data service.
                        </p>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-[#1E2939]">
                <button type="button" @click="showEditServiceModal = false" class="px-4 h-[38px] bg-[#1A1A1A] border border-[#364153] text-white text-sm rounded-[10px] hover:bg-[#2A2A2A] transition-colors" style="font-family: Arial, sans-serif;">
                    Batal
                </button>
                <button type="submit" class="px-4 h-9 bg-[#6B7C4F] text-white text-sm rounded-[10px] hover:bg-[#5A6A40] transition-colors flex items-center gap-2" style="font-family: Arial, sans-serif;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>