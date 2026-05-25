<?php

namespace App\Mail\Reminders;

use App\Enums\ReminderType;
use App\Mail\LandlordMailable;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Reminders\RentDueReminderContentBuilder;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RentDueReminderMail extends LandlordMailable
{
    /** @var array<string, mixed> */
    protected array $reminderContent;

    public function __construct(
        public PaymentHistory $payment,
        public ReminderType $reminderType,
        public int $daysOffset,
        public Tenant $tenant,
    ) {
        $this->reminderContent = app(RentDueReminderContentBuilder::class)
            ->forTenant($tenant, $reminderType, $daysOffset);
    }

    public function envelope(): Envelope
    {
        $dueDate = $this->payment->due_date->format('j F Y');
        $landlordName = $this->payment->tenant->landlord->name;

        if ($this->reminderContent['isDueDay']) {
            $scoreLine = $this->reminderContent['showScoreBlock']
                ? " — you're at {$this->reminderContent['scoreDisplay']} ({$this->reminderContent['tierLabel']})"
                : '';

            $subject = "Rent due today{$scoreLine}";
        } else {
            $subject = $this->reminderType === ReminderType::BeforeDue
                ? "Rent reminder: due on {$dueDate}"
                : "Overdue rent notice: was due on {$dueDate}";
        }

        return $this->transactionalEnvelope($subject, $landlordName);
    }

    public function content(): Content
    {
        $landlord = $this->payment->tenant->landlord;
        $dueDate = $this->payment->due_date->format('j F Y');
        $amount = number_format((float) $this->payment->amount, 2);

        $isBeforeDue = $this->reminderType === ReminderType::BeforeDue;
        $isDueDay = $this->reminderContent['isDueDay'];

        $preheader = $isDueDay
            ? "Rent of {$amount} is due today. {$this->reminderContent['primaryNudge']}"
            : ($isBeforeDue
                ? "Your rent of {$amount} is due on {$dueDate}."
                : "Your rent payment of {$amount} was due on {$dueDate} and is still outstanding.");

        return new Content(
            view: 'mail.reminders.rent-due',
            with: $this->withMailTheme(array_merge($this->reminderContent, [
                'tenantName' => $this->tenant->name,
                'landlordName' => $landlord->name,
                'dueDate' => $dueDate,
                'amount' => $amount,
                'currencySymbol' => config('landlord.mail.currency_symbol'),
                'isBeforeDue' => $isBeforeDue,
                'daysOffset' => $this->daysOffset,
                'preheader' => $preheader,
                'title' => $isDueDay ? 'Rent due today' : ($isBeforeDue ? 'Rent payment reminder' : 'Overdue rent notice'),
            ])),
        );
    }
}
