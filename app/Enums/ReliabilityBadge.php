<?php

namespace App\Enums;

enum ReliabilityBadge: string
{
    case New = 'new';
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
    case Platinum = 'platinum';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Getting started',
            self::Bronze => 'Building momentum',
            self::Silver => 'Consistent payer',
            self::Gold => 'Highly reliable',
            self::Platinum => 'Elite consistency',
        };
    }

    public static function forScore(float $score, int $trackedPeriods): self
    {
        if ($trackedPeriods === 0) {
            return self::New;
        }

        return match (true) {
            $score >= 95 => self::Platinum,
            $score >= 85 => self::Gold,
            $score >= 70 => self::Silver,
            $score >= 50 => self::Bronze,
            default => self::Bronze,
        };
    }
}
