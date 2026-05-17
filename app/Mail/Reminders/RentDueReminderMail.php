<?php

namespace App\Mail\Reminders;

use App\Enums\ReminderType;
use App\Mail\LandlordMailable;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RentDueReminderMail extends LandlordMailable
{
    public function __construct(
        public PaymentHistory $payment,
        public ReminderType $reminderType,
        public int $daysOffset,
        public Tenant $tenant,
    ) {}

    public function envelope(): Envelope
    {
        $dueDate = $this->payment->due_date->format('j F Y');

        $subject = $this->reminderType === ReminderType::BeforeDue
            ? "Rent reminder: due on {$dueDate}"
            : "Overdue rent notice: was due on {$dueDate}";

        $landlordName = $this->payment->tenant->landlord->name;

        return $this->transactionalEnvelope($subject, $landlordName);
    }

    public function content(): Content
    {
        $landlord = $this->payment->tenant->landlord;
        $dueDate = $this->payment->due_date->format('j F Y');
        $amount = number_format((float) $this->payment->amount, 2);

        $isBeforeDue = $this->reminderType === ReminderType::BeforeDue;

        return new Content(
            view: 'mail.reminders.rent-due',
            with: $this->withMailTheme([
                'tenantName' => $this->tenant->name,
                'landlordName' => $landlord->name,
                'dueDate' => $dueDate,
                'amount' => $amount,
                'currencySymbol' => config('landlord.mail.currency_symbol'),
                'isBeforeDue' => $isBeforeDue,
                'daysOffset' => $this->daysOffset,
                'preheader' => $isBeforeDue
                    ? "Your rent of {$amount} is due on {$dueDate}."
                    : "Your rent payment of {$amount} was due on {$dueDate} and is still outstanding.",
                'title' => $isBeforeDue ? 'Rent payment reminder' : 'Overdue rent notice',
            ]),
        );
    }
}
