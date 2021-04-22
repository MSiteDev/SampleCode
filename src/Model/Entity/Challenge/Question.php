<?php

namespace App\Model\Entity\Challenge;

use App\Model\Entity\Language;
use App\Utils\Translations\Model\AbstractTranslatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @ORM\Table(name="challenge_questions")
 * @method QuestionTranslation getTranslation()
 * @method QuestionTranslation translatedTo(Language $language)
 */
class Question extends AbstractTranslatable
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    private UuidInterface $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Challenge\AbstractChallenge", inversedBy="questions")
     */
    private AbstractChallenge $challenge;
    /**
     * @ORM\OneToMany(targetEntity="QuestionTranslation", mappedBy="question", cascade={"persist", "remove"})
     * @var Collection|QuestionTranslation[]
     */
    private Collection $translations;
    /**
     * @ORM\Column(type="integer")
     */
    private int $ordinal = 0;

    public function __construct(AbstractChallenge $challenge)
    {
        $this->id = Uuid::uuid4();
        $this->challenge = $challenge;
        $this->translations = new ArrayCollection();
    }

    /**
     * @Groups({"course", "challenges", "feedback_created_by_me"})
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function challenge(): AbstractChallenge
    {
        return $this->challenge;
    }

    public function translations(): Collection
    {
        return $this->translations;
    }

    public function setTranslations(Collection $translations): void
    {
        $this->translations = $translations;
    }

    public function setTranslation(Language $language, string $text = ''): void
    {
        if (!$this->hasTranslationFor($language)) {
            $this->translations->add(
                new QuestionTranslation($language, $this, $text)
            );
        } else {
            $this->translatedTo($language)->setText($text);
        }
    }

    public function ordinal(): int
    {
        return $this->ordinal;
    }

    public function setOrdinal(int $order): void
    {
        $this->ordinal = $order;
    }

    /**
     * @Groups({"course", "challenges", "feedback_created_by_me"})
     */
    public function getText(): string
    {
        return $this->getTranslation()->getText();
    }
}
