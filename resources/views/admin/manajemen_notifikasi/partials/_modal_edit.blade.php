<!-- Modal Edit Notifikasi -->
<div x-cloak
     x-show="showEditModal" 
     @click.away="showEditModal = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px);">
    <div @click.stop class="bg-[#111111] border border-[#1E2939] rounded-[14px] w-full max-w-[878px] max-h-[90vh] overflow-y-auto relative">
        <!-- Modal Header with Title & Metadata -->
        <div class="sticky top-0 z-30 flex items-center justify-between px-[21px] pt-[21px] pb-4 bg-[#111111] border-b border-[#1E2939]">
            <div class="flex-1">
                <h3 class="text-base font-bold text-white mb-1" style="font-family: Arial, sans-serif;" x-text="selectedNotification ? selectedNotification.name : ''"></h3>
                <template x-if="selectedNotification">
                    <div class="flex items-center gap-3 text-xs" style="font-family: Arial, sans-serif;">
                        <span class="text-[#6A7282]" x-text="'Trigger: ' + selectedNotification.trigger"></span>
                        <span class="text-[#6A7282]">•</span>
                        <span :style="'color: ' + selectedNotification.priorityColor" x-text="selectedNotification.priority"></span>
                        <span class="text-[#6A7282]">•</span>
                        <span class="text-[#6A7282]" x-text="selectedNotification.channels"></span>
                    </div>
                </template>
            </div>
            <div class="flex items-center gap-3 ml-4">
                <button class="h-[38px] px-4 bg-[#1A1A1A] border border-[#364153] text-white text-sm rounded-[10px] hover:bg-[#2A2A2A] transition-colors flex items-center gap-2" style="font-family: Arial, sans-serif;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    Test Push
                </button>
                <button class="w-12 h-6 bg-[#6B7C4F] rounded-full relative flex items-center transition-colors">
                    <div class="absolute right-1 w-4 h-4 bg-white rounded-full"></div>
                </button>
                <button @click="showEditModal = false" class="text-[#99A1AF] hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Content -->
        <form @submit.prevent="showEditModal = false" class="px-[21px] pb-[21px]">
            <!-- Preview Pesan -->
            <div class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] p-[13px] mb-3 mt-4">
                <p class="text-xs text-[#6A7282] uppercase tracking-wider mb-1" style="font-family: Arial, sans-serif; letter-spacing: 0.3px;">Preview Pesan</p>
                <template x-if="selectedNotification">
                    <p class="text-sm text-white" style="font-family: Arial, sans-serif;" x-text="selectedNotification.message"></p>
                </template>
            </div>

            <!-- Form Fields Container -->
            <div class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] p-[17px] space-y-3">
                <!-- Threshold (KM) -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.3px;">Threshold (km)</label>
                    <input type="number" x-model="selectedNotification.threshold" class="h-[38px] bg-[#111111] border border-[#364153] rounded-[10px] px-3 text-sm text-white focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                </div>

                <!-- Isi Pesan -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.3px;">Isi Pesan</label>
                    <textarea x-model="selectedNotification.message" rows="3" class="bg-[#111111] border border-[#364153] rounded-[10px] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#6B7C4F] resize-none" style="font-family: Arial, sans-serif;"></textarea>
                </div>

                <!-- Channel -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs text-[#6A7282] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.3px;">Channel</label>
                    <select x-model="selectedNotification.channels" class="h-[38px] bg-[#111111] border border-[#364153] rounded-[10px] px-3 text-sm text-white focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                        <option value="Push + In-App">Push + In-App</option>
                        <option value="Push + Email">Push + Email</option>
                        <option value="In-App">In-App Only</option>
                    </select>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center gap-2 mt-4">
                <button type="submit" class="h-9 px-4 bg-[#6B7C4F] text-white text-sm rounded-[10px] hover:bg-[#5A6A40] transition-colors flex items-center gap-2" style="font-family: Arial, sans-serif;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan
                </button>
                <button type="button" @click="showEditModal = false" class="h-[38px] px-4 bg-[#1A1A1A] border border-[#364153] text-white text-sm rounded-[10px] hover:bg-[#2A2A2A] transition-colors" style="font-family: Arial, sans-serif;">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
