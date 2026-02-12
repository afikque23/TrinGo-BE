<!-- Modal Edit Template -->
<div id="modalEditTemplate" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="relative bg-[#111111] border border-[#1E2939] rounded-2xl w-[896px] max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 pt-6 pb-4">
            <div class="flex flex-col gap-1">
                <h3 id="editTemplateTitle" class="text-white text-xl font-bold">Edit: Pesan Servis Mendesak</h3>
                <p id="editTemplateSubtitle" class="text-[#99A1AF] text-sm">Service • Servis Mendesak</p>
            </div>
            <button onclick="closeModal('modalEditTemplate')" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form id="formEditTemplate" class="px-6 pb-6">
            <!-- Available Variables -->
            <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-lg p-4 mb-4">
                <p class="text-[#6A7282] text-xs uppercase tracking-wider mb-4">Semua Variabel Tersedia (Klik untuk insert)</p>
                
                <!-- Service Variables -->
                <div class="mb-6">
                    <h4 class="text-[#99A1AF] text-xs font-bold mb-2">Service</h4>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="insertVariableEdit('{current_km}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {current_km}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{km_remaining}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {km_remaining}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{last_service_km}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {last_service_km}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{last_service_date}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {last_service_date}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{next_service_km}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {next_service_km}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{days_since_service}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {days_since_service}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{service_interval_km}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {service_interval_km}
                        </button>
                    </div>
                </div>

                <!-- Riding Variables -->
                <div class="mb-6">
                    <h4 class="text-[#99A1AF] text-xs font-bold mb-2">Riding</h4>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="insertVariableEdit('{avg_daily_km}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {avg_daily_km}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{total_week_km}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {total_week_km}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{prev_week_km}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {prev_week_km}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{percentage_change}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {percentage_change}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{total_trips}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {total_trips}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{avg_speed}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {avg_speed}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{max_speed}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {max_speed}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{days_inactive}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {days_inactive}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{traffic_level}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {traffic_level}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{road_condition}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {road_condition}
                        </button>
                    </div>
                </div>

                <!-- Vehicle Variables -->
                <div class="mb-6">
                    <h4 class="text-[#99A1AF] text-xs font-bold mb-2">Vehicle</h4>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="insertVariableEdit('{motor_type}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {motor_type}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{motor_brand}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {motor_brand}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{motor_year}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {motor_year}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{riding_style}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {riding_style}
                        </button>
                    </div>
                </div>

                <!-- Community Variables -->
                <div class="mb-6">
                    <h4 class="text-[#99A1AF] text-xs font-bold mb-2">Community</h4>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="insertVariableEdit('{template_count}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {template_count}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{contributor_name}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {contributor_name}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{success_rate}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {success_rate}
                        </button>
                    </div>
                </div>

                <!-- System Variables -->
                <div>
                    <h4 class="text-[#99A1AF] text-xs font-bold mb-2">System</h4>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="insertVariableEdit('{usage_intensity}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {usage_intensity}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{predicted_service_date}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {predicted_service_date}
                        </button>
                        <button type="button" onclick="insertVariableEdit('{health_score}')" class="px-3 py-1.5 bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded text-[#6B7C4F] text-xs hover:bg-[rgba(107,124,79,0.2)] transition-colors">
                            {health_score}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Isi Pesan -->
            <div class="flex flex-col gap-2 mb-4">
                <label class="text-[#D1D5DC] text-sm">Isi Pesan</label>
                <textarea 
                    id="textareaIsiPesanEdit"
                    name="isi_pesan"
                    rows="5"
                    placeholder="Tulis pesan dengan variabel {contoh_variabel}"
                    class="w-full px-4 py-3 bg-[#0A0A0A] border border-[#364153] rounded-lg text-white text-sm placeholder-white/50 focus:outline-none focus:border-[#6B7C4F] transition-colors font-mono resize-none"
                ></textarea>
            </div>

            <!-- Preview -->
            <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-lg p-4 mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-[#6B7C4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <p class="text-[#6A7282] text-xs uppercase tracking-wider">Preview Pesan</p>
                </div>
                <div id="previewPesanEdit" class="bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded-lg p-3 mb-2 min-h-[34px]">
                    <p class="text-white text-sm"></p>
                </div>
                <p class="text-[#6A7282] text-xs">Preview menggunakan data sampel untuk memperlihatkan format pesan</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button 
                    type="button"
                    onclick="closeModal('modalEditTemplate')"
                    class="flex-1 py-3 bg-[#2A2A2A] hover:bg-[#3A3A3A] text-white font-bold rounded-lg transition-colors"
                >
                    Batal
                </button>
                <button 
                    type="submit"
                    class="flex-1 flex items-center justify-center gap-2 py-3 bg-[#6B7C4F] hover:bg-[#5a6a42] text-white font-bold rounded-lg shadow-lg transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    Simpan Template
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Insert variable into textarea (Edit Modal)
    function insertVariableEdit(variable) {
        const textarea = document.getElementById('textareaIsiPesanEdit');
        const cursorPos = textarea.selectionStart;
        const textBefore = textarea.value.substring(0, cursorPos);
        const textAfter = textarea.value.substring(cursorPos);
        
        textarea.value = textBefore + variable + textAfter;
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = cursorPos + variable.length;
        
        // Update preview
        updatePreviewEdit();
    }

    // Update preview with sample data (Edit Modal)
    function updatePreviewEdit() {
        const textarea = document.getElementById('textareaIsiPesanEdit');
        const previewDiv = document.getElementById('previewPesanEdit').querySelector('p');
        
        // Sample data for preview
        const sampleData = {
            '{current_km}': '15,250',
            '{km_remaining}': '750',
            '{last_service_km}': '14,000',
            '{last_service_date}': '15 Jan 2026',
            '{next_service_km}': '16,000',
            '{days_since_service}': '45',
            '{service_interval_km}': '2,000',
            '{avg_daily_km}': '35',
            '{total_week_km}': '245',
            '{prev_week_km}': '180',
            '{percentage_change}': '+36%',
            '{total_trips}': '12',
            '{avg_speed}': '45',
            '{max_speed}': '80',
            '{days_inactive}': '3',
            '{traffic_level}': 'Sedang',
            '{road_condition}': 'Baik',
            '{motor_type}': 'Sport',
            '{motor_brand}': 'Honda',
            '{motor_year}': '2022',
            '{riding_style}': 'Agresif',
            '{template_count}': '25',
            '{contributor_name}': 'Ahmad',
            '{success_rate}': '92%',
            '{usage_intensity}': 'Tinggi',
            '{predicted_service_date}': '28 Feb 2026',
            '{health_score}': '85'
        };
        
        let previewText = textarea.value || 'Belum ada pesan...';
        
        // Replace variables with sample data
        for (const [variable, value] of Object.entries(sampleData)) {
            previewText = previewText.replaceAll(variable, value);
        }
        
        previewDiv.textContent = previewText;
    }

    // Update preview on textarea input (Edit Modal)
    document.addEventListener('DOMContentLoaded', function() {
        const textareaEdit = document.getElementById('textareaIsiPesanEdit');
        if (textareaEdit) {
            textareaEdit.addEventListener('input', updatePreviewEdit);
        }
    });
</script>
