<?php

namespace App\Model\Entity\Challenge;

use App\Model\Entity\Coach;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 */
class FeedbackFromCoach extends AbstractFeedback
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Coach")
     */
    protected Coach $coachAuthor;

    public function __construct(Submission $challengeSubmission, Coach $coach)
    {
        parent::__construct($challengeSubmission);
        $this->setAuthor($coach);
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getAuthor(): Coach
    {
        return $this->coachAuthor;
    }

    public function setAuthor(Coach $coach): void
    {
        $this->coachAuthor = $coach;
    }
}
