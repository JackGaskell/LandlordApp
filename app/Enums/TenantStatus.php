<?php

namespace App\Enums;

use App\Enums\Concerns\InteractsWithPresentation;

/**
 * Stored on tenants.status — whether the landlord is actively tracking this tenant.
 */
enum TenantStatus: string
{
    use InteractsWithPresentation;

    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Archived => 'Archived',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Active => 'bg-emerald-100 text-emerald-800',
            self::Inactive => 'bg-zinc-100 text-zinc-700',
            self::Archived => 'bg-slate-100 text-slate-700',
        };
    }
}
