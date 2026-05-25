<?php

namespace App\Services\Tenants;

use App\DataTransferObjects\Reliability\TenantReliabilityProfile;
use App\DataTransferObjects\Tenants\TenantReliabilityScore;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Reliability\TenantReliabilityProfileService;
use Illuminate\Support\Collection;

/**
 * Landlord-facing reliability API (delegates to the reliability engine).
 */
class TenantReliabilityService
{
    public function __construct(
        protected TenantReliabilityProfileService $profiles,
    ) {}

    public function profile(Tenant $tenant): TenantReliabilityProfile
    {
        return $this->profiles->profile($tenant);
    }

    public function score(Tenant $tenant): TenantReliabilityScore
    {
        return $this->profile($tenant)->toLegacyScore();
    }

    /**
     * @return Collection<int, TenantReliabilityScore>
     */
    public function scoresForLandlord(User $landlord): Collection
    {
        return $this->profiles->profilesForLandlord($landlord)
            ->map(fn ($profile) => $profile->toLegacyScore());
    }
}
