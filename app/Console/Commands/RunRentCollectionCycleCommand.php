<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Rent\RentPeriodAutomationService;
use Illuminate\Console\Command;

class RunRentCollectionCycleCommand extends Command
{
    protected $signature = 'rent:run-collection-cycle
                            {--landlord= : Limit to a single landlord user ID}';

    protected $description = 'Sync rent period statuses and open the next period for active tenants';

    public function handle(RentPeriodAutomationService $automation): int
    {
        $landlordId = $this->option('landlord');

        if ($landlordId) {
            $landlord = User::query()->find($landlordId);

            if (! $landlord) {
                $this->error("Landlord user [{$landlordId}] not found.");

                return self::FAILURE;
            }

            $count = $automation->runForLandlord($landlord);
            $this->info("Collection cycle ran for landlord #{$landlordId} ({$count} tenant(s) processed).");

            return self::SUCCESS;
        }

        $count = $automation->runForAllLandlords();
        $this->info("Collection cycle ran for all landlords ({$count} tenant(s) processed).");

        return self::SUCCESS;
    }
}
