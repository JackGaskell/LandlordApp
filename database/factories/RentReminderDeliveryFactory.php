<?php

namespace Database\Factories;

use App\Enums\ReminderChannel;
use App\Enums\ReminderDeliveryStatus;
use App\Enums\ReminderType;
use App\Models\PaymentHistory;
use App\Models\RentReminderDelivery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentReminderDelivery>
 */
class RentReminderDeliveryFactory extends Factory
{
    protected $model = RentReminderDelivery::class;

    public function definition(): array
    {
        $payment = PaymentHistory::factory()->create();

        return [
            'payment_history_id' => $payment->id,
            'tenant_id' => $payment->tenant_id,
            'landlord_user_id' => $payment->tenant->user_id,
            'reminder_type' => ReminderType::BeforeDue,
            'days_offset' => 7,
            'channel' => ReminderChannel::Email,
            'dispatch_date' => now()->toDateString(),
            'status' => ReminderDeliveryStatus::Pending,
        ];
    }
}
