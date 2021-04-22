<?php

namespace App\Model\Entity\Challenge;

use App\Model\Entity\File;
use App\Model\Event\ChallengeSubmissionHasNewFeedback;
use App\Model\Event\FeedbackEvaluationCreatedEvent;
use App\Utils\EventRecorder\EventRecorderInterface;
use App\Utils\EventRecorder\EventRecorderTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @ORM\Table(name="challenge_feedbacks")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "COACH" = "App\Model\Entity\Challenge\FeedbackFromCoach",
 *     "P2P" = "App\Model\Entity\Challenge\FeedbackFromParticipant",
 * })
 *
 */
abstract class AbstractFeedback implements EventRecorderInterface
{
    use EventRecorderTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    protected UuidInterface $id;
    /**
     * @ORM\ManyToOne(targetEntity="Submission", inversedBy="feedbacks")
     * @ORM\JoinColumn(name="submission_id", referencedColumnName="id")
     */
    protected Submission $challengeSubmission;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $content = null;
    /**
     * @ORM\ManyToMany(targetEntity="App\Model\Entity\File", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="challenge_feedback_file",
     *     joinColumns={@ORM\JoinColumn(name="challenge_feedback_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="file_id", referencedColumnName="id", onDelete="CASCADE")},
     * )
     * @var Collection|File[]
     */
    protected Collection $media;
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    protected DateTimeImmutable $createdAt;
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    protected ?DateTimeImmutable $completedAt = null;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $evaluationContent = null;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $evaluationRate = null;
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    protected ?DateTimeImmutable $evaluationCreatedAt = null;

    public function __construct(Submission $challengeSubmission)
    {
        $this->id = Uuid::uuid4();
        $this->challengeSubmission = $challengeSubmission;
        $this->createdAt = new DateTimeImmutable();
        $this->media = new ArrayCollection();
    }

    /**
     * @Groups({"feedback_created_by_me", "challenges"})
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @Groups({"challenges", "course", "feedback_created_by_me"})
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @Groups({"challenges", "course", "feedback_created_by_me"})
     * @return Collection|File[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function setMedia(Collection $media): void
    {
        foreach ($media as $element) {
            if (!$element instanceof File) {
                throw new RuntimeException('Every element must be instance of "' . File::class . '"');
            }
        }

        $this->media = $media;
    }

    /**
     * @Groups({"course", "feedback_created_by_me"})
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @Groups({"feedback_created_by_me"})
     */
    public function getSubmission(): Submission
    {
        return $this->challengeSubmission;
    }

    /**
     * @Groups({"challenges", "feedback_created_by_me", "course"})
     */
    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function markAsCompleted(): void
    {
        if (is_null($this->completedAt)) {
            $this->recordOnce(new ChallengeSubmissionHasNewFeedback($this->getSubmission(), $this));
            $this->completedAt = new DateTimeImmutable();
        }
    }

    public function getEvaluationContent(): ?string
    {
        return $this->evaluationContent;
    }

    public function setEvaluationContent(?string $evaluationContent): void
    {
        $this->evaluationContent = $evaluationContent;
    }

    public function getEvaluationRate(): ?int
    {
        return $this->evaluationRate;
    }

    public function setEvaluationRate(?int $evaluationRate): void
    {
        if (!is_null($evaluationRate) && ($evaluationRate < 1 || $evaluationRate > 5)) {
            throw new InvalidArgumentException();
        }

        if (is_null($this->evaluationRate) && !is_null($evaluationRate)) {
            $this->record(new FeedbackEvaluationCreatedEvent($this));
        }

        $this->evaluationRate = $evaluationRate;
        $this->evaluationCreatedAt = new DateTimeImmutable();
    }

    public function getEvaluationCreatedAt(): ?DateTimeImmutable
    {
        return $this->evaluationCreatedAt;
    }

    /**
     * @Groups({"challenges", "feedback_created_by_me"})
     * @return mixed[]
     */
    public function getEvaluation(): ?array
    {
        return is_null($this->evaluationRate)
            ? null
            : [
                'rate' => $this->evaluationRate,
                'content' => $this->evaluationContent,
                'createdAt' => $this->evaluationCreatedAt,
            ];
    }
}
