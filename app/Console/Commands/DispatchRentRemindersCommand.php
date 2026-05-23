<?php

namespace App\Console\Commands;

use App\Services\Reminders\ReminderDispatchService;
use Illuminate\Console\Command;

class DispatchRentRemindersCommand extends Command
{
    protected $signature = 'rent:dispatch-reminders
                            {--dry-run : Show how many reminders would be queued without dispatching}
                            {--landlord= : Limit to a single landlord user ID}';

    protected $description = 'Queue rent reminder emails for payments due today based on landlord settings';

    public function handle(ReminderDispatchService $dispatcher): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $landlordId = $this->option('landlord');

        $landlord = null;
        if ($landlordId) {
            $landlord = \App\Models\User::query()->find($landlordId);
            if (! $landlord) {
                $this->error("Landlord user [{$landlordId}] not found.");

                return self::FAILURE;
            }
        }

        if ($dryRun) {
            $this->warn('Dry run — no jobs will be queued.');
        }

        $result = $dispatcher->dispatchDueReminders(landlord: $landlord, dryRun: $dryRun);

        $this->info("Reminders queued: {$result->queued}");
        $this->line("Skipped (duplicate): {$result->skippedDuplicate}");
        $this->line("Skipped (ineligible): {$result->skippedIneligible}");
        $this->line("Skipped (channel disabled): {$result->skippedDisabled}");

        return self::SUCCESS;
    }
}
