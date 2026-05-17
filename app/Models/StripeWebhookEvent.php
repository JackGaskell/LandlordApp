<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Idempotency log for inbound Stripe webhooks.
 */
class StripeWebhookEvent extends Model
{
    protected $fillable = [
        'stripe_event_id',
        'type',
        'payload',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function isProcessed(): bool
    {
        return $this->processed_at !== null;
    }

    public function markProcessed(): void
    {
        $this->update(['processed_at' => now()]);
    }
}
