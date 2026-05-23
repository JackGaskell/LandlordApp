<?php

namespace Database\Seeders;

use App\Enums\TenantStatus;
use App\Models\LandlordSetting;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Payments\PaymentTrackingService;
use Database\Seeders\Concerns\SeedsPortalDemoPayments;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LandlordDemoSeeder extends Seeder
{
    use SeedsPortalDemoPayments;
    public function run(): void
    {
        $landlords = [
            ['first_name' => 'Alex', 'last_name' => 'Morgan', 'email' => 'alex@landlordapp.test'],
            ['first_name' => 'Sam', 'last_name' => 'Patel', 'email' => 'sam@landlordapp.test'],
            ['first_name' => 'Jordan', 'last_name' => 'Lee', 'email' => 'jordan@landlordapp.test'],
        ];

        foreach ($landlords as $landlordData) {
            $landlord = User::factory()->create([
                'first_name' => $landlordData['first_name'],
                'last_name' => $landlordData['last_name'],
                'email' => $landlordData['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            LandlordSetting::factory()->create([
                'user_id' => $landlord->id,
                'reminder_days_before' => [7, 3, 1],
                'overdue_reminder_days' => [1, 3, 7],
                'email_reminders_enabled' => true,
            ]);

            $this->seedTenantsForLandlord($landlord);
        }
    }

    protected function seedTenantsForLandlord(User $landlord): void
    {
        $scenarios = [
            ['label' => 'overdue', 'count' => 2, 'paymentState' => 'overdue'],
            ['label' => 'due_soon', 'count' => 2, 'paymentState' => 'dueSoon'],
            ['label' => 'paid', 'count' => 3, 'paymentState' => 'paid'],
        ];

        foreach ($scenarios as $scenario) {
            Tenant::factory()
                ->count($scenario['count'])
                ->for($landlord)
                ->create(['status' => TenantStatus::Active])
                ->each(function (Tenant $tenant) use ($scenario) {
                    PaymentHistory::factory()
                        ->{$scenario['paymentState']}()
                        ->for($tenant)
                        ->create([
                            'amount' => $tenant->rent_amount,
                        ]);

                    // Prior months: mostly paid history for realism.
                    PaymentHistory::factory()
                        ->paid()
                        ->for($tenant)
                        ->create([
                            'amount' => $tenant->rent_amount,
                            'due_date' => now()->subMonth()->day(min($tenant->rent_due_day, 28)),
                        ]);

                    if ($scenario['paymentState'] === 'paid') {
                        PaymentHistory::factory()
                            ->paid()
                            ->for($tenant)
                            ->create([
                                'amount' => $tenant->rent_amount,
                                'due_date' => now()->startOfMonth()->day(min($tenant->rent_due_day, 28)),
                            ]);
                    }
                });
        }

        // One inactive tenant with no open rent.
        $inactive = Tenant::factory()
            ->inactive()
            ->for($landlord)
            ->create();

        PaymentHistory::factory()
            ->paid()
            ->for($inactive)
            ->create(['amount' => $inactive->rent_amount, 'due_date' => now()->subMonth()]);

        if ($landlord->email === 'alex@landlordapp.test') {
            $this->seedPortalDemoTenants($landlord, app(PaymentTrackingService::class));
        }
    }
}
