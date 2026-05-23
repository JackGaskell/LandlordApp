<?php

namespace App\Services\Reminders;

use App\Enums\ReminderChannel;
use App\Services\Reminders\Contracts\ReminderChannelSender;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class ReminderChannelRegistry
{
    /** @var array<string, class-string<ReminderChannelSender>> */
    protected array $senders = [];

    public function __construct(
        protected Container $container,
    ) {}

    /**
     * @param  class-string<ReminderChannelSender>  $senderClass
     */
    public function register(ReminderChannel $channel, string $senderClass): void
    {
        $this->senders[$channel->value] = $senderClass;
    }

    public function sender(ReminderChannel $channel): ReminderChannelSender
    {
        $senderClass = $this->senders[$channel->value] ?? null;

        if (! $senderClass) {
            throw new InvalidArgumentException("No reminder sender registered for channel [{$channel->value}].");
        }

        return $this->container->make($senderClass);
    }

    /**
     * @return list<ReminderChannel>
     */
    public function enabledChannels(): array
    {
        return collect(ReminderChannel::cases())
            ->filter(fn (ReminderChannel $channel) => $channel->isEnabled())
            ->values()
            ->all();
    }
}
