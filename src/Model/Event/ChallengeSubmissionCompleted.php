<?php

namespace App\Model\Event;

use App\Model\Entity\Challenge\Submission;

final class ChallengeSubmissionCompleted implements AsyncEvent
{
    private string $challengeSubmissionId;

    public function __construct(Submission $challengeSubmission)
    {
        $this->challengeSubmissionId = $challengeSubmission->getId();
    }

    public function challengeSubmissionId(): string
    {
        return $this->challengeSubmissionId;
    }
}
