<?php

namespace Tests\Feature\Rent;

use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RunRentCollectionCycleCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_syncs_periods_for_active_tenants(): void
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->for($landlord)->create();

        Artisan::call('rent:run-collection-cycle');

        $this->assertDatabaseHas('payment_histories', [
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_command_scoped_to_landlord(): void
    {
        $landlord = User::factory()->create();
        $other = User::factory()->create();

        Tenant::factory()->for($landlord)->create();
        Tenant::factory()->for($other)->create();

        Artisan::call('rent:run-collection-cycle', ['--landlord' => $landlord->id]);

        $this->assertSame(1, PaymentHistory::query()->count());
    }
}
