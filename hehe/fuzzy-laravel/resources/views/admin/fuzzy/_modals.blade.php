{{-- resources/views/admin/fuzzy/_modal_rule.blade.php --}}
<template x-if="showRuleModal">
  <div class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4 modal-backdrop"
    @click.self="showRuleModal=false">
    <div class="bg-[#111111] border border-gray-800 rounded-xl w-full max-w-md shadow-2xl modal-box">
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
        <h3 class="text-white font-semibold text-sm" x-text="editRule ? 'Edit Rule' : 'Tambah Rule Baru'"></h3>
        <button @click="showRuleModal=false" class="text-gray-600 hover:text-gray-400">✕</button>
      </div>
      <div class="p-5 space-y-4">

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Variabel 1</label>
            <select x-model="ruleForm.var1" class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none">
              <template x-for="v in activeVarKeys" :key="v">
                <option :value="v" x-text="varLabels[v]?.split(' (')[0]"></option>
              </template>
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Label</label>
            <select x-model="ruleForm.label1" class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none">
              <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Operator</label>
          <div class="flex gap-2">
            <template x-for="op in ['AND','OR']" :key="op">
              <button type="button" @click="ruleForm.operator=op"
                :class="ruleForm.operator===op ? 'bg-[#6b7c4f] border-[#6b7c4f] text-white' : 'border-gray-800 text-gray-500'"
                class="flex-1 py-2 rounded-lg text-sm border transition-colors" x-text="op"></button>
            </template>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Variabel 2</label>
            <select x-model="ruleForm.var2" class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none">
              <template x-for="v in activeVarKeys" :key="v">
                <option :value="v" x-text="varLabels[v]?.split(' (')[0]"></option>
              </template>
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Label</label>
            <select x-model="ruleForm.label2" class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none">
              <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Output (THEN)</label>
            <select x-model="ruleForm.output" class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none">
              <option>Baik</option><option>Perlu Servis</option><option>Kritis</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Bobot</label>
            <input type="number" min="0" max="1" step="0.1" x-model="ruleForm.weight"
              class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none">
          </div>
        </div>

        {{-- Preview rule --}}
        <div class="bg-[#0a0a0a] border border-gray-800 rounded-lg px-3.5 py-2.5 text-xs text-gray-500">
          <span class="text-gray-600">IF </span>
          <span class="text-gray-300" x-text="varLabels[ruleForm.var1]?.split(' (')[0]"></span>
          <span class="text-gray-700"> = </span><span class="text-gray-200" x-text="ruleForm.label1"></span>
          <span class="text-gray-600" x-text="` ${ruleForm.operator} `"></span>
          <span class="text-gray-300" x-text="varLabels[ruleForm.var2]?.split(' (')[0]"></span>
          <span class="text-gray-700"> = </span><span class="text-gray-200" x-text="ruleForm.label2"></span>
          <span class="text-gray-600"> THEN </span><span class="text-white" x-text="ruleForm.output"></span>
          <span class="text-gray-700" x-text="` [bobot: ${parseFloat(ruleForm.weight).toFixed(1)}]`"></span>
        </div>
      </div>
      <div class="flex gap-2.5 px-5 pb-5">
        <button @click="showRuleModal=false" class="flex-1 py-2.5 border border-gray-800 text-gray-400 hover:text-white rounded-lg text-sm transition-colors">Batal</button>
        <button @click="saveRule()" class="flex-1 py-2.5 bg-[#6b7c4f] hover:bg-[#7a8d5e] text-white rounded-lg text-sm font-medium transition-colors">✓ Simpan Rule</button>
      </div>
    </div>
  </div>
</template>

