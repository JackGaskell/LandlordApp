<?php

namespace App\Enums;

/**
 * Tenant-facing collection state (supportive tone, not punitive).
 */
enum TenantCollectionStatus: string
{
    case OnTrack = 'on_track';
    case Upcoming = 'upcoming';
    case ActionNeeded = 'action_needed';
    case PartialProgress = 'partial_progress';
    case Clear = 'clear';

    public function label(): string
    {
        return match ($this) {
            self::OnTrack => 'On track',
            self::Upcoming => 'Upcoming',
            self::ActionNeeded => 'Needs attention',
            self::PartialProgress => 'In progress',
            self::Clear => 'All clear',
        };
    }

    public function headline(): string
    {
        return match ($this) {
            self::OnTrack => 'You are in great shape',
            self::Upcoming => 'Rent is on the horizon',
            self::ActionNeeded => 'Let us get this sorted',
            self::PartialProgress => 'Good progress so far',
            self::Clear => 'Nothing due right now',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::OnTrack, self::Clear => 'success',
            self::Upcoming => 'brand',
            self::PartialProgress => 'warning',
            self::ActionNeeded => 'warning',
        };
    }
}
