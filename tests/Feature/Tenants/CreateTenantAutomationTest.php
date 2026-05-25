<?php

namespace Tests\Feature\Tenants;

use App\Enums\TenantStatus;
use App\Models\LandlordSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTenantAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_landlord_can_add_tenant_with_minimal_fields(): void
    {
        $landlord = User::factory()->create();

        $response = $this->actingAs($landlord)
            ->post(route('tenants.store'), [
                'name' => 'Jamie Taylor',
                'email' => 'jamie@example.com',
                'rent_amount' => 950,
                'rent_due_day' => 5,
                'property_label' => '12 High Street',
            ]);

        $tenant = Tenant::query()->where('email', 'jamie@example.com')->first();

        $response
            ->assertRedirect(route('tenants.show', $tenant))
            ->assertSessionHas('status')
            ->assertSessionHas('portal_invite_url');

        $this->assertSame(TenantStatus::Active, $tenant->status);
        $this->assertSame('12 High Street', $tenant->property_label);
        $this->assertDatabaseHas('payment_histories', [
            'tenant_id' => $tenant->id,
            'amount' => 950,
        ]);
        $this->assertNotNull($tenant->portal_enabled_at);
    }

    public function test_mark_paid_opens_next_rent_period(): void
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->for($landlord)->create(['rent_due_day' => 10]);

        $payment = \App\Models\PaymentHistory::factory()->dueSoon()->for($tenant)->create([
            'due_date' => now()->subMonth()->day(10),
            'amount' => $tenant->rent_amount,
        ]);

        $this->actingAs($landlord)
            ->post(route('payments.mark-paid', $payment))
            ->assertRedirect(route('tenants.show', $tenant));

        $this->assertDatabaseCount('payment_histories', 2);
        $this->assertTrue(
            \App\Models\PaymentHistory::query()
                ->where('tenant_id', $tenant->id)
                ->whereNull('paid_at')
                ->exists(),
        );
    }
}
