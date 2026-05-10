<template x-if="deleteTarget">
  <div class="fixed inset-0 bg-black/70 z-[110] flex items-center justify-center p-4 modal-backdrop">
    <div class="bg-[#111111] border border-[#1e2939] rounded-2xl w-full max-w-sm shadow-2xl modal-box" style="font-family:'Inter',Arial,sans-serif;">

      {{-- Header --}}
      <div class="px-6 pt-6 pb-4 flex items-start gap-4">
        <div class="w-11 h-11 bg-red-500/10 border border-red-500/25 rounded-xl flex items-center justify-center shrink-0">
          <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
        </div>
        <div>
          <h3 class="text-white font-semibold text-base mb-1">Hapus Komponen Permanen</h3>
          <p class="text-[#99a1af] text-sm leading-relaxed">
            Yakin ingin menghapus komponen
            <span class="text-white font-semibold" x-text="`\"${deleteTarget?.name}\"`"></span>?
            <br>Semua konfigurasi dan riwayat data akan ikut terhapus dan <span class="text-red-400 font-medium">tidak bisa dikembalikan</span>.
          </p>
        </div>
      </div>

      {{-- Divider --}}
      <div class="mx-6 border-t border-[#1e2939]"></div>

      {{-- Actions --}}
      <div class="flex gap-3 px-6 py-4">
        <button @click="deleteTarget=null"
          class="flex-1 py-2.5 border border-[#1e2939] hover:border-[#364153] text-[#6a7282] hover:text-white rounded-[10px] text-sm transition-colors">
          Batal
        </button>
        <button @click="handleDelete()"
          class="flex-1 py-2.5 bg-red-600 hover:bg-red-500 active:bg-red-700 text-white rounded-[10px] text-sm font-semibold transition-colors flex items-center justify-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6"/>
          </svg>
          Hapus Sekarang
        </button>
      </div>

    </div>
  </div>
</template>
