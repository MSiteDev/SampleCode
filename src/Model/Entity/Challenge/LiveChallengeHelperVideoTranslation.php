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
 * @ORM\Table(name="live_challenge_helper_video_translations")
 */
class LiveChallengeHelperVideoTranslation extends AbstractTranslation
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    private UuidInterface $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Challenge\LiveChallengeHelperVideo")
     */
    private LiveChallengeHelperVideo $helperVideo;
    /**
     * @ORM\Column(type="string")
     */
    private ?string $title;
    /**
     * @ORM\Column(type="text")
     */
    private ?string $description;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Video")
     */
    private ?Video $video;

    public function __construct(
        LiveChallengeHelperVideo $helperVideo,
        Language $language,
        ?string $title,
        ?string $description,
        ?Video $video
    ) {
        $this->id = Uuid::uuid4();
        $this->helperVideo = $helperVideo;
        $this->language = $language;
        $this->title = $title;
        $this->description = $description;
        $this->video = $video;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setVideo(?Video $video): void
    {
        $this->video = $video;
    }
}
