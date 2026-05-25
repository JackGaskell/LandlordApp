<?php

namespace Tests\Feature\Mail;

use App\Enums\ReminderType;
use App\Mail\Reminders\RentDueReminderMail;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentDueReminderMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_rent_due_reminder_mail_renders(): void
    {
        $landlord = User::factory()->create([
            'first_name' => 'Alex',
            'last_name' => 'Landlord',
        ]);
        $tenant = Tenant::factory()->for($landlord)->create([
            'name' => 'Jamie Tenant',
            'email' => 'jamie@example.com',
        ]);
        $payment = PaymentHistory::factory()->for($tenant)->create([
            'amount' => 1200,
            'due_date' => now()->addDays(3),
        ]);

        $mailable = new RentDueReminderMail(
            payment: $payment->load('tenant.landlord'),
            reminderType: ReminderType::BeforeDue,
            daysOffset: 3,
            tenant: $tenant,
        );

        $html = $mailable->render();

        $this->assertStringContainsString('Jamie Tenant', $html);
        $this->assertStringContainsString('1,200.00', $html);
        $this->assertStringContainsString('Rent payment reminder', $html);
    }

    public function test_due_day_reminder_includes_score_and_portal_cta(): void
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->for($landlord)->create([
            'name' => 'Jamie Tenant',
            'email' => 'jamie@example.com',
        ]);

        PaymentHistory::factory()->paid()->for($tenant)->create([
            'due_date' => now()->subMonth(),
            'paid_at' => now()->subMonth()->addDay(),
        ]);

        $payment = PaymentHistory::factory()->for($tenant)->create([
            'amount' => 950,
            'due_date' => now()->startOfDay(),
        ]);

        $mailable = new RentDueReminderMail(
            payment: $payment->load('tenant.landlord'),
            reminderType: ReminderType::BeforeDue,
            daysOffset: 0,
            tenant: $tenant,
        );

        $html = $mailable->render();

        $this->assertStringContainsString('Rent due today', $html);
        $this->assertStringContainsString('Your tenant score', $html);
        $this->assertStringContainsString('Confirm payment in your portal', $html);
        $this->assertStringStartsWith('Rent due today', $mailable->envelope()->subject);
    }
}
