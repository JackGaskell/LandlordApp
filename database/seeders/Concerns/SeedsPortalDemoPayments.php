<?php

namespace Database\Seeders\Concerns;

use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Payments\PaymentTrackingService;

trait SeedsPortalDemoPayments
{
    protected function seedPortalDemoTenants(User $landlord, PaymentTrackingService $tracking): void
    {
        $this->seedPortalTenant(
            $landlord,
            $tracking,
            name: 'Jamie Taylor',
            email: 'jamie.tenant@landlordapp.test',
            rentAmount: 950.00,
            scenario: 'upcoming',
        );

        $this->seedPortalTenant(
            $landlord,
            $tracking,
            name: 'Casey Morgan',
            email: 'casey.overdue@landlordapp.test',
            rentAmount: 1100.00,
            scenario: 'overdue',
        );

        $this->seedPortalTenant(
            $landlord,
            $tracking,
            name: 'Riley Chen',
            email: 'riley.ontrack@landlordapp.test',
            rentAmount: 875.00,
            scenario: 'on_track',
        );
    }

    protected function seedPortalTenant(
        User $landlord,
        PaymentTrackingService $tracking,
        string $name,
        string $email,
        float $rentAmount,
        string $scenario,
    ): void {
        $dueDay = max(1, min(28, (int) now()->day));

        $tenant = Tenant::factory()
            ->for($landlord)
            ->create([
                'name' => $name,
                'email' => $email,
                'rent_amount' => $rentAmount,
                'rent_due_day' => $dueDay,
                'password' => 'password',
                'portal_enabled_at' => now(),
            ]);

        $historyMonths = [4, 3, 2, 1];

        foreach ($historyMonths as $monthsAgo) {
            $payment = PaymentHistory::factory()
                ->paid()
                ->for($tenant)
                ->create([
                    'amount' => $rentAmount,
                    'due_date' => now()->subMonths($monthsAgo)->day(min($dueDay, 28)),
                    'paid_at' => now()->subMonths($monthsAgo)->day(min($dueDay, 28))->addDay(),
                ]);

            $tracking->sync($payment);
        }

        $current = match ($scenario) {
            'upcoming' => PaymentHistory::factory()
                ->for($tenant)
                ->create([
                    'amount' => $rentAmount,
                    'due_date' => now()->addDays(5)->startOfDay(),
                    'paid_at' => null,
                    'status' => \App\Enums\PaymentStatus::DueSoon,
                ]),
            'overdue' => PaymentHistory::factory()
                ->for($tenant)
                ->create([
                    'amount' => $rentAmount,
                    'due_date' => now()->subDays(4)->startOfDay(),
                    'paid_at' => null,
                    'status' => \App\Enums\PaymentStatus::Overdue,
                ]),
            'on_track' => PaymentHistory::factory()
                ->paid()
                ->for($tenant)
                ->create([
                    'amount' => $rentAmount,
                    'due_date' => now()->startOfMonth()->day(min($dueDay, 28)),
                    'paid_at' => now()->subDays(2),
                ]),
            default => null,
        };

        if ($current) {
            $tracking->sync($current);
        }
    }
}
