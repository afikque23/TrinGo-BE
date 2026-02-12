<!-- Modal Hapus Filter -->
<div x-show="showDeleteModal" 
     x-cloak
     @click.away="showDeleteModal = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px);">
    <div @click.stop class="bg-[#111111] border border-[#1E2939] rounded-[14px] w-full max-w-md">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-[#1E2939]">
            <h3 class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">Hapus Filter</h3>
        </div>

        <!-- Modal Content -->
        <div class="p-6">
            <div class="flex gap-4 mb-6">
                <div class="flex-shrink-0 w-12 h-12 bg-[#3D1A1A] rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#FB2C36]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-white mb-2" style="font-family: Arial, sans-serif;">
                        Apakah Anda yakin ingin menghapus filter <span class="font-bold" x-text="selectedFilter ? selectedFilter.name : ''"></span>?
                    </p>
                    <p class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                        Tindakan ini tidak dapat dibatalkan dan akan mempengaruhi semua modul yang menggunakan filter ini.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" @click="showDeleteModal = false" class="px-4 h-[38px] bg-[#1A1A1A] border border-[#364153] text-white text-sm rounded-[10px] hover:bg-[#2A2A2A] transition-colors" style="font-family: Arial, sans-serif;">
                    Batal
                </button>
                <form :action="`{{ url('admin/filters') }}/${selectedFilter ? selectedFilter.id : ''}`" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 h-[38px] bg-[#FB2C36] text-white text-sm rounded-[10px] hover:bg-[#E01B25] transition-colors" style="font-family: Arial, sans-serif;">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
