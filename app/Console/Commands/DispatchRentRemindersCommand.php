<?php

namespace App\Console\Commands;

use App\Services\Reminders\ReminderDispatchService;
use Illuminate\Console\Command;

class DispatchRentRemindersCommand extends Command
{
    protected $signature = 'rent:dispatch-reminders
                            {--dry-run : Show how many reminders would be queued without dispatching}';

    protected $description = 'Queue rent reminder emails for payments due today based on landlord settings';

    public function handle(ReminderDispatchService $dispatcher): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run — no jobs will be pushed to the queue.');
        }

        $result = $dispatcher->dispatchDueReminders(dryRun: $dryRun);

        $this->info("Reminders queued: {$result['queued']}");
        $this->line("Skipped (already sent today): {$result['skipped']}");

        if (! $dryRun && $result['queued'] > 0) {
            $this->comment('Run a queue worker to process jobs: php artisan queue:work');
        }

        return self::SUCCESS;
    }
}
