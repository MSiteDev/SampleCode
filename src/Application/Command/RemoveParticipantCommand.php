<?php

namespace App\Application\Command;

use App\Model\Entity\Participant;
use Ramsey\Uuid\UuidInterface;

final class RemoveParticipantCommand implements Command
{
    private UuidInterface $participantId;

    public function __construct(Participant $participant)
    {
        $this->participantId = $participant->getId();
    }

    public function getParticipantId(): UuidInterface
    {
        return $this->participantId;
    }
}
