<style>
  .add-input, .add-textarea {
    background: #0d0d0d;
    border: 1px solid #1e2939;
    border-radius: 10px;
    padding: 10px 14px;
    width: 100%;
    font-size: 13px;
    color: #8a919e;
    outline: none;
    transition: border-color .2s;
  }
  .add-input:focus, .add-textarea:focus { border-color: #6b7c4f; }
  .add-input::placeholder, .add-textarea::placeholder { color: #2d3748; }
  .add-textarea { resize: none; }
  .add-label {
    display: block;
    font-size: 10px;
    font-weight: 600;
    color: #3d4a56;
    text-transform: uppercase;
    letter-spacing: .1em;
    margin-bottom: 7px;
  }
  .add-unit {
    font-size: 11px;
    color: #3d4a56;
    white-space: nowrap;
  }
  /* Divider vertikal antar kolom */
  .modal-col-divider {
    width: 1px;
    background: #1e2939;
    align-self: stretch;
    flex-shrink: 0;
  }
</style>

<template x-if="showAddComp">
  {{-- Backdrop: full-screen, flex center --}}
  <div
    style="position:fixed; inset:0; background:rgba(0,0,0,.72); z-index:9999;
           display:flex; align-items:center; justify-content:center; padding:24px;"
    @click.self="showAddComp=false"
  >
    {{-- Modal: landscape / horizontal rectangle --}}
    <div class="bg-[#111111] border border-[#1e2939] rounded-2xl w-full max-w-[860px] shadow-[0_25px_60px_rgba(0,0,0,0.85)] flex flex-col font-['Inter',Arial,sans-serif]"
         style="animation:slideUp .18s ease; max-height:80vh; overflow:hidden;">

      {{-- ══ Header ══ --}}
      <div class="flex items-start justify-between px-6 py-4 border-b border-[#1e2939]">
        <div>
          <h3 class="font-semibold text-base" style="color:#adb5bd;">Tambah Komponen Baru</h3>
          <p class="text-xs mt-0.5" style="color:#3d4a56;">Komponen baru akan tersedia di semua jenis motor yang dipilih</p>
        </div>
        <button @click="showAddComp=false"
          class="w-8 h-8 flex items-center justify-center rounded-lg text-xl leading-none transition-colors"
          style="color:#3d4a56;"
          onmouseenter="this.style.color='#8a919e';this.style.background='#1e2939'"
          onmouseleave="this.style.color='#3d4a56';this.style.background='transparent'">&times;</button>
      </div>

      {{-- ══ Body: dua kolom horizontal ══ --}}
      <div class="flex flex-col md:flex-row overflow-y-auto" style="flex:1; min-height:0;">

        {{-- Kolom Kiri --}}
        <div class="flex-1 p-5 md:p-6 flex flex-col gap-[18px] shrink-0">

          {{-- Error --}}
          <template x-if="addCompError">
            <div class="flex items-center gap-2 border text-xs px-3 py-2.5 rounded-[10px]"
              style="background:rgba(239,68,68,.06); border-color:rgba(239,68,68,.2); color:#f87171;">
              ⚠ <span x-text="addCompError"></span>
            </div>
          </template>

          {{-- Nama Komponen --}}
          <div>
            <label class="add-label">Nama Komponen</label>
            <input type="text" x-model="addCompForm.name"
              placeholder="contoh: Kampas Kopling"
              @input="addCompError=''"
              class="add-input">
          </div>

          {{-- Jenis Motor --}}
          <div>
            <label class="add-label">Berlaku untuk Jenis Motor</label>
            <div class="grid grid-cols-2 gap-2">
              @foreach($motorTypes as $mt)
              <button type="button" @click="toggleAddMotorType('{{ $mt->slug }}')"
                :class="addCompForm.motorTypes.includes('{{ $mt->slug }}')
                  ? 'border-[#6b7c4f]/40 text-[#8a919e]'
                  : 'border-[#1e2939] text-[#3d4a56]'"
                class="flex items-center gap-2.5 px-4 py-2.5 rounded-[10px] border text-sm transition-colors text-left"
                style="background:#0d0d0d;">
                <span
                  :class="addCompForm.motorTypes.includes('{{ $mt->slug }}') ? 'bg-[#6b7c4f] border-[#6b7c4f]' : 'border-[#1e2939]'"
                  class="w-4 h-4 rounded border flex items-center justify-center shrink-0 transition-colors">
                  <span x-show="addCompForm.motorTypes.includes('{{ $mt->slug }}')" style="color:#fff; font-size:10px; line-height:1;">✓</span>
                </span>
                <span style="font-size:13px;">{{ $mt->name }}</span>
              </button>
              @endforeach
            </div>
          </div>

          {{-- Catatan --}}
          <div style="flex:1; display:flex; flex-direction:column;">
            <label class="add-label">Catatan <span style="text-transform:none; color:#2d3748;">(opsional)</span></label>
            <textarea x-model="addCompForm.notes" rows="3"
              placeholder="contoh: Diganti setiap 20.000 km atau 2 tahun"
              class="add-textarea" style="flex:1;"></textarea>
          </div>

        </div>

        {{-- Divider vertikal --}}
        <div class="hidden md:block modal-col-divider"></div>
        <div class="block md:hidden h-px bg-[#1e2939] mx-5 shrink-0"></div>

        {{-- Kolom Kanan --}}
        <div class="flex-1 p-5 md:p-6 flex flex-col gap-[18px] shrink-0">

          {{-- Threshold --}}
          <div>
            <label class="add-label">Threshold Awal</label>
            <div style="display:flex; flex-direction:column; gap:10px;">
              <div style="display:grid; grid-template-columns:1fr auto; align-items:center; gap:8px;">
                <div>
                  <label class="add-label" style="font-size:9px;">Warn</label>
                  <input type="number" x-model="addCompForm.warn" class="add-input" placeholder="0">
                </div>
                <span class="add-unit" style="margin-top:18px;">km</span>
              </div>
              <div style="display:grid; grid-template-columns:1fr auto; align-items:center; gap:8px;">
                <div>
                  <label class="add-label" style="font-size:9px;">Critical</label>
                  <input type="number" x-model="addCompForm.critical" class="add-input" placeholder="0">
                </div>
                <span class="add-unit" style="margin-top:18px;">km</span>
              </div>
              <div style="display:grid; grid-template-columns:1fr auto; align-items:center; gap:8px;">
                <div>
                  <label class="add-label" style="font-size:9px;">Reset Interval</label>
                  <input type="number" x-model="addCompForm.resetInterval" class="add-input" placeholder="0">
                </div>
                <span class="add-unit" style="margin-top:18px;">hari</span>
              </div>
            </div>
          </div>

          {{-- Variabel Aktif --}}
          <div style="flex:1; display:flex; flex-direction:column;">
            <label class="add-label">Variabel Input Aktif</label>
            <div style="background:#0d0d0d; border:1px solid #1e2939; border-radius:10px; overflow:hidden; flex:1;">
              <template x-for="v in allVars" :key="v.key">
                <div :class="addCompForm.activeVars.includes(v.key) ? 'bg-[#111111]' : ''"
                  class="flex items-center justify-between px-4 py-3 border-b border-[#1e2939] last:border-b-0 transition-colors">
                  <span :class="addCompForm.activeVars.includes(v.key) ? 'text-[#8a919e]' : 'text-[#3d4a56]'"
                    class="text-sm pr-4" x-text="v.label"></span>
                  <button type="button"
                    :class="[addCompForm.activeVars.includes(v.key) ? 'on' : 'off', addCompForm.activeVars.includes(v.key) && addCompForm.activeVars.length <= 1 ? 'disabled' : '']"
                    @click="toggleAddVar(v.key)" class="toggle-btn shrink-0"><span class="knob"></span></button>
                </div>
              </template>
            </div>
            <p class="text-xs mt-2" style="color:#2d3748;">Bisa diubah lagi setelah komponen disimpan.</p>
          </div>

        </div>
      </div>

      {{-- ══ Footer ══ --}}
      <div class="flex gap-3 px-6 py-4 border-t border-[#1e2939]">
        <button @click="showAddComp=false"
          class="flex-1 py-2.5 border rounded-[10px] text-sm transition-colors"
          style="border-color:#1e2939; color:#4a5565;"
          onmouseenter="this.style.color='#8a919e'; this.style.borderColor='#2d3748'"
          onmouseleave="this.style.color='#4a5565'; this.style.borderColor='#1e2939'">Batal</button>
        <button @click="saveComp()" class="flex-1 btn-olive justify-center">&#10003; Simpan Komponen</button>
      </div>

    </div>
  </div>
</template>

<script>
  window.motorTypeMap = @json($motorTypes->pluck('id', 'slug'));
</script>
