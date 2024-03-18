<?php

namespace Foodticket\Takeaway;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TakeawayWebhook
{
    public string $platform = 'takeaway-pos';

    public function __construct(
        public string $eventName,
        public ?string $restaurantId,
        public ?string $resourceId,
        public array $payload,
    ) {
    }

    public function platform(): string
    {
        return $this->platform;
    }

    public function eventName(): string
    {
        $eventName = Str::of(Str::kebab($this->eventName))->slug();

        return $this->platform . '.' . $eventName->toString();
    }

    public function restaurantId(): ?string
    {
        return $this->restaurantId;
    }

    public function resourceId(): ?string
    {
        return $this->resourceId;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * @throws Exception
     */
    public static function fromNotification(array $notification): self
    {
        $type = Arr::get($notification, 'event');
        $restaurantId = Arr::get($notification, 'restaurantId') ?? Arr::get($notification, 'restaurant');
        $resourceId = Arr::get($notification, 'id') ?? Arr::get($notification, 'order.id');

        if (! $type) {
            throw new Exception();
        }

        return new self(
            $type,
            $restaurantId,
            $resourceId,
            $notification
        );
    }
}
