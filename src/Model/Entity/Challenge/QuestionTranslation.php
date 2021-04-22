<?php

namespace App\Model\Entity\Challenge;

use App\Model\Entity\Language;
use App\Utils\Translations\Model\AbstractTranslation;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="challenge_question_translations")
 */
class QuestionTranslation extends AbstractTranslation
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    private UuidInterface $id;
    /**
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="translations")
     */
    private Question $question;
    /**
     * @ORM\Column(type="text")
     */
    private string $text;

    public function __construct(Language $language, Question $question, string $text)
    {
        $this->id = Uuid::uuid4();
        $this->language = $language;
        $this->question = $question;
        $this->text = $text;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }
}
