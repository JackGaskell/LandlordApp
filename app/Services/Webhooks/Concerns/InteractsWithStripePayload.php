<?php

namespace App\Services\Webhooks\Concerns;

trait InteractsWithStripePayload
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function eventObject(array $payload): array
    {
        /** @var array<string, mixed> $object */
        $object = data_get($payload, 'data.object', []);

        return $object;
    }

    /**
     * @param  array<string, mixed>  $object
     * @return array<string, mixed>
     */
    protected function metadata(array $object): array
    {
        /** @var array<string, mixed> $metadata */
        $metadata = $object['metadata'] ?? [];

        return $metadata;
    }
}
