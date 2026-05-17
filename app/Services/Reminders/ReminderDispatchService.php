<?php

namespace App\Services\Reminders;

use App\Enums\PaymentStatus;
use App\Enums\ReminderType;
use App\Jobs\Reminders\SendRentReminderJob;
use App\Models\LandlordSetting;
use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Finds payments that need a reminder today and queues SendRentReminderJob.
 */
class ReminderDispatchService
{
    public function __construct(
        protected ReminderScheduler $scheduler,
    ) {}

    /**
     * @return array{queued: int, skipped: int}
     */
    public function dispatchDueReminders(?User $landlord = null, bool $dryRun = false): array
    {
        $queued = 0;
        $skipped = 0;

        $landlords = $landlord
            ? collect([$landlord->load('landlordSetting')])
            : User::query()->whereHas('landlordSetting')->with('landlordSetting')->get();

        foreach ($landlords as $user) {
            $settings = $user->landlordSetting;

            if (! $settings?->email_reminders_enabled) {
                continue;
            }

            $schedule = $this->scheduler->scheduleForSettings($settings);

            foreach ($this->openPaymentsForLandlord($user) as $payment) {
                foreach ($schedule as $entry) {
                    if (! $this->shouldSendToday($payment, $entry['offset'])) {
                        continue;
                    }

                    if ($this->alreadySentToday($payment->id, $entry['type'], $entry['offset'])) {
                        $skipped++;

                        continue;
                    }

                    if (! $dryRun) {
                        SendRentReminderJob::dispatch(
                            $payment->id,
                            $entry['type'],
                            $entry['offset'],
                        );

                        $this->markSentToday($payment->id, $entry['type'], $entry['offset']);
                    }

                    $queued++;
                }
            }
        }

        return ['queued' => $queued, 'skipped' => $skipped];
    }

    /**
     * @return Collection<int, PaymentHistory>
     */
    protected function openPaymentsForLandlord(User $landlord): Collection
    {
        return PaymentHistory::query()
            ->forLandlord($landlord)
            ->with(['tenant.landlord'])
            ->whereIn('status', [
                PaymentStatus::DueSoon,
                PaymentStatus::Overdue,
                PaymentStatus::PartiallyPaid,
            ])
            ->get();
    }

    protected function shouldSendToday(PaymentHistory $payment, int $offset): bool
    {
        return $payment->due_date->copy()->addDays($offset)->isToday();
    }

    protected function cacheKey(int $paymentId, ReminderType $type, int $offset): string
    {
        return "rent-reminder:{$paymentId}:{$type->value}:{$offset}:".now()->toDateString();
    }

    protected function alreadySentToday(int $paymentId, ReminderType $type, int $offset): bool
    {
        return Cache::has($this->cacheKey($paymentId, $type, $offset));
    }

    protected function markSentToday(int $paymentId, ReminderType $type, int $offset): void
    {
        Cache::put($this->cacheKey($paymentId, $type, $offset), true, now()->endOfDay());
    }
}
