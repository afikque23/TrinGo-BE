/**
 * fuzzy/admin.js
 * Alpine.js component untuk halaman Konfigurasi Fuzzy Logic
 * Taruh di: public/js/fuzzy/admin.js
 */

const ROUTES = {
  components:  (motorTypeId) => `/admin/fuzzy/components?motor_type_id=${motorTypeId}`,
  threshold:   (id) => `/admin/fuzzy/components/${id}/threshold`,
  membership:  (id) => `/admin/fuzzy/components/${id}/membership`,
  rulesStore:  (id) => `/admin/fuzzy/components/${id}/rules`,
  rulesUpdate: (id) => `/admin/fuzzy/rules/${id}`,
  rulesDelete: (id) => `/admin/fuzzy/rules/${id}`,
  compStore:   ()   => `/admin/fuzzy/components`,
  compToggle:  (id) => `/admin/fuzzy/components/${id}/toggle`,
  compDelete:  (id) => `/admin/fuzzy/components/${id}`,
  test:        (id) => `/admin/fuzzy/components/${id}/test`,
  chart:       ()   => `/admin/fuzzy/chart-data`,
};

const VAR_LABELS = {
  jarak:      'Jarak Sejak Servis (km)',
  durasi:     'Durasi Sejak Servis (hari)',
  kecepatan:  'Kecepatan Rata-rata (km/h)',
  intensitas: 'Intensitas Pakai (km/hari)',
};

const ALL_VARS = [
  { key: 'jarak',      label: VAR_LABELS.jarak,      unit: 'km'      },
  { key: 'durasi',     label: VAR_LABELS.durasi,     unit: 'hari'    },
  { key: 'kecepatan',  label: VAR_LABELS.kecepatan,  unit: 'km/h'    },
  { key: 'intensitas', label: VAR_LABELS.intensitas, unit: 'km/hari' },
];

