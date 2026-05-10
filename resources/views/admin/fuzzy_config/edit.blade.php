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

        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">Config JSON (inputs/output/rules)</label>
                <div class="flex items-center gap-2">
                    <button type="submit" form="resetDefaultForm" onclick="return confirm('Reset config JSON ke default? Perubahan ini akan menimpa JSON yang ada.');" class="h-8 px-3 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-xs" style="font-family: Arial, sans-serif;">Reset Default</button>
                    <button type="button" id="btnFormat" class="h-8 px-3 bg-[#1A1A1A] border border-[#364153] rounded-[10px] text-white text-xs" style="font-family: Arial, sans-serif;">Format JSON</button>
                </div>
            </div>

            <textarea id="config_json" name="config_json" rows="22" class="w-full bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] px-3 py-2 text-xs text-white font-mono">{{ old('config_json', $configJson) }}</textarea>
            @error('config_json')
                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-2">
            <button type="submit" class="h-10 px-4 bg-[#6B7C4F] rounded-[10px] text-sm text-white" style="font-family: Arial, sans-serif;">Simpan</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('btnFormat')?.addEventListener('click', function () {
        const el = document.getElementById('config_json');
        if (!el) return;
        try {
            const parsed = JSON.parse(el.value);
            el.value = JSON.stringify(parsed, null, 2);
        } catch (e) {
            alert('JSON tidak valid, tidak bisa diformat.');
        }
    });
</script>
@endsection
