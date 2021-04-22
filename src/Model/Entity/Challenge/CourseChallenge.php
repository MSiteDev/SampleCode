<?php

namespace App\Model\Entity\Challenge;

use App\Model\Entity\Chapter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 */
class CourseChallenge extends AbstractChallenge
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Chapter", inversedBy="challenges")
     */
    private ?Chapter $chapter = null;

    public function __construct(bool $isP2P)
    {
        parent::__construct();
        $this->isP2P = $isP2P;
    }

    /**
     * @Groups({"challenges"})
     */
    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function setChapter(?Chapter $chapter): void
    {
        $this->chapter = $chapter;
    }

    public function setIsP2P(bool $isP2P): void
    {
        $this->isP2P = $isP2P;
    }
}
