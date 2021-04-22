<?php

namespace App\Model\Event;

use App\Model\Entity\Challenge\AbstractFeedback;
use App\Model\Entity\Challenge\Submission;
use Ramsey\Uuid\UuidInterface;

final class ChallengeSubmissionHasNewFeedback implements Event
{
    private UuidInterface $challengeSubmissionId;
    private UuidInterface $feedbackId;

    public function __construct(Submission $challengeSubmission, AbstractFeedback $feedback)
    {
        $this->challengeSubmissionId = $challengeSubmission->getId();
        $this->feedbackId = $feedback->getId();
    }

    public function getChallengeSubmissionId(): UuidInterface
    {
        return $this->challengeSubmissionId;
    }

    public function getFeedbackId(): UuidInterface
    {
        return $this->feedbackId;
    }
}
