<?php

namespace App\Model\Entity\Challenge;

use App\Model\Entity\Language;
use App\Model\Entity\Partial\HasCoverImages;
use App\Model\Entity\Video;
use App\Utils\Translations\Model\AbstractTranslatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @ORM\Table(name="live_challenge_helper_videos")
 * @method LiveChallengeHelperVideoTranslation getTranslation()
 * @method LiveChallengeHelperVideoTranslation translatedTo(Language $language)
 */
class LiveChallengeHelperVideo extends AbstractTranslatable
{
    use HasCoverImages;

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    private UuidInterface $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Challenge\LiveChallenge")
     */
    private LiveChallenge $challenge;
    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Model\Entity\Challenge\LiveChallengeHelperVideoTranslation",
     *     mappedBy="helperVideo",
     *     cascade={"persist", "remove"}
     * )
     * @var Collection|LiveChallengeHelperVideoTranslation[]
     */
    private Collection $translations;
    /**
     * @ORM\Column(type="integer")
     */
    private int $ordinal = 0;

    public function __construct(LiveChallenge $challenge)
    {
        $this->id = Uuid::uuid4();
        $this->translations = new ArrayCollection();
        $this->challenge = $challenge;
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getTitle(): ?string
    {
        return $this->getTranslation()->getTitle();
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getDescription(): ?string
    {
        return $this->getTranslation()->getDescription();
    }

    /**
     * @Groups({"course", "challenges"})
     */
    public function getVideo(): ?Video
    {
        return $this->getTranslation()->getVideo();
    }

    public function translations(): Collection
    {
        return $this->translations;
    }

    public function setTranslation(
        Language $language,
        string $title = '',
        string $description = '',
        ?Video $video = null,
    ): void {
        if (!$this->hasTranslationFor($language)) {
            $this->translations->add(
                new LiveChallengeHelperVideoTranslation($this, $language, $title, $description, $video)
            );
        } else {
            $translation = $this->translatedTo($language);
            $translation->setTitle($title);
            $translation->setDescription($description);
            $translation->setVideo($video);
        }
    }

    public function getOrdinal(): int
    {
        return $this->ordinal;
    }

    public function setOrdinal(int $ordinal): void
    {
        $this->ordinal = $ordinal;
    }
}
