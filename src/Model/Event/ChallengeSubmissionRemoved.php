<?php

namespace App\Model\Event;

use App\Model\Entity\Challenge\AbstractChallenge;
use App\Model\Entity\Participant;

final class ChallengeSubmissionRemoved implements Event
{
    private string $participantId;
    private string $challengeId;

    public function __construct(Participant $participant, AbstractChallenge $challenge)
    {
        $this->participantId = $participant->id();
        $this->challengeId = $challenge->getId();
    }

    public function participantId(): string
    {
        return $this->participantId;
    }

    public function challengeId(): string
    {
        return $this->challengeId;
    }
}
