<?php

namespace App\Console\Commands;

use App\Services\ServiceScheduleReminderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckServiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:check-service-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and send service schedule reminders (both km-based and date-based)';

    protected ServiceScheduleReminderService $reminderService;

    public function __construct(ServiceScheduleReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking service schedule reminders...');

        try {
            // Check all pending reminders (km-based + date-based)
            $results = $this->reminderService->checkAllPendingReminders();

            $kmBasedCount = count($results['km_based']);
            $dateBasedCount = count($results['date_based']);
            $totalCount = $kmBasedCount + $dateBasedCount;

            $this->info("✓ KM-based reminders sent: {$kmBasedCount}");
            $this->info("✓ Date-based reminders sent: {$dateBasedCount}");
            $this->info("✓ Total reminders sent: {$totalCount}");

            if ($totalCount > 0) {
                Log::info("Service reminders check completed: {$totalCount} reminders sent", [
                    'km_based' => $kmBasedCount,
                    'date_based' => $dateBasedCount,
                ]);
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to check service reminders: ' . $e->getMessage());
            Log::error('Service reminders check failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
