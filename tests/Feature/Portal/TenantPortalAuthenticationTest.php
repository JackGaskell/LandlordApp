<?php

namespace Tests\Feature\Portal;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantPortalAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_log_in_to_portal(): void
    {
        $tenant = Tenant::factory()->create([
            'password' => Hash::make('password'),
            'portal_enabled_at' => now(),
        ]);

        $response = $this->post(route('portal.login'), [
            'email' => $tenant->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($tenant, 'tenant');
    }

    public function test_tenant_without_portal_access_cannot_log_in(): void
    {
        $tenant = Tenant::factory()->create([
            'password' => Hash::make('password'),
            'portal_enabled_at' => null,
        ]);

        $this->post(route('portal.login'), [
            'email' => $tenant->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('tenant');
    }

    public function test_landlord_can_enable_tenant_portal_and_receive_invite_url(): void
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->for($landlord)->create();

        $response = $this->actingAs($landlord)
            ->post(route('tenants.portal.store', $tenant));

        $response->assertRedirect(route('tenants.show', $tenant));
        $response->assertSessionHas('portal_invite_url');

        $tenant->refresh();
        $this->assertNotNull($tenant->portal_enabled_at);
        $this->assertNotNull($tenant->portal_invite_token);
    }

    public function test_tenant_can_complete_invite_and_access_dashboard(): void
    {
        $tenant = Tenant::factory()->create([
            'password' => null,
            'portal_enabled_at' => now(),
        ]);

        $plainToken = $tenant->issuePortalInvite();

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'portal.invite.show',
            now()->addHour(),
            ['tenant' => $tenant->id, 'token' => $plainToken],
        );

        $this->get($url)->assertOk();

        $this->post($url, [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('portal.dashboard'));

        $this->assertAuthenticatedAs($tenant->fresh(), 'tenant');
    }
}
