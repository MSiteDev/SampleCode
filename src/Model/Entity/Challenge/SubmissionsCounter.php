<?php

namespace App\Model\Entity\Challenge;

use App\Model\Entity\Company;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="challenge_submissions_counters",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="unique_counter",
 *              columns={"challenge_id", "company_id"}
 *          )
 *     }
 * )
 */
class SubmissionsCounter
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    private UuidInterface $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Challenge\AbstractChallenge")
     */
    private AbstractChallenge $challenge;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Company")
     */
    private Company $company;
    /**
     * @ORM\Column(type="integer")
     */
    private int $completed = 0;
    /**
     * @ORM\Column(type="integer")
     */
    private int $inProgress = 0;

    public function __construct(AbstractChallenge $challenge, Company $company)
    {
        $this->id = Uuid::uuid4();
        $this->challenge = $challenge;
        $this->company = $company;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getChallenge(): AbstractChallenge
    {
        return $this->challenge;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getCompleted(): int
    {
        return $this->completed;
    }

    public function setCompleted(int $completed): void
    {
        $this->completed = $completed;
    }

    public function getInProgress(): int
    {
        return $this->inProgress;
    }

    public function setInProgress(int $inProgress): void
    {
        $this->inProgress = $inProgress;
    }
}
