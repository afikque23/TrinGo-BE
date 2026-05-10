{{-- resources/views/admin/fuzzy/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Konfigurasi Fuzzy Logic')

@push('styles')
<style>
  /* ─── Toggle switch ─── */
  .toggle-btn { position:relative; width:36px; height:20px; border-radius:999px; transition:background .2s; cursor:pointer; border:none; outline:none; }
  .toggle-btn.on  { background:#6b7c4f; }
  .toggle-btn.off { background:#374151; }
  .toggle-btn.disabled { opacity:.4; cursor:not-allowed; }
  .toggle-btn span { position:absolute; top:2px; width:16px; height:16px; border-radius:50%; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.4); transition:transform .2s; }
  .toggle-btn.on span  { transform:translateX(18px); }
  .toggle-btn.off span { transform:translateX(2px); }

  /* ─── Chart canvas ─── */
  #mf-chart { width:100%!important; height:180px!important; }

  /* ─── Animations ─── */
  @keyframes fadeIn  { from{opacity:0}       to{opacity:1} }
  @keyframes slideUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
  .modal-backdrop { animation:fadeIn .15s ease; }
  .modal-box      { animation:slideUp .15s ease; }
</style>
@endpush

@section('content')
<div x-data="fuzzyAdmin()" x-init="init()" class="space-y-6">

  {{-- Toast --}}
  <template x-if="toast">
    <div class="fixed top-5 right-6 z-[200] flex items-center gap-2.5 bg-[#6b7c4f] text-white text-sm px-4 py-2.5 rounded-lg shadow-xl">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      <span x-text="toast"></span>
    </div>
  </template>

  {{-- ── Page Header ── --}}
  <div class="flex items-start justify-between">
    <div>
      <h2 class="text-white text-2xl font-bold mb-1">Konfigurasi Fuzzy Logic</h2>
      <p class="text-gray-400 text-sm">Atur parameter sistem rekomendasi perawatan motor</p>
    </div>
    <button @click="saveAll()"
      class="flex items-center gap-2 bg-[#6b7c4f] hover:bg-[#7a8d5e] text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
      Simpan Semua Perubahan
      <span x-show="hasChanges" class="w-2 h-2 rounded-full bg-yellow-400 ml-0.5"></span>
    </button>
  </div>

  {{-- ══ SECTION 1 — Pilih Jenis Motor & Komponen ══ --}}
  <div class="bg-[#111111] border border-gray-800 rounded-xl p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-white font-semibold">Pilih Jenis Motor & Komponen</h3>
      <button @click="openAddComp()"
        class="flex items-center gap-1.5 text-xs text-[#8fa06a] border border-gray-700 hover:border-[#6b7c4f] hover:bg-[#6b7c4f]/10 px-3 py-1.5 rounded-lg transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Komponen
      </button>
    </div>

    {{-- Motor Tabs --}}
    <div class="flex gap-1 bg-[#0a0a0a] border border-gray-800 rounded-lg p-1 w-fit mb-5">
      @foreach($motorTypes as $mt)
      <button
        :class="activeTab === {{ $mt->id }} ? 'bg-[#6b7c4f] text-white font-medium' : 'text-gray-400 hover:text-gray-300'"
        @click="switchTab({{ $mt->id }})"
        class="relative px-4 py-1.5 rounded-md text-sm transition-colors">
        {{ $mt->name }}
      </button>
      @endforeach
    </div>

    {{-- Component Badges --}}
    <div class="flex flex-wrap gap-2">
      <template x-for="comp in currentComponents" :key="comp.id">
        <div class="relative" @click.outside="if(openMenu===comp.id) openMenu=null">
          <div :class="{
              'bg-[#6b7c4f] border-[#6b7c4f] text-white': selectedComp?.id === comp.id,
              'bg-[#0a0a0a] border-gray-800 text-gray-600 opacity-50': !comp.is_active,
              'bg-[#0a0a0a] border-gray-800 text-gray-300 hover:border-gray-700': comp.is_active && selectedComp?.id !== comp.id,
            }"
            class="group flex items-center gap-2 pl-3 pr-2 py-1.5 rounded-lg text-sm border transition-all">

            <button @click="!comp.is_active || selectComp(comp)" class="flex items-center gap-2 min-w-0">
              {{-- Status dot --}}
              <span :class="{
                'bg-[#6b7c4f]': comp.status==='good',
                'bg-yellow-600': comp.status==='warning',
                'bg-red-700': comp.status==='critical'
              }" class="w-1.5 h-1.5 rounded-full shrink-0"></span>

              <span x-text="comp.name"></span>

              <span x-show="!comp.is_active" class="text-[10px] text-gray-600 border border-gray-800 px-1 rounded">nonaktif</span>
              <span x-show="comp.is_custom && comp.is_active" class="text-[10px] text-[#8fa06a] border border-[#6b7c4f]/30 px-1 rounded">custom</span>
            </button>

            {{-- Menu button --}}
            <button @click.stop="openMenu = openMenu===comp.id ? null : comp.id"
              :class="openMenu===comp.id ? 'opacity-100 text-white' : (selectedComp?.id===comp.id ? 'opacity-60 hover:opacity-100 text-white' : 'opacity-0 group-hover:opacity-100 text-gray-500')"
              class="p-0.5 rounded transition-opacity">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
            </button>
          </div>

          {{-- Dropdown menu --}}
          <div x-show="openMenu===comp.id" x-transition
            class="absolute top-full left-0 mt-1 z-50 bg-[#1a1a1a] border border-gray-700 rounded-lg shadow-xl overflow-hidden min-w-[160px]">
            <button x-show="!comp.is_active" @click="toggleComp(comp)"
              class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-sm text-gray-300 hover:bg-gray-800 text-left">
              ✓ Aktifkan Kembali
            </button>
            <button x-show="comp.is_active" @click="toggleComp(comp)"
              class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-sm text-gray-300 hover:bg-gray-800 text-left">
              Nonaktifkan
            </button>
            <div class="border-t border-gray-800"></div>
            <button @click="confirmDelete(comp)"
              class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-sm text-red-400 hover:bg-red-500/10 text-left">
              Hapus Permanen
            </button>
          </div>
        </div>
      </template>
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-800">
      <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#6b7c4f]"></span><span class="text-xs text-gray-500">Baik</span></div>
      <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-yellow-600"></span><span class="text-xs text-gray-500">Perlu Servis</span></div>
      <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-red-700"></span><span class="text-xs text-gray-500">Kritis</span></div>
    </div>
  </div>

  {{-- ══ SECTION 2 — Threshold ══ --}}
  <template x-if="selectedComp">
    <div class="bg-[#111111] border border-gray-800 rounded-xl p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-white font-semibold" x-text="`Threshold — ${selectedComp.name}`"></h3>
        <button @click="resetThreshold()"
          class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-300 border border-gray-800 hover:border-gray-700 rounded-lg px-3 py-1.5 transition-colors">
          Reset ke Default
        </button>
      </div>

      <div class="grid grid-cols-3 gap-4 mb-4">
        <div>
          <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Warn</label>
          <div class="flex items-center gap-2">
            <input type="number" x-model="form.warn" class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-gray-600">
            <span class="text-xs text-gray-600 shrink-0">km</span>
          </div>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Critical</label>
          <div class="flex items-center gap-2">
            <input type="number" x-model="form.critical" class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-gray-600">
            <span class="text-xs text-gray-600 shrink-0">km</span>
          </div>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide">Reset Interval</label>
          <div class="flex items-center gap-2">
            <input type="number" x-model="form.resetInterval" class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-gray-600">
            <span class="text-xs text-gray-600 shrink-0">hari</span>
          </div>
        </div>
      </div>

      <p class="text-xs text-gray-600 flex items-center gap-1.5 mb-5">
        ⓘ Nilai warn dan critical digunakan sebagai batas keanggotaan medium dan high pada Fuzzy Logic.
      </p>

      {{-- Variabel Input Aktif --}}
      <div class="bg-[#0a0a0a] border border-gray-800 rounded-lg p-4">
        <p class="text-sm text-white font-medium mb-1" x-text="`Variabel Input Aktif — ${selectedComp.name}`"></p>
        <p class="text-xs text-gray-500 mb-4">Pilih variabel IoT yang digunakan untuk menghitung kondisi komponen ini. Minimal 1 variabel.</p>
        <div class="grid grid-cols-2 gap-2.5">
          <template x-for="v in allVars" :key="v.key">
            <div :class="form.activeVars.includes(v.key) ? 'bg-[#111111] border-gray-700' : 'bg-[#0a0a0a] border-gray-800'"
              class="flex items-center justify-between px-3.5 py-2.5 rounded-lg border transition-colors">
              <span :class="form.activeVars.includes(v.key) ? 'text-gray-200' : 'text-gray-600'" class="text-sm" x-text="v.label"></span>
              <button type="button"
                :class="form.activeVars.includes(v.key) ? 'on' : 'off'"
                :class="form.activeVars.includes(v.key) && form.activeVars.length <= 1 ? 'disabled' : ''"
                @click="toggleVar(v.key)"
                class="toggle-btn">
                <span></span>
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </template>

  {{-- ══ SECTION 3 — Membership Function ══ --}}
  <template x-if="selectedComp">
    <div class="bg-[#111111] border border-gray-800 rounded-xl p-5">
      <h3 class="text-white font-semibold mb-4" x-text="`Membership Function — ${mfVarLabel}`"></h3>

      <div class="flex gap-1.5 mb-5">
        <template x-for="v in activeVarKeys" :key="v">
          <button @click="switchMfVar(v)"
            :class="mfVar === v ? 'bg-[#6b7c4f] border-[#6b7c4f] text-white' : 'border-gray-800 text-gray-500 hover:border-gray-700'"
            class="px-3 py-1.5 rounded-lg text-xs border transition-colors"
            x-text="varLabels[v].split(' (')[0]">
          </button>
        </template>
      </div>

      <div class="grid grid-cols-2 gap-6">
        {{-- MF Inputs --}}
        <div class="space-y-4">
          <template x-for="lbl in ['low','medium','high']" :key="lbl">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide mb-2" x-text="lbl"></p>
              <div class="grid grid-cols-3 gap-2">
                <template x-for="(pt, idx) in ['a','b','c']" :key="pt">
                  <div>
                    <input type="number"
                      :value="mfForm[mfVar]?.[lbl]?.[idx] ?? 0"
                      @input="updateMF(lbl, idx, $event.target.value)"
                      class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-2 py-2 text-white text-sm text-center focus:outline-none focus:border-gray-600">
                    <p class="text-center text-[10px] text-gray-700 mt-1" x-text="`[${pt}]`"></p>
                  </div>
                </template>
              </div>
            </div>
          </template>
          <p class="text-xs text-gray-700">ⓘ [a] batas awal · [b] puncak · [c] batas akhir. Gunakan 999 untuk ∞ pada High.</p>
        </div>

        {{-- Chart --}}
        <div>
          <div class="flex items-center gap-4 mb-3">
            <div class="flex items-center gap-1.5"><span class="w-4 h-0.5 rounded bg-[#6b7c4f]"></span><span class="text-xs text-gray-500">Low</span></div>
            <div class="flex items-center gap-1.5"><span class="w-4 h-0.5 rounded bg-gray-400"></span><span class="text-xs text-gray-500">Medium</span></div>
            <div class="flex items-center gap-1.5"><span class="w-4 h-0.5 rounded bg-gray-200"></span><span class="text-xs text-gray-500">High</span></div>
          </div>
          <div class="bg-[#0a0a0a] border border-gray-800 rounded-lg p-3">
            <canvas id="mf-chart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </template>

  {{-- ══ SECTION 4 — Rule Base ══ --}}
  <template x-if="selectedComp">
    <div class="bg-[#111111] border border-gray-800 rounded-xl p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-white font-semibold" x-text="`Rule Base — ${selectedComp.name}`"></h3>
        <button @click="openRuleModal(null)"
          class="flex items-center gap-1.5 text-xs text-[#8fa06a] border border-gray-800 hover:border-gray-700 px-3 py-1.5 rounded-lg transition-colors">
          + Tambah Rule
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-800">
              <th class="text-left text-xs text-gray-600 pb-3 pr-4 font-medium uppercase tracking-wide">No</th>
              <th class="text-left text-xs text-gray-600 pb-3 pr-4 font-medium uppercase tracking-wide">Kondisi IF</th>
              <th class="text-left text-xs text-gray-600 pb-3 pr-4 font-medium uppercase tracking-wide">Output THEN</th>
              <th class="text-left text-xs text-gray-600 pb-3 pr-4 font-medium uppercase tracking-wide">Bobot</th>
              <th class="text-left text-xs text-gray-600 pb-3 font-medium uppercase tracking-wide">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-800/50">
            <template x-for="(rule, idx) in selectedComp.rules" :key="rule.id">
              <tr class="hover:bg-gray-800/20 transition-colors">
                <td class="py-3 pr-4 text-gray-600 text-xs" x-text="idx+1"></td>
                <td class="py-3 pr-4 text-sm">
                  <span class="text-gray-400" x-text="varLabels[rule.var1]?.split(' (')[0]"></span>
                  <span class="text-gray-700 mx-1.5">=</span>
                  <span class="text-gray-300" x-text="rule.label1"></span>
                  <span class="text-gray-700 mx-2 text-xs" x-text="rule.operator"></span>
                  <span class="text-gray-400" x-text="varLabels[rule.var2]?.split(' (')[0]"></span>
                  <span class="text-gray-700 mx-1.5">=</span>
                  <span class="text-gray-300" x-text="rule.label2"></span>
                </td>
                <td class="py-3 pr-4">
                  <span class="text-xs px-2.5 py-1 rounded-md border"
                    :class="{
                      'text-gray-400 border-gray-700': rule.output==='Baik',
                      'text-gray-300 border-gray-600': rule.output==='Perlu Servis',
                      'text-white border-gray-500': rule.output==='Kritis'
                    }"
                    x-text="rule.output"></span>
                </td>
                <td class="py-3 pr-4 text-gray-500 text-xs" x-text="parseFloat(rule.weight).toFixed(1)"></td>
                <td class="py-3">
                  <div class="flex items-center gap-1.5">
                    <button @click="openRuleModal(rule)" class="p-1.5 text-gray-600 hover:text-gray-300 rounded-md hover:bg-gray-800 transition-colors">✎</button>
                    <button @click="deleteRule(rule)" class="p-1.5 text-gray-600 hover:text-red-400 rounded-md hover:bg-gray-800 transition-colors">✕</button>
                  </div>
                </td>
              </tr>
            </template>
            <template x-if="!selectedComp.rules?.length">
              <tr><td colspan="5" class="py-8 text-center text-gray-700 text-sm">Belum ada rule. Klik "+ Tambah Rule".</td></tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </template>

  {{-- ══ SECTION 5 — Test Konfigurasi ══ --}}
  <template x-if="selectedComp">
    <div class="bg-[#111111] border border-gray-800 rounded-xl p-5">
      <h3 class="text-white font-semibold mb-4">Test Konfigurasi</h3>
      <div class="grid grid-cols-2 gap-6">
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <template x-for="v in allVars" :key="v.key">
              <div :class="form.activeVars.includes(v.key) ? '' : 'opacity-30'">
                <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wide" x-text="v.label.split(' (')[0]"></label>
                <div class="flex items-center gap-2">
                  <input type="number" x-model="testInputs[v.key]" :disabled="!form.activeVars.includes(v.key)"
                    class="w-full bg-[#0a0a0a] border border-gray-800 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-gray-600 disabled:cursor-not-allowed">
                  <span class="text-xs text-gray-600 shrink-0" x-text="v.unit"></span>
                </div>
              </div>
            </template>
          </div>
          <button @click="runTest()"
            class="w-full bg-[#6b7c4f] hover:bg-[#7a8d5e] text-white py-2.5 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-colors">
            ▶ Hitung Sekarang
          </button>
        </div>

        <div class="bg-[#0a0a0a] border border-gray-800 rounded-lg p-4">
          <template x-if="testResult">
            <div>
              <div class="flex items-end gap-3 mb-3">
                <div>
                  <p class="text-xs text-gray-600 mb-0.5 uppercase tracking-wide">Skor Kondisi</p>
                  <p class="text-4xl text-white" x-text="testResult.score"></p>
                  <p class="text-xs text-gray-600">/ 100</p>
                </div>
                <div class="pb-1.5">
                  <span class="text-xs px-2.5 py-1 rounded-md border"
                    :class="{
                      'text-gray-400 border-gray-700': testResult.label==='Baik',
                      'text-gray-300 border-gray-600': testResult.label==='Perlu Servis',
                      'text-white border-gray-500': testResult.label==='Kritis'
                    }"
                    x-text="testResult.label"></span>
                </div>
              </div>
              <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden mb-4">
                <div class="h-full bg-[#6b7c4f] rounded-full transition-all duration-700" :style="`width:${testResult.score}%`"></div>
              </div>
              <p class="text-xs text-gray-600 uppercase tracking-wide mb-2">Derajat Keanggotaan</p>
              <table class="w-full text-xs">
                <thead>
                  <tr class="border-b border-gray-800">
                    <th class="pb-1.5 font-medium text-gray-700 text-left">Variabel</th>
                    <th class="pb-1.5 font-medium text-gray-700 text-center">Low</th>
                    <th class="pb-1.5 font-medium text-gray-700 text-center">Medium</th>
                    <th class="pb-1.5 font-medium text-gray-700 text-center">High</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/50">
                  <template x-for="(m, varKey) in testResult.membership" :key="varKey">
                    <tr>
                      <td class="py-1.5 text-gray-500" x-text="varLabels[varKey]?.split(' (')[0]"></td>
                      <td class="text-center text-gray-400 py-1.5" x-text="m.low.toFixed(2)"></td>
                      <td class="text-center text-gray-400 py-1.5" x-text="m.medium.toFixed(2)"></td>
                      <td class="text-center text-gray-400 py-1.5" x-text="m.high.toFixed(2)"></td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </template>
          <template x-if="!testResult">
            <div class="h-full flex flex-col items-center justify-center gap-2 py-10">
              <p class="text-4xl text-gray-800">▶</p>
              <p class="text-xs text-gray-700">Klik "Hitung Sekarang" untuk melihat hasil</p>
            </div>
          </template>
        </div>
      </div>
    </div>
  </template>

  {{-- ══ SECTION 6 — Audit Log ══ --}}
  <div class="bg-[#111111] border border-gray-800 rounded-xl p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-white font-semibold">Riwayat Perubahan</h3>
      <a href="{{ route('admin.fuzzy.audit') }}" class="text-xs text-gray-500 hover:text-gray-300 border border-gray-800 hover:border-gray-700 px-3 py-1.5 rounded-lg transition-colors">
        Lihat Semua
      </a>
    </div>
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-gray-800">
          @foreach(['Waktu','Admin','Komponen','Yang Diubah','Perubahan'] as $h)
          <th class="text-left text-xs text-gray-600 pb-3 pr-4 font-medium uppercase tracking-wide">{{ $h }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800/50">
        @foreach($auditLogs as $log)
        <tr class="hover:bg-gray-800/20 transition-colors">
          <td class="py-3 pr-4 text-gray-600 text-xs whitespace-nowrap">{{ $log->created_at->format('d M Y, H:i') }}</td>
          <td class="py-3 pr-4 text-gray-500 text-xs">{{ $log->admin->email ?? '-' }}</td>
          <td class="py-3 pr-4"><span class="text-xs text-gray-400 bg-gray-800 px-2 py-0.5 rounded">{{ $log->component_name }} ({{ $log->motor_type }})</span></td>
          <td class="py-3 pr-4 text-gray-400 text-xs">{{ $log->field_changed }}</td>
          <td class="py-3">
            <div class="flex items-center gap-1.5 text-xs">
              <span class="text-gray-600">{{ $log->old_value ?? '-' }}</span>
              <span class="text-gray-800">→</span>
              <span class="text-gray-300">{{ $log->new_value ?? '-' }}</span>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- ══ MODAL: Tambah Komponen ══ --}}
  @include('admin.fuzzy._modal_add_comp')

  {{-- ══ MODAL: Rule ══ --}}
  @include('admin.fuzzy._modal_rule')

  {{-- ══ DIALOG: Konfirmasi Hapus ══ --}}
  @include('admin.fuzzy._dialog_delete')

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/fuzzy/admin.js') }}"></script>
@endpush
