<!-- Modal Edit Kategori Notifikasi -->
<div x-show="showEditCategoryModal" x-cloak class="fixed inset-0 overflow-y-auto z-[9999]" style="background-color: rgba(0, 0, 0, 0.75);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div @click.away="showEditCategoryModal = false" class="bg-[#0E0E0E] border border-[#364153] rounded-[14px] w-full max-w-md overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#1E2939]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#6B7C4F]/20 rounded-[10px] flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#6B7C4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">Edit Kategori Notifikasi</h3>
                        <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Perbarui informasi kategori</p>
                    </div>
                </div>
                <button @click="showEditCategoryModal = false" class="text-[#6A7282] hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form x-bind:action="selectedCategory ? '{{ url('admin/filters/notification-categories') }}/' + selectedCategory.id : '#'" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <!-- Nama Kategori -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2" style="font-family: Arial, sans-serif;">
                            Nama Kategori <span class="text-[#FB2C36]">*</span>
                        </label>
                        <input type="text" name="name" required x-model="selectedCategory.name"
                            class="w-full px-4 py-2.5 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-white text-sm focus:border-[#6B7C4F] focus:ring-1 focus:ring-[#6B7C4F] outline-none transition-colors"
                            style="font-family: Arial, sans-serif;"
                            placeholder="Contoh: Servis, Perjalanan, Peringatan">
                    </div>

                    <!-- Key -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2" style="font-family: Arial, sans-serif;">
                            Key (Unique) <span class="text-[#FB2C36]">*</span>
                        </label>
                        <input type="text" name="key" required pattern="[a-z_]+" x-model="selectedCategory.key"
                            class="w-full px-4 py-2.5 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-white text-sm focus:border-[#6B7C4F] focus:ring-1 focus:ring-[#6B7C4F] outline-none transition-colors"
                            style="font-family: Consolas, monospace;"
                            placeholder="contoh: service, trip, alert">
                        <p class="text-xs text-[#6A7282] mt-1" style="font-family: Arial, sans-serif;">Gunakan lowercase dan underscore saja (service, trip, alert)</p>
                    </div>

                    <!-- Icon -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2" style="font-family: Arial, sans-serif;">
                            Icon
                        </label>
                        <input type="text" name="icon" x-model="selectedCategory.icon"
                            class="w-full px-4 py-2.5 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-white text-sm focus:border-[#6B7C4F] focus:ring-1 focus:ring-[#6B7C4F] outline-none transition-colors"
                            style="font-family: Arial, sans-serif;"
                            placeholder="Nama icon (opsional)">
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2" style="font-family: Arial, sans-serif;">
                            Color
                        </label>
                        <div class="flex gap-2">
                            <input type="color" name="color" x-model="selectedCategory.color"
                                class="w-14 h-11 bg-[#1A1A1A] border border-[#364153] rounded-[10px] cursor-pointer">
                            <input type="text" x-model="selectedCategory.color"
                                class="flex-1 px-4 py-2.5 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-white text-sm focus:border-[#6B7C4F] focus:ring-1 focus:ring-[#6B7C4F] outline-none transition-colors"
                                style="font-family: Consolas, monospace;"
                                placeholder="#6B7C4F">
                        </div>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2" style="font-family: Arial, sans-serif;">
                            Urutan <span class="text-[#FB2C36]">*</span>
                        </label>
                        <input type="number" name="sort_order" required min="1" x-model="selectedCategory.sort_order"
                            class="w-full px-4 py-2.5 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-white text-sm focus:border-[#6B7C4F] focus:ring-1 focus:ring-[#6B7C4F] outline-none transition-colors"
                            style="font-family: Arial, sans-serif;">
                        <p class="text-xs text-[#6A7282] mt-1" style="font-family: Arial, sans-serif;">Angka kecil akan tampil lebih dulu</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" x-model="selectedCategory.is_active"
                                class="w-4 h-4 bg-[#1A1A1A] border border-[#364153] rounded text-[#6B7C4F] focus:ring-[#6B7C4F] focus:ring-offset-0">
                            <span class="text-sm text-white" style="font-family: Arial, sans-serif;">Aktif</span>
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center gap-3 mt-6 pt-6 border-t border-[#1E2939]">
                    <button type="button" @click="showEditCategoryModal = false"
                        class="flex-1 h-11 px-4 bg-[#1A1A1A] border border-[#364153] text-white text-sm rounded-[10px] hover:bg-[#2A2A2A] transition-colors"
                        style="font-family: Arial, sans-serif;">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 h-11 px-4 bg-[#6B7C4F] text-white text-sm rounded-[10px] hover:bg-[#5A6A40] transition-colors"
                        style="font-family: Arial, sans-serif;">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
