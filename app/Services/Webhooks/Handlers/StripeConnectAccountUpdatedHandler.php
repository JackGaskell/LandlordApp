<?php

namespace App\Services\Webhooks\Handlers;

use App\Contracts\Webhooks\StripeWebhookHandler;
use App\Models\User;
use App\Services\Webhooks\Concerns\InteractsWithStripePayload;

class StripeConnectAccountUpdatedHandler implements StripeWebhookHandler
{
    use InteractsWithStripePayload;

    public function handles(string $eventType): bool
    {
        return $eventType === 'account.updated';
    }

    public function handle(array $payload): void
    {
        $account = $this->eventObject($payload);
        $accountId = (string) ($account['id'] ?? '');

        if ($accountId === '') {
            return;
        }

        User::query()
            ->where('stripe_connect_account_id', $accountId)
            ->update([
                'stripe_connect_charges_enabled' => (bool) ($account['charges_enabled'] ?? false),
                'stripe_connect_details_submitted' => (bool) ($account['details_submitted'] ?? false),
            ]);
    }
}
