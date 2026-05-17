<?php

namespace App\Services\Rent;

use App\Models\Tenant;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Rent due-date calculations based on a tenant's monthly due day.
 */
class RentScheduleService
{
    public function dueDateForMonth(Tenant $tenant, int $year, int $month): Carbon
    {
        $day = min($tenant->rent_due_day, $this->daysInMonth($year, $month));

        return Carbon::create($year, $month, $day)->startOfDay();
    }

    public function nextDueDate(Tenant $tenant, ?CarbonInterface $from = null): Carbon
    {
        $from = Carbon::parse($from ?? now())->startOfDay();
        $candidate = $this->dueDateForMonth($tenant, $from->year, $from->month);

        if ($candidate->lt($from)) {
            $next = $from->copy()->addMonthNoOverflow();

            return $this->dueDateForMonth($tenant, $next->year, $next->month);
        }

        return $candidate;
    }

    /**
     * @return Collection<int, Carbon>
     */
    public function upcomingDueDates(Tenant $tenant, int $months = 3, ?CarbonInterface $from = null): Collection
    {
        $dates = collect();
        $cursor = $this->nextDueDate($tenant, $from);

        for ($i = 0; $i < $months; $i++) {
            $dates->push($cursor->copy());
            $next = $cursor->copy()->addMonthNoOverflow();
            $cursor = $this->dueDateForMonth($tenant, $next->year, $next->month);
        }

        return $dates;
    }

    /**
     * Suggested amount for a scheduled rent period.
     */
    public function scheduledAmount(Tenant $tenant): float
    {
        return (float) $tenant->rent_amount;
    }

    protected function daysInMonth(int $year, int $month): int
    {
        return Carbon::create($year, $month, 1)->daysInMonth;
    }
}
