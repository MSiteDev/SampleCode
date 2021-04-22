<?php

namespace App\Model\Entity\Challenge;

use App\Model\Entity\File;
use App\Model\Entity\Participant;
use App\Model\Event\ChallengeSubmissionCompleted;
use App\Model\Event\ChallengeTakenEvent;
use App\Utils\EventRecorder\EventRecorderInterface;
use App\Utils\EventRecorder\EventRecorderTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use LogicException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity()
 * @ORM\Table(name="challenge_submissions")
 */
class Submission implements EventRecorderInterface
{
    use EventRecorderTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    private UuidInterface $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Challenge\AbstractChallenge", inversedBy="submissions")
     */
    private AbstractChallenge $challenge;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Participant", inversedBy="challenges")
     */
    private Participant $participant;
    /**
     * @ORM\ManyToMany(targetEntity="App\Model\Entity\File", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="challenge_submission_file",
     *     joinColumns={@ORM\JoinColumn(name="challenge_submission_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="file_id", referencedColumnName="id")}
     * )
     * @var Collection|File[]
     */
    private Collection $media;
    /**
     * @ORM\OneToMany(
     *     targetEntity="AbstractFeedback",
     *     mappedBy="challengeSubmission",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @var Collection|AbstractFeedback[]
     */
    private Collection $feedbacks;
    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="challengeSubmission", cascade={"persist", "remove"})
     * @var Collection|Answer[]
     */
    private Collection $answers;
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $updatedAt;
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $completedAt = null;
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $takenAt;
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $p2pFeedbackGiven = false;

    public function __construct(AbstractChallenge $challenge, Participant $participant)
    {
        $this->id = Uuid::uuid4();
        $this->challenge = $challenge;
        $this->participant = $participant;
        $this->media = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->updatedAt = new DateTimeImmutable();
        $this->takenAt = $this->updatedAt;
        $this->record(new ChallengeTakenEvent($this));
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @Groups({"feedback_created_by_me"})
     */
    public function getChallenge(): AbstractChallenge
    {
        return $this->challenge;
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getStatus(): SubmissionStatus
    {
        switch (true) {
            case !is_null($this->completedAt):
                return SubmissionStatus::completed();
            case !!($this->media->count() || $this->answers->count()):
                return SubmissionStatus::draft();
            default:
                return SubmissionStatus::taken();
        }
    }

    /**
     * @Groups({"course", "challenges", "feedback_created_by_me"})
     * @return File[]|Collection
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function setMedia(Collection $media): void
    {
        $this->media = $media;
        $this->updateTimestamp();
    }

    public function hasFeedback(): bool
    {
        return $this->feedbacks->count() > 0;
    }

    /**
     * @Groups({"course", "challenges"})
     * @return Collection|AbstractFeedback[]
     */
    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

    public function addFeedback(AbstractFeedback $feedback): void
    {
        if (!$feedback->getSubmission()->getId()->equals($this->getId())) {
            throw new LogicException();
        }
        $this->feedbacks->add($feedback);
        $this->updateTimestamp();
    }

    /**
     * @Groups({"course", "challenges", "feedback_created_by_me"})
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function hasAnswerForQuestion(Question $question): bool
    {
        foreach ($this->answers as $answer) {
            if ($question->getId()->equals($answer->getQuestion()->getId())) {
                return true;
            }
        }

        return false;
    }

    public function answerForQuestion(Question $question): ?Answer
    {
        foreach ($this->answers as $answer) {
            if ($question->getId()->equals($answer->getQuestion()->getId())) {
                return $answer;
            }
        }

        return null;
    }

    public function setQuestionAnswer(Question $question, ?string $content): void
    {
        if (!$question->challenge()->getId()->equals($this->challenge->getId())) {
            throw new DomainException('Question does not belong to the appropriate challenge.');
        }

        if ($this->hasAnswerForQuestion($question)) {
            $this->answerForQuestion($question)->setContent($content);
        } else {
            $this->answers->add(
                new Answer($this, $question, $content)
            );
        }

        $this->updateTimestamp();
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    protected function updateTimestamp(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @Groups({"course", "challenges", "feedback_created_by_me"})
     */
    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function markAsCompleted(): void
    {
        if (is_null($this->completedAt)) {
            $this->completedAt = new DateTimeImmutable();
            $this->record(new ChallengeSubmissionCompleted($this));
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getTakenAt(): DateTimeImmutable
    {
        return $this->takenAt;
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function isP2pFeedbackGiven(): bool
    {
        return $this->p2pFeedbackGiven;
    }

    public function setP2pFeedbackGiven(bool $p2pFeedbackGiven): void
    {
        $this->p2pFeedbackGiven = $p2pFeedbackGiven;
    }

    public function isCompleted(): bool
    {
        return !is_null($this->completedAt);
    }

    /**
     * @Groups({"course", "challenges"})
     * @SerializedName("isMatched")
     */
    public function isMatched(): bool
    {
        $criteria = Criteria::create()->where(
            Criteria::expr()->eq('completedAt', null)
        );

        return $this->feedbacks->matching($criteria)->count() > 0;
    }
}
