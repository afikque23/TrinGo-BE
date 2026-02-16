<!-- Modal Tambah Notifikasi -->
<div x-cloak
     x-show="showAddModal" 
     @click.away="showAddModal = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px);"
     x-data="{
    formData: {
        name: '',
        trigger: '',
        priority: 'Normal',
        channel: 'Push',
        threshold: 0,
        message: '',
        previewMessage: 'Isi template pesan akan tampil di sini...'
    },
    updatePreview() {
        if (this.formData.message) {
            this.formData.previewMessage = this.formData.message;
        } else {
            this.formData.previewMessage = 'Isi template pesan akan tampil di sini...';
        }
    },
    insertVariable(variable) {
        this.formData.message += variable;
        this.updatePreview();
    },
    resetForm() {
        this.formData = {
            name: '',
            trigger: '',
            priority: 'Normal',
            channel: 'Push',
            threshold: 0,
            message: '',
            previewMessage: 'Isi template pesan akan tampil di sini...'
        };
    }
}">
    <div @click.stop class="bg-[#111111] border border-[#1E2939] rounded-[14px] w-full max-w-[672px] max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#1E2939]">
                <h3 class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">Tambah Notifikasi Baru</h3>
                <button @click="showAddModal = false; resetForm()" class="text-[#99A1AF] hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <form @submit.prevent="showAddModal = false; resetForm()" class="px-6 py-6 space-y-5">
                
                <!-- Nama Notifikasi -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Nama Notifikasi</label>
                    <input 
                        type="text" 
                        x-model="formData.name"
                        placeholder="Contoh: Pengingat Ganti Ban"
                        class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-base placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] transition-colors"
                        style="font-family: Arial, sans-serif;">
                </div>

                <!-- Trigger / Pemicu -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Trigger / Pemicu</label>
                    <input 
                        type="text" 
                        x-model="formData.trigger"
                        placeholder="Contoh: 500 km sebelum interval"
                        class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-base placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] transition-colors"
                        style="font-family: Arial, sans-serif;">
                </div>

                <!-- Prioritas & Channel (2 Columns) -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Prioritas -->
                    <div class="flex flex-col gap-2">
                        <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Prioritas</label>
                        <select 
                            x-model="formData.priority"
                            class="w-full h-[45.5px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-base focus:outline-none focus:border-[#6B7C4F] transition-colors"
                            style="font-family: Arial, sans-serif;">
                            <option value="Low">Low</option>
                            <option value="Normal">Normal</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>

                    <!-- Channel -->
                    <div class="flex flex-col gap-2">
                        <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Channel</label>
                        <select 
                            x-model="formData.channel"
                            class="w-full h-[45.5px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-base focus:outline-none focus:border-[#6B7C4F] transition-colors"
                            style="font-family: Arial, sans-serif;">
                            <option value="Push">Push</option>
                            <option value="In-App">In-App</option>
                            <option value="Push + In-App">Push + In-App</option>
                            <option value="Push + Email">Push + Email</option>
                        </select>
                    </div>
                </div>

                <!-- Threshold (km) -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Threshold (km)</label>
                    <input 
                        type="number" 
                        x-model.number="formData.threshold"
                        placeholder="0"
                        class="w-full h-[46px] px-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-base placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] transition-colors"
                        style="font-family: Arial, sans-serif;">
                </div>

                <!-- Variabel yang Tersedia -->
                <div class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] p-[17px]">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-[6px] h-[6px] bg-[#6B7C4F] rounded-full"></div>
                        <span class="text-xs text-[#99A1AF] uppercase tracking-wider" style="font-family: Arial, sans-serif; letter-spacing: 0.35px;">Variabel yang Tersedia:</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-x-8 gap-y-2">
                        <button type="button" @click="insertVariable('{current_km}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{current_km}</button>
                        <button type="button" @click="insertVariable('{km_remaining}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{km_remaining}</button>
                        <button type="button" @click="insertVariable('{service_type}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{service_type}</button>
                        <button type="button" @click="insertVariable('{days}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{days}</button>
                        <button type="button" @click="insertVariable('{riding_pattern}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{riding_pattern}</button>
                        <button type="button" @click="insertVariable('{confidence_score}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{confidence_score}</button>
                        <button type="button" @click="insertVariable('{overdue_km}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{overdue_km}</button>
                        <button type="button" @click="insertVariable('{avg_daily_km}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{avg_daily_km}</button>
                        <button type="button" @click="insertVariable('{predicted_cost}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{predicted_cost}</button>
                        <button type="button" @click="insertVariable('{month}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{month}</button>
                        <button type="button" @click="insertVariable('{workshop_name}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{workshop_name}</button>
                        <button type="button" @click="insertVariable('{distance}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{distance}</button>
                        <button type="button" @click="insertVariable('{rating}')" class="text-xs text-[#6A7282] hover:text-[#6B7C4F] text-left transition-colors" style="font-family: Consolas, monospace;">{rating}</button>
                    </div>
                </div>

                <!-- Isi Template Pesan -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Isi Template Pesan</label>
                    <textarea 
                        x-model="formData.message"
                        @input="updatePreview()"
                        rows="4"
                        placeholder="Contoh: Servis {service_type} dalam {km_remaining} km lagi!"
                        class="w-full px-4 py-3 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-sm placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] transition-colors resize-none"
                        style="font-family: Consolas, monospace;"></textarea>
                </div>

                <!-- Preview Output -->
                <div class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] p-[17px]">
                    <p class="text-xs text-[#6A7282] uppercase tracking-wider mb-2" style="font-family: Arial, sans-serif; letter-spacing: 0.3px;">Preview Output</p>
                    <div class="bg-[#111111] border border-[#1E2939] rounded-[10px] px-[13px] py-3">
                        <p class="text-sm text-white" style="font-family: Arial, sans-serif;" x-text="formData.previewMessage"></p>
                    </div>
                </div>

                <!-- Modal Footer - Buttons -->
                <div class="flex items-center gap-3 pt-1">
                    <button 
                        type="submit"
                        class="flex-1 h-12 bg-[#6B7C4F] text-white text-base rounded-[10px] hover:bg-[#5A6A40] transition-colors flex items-center justify-center gap-2"
                        style="font-family: Arial, sans-serif;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Notifikasi
                    </button>
                    <button 
                        type="button"
                        @click="showAddModal = false; resetForm()"
                        class="flex-1 h-12 bg-[#1A1A1A] border border-[#364153] text-white text-base rounded-[10px] hover:bg-[#2A2A2A] transition-colors flex items-center justify-center"
                        style="font-family: Arial, sans-serif;">
                        Batal
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
