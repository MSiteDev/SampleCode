<?php

namespace App\Model\Entity;

use App\Model\Entity\Challenge\AbstractChallenge;
use App\Model\Entity\Challenge\Submission;
use App\Model\Event\ChallengeSubmissionRemoved;
use App\Model\Event\RegistrationTokenHasBeenAddedToParticipant;
use App\Model\Exception\ParticipantCourseNotFoundException;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Infrastructure\Model\Repository\ORMParticipants")
 * @ORM\Table(name="participants")
 */
class Participant extends AbstractUser
{
    /**
     * @ORM\Column(type="string")
     */
    private string $firstName;
    /**
     * @ORM\Column(type="string")
     */
    private string $lastName;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $nickname = null;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Company")
     */
    private Company $company;
    /**
     * @var Collection<Group>
     * @ORM\ManyToMany(targetEntity="App\Model\Entity\Group", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="participants_groups",
     *     joinColumns={@ORM\JoinColumn(name="participant_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    private Collection $groups;
    /**
     * @ORM\OneToOne(targetEntity="App\Model\Entity\File", cascade={"persist", "remove"})
     */
    private ?File $photo = null;
    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Model\Entity\RegistrationToken",
     *     mappedBy="participant",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     */
    private Collection $registrationTokens;
    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Model\Entity\Challenge\Submission",
     *     mappedBy="participant",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     * @var Collection|Submission[]
     */
    private Collection $challenges;
    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Model\Entity\ParticipantCourse",
     *     mappedBy="participant",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     * @var Collection|ParticipantCourse[]
     */
    private Collection $courses;
    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Model\Entity\LiveSessionParticipant",
     *     mappedBy="participant",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     */
    private Collection $liveSessions;
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $activatedAt = null;
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $allowEmailNotifications = true;
    /**
     * @ORM\Column(type="boolean", name="can_take_p2p")
     */
    private bool $canTakeP2P = false;
    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Model\Entity\Notification",
     *     mappedBy="participant",
     *     orphanRemoval=true,
     *     cascade={"remove"}
     * )
     */
    private Collection $notifications;
    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Model\Entity\WatchableProgress",
     *     mappedBy="participant",
     *     orphanRemoval=true,
     *     cascade={"remove"}
     * )
     */
    private Collection $progresses;
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $liveChallengeReadyAt = null;
    private Collection $feedbackSurveys;
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $tokenValidAfter;

    private function __construct()
    {
        parent::__construct();
        $this->groups = new ArrayCollection();
        $this->registrationTokens = new ArrayCollection();
        $this->challenges = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->courses = new ArrayCollection();
        $this->liveSessions = new ArrayCollection();
        $this->progresses = new ArrayCollection();
        $this->feedbackSurveys = new ArrayCollection();
    }

    public static function new(string $firstName, string $lastName, string $emailAddress, Company $company): self
    {
        $self = new self();
        $self->setFirstName($firstName);
        $self->setLastName($lastName);
        $self->setEmail($emailAddress);
        $self->setCompany($company);
        $self->addNewRegistrationToken();

        return $self;
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFullName(): string
    {
        return trim(sprintf("%s %s", $this->firstName(), $this->lastName()));
    }

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function company(): Company
    {
        return $this->company;
    }

    public function groups(): Collection
    {
        return $this->groups;
    }

    public function setGroups(Collection $groups): void
    {
        $this->groups = $groups;
    }

    public function setPhoto(?File $photo): void
    {
        $this->photo = $photo;
    }

    /**
     * @Groups("post")
     */
    public function getPhoto(): ?File
    {
        return $this->photo;
    }

    public function hasPhoto(): bool
    {
        return $this->photo !== null;
    }

    public function addNewRegistrationToken(): void
    {
        $token = new RegistrationToken($this);
        $this->registrationTokens->add($token);
        $this->record(new RegistrationTokenHasBeenAddedToParticipant($this, $token));
    }

    public function removeRegistrationTokens(): void
    {
        $this->registrationTokens->clear();
    }

    /** @return Collection|Submission[] */
    public function challenges(): Collection
    {
        return $this->challenges;
    }

    public function removeChallengeSubmission(AbstractChallenge $challenge): void
    {
        if (!$this->hasSubmissionForChallenge($challenge)) {
            throw new DomainException('Can\'t remove a challenge without taking it.');
        }

        $this->challenges->removeElement($this->submissionForChallenge($challenge));
        $this->record(new ChallengeSubmissionRemoved($this, $challenge));
    }

    public function takeChallenge(AbstractChallenge $challenge): void
    {
        if ($this->hasSubmissionForChallenge($challenge)) {
            throw new DomainException('Already joined this challenge.');
        }

        $this->challenges->add(
            new Submission($challenge, $this)
        );
    }

    public function hasSubmissionForChallenge(AbstractChallenge $challenge): bool
    {
        foreach ($this->challenges as $challengeSubmission) {
            if ($challengeSubmission->getChallenge()->getId()->equals($challenge->getId())) {
                return true;
            }
        }

        return false;
    }

    public function submissionForChallenge(AbstractChallenge $challenge): Submission
    {
        foreach ($this->challenges as $challengeSubmission) {
            if ($challengeSubmission->getChallenge()->getId()->equals($challenge->getId())) {
                return $challengeSubmission;
            }
        }

        throw new DomainException(sprintf(
            'Challenge submission for participant(%s) and challenge(%s) not found.',
            $this->id()->toString(),
            $challenge->getId()->toString()
        ));
    }

    /** @return Collection|ParticipantCourse[] */
    public function courses(): Collection
    {
        return $this->courses;
    }

    public function hasJoinedCourse(Course $course): bool
    {
        foreach ($this->courses as $participantCourse) {
            if ($participantCourse->course()->getId()->equals($course->getId())) {
                return true;
            }
        }

        return false;
    }

    public function joinedCourse(Course $course): ParticipantCourse
    {
        foreach ($this->courses as $participantCourse) {
            if ($participantCourse->course()->getId()->equals($course->getId())) {
                return $participantCourse;
            }
        }

        throw ParticipantCourseNotFoundException::withParticipantAndCourse($this->id(), $course->getId());
    }

    public function leaveCourse(Course $course): void
    {
        if (!$this->hasJoinedCourse($course)) {
            throw new DomainException('Can\'t leave a course without join it.');
        }

        $this->courses->removeElement($this->joinedCourse($course));
    }

    public function joinCourse(Course $course): void
    {
        if ($this->hasJoinedCourse($course)) {
            throw new DomainException('Already joined this course');
        }

        $this->courses->add(
            new ParticipantCourse($this, $course)
        );
    }

    public function nickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): void
    {
        $this->nickname = $nickname;
    }

    /**
     * @Groups("post")
     */
    public function getDisplayName(): string
    {
        return $this->nickname ?? $this->firstName;
    }

    public function getRoles(): array
    {
        return [
            'ROLE_PARTICIPANT',
        ];
    }

    public function isActivated(): bool
    {
        return $this->activatedAt !== null;
    }

    public function markAsActivated(): void
    {
        $this->activatedAt = new DateTimeImmutable();
    }

    public function setAllowEmailNotifications(bool $allowEmailNotifications): void
    {
        $this->allowEmailNotifications = $allowEmailNotifications;
    }

    public function isAllowEmailNotifications(): bool
    {
        return $this->allowEmailNotifications;
    }

    public function getCanTakeP2P(): bool
    {
        return $this->canTakeP2P;
    }

    public function setCanTakeP2P(bool $canTakeP2P): void
    {
        $this->canTakeP2P = $canTakeP2P;
    }

    /** @return Collection|LiveSessionParticipant[] */
    public function getLiveSessions(): Collection
    {
        return $this->liveSessions;
    }

    public function getLiveChallengeReadyAt(): ?DateTimeImmutable
    {
        return $this->liveChallengeReadyAt;
    }

    public function setLiveChallengeReadyAt(?DateTimeInterface $liveChallengeReadyAt): void
    {
        if ($this->liveChallengeReadyAt < $liveChallengeReadyAt) {
            $this->liveChallengeReadyAt = DateTimeImmutable::createFromInterface($liveChallengeReadyAt);
        }
    }

    public function getFeedbackSurveys(): Collection
    {
        return $this->feedbackSurveys;
    }

    public function addFeedbackSurvey(FeedbackSurvey $survey): void
    {
        if (!$this->feedbackSurveys->contains($survey)) {
            $this->feedbackSurveys->add($survey);
            $survey->addUser($this);
        }
    }

    public function removeFeedbackSurvey(FeedbackSurvey $survey): void
    {
        if ($this->feedbackSurveys->contains($survey)) {
            $this->feedbackSurveys->removeElement($survey);
            $survey->removeUser($this);
        }
    }

    public function getTokenValidAfter(): ?DateTimeImmutable
    {
        return $this->tokenValidAfter;
    }

    public function setTokenValidAfter(DateTimeInterface $tokenValidAfter): void
    {
        $this->tokenValidAfter = DateTimeImmutable::createFromInterface($tokenValidAfter);
    }
}
