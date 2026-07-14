@extends('layouts.sidebar')

@section('title', 'Edit Konfigurasi Fuzzy - MotoTracker Admin')

@section('page-title', 'Edit Konfigurasi Fuzzy')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white" style="font-family: Arial, sans-serif;">Edit Konfigurasi</h1>
            <p class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                {{ ucfirst($mapping->motor_type) }} • {{ $mapping->component?->name ?? '-' }} ({{ $mapping->component?->key ?? '' }})
            </p>
        </div>
        <a href="{{ route('admin.fuzzy-config.index', ['motor_type' => $mapping->motor_type]) }}" class="h-10 px-4 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-white inline-flex items-center" style="font-family: Arial, sans-serif;">Kembali</a>
    </div>

    <form id="resetDefaultForm" method="POST" action="{{ route('admin.fuzzy-config.reset-default', $mapping) }}">
        @csrf
    </form>

    <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-[14px] p-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-sm font-semibold text-white" style="font-family: Arial, sans-serif;">Mode Sederhana</div>
                <div class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Pakai preset untuk mengatur Warn/Critical tanpa edit JSON. JSON tetap bisa dipakai di Mode Lanjutan.</div>
            </div>

            <form method="POST" action="{{ route('admin.fuzzy-config.apply-preset', $mapping) }}" class="flex items-center gap-2">
                @csrf
                <select name="preset" class="h-8 bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 text-xs text-white" style="font-family: Arial, sans-serif;">
                    <option value="default">Default (60 / 40)</option>
                    <option value="sensitive">Lebih Sensitif (70 / 50)</option>
                    <option value="loose">Lebih Longgar (50 / 30)</option>
                </select>
                <button type="submit" class="h-8 px-3 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-white text-xs" style="font-family: Arial, sans-serif;">Terapkan Preset</button>
            </form>
        </div>

        @error('preset')
            <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <form method="POST" action="{{ route('admin.fuzzy-config.update', $mapping) }}" class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-5 flex flex-col gap-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs text-[#6A7282] mb-1" style="font-family: Arial, sans-serif;">Warn Score</label>
                <input type="number" name="warn_score" min="0" max="100" value="{{ old('warn_score', $config->warn_score) }}" class="w-full bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] px-3 py-2 text-sm text-white" />
                @error('warn_score')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs text-[#6A7282] mb-1" style="font-family: Arial, sans-serif;">Critical Score</label>
                <input type="number" name="critical_score" min="0" max="100" value="{{ old('critical_score', $config->critical_score) }}" class="w-full bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] px-3 py-2 text-sm text-white" />
                @error('critical_score')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs text-[#6A7282] mb-1" style="font-family: Arial, sans-serif;">Versi</label>
                <div class="h-[38px] bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] px-3 flex items-center text-sm text-white" style="font-family: Arial, sans-serif;">
                    {{ $config->version }}
                </div>
            </div>
        </div>

        <div x-data="fuzzyConfig()">
            <div class="flex items-center justify-between mb-4">
                <label class="block text-sm font-semibold text-white" style="font-family: Arial, sans-serif;">Parameter Fungsi Keanggotaan (Membership Function)</label>
                <div class="flex items-center gap-2">
                    <button type="submit" form="resetDefaultForm" onclick="return confirm('Reset config JSON ke default? Perubahan ini akan menimpa JSON yang ada.');" class="h-8 px-3 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-xs hover:bg-[#1A1A1A] transition" style="font-family: Arial, sans-serif;">Reset Default</button>
                    <button type="button" onclick="document.getElementById('json_editor_modal').classList.toggle('hidden')" class="h-8 px-3 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-white text-xs hover:bg-[#2A2A2A] transition" style="font-family: Arial, sans-serif;">Edit JSON Manual</button>
                </div>
            </div>

            <!-- Dynamic Fields Container -->
            <div id="dynamic_fields" class="space-y-6"></div>

            <!-- Hidden JSON Textarea for form submission -->
            <div id="json_editor_modal" class="hidden mt-4">
                <label class="block text-xs text-[#6A7282] mb-1">Config JSON Asli (Ter-sinkronisasi)</label>
                <textarea id="config_json" name="config_json" rows="12" class="w-full bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] px-3 py-2 text-xs text-white font-mono">{{ old('config_json', $configJson) }}</textarea>
                @error('config_json')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
                <button type="button" id="btnFormat" class="mt-2 h-8 px-3 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-white text-xs" style="font-family: Arial, sans-serif;">Format JSON</button>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 mt-6">
            <button type="submit" class="h-10 px-6 bg-[#6B7C4F] rounded-[10px] text-sm text-white font-bold hover:bg-[#7b8c5f] transition" style="font-family: Arial, sans-serif;">Simpan Konfigurasi</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jsonTextarea = document.getElementById('config_json');
        const container = document.getElementById('dynamic_fields');
        let config = {};
        
        try {
            config = JSON.parse(jsonTextarea.value);
        } catch(e) {
            console.error('Invalid JSON initial load', e);
        }

        const labels = {
            'distance_since_service_km': 'Jarak Tempuh (km)',
            'duration_since_service_days': 'Durasi Servis (Hari)',
            'avg_speed_kph': 'Kecepatan Rata-rata (km/jam)',
            'intensity_km_per_day': 'Intensitas Berkendara (Score)'
        };

        const renderFields = () => {
            container.innerHTML = '';
            if (!config.inputs) return;

            Object.keys(config.inputs).forEach(varKey => {
                const varData = config.inputs[varKey];
                const sets = varData.sets || {};
                
                let title = labels[varKey] || varKey;

                let html = `
                <div class="bg-[#1A1A1A] border border-[#2A3547] rounded-[12px] p-4">
                    <h3 class="text-white font-semibold mb-3 border-b border-[#2A3547] pb-2">${title}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                `;

                ['low', 'medium', 'high'].forEach(setKey => {
                    if (sets[setKey]) {
                        const setDef = sets[setKey];
                        html += `
                        <div class="bg-[#0A0A0A] p-3 rounded-[8px] border border-[#1E2939]">
                            <div class="text-xs text-gray-400 mb-2 capitalize font-semibold">${setKey} (${setDef.type})</div>
                            <div class="flex flex-col gap-2">
                        `;
                        
                        setDef.params.forEach((paramVal, pIdx) => {
                            html += `
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500 w-4">P${pIdx+1}</span>
                                    <input type="number" 
                                        class="flex-1 bg-[#111111] border border-[#364153] rounded-[6px] px-2 py-1 text-sm text-white" 
                                        value="${paramVal}" 
                                        onchange="updateParam('${varKey}', '${setKey}', ${pIdx}, this.value)"
                                    />
                                </div>
                            `;
                        });

                        html += `</div></div>`;
                    }
                });

                html += `</div></div>`;
                container.innerHTML += html;
            });
        };

        window.updateParam = (varKey, setKey, pIdx, val) => {
            config.inputs[varKey].sets[setKey].params[pIdx] = parseFloat(val) || 0;
            jsonTextarea.value = JSON.stringify(config, null, 2);
        };

        jsonTextarea.addEventListener('change', () => {
            try {
                config = JSON.parse(jsonTextarea.value);
                renderFields();
            } catch(e) {}
        });

        document.getElementById('btnFormat')?.addEventListener('click', function () {
            try {
                const parsed = JSON.parse(jsonTextarea.value);
                jsonTextarea.value = JSON.stringify(parsed, null, 2);
                config = parsed;
                renderFields();
            } catch (e) {
                alert('JSON tidak valid, tidak bisa diformat.');
            }
        });

        renderFields();
    });
</script>
@endsection