{{-- ─────────────────────────────────────────────────────────── --}}
{{-- resources/views/admin/fuzzy/_modal_add_comp.blade.php      --}}
{{-- (tambahkan file terpisah, ini digabung untuk ringkas)       --}}
<template x-if="showAddComp">
  <div class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4 modal-backdrop"
    @click.self="showAddComp=false">
    <div class="bg-[#111111] border border-gray-800 rounded-xl w-full max-w-[560px] shadow-2xl modal-box overflow-hidden">
      <div class="flex items-start justify-between px-5 py-4 border-b border-gray-800">
        <div>
          <h3 class="text-white font-semibold">Tambah Komponen Baru</h3>
          <p class="text-xs text-gray-500 mt-0.5">Komponen baru akan tersedia di semua jenis motor yang dipilih</p>
        </div>
        <button @click="showAddComp=false" class="text-gray-600 hover:text-gray-400">✕</button>
      </div>
      <div class="p-5 space-y-5 max-h-[70vh] overflow-y-auto">

        <template x-if="addCompError">
          <div class="flex items-center gap-2 bg-red-500/10 border border-red-500/30 text-red-400 text-xs px-3 py-2.5 rounded-lg">
            ⚠ <span x-text="addCompError"></span>
          </div>
        </template>

        <div>
          <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Nama Komponen</label>
          <input type="text" x-model="addCompForm.name" placeholder="contoh: Kampas Kopling"
            @input="addCompError=''"
            class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm placeholder-gray-700 focus:outline-none focus:border-gray-600">
        </div>

        <div>
          <label class="block text-xs text-gray-500 mb-2 uppercase tracking-wide">Berlaku untuk Jenis Motor</label>
          <div class="grid grid-cols-2 gap-2">
            @foreach($motorTypes as $mt)
            <button type="button" @click="toggleAddMotorType('{{ $mt->slug }}')"
              :class="addCompForm.motorTypes.includes('{{ $mt->slug }}')
                ? 'bg-[#6b7c4f]/10 border-[#6b7c4f]/40 text-gray-200'
                : 'bg-[#0a0a0a] border-gray-800 text-gray-500'"
              class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg border text-sm transition-colors text-left">
              <span :class="addCompForm.motorTypes.includes('{{ $mt->slug }}') ? 'bg-[#6b7c4f] border-[#6b7c4f]' : 'border-gray-700'"
                class="w-4 h-4 rounded border flex items-center justify-center shrink-0 transition-colors">
                <span x-show="addCompForm.motorTypes.includes('{{ $mt->slug }}')" class="text-white text-[10px]">✓</span>
              </span>
              {{ $mt->name }}
            </button>
            @endforeach
          </div>
        </div>

        <div>
          <label class="block text-xs text-gray-500 mb-2 uppercase tracking-wide">Threshold Awal</label>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Warn</label>
              <div class="flex items-center gap-1"><input type="number" x-model="addCompForm.warn" class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none"><span class="text-xs text-gray-600 shrink-0">km</span></div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Critical</label>
              <div class="flex items-center gap-1"><input type="number" x-model="addCompForm.critical" class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none"><span class="text-xs text-gray-600 shrink-0">km</span></div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Reset</label>
              <div class="flex items-center gap-1"><input type="number" x-model="addCompForm.resetInterval" class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none"><span class="text-xs text-gray-600 shrink-0">hari</span></div>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs text-gray-500 mb-2 uppercase tracking-wide">Variabel Input Aktif</label>
          <div class="bg-[#0a0a0a] border border-gray-800 rounded-lg p-3.5 space-y-2">
            <template x-for="v in allVars" :key="v.key">
              <div class="flex items-center justify-between">
                <span :class="addCompForm.activeVars.includes(v.key) ? 'text-gray-300' : 'text-gray-600'" class="text-sm" x-text="v.label"></span>
                <button type="button"
                  :class="[addCompForm.activeVars.includes(v.key) ? 'on' : 'off', addCompForm.activeVars.includes(v.key) && addCompForm.activeVars.length <= 1 ? 'disabled' : '']"
                  @click="toggleAddVar(v.key)" class="toggle-btn"><span></span></button>
              </div>
            </template>
          </div>
          <p class="text-xs text-gray-700 mt-2">Bisa diubah lagi setelah komponen disimpan.</p>
        </div>

        <div>
          <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Catatan <span class="normal-case text-gray-700">(opsional)</span></label>
          <textarea x-model="addCompForm.notes" rows="2" placeholder="contoh: Diganti setiap 20.000 km atau 2 tahun"
            class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm placeholder-gray-700 focus:outline-none resize-none"></textarea>
        </div>
      </div>
      <div class="flex gap-2.5 px-5 py-4 border-t border-gray-800">
        <button @click="showAddComp=false" class="flex-1 py-2.5 border border-gray-800 text-gray-400 hover:text-white rounded-lg text-sm transition-colors">Batal</button>
        <button @click="saveComp()" class="flex-1 py-2.5 bg-[#6b7c4f] hover:bg-[#7a8d5e] text-white rounded-lg text-sm font-medium transition-colors">✓ Simpan Komponen</button>
      </div>
    </div>
  </div>
</template>

{{-- Dialog Hapus --}}
<template x-if="deleteTarget">
  <div class="fixed inset-0 bg-black/70 z-[110] flex items-center justify-center p-4">
    <div class="bg-[#111111] border border-gray-800 rounded-xl w-full max-w-sm shadow-2xl modal-box">
      <div class="p-5">
        <div class="flex items-start gap-3 mb-4">
          <div class="w-9 h-9 bg-red-500/10 border border-red-500/30 rounded-lg flex items-center justify-center shrink-0">
            <span class="text-red-400">⚠</span>
          </div>
          <div>
            <h3 class="text-white font-semibold text-sm mb-1">Hapus Komponen Permanen</h3>
            <p class="text-gray-400 text-xs leading-relaxed">
              Yakin ingin menghapus komponen <span class="text-white font-medium" x-text="`"${deleteTarget?.name}"`"></span>?
              Semua konfigurasi dan riwayat data akan ikut terhapus.
            </p>
          </div>
        </div>
        <div class="flex gap-2.5">
          <button @click="deleteTarget=null" class="flex-1 py-2.5 border border-gray-800 text-gray-400 hover:text-white rounded-lg text-sm transition-colors">Batal</button>
          <button @click="handleDelete()" class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">Hapus</button>
        </div>
      </div>
    </div>
  </div>
</template>

{{-- Inject motor type map ke JS --}}
<script>
  window.motorTypeMap = @json($motorTypes->pluck('id', 'slug'));
</script>
