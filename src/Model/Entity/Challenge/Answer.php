<?php

namespace App\Model\Entity\Challenge;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @ORM\Table(name="challenge_submission_question_answers")
 */
class Answer
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    private UuidInterface $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Challenge\Submission", inversedBy="answers")
     */
    private Submission $challengeSubmission;
    /**
     * @ORM\ManyToOne(targetEntity="Question")
     */
    private Question $question;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $content;

    public function __construct(
        Submission $challengeSubmission,
        Question $question,
        ?string $content
    ) {
        $this->id = Uuid::uuid4();
        $this->challengeSubmission = $challengeSubmission;
        $this->question = $question;
        $this->content = $content;
    }

    /**
     * @Groups({"feedback_created_by_me"})
     */
    public function getQuestion(): Question
    {
        return $this->question;
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getQuestionId(): UuidInterface
    {
        return $this->getQuestion()->getId();
    }

    /**
     * @Groups({"course", "challenges", "feedback_created_by_me"})
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }
}
