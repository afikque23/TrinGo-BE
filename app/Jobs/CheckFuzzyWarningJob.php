<?php

namespace App\Jobs;

use App\Models\Vehicle;
use App\Models\NotificationTemplate;
use App\Services\NotificationService;
use App\Services\RecommendationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckFuzzyWarningJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService, RecommendationService $recommendationService): void
    {
        Log::info('CheckFuzzyWarningJob started.');
        
        $template = NotificationTemplate::where('trigger_type', 'fuzzy_warning')
                        ->where('is_active', true)
                        ->first();

        if (!$template) {
            Log::info('CheckFuzzyWarningJob: Template fuzzy_warning is disabled or missing.');
            return;
        }

        $vehicles = Vehicle::with('user')->get();

        foreach ($vehicles as $vehicle) {
            try {
                $fuzzyData = $recommendationService->getVehicleFuzzySnapshot($vehicle);
                
                $warningComponents = [];
                $highestWarningScore = 0;
                $statuses = $fuzzyData['component_statuses'] ?? [];
                $scores = $fuzzyData['component_scores'] ?? [];

                foreach ($statuses as $component => $status) {
                    if ($status === 'warning') {
                        $namaKomponen = ucwords(str_replace('_', ' ', (string) $component));
                        $warningComponents[] = $namaKomponen;
                        $score = $scores[$component] ?? 0;
                        if ($score > $highestWarningScore) {
                            $highestWarningScore = $score;
                        }
                    }
                }

                if (!empty($warningComponents)) {
                    $user = $vehicle->user ?? ($vehicle->user_id ? \App\Models\User::find($vehicle->user_id) : null);
                    if (!$user) {
                        continue;
                    }

                    $serviceName = implode(', ', $warningComponents);

                    $notificationService->sendFromTemplate(
                        $template,
                        [
                            'service_name' => $serviceName,
                            'fuzzy_score' => round($highestWarningScore),
                        ],
                        $user,
                        $vehicle->device_id,
                        $vehicle
                    );
                    Log::info("Warning notification sent for Vehicle {$vehicle->id} on components: {$serviceName}");
                }
                
            } catch (\Exception $e) {
                Log::error("CheckFuzzyWarningJob error for Vehicle {$vehicle->id}: " . $e->getMessage());
            }
        }
        
        Log::info('CheckFuzzyWarningJob finished.');
    }
}
