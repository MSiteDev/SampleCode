<?php

namespace App\Utils\EventRecorder;

use App\Model\Event\Event;

interface EventRecorderInterface
{
    public function record(Event $event): void;

    /** @return Event[] */
    public function getRecordedEvents(): array;

    public function eraseRecordedEvents(): void;

    /** @return Event[] */
    public function getAndEraseRecordedEvents(): array;
}
