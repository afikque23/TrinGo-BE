<?php

namespace App\Listeners;

use App\Events\VehicleStatusChecked;
use App\Models\NotificationTemplate;
use App\Services\NotificationService;
use App\Services\RecommendationService;
use Illuminate\Support\Facades\Log;

class CheckCriticalFuzzyStatusListener
{

    public function __construct(
        private NotificationService $notificationService,
        private RecommendationService $recommendationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(VehicleStatusChecked $event): void
    {
        $vehicle = $event->vehicle;
        
        $template = NotificationTemplate::where('trigger_type', 'fuzzy_critical')
                        ->where('is_active', true)
                        ->first();

        if (!$template) {
            Log::info('CheckCriticalFuzzyStatusListener: Template fuzzy_critical is disabled or missing.');
            return;
        }

        try {
            $fuzzyData = $this->recommendationService->getVehicleFuzzySnapshot($vehicle);
            
            $criticalComponents = [];
            $statuses = $fuzzyData['component_statuses'] ?? [];
            $scores = $fuzzyData['component_scores'] ?? [];
            $lowestHealthScore = 100;

            foreach ($statuses as $component => $status) {
                if ($status === 'critical') {
                    $namaKomponen = ucwords(str_replace('_', ' ', (string) $component));
                    $criticalComponents[] = $namaKomponen;
                    $score = $scores[$component] ?? 100;
                    if ($score < $lowestHealthScore) {
                        $lowestHealthScore = $score;
                    }
                }
            }

            if (!empty($criticalComponents)) {
                $user = $vehicle->user ?? ($vehicle->user_id ? \App\Models\User::find($vehicle->user_id) : null);

                if (!$user) {
                    Log::warning("CheckCriticalFuzzyStatusListener: Vehicle {$vehicle->id} has no valid user owner. Skipping notification.");
                    return;
                }

                // Konversi kembali healthScore (0-100, 100=bagus) menjadi urgencyScore (0-100, 100=kritis) untuk notifikasi
                $urgencyScore = 100 - $lowestHealthScore;
                $serviceName = implode(', ', $criticalComponents);

                // Notifikasi dikirim saat itu juga secara real-time
                $this->notificationService->sendFromTemplate(
                    $template,
                    [
                        'service_name' => $serviceName,
                        'fuzzy_score' => round($urgencyScore),
                    ],
                    $user,
                    $vehicle->device_id,
                    $vehicle
                );
                
                Log::info("Critical alert sent for Vehicle {$vehicle->id} (User {$user->id}) on components: {$serviceName}");
            }
            
        } catch (\Exception $e) {
            Log::error("CheckCriticalFuzzyStatusListener error for Vehicle {$vehicle->id}: " . $e->getMessage());
        }
    }
}
