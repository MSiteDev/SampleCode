<?php

namespace App\Model\Entity\Challenge;

use App\Model\Entity\Group;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 */
class LiveChallenge extends AbstractChallenge
{
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $availableFrom;
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $availableTo;
    /**
     * @ORM\ManyToMany(targetEntity="App\Model\Entity\Group", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="live_challenge_group",
     *     joinColumns={@ORM\JoinColumn(name="live_challenge_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     * @var Collection|Group[]
     */
    private Collection $groups;
    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Model\Entity\Challenge\LiveChallengeHelperVideo",
     *     mappedBy="challenge",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"ordinal" = "ASC"})
     */
    private Collection $helperVideos;

    public function __construct(DateTimeImmutable $availableFrom, DateTimeImmutable $availableTo)
    {
        parent::__construct();
        $this->groups = new ArrayCollection();
        $this->helperVideos = new ArrayCollection();
        $this->isP2P = true;
        $this->availableFrom = $availableFrom;
        $this->availableTo = $availableTo;
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getAvailableFrom(): DateTimeImmutable
    {
        return $this->availableFrom;
    }

    public function setAvailableFrom(DateTimeImmutable $availableFrom): void
    {
        $this->availableFrom = $availableFrom;
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getAvailableTo(): DateTimeImmutable
    {
        return $this->availableTo;
    }

    public function setAvailableTo(DateTimeImmutable $availableTo): void
    {
        $this->availableTo = $availableTo;
    }

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function setGroups(Collection $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getHelperVideos(): Collection
    {
        return $this->helperVideos;
    }
}
