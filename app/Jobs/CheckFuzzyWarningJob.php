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
                
                // Track highest warning score component to only send one notification per vehicle
                $highestWarningScore = 0;
                $warningComponent = null;

                foreach ($fuzzyData['component_scores'] as $component => $score) {
                    if ($score >= 40 && $score < 75) {
                        if ($score > $highestWarningScore) {
                            $highestWarningScore = $score;
                            $warningComponent = $component;
                        }
                    }
                }

                if ($warningComponent) {
                    $notificationService->sendFromTemplate(
                        $template,
                        [
                            'service_name' => ucfirst(str_replace('_', ' ', $warningComponent)),
                            'fuzzy_score' => round($highestWarningScore),
                        ],
                        $vehicle->user,
                        $vehicle->device_id,
                        $vehicle
                    );
                    Log::info("Fuzzy warning sent for Vehicle {$vehicle->id} on component {$warningComponent}");
                }
                
            } catch (\Exception $e) {
                Log::error("CheckFuzzyWarningJob error for Vehicle {$vehicle->id}: " . $e->getMessage());
            }
        }
        
        Log::info('CheckFuzzyWarningJob finished.');
    }
}
