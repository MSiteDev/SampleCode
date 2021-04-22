<?php

namespace App\Model\Entity\Challenge;

use App\Model\Entity\Language;
use App\Model\Entity\Video;
use App\Utils\Translations\Model\AbstractTranslation;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="challenge_translations")
 */
class ChallengeTranslation extends AbstractTranslation
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    private UuidInterface $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Challenge\AbstractChallenge", inversedBy="translations")
     */
    private AbstractChallenge $challenge;
    /**
     * @ORM\Column(type="string")
     */
    private string $name;
    /**
     * @ORM\Column(type="text")
     */
    private string $description;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $hint;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Video")
     */
    private ?Video $video;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Video")
     */
    private ?Video $trailerVideo;

    private function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public static function new(
        AbstractChallenge $challenge,
        Language $language,
        string $name,
        string $description,
        ?string $hint,
        ?Video $video,
        ?Video $trailerVideo
    ): self {
        $self = new self();
        $self->challenge = $challenge;
        $self->language = $language;
        $self->name = $name;
        $self->description = $description;
        $self->hint = $hint;
        $self->video = $video;
        $self->trailerVideo = $trailerVideo;

        return $self;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function hint(): ?string
    {
        return $this->hint;
    }

    public function setHint(?string $hint): void
    {
        $this->hint = $hint;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setVideo(?Video $video): void
    {
        $this->video = $video;
    }

    public function getTrailerVideo(): ?Video
    {
        return $this->trailerVideo;
    }

    public function setTrailerVideo(?Video $trailerVideo): void
    {
        $this->trailerVideo = $trailerVideo;
    }
}
