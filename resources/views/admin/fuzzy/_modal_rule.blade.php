<style>
  .rule-select, .rule-input {
    background: #0d0d0d;
    border: 1px solid #1e2939;
    border-radius: 10px;
    padding: 10px 14px;
    width: 100%;
    font-size: 13px;
    color: #8a919e;
    outline: none;
    transition: border-color .2s;
    appearance: none;
    -webkit-appearance: none;
  }
  .rule-select:focus, .rule-input:focus { border-color: #6b7c4f; }
  .rule-select option { background: #1a1a1a; color: #8a919e; }
  .rule-label {
    display: block;
    font-size: 10px;
    font-weight: 600;
    color: #3d4a56;
    text-transform: uppercase;
    letter-spacing: .1em;
    margin-bottom: 7px;
  }
  .op-btn {
    flex: 1;
    padding: 10px 0;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    border: 1px solid #1e2939;
    background: #0d0d0d;
    color: #3d4a56;
    transition: all 0.2s;
  }
  .op-btn:hover {
    border-color: #2d3748;
    color: #6a7282;
  }
  .op-btn.active {
    background: #6b7c4f;
    border-color: #6b7c4f;
    color: #ffffff;
  }
</style>

<template x-if="showRuleModal">
  <div class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4 modal-backdrop"
    @click.self="showRuleModal=false">
    <div class="bg-[#111111] border border-[#1e2939] rounded-2xl w-full max-w-md shadow-2xl modal-box" style="font-family:'Inter',Arial,sans-serif;">

      {{-- Header --}}
      <div class="flex items-center justify-between px-6 py-5 border-b border-[#1e2939]">
        <div>
          <h3 class="font-semibold text-base" style="color:#adb5bd;" x-text="editRule ? 'Edit Rule' : 'Tambah Rule Baru'"></h3>
          <p class="text-xs mt-0.5" style="color:#3d4a56;">Definisikan kondisi IF–THEN untuk inferensi fuzzy</p>
        </div>
        <button @click="showRuleModal=false" class="transition-colors text-xl leading-none w-8 h-8 flex items-center justify-center rounded-lg" style="color:#3d4a56;" onmouseenter="this.style.color='#8a919e'" onmouseleave="this.style.color='#3d4a56'">&times;</button>
      </div>

      {{-- Body --}}
      <div class="px-6 py-5 space-y-5">

        {{-- Variabel 1 + Label 1 --}}
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="rule-label">Variabel 1</label>
            <select x-model="ruleForm.var1" class="rule-select">
              <template x-for="v in activeVarKeys" :key="v">
                <option :value="v" x-text="varLabels[v]?.split(' (')[0]"></option>
              </template>
            </select>
          </div>
          <div>
            <label class="rule-label">Label</label>
            <select x-model="ruleForm.label1" class="rule-select">
              <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option>
            </select>
          </div>
        </div>

        {{-- Operator --}}
        <div>
          <label class="rule-label">Operator</label>
          <div class="flex gap-2">
            <template x-for="op in ['AND','OR']" :key="op">
              <button type="button" @click="ruleForm.operator=op"
                :class="ruleForm.operator===op ? 'op-btn active' : 'op-btn'"
                x-text="op">
              </button>
            </template>
          </div>
        </div>

        {{-- Variabel 2 + Label 2 --}}
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="rule-label">Variabel 2</label>
            <select x-model="ruleForm.var2" class="rule-select">
              <template x-for="v in activeVarKeys" :key="v">
                <option :value="v" x-text="varLabels[v]?.split(' (')[0]"></option>
              </template>
            </select>
          </div>
          <div>
            <label class="rule-label">Label</label>
            <select x-model="ruleForm.label2" class="rule-select">
              <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option>
            </select>
          </div>
        </div>

        {{-- Output + Bobot --}}
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="rule-label">Output (THEN)</label>
            <select x-model="ruleForm.output" class="rule-select">
              <option>Baik</option><option>Perlu Servis</option><option>Kritis</option>
            </select>
          </div>
          <div>
            <label class="rule-label">Bobot</label>
            <input type="number" min="0" max="1" step="0.1" x-model="ruleForm.weight" class="rule-input">
          </div>
        </div>

        {{-- Preview Rule --}}
        <div style="background:#0d0d0d; border:1px solid #1e2939; border-radius:10px; padding:14px 16px;">
          <p style="font-size:9px; color:#2d3748; text-transform:uppercase; letter-spacing:.1em; font-weight:600; margin-bottom:8px;">Preview Rule</p>
          <div class="flex flex-wrap gap-x-1 gap-y-0.5 text-xs">
            <span style="color:#6b7c4f; font-weight:600;">IF</span>
            <span style="color:#6a7282;" x-text="varLabels[ruleForm.var1]?.split(' (')[0]"></span>
            <span style="color:#2d3748;">=</span>
            <span style="color:#8a919e; font-weight:500;" x-text="ruleForm.label1"></span>
            <span style="color:#6b7c4f; font-weight:600;" x-text="ruleForm.operator"></span>
            <span style="color:#6a7282;" x-text="varLabels[ruleForm.var2]?.split(' (')[0]"></span>
            <span style="color:#2d3748;">=</span>
            <span style="color:#8a919e; font-weight:500;" x-text="ruleForm.label2"></span>
            <span style="color:#6b7c4f; font-weight:600;">THEN</span>
            <span style="color:#8a919e; font-weight:600;" x-text="ruleForm.output"></span>
            <span style="color:#2d3748;" x-text="`[bobot: ${parseFloat(ruleForm.weight).toFixed(1)}]`"></span>
          </div>
        </div>

      </div>

      {{-- Footer --}}
      <div class="flex gap-3 px-6 pb-6">
        <button @click="showRuleModal=false" class="flex-1 py-2.5 border border-[#1e2939] rounded-[10px] text-sm transition-colors" style="color:#4a5565;" onmouseenter="this.style.color='#8a919e'; this.style.borderColor='#2d3748'" onmouseleave="this.style.color='#4a5565'; this.style.borderColor='#1e2939'">Batal</button>
        <button @click="saveRule()" class="flex-1 btn-olive justify-center">&#10003; Simpan Rule</button>
      </div>

    </div>
  </div>
</template>

