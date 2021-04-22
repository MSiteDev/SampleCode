<?php

namespace App\Utils\Translations\Selectors;

use App\Model\Entity\Language;
use App\Utils\Translations\LanguagesBag;
use App\Utils\Translations\LanguageSelector;

final class DefinedLanguageSelector implements LanguageSelector
{
    private LanguagesBag $languagesBag;
    /** @var Language[] */
    private array $languages;
    private Language $default;

    public function __construct(LanguagesBag $languagesBag, Language $default)
    {
        $this->languagesBag = $languagesBag;
        $this->default = $default;
    }

    public function languages(): array
    {
        if (!isset($this->languages)) {
            $this->languages = $this->languagesBag->withFirst($this->default());
        }

        return $this->languages;
    }

    public function default(): Language
    {
        return $this->default;
    }
}
