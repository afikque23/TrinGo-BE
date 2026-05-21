<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FuzzyConfigAudit;
use App\Models\FuzzyComponentConfig;
use App\Models\MaintenanceComponent;
use App\Models\MotorTypeComponent;
use App\Services\Fuzzy\FuzzyEngine;
use App\Services\Fuzzy\DefaultFuzzyConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FuzzyConfigController extends Controller
{
    private const MOTOR_TYPES = ['matic', 'manual', 'sport', 'adventure'];

    private function normalizeMotorType(?string $motorType): string
    {
        $motorType = strtolower(trim((string) $motorType));
        if (!in_array($motorType, self::MOTOR_TYPES, true)) {
            return 'matic';
        }
        return $motorType;
    }

    private function ensureConfigExists(MotorTypeComponent $mapping): FuzzyComponentConfig
    {
        $config = $mapping->fuzzyConfig;
        if ($config) {
            return $config;
        }

        $config = FuzzyComponentConfig::create([
            'motor_type_component_id' => $mapping->id,
            'warn_score' => 60,
            'critical_score' => 40,
            'config' => DefaultFuzzyConfig::make(),
            'version' => 1,
        ]);

        $mapping->setRelation('fuzzyConfig', $config);
        return $config;
    }

    public function index(Request $request): View
    {
        $motorType = $this->normalizeMotorType((string) $request->query('motor_type', 'matic'));

        $mappings = MotorTypeComponent::query()
            ->where('motor_type', $motorType)
            ->with(['component', 'fuzzyConfig'])
            ->get()
            ->sortBy(fn (MotorTypeComponent $m) => $m->component?->name ?? '')
            ->values();

        $mappedComponentIds = $mappings
            ->pluck('maintenance_component_id')
            ->filter()
            ->values();

        $availableComponents = MaintenanceComponent::query()
            ->where('is_active', true)
            ->whereNotIn('id', $mappedComponentIds)
            ->orderBy('name')
            ->get();

        $countsByType = [];
        foreach (self::MOTOR_TYPES as $type) {
            $countsByType[$type] = MotorTypeComponent::query()
                ->where('motor_type', $type)
                ->count();
        }

        return view('admin.fuzzy_config.index', [
            'motorTypes' => self::MOTOR_TYPES,
            'motorType' => $motorType,
            'countsByType' => $countsByType,
            'mappings' => $mappings,
            'availableComponents' => $availableComponents,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $motorType = $this->normalizeMotorType($request->query('motor_type'));

        $mappings = MotorTypeComponent::query()
            ->where('motor_type', $motorType)
            ->with(['component', 'fuzzyConfig'])
            ->get()
            ->sortBy(fn (MotorTypeComponent $m) => $m->component?->name ?? '')
            ->values();

        $items = [];
        foreach ($mappings as $mapping) {
            $config = $this->ensureConfigExists($mapping);

            $items[] = [
                'id' => $mapping->id,
                'motor_type' => $mapping->motor_type,
                'is_active' => (bool) $mapping->is_active,
                'component' => [
                    'id' => $mapping->component?->id,
                    'key' => $mapping->component?->key,
                    'name' => $mapping->component?->name,
                ],
                'warn_score' => (int) ($config->warn_score ?? 60),
                'critical_score' => (int) ($config->critical_score ?? 40),
                'version' => (int) ($config->version ?? 1),
                'updated_at' => $config->updated_at?->toISOString(),
                'config' => $config->config ?? [],
            ];
        }

        $mappedComponentIds = $mappings
            ->pluck('maintenance_component_id')
            ->filter()
            ->values();

        $availableComponents = MaintenanceComponent::query()
            ->where('is_active', true)
            ->whereNotIn('id', $mappedComponentIds)
            ->orderBy('name')
            ->get(['id', 'key', 'name']);

        return response()->json([
            'motor_types' => self::MOTOR_TYPES,
            'motor_type' => $motorType,
            'items' => $items,
            'available_components' => $availableComponents,
        ]);
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'motor_type' => ['required', 'in:' . implode(',', self::MOTOR_TYPES)],
            'items' => ['required', 'array'],
            'items.*.motor_type_component_id' => ['required', 'integer'],
            'items.*.warn_score' => ['required', 'integer', 'min:0', 'max:100'],
            'items.*.critical_score' => ['required', 'integer', 'min:0', 'max:100'],
            'items.*.config' => ['required', 'array'],
        ]);

        $motorType = (string) $validated['motor_type'];
        $admin = $request->user();
        $adminEmail = $admin?->email ?? null;

        try {
            DB::transaction(function () use ($validated, $motorType, $adminEmail) {
                foreach ($validated['items'] as $item) {
                    $mtcId = (int) $item['motor_type_component_id'];

                    $mapping = MotorTypeComponent::query()
                        ->where('id', $mtcId)
                        ->where('motor_type', $motorType)
                        ->with(['component', 'fuzzyConfig'])
                        ->first();

                    if (!$mapping) {
                        continue;
                    }

                    $warnScore = (int) $item['warn_score'];
                    $criticalScore = (int) $item['critical_score'];
                    if ($criticalScore > $warnScore) {
                        throw new \InvalidArgumentException('critical_score harus <= warn_score.');
                    }

                    $config = $mapping->fuzzyConfig;
                    if (!$config) {
                        $config = new FuzzyComponentConfig([
                            'motor_type_component_id' => $mapping->id,
                            'version' => 0,
                        ]);
                    }

                    $oldWarn = (int) ($config->warn_score ?? 60);
                    $oldCritical = (int) ($config->critical_score ?? 40);
                    $oldConfig = $config->config ?? [];

                    $newConfig = $item['config'];

                    $config->warn_score = $warnScore;
                    $config->critical_score = $criticalScore;
                    $config->config = $newConfig;
                    $config->version = ((int) ($config->version ?? 0)) + 1;
                    $config->save();

                    if ($oldWarn !== $warnScore) {
                        FuzzyConfigAudit::create([
                            'motor_type' => $motorType,
                            'motor_type_component_id' => $mapping->id,
                            'maintenance_component_id' => $mapping->maintenance_component_id,
                            'admin_email' => $adminEmail,
                            'component_label' => ($mapping->component?->name ?? '-') . ' (' . ucfirst($motorType) . ')',
                            'changed_field' => 'Threshold Warn',
                            'old_value' => (string) $oldWarn,
                            'new_value' => (string) $warnScore,
                        ]);
                    }

                    if ($oldCritical !== $criticalScore) {
                        FuzzyConfigAudit::create([
                            'motor_type' => $motorType,
                            'motor_type_component_id' => $mapping->id,
                            'maintenance_component_id' => $mapping->maintenance_component_id,
                            'admin_email' => $adminEmail,
                            'component_label' => ($mapping->component?->name ?? '-') . ' (' . ucfirst($motorType) . ')',
                            'changed_field' => 'Threshold Critical',
                            'old_value' => (string) $oldCritical,
                            'new_value' => (string) $criticalScore,
                        ]);
                    }

                    if (json_encode($oldConfig) !== json_encode($newConfig)) {
                        FuzzyConfigAudit::create([
                            'motor_type' => $motorType,
                            'motor_type_component_id' => $mapping->id,
                            'maintenance_component_id' => $mapping->maintenance_component_id,
                            'admin_email' => $adminEmail,
                            'component_label' => ($mapping->component?->name ?? '-') . ' (' . ucfirst($motorType) . ')',
                            'changed_field' => 'Config',
                            'old_value' => null,
                            'new_value' => null,
                        ]);
                    }
                }
            });
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'items' => [$e->getMessage()],
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function test(Request $request, FuzzyEngine $fuzzyEngine): JsonResponse
    {
        $validated = $request->validate([
            'motor_type_component_id' => ['required', 'integer', 'exists:motor_type_components,id'],
            'inputs' => ['required', 'array'],
            'inputs.distance_since_service_km' => ['nullable', 'numeric'],
            'inputs.duration_since_service_days' => ['nullable', 'numeric'],
            'inputs.avg_speed_kph' => ['nullable', 'numeric'],
            'inputs.intensity_km_per_day' => ['nullable', 'numeric'],
            'warn_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'critical_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'config' => ['nullable', 'array'],
        ]);

        $mapping = MotorTypeComponent::query()
            ->with(['component', 'fuzzyConfig'])
            ->findOrFail((int) $validated['motor_type_component_id']);

        $configRow = $this->ensureConfigExists($mapping);

        $warnScore = array_key_exists('warn_score', $validated) && $validated['warn_score'] !== null
            ? (int) $validated['warn_score']
            : (int) ($configRow->warn_score ?? 60);

        $criticalScore = array_key_exists('critical_score', $validated) && $validated['critical_score'] !== null
            ? (int) $validated['critical_score']
            : (int) ($configRow->critical_score ?? 40);

        if ($criticalScore > $warnScore) {
            throw ValidationException::withMessages([
                'critical_score' => ['critical_score harus <= warn_score.'],
            ]);
        }

        $config = array_key_exists('config', $validated) && is_array($validated['config'])
            ? $validated['config']
            : ($configRow->config ?? []);

        $score = $fuzzyEngine->evaluateConfigScore($validated['inputs'], $config);
        $status = $fuzzyEngine->statusFromScorePublic($score, $warnScore, $criticalScore);

        return response()->json([
            'component' => [
                'name' => $mapping->component?->name,
                'key' => $mapping->component?->key,
            ],
            'score' => $score,
            'status' => $status,
            'thresholds' => [
                'warn' => $warnScore,
                'critical' => $criticalScore,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'motor_type' => ['required', 'in:' . implode(',', self::MOTOR_TYPES)],
            'maintenance_component_id' => ['required', 'integer', 'exists:maintenance_components,id'],
            'warn_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'critical_score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $warnScore = array_key_exists('warn_score', $validated) && $validated['warn_score'] !== null
            ? (int) $validated['warn_score']
            : 60;

        $criticalScore = array_key_exists('critical_score', $validated) && $validated['critical_score'] !== null
            ? (int) $validated['critical_score']
            : 40;

        if ($criticalScore > $warnScore) {
            return back()->withInput()->with('error', 'critical_score harus <= warn_score.');
        }

        DB::transaction(function () use ($validated, $warnScore, $criticalScore) {
            $mapping = MotorTypeComponent::firstOrCreate([
                'motor_type' => $validated['motor_type'],
                'maintenance_component_id' => (int) $validated['maintenance_component_id'],
            ], [
                'is_active' => true,
            ]);

            FuzzyComponentConfig::firstOrCreate([
                'motor_type_component_id' => $mapping->id,
            ], [
                'warn_score' => $warnScore,
                'critical_score' => $criticalScore,
                'config' => DefaultFuzzyConfig::make(),
                'version' => 1,
            ]);
        });

        return redirect()
            ->route('admin.fuzzy-config.index', ['motor_type' => $validated['motor_type']])
            ->with('success', 'Konfigurasi komponen berhasil ditambahkan.');
    }

    public function edit(MotorTypeComponent $motorTypeComponent): View
    {
        $motorTypeComponent->loadMissing(['component', 'fuzzyConfig']);

        $config = $motorTypeComponent->fuzzyConfig;
        if (!$config) {
            $config = FuzzyComponentConfig::create([
                'motor_type_component_id' => $motorTypeComponent->id,
                'warn_score' => 60,
                'critical_score' => 40,
                'config' => DefaultFuzzyConfig::make(),
                'version' => 1,
            ]);

            $motorTypeComponent->setRelation('fuzzyConfig', $config);
        }

        return view('admin.fuzzy_config.edit', [
            'mapping' => $motorTypeComponent,
            'config' => $config,
            'configJson' => json_encode($config->config ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function resetDefault(Request $request, MotorTypeComponent $motorTypeComponent): RedirectResponse|JsonResponse
    {
        $motorTypeComponent->loadMissing(['component', 'fuzzyConfig']);

        $adminEmail = $request->user()?->email ?? null;

        DB::transaction(function () use ($motorTypeComponent, $adminEmail) {
            $config = $this->ensureConfigExists($motorTypeComponent);
            $oldConfig = $config->config ?? [];

            $config->config = DefaultFuzzyConfig::make();
            $config->version = ((int) ($config->version ?? 0)) + 1;
            $config->save();

            if (json_encode($oldConfig) !== json_encode($config->config)) {
                FuzzyConfigAudit::create([
                    'motor_type' => $motorTypeComponent->motor_type,
                    'motor_type_component_id' => $motorTypeComponent->id,
                    'maintenance_component_id' => $motorTypeComponent->maintenance_component_id,
                    'admin_email' => $adminEmail,
                    'component_label' => ($motorTypeComponent->component?->name ?? '-') . ' (' . ucfirst($motorTypeComponent->motor_type) . ')',
                    'changed_field' => 'Reset Default',
                    'old_value' => null,
                    'new_value' => null,
                ]);
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Config berhasil di-reset ke default.');
    }

    public function update(Request $request, MotorTypeComponent $motorTypeComponent): RedirectResponse
    {
        $validated = $request->validate([
            'warn_score' => ['required', 'integer', 'min:0', 'max:100'],
            'critical_score' => ['required', 'integer', 'min:0', 'max:100'],
            'config_json' => ['required', 'string'],
        ]);

        $warnScore = (int) $validated['warn_score'];
        $criticalScore = (int) $validated['critical_score'];

        if ($criticalScore > $warnScore) {
            return back()->withInput()->with('error', 'critical_score harus <= warn_score.');
        }

        $decoded = json_decode((string) $validated['config_json'], true);
        if (!is_array($decoded)) {
            return back()->withInput()->with('error', 'JSON config tidak valid.');
        }

        DB::transaction(function () use ($motorTypeComponent, $warnScore, $criticalScore, $decoded) {
            $config = $motorTypeComponent->fuzzyConfig;
            if (!$config) {
                $config = new FuzzyComponentConfig([
                    'motor_type_component_id' => $motorTypeComponent->id,
                    'version' => 0,
                ]);
            }

            $config->warn_score = $warnScore;
            $config->critical_score = $criticalScore;
            $config->config = $decoded;
            $config->version = ((int) ($config->version ?? 0)) + 1;
            $config->save();
        });

        return redirect()
            ->route('admin.fuzzy-config.index', ['motor_type' => $motorTypeComponent->motor_type])
            ->with('success', 'Konfigurasi berhasil disimpan.');
    }

    public function toggle(MotorTypeComponent $motorTypeComponent): RedirectResponse
    {
        $motorTypeComponent->is_active = !$motorTypeComponent->is_active;
        $motorTypeComponent->save();

        return back()->with('success', 'Status komponen berhasil diubah.');
    }

    public function destroy(MotorTypeComponent $motorTypeComponent): RedirectResponse
    {
        $motorType = $motorTypeComponent->motor_type;

        DB::transaction(function () use ($motorTypeComponent) {
            $motorTypeComponent->fuzzyConfig()?->delete();
            $motorTypeComponent->delete();
        });

        return redirect()
            ->route('admin.fuzzy-config.index', ['motor_type' => $motorType])
            ->with('success', 'Mapping komponen berhasil dihapus.');
    }
}
