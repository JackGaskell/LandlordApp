<?php

namespace Tests\Feature\Reliability;

use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Payments\PaymentTrackingService;
use App\Services\Reliability\TenantReliabilityProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantReliabilityProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_persists_tenant_cache_columns(): void
    {
        $tenant = Tenant::factory()->create();
        $tracking = app(PaymentTrackingService::class);

        $payment = PaymentHistory::factory()->paid()->for($tenant)->create([
            'due_date' => now()->subMonth(),
            'paid_at' => now()->subMonth()->addDay(),
        ]);
        $tracking->sync($payment);

        $profile = app(TenantReliabilityProfileService::class)->profile($tenant->fresh());

        $this->assertGreaterThan(0, $tenant->fresh()->reliability_tracked_periods);
        $this->assertNotNull($tenant->fresh()->reliability_calculated_at);
        $this->assertSame($profile->score, (float) $tenant->fresh()->reliability_score);
    }

    public function test_sync_command_recalculates_metrics(): void
    {
        $tenant = Tenant::factory()->create();
        PaymentHistory::factory()->paid()->count(2)->for($tenant)->create();

        $this->artisan('reliability:sync', ['--tenant' => $tenant->id])
            ->assertSuccessful();

        $tenant->refresh();
        $this->assertNotNull($tenant->reliability_calculated_at);
        $this->assertGreaterThanOrEqual(2, $tenant->reliability_tracked_periods);
    }
}
