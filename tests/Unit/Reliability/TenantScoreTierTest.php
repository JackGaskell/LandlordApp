<?php

namespace Tests\Unit\Reliability;

use App\DataTransferObjects\Reliability\TenantReliabilityProfile;
use App\Enums\ReliabilityBadge;
use App\Enums\TenantScoreTier;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Portal\TenantPortalDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
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

    public function test_portal_score_ranges_match_tier_thresholds(): void
    {
        $this->assertSame('0–39', TenantScoreTier::NeedsAttention->scoreRangeLabel());
        $this->assertSame('40–59', TenantScoreTier::Improving->scoreRangeLabel());
        $this->assertSame('60–74', TenantScoreTier::Reliable->scoreRangeLabel());
        $this->assertSame('75–89', TenantScoreTier::Trusted->scoreRangeLabel());
        $this->assertSame('90–100', TenantScoreTier::Excellent->scoreRangeLabel());
    }

    public function test_untracked_profile_shows_new_score_state_in_portal(): void
    {
        $tenant = Tenant::factory()->create(['portal_enabled_at' => now()]);

        $snapshot = app(TenantPortalDashboardService::class)->snapshot($tenant);
        $profile = $snapshot->reliability;

        $this->assertDatabaseHas('payment_histories', ['tenant_id' => $tenant->id]);
        $this->assertFalse($profile->portalScoreIsEstablished());
        $this->assertSame('New', $profile->portalScoreDisplay());
        $this->assertSame('Not started yet', $profile->portalScoreSubtitle());
        $this->assertSame(0, $profile->portalScoreProgressPercent());
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

    public function test_portal_behavioural_messages_for_established_tenant(): void
    {
        $tenant = Tenant::factory()->create(['portal_enabled_at' => now()]);

        foreach ([4, 3, 2, 1] as $monthsAgo) {
            PaymentHistory::factory()->paid()->for($tenant)->create([
                'due_date' => now()->subMonths($monthsAgo)->day(15),
                'paid_at' => now()->subMonths($monthsAgo)->day(16),
            ]);
        }

        PaymentHistory::factory()->dueSoon()->for($tenant)->create([
            'due_date' => now()->addDays(5),
        ]);

        $profile = app(TenantPortalDashboardService::class)->snapshot($tenant)->reliability;

        $this->assertSame('Your next on-time payment keeps your streak alive.', $profile->portalScoreImpactMessage());
        $this->assertSame('Your next on-time payment keeps this streak alive.', $profile->portalStreakProtectionMessage());
        $this->assertStringContainsString('On-time payment:', $profile->portalProjectedScoreOnTimeLabel());
        $this->assertStringContainsString('rental record', $profile->portalProjectedScoreLateLabel());
    }

    public function test_portal_milestone_nudge_when_close_to_next_tier(): void
    {
        $profile = $this->makeProfile(score: 55, trackedPeriods: 4, currentStreak: 2);

        $this->assertTrue($profile->portalIsNearNextTier());
        $this->assertSame('One more on-time payment helps you reach Reliable.', $profile->portalMilestoneNudgeMessage());
    }

    public function test_portal_maintain_message_at_top_tier_without_streak(): void
    {
        $profile = $this->makeProfile(score: 100, trackedPeriods: 8, currentStreak: 0);

        $this->assertSame(
            'Paying on time protects your Excellent status.',
            $profile->portalScoreImpactMessage(),
        );
        $this->assertSame(
            'Paying before the due date protects your Excellent status.',
            $profile->portalPaymentProtectionMessage(),
        );
        $this->assertSame('On-time payment: score stays at 100', $profile->portalProjectedScoreOnTimeLabel());
    }

    protected function makeProfile(float $score, int $trackedPeriods, int $currentStreak): TenantReliabilityProfile
    {
        return new TenantReliabilityProfile(
            tenantId: 1,
            tenantName: 'Jamie Taylor',
            score: $score,
            badge: ReliabilityBadge::forScore($score, $trackedPeriods),
            currentStreak: $currentStreak,
            bestStreak: max($currentStreak, 1),
            totalOnTime: $trackedPeriods,
            lateCount: 0,
            missedCount: 0,
            partialCount: 0,
            trackedPeriods: $trackedPeriods,
            consistencyRate: 100,
            consistencyWindowMonths: 12,
            timeline: collect(),
        );
    }
}
