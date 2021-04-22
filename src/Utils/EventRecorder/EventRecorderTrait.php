<?php

namespace App\Utils\EventRecorder;

use App\Model\Event\Event;

trait EventRecorderTrait
{
    /** @var Event[] */
    protected array $recordedEvents = [];

    public function record(Event $event): void
    {
        array_push($this->recordedEvents, $event);
    }

    public function recordOnce(Event $event): void
    {
        $this->recordedEvents = array_filter($this->recordedEvents, function (Event $existingEvent) use ($event) {
            return $existingEvent::class !== $event::class;
        });

        $this->record($event);
    }

    /** @return Event[] */
    public function getRecordedEvents(): array
    {
        return $this->recordedEvents;
    }

    public function eraseRecordedEvents(): void
    {
        $this->recordedEvents = [];
    }

    /** @return Event[] */
    public function getAndEraseRecordedEvents(): array
    {
        $events = $this->getRecordedEvents();
        $this->eraseRecordedEvents();

        return $events;
    }
}
