<?php

namespace Tests\Feature\Reminders;

use App\Enums\PaymentStatus;
use App\Enums\ReminderDeliveryStatus;
use App\Enums\ReminderType;
use App\Jobs\Reminders\SendRentReminderJob;
use App\Models\LandlordSetting;
use App\Models\PaymentHistory;
use App\Models\RentReminderDelivery;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\RentDueReminderNotification;
use App\Services\Reminders\ReminderDispatchService;
use App\Services\Reminders\ReminderEligibilityService;
use App\Services\Reminders\ReminderSendService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RentReminderEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_before_due_reminder_when_due_date_matches_schedule(): void
    {
        Queue::fake();

        $landlord = User::factory()->create();
        LandlordSetting::factory()->for($landlord)->create([
            'reminder_days_before' => [7],
            'overdue_reminder_days' => [1],
            'email_reminders_enabled' => true,
        ]);

        $tenant = Tenant::factory()->for($landlord)->create();
        $payment = PaymentHistory::factory()->for($tenant)->create([
            'due_date' => now()->addDays(7),
            'status' => PaymentStatus::DueSoon,
        ]);

        $result = app(ReminderDispatchService::class)->dispatchDueReminders($landlord);

        $this->assertSame(1, $result->queued);
        $this->assertDatabaseHas('rent_reminder_deliveries', [
            'payment_history_id' => $payment->id,
            'reminder_type' => ReminderType::BeforeDue->value,
            'days_offset' => 7,
            'status' => ReminderDeliveryStatus::Pending->value,
        ]);

        Queue::assertPushed(SendRentReminderJob::class);
    }

    public function test_dispatches_overdue_reminder_on_configured_day(): void
    {
        Queue::fake();

        $landlord = User::factory()->create();
        LandlordSetting::factory()->for($landlord)->create([
            'reminder_days_before' => [7],
            'overdue_reminder_days' => [3],
            'email_reminders_enabled' => true,
        ]);

        $tenant = Tenant::factory()->for($landlord)->create();
        PaymentHistory::factory()->for($tenant)->overdue()->create([
            'due_date' => now()->subDays(3),
        ]);

        $result = app(ReminderDispatchService::class)->dispatchDueReminders($landlord);

        $this->assertSame(1, $result->queued);
        $this->assertDatabaseHas('rent_reminder_deliveries', [
            'reminder_type' => ReminderType::AfterDue->value,
            'days_offset' => 3,
        ]);
    }

    public function test_does_not_dispatch_duplicate_reminders_on_same_day(): void
    {
        Queue::fake();

        $landlord = User::factory()->create();
        LandlordSetting::factory()->for($landlord)->create([
            'reminder_days_before' => [7],
            'overdue_reminder_days' => [1],
        ]);

        $tenant = Tenant::factory()->for($landlord)->create();
        PaymentHistory::factory()->for($tenant)->create([
            'due_date' => now()->addDays(7),
            'status' => PaymentStatus::DueSoon,
        ]);

        $dispatcher = app(ReminderDispatchService::class);
        $first = $dispatcher->dispatchDueReminders($landlord);
        $second = $dispatcher->dispatchDueReminders($landlord);

        $this->assertSame(1, $first->queued);
        $this->assertSame(0, $second->queued);
        $this->assertSame(1, $second->skippedDuplicate);
        Queue::assertPushed(SendRentReminderJob::class, 1);
    }

    public function test_skips_before_due_reminders_for_overdue_payments(): void
    {
        Queue::fake();

        $landlord = User::factory()->create();
        LandlordSetting::factory()->for($landlord)->create([
            'reminder_days_before' => [1],
            'overdue_reminder_days' => [7],
        ]);

        $tenant = Tenant::factory()->for($landlord)->create();
        PaymentHistory::factory()->for($tenant)->create([
            'due_date' => now()->addDay(),
            'status' => PaymentStatus::Overdue,
        ]);

        $result = app(ReminderDispatchService::class)->dispatchDueReminders($landlord);

        $this->assertSame(0, $result->queued);
        $this->assertSame(1, $result->skippedIneligible);
        Queue::assertNothingPushed();
    }

    public function test_respects_disabled_email_reminders(): void
    {
        Queue::fake();

        $landlord = User::factory()->create();
        LandlordSetting::factory()->for($landlord)->create([
            'reminder_days_before' => [7],
            'overdue_reminder_days' => [1],
            'email_reminders_enabled' => false,
        ]);

        $tenant = Tenant::factory()->for($landlord)->create();
        PaymentHistory::factory()->for($tenant)->create([
            'due_date' => now()->addDays(7),
            'status' => PaymentStatus::DueSoon,
        ]);

        $result = app(ReminderDispatchService::class)->dispatchDueReminders($landlord);

        $this->assertSame(0, $result->queued);
        Queue::assertNothingPushed();
    }

    public function test_send_service_delivers_email_and_marks_delivery_sent(): void
    {
        Notification::fake();

        $landlord = User::factory()->create();
        LandlordSetting::factory()->for($landlord)->create();

        $tenant = Tenant::factory()->for($landlord)->create(['email' => 'tenant@example.com']);
        $payment = PaymentHistory::factory()->for($tenant)->create([
            'due_date' => now()->addDays(7),
            'status' => PaymentStatus::DueSoon,
        ]);

        $delivery = RentReminderDelivery::factory()->create([
            'payment_history_id' => $payment->id,
            'tenant_id' => $tenant->id,
            'landlord_user_id' => $landlord->id,
            'reminder_type' => ReminderType::BeforeDue,
            'days_offset' => 7,
            'status' => ReminderDeliveryStatus::Pending,
        ]);

        app(ReminderSendService::class)->send($delivery->fresh());

        Notification::assertSentTo($tenant, RentDueReminderNotification::class);

        $this->assertSame(
            ReminderDeliveryStatus::Sent,
            $delivery->fresh()->status,
        );
        $this->assertNotNull($delivery->fresh()->sent_at);
    }

    public function test_artisan_command_reports_dispatch_summary(): void
    {
        Queue::fake();

        $landlord = User::factory()->create();
        LandlordSetting::factory()->for($landlord)->create([
            'reminder_days_before' => [7],
            'overdue_reminder_days' => [1],
        ]);

        $tenant = Tenant::factory()->for($landlord)->create();
        PaymentHistory::factory()->for($tenant)->create([
            'due_date' => now()->addDays(7),
            'status' => PaymentStatus::DueSoon,
        ]);

        Artisan::call('rent:dispatch-reminders');

        $this->assertStringContainsString('Reminders queued: 1', Artisan::output());
    }

    public function test_eligibility_service_matches_payment_status_to_reminder_type(): void
    {
        $eligibility = app(ReminderEligibilityService::class);

        $dueSoon = PaymentHistory::factory()->make(['status' => PaymentStatus::DueSoon]);
        $overdue = PaymentHistory::factory()->make(['status' => PaymentStatus::Overdue]);

        $this->assertTrue($eligibility->paymentQualifiesForType($dueSoon, ReminderType::BeforeDue));
        $this->assertFalse($eligibility->paymentQualifiesForType($overdue, ReminderType::BeforeDue));
        $this->assertTrue($eligibility->paymentQualifiesForType($overdue, ReminderType::AfterDue));
    }
}
