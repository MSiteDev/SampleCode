<?php

namespace App\Framework\DependencyInjection\Compiler\EventListeners;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class EventListenerTag
{
    public function __construct(private string $event, private int $priority = 0)
    {
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
