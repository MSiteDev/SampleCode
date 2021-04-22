<?php

namespace App\Model\Entity\Challenge;

use App\Model\Entity\Participant;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 */
class FeedbackFromParticipant extends AbstractFeedback
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Participant")
     */
    protected Participant $participantAuthor;

    public function __construct(Submission $challengeSubmission, Participant $participant)
    {
        parent::__construct($challengeSubmission);
        $this->setAuthor($participant);
//        $this->record(new P2PFeedbackMatchCreatedEvent($this));
    }

    /**
     * @Groups("course")
     */
    public function getAuthor(): Participant
    {
        return $this->participantAuthor;
    }

    public function setAuthor(Participant $author): void
    {
        $this->participantAuthor = $author;
    }
}
