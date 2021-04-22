<?php

namespace App\Utils\Translations\Model;

use App\Model\Entity\Language;
use Doctrine\ORM\Mapping as ORM;

abstract class AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Language")
     */
    protected Language $language;

    final public function getLanguage(): Language
    {
        return $this->language;
    }

    final public function setLanguage(Language $language): void
    {
        $this->language = $language;
    }
}
