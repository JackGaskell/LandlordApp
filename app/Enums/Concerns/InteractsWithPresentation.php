<?php

namespace App\Enums\Concerns;

trait InteractsWithPresentation
{
    /**
     * @return array<string, string> value => label (for selects)
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
