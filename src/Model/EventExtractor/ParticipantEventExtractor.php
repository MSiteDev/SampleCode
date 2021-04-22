<?php

namespace App\Model\EventExtractor;

use App\Model\Entity\Challenge\Submission;
use App\Model\Entity\Participant;
use App\Model\Entity\ParticipantCourse;
use App\Model\Event\Event;

final class ParticipantEventExtractor
{
    /** @return Event[] */
    public function extractAndClear(Participant $participant): array
    {
        return array_merge(
            $participant->getAndEraseRecordedEvents(),
            array_reduce(
                $participant->challenges()->toArray(),
                function (array $events, Submission $challenge) {
                    return array_merge($events, $challenge->getAndEraseRecordedEvents());
                },
                []
            ),
            array_reduce(
                $participant->courses()->toArray(),
                function (array $events, ParticipantCourse $participantCourse) {
                    return array_merge($events, $participantCourse->getAndEraseRecordedEvents());
                },
                []
            ),
        );
    }
}
