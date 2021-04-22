<?php

namespace App\Model\Event;

use App\Model\Entity\LiveSession;
use App\Model\Entity\Participant;

final class ParticipantJoinedToLiveSessionEvent implements AsyncEvent
{
    private string $liveSessionId;
    private string $participantId;

    public function __construct(Participant $participant, LiveSession $liveSession)
    {
        $this->participantId = $participant->getId();
        $this->liveSessionId = $liveSession->getId();
    }

    public function getLiveSessionId(): string
    {
        return $this->liveSessionId;
    }

    public function getParticipantId(): string
    {
        return $this->participantId;
    }
}
