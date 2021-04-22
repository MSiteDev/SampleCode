<?php

namespace App\Model\Entity\Challenge;

use App\Model\Exception\InvalidChallengeSubmissionStatusException;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Embeddable
 */
class SubmissionStatus implements JsonSerializable
{
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_SUBMITTED = 'SUBMITTED';
    public const STATUS_TAKEN = 'TAKEN';
    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $status;

    public function __construct(string $status)
    {
        if (!in_array($status, self::statuses())) {
            throw new InvalidChallengeSubmissionStatusException($status);
        }

        $this->status = $status;
    }

    /** @return string[] */
    public static function statuses(): array
    {
        return [self::STATUS_TAKEN, self::STATUS_COMPLETED, self::STATUS_SUBMITTED, self::STATUS_DRAFT];
    }

    public static function taken(): self
    {
        return new self(self::STATUS_TAKEN);
    }

    public static function submitted(): self
    {
        return new self(self::STATUS_SUBMITTED);
    }

    public static function completed(): self
    {
        return new self(self::STATUS_COMPLETED);
    }

    public static function draft(): self
    {
        return new self(self::STATUS_DRAFT);
    }

    public function isTaken(): bool
    {
        return $this->status === self::STATUS_TAKEN;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function __toString(): string
    {
        return $this->status;
    }

    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
}
