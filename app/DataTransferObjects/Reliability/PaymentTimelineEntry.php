<?php

namespace App\DataTransferObjects\Reliability;

use App\Enums\PaymentOutcome;
use App\Enums\PaymentStatus;
use Carbon\CarbonInterface;

readonly class PaymentTimelineEntry
{
    public function __construct(
        public int $id,
        public string $periodLabel,
        public float $amount,
        public CarbonInterface $dueDate,
        public ?CarbonInterface $paidAt,
        public PaymentStatus $status,
        public PaymentOutcome $outcome,
        public ?int $daysLate,
        public ?string $paymentMethodLabel,
    ) {}

    public function amountFormatted(): string
    {
        return '£'.number_format($this->amount, 2);
    }

    public function subtitle(): string
    {
        if ($this->outcome === PaymentOutcome::OnTime && $this->paidAt) {
            return 'Paid '.$this->paidAt->format('j M').' · on time';
        }

        if ($this->outcome === PaymentOutcome::Late && $this->paidAt && $this->daysLate) {
            return 'Paid '.$this->paidAt->format('j M').' · '.$this->daysLate.' '.str('day')->plural($this->daysLate).' late';
        }

        if ($this->outcome === PaymentOutcome::Missed) {
            return 'Due '.$this->dueDate->format('j M').' · not yet paid';
        }

        if ($this->outcome === PaymentOutcome::Pending) {
            return 'Due '.$this->dueDate->format('j M');
        }

        return $this->outcome->label();
    }

    public function badgeTone(): string
    {
        return match ($this->outcome->timelineTone()) {
            'emerald' => 'success',
            'amber' => 'warning',
            'brand' => 'brand',
            'orange' => 'warning',
            default => 'neutral',
        };
    }

    public function dotClasses(): string
    {
        return match ($this->outcome->timelineTone()) {
            'emerald' => 'bg-emerald-500 ring-emerald-500/30',
            'amber' => 'bg-amber-500 ring-amber-500/30',
            'brand' => 'bg-brand-500 ring-brand-500/30',
            'orange' => 'bg-orange-500 ring-orange-500/30',
            default => 'bg-slate-500 ring-slate-500/30',
        };
    }
}

