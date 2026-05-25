<?php

namespace Tests\Feature\Dashboard;

use App\Enums\PaymentStatus;
use App\Models\PaymentHistory;
use App\Models\PaymentProof;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Dashboard\CollectionHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardQueryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_snapshot_uses_a_bounded_number_of_queries(): void
    {
        $landlord = User::factory()->create();

        $tenants = Tenant::factory()
            ->count(8)
            ->for($landlord)
            ->create();

        foreach ($tenants as $tenant) {
            PaymentHistory::factory()->for($tenant)->create([
                'status' => PaymentStatus::Overdue,
                'due_date' => now()->subDays(3),
            ]);
            PaymentHistory::factory()->for($tenant)->create([
                'status' => PaymentStatus::DueSoon,
                'due_date' => now()->addDays(2),
            ]);
            PaymentHistory::factory()->for($tenant)->create([
                'status' => PaymentStatus::Paid,
                'due_date' => now()->startOfMonth(),
                'paid_at' => now(),
            ]);
        }

        $queries = 0;

        DB::listen(function () use (&$queries) {
            $queries++;
        });

        app(CollectionHealthService::class)->snapshot($landlord);

        // aggregates + lists + activity + pending confirmations
        $this->assertLessThanOrEqual(12, $queries, "Expected ≤12 queries, ran {$queries}");
    }

    public function test_dashboard_shows_needs_attention_when_confirmations_pending(): void
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->for($landlord)->create(['name' => 'Alex Renter']);
        $payment = PaymentHistory::factory()->dueSoon()->for($tenant)->create();

        PaymentProof::factory()->for($tenant)->for($payment)->create();

        $this->actingAs($landlord)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Needs your attention', false)
            ->assertSee('Payment confirmations', false)
            ->assertSee('Alex Renter', false)
            ->assertSee('Review', false);
    }

    public function test_dashboard_page_renders_for_authenticated_landlord(): void
    {
        $landlord = User::factory()->create();

        $this->actingAs($landlord)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Collection health this month');
    }
}
