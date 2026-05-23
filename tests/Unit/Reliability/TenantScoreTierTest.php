<?php

namespace Tests\Unit\Reliability;

use App\Enums\TenantScoreTier;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Portal\TenantPortalDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantScoreTierTest extends TestCase
{
    use RefreshDatabase;

    public function test_tier_thresholds(): void
    {
        $this->assertSame(TenantScoreTier::Excellent, TenantScoreTier::forScore(95, 3));
        $this->assertSame(TenantScoreTier::Trusted, TenantScoreTier::forScore(80, 3));
        $this->assertSame(TenantScoreTier::Reliable, TenantScoreTier::forScore(65, 3));
        $this->assertSame(TenantScoreTier::Improving, TenantScoreTier::forScore(45, 3));
        $this->assertSame(TenantScoreTier::NeedsAttention, TenantScoreTier::forScore(30, 3));
    }

    public function test_untracked_tenant_starts_in_improving_tier(): void
    {
        $this->assertSame(TenantScoreTier::Improving, TenantScoreTier::forScore(100, 0));
    }

    public function test_profile_exposes_portal_score_guidance(): void
    {
        $tenant = Tenant::factory()->create(['portal_enabled_at' => now()]);

        PaymentHistory::factory()->paid()->for($tenant)->create([
            'due_date' => now()->subMonth(),
            'paid_at' => now()->subMonth()->addDay(),
        ]);

        $profile = app(TenantPortalDashboardService::class)->snapshot($tenant)->reliability;

        $this->assertInstanceOf(TenantScoreTier::class, $profile->scoreTier());
        $this->assertNotEmpty($profile->portalMaintainActions());
        $this->assertCount(4, $profile->portalAchievements());
        $this->assertCount(3, $profile->portalScoreStats());
    }
}