function fuzzyAdmin() {
  return {
    // ── State ──────────────────────────────────────────────────────────────
    activeTab:        null,
    components:       {},     // { motorTypeId: [...] }
    selectedComp:     null,
    openMenu:         null,
    hasChanges:       false,
    toast:            null,
    toastTimer:       null,

    // Form threshold + vars
    form: {
      warn: 1500, critical: 2500, resetInterval: 90,
      activeVars: ['jarak'],
    },

    // Membership function forms
    mfVar:  'jarak',
    mfForm: {},   // { jarak: { low:[0,0,800], medium:[...], high:[...] }, ... }

    // Rules
    showRuleModal: false,
    editRule:      null,
    ruleForm: {
      var1: 'jarak', label1: 'high', operator: 'AND',
      var2: 'durasi', label2: 'high', output: 'Kritis', weight: 1.0,
    },

    // Add component
    showAddComp: false,
    addCompForm: {
      name: '', motorTypes: ['matic','manual','sport','adventure'],
      warn: 1500, critical: 2500, resetInterval: 90,
      activeVars: ['jarak'], notes: '',
    },
    addCompError: '',

    // Delete confirm
    deleteTarget: null,

    // Test
    testInputs: { jarak: 1200, durasi: 50, kecepatan: 45, intensitas: 20 },
    testResult:  null,

    // Chart
    chartInstance: null,

    // Constants (untuk template)
    varLabels: VAR_LABELS,
    allVars:   ALL_VARS,

    // ── Computed ───────────────────────────────────────────────────────────
    get currentComponents() {
      return this.components[this.activeTab] ?? [];
    },
    get activeVarKeys() {
      return this.form.activeVars;
    },
    get mfVarLabel() {
      return VAR_LABELS[this.mfVar]?.split(' (')[0] ?? '';
    },

    // ── Init ───────────────────────────────────────────────────────────────
    async init() {
      // activeTab diisi dari ID tab pertama yang di-render oleh Blade
      const firstTab = document.querySelector('[\\@click^="switchTab"]');
      if (firstTab) {
        const match = firstTab.getAttribute('@click')?.match(/switchTab\((\d+)\)/);
        if (match) this.activeTab = parseInt(match[1]);
      }
      if (this.activeTab) await this.loadComponents(this.activeTab);
    },

    // ── Load components per tab ────────────────────────────────────────────
    async loadComponents(motorTypeId) {
      const res  = await this.api('GET', ROUTES.components(motorTypeId));
      this.components = { ...this.components, [motorTypeId]: res };
      if (!this.selectedComp && res.length) this.selectComp(res[0]);
    },

    async switchTab(motorTypeId) {
      this.activeTab = motorTypeId;
      if (!this.components[motorTypeId]) {
        await this.loadComponents(motorTypeId);
      } else {
        const first = this.components[motorTypeId].find(c => c.is_active);
        if (first) this.selectComp(first);
      }
    },

    // ── Select component ───────────────────────────────────────────────────
    selectComp(comp) {
      this.selectedComp = comp;
      this.form.warn           = comp.warn;
      this.form.critical       = comp.critical;
      this.form.resetInterval  = comp.reset_interval;
      this.form.activeVars     = [...(comp.active_vars ?? ['jarak'])];
      this.mfForm              = JSON.parse(JSON.stringify(comp.mf ?? {}));
      this.mfVar               = this.form.activeVars[0] ?? 'jarak';
      this.testResult          = null;

      this.$nextTick(() => this.renderChart());
    },

    // ── Toggle variabel aktif ──────────────────────────────────────────────
    toggleVar(varKey) {
      const idx = this.form.activeVars.indexOf(varKey);
      if (idx === -1) {
        this.form.activeVars.push(varKey);
      } else {
        if (this.form.activeVars.length <= 1) return;
        this.form.activeVars.splice(idx, 1);
        if (this.mfVar === varKey) this.mfVar = this.form.activeVars[0];
      }
      this.hasChanges = true;
    },

    switchMfVar(v) {
      this.mfVar = v;
      this.$nextTick(() => this.renderChart());
    },

    // ── Update membership value ────────────────────────────────────────────
    updateMF(label, idx, value) {
      if (!this.mfForm[this.mfVar]) return;
      this.mfForm[this.mfVar][label][idx] = parseFloat(value) || 0;
      this.hasChanges = true;
      this.renderChart();
    },

    // ── Reset threshold ────────────────────────────────────────────────────
    resetThreshold() {
      this.form.warn = 1500; this.form.critical = 2500; this.form.resetInterval = 90;
      this.hasChanges = true;
    },

    // ── Save all ───────────────────────────────────────────────────────────
    async saveAll() {
      if (!this.selectedComp) return;

      // 1. Save threshold + active vars
      await this.api('PATCH', ROUTES.threshold(this.selectedComp.id), {
        warn:           this.form.warn,
        critical:       this.form.critical,
        reset_interval: this.form.resetInterval,
        active_vars:    this.form.activeVars,
      });

      // 2. Save setiap MF yang aktif
      for (const varKey of this.form.activeVars) {
        const mf = this.mfForm[varKey];
        if (!mf) continue;
        await this.api('PATCH', ROUTES.membership(this.selectedComp.id), {
          var_key: varKey,
          low_a: mf.low[0],    low_b: mf.low[1],    low_c: mf.low[2],
          med_a: mf.medium[0], med_b: mf.medium[1], med_c: mf.medium[2],
          high_a: mf.high[0],  high_b: mf.high[1],  high_c: mf.high[2],
        });
      }

      this.hasChanges = false;
      this.showToast('Konfigurasi berhasil disimpan');
    },

    // ── Rules ──────────────────────────────────────────────────────────────
    openRuleModal(rule) {
      this.editRule = rule;
      this.ruleForm = rule
        ? { ...rule }
        : { var1: 'jarak', label1: 'high', operator: 'AND', var2: 'durasi', label2: 'high', output: 'Kritis', weight: 1.0 };
      this.showRuleModal = true;
    },

    async saveRule() {
      if (this.editRule) {
        const res = await this.api('PATCH', ROUTES.rulesUpdate(this.editRule.id), this.ruleForm);
        const rules = this.selectedComp.rules.map(r => r.id === this.editRule.id ? res.rule : r);
        this.updateSelectedRules(rules);
      } else {
        const res = await this.api('POST', ROUTES.rulesStore(this.selectedComp.id), this.ruleForm);
        this.updateSelectedRules([...this.selectedComp.rules, res.rule]);
      }
      this.showRuleModal = false;
      this.showToast(this.editRule ? 'Rule diperbarui' : 'Rule ditambahkan');
    },

    async deleteRule(rule) {
      await this.api('DELETE', ROUTES.rulesDelete(rule.id));
      this.updateSelectedRules(this.selectedComp.rules.filter(r => r.id !== rule.id));
      this.showToast('Rule dihapus');
    },

    updateSelectedRules(rules) {
      this.selectedComp = { ...this.selectedComp, rules };
      const comps = this.components[this.activeTab];
      const idx = comps.findIndex(c => c.id === this.selectedComp.id);
      if (idx !== -1) comps[idx] = this.selectedComp;
    },

    // ── Add Component ──────────────────────────────────────────────────────
    openAddComp() {
      this.addCompForm = {
        name: '', motorTypes: ['matic','manual','sport','adventure'],
        warn: 1500, critical: 2500, resetInterval: 90,
        activeVars: ['jarak'], notes: '',
      };
      this.addCompError = '';
      this.showAddComp = true;
    },

    toggleAddVar(v) {
      const idx = this.addCompForm.activeVars.indexOf(v);
      if (idx === -1) {
        this.addCompForm.activeVars.push(v);
      } else {
        if (this.addCompForm.activeVars.length <= 1) return;
        this.addCompForm.activeVars.splice(idx, 1);
      }
    },

    toggleAddMotorType(tab) {
      const idx = this.addCompForm.motorTypes.indexOf(tab);
      if (idx === -1) {
        this.addCompForm.motorTypes.push(tab);
      } else {
        if (this.addCompForm.motorTypes.length <= 1) return;
        this.addCompForm.motorTypes.splice(idx, 1);
      }
    },

    async saveComp() {
      if (!this.addCompForm.name.trim()) {
        this.addCompError = 'Nama komponen tidak boleh kosong.';
        return;
      }

      // Dapat motor_type_ids dari nama tab
      const motorTypeIds = this.addCompForm.motorTypes.map(slug => {
        // mapping slug ke id ada di window.motorTypeMap yang di-inject Blade
        return window.motorTypeMap?.[slug];
      }).filter(Boolean);

      const res = await this.api('POST', ROUTES.compStore(), {
        name:            this.addCompForm.name.trim(),
        motor_type_ids:  motorTypeIds,
        warn:            this.addCompForm.warn,
        critical:        this.addCompForm.critical,
        reset_interval:  this.addCompForm.resetInterval,
        active_vars:     this.addCompForm.activeVars,
        notes:           this.addCompForm.notes,
      });

      // Reload komponen untuk semua tab yang terpengaruh
      for (const motorTypeId of motorTypeIds) {
        await this.loadComponents(motorTypeId);
      }

      this.showAddComp = false;
      this.showToast(res.message);
    },

    // ── Toggle / Delete Component ──────────────────────────────────────────
    async toggleComp(comp) {
      this.openMenu = null;
      const res = await this.api('PATCH', ROUTES.compToggle(comp.id));
      await this.loadComponents(this.activeTab);
      this.showToast(res.message);
    },

    confirmDelete(comp) {
      this.openMenu = null;
      this.deleteTarget = comp;
    },

    async handleDelete() {
      const res = await this.api('DELETE', ROUTES.compDelete(this.deleteTarget.id));
      await this.loadComponents(this.activeTab);
      if (this.selectedComp?.id === this.deleteTarget.id) {
        const first = this.components[this.activeTab]?.[0];
        if (first) this.selectComp(first); else this.selectedComp = null;
      }
      this.deleteTarget = null;
      this.showToast(res.message);
    },

    // ── Test ───────────────────────────────────────────────────────────────
    async runTest() {
      const res = await this.api('POST', ROUTES.test(this.selectedComp.id), this.testInputs);
      this.testResult = res;
    },

    // ── Chart ──────────────────────────────────────────────────────────────
    async renderChart() {
      const mf = this.mfForm[this.mfVar];
      if (!mf) return;

      const res = await this.api('POST', ROUTES.chart(), {
        low: mf.low, medium: mf.medium, high: mf.high,
        max_x: Math.max(mf.high[2] ?? 3000, 3000),
      });

      const canvas = document.getElementById('mf-chart');
      if (!canvas) return;

      if (this.chartInstance) this.chartInstance.destroy();

      this.chartInstance = new Chart(canvas, {
        type: 'line',
        data: {
          labels: res.map(d => d.x),
          datasets: [
            { label: 'Low',    data: res.map(d => d.low),    borderColor: '#6b7c4f', tension: 0.3, pointRadius: 0, borderWidth: 2 },
            { label: 'Medium', data: res.map(d => d.medium), borderColor: '#9ca3af', tension: 0.3, pointRadius: 0, borderWidth: 2 },
            { label: 'High',   data: res.map(d => d.high),   borderColor: '#e5e7eb', tension: 0.3, pointRadius: 0, borderWidth: 2 },
          ],
        },
        options: {
          responsive: true,
          animation: false,
          plugins: { legend: { display: false } },
          scales: {
            x: { ticks: { color: '#4b5563', font: { size: 10 } }, grid: { color: '#1f1f1f' } },
            y: { min: 0, max: 1, ticks: { color: '#4b5563', font: { size: 10 }, stepSize: 0.5 }, grid: { color: '#1f1f1f' } },
          },
        },
      });
    },

    // ── API Helper ─────────────────────────────────────────────────────────
    async api(method, url, body = null) {
      const opts = {
        method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'Accept':        'application/json',
        },
      };
      if (body && method !== 'GET') opts.body = JSON.stringify(body);
      const res  = await fetch(url, opts);
      const data = await res.json();
      if (!res.ok) {
        this.showToast(data.message ?? 'Terjadi kesalahan');
        throw new Error(data.message);
      }
      return data;
    },

    // ── Toast ──────────────────────────────────────────────────────────────
    showToast(msg) {
      clearTimeout(this.toastTimer);
      this.toast = msg;
      this.toastTimer = setTimeout(() => this.toast = null, 3000);
    },
  };
}
