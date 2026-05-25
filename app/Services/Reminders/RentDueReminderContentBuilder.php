<?php

namespace App\Services\Reminders;

use App\Enums\ReminderType;
use App\Models\Tenant;
use App\Services\Reliability\TenantReliabilityProfileService;

class RentDueReminderContentBuilder
{
    public function __construct(
        protected TenantReliabilityProfileService $reliability,
    ) {}

    /**
     * @return array{
     *     isDueDay: bool,
     *     showScoreBlock: bool,
     *     scoreDisplay: string,
     *     tierLabel: string,
     *     primaryNudge: ?string,
     *     secondaryLines: array<int, string>,
     *     portalUrl: string,
     *     confirmPaymentCta: string,
     * }
     */
    public function forTenant(Tenant $tenant, ReminderType $reminderType, int $daysOffset): array
    {
        $isDueDay = $reminderType === ReminderType::BeforeDue && $daysOffset === 0;
        $profile = $this->reliability->profile($tenant);

        $primaryNudge = $profile->portalPaymentProtectionMessage()
            ?? $profile->portalScoreImpactMessage();

        $secondaryLines = $isDueDay
            ? array_values(array_filter([
                $profile->portalProjectedScoreOnTimeLabel(),
                $profile->portalProjectedScoreLateLabel(),
                $profile->portalMilestoneNudgeMessage(),
                $profile->portalStreakProtectionMessage(),
            ]))
            : [];

        return [
            'isDueDay' => $isDueDay,
            'showScoreBlock' => $isDueDay && filled($primaryNudge),
            'scoreDisplay' => $profile->portalScoreDisplay(),
            'tierLabel' => $profile->portalScoreIsEstablished()
                ? $profile->scoreTier()->scaleLabel()
                : 'Starting',
            'primaryNudge' => $primaryNudge,
            'secondaryLines' => $secondaryLines,
            'portalUrl' => route('portal.login'),
            'confirmPaymentCta' => 'Confirm payment in your portal',
        ];
    }
}
