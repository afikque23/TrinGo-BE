<!-- Modal Hapus Template -->
<div id="modalHapusTemplate" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-[#111111] border border-gray-800 rounded-2xl w-full max-w-md">
        <div class="p-6">
            <!-- Icon & Title -->
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-16 h-16 bg-red-600/20 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Hapus Template</h3>
                <p class="text-gray-400 text-sm mb-1">Apakah Anda yakin ingin menghapus template:</p>
                <p id="namaTemplateHapus" class="text-white font-bold text-base">"Pesan Servis Mendesak"</p>
            </div>

            <!-- Warning Message -->
            <div class="bg-red-900/20 border border-red-900/30 rounded-lg p-4 mb-6">
                <p class="text-sm text-red-400">
                    <strong>Peringatan:</strong> Template yang dihapus tidak dapat dikembalikan. Pastikan template ini tidak sedang digunakan oleh aturan aktif.
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button 
                    type="button" 
                    onclick="closeModal('modalHapusTemplate')" 
                    class="flex-1 px-6 py-3 bg-[#2A2A2A] hover:bg-[#3A3A3A] text-white rounded-lg font-bold transition-colors"
                >
                    Batal
                </button>
                <button 
                    type="button" 
                    onclick="confirmHapusTemplate()" 
                    class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold transition-colors"
                >
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>
