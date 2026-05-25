<?php

namespace Tests\Feature\Portal;

use App\Enums\PaymentOutcome;
use App\Enums\PaymentStatus;
use App\Enums\TenantCollectionStatus;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Payments\PaymentTrackingService;
use App\Services\Portal\TenantPortalDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantPortalDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_for_authenticated_tenant(): void
    {
        $tenant = Tenant::factory()->create([
            'password' => Hash::make('password'),
            'portal_enabled_at' => now(),
        ]);

        PaymentHistory::factory()->dueSoon()->for($tenant)->create([
            'due_date' => now()->addDays(5),
        ]);

        $this->actingAs($tenant, 'tenant')
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('tenant score')
            ->assertSee('reflects how consistently you pay rent')
            ->assertSee('New')
            ->assertSee('Not started yet')
            ->assertSee('Your score hasn\'t started yet')
            ->assertSee('Starting')
            ->assertSee('0–39')
            ->assertSee('40–59')
            ->assertSee('Excellent')
            ->assertSee('Pay on time to begin')
            ->assertSee('Next payment')
            ->assertSee('On time')
            ->assertSee('Late')
            ->assertSee('Month streak')
            ->assertSee('Consistency')
            ->assertSee('History')
            ->assertSee('Confirm payment');
    }

    public function test_dashboard_shows_numeric_score_after_first_scored_payment(): void
    {
        $tenant = Tenant::factory()->create([
            'password' => Hash::make('password'),
            'portal_enabled_at' => now(),
        ]);

        PaymentHistory::factory()->paid()->for($tenant)->create([
            'due_date' => now()->subMonth(),
            'paid_at' => now()->subMonth()->addDay(),
        ]);

        $this->actingAs($tenant, 'tenant')
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('out of 100')
            ->assertSee('You\'re at the')
            ->assertDontSee('Not started yet');
    }

    public function test_portal_snapshot_reflects_overdue_collection_state(): void
    {
        $tenant = Tenant::factory()->create(['portal_enabled_at' => now()]);

        PaymentHistory::factory()->for($tenant)->create([
            'due_date' => now()->subDays(3),
            'paid_at' => null,
            'status' => PaymentStatus::Overdue,
            'amount' => 1000,
        ]);

        $snapshot = app(TenantPortalDashboardService::class)->snapshot($tenant);

        $this->assertTrue($snapshot->collection->isOverdue);
        $this->assertSame(TenantCollectionStatus::ActionNeeded, $snapshot->collection->status);
        $this->assertTrue($snapshot->upcomingRent->isOverdue);
        $this->assertCount(4, $snapshot->summaryCards);
        $this->assertGreaterThanOrEqual(1, $snapshot->paymentHistory->count());
    }

    public function test_portal_snapshot_reflects_on_track_paid_period(): void
    {
        $tenant = Tenant::factory()->create(['portal_enabled_at' => now()]);

        PaymentHistory::factory()->paid()->for($tenant)->create([
            'due_date' => now()->startOfMonth(),
            'paid_at' => now()->subDay(),
        ]);

        $snapshot = app(TenantPortalDashboardService::class)->snapshot($tenant);

        $this->assertContains($snapshot->collection->status, [
            TenantCollectionStatus::OnTrack,
            TenantCollectionStatus::Upcoming,
        ]);
        $this->assertFalse($snapshot->upcomingRent->isOverdue);
        $this->assertGreaterThanOrEqual(2, $snapshot->paymentHistory->count());
    }

    public function test_overdue_period_lowers_reliability_score_on_dashboard_snapshot(): void
    {
        $tenant = Tenant::factory()->create(['portal_enabled_at' => now()]);
        $tracking = app(PaymentTrackingService::class);

        foreach ([4, 3, 2, 1] as $monthsAgo) {
            $paid = PaymentHistory::factory()->paid()->for($tenant)->create([
                'due_date' => now()->subMonths($monthsAgo)->day(15),
                'paid_at' => now()->subMonths($monthsAgo)->day(16),
            ]);
            $tracking->sync($paid);
        }

        PaymentHistory::factory()->for($tenant)->create([
            'due_date' => now()->subDays(2),
            'paid_at' => null,
            'status' => PaymentStatus::DueSoon,
            'amount' => 950,
        ]);

        $snapshot = app(TenantPortalDashboardService::class)->snapshot($tenant);

        $this->assertLessThan(100.0, $snapshot->reliability->score);
        $this->assertGreaterThanOrEqual(1, $snapshot->reliability->missedCount);
        $this->assertSame(0, $snapshot->reliability->currentStreak);
        $this->assertSame(PaymentOutcome::Missed, $snapshot->reliability->timeline->first()->outcome);
    }

    public function test_payment_history_marks_current_period(): void
    {
        $tenant = Tenant::factory()->create();

        PaymentHistory::factory()->paid()->for($tenant)->create(['due_date' => now()->subMonth()]);
        $current = PaymentHistory::factory()->dueSoon()->for($tenant)->create(['due_date' => now()->addDays(3)]);

        $snapshot = app(TenantPortalDashboardService::class)->snapshot($tenant);

        $currentItem = $snapshot->paymentHistory->firstWhere('id', $current->id);

        $this->assertNotNull($currentItem);
        $this->assertTrue($currentItem->isCurrentPeriod);
    }
}
