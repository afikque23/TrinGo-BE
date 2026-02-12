{{-- Modal Hapus Opsi Pengingat --}}
<div x-show="showDeleteReminderModal"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true"
     @click.away="showDeleteReminderModal = false"
     style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);">
    <div @click.stop class="bg-transparent w-full max-w-lg">
        <!-- Modal panel (ensure it's above overlay) -->
        <div class="inline-block relative z-60 overflow-hidden text-left align-bottom transition-all transform bg-[#0A0A0A] border border-[#1E2939] rounded-[14px] shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <!-- Modal Header -->
            <div class="px-6 pt-5 pb-4 border-b border-[#1E2939]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white" style="font-family: Arial, sans-serif;">
                        Konfirmasi Hapus
                    </h3>
                    <button @click="showDeleteReminderModal = false" class="text-[#99A1AF] hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-5">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-[#FB2C36]/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#FB2C36]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-base font-medium text-white mb-2" style="font-family: Arial, sans-serif;">
                            Apakah Anda yakin ingin menghapus opsi pengingat ini?
                        </h4>
                        <div class="space-y-2">
                            <p class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                                Opsi: <span class="text-white font-medium" x-text="selectedOption ? selectedOption.label : ''"></span>
                            </p>
                            <div class="p-3 bg-[#111111] border border-[#1E2939] rounded-[10px]">
                                <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                                    ⚠️ <strong class="text-white">Peringatan:</strong> Data yang sudah dihapus tidak dapat dikembalikan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-[#111111] border-t border-[#1E2939] flex justify-end gap-3">
                <button type="button" 
                        @click="showDeleteReminderModal = false"
                        class="px-4 py-2 text-sm font-medium text-[#99A1AF] bg-[#1A1A1A] border border-[#364153] rounded-[10px] hover:bg-[#2A2A2A] transition-colors" 
                        style="font-family: Arial, sans-serif;">
                    Batal
                </button>
                <form method="POST" :action="`{{ url('admin/filters/reminder-options') }}/${selectedOption ? selectedOption.id : ''}`" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-[#FB2C36] rounded-[10px] hover:bg-[#E01B25] transition-colors" 
                            style="font-family: Arial, sans-serif;">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
