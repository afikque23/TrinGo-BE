@extends('layouts.sidebar')
@section('title', 'Fuzzy Simulator')
@section('page-title', 'Fuzzy Simulator')
@section('content')
<div class="p-6 space-y-6 font-['Inter',sans-serif]">
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="space-y-5">
      <div class="bg-[#111111] border border-[#1e2939] rounded-xl p-5">
        <label class="sim-label">Mode Pengujian</label>
        <div class="grid grid-cols-2 gap-2 mt-2">
          <button id="btn-mode-manual" onclick="setMode('manual')" class="mode-btn active py-2.5 rounded-lg text-sm font-medium transition-all border border-[#6b7c4f]/50 bg-[#6b7c4f]/10 text-[#8a919e]">✏️ Input Manual</button>
          <button id="btn-mode-vehicle" onclick="setMode('vehicle')" class="mode-btn py-2.5 rounded-lg text-sm font-medium transition-all border border-[#1e2939] text-[#3d4a56]">🚗 Dari Kendaraan</button>
        </div>
        <p class="text-[10px] text-[#2d3748] mt-2 leading-relaxed"><strong class="text-[#3d4a56]">Manual:</strong> Input 4 variabel bebas.<br><strong class="text-[#3d4a56]">Kendaraan:</strong> Ambil data nyata dari DB (trip 30 hari).</p>
      </div>
      <div id="panel-manual" class="bg-[#111111] border border-[#1e2939] rounded-xl p-5 space-y-4">
        <label class="sim-label">Input Variabel (Manual)</label>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="sim-label" style="font-size:9px;">Jarak Tempuh (km)</label><div class="sim-input-wrap"><input type="number" id="inp-jarak" class="sim-input" value="2500" min="0" step="50"><span class="sim-unit">km</span></div></div>
          <div><label class="sim-label" style="font-size:9px;">Durasi Sejak Servis (hari)</label><div class="sim-input-wrap"><input type="number" id="inp-durasi" class="sim-input" value="60" min="0"><span class="sim-unit">hari</span></div></div>
          <div><label class="sim-label" style="font-size:9px;">Kecepatan Rata-rata (km/jam)</label><div class="sim-input-wrap"><input type="number" id="inp-kecepatan" class="sim-input" value="45" min="0" step="5"><span class="sim-unit">km/j</span></div></div>
          <div><label class="sim-label" style="font-size:9px;">Intensitas (km/hari)</label><div class="sim-input-wrap"><input type="number" id="inp-intensitas" class="sim-input" value="15" min="0" step="1"><span class="sim-unit">km/h</span></div></div>
        </div>
        <div>
          <label class="sim-label" style="font-size:9px;">Preset Skenario Cepat</label>
          <div class="flex flex-wrap gap-2 mt-1">
            <button onclick="setPreset(500,20,30,5)" class="preset-btn">🟢 Normal Ringan</button>
            <button onclick="setPreset(2500,60,45,15)" class="preset-btn">🟡 Warning Sedang</button>
            <button onclick="setPreset(4500,120,90,30)" class="preset-btn">🔴 Critical Berat</button>
            <button onclick="setPreset(3500,90,60,25)" class="preset-btn">📊 Campuran</button>
          </div>
        </div>
      </div>
      <div id="panel-vehicle" class="hidden bg-[#111111] border border-[#1e2939] rounded-xl p-5 space-y-4">
        <label class="sim-label">Pilih Kendaraan</label>
        <select id="sel-vehicle" class="sim-select">
          <option value="">-- Pilih Kendaraan --</option>
          @foreach($vehicles as $v)
          <option value="{{ $v->id }}">[ID:{{ $v->id }}] {{ $v->title ?? ($v->make . ' ' . $v->model) }} — {{ $v->owner ? $v->owner->name : 'Unknown' }}</option>
          @endforeach
        </select>
        <p class="text-[10px] text-[#2d3748] leading-relaxed">Sistem akan membaca jarak dari odometer, durasi dari riwayat servis, kecepatan & intensitas dari trip 30 hari terakhir.</p>
      </div>
      <div class="bg-[#111111] border border-[#1e2939] rounded-xl p-5">
        <label class="sim-label">Jenis Motor</label>
        <select id="sel-motor-type" class="sim-select mt-2">
          @foreach($motorTypes as $mt)<option value="{{ $mt->slug }}">{{ $mt->name }}</option>@endforeach
        </select>
      </div>
      <button onclick="runSimulator()" id="btn-run" class="w-full py-3 rounded-xl font-semibold text-sm bg-[#6b7c4f] hover:bg-[#5a6940] text-white transition-all flex items-center justify-center gap-2 shadow-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Hitung Fuzzy Sekarang
      </button>
      <div class="bg-[#111111] border border-[#1e2939] rounded-xl p-5 space-y-4">
        <div class="flex items-center gap-2">
          <span class="text-base">💉</span>
          <div><label class="sim-label" style="margin:0;">Inject Trip Dummy ke Database</label><p class="text-[10px] text-[#2d3748] leading-relaxed mt-0.5">Data ini akan masuk ke riwayat trip & BERPENGARUH pada rekomendasi di aplikasi mobile.</p></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="sim-label" style="font-size:9px;">Kendaraan Target</label><select id="inj-vehicle" class="sim-select"><option value="">-- Pilih --</option>@foreach($vehicles as $v)<option value="{{ $v->id }}">[{{ $v->id }}] {{ $v->title ?? ($v->make . ' ' . $v->model) }}</option>@endforeach</select></div>
          <div><label class="sim-label" style="font-size:9px;">Berapa Hari Lalu</label><div class="sim-input-wrap"><input type="number" id="inj-days-ago" class="sim-input" value="1" min="0" max="30"><span class="sim-unit">hari</span></div></div>
          <div><label class="sim-label" style="font-size:9px;">Jarak Perjalanan</label><div class="sim-input-wrap"><input type="number" id="inj-distance" class="sim-input" value="25" min="0.1" step="0.5"><span class="sim-unit">km</span></div></div>
          <div><label class="sim-label" style="font-size:9px;">Kecepatan Rata-rata</label><div class="sim-input-wrap"><input type="number" id="inj-speed" class="sim-input" value="45" min="1"><span class="sim-unit">km/j</span></div></div>
          <div class="col-span-2"><label class="sim-label" style="font-size:9px;">Durasi Perjalanan</label><div class="sim-input-wrap"><input type="number" id="inj-duration" class="sim-input" value="30" min="1"><span class="sim-unit">menit</span></div></div>
        </div>
        <button onclick="injectTrip()" class="w-full py-2.5 rounded-lg text-sm font-semibold border border-amber-600/40 text-amber-500 hover:bg-amber-600/10 transition-colors flex items-center justify-center gap-2">💉 Inject Trip ke Database</button>
        <div id="inject-log" class="hidden space-y-1 max-h-32 overflow-y-auto"></div>
      </div>
    </div>
    <div class="space-y-5">
      <div id="result-inputs" class="hidden bg-[#111111] border border-[#1e2939] rounded-xl p-5">
        <label class="sim-label mb-3 block">📥 Input yang Digunakan Sistem</label>
        <div class="grid grid-cols-2 gap-2" id="result-inputs-grid"></div>
      </div>
      <div id="result-area" class="hidden space-y-4">
        <label class="sim-label block">📊 Hasil Kalkulasi Fuzzy per Komponen</label>
        <div id="result-components"></div>
      </div>
      <div id="empty-state" class="bg-[#111111] border border-[#1e2939] rounded-xl p-10 flex flex-col items-center justify-center gap-3 text-center">
        <div class="text-4xl">🧮</div>
        <p class="text-sm text-[#3d4a56]">Hasil kalkulasi Fuzzy akan muncul di sini</p>
        <p class="text-[10px] text-[#2d3748]">Isi input di sebelah kiri lalu klik <strong>"Hitung Fuzzy Sekarang"</strong></p>
      </div>
      <div id="loading-state" class="hidden bg-[#111111] border border-[#1e2939] rounded-xl p-10 flex flex-col items-center gap-3">
        <div class="w-8 h-8 border-2 border-[#6b7c4f] border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm text-[#3d4a56]">Menghitung...</p>
      </div>
    </div>
  </div>
