<?php

namespace App\Model\Entity\Challenge;

use App\Model\Entity\ChallengeProgress;
use App\Model\Entity\Company;
use App\Model\Entity\File;
use App\Model\Entity\Language;
use App\Model\Entity\Partial\HasCoverImagesWithTitle;
use App\Model\Entity\Participant;
use App\Model\Entity\Video;
use App\Model\Entity\Watchable;
use App\Model\Entity\WatchableProgress;
use App\Utils\Translations\Model\AbstractTranslatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity()
 * @ORM\Table(name="challenges")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "COURSE" = "App\Model\Entity\Challenge\CourseChallenge",
 *     "LIVE" = "App\Model\Entity\Challenge\LiveChallenge",
 * })
 * @method ChallengeTranslation getTranslation()
 * @method ChallengeTranslation translatedTo(Language $language)
 */
abstract class AbstractChallenge extends AbstractTranslatable implements Watchable
{
    use HasCoverImagesWithTitle;

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    protected UuidInterface $id;
    /**
     * @ORM\OneToMany(targetEntity="App\Model\Entity\Challenge\ChallengeTranslation", mappedBy="challenge", cascade={"persist", "remove"})
     * @var Collection|ChallengeTranslation[]
     */
    protected Collection $translations;
    /**
     * @ORM\OneToOne(targetEntity="App\Model\Entity\File", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected ?File $instructions = null;
    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="challenge", cascade={"persist", "remove"})
     * @ORM\OrderBy({"ordinal" = "ASC"})
     * @var Collection|Question[]
     */
    protected Collection $questions;
    /**
     * @ORM\OneToMany(targetEntity="App\Model\Entity\Challenge\Submission", mappedBy="challenge", cascade={"remove"})
     * @var Collection|Submission[]
     */
    protected Collection $submissions;
    /**
     * @ORM\Column(type="boolean", name="is_p2p")
     */
    protected bool $isP2P;
    /**
     * @ORM\OneToMany(targetEntity="App\Model\Entity\ChallengeProgress", mappedBy="challenge", cascade={"remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private Collection $progresses;
    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Model\Entity\Challenge\SubmissionsCounter",
     *     mappedBy="challenge",
     *     cascade={"remove"}
     * )
     * @var Collection|SubmissionsCounter[]
     */
    private Collection $counters;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->translations = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->submissions = new ArrayCollection();
        $this->counters = new ArrayCollection();
    }

    /**
     * @Groups({"course", "challenges", "feedback_created_by_me"})
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /** @return Collection|ChallengeTranslation[] */
    public function translations(): Collection
    {
        return $this->translations;
    }

    public function setTranslation(
        Language $language,
        string $name = '',
        string $description = '',
        ?string $hint = null,
        ?Video $video = null,
        ?Video $trailerVideo = null
    ): void {
        if (!$this->hasTranslationFor($language)) {
            $this->translations->add(
                ChallengeTranslation::new($this, $language, $name, $description, $hint, $video, $trailerVideo)
            );
        } else {
            $translation = $this->translatedTo($language);
            $translation->setName($name);
            $translation->setDescription($description);
            $translation->setHint($hint);
            $translation->setVideo($video);
            $translation->setTrailerVideo($video);
        }
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getTitle(): string
    {
        return $this->getTranslation()->name();
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getDescription(): string
    {
        return $this->getTranslation()->description();
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getHint(): ?string
    {
        return $this->getTranslation()->hint();
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getVideo(): ?Video
    {
        return $this->getTranslation()->getVideo();
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getTrailerVideo(): ?Video
    {
        return $this->getTranslation()->getTrailerVideo();
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getInstructions(): ?File
    {
        return $this->instructions;
    }

    public function setInstructions(?File $file): void
    {
        $this->instructions = $file;
    }

    /**
     * @return Collection|Question[]
     * @Groups({"course", "challenges"})
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): void
    {
        if (!$question->challenge()->getId()->equals($this->id)) {
            throw new InvalidArgumentException();
        }

        $this->questions->add($question);
    }

    public function removeQuestion(Question $question): void
    {
        foreach ($this->questions as $existsQuestion) {
            if ($question->getId()->equals($existsQuestion->getId())) {
                $this->questions->removeElement($existsQuestion);

                return;
            }
        }

        throw new InvalidArgumentException();
    }

    /**
     * @return Collection|Submission[]
     */
    public function getSubmissions(): Collection
    {
        return $this->submissions;
    }

    public function getSubmissionOfParticipant(Participant $participant): ?Submission
    {
        return $this->submissions->matching(
            Criteria::create()->where(
                Criteria::expr()->eq('participant', $participant)
            )
        )->first() ?: null;
    }

    /**
     * @Groups({"course", "challenges"})
     * @SerializedName("is_p2p")
     */
    public function isP2P(): bool
    {
        return $this->isP2P;
    }

    /**
     * @Groups({"course", "challenges"})
     * @SerializedName("is_live")
     */
    public function isLive(): bool
    {
        return static::class === LiveChallenge::class;
    }

    /**
     * @Groups({"course", "challenges", "challengeProgress"})
     * @return Collection|ChallengeProgress[]
     */
    public function getProgresses(): Collection
    {
        return $this->progresses;
    }

    public function getProgressOfParticipant(Participant $participant): WatchableProgress
    {
        foreach ($this->progresses as $progress) {
            if ($progress->getParticipant()->getId()->equals($participant->getId())) {
                return $progress;
            }
        }

        $progress = new ChallengeProgress($participant, $this, 0);

        return $progress;
    }

    public function getCounters(): ArrayCollection|Collection
    {
        return $this->counters;
    }

    public function getCounterForCompany(Company $company): ?SubmissionsCounter
    {
        foreach ($this->counters as $counter) {
            if ($counter->getCompany()->getId()->equals($company->getId())) {
                return $counter;
            }
        }

        return null;
    }
}
