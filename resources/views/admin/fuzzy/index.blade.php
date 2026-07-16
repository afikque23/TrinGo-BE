@extends('layouts.sidebar')

@section('title', 'Konfigurasi Fuzzy - MotoTracker Admin')
@section('page-title', 'Konfigurasi Fuzzy')

@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

  /* ─── Base font ─── */
  [x-data] { font-family: 'Inter', Arial, sans-serif; }

  /* ─── Toggle switch (circle stays INSIDE pill) ─── */
  .toggle-btn {
    position: relative;
    display: inline-block;
    width: 44px; height: 24px;
    border-radius: 999px;
    transition: background .25s;
    cursor: pointer;
    border: none; outline: none;
    flex-shrink: 0;
  }
  .toggle-btn.on  { background: #6b7c4f; }
  .toggle-btn.off { background: #2d3748; }
  .toggle-btn.disabled { opacity: .4; cursor: not-allowed; pointer-events: none; }
  .toggle-btn .knob {
    position: absolute;
    top: 3px; left: 3px;
    width: 18px; height: 18px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.5);
    transition: transform .25s cubic-bezier(.4,0,.2,1);
  }
  .toggle-btn.on .knob  { transform: translateX(20px); }
  .toggle-btn.off .knob { transform: translateX(0); }

  /* ─── Section cards ─── */
  .fz-card {
    background: #111111;
    border: 1px solid #1e2939;
    border-radius: 14px;
    padding: 26px;
  }

  /* ─── Section header ─── */
  .fz-section-title {
    font-size: 15px;
    font-weight: 600;
    color: #e2e8f0;
    margin: 0;
  }

  /* ─── Primary olive button ─── */
  .btn-olive {
    display: inline-flex; align-items: center; gap: 6px;
    background: #6b7c4f;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 0 16px;
    height: 38px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background .2s, transform .1s;
  }
  .btn-olive:hover { background: #7d9059; }
  .btn-olive:active { transform: scale(.98); }
  .btn-olive:disabled { opacity: .55; cursor: not-allowed; }

  /* ─── Ghost button ─── */
  .btn-ghost {
    display: inline-flex; align-items: center; gap: 6px;
    background: transparent;
    color: #8fa06a;
    border: 1px solid #2d3a2a;
    border-radius: 10px;
    padding: 0 12px;
    height: 32px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
    transition: border-color .2s, background .2s;
  }
  .btn-ghost:hover { background: rgba(107,124,79,.12); border-color: #6b7c4f; }

  /* ─── Subtle button ─── */
  .btn-subtle {
    display: inline-flex; align-items: center; gap: 6px;
    background: transparent;
    color: #6a7282;
    border: 1px solid #1e2939;
    border-radius: 10px;
    padding: 0 12px;
    height: 32px;
    font-size: 12px;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
    transition: color .2s, border-color .2s;
  }
  .btn-subtle:hover { color: #d1d5dc; border-color: #364153; }

  /* ─── Input ─── */
  .fz-input {
    width: 100%;
    background: #0a0a0a;
    border: 1px solid #1e2939;
    border-radius: 10px;
    padding: 0 12px;
    height: 38px;
    color: #fff;
    font-size: 13px;
    outline: none;
    transition: border-color .2s;
  }
  .fz-input:focus { border-color: #6b7c4f; }

  select.fz-input { appearance: none; }

  /* ─── Chart container ─── */
  .chart-wrap {
    background: #0a0a0a;
    border: 1px solid #1e2939;
    border-radius: 10px;
    padding: 14px;
  }

  /* ─── Rule table ─── */
  .rule-table th {
    padding: 8px 16px 8px 0;
    font-size: 11px;
    font-weight: 600;
    color: #4a5565;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-bottom: 1px solid #1e2939;
  }
  .rule-table td { padding: 11px 16px 11px 0; border-bottom: 1px solid #1e293920; }
  .rule-table tbody tr:last-child td { border-bottom: none; }
  .rule-table tbody tr:hover td { background: #ffffff04; }

  /* ─── Badge ─── */
  .badge-baik     { border: 1px solid #2d3a2a; color: #6b7c4f; }
  .badge-servis   { border: 1px solid #4a3a1a; color: #d08700; }
  .badge-kritis   { border: 1px solid #4a1a1a; color: #e55353; }
  .badge { display:inline-flex; align-items:center; padding:2px 10px; border-radius:6px; font-size:11px; font-weight:500; }

  /* ─── Audit table ─── */
  .audit-table th { padding: 8px 14px 8px 0; font-size:11px; color:#4a5565; text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid #1e2939; }
  .audit-table td { padding: 10px 14px 10px 0; font-size:12px; border-bottom:1px solid #1e293920; }

  /* ─── Animations ─── */
  @keyframes fadeIn  { from{opacity:0}       to{opacity:1} }
  @keyframes slideUp { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
  .modal-backdrop { animation:fadeIn .18s ease; }
  .modal-box      { animation:slideUp .18s ease; }

  /* ─── Chart canvas ─── */
  #mf-chart { width:100%!important; height:190px!important; }
</style>

<div x-data="fuzzyAdmin()" x-init="init()" class="flex flex-col gap-4">

  {{-- Toast --}}
  <template x-if="toast">
    <div class="fixed top-5 right-6 z-[200] flex items-center gap-2.5 bg-[#6b7c4f] text-white text-sm px-4 py-2.5 rounded-lg shadow-xl">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      <span x-text="toast"></span>
    </div>
  </template>

  {{-- ── Page Header ── --}}
  <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-2">
    <div>
      <h2 class="text-[#e2e8f0] text-xl sm:text-2xl font-bold mb-1">Konfigurasi Fuzzy Logic</h2>
      <p class="text-[#6a7282] text-sm">Atur parameter sistem rekomendasi perawatan motor</p>
    </div>
    <button @click="saveAll()" class="btn-olive h-10 px-5 shrink-0">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
      <span>Simpan Semua Perubahan</span>
      <span x-show="hasChanges" class="w-2 h-2 rounded-full bg-yellow-400"></span>
    </button>
  </div>

  {{-- ══ SECTION 1 — Pilih Jenis Motor & Komponen ══ --}}
  <div class="fz-card"> {{-- Section 1 --}}
    <div class="flex items-center justify-between mb-5">
      <h3 class="fz-section-title">Pilih Jenis Motor &amp; Komponen</h3>
      <button @click="openAddComp()" class="btn-ghost">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Komponen
      </button>
    </div>

    {{-- Motor Tabs (scrollable on mobile) --}}
    <div class="overflow-x-auto mb-5">
      <div class="flex gap-1 bg-[#0a0a0a] border border-gray-800 rounded-lg p-1 w-max min-w-full sm:w-fit">
        @foreach($motorTypes as $mt)
        <button
          :class="activeTab === {{ $mt->id }} ? 'bg-[#6b7c4f] text-white font-medium' : 'text-gray-400 hover:text-gray-300'"
          @click="switchTab({{ $mt->id }})"
          class="relative px-4 py-1.5 rounded-md text-sm transition-colors whitespace-nowrap"
          style="font-family: Arial, sans-serif;">
          {{ $mt->name }}
        </button>
        @endforeach
      </div>
    </div>

    {{-- Component Badges --}}
    <div class="flex flex-wrap gap-2">
      <template x-for="comp in currentComponents" :key="comp.id">
        <div class="relative" @click.outside="if(openMenu===comp.id) openMenu=null" :class="{'z-50': openMenu===comp.id}">
          <div :class="{
              'bg-[#6b7c4f] border-[#6b7c4f] text-white': selectedComp?.id === comp.id,
              'bg-[#0a0a0a] border-gray-800 text-gray-600 opacity-50': !comp.is_active,
              'bg-[#0a0a0a] border-gray-800 text-gray-300 hover:border-gray-700': comp.is_active && selectedComp?.id !== comp.id,
            }"
            class="group flex items-center gap-2 pl-3 pr-2 py-1.5 rounded-lg text-sm border transition-all">

            <button @click="!comp.is_active || selectComp(comp)" class="flex items-center gap-2 min-w-0" style="font-family: Arial, sans-serif;">
              <span :class="{
                'bg-[#6b7c4f]': comp.status==='good',
                'bg-yellow-600': comp.status==='warning',
                'bg-red-700': comp.status==='critical'
              }" class="w-1.5 h-1.5 rounded-full shrink-0"></span>

              <span x-text="comp.name"></span>

              <span x-show="!comp.is_active" class="text-[10px] text-gray-600 border border-gray-800 px-1 rounded">nonaktif</span>
              <span x-show="comp.is_custom && comp.is_active" class="text-[10px] text-[#8fa06a] border border-[#6b7c4f]/30 px-1 rounded">custom</span>
            </button>

            <button @click.stop="openMenu = openMenu===comp.id ? null : comp.id"
              :class="openMenu===comp.id ? 'opacity-100 text-white' : (selectedComp?.id===comp.id ? 'opacity-60 hover:opacity-100 text-white' : 'opacity-0 group-hover:opacity-100 text-gray-500')"
              class="p-0.5 rounded transition-opacity">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
            </button>
          </div>

          <div x-show="openMenu===comp.id" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="absolute top-full left-0 mt-1.5 z-[100] border border-[#2d3748] rounded-xl shadow-2xl overflow-hidden min-w-[190px]"
            style="background:#141414; box-shadow:0 8px 32px rgba(0,0,0,.85);">

            {{-- Aktifkan kembali (hanya jika nonaktif) --}}
            <button x-show="!comp.is_active" @click="toggleComp(comp); openMenu=null"
              class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-[#d1d5dc] hover:bg-[#6b7c4f]/15 hover:text-white transition-colors text-left whitespace-nowrap">
              <svg class="w-4 h-4 text-[#6b7c4f] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Aktifkan Kembali
            </button>

            {{-- Nonaktifkan (hanya jika aktif) --}}
            <button x-show="comp.is_active" @click="toggleComp(comp); openMenu=null"
              class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-[#d1d5dc] hover:bg-[#1e2939] hover:text-white transition-colors text-left whitespace-nowrap">
              <svg class="w-4 h-4 text-[#6a7282] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
              Nonaktifkan
            </button>

            <div class="mx-3 my-1 border-t border-[#2d3748]"></div>

            {{-- Hapus Permanen --}}
            <button @click="confirmDelete(comp); openMenu=null"
              class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors text-left whitespace-nowrap">
              <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Hapus Permanen
            </button>
          </div>
        </div>
      </template>
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-800" style="font-family: Arial, sans-serif;">
      <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#6b7c4f]"></span><span class="text-xs text-gray-500">Baik</span></div>
      <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-yellow-600"></span><span class="text-xs text-gray-500">Perlu Servis</span></div>
      <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-red-700"></span><span class="text-xs text-gray-500">Kritis</span></div>
    </div>
  </div>

  {{-- ══ SECTION 2 — Threshold ══ --}}
  <template x-if="selectedComp">
    <div class="fz-card"> {{-- Section 2 Threshold --}}
      <div class="flex items-center justify-between mb-5">
        <h3 class="fz-section-title" x-text="`Threshold — ${selectedComp.name}`"></h3>
        <button @click="resetThreshold()" class="btn-subtle">Reset ke Default</button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
        <div>
          <label class="block text-xs text-[#6a7282] mb-2 uppercase tracking-widest">Warn</label>
          <div class="flex items-center gap-2">
            <input type="number" x-model="form.warn" class="fz-input">
            <span class="text-xs text-[#4a5565] shrink-0">km</span>
          </div>
        </div>
        <div>
          <label class="block text-xs text-[#6a7282] mb-2 uppercase tracking-widest">Critical</label>
          <div class="flex items-center gap-2">
            <input type="number" x-model="form.critical" class="fz-input">
            <span class="text-xs text-[#4a5565] shrink-0">km</span>
          </div>
        </div>
        <div>
          <label class="block text-xs text-[#6a7282] mb-2 uppercase tracking-widest">Reset Interval</label>
          <div class="flex items-center gap-2">
            <input type="number" x-model="form.resetInterval" class="fz-input">
            <span class="text-xs text-[#4a5565] shrink-0">hari</span>
          </div>
        </div>
      </div>

      <p class="text-xs text-[#4a5565] flex items-center gap-1.5 mb-5">
        ⓘ Nilai warn dan critical digunakan sebagai batas keanggotaan medium dan high pada Fuzzy Logic.
      </p>

      <div class="bg-[#0a0a0a] border border-[#1e2939] rounded-[10px] p-4">
        <p class="text-sm text-[#c8cdd6] font-semibold mb-1" x-text="`Variabel Input Aktif — ${selectedComp.name}`"></p>
        <p class="text-xs text-[#6a7282] mb-4">Pilih variabel IoT yang digunakan untuk menghitung kondisi komponen ini. Minimal 1 variabel.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
          <template x-for="v in allVars" :key="v.key">
            <div :class="form.activeVars.includes(v.key) ? 'bg-[#111111] border-[#364153]' : 'bg-[#0a0a0a] border-[#1e2939]'"
              class="flex items-center justify-between px-5 py-3.5 rounded-lg border transition-colors">
              <span :class="form.activeVars.includes(v.key) ? 'text-gray-200' : 'text-gray-600'" class="text-sm pr-4" x-text="v.label"></span>
              <button type="button"
                :class="[form.activeVars.includes(v.key) ? 'on' : 'off', form.activeVars.includes(v.key) && form.activeVars.length <= 1 ? 'disabled' : '']"
                @click="toggleVar(v.key)"
                class="toggle-btn shrink-0">
                <span class="knob"></span>
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </template>

  {{-- ══ SECTION 3 — Membership Function ══ --}}
  <template x-if="selectedComp">
    <div class="fz-card"> {{-- Section 3 Membership --}}
      <h3 class="fz-section-title mb-3" x-text="`Membership Function — ${mfVarLabel}`"></h3>
      <p class="text-xs text-[#6a7282] mb-5">Atur nilai batas keanggotaan (low / medium / high) untuk setiap variabel input.</p>

      <div class="flex gap-1.5 mb-6">
        <template x-for="v in activeVarKeys" :key="v">
          <button @click="switchMfVar(v)"
            :class="mfVar === v ? 'bg-[#6b7c4f] border-[#6b7c4f] text-white' : 'border-gray-800 text-gray-500 hover:border-gray-700'"
            class="px-3 py-1.5 rounded-lg text-xs border transition-colors"
            x-text="varLabels[v].split(' (')[0]"
            style="font-family: Arial, sans-serif;">
          </button>
        </template>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-4">
          <template x-for="lbl in ['low','medium','high']" :key="lbl">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide mb-2" x-text="lbl" style="font-family: Arial, sans-serif;"></p>
              <div class="grid grid-cols-3 gap-2">
                <template x-for="(pt, idx) in ['a','b','c']" :key="pt">
                  <div>
                    <input type="number"
                      :value="mfForm[mfVar]?.[lbl]?.[idx] ?? 0"
                      @input="updateMF(lbl, idx, $event.target.value)"
                      class="fz-input text-center">
                    <p class="text-center text-[10px] text-[#364153] mt-1" x-text="`[${pt}]`"></p>
                  </div>
                </template>
              </div>
            </div>
          </template>
          <p class="text-xs text-[#4a5565] mt-2">ⓘ [a] batas awal · [b] puncak · [c] batas akhir. Gunakan 999 untuk ∞ pada High.</p>
        </div>

        <div>
          <div class="flex items-center gap-4 mb-3" style="font-family: Arial, sans-serif;">
            <div class="flex items-center gap-1.5"><span class="w-4 h-0.5 rounded bg-[#6b7c4f]"></span><span class="text-xs text-gray-500">Low</span></div>
            <div class="flex items-center gap-1.5"><span class="w-4 h-0.5 rounded bg-gray-400"></span><span class="text-xs text-gray-500">Medium</span></div>
            <div class="flex items-center gap-1.5"><span class="w-4 h-0.5 rounded bg-gray-200"></span><span class="text-xs text-gray-500">High</span></div>
          </div>
          <div class="chart-wrap">
            <canvas id="mf-chart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </template>

  {{-- ══ SECTION 4 — Rule Base ══ --}}
  <template x-if="selectedComp">
    <div class="fz-card"> {{-- Section 4 Rule Base --}}
      <div class="flex items-center justify-between mb-5">
        <h3 class="fz-section-title" x-text="`Rule Base — ${selectedComp.name}`"></h3>
        <button @click="openRuleModal(null)" class="btn-ghost">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Tambah Rule
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="rule-table w-full text-sm">
          <thead>
            <tr>
              <th class="text-left">No</th>
              <th class="text-left">Kondisi IF</th>
              <th class="text-left">Output THEN</th>
              <th class="text-left">Bobot</th>
              <th class="text-left">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <template x-for="(rule, idx) in selectedComp.rules" :key="rule.id">
              <tr>
                <td class="text-[#4a5565] text-xs" x-text="idx+1"></td>
                <td class="text-sm text-[#99a1af]">
                  <span x-text="varLabels[rule.var1]?.split(' (')[0]"></span>
                  <span class="text-[#4a5565] mx-1">=</span>
                  <span class="text-white font-medium" x-text="rule.label1"></span>
                  <span class="text-[#6b7c4f] mx-2 text-xs font-bold" x-text="rule.operator"></span>
                  <span x-text="varLabels[rule.var2]?.split(' (')[0]"></span>
                  <span class="text-[#4a5565] mx-1">=</span>
                  <span class="text-white font-medium" x-text="rule.label2"></span>
                </td>
                <td>
                  <span class="badge"
                    :class="{
                      'badge-baik':   rule.output==='Baik',
                      'badge-servis': rule.output==='Perlu Servis',
                      'badge-kritis': rule.output==='Kritis'
                    }"
                    x-text="rule.output"></span>
                </td>
                <td class="text-[#6a7282] text-xs" x-text="parseFloat(rule.weight).toFixed(1)"></td>
                <td>
                  <div class="flex items-center gap-1">
                    <button @click="openRuleModal(rule)" class="p-1.5 text-[#6a7282] hover:text-white rounded-md hover:bg-[#1e2939] transition-colors" title="Edit">✎</button>
                    <button @click="deleteRule(rule)" class="p-1.5 text-[#6a7282] hover:text-red-400 rounded-md hover:bg-[#1e2939] transition-colors" title="Hapus">✕</button>
                  </div>
                </td>
              </tr>
            </template>
            <template x-if="!selectedComp.rules?.length">
              <tr><td colspan="5" class="py-8 text-center text-[#4a5565] text-sm">Belum ada rule. Klik "Tambah Rule" untuk menambahkan.</td></tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </template>

  {{-- ══ SECTION 5 — Test Konfigurasi ══ --}}
  <template x-if="selectedComp">
    <div class="fz-card"> {{-- Section 5 Test --}}
      <h3 class="fz-section-title mb-5">Test Konfigurasi</h3>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <template x-for="v in allVars" :key="v.key">
              <div :class="form.activeVars.includes(v.key) ? '' : 'opacity-30'">
                <label class="block text-xs text-[#6a7282] mb-2 uppercase tracking-widest" x-text="v.label.split(' (')[0]"></label>
                <div class="flex items-center gap-2">
                  <input type="number" x-model="testInputs[v.key]" :disabled="!form.activeVars.includes(v.key)" class="fz-input disabled:opacity-50 disabled:cursor-not-allowed">
                  <span class="text-xs text-[#4a5565] shrink-0" x-text="v.unit"></span>
                </div>
              </div>
            </template>
          </div>
          <button @click="runTest()" class="btn-olive w-full justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Hitung Sekarang
          </button>
        </div>

        <div class="bg-[#0a0a0a] border border-gray-800 rounded-lg p-4">
          <template x-if="testResult">
            <div>
              <div class="flex items-end gap-3 mb-3">
                <div>
                  <p class="text-xs text-gray-600 mb-0.5 uppercase tracking-wide" style="font-family: Arial, sans-serif;">Skor Kondisi</p>
                  <p class="text-4xl text-white" x-text="testResult.score" style="font-family: Arial, sans-serif;"></p>
                  <p class="text-xs text-gray-600" style="font-family: Arial, sans-serif;">/ 100</p>
                </div>
                <div class="pb-1.5">
                  <span class="text-xs px-2.5 py-1 rounded-md border"
                    :class="{
                      'text-gray-400 border-gray-700': testResult.label==='Baik',
                      'text-gray-300 border-gray-600': testResult.label==='Perlu Servis',
                      'text-white border-gray-500': testResult.label==='Kritis'
                    }"
                    x-text="testResult.label"
                    style="font-family: Arial, sans-serif;"></span>
                </div>
              </div>
              <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden mb-4">
                <div class="h-full bg-[#6b7c4f] rounded-full transition-all duration-700" :style="`width:${testResult.score}%`"></div>
              </div>
              <p class="text-xs text-gray-600 uppercase tracking-wide mb-2" style="font-family: Arial, sans-serif;">Derajat Keanggotaan</p>
              <table class="w-full text-xs">
                <thead>
                  <tr class="border-b border-gray-800">
                    <th class="pb-1.5 font-medium text-gray-700 text-left" style="font-family: Arial, sans-serif;">Variabel</th>
                    <th class="pb-1.5 font-medium text-gray-700 text-center" style="font-family: Arial, sans-serif;">Low</th>
                    <th class="pb-1.5 font-medium text-gray-700 text-center" style="font-family: Arial, sans-serif;">Medium</th>
                    <th class="pb-1.5 font-medium text-gray-700 text-center" style="font-family: Arial, sans-serif;">High</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/50">
                  <template x-for="(m, varKey) in testResult.membership" :key="varKey">
                    <tr>
                      <td class="py-1.5 text-gray-500" x-text="varLabels[varKey]?.split(' (')[0]" style="font-family: Arial, sans-serif;"></td>
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
            <div class="h-full flex flex-col items-center justify-center gap-2 py-10" style="font-family: Arial, sans-serif;">
              <p class="text-4xl text-gray-800">▶</p>
              <p class="text-xs text-gray-700">Klik "Hitung Sekarang" untuk melihat hasil</p>
            </div>
          </template>
        </div>
      </div>
    </div>
  </template>

  @include('admin.fuzzy._modal_add_comp')
  @include('admin.fuzzy._modal_rule')
  @include('admin.fuzzy._dialog_delete')

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/fuzzy/admin.js') }}?v={{ filemtime(public_path('js/fuzzy/admin.js')) }}"></script>
@endsection