</div>
<style>
  .sim-label{display:block;font-size:10px;font-weight:600;color:#3d4a56;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;}
  .sim-input-wrap{display:flex;align-items:center;gap:6px;}
  .sim-input{flex:1;background:#0d0d0d;border:1px solid #1e2939;border-radius:8px;padding:8px 10px;font-size:13px;color:#8a919e;outline:none;width:100%;transition:border-color .2s;}
  .sim-input:focus{border-color:#6b7c4f;}
  .sim-unit{font-size:11px;color:#3d4a56;white-space:nowrap;min-width:28px;}
  .sim-select{width:100%;background:#0d0d0d;border:1px solid #1e2939;border-radius:8px;padding:8px 10px;font-size:13px;color:#8a919e;outline:none;}
  .preset-btn{padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500;background:#0d0d0d;border:1px solid #1e2939;color:#3d4a56;cursor:pointer;transition:all .15s;}
  .preset-btn:hover{border-color:#6b7c4f;color:#8a919e;}
  .mode-btn.active{border-color:#6b7c4f!important;background:rgba(107,124,79,.12)!important;color:#8a919e!important;}
</style>
<script>
let currentMode='manual';
function setMode(mode){currentMode=mode;document.getElementById('panel-manual').classList.toggle('hidden',mode!=='manual');document.getElementById('panel-vehicle').classList.toggle('hidden',mode!=='vehicle');document.getElementById('btn-mode-manual').classList.toggle('active',mode==='manual');document.getElementById('btn-mode-vehicle').classList.toggle('active',mode==='vehicle');}
function setPreset(j,d,k,i){document.getElementById('inp-jarak').value=j;document.getElementById('inp-durasi').value=d;document.getElementById('inp-kecepatan').value=k;document.getElementById('inp-intensitas').value=i;}
async function runSimulator(){
  document.getElementById('empty-state').classList.add('hidden');document.getElementById('result-area').classList.add('hidden');document.getElementById('result-inputs').classList.add('hidden');document.getElementById('loading-state').classList.remove('hidden');
  const motorType=document.getElementById('sel-motor-type').value;
  let body={motor_type:motorType,_token:'{{ csrf_token() }}'};
  if(currentMode==='vehicle'){const vid=document.getElementById('sel-vehicle').value;if(!vid){alert('Pilih kendaraan terlebih dahulu.');document.getElementById('loading-state').classList.add('hidden');document.getElementById('empty-state').classList.remove('hidden');return;}body.mode='vehicle';body.vehicle_id=vid;}
  else{body.mode='manual';body.jarak=document.getElementById('inp-jarak').value;body.durasi=document.getElementById('inp-durasi').value;body.kecepatan=document.getElementById('inp-kecepatan').value;body.intensitas=document.getElementById('inp-intensitas').value;}
  try{
    const res=await fetch('{{ route("admin.fuzzy.simulator.run") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(body)});
    const data=await res.json();
    document.getElementById('loading-state').classList.add('hidden');
    if(!res.ok){alert(data.message||'Error');document.getElementById('empty-state').classList.remove('hidden');return;}
    renderInputs(data.inputs);renderResults(data.results);
  }catch(e){document.getElementById('loading-state').classList.add('hidden');document.getElementById('empty-state').classList.remove('hidden');alert('Error: '+e.message);}
}
function renderInputs(inputs){
  const map=[['Jarak Tempuh',(inputs.jarak??inputs.distance_since_service_km??0).toFixed(1),'km'],['Durasi Servis',(inputs.durasi??inputs.duration_since_service_days??0).toFixed(0),'hari'],['Kecepatan Rata2',(inputs.kecepatan??inputs.avg_speed_kph??0).toFixed(1),'km/jam'],['Intensitas',(inputs.intensitas??inputs.intensity_km_per_day??0).toFixed(1),'km/hari']];
  document.getElementById('result-inputs-grid').innerHTML=map.map(([l,v,u])=>`<div class="bg-[#0d0d0d] border border-[#1e2939] rounded-lg p-3"><div class="text-[9px] text-[#3d4a56] uppercase tracking-wider">${l}</div><div class="text-base font-bold text-[#8a919e] mt-0.5">${v} <span class="text-[10px] font-normal text-[#3d4a56]">${u}</span></div></div>`).join('');
  document.getElementById('result-inputs').classList.remove('hidden');
}
function renderResults(results){
  const container=document.getElementById('result-components');
  if(!results||results.length===0){container.innerHTML='<div class="text-sm text-[#3d4a56] p-4">Tidak ada komponen aktif untuk jenis motor ini.</div>';document.getElementById('result-area').classList.remove('hidden');return;}
  container.innerHTML=results.map(item=>{
    const r=item.result,label=r.label,score=r.score;
    const bc=label==='Kritis'?'bg-red-500/10 text-red-400 border border-red-500/20':label==='Perlu Servis'?'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20':'bg-green-500/10 text-green-400 border border-green-500/20';
    const mRows=Object.entries(r.membership??{}).map(([k,v])=>`<tr class="border-b border-[#1e2939] last:border-0"><td class="py-2 px-3 text-xs text-[#3d4a56] capitalize">${k}</td><td class="py-2 px-3 text-center text-xs font-mono text-[#8a919e]">${(v.low??0).toFixed(3)}</td><td class="py-2 px-3 text-center text-xs font-mono text-[#8a919e]">${(v.medium??0).toFixed(3)}</td><td class="py-2 px-3 text-center text-xs font-mono text-[#8a919e]">${(v.high??0).toFixed(3)}</td></tr>`).join('');
    const sRows=Object.entries(r.scores??{}).map(([o,w])=>`<div class="flex justify-between items-center text-xs py-1"><span class="text-[#3d4a56]">${o}</span><span class="font-mono text-[#8a919e]">${(w*100).toFixed(1)}%</span></div>`).join('');
    const barColor=score>=75?'bg-red-500':score>=40?'bg-yellow-500':'bg-green-500';
    return `<div class="bg-[#111111] border border-[#1e2939] rounded-xl overflow-hidden"><div class="flex items-center justify-between px-4 py-3 border-b border-[#1e2939]"><span class="font-semibold text-sm text-[#adb5bd]">⚙️ ${item.component}</span><div class="flex items-center gap-2"><span class="text-xs font-mono text-[#8a919e]">Skor: <strong>${score}</strong></span><span class="text-[11px] font-medium px-2 py-0.5 rounded-full ${bc}">${label}</span></div></div><div class="p-4 grid md:grid-cols-2 gap-4"><div><p class="sim-label mb-2">Derajat Keanggotaan (μ)</p><table class="w-full bg-[#0d0d0d] rounded-lg overflow-hidden text-left"><thead><tr class="border-b border-[#1e2939]"><th class="py-1.5 px-3 text-[9px] text-[#2d3748] uppercase">Variabel</th><th class="py-1.5 px-3 text-[9px] text-[#2d3748] uppercase text-center">Low</th><th class="py-1.5 px-3 text-[9px] text-[#2d3748] uppercase text-center">Med</th><th class="py-1.5 px-3 text-[9px] text-[#2d3748] uppercase text-center">High</th></tr></thead><tbody>${mRows}</tbody></table></div><div><p class="sim-label mb-2">Skor Output per Kategori</p><div class="bg-[#0d0d0d] rounded-lg p-3">${sRows}</div><div class="mt-3 bg-[#0d0d0d] rounded-lg p-3"><div class="flex justify-between items-center"><span class="sim-label" style="margin:0;">Skor Akhir (Centroid)</span><span class="text-xl font-bold text-[#adb5bd]">${score}</span></div><div class="mt-1.5 h-1.5 bg-[#1e2939] rounded-full overflow-hidden"><div class="h-full rounded-full transition-all ${barColor}" style="width:${Math.min(score,100)}%"></div></div></div></div></div></div>`;
  }).join('');
  document.getElementById('result-area').classList.remove('hidden');
}
async function injectTrip(){
  const vid=document.getElementById('inj-vehicle').value;if(!vid){alert('Pilih kendaraan target terlebih dahulu.');return;}
  const body={vehicle_id:vid,distance_km:document.getElementById('inj-distance').value,avg_speed_kph:document.getElementById('inj-speed').value,duration_minutes:document.getElementById('inj-duration').value,days_ago:document.getElementById('inj-days-ago').value,_token:'{{ csrf_token() }}'};
  try{
    const res=await fetch('{{ route("admin.fuzzy.simulator.inject") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(body)});
    const data=await res.json();
    const logEl=document.getElementById('inject-log');logEl.classList.remove('hidden');
    const color=res.ok?'text-green-500':'text-red-500';
    logEl.innerHTML=`<div class="text-[11px] ${color} bg-[#0d0d0d] border border-[#1e2939] rounded-lg px-3 py-2">${res.ok?'✅':'❌'} ${data.message} ${res.ok?`— Tgl: ${data.detail.tanggal}, ${data.detail.jarak}, ${data.detail.kecepatan}`:''}</div>`+logEl.innerHTML;
  }catch(e){alert('Error: '+e.message);}
}
</script>
@endsection
