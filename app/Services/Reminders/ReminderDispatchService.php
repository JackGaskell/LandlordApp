<?php

namespace App\Services\Reminders;

use App\DataTransferObjects\Reminders\ReminderDispatchResult;
use App\Enums\PaymentStatus;
use App\Enums\ReminderChannel;
use App\Jobs\Reminders\SendRentReminderJob;
use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Finds payments that need a reminder today and queues SendRentReminderJob.
 */
class ReminderDispatchService
{
    public function __construct(
        protected ReminderScheduler $scheduler,
        protected ReminderEligibilityService $eligibility,
        protected RentReminderDeliveryService $deliveries,
        protected ReminderChannelRegistry $channels,
    ) {}

    public function dispatchDueReminders(
        ?User $landlord = null,
        bool $dryRun = false,
        ?Carbon $dispatchDate = null,
    ): ReminderDispatchResult {
        $dispatchDate ??= now();
        $queued = 0;
        $skippedDuplicate = 0;
        $skippedIneligible = 0;
        $skippedDisabled = 0;

        $landlords = $landlord
            ? collect([$landlord->load('landlordSetting')])
            : User::query()->whereHas('landlordSetting')->with('landlordSetting')->get();

        foreach ($landlords as $user) {
            $settings = $user->landlordSetting;

            if (! $this->eligibility->landlordRemindersEnabled($settings)) {
                continue;
            }

            $schedule = $this->scheduler->scheduleForSettings($settings);

            foreach ($this->openPaymentsForLandlord($user) as $payment) {
                foreach ($schedule as $entry) {
                    if (! $this->eligibility->shouldSendOnDate($payment, $entry->signedOffset, $dispatchDate)) {
                        continue;
                    }

                    if (! $this->eligibility->paymentQualifiesForType($payment, $entry->type)) {
                        $skippedIneligible++;

                        continue;
                    }

                    foreach ($this->channels->enabledChannels() as $channel) {
                        if ($channel === ReminderChannel::Email && ! $settings->email_reminders_enabled) {
                            $skippedDisabled++;

                            continue;
                        }

                        if ($dryRun) {
                            $queued++;

                            continue;
                        }

                        $delivery = $this->deliveries->recordPending(
                            $payment,
                            $entry->type,
                            $entry->days,
                            $channel,
                            $dispatchDate->copy()->startOfDay(),
                        );

                        if (! $delivery) {
                            $skippedDuplicate++;

                            continue;
                        }

                        SendRentReminderJob::dispatch($delivery->id);
                        $queued++;
                    }
                }
            }
        }

        return new ReminderDispatchResult(
            queued: $queued,
            skippedDuplicate: $skippedDuplicate,
            skippedIneligible: $skippedIneligible,
            skippedDisabled: $skippedDisabled,
        );
    }

    /**
     * @return Collection<int, PaymentHistory>
     */
    protected function openPaymentsForLandlord(User $landlord): Collection
    {
        return PaymentHistory::query()
            ->forLandlord($landlord)
            ->with(['tenant'])
            ->whereIn('status', [
                PaymentStatus::DueSoon,
                PaymentStatus::Overdue,
                PaymentStatus::PartiallyPaid,
            ])
            ->get();
    }
}
