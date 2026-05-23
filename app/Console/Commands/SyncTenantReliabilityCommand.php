<?php

namespace App\Console\Commands;

use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Payments\PaymentTrackingService;
use App\Services\Reliability\TenantReliabilityProfileService;
use Illuminate\Console\Command;

class SyncTenantReliabilityCommand extends Command
{
    protected $signature = 'reliability:sync {--tenant= : Tenant ID to sync}';

    protected $description = 'Recalculate payment outcomes and tenant reliability metrics';

    public function handle(
        PaymentTrackingService $tracking,
        TenantReliabilityProfileService $profiles,
    ): int {
        $tenantId = $this->option('tenant');

        PaymentHistory::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->orderBy('id')
            ->chunkById(100, function ($payments) use ($tracking) {
                foreach ($payments as $payment) {
                    $tracking->sync($payment);
                }
            });

        $tenants = Tenant::query()
            ->when($tenantId, fn ($q) => $q->whereKey($tenantId))
            ->get();

        foreach ($tenants as $tenant) {
            $profile = $profiles->profileFromPayments($tenant);
            $profiles->persistCache($tenant, $profile);
        }

        $this->info('Reliability metrics synced for '.$tenants->count().' tenant(s).');

        return self::SUCCESS;
    }
}
