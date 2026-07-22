<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComponentConfig;
use App\Models\FuzzyAuditLog;
use App\Models\FuzzyRule;
use App\Models\FuzzyVariable;
use App\Models\MotorType;
use App\Services\FuzzyEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FuzzyLogicController extends Controller
{
    public function __construct(private FuzzyEngine $fuzzy) {}

    public function index(): View
    {
        $motorTypes = MotorType::where('is_active', true)->get();
        return view('admin.fuzzy.index', compact('motorTypes'));
    }

    public function getComponents(Request $request)
    {
        $motorTypeId = $request->input('motor_type_id');

        $components = ComponentConfig::with(['fuzzyVariables', 'fuzzyRules'])
            ->where('motor_type_id', $motorTypeId)
            ->get()
            ->map(fn ($c) => [
                'id'             => $c->id,
                'name'           => $c->name,
                'status'         => $c->status,
                'warn'           => $c->warn,
                'critical'       => $c->critical,
                'reset_interval' => $c->reset_interval,
                'active_vars'    => $c->active_vars,
                'is_active'      => $c->is_active,
                'is_custom'      => $c->is_custom,
                'notes'          => $c->notes,
                'mf'             => $this->formatMF($c->fuzzyVariables),
                'rules'          => $c->fuzzyRules->map(fn ($r) => [
                    'id'       => $r->id,
                    'var1'     => $r->var1,
                    'label1'   => $r->label1,
                    'operator' => $r->operator,
                    'var2'     => $r->var2,
                    'label2'   => $r->label2,
                    'output'   => $r->output,
                    'weight'   => $r->weight,
                ]),
            ]);

        return response()->json($components);
    }

    public function saveThreshold(Request $request, ComponentConfig $component)
    {
        $validated = $request->validate([
            'warn'           => 'required|integer|min:1',
            'critical'       => 'required|integer|min:1',
            'reset_interval' => 'required|integer|min:1',
            'active_vars'    => 'required|array|min:1',
        ]);

        $ruleVars = $component->fuzzyRules()
            ->get(['var1', 'var2'])
            ->flatMap(fn ($r) => [$r->var1, $r->var2])
            ->filter()
            ->unique()
            ->values()
            ->all();

        $missingRuleVars = collect($validated['active_vars'])
            ->filter(fn ($var) => !in_array($var, $ruleVars, true))
            ->values()
            ->all();

        if (!empty($missingRuleVars)) {
            return response()->json([
                'message' => 'Variabel aktif harus dipakai minimal pada satu rule.',
                'missing_rule_vars' => $missingRuleVars,
            ], 422);
        }

        $old = $component->only(['warn', 'critical', 'reset_interval', 'active_vars']);
        $component->update($validated);

        $this->log($component, 'Threshold & Variabel Aktif', json_encode($old), json_encode($validated));

        return response()->json(['message' => 'Threshold berhasil disimpan']);
    }

    public function saveMembership(Request $request, ComponentConfig $component)
    {
        $validated = $request->validate([
            'var_key'  => 'required|in:jarak,durasi,kecepatan,intensitas',
            'low_a'    => 'required|numeric', 'low_b'    => 'required|numeric', 'low_c'    => 'required|numeric',
            'med_a'    => 'required|numeric', 'med_b'    => 'required|numeric', 'med_c'    => 'required|numeric',
            'high_a'   => 'required|numeric', 'high_b'   => 'required|numeric', 'high_c'   => 'required|numeric',
        ]);

        $lowA = (float) $validated['low_a'];
        $lowB = (float) $validated['low_b'];
        $lowC = (float) $validated['low_c'];
        $medA = (float) $validated['med_a'];
        $medB = (float) $validated['med_b'];
        $medC = (float) $validated['med_c'];
        $highA = (float) $validated['high_a'];
        $highB = (float) $validated['high_b'];
        $highC = (float) $validated['high_c'];

        if (!($lowA <= $lowB && $lowB <= $lowC)) {
            return response()->json([
                'message' => 'Parameter LOW tidak valid. Gunakan urutan low_a <= low_b <= low_c.',
            ], 422);
        }

        if (!($medA <= $medB && $medB <= $medC)) {
            return response()->json([
                'message' => 'Parameter MEDIUM tidak valid. Gunakan urutan med_a <= med_b <= med_c.',
            ], 422);
        }

        $isHighOpenEnded = $highC === 999.0;
        $isHighOrdered = ($highA <= $highB) && ($highB <= $highC);
        if (!($isHighOpenEnded || $isHighOrdered)) {
            return response()->json([
                'message' => 'Parameter HIGH tidak valid. Gunakan high_a <= high_b <= high_c atau high_c = 999 untuk batas atas terbuka.',
            ], 422);
        }

        if (!($lowB <= $medB && $medB <= $highB)) {
            return response()->json([
                'message' => 'Urutan batas fuzzy tidak konsisten. Gunakan low_b <= med_b <= high_b.',
            ], 422);
        }

        $mf = FuzzyVariable::updateOrCreate(
            ['component_config_id' => $component->id, 'var_key' => $validated['var_key']],
            $validated
        );

        $this->log($component, "MF [{$validated['var_key']}]", null, json_encode($validated));

        return response()->json(['message' => 'Membership function disimpan', 'mf' => $mf]);
    }

    public function storeRule(Request $request, ComponentConfig $component)
    {
        $validated = $request->validate([
            'var1'     => 'required|in:jarak,durasi,kecepatan,intensitas',
            'label1'   => 'required|in:low,medium,high',
            'operator' => 'required|in:AND,OR',
            'var2'     => 'required|in:jarak,durasi,kecepatan,intensitas',
            'label2'   => 'required|in:low,medium,high',
            'output'   => 'required|in:Baik,Perlu Servis,Kritis',
            'weight'   => 'required|numeric|min:0|max:1',
        ]);

        $rule = $component->fuzzyRules()->create($validated);
        $this->log($component, 'Rule Baru', null, json_encode($validated));

        return response()->json(['message' => 'Rule ditambahkan', 'rule' => $rule]);
    }

    public function updateRule(Request $request, FuzzyRule $rule)
    {
        $validated = $request->validate([
            'var1'     => 'required|in:jarak,durasi,kecepatan,intensitas',
            'label1'   => 'required|in:low,medium,high',
            'operator' => 'required|in:AND,OR',
            'var2'     => 'required|in:jarak,durasi,kecepatan,intensitas',
            'label2'   => 'required|in:low,medium,high',
            'output'   => 'required|in:Baik,Perlu Servis,Kritis',
            'weight'   => 'required|numeric|min:0|max:1',
        ]);

        $old = $rule->toArray();
        $rule->update($validated);
        $this->log($rule->componentConfig, 'Edit Rule', json_encode($old), json_encode($validated));

        return response()->json(['message' => 'Rule diperbarui', 'rule' => $rule]);
    }

    public function deleteRule(FuzzyRule $rule)
    {
        $component = $rule->componentConfig;
        $old = $rule->toArray();
        $rule->delete();
        $this->log($component, 'Hapus Rule', json_encode($old), null);

        return response()->json(['message' => 'Rule dihapus']);
    }

    public function storeComponent(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'motor_type_ids' => 'required|array|min:1',
            'warn'           => 'required|integer',
            'critical'       => 'required|integer',
            'reset_interval' => 'required|integer',
            'active_vars'    => 'required|array|min:1',
            'notes'          => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['motor_type_ids'] as $motorTypeId) {
                $component = ComponentConfig::create([
                    'motor_type_id'  => $motorTypeId,
                    'name'           => $validated['name'],
                    'warn'           => $validated['warn'],
                    'critical'       => $validated['critical'],
                    'reset_interval' => $validated['reset_interval'],
                    'active_vars'    => $validated['active_vars'],
                    'notes'          => $validated['notes'] ?? null,
                    'is_custom'      => true,
                ]);

                $defaults = [
                    'jarak'      => [0, 0, 800, 600, 1000, 1500, 1200, 2000, 3000],
                    'durasi'     => [0, 0, 30,  20,  45,   75,   60,   90,   999],
                    'kecepatan'  => [0, 0, 40,  30,  55,   80,   70,   100,  150],
                    'intensitas' => [0, 0, 15,  10,  25,   40,   30,   50,   100],
                ];

                foreach ($defaults as $varKey => $vals) {
                    FuzzyVariable::create([
                        'component_config_id' => $component->id,
                        'var_key' => $varKey,
                        'low_a'  => $vals[0], 'low_b'  => $vals[1], 'low_c'  => $vals[2],
                        'med_a'  => $vals[3], 'med_b'  => $vals[4], 'med_c'  => $vals[5],
                        'high_a' => $vals[6], 'high_b' => $vals[7], 'high_c' => $vals[8],
                    ]);
                }
            }
        });

        return response()->json(['message' => "Komponen \"{$validated['name']}\" berhasil ditambahkan"]);
    }

    public function toggleComponent(ComponentConfig $component)
    {
        $oldIsActive = (bool) $component->is_active;
        $component->update(['is_active' => !$oldIsActive]);

        $status = $component->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->log($component, 'Status Aktif', $oldIsActive ? 'aktif' : 'nonaktif', $component->is_active ? 'aktif' : 'nonaktif');

        return response()->json(['message' => "Komponen {$status}", 'is_active' => $component->is_active]);
    }

    public function deleteComponent(ComponentConfig $component)
    {
        $name = $component->name;
        $component->delete();

        return response()->json(['message' => "Komponen \"{$name}\" dihapus permanen"]);
    }

    public function test(Request $request, ComponentConfig $component)
    {
        $validated = $request->validate([
            'jarak'      => 'required|numeric|min:0',
            'durasi'     => 'required|numeric|min:0',
            'kecepatan'  => 'required|numeric|min:0',
            'intensitas' => 'required|numeric|min:0',
        ]);

        $component->load(['fuzzyVariables', 'fuzzyRules']);
        $result = $this->fuzzy->calculate($component, $validated);

        return response()->json($result);
    }

    public function chartData(Request $request)
    {
        $validated = $request->validate([
            'low'    => 'required|array|size:3',
            'medium' => 'required|array|size:3',
            'high'   => 'required|array|size:3',
            'max_x'  => 'nullable|integer',
        ]);

        $data = $this->fuzzy->generateChartData(
            ['low' => $validated['low'], 'medium' => $validated['medium'], 'high' => $validated['high']],
            $validated['max_x'] ?? 3000
        );

        return response()->json($data);
    }

    private function formatMF($variables): array
    {
        $mf = [];
        foreach ($variables as $v) {
            $mf[$v->var_key] = [
                'low'    => [$v->low_a,  $v->low_b,  $v->low_c],
                'medium' => [$v->med_a,  $v->med_b,  $v->med_c],
                'high'   => [$v->high_a, $v->high_b, $v->high_c],
            ];
        }
        return $mf;
    }

    private function log(ComponentConfig $component, string $field, ?string $old, ?string $new): void
    {
        $component->loadMissing('motorType');

        FuzzyAuditLog::create([
            'admin_id'       => Auth::id(),
            'component_name' => $component->name,
            'motor_type'     => $component->motorType->name ?? '-',
            'field_changed'  => $field,
            'old_value'      => $old,
            'new_value'      => $new,
        ]);
    }
}
