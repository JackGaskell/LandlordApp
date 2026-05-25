<?php

namespace Tests\Unit\Rent;

use App\Enums\PaymentStatus;
use App\Enums\TenantStatus;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Rent\RentPeriodAutomationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentPeriodAutomationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintain_creates_first_period_for_new_active_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        app(RentPeriodAutomationService::class)->maintainTenantSchedule($tenant);

        $this->assertDatabaseCount('payment_histories', 1);
        $this->assertDatabaseHas('payment_histories', [
            'tenant_id' => $tenant->id,
            'amount' => $tenant->rent_amount,
        ]);
    }

    public function test_maintain_does_not_duplicate_open_period(): void
    {
        $tenant = Tenant::factory()->create();
        PaymentHistory::factory()->dueSoon()->for($tenant)->create([
            'amount' => $tenant->rent_amount,
        ]);

        app(RentPeriodAutomationService::class)->maintainTenantSchedule($tenant);

        $this->assertDatabaseCount('payment_histories', 1);
    }

    public function test_advance_after_settlement_opens_next_period(): void
    {
        $tenant = Tenant::factory()->create(['rent_due_day' => 1]);

        $paid = PaymentHistory::factory()->paid()->for($tenant)->create([
            'due_date' => now()->subMonth()->day(1),
            'amount' => $tenant->rent_amount,
        ]);

        app(RentPeriodAutomationService::class)->advanceAfterPeriodSettled($paid);

        $this->assertDatabaseCount('payment_histories', 2);

        $open = PaymentHistory::query()
            ->where('tenant_id', $tenant->id)
            ->whereNull('paid_at')
            ->first();

        $this->assertNotNull($open);
        $this->assertTrue($open->due_date->gt($paid->due_date));
    }

    public function test_inactive_tenant_skips_automation(): void
    {
        $tenant = Tenant::factory()->inactive()->create();

        $this->assertFalse(app(RentPeriodAutomationService::class)->maintainTenantSchedule($tenant));
        $this->assertDatabaseCount('payment_histories', 0);
    }

    public function test_run_for_landlord_processes_active_tenants_only(): void
    {
        $landlord = User::factory()->create();
        $active = Tenant::factory()->for($landlord)->create();
        Tenant::factory()->for($landlord)->inactive()->create();

        $count = app(RentPeriodAutomationService::class)->runForLandlord($landlord);

        $this->assertSame(1, $count);
        $this->assertDatabaseCount('payment_histories', 1);
        $this->assertDatabaseHas('payment_histories', ['tenant_id' => $active->id]);
    }
}
