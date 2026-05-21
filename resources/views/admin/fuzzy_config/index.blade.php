@extends('layouts.sidebar')

@section('title', 'Konfigurasi Fuzzy - MotoTracker Admin')

@section('page-title', 'Fuzzy Logic')

@section('content')
<div
    x-data="fuzzyLogicAdmin({ initialMotorType: @js($motorType ?? 'matic') })"
    x-init="init()"
    class="flex flex-col gap-6"
>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white" style="font-family: Arial, sans-serif;">Konfigurasi Fuzzy Logic</h1>
            <p class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Atur parameter sistem rekomendasi perawatan motor</p>
        </div>

        <button
            type="button"
            @click="saveAll()"
            :disabled="saving || items.length === 0"
            class="h-10 px-4 bg-[#6B7C4F] rounded-[10px] text-sm text-white inline-flex items-center gap-2 disabled:opacity-60"
            style="font-family: Arial, sans-serif;"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-8 0h8m-8 0a2 2 0 01-2-2V7a2 2 0 012-2h6l4 4v6a2 2 0 01-2 2" />
            </svg>
            <span x-text="saving ? 'Menyimpan…' : 'Simpan Semua Perubahan'"></span>
        </button>
    </div>

    <!-- Pilih Jenis Motor & Komponen -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-white" style="font-family: Arial, sans-serif;">Pilih Jenis Motor &amp; Komponen</h2>
            <button
                type="button"
                @click="showAddComponent = !showAddComponent"
                class="h-[30px] px-3 bg-transparent border border-[#364153] rounded-[10px] text-xs text-[#8FA06A] inline-flex items-center gap-2"
                style="font-family: Arial, sans-serif;"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 5v14M5 12h14" />
                </svg>
                <span>Tambah Komponen</span>
            </button>
        </div>

        <!-- Motor type segmented -->
        <div class="mt-4 bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-1 inline-flex gap-1">
            <template x-for="type in motorTypes" :key="type">
                <button
                    type="button"
                    @click="selectMotorType(type)"
                    class="h-8 px-3 rounded-[8px] text-sm"
                    :class="motorType === type ? 'bg-[#6B7C4F] text-white' : 'text-[#99A1AF] hover:bg-[#1E2939]'"
                    style="font-family: Arial, sans-serif;"
                    x-text="labelMotorType(type)"
                ></button>
            </template>
        </div>

        <!-- Component chips -->
        <div class="mt-5 flex flex-wrap gap-2">
            <template x-for="it in items" :key="it.id">
                <button
                    type="button"
                    @click="selectComponent(it.id)"
                    class="h-[34px] px-3 rounded-[10px] border text-sm inline-flex items-center gap-2"
                    :class="selectedId === it.id ? 'bg-[#6B7C4F] border-[#6B7C4F] text-white' : 'bg-[#0A0A0A] border-[#1E2939] text-[#D1D5DC]'"
                    style="font-family: Arial, sans-serif;"
                >
                    <span class="w-1.5 h-1.5 rounded-full" :class="it.is_active ? (selectedId === it.id ? 'bg-white' : 'bg-[#6B7C4F]') : 'bg-[#4A5565]'" aria-hidden="true"></span>
                    <span x-text="it.component?.name ?? '-'" class="truncate max-w-[180px]"></span>
                </button>
            </template>

            <div x-show="items.length === 0" class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Belum ada komponen untuk tipe motor ini.</div>
        </div>

        <!-- Legend -->
        <div class="mt-5 pt-3 border-t border-[#1E2939] flex flex-wrap items-center gap-6 text-xs" style="font-family: Arial, sans-serif;">
            <div class="inline-flex items-center gap-2 text-[#6A7282]"><span class="w-1.5 h-1.5 rounded-full bg-[#6B7C4F]"></span> Baik</div>
            <div class="inline-flex items-center gap-2 text-[#6A7282]"><span class="w-1.5 h-1.5 rounded-full bg-[#D08700]"></span> Perlu Servis</div>
            <div class="inline-flex items-center gap-2 text-[#6A7282]"><span class="w-1.5 h-1.5 rounded-full bg-[#C10007]"></span> Kritis</div>
            <div class="inline-flex items-center gap-2 text-[#4A5565]"><span class="px-1.5 py-0.5 border border-[#1E2939] rounded text-[10px]">nonaktif</span> Disembunyikan dari user</div>
        </div>

        <!-- Add mapping (collapsed) -->
        <div x-show="showAddComponent" x-collapse class="mt-4">
            <form method="POST" action="{{ route('admin.fuzzy-config.store') }}" class="bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-4 flex flex-col gap-3">
                @csrf
                <input type="hidden" name="motor_type" :value="motorType" />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-[#6A7282] mb-1" style="font-family: Arial, sans-serif;">Komponen</label>
                        <select name="maintenance_component_id" class="w-full bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 py-2 text-sm text-white">
                            <template x-for="c in availableComponents" :key="c.id">
                                <option :value="c.id" x-text="c.name + ' (' + c.key + ')'"> </option>
                            </template>
                        </select>
                        <p x-show="availableComponents.length === 0" class="text-xs text-[#99A1AF] mt-2" style="font-family: Arial, sans-serif;">Semua komponen aktif sudah ter-mapping.</p>
                    </div>

                    <div>
                        <label class="block text-xs text-[#6A7282] mb-1" style="font-family: Arial, sans-serif;">Warn</label>
                        <input type="number" name="warn_score" min="0" max="100" value="60" class="w-full bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 py-2 text-sm text-white" />
                    </div>

                    <div>
                        <label class="block text-xs text-[#6A7282] mb-1" style="font-family: Arial, sans-serif;">Critical</label>
                        <input type="number" name="critical_score" min="0" max="100" value="40" class="w-full bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 py-2 text-sm text-white" />
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="h-10 px-4 bg-[#6B7C4F] rounded-[10px] text-sm text-white disabled:opacity-60" style="font-family: Arial, sans-serif;" :disabled="availableComponents.length === 0">
                        Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Threshold -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-5" x-show="selected">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-white" style="font-family: Arial, sans-serif;">
                <span>Threshold — </span><span x-text="selected?.component?.name ?? '-'" class="font-semibold"></span>
            </h2>
            <button
                type="button"
                @click="resetDefaultSelected()"
                class="h-[30px] px-3 bg-transparent border border-[#1E2939] rounded-[10px] text-xs text-[#6A7282] inline-flex items-center gap-2"
                style="font-family: Arial, sans-serif;"
            >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M4 12a8 8 0 1014.906 3" />
                </svg>
                <span>Reset ke Default</span>
            </button>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs text-[#6A7282] mb-1 tracking-wider uppercase" style="font-family: Arial, sans-serif;">Warn</label>
                <div class="flex items-center gap-2">
                    <input type="number" min="0" max="100" class="w-full h-[38px] bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] px-3 text-sm text-white"
                           x-model.number="selected.warn_score" @input="markDirty()" />
                    <span class="text-xs text-[#4A5565]">score</span>
                </div>
            </div>
            <div>
                <label class="block text-xs text-[#6A7282] mb-1 tracking-wider uppercase" style="font-family: Arial, sans-serif;">Critical</label>
                <div class="flex items-center gap-2">
                    <input type="number" min="0" max="100" class="w-full h-[38px] bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] px-3 text-sm text-white"
                           x-model.number="selected.critical_score" @input="markDirty()" />
                    <span class="text-xs text-[#4A5565]">score</span>
                </div>
            </div>
            <div>
                <label class="block text-xs text-[#6A7282] mb-1 tracking-wider uppercase" style="font-family: Arial, sans-serif;">Reset Interval</label>
                <div class="flex items-center gap-2">
                    <input type="number" min="0" class="w-full h-[38px] bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] px-3 text-sm text-white"
                           x-model.number="resetIntervalDays" @input="applyResetInterval()" />
                    <span class="text-xs text-[#4A5565]">hari</span>
                </div>
            </div>
        </div>

        <div class="mt-4 text-xs text-[#4A5565] inline-flex items-center gap-2" style="font-family: Arial, sans-serif;">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Nilai warn dan critical digunakan sebagai batas status dari skor fuzzy (0-100).</span>
        </div>

        <!-- Active inputs -->
        <div class="mt-4 bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-4">
            <div class="text-sm font-medium text-white" style="font-family: Arial, sans-serif;">Variabel Input Aktif — <span x-text="selected?.component?.name ?? '-'"> </span></div>
            <div class="text-xs text-[#6A7282] mt-1" style="font-family: Arial, sans-serif;">Pilih variabel IoT yang digunakan untuk menghitung</div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <template x-for="v in inputVars" :key="v.key">
                    <div class="h-[42px] rounded-[10px] border flex items-center justify-between px-4"
                         :class="isInputEnabled(v.key) ? 'bg-[#111111] border-[#364153]' : 'bg-[#0A0A0A] border-[#1E2939]'">
                        <div class="text-sm" :class="isInputEnabled(v.key) ? 'text-[#E5E7EB]' : 'text-[#4A5565]'" style="font-family: Arial, sans-serif;" x-text="v.label"></div>
                        <button type="button" class="w-9 h-5 rounded-full relative" @click="toggleInput(v.key)"
                                :class="isInputEnabled(v.key) ? 'bg-[#6B7C4F]' : 'bg-[#364153]'">
                            <span class="w-4 h-4 bg-white rounded-full absolute top-0.5 transition-all"
                                  :class="isInputEnabled(v.key) ? 'right-0.5' : 'left-0.5'"></span>
                        </button>
                    </div>
                </template>
            </div>

            <div class="mt-4 text-xs text-[#364153]" style="font-family: Arial, sans-serif;">Contoh: Ban cukup pakai Jarak + Kecepatan. Oli pakai Jarak + Durasi + Intensitas.</div>
        </div>
    </div>

    <!-- Membership Function -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-5" x-show="selected">
        <div class="text-lg font-semibold text-white" style="font-family: Arial, sans-serif;">
            Membership Function — <span x-text="currentVar?.label ?? ''"></span>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <template x-for="v in inputVars" :key="v.key">
                <button type="button" @click="selectVar(v.key)"
                        class="h-[30px] px-3 rounded-[10px] border text-xs"
                        :class="currentVarKey === v.key ? 'bg-[#6B7C4F] border-[#6B7C4F] text-white' : 'bg-transparent border-[#1E2939] text-[#6A7282]'"
                        style="font-family: Arial, sans-serif;" x-text="v.short"></button>
            </template>
        </div>

        <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Params -->
            <div class="flex flex-col gap-4">
                <template x-for="term in ['low','medium','high']" :key="term">
                    <div>
                        <div class="text-xs text-[#6A7282] tracking-wider uppercase mb-2" style="font-family: Arial, sans-serif;" x-text="term"></div>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="(p, idx) in getUiSetParams(currentVarKey, term)" :key="idx">
                                <div>
                                    <input type="number" class="w-full h-[38px] bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] px-3 text-sm text-white text-center"
                                           :value="p" @input="setUiSetParam(currentVarKey, term, idx, $event.target.value)" />
                                    <div class="text-[10px] text-[#364153] text-center mt-1" style="font-family: Arial, sans-serif;" x-text="idx === 0 ? '[a]' : (idx === 1 ? '[b]' : '[c]')"></div>
                                </div>
                            </template>
                        </div>
                        <div class="text-[10px] text-[#364153] mt-2" style="font-family: Arial, sans-serif;" x-text="hintForUiSet(currentVarKey, term)"></div>
                    </div>
                </template>

                <div class="text-xs text-[#364153] inline-flex items-start gap-2" style="font-family: Arial, sans-serif;">
                    <svg class="w-3.5 h-3.5 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>[a] batas awal · [b] puncak · [c] batas akhir.</span>
                </div>
            </div>

            <!-- Chart -->
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-4 text-xs" style="font-family: Arial, sans-serif;">
                    <div class="inline-flex items-center gap-2 text-[#6A7282]"><span class="w-4 h-0.5 bg-[#6B7C4F] rounded"></span> Low</div>
                    <div class="inline-flex items-center gap-2 text-[#6A7282]"><span class="w-4 h-0.5 bg-[#9CA3AF] rounded"></span> Medium</div>
                    <div class="inline-flex items-center gap-2 text-[#6A7282]"><span class="w-4 h-0.5 bg-[#E5E7EB] rounded"></span> High</div>
                </div>

                <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-3">
                    <svg viewBox="0 0 330 174" class="w-full h-[174px]" aria-label="Membership chart">
                        <!-- grid -->
                        <path d="M20 20H320" stroke="#1F1F1F" stroke-dasharray="4 4" />
                        <path d="M20 87H320" stroke="#1F1F1F" stroke-dasharray="4 4" />
                        <path d="M20 154H320" stroke="#1F1F1F" stroke-dasharray="4 4" />
                        <path d="M20 20V154" stroke="#1F1F1F" stroke-dasharray="4 4" />

                        <path :d="chartPaths.low" stroke="#6B7C4F" stroke-width="2" fill="none" />
                        <path :d="chartPaths.medium" stroke="#9CA3AF" stroke-width="2" fill="none" />
                        <path :d="chartPaths.high" stroke="#E5E7EB" stroke-width="2" fill="none" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Rule Base -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-5" x-show="selected">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-white" style="font-family: Arial, sans-serif;">Rule Base — <span x-text="selected?.component?.name ?? '-'"> </span></h2>
            <button type="button" @click="addRule()" class="h-[30px] px-3 border border-[#1E2939] rounded-[10px] text-xs text-[#8FA06A] inline-flex items-center gap-2" style="font-family: Arial, sans-serif;">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 5v14M5 12h14" /></svg>
                <span>Tambah Rule</span>
            </button>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-[#4A5565] border-b border-[#1E2939]">
                    <tr>
                        <th class="text-left py-2 pr-4 text-xs uppercase tracking-wider" style="font-family: Arial, sans-serif;">No</th>
                        <th class="text-left py-2 pr-4 text-xs uppercase tracking-wider" style="font-family: Arial, sans-serif;">Kondisi IF</th>
                        <th class="text-left py-2 pr-4 text-xs uppercase tracking-wider" style="font-family: Arial, sans-serif;">Output THEN</th>
                        <th class="text-left py-2 pr-4 text-xs uppercase tracking-wider" style="font-family: Arial, sans-serif;">Bobot</th>
                        <th class="text-left py-2 text-xs uppercase tracking-wider" style="font-family: Arial, sans-serif;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1E2939]/50">
                    <template x-for="(r, idx) in (selected?.config?.rules ?? [])" :key="idx">
                        <tr>
                            <td class="py-3 pr-4 text-[#4A5565]" style="font-family: Arial, sans-serif;" x-text="idx + 1"></td>
                            <td class="py-3 pr-4 text-[#99A1AF]" style="font-family: Arial, sans-serif;" x-text="formatRule(r)"></td>
                            <td class="py-3 pr-4" style="font-family: Arial, sans-serif;">
                                <span class="inline-flex items-center px-2 py-1 text-xs rounded border"
                                      :class="r.then === 'good' ? 'border-[#364153] text-[#99A1AF]' : (r.then === 'fair' ? 'border-[#4A5565] text-[#D1D5DC]' : 'border-[#6A7282] text-white')"
                                      x-text="labelThen(r.then)"></span>
                            </td>
                            <td class="py-3 pr-4 text-[#6A7282]" style="font-family: Arial, sans-serif;" x-text="(r.weight ?? 1).toFixed ? (r.weight ?? 1).toFixed(1) : (r.weight ?? 1)"></td>
                            <td class="py-3" style="font-family: Arial, sans-serif;">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" class="h-7 px-2 rounded-[8px] border border-[#1E2939] text-xs text-[#6A7282]" @click="editRule(idx)">Edit</button>
                                    <button type="button" class="h-7 px-2 rounded-[8px] border border-[#1E2939] text-xs text-[#6A7282]" @click="removeRule(idx)">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="(selected?.config?.rules ?? []).length === 0">
                        <td colspan="5" class="py-4 text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Belum ada rule.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Simple rule editor -->
        <div x-show="ruleEditor.open" x-collapse class="mt-4 bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-4">
            <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-white" style="font-family: Arial, sans-serif;">Edit Rule</div>
                <button type="button" class="text-xs text-[#6A7282]" @click="ruleEditor.open = false" style="font-family: Arial, sans-serif;">Tutup</button>
            </div>

            <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs text-[#6A7282] mb-1" style="font-family: Arial, sans-serif;">Logic</label>
                    <select class="w-full h-[38px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 text-sm text-white" x-model="ruleEditor.logic">
                        <option value="all">AND</option>
                        <option value="any">OR</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-[#6A7282] mb-1" style="font-family: Arial, sans-serif;">Then</label>
                    <select class="w-full h-[38px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 text-sm text-white" x-model="ruleEditor.then">
                        <option value="good">Baik</option>
                        <option value="fair">Perlu Servis</option>
                        <option value="bad">Kritis</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-[#6A7282] mb-1" style="font-family: Arial, sans-serif;">Bobot</label>
                    <input type="number" step="0.1" min="0" max="1" class="w-full h-[38px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 text-sm text-white" x-model.number="ruleEditor.weight" />
                </div>

                <div class="flex items-end">
                    <button type="button" class="h-[38px] w-full bg-[#6B7C4F] rounded-[10px] text-sm text-white" style="font-family: Arial, sans-serif;" @click="applyRuleEditor()">Terapkan</button>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-2">
                <template x-for="(c, cIdx) in ruleEditor.conditions" :key="cIdx">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-2 items-center">
                        <select class="md:col-span-2 h-[38px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 text-sm text-white" x-model="c.var">
                            <template x-for="v in inputVars" :key="v.key">
                                <option :value="v.key" x-text="v.label"></option>
                            </template>
                        </select>

                        <select class="h-[38px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 text-sm text-white" x-model="c.is">
                            <option value="low">low</option>
                            <option value="medium">medium</option>
                            <option value="high">high</option>
                        </select>

                        <button type="button" class="h-[38px] border border-[#1E2939] rounded-[10px] text-sm text-[#6A7282]" @click="removeRuleCondition(cIdx)">Hapus Kondisi</button>
                        <button type="button" class="h-[38px] border border-[#1E2939] rounded-[10px] text-sm text-[#6A7282]" @click="addRuleCondition()">Tambah Kondisi</button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Test Konfigurasi -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-5" x-show="selected">
        <div class="text-lg font-semibold text-white" style="font-family: Arial, sans-serif;">Test Konfigurasi</div>

        <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="flex flex-col gap-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <template x-for="v in inputVars" :key="v.key">
                        <div :class="isInputEnabled(v.key) ? '' : 'opacity-30'">
                            <label class="block text-xs text-[#6A7282] mb-1 tracking-wider uppercase" style="font-family: Arial, sans-serif;" x-text="v.short"></label>
                            <div class="flex items-center gap-2">
                                <input type="number" class="w-full h-[38px] bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] px-3 text-sm text-white"
                                       :disabled="!isInputEnabled(v.key)" x-model.number="testInputs[v.key]" />
                                <span class="text-xs text-[#4A5565]" x-text="v.unit"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <button type="button" class="h-10 bg-[#6B7C4F] rounded-[10px] text-sm text-white inline-flex items-center justify-center gap-2"
                        style="font-family: Arial, sans-serif;" @click="runTest()" :disabled="testing">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M14 2v6h6" /></svg>
                    <span x-text="testing ? 'Menghitung…' : 'Hitung Sekarang'"></span>
                </button>
            </div>

            <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-4 flex flex-col justify-center items-center gap-2">
                <template x-if="!testResult">
                    <div class="text-xs text-[#364153]" style="font-family: Arial, sans-serif;">Klik "Hitung Sekarang" untuk melihat hasil</div>
                </template>

                <template x-if="testResult">
                    <div class="w-full">
                        <div class="text-sm text-white font-semibold" style="font-family: Arial, sans-serif;" x-text="'Hasil: ' + labelStatus(testResult.status)"></div>
                        <div class="mt-1 text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;" x-text="'Skor: ' + testResult.score + ' (warn=' + testResult.thresholds.warn + ', critical=' + testResult.thresholds.critical + ')'"> </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>

<script>
    function fuzzyLogicAdmin({ initialMotorType }) {
        return {
            routes: {
                data: @js(route('admin.fuzzy-config.data')),
                bulkUpdate: @js(route('admin.fuzzy-config.bulk-update')),
                test: @js(route('admin.fuzzy-config.test')),
                resetDefaultTemplate: @js(url('/admin/fuzzy-config/__ID__/reset-default')),
            },

            motorTypes: @js($motorTypes ?? ['matic', 'manual', 'sport', 'adventure']),
            motorType: initialMotorType,

            items: [],
            availableComponents: [],

            selectedId: null,
            showAddComponent: false,
            saving: false,
            testing: false,
            testResult: null,

            currentVarKey: 'distance_since_service_km',
            chartPaths: { low: '', medium: '', high: '' },

            resetIntervalDays: 0,

            inputVars: [
                { key: 'distance_since_service_km', short: 'Jarak Sejak Servis', label: 'Jarak Sejak Servis (km)', unit: 'km' },
                { key: 'duration_since_service_days', short: 'Durasi Sejak Servis', label: 'Durasi Sejak Servis (hari)', unit: 'hari' },
                { key: 'avg_speed_kph', short: 'Kecepatan Rata-rata', label: 'Kecepatan Rata-rata (km/jam)', unit: 'km/jam' },
                { key: 'intensity_km_per_day', short: 'Intensitas Pakai', label: 'Intensitas Pakai (km/hari)', unit: 'km/hari' },
            ],

            testInputs: {
                distance_since_service_km: 1200,
                duration_since_service_days: 50,
                avg_speed_kph: 45,
                intensity_km_per_day: 20,
            },

            ruleEditor: {
                open: false,
                index: null,
                logic: 'all',
                then: 'fair',
                weight: 1,
                conditions: [],
            },

            get selected() {
                return this.items.find(i => i.id === this.selectedId) || null;
            },

            get currentVar() {
                return this.inputVars.find(v => v.key === this.currentVarKey) || null;
            },

            csrf() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            },

            async init() {
                await this.loadData(this.motorType);
            },

            labelMotorType(type) {
                if (type === 'manual') return 'Manual/Bebek';
                return type.charAt(0).toUpperCase() + type.slice(1);
            },

            labelThen(thenKey) {
                if (thenKey === 'good') return 'Baik';
                if (thenKey === 'fair') return 'Perlu Servis';
                return 'Kritis';
            },

            labelStatus(statusKey) {
                if (statusKey === 'normal') return 'Baik';
                if (statusKey === 'warning') return 'Perlu Servis';
                return 'Kritis';
            },

            async loadData(motorType) {
                const url = new URL(this.routes.data, window.location.origin);
                url.searchParams.set('motor_type', motorType);

                const res = await fetch(url.toString(), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });

                if (!res.ok) {
                    alert('Gagal memuat data.');
                    return;
                }

                const json = await res.json();
                this.motorType = json.motor_type;
                this.items = (json.items || []).map(it => {
                    it.warn_score = Number(it.warn_score ?? 60);
                    it.critical_score = Number(it.critical_score ?? 40);
                    it.config = this.normalizeConfig(it.config || {});
                    return it;
                });
                this.availableComponents = json.available_components || [];

                if (!this.selectedId || !this.items.some(i => i.id === this.selectedId)) {
                    this.selectedId = this.items[0]?.id ?? null;
                }

                this.syncResetIntervalFromSelected();
                this.$nextTick(() => this.refreshChart());
            },

            selectMotorType(type) {
                this.selectedId = null;
                this.testResult = null;
                this.ruleEditor.open = false;
                this.loadData(type);
            },

            selectComponent(id) {
                this.selectedId = id;
                this.testResult = null;
                this.ruleEditor.open = false;
                this.syncResetIntervalFromSelected();
                this.$nextTick(() => this.refreshChart());
            },

            selectVar(key) {
                this.currentVarKey = key;
                this.$nextTick(() => this.refreshChart());
            },

            markDirty() {
                // placeholder to keep parity with UI; bulk save is explicit
            },

            normalizeConfig(cfg) {
                const out = typeof cfg === 'object' && cfg ? cfg : {};
                out.inputs = out.inputs && typeof out.inputs === 'object' ? out.inputs : {};
                out.output = out.output && typeof out.output === 'object' ? out.output : {};
                out.rules = Array.isArray(out.rules) ? out.rules : [];

                // ensure enabled + normalize input sets to tri params
                for (const key of Object.keys(out.inputs)) {
                    const def = out.inputs[key];
                    if (typeof def !== 'object' || !def) continue;
                    if (typeof def.enabled !== 'boolean') def.enabled = true;
                    def.sets = def.sets && typeof def.sets === 'object' ? def.sets : {};

                    for (const setName of Object.keys(def.sets)) {
                        const setDef = def.sets[setName];
                        if (!setDef || typeof setDef !== 'object') continue;
                        const type = setDef.type === 'trap' ? 'trap' : 'tri';
                        const rawParams = Array.isArray(setDef.params) ? setDef.params : [];
                        const params = rawParams.map(n => Number(n));

                        if (type === 'tri') {
                            def.sets[setName].type = 'tri';
                            def.sets[setName].params = (params.length === 3) ? params : [0, 0, 0];
                        } else {
                            def.sets[setName].type = 'trap';
                            def.sets[setName].params = (params.length === 4) ? params : [0, 0, 0, 0];
                        }
                    }
                }

                // rules: ensure weight
                out.rules = out.rules.map(r => {
                    if (!r || typeof r !== 'object') return r;
                    if (typeof r.weight !== 'number') r.weight = 1;
                    return r;
                });

                // meta
                out.meta = out.meta && typeof out.meta === 'object' ? out.meta : {};
                return out;
            },

            isInputEnabled(key) {
                const def = this.selected?.config?.inputs?.[key];
                if (!def) return false;
                return def.enabled !== false;
            },

            ensureInputDef(varKey) {
                if (!this.selected) return null;
                if (!this.selected.config.inputs) this.selected.config.inputs = {};
                if (!this.selected.config.inputs[varKey]) {
                    this.selected.config.inputs[varKey] = { enabled: true, universe: [0, 100], sets: {} };
                }
                if (!this.selected.config.inputs[varKey].sets) this.selected.config.inputs[varKey].sets = {};
                return this.selected.config.inputs[varKey];
            },

            toggleInput(key) {
                if (!this.selected) return;
                if (!this.selected.config.inputs[key]) this.selected.config.inputs[key] = { enabled: true, sets: {} };
                const current = this.selected.config.inputs[key].enabled !== false;
                this.selected.config.inputs[key].enabled = !current;
                this.refreshChart();
            },

            syncResetIntervalFromSelected() {
                const v = Number(this.selected?.config?.meta?.reset_interval_days ?? 0);
                this.resetIntervalDays = Number.isFinite(v) ? v : 0;
            },

            applyResetInterval() {
                if (!this.selected) return;
                if (!this.selected.config.meta) this.selected.config.meta = {};
                const v = Number(this.resetIntervalDays ?? 0);
                this.selected.config.meta.reset_interval_days = Number.isFinite(v) ? v : 0;
            },

            getSetType(varKey, term) {
                const setDef = this.selected?.config?.inputs?.[varKey]?.sets?.[term];
                return (setDef?.type === 'trap') ? 'trap' : 'tri';
            },

            getTrapMode(params) {
                if (!Array.isArray(params) || params.length !== 4) return null;
                if (Number(params[0]) === Number(params[1])) return 'left-shoulder';
                if (Number(params[2]) === Number(params[3])) return 'right-shoulder';
                return 'general';
            },

            getUiSetParams(varKey, term) {
                const setDef = this.selected?.config?.inputs?.[varKey]?.sets?.[term];
                const type = (setDef?.type === 'trap') ? 'trap' : 'tri';
                const params = Array.isArray(setDef?.params) ? setDef.params.map(Number) : [];

                if (type === 'tri') {
                    if (params.length === 3) return params;
                    return [0, 0, 0];
                }

                if (params.length !== 4) return [0, 0, 0];
                const mode = this.getTrapMode(params);
                const [a, b, c, d] = params;

                if (mode === 'left-shoulder') {
                    // trap [a,a,c,d] -> UI [a,c,d]
                    return [a, c, d];
                }
                if (mode === 'right-shoulder') {
                    // trap [a,b,d,d] -> UI [a,b,d]
                    return [a, b, d];
                }
                // general trap: best-effort show [a, mid, d]
                return [a, (b + c) / 2, d];
            },

            hintForUiSet(varKey, term) {
                const type = this.getSetType(varKey, term);
                if (type === 'tri') {
                    return '[a] batas awal · [b] puncak · [c] batas akhir.';
                }

                const setDef = this.selected?.config?.inputs?.[varKey]?.sets?.[term];
                const params = Array.isArray(setDef?.params) ? setDef.params.map(Number) : [];
                const mode = this.getTrapMode(params);
                if (mode === 'left-shoulder') return 'Trap (left shoulder): [a] start · [b] akhir puncak · [c] end.';
                if (mode === 'right-shoulder') return 'Trap (right shoulder): [a] start · [b] mulai puncak · [c] end.';
                return 'Trap: editor ini best-effort (bukan 4 titik penuh).';
            },

            setUiSetParam(varKey, term, idx, value) {
                if (!this.selected) return;
                const v = Number(value);
                const num = Number.isFinite(v) ? v : 0;
                const def = this.ensureInputDef(varKey);
                if (!def) return;

                const existing = def.sets[term];
                const type = (existing?.type === 'trap') ? 'trap' : 'tri';

                if (type === 'tri') {
                    if (!def.sets[term] || typeof def.sets[term] !== 'object') def.sets[term] = { type: 'tri', params: [0, 0, 0] };
                    def.sets[term].type = 'tri';
                    if (!Array.isArray(def.sets[term].params) || def.sets[term].params.length !== 3) def.sets[term].params = [0, 0, 0];
                    def.sets[term].params[idx] = num;
                    this.refreshChart();
                    return;
                }

                // trap: preserve shoulder form when possible
                if (!def.sets[term] || typeof def.sets[term] !== 'object') def.sets[term] = { type: 'trap', params: [0, 0, 0, 0] };
                def.sets[term].type = 'trap';
                if (!Array.isArray(def.sets[term].params) || def.sets[term].params.length !== 4) def.sets[term].params = [0, 0, 0, 0];

                const p = def.sets[term].params.map(Number);
                const mode = this.getTrapMode(p);
                const ui = this.getUiSetParams(varKey, term);
                ui[idx] = num;

                if (mode === 'left-shoulder') {
                    // UI [a,c,d] -> trap [a,a,c,d]
                    def.sets[term].params = [ui[0], ui[0], ui[1], ui[2]];
                } else if (mode === 'right-shoulder') {
                    // UI [a,b,d] -> trap [a,b,d,d]
                    def.sets[term].params = [ui[0], ui[1], ui[2], ui[2]];
                } else {
                    // best-effort: keep symmetry around ui[1]
                    def.sets[term].params = [ui[0], ui[1], ui[1], ui[2]];
                }
                this.refreshChart();
            },

            refreshChart() {
                if (!this.selected) return;
                const inputDef = this.selected.config.inputs?.[this.currentVarKey];
                const uni = inputDef?.universe || [0, 100];
                const minX = Number(uni?.[0] ?? 0);
                const maxX = Number(uni?.[1] ?? 100);

                const toX = (x) => {
                    const w = 300;
                    if (maxX === minX) return 20;
                    return 20 + ((x - minX) / (maxX - minX)) * w;
                };
                const toY = (mu) => {
                    const h = 134;
                    return 20 + (1 - mu) * h;
                };

                const sample = (setDef) => {
                    const pts = [];
                    const steps = 40;
                    for (let i = 0; i <= steps; i++) {
                        const x = minX + (i / steps) * (maxX - minX);
                        const mu = this.membership(x, setDef);
                        pts.push([toX(x), toY(mu)]);
                    }
                    return pts;
                };

                const pathFromPts = (pts) => {
                    if (!pts.length) return '';
                    return 'M ' + pts.map(p => `${p[0].toFixed(2)} ${p[1].toFixed(2)}`).join(' L ');
                };

                const sets = inputDef?.sets || {};
                this.chartPaths.low = pathFromPts(sample(sets.low));
                this.chartPaths.medium = pathFromPts(sample(sets.medium));
                this.chartPaths.high = pathFromPts(sample(sets.high));
            },

            membership(x, setDef) {
                if (!setDef) return 0;
                const type = setDef.type;
                const p = Array.isArray(setDef.params) ? setDef.params.map(Number) : [];
                if (type === 'tri' && p.length === 3) return this.tri(x, p[0], p[1], p[2]);
                if (type === 'trap' && p.length === 4) return this.trap(x, p[0], p[1], p[2], p[3]);
                return 0;
            },

            tri(x, a, b, c) {
                if (a === b && b === c) return 0;
                if (x === b) return 1;
                if (a === b) {
                    if (x <= b) return 1;
                    if (x >= c) return 0;
                    return (c === b) ? 0 : ((c - x) / (c - b));
                }
                if (b === c) {
                    if (x >= b) return 1;
                    if (x <= a) return 0;
                    return (b === a) ? 0 : ((x - a) / (b - a));
                }
                if (x <= a || x >= c) return 0;
                if (x < b) return (b === a) ? 0 : ((x - a) / (b - a));
                return (c === b) ? 0 : ((c - x) / (c - b));
            },

            trap(x, a, b, c, d) {
                if (x <= a || x >= d) return 0;
                if (x >= b && x <= c) return 1;
                if (x > a && x < b) return (b === a) ? 0 : ((x - a) / (b - a));
                return (d === c) ? 0 : ((d - x) / (d - c));
            },

            formatRule(rule) {
                if (!rule) return '-';
                const logic = Array.isArray(rule.all) ? 'all' : (Array.isArray(rule.any) ? 'any' : null);
                const conds = logic === 'all' ? rule.all : rule.any;
                if (!logic || !Array.isArray(conds)) return '-';

                const op = logic === 'all' ? ' AND ' : ' OR ';
                return conds.map(c => `${this.labelVar(c.var)}=${c.is}`).join(op);
            },

            labelVar(key) {
                return this.inputVars.find(v => v.key === key)?.short || key;
            },

            addRule() {
                if (!this.selected) return;
                if (!Array.isArray(this.selected.config.rules)) this.selected.config.rules = [];

                const enabled = this.inputVars.filter(v => this.isInputEnabled(v.key));
                const a = enabled[0]?.key || this.inputVars[0].key;
                const b = enabled[1]?.key || this.inputVars[1].key;

                this.selected.config.rules.push({
                    all: [
                        { var: a, is: 'medium' },
                        { var: b, is: 'medium' },
                    ],
                    then: 'fair',
                    weight: 1,
                });
            },

            removeRule(idx) {
                if (!this.selected) return;
                this.selected.config.rules.splice(idx, 1);
                if (this.ruleEditor.open && this.ruleEditor.index === idx) {
                    this.ruleEditor.open = false;
                }
            },

            editRule(idx) {
                const r = this.selected?.config?.rules?.[idx];
                if (!r) return;
                const logic = Array.isArray(r.all) ? 'all' : 'any';
                const conds = logic === 'all' ? (r.all || []) : (r.any || []);

                this.ruleEditor.open = true;
                this.ruleEditor.index = idx;
                this.ruleEditor.logic = logic;
                this.ruleEditor.then = r.then || 'fair';
                this.ruleEditor.weight = Number(r.weight ?? 1);
                this.ruleEditor.conditions = conds.map(c => ({ var: c.var, is: c.is }));
                if (this.ruleEditor.conditions.length === 0) {
                    this.ruleEditor.conditions = [{ var: this.inputVars[0].key, is: 'medium' }];
                }
            },

            addRuleCondition() {
                this.ruleEditor.conditions.push({ var: this.inputVars[0].key, is: 'medium' });
            },

            removeRuleCondition(idx) {
                this.ruleEditor.conditions.splice(idx, 1);
                if (this.ruleEditor.conditions.length === 0) {
                    this.ruleEditor.conditions = [{ var: this.inputVars[0].key, is: 'medium' }];
                }
            },

            applyRuleEditor() {
                if (!this.selected) return;
                const idx = this.ruleEditor.index;
                if (idx === null || idx === undefined) return;

                const rule = {
                    then: this.ruleEditor.then,
                    weight: Math.max(0, Math.min(1, Number(this.ruleEditor.weight ?? 1))),
                };
                const conds = this.ruleEditor.conditions
                    .filter(c => c && c.var && c.is)
                    .map(c => ({ var: c.var, is: c.is }));

                if (this.ruleEditor.logic === 'any') rule.any = conds;
                else rule.all = conds;

                this.selected.config.rules[idx] = rule;
                this.ruleEditor.open = false;
            },

            async resetDefaultSelected() {
                if (!this.selected) return;
                if (!confirm('Reset config ke default?')) return;
                const url = this.routes.resetDefaultTemplate.replace('__ID__', String(this.selected.id));
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf(),
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    alert('Gagal reset default.');
                    return;
                }
                await this.loadData(this.motorType);
            },

            async saveAll() {
                if (this.saving) return;
                // quick client validation
                for (const it of this.items) {
                    const w = Number(it.warn_score);
                    const c = Number(it.critical_score);
                    if (!Number.isFinite(w) || !Number.isFinite(c) || c > w) {
                        alert('Validasi gagal: pastikan critical <= warn untuk semua komponen.');
                        return;
                    }
                }

                this.saving = true;
                try {
                    const payload = {
                        motor_type: this.motorType,
                        items: this.items.map(it => ({
                            motor_type_component_id: it.id,
                            warn_score: Number(it.warn_score),
                            critical_score: Number(it.critical_score),
                            config: it.config,
                        })),
                    };

                    const res = await fetch(this.routes.bulkUpdate, {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf(),
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload),
                    });

                    if (!res.ok) {
                        const err = await res.json().catch(() => null);
                        alert(err?.message || 'Gagal menyimpan perubahan.');
                        return;
                    }

                    await this.loadData(this.motorType);
                } finally {
                    this.saving = false;
                }
            },

            async runTest() {
                if (!this.selected || this.testing) return;
                this.testing = true;
                this.testResult = null;

                try {
                    const res = await fetch(this.routes.test, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf(),
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            motor_type_component_id: this.selected.id,
                            inputs: this.testInputs,
                            warn_score: Number(this.selected.warn_score),
                            critical_score: Number(this.selected.critical_score),
                            config: this.selected.config,
                        }),
                    });

                    if (!res.ok) {
                        alert('Gagal menghitung.');
                        return;
                    }
                    this.testResult = await res.json();
                } finally {
                    this.testing = false;
                }
            },

        };
    }
</script>
@endsection
