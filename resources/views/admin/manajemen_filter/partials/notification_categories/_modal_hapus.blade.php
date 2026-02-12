<!-- Modal Hapus Kategori Notifikasi -->
<div x-show="showDeleteCategoryModal" x-cloak class="fixed inset-0 overflow-y-auto z-[9999]" style="background-color: rgba(0, 0, 0, 0.75);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div @click.away="showDeleteCategoryModal = false" class="bg-[#0E0E0E] border border-[#364153] rounded-[14px] w-full max-w-md overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#1E2939]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#FB2C36]/20 rounded-[10px] flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#FB2C36]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">Hapus Kategori Notifikasi</h3>
                        <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Konfirmasi penghapusan kategori</p>
                    </div>
                </div>
                <button @click="showDeleteCategoryModal = false" class="text-[#6A7282] hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div class="flex items-start gap-4 mb-6">
                    <div class="flex-shrink-0 w-12 h-12 bg-[#FB2C36]/10 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#FB2C36]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-white mb-2" style="font-family: Arial, sans-serif;">
                            Anda yakin ingin menghapus kategori notifikasi:
                        </p>
                        <div class="bg-[#1A1A1A] border border-[#364153] rounded-[10px] p-3 mb-3">
                            <p class="text-base font-bold text-white mb-1" style="font-family: Arial, sans-serif;" x-text="selectedCategory?.name"></p>
                            <p class="text-xs text-[#6A7282]" style="font-family: Consolas, monospace;">
                                Key: <span x-text="selectedCategory?.key"></span>
                            </p>
                        </div>
                        <div class="bg-[#FB2C36]/10 border border-[#FB2C36]/30 rounded-[10px] p-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-[#FB2C36] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-xs text-[#FB2C36]" style="font-family: Arial, sans-serif;">
                                    Peringatan: Semua template notifikasi dan notifikasi terkait kategori ini akan terpengaruh. Tindakan ini tidak dapat dibatalkan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <form x-bind:action="selectedCategory ? '{{ url('admin/filters/notification-categories') }}/' + selectedCategory.id : '#'" method="POST">
                    @csrf
                    @method('DELETE')

                    <!-- Modal Footer -->
                    <div class="flex items-center gap-3 pt-6 border-t border-[#1E2939]">
                        <button type="button" @click="showDeleteCategoryModal = false"
                            class="flex-1 h-11 px-4 bg-[#1A1A1A] border border-[#364153] text-white text-sm rounded-[10px] hover:bg-[#2A2A2A] transition-colors"
                            style="font-family: Arial, sans-serif;">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 h-11 px-4 bg-[#FB2C36] text-white text-sm rounded-[10px] hover:bg-[#E01B25] transition-colors"
                            style="font-family: Arial, sans-serif;">
                            Ya, Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
