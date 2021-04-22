<?php

namespace App\Utils\Translations\Selectors;

use App\Model\Entity\Language;
use App\Utils\Translations\Exception\CannotUseSelectorException;
use App\Utils\Translations\LanguagesBag;
use App\Utils\Translations\LanguageSelector;

final class DefaultLanguageSelector implements LanguageSelector
{
    private LanguagesBag $sharedLanguages;
    private Language $default;
    /** @var Language[] */
    private array $languages;

    public function __construct(LanguagesBag $sharedLanguages)
    {
        $this->sharedLanguages = $sharedLanguages;
    }

    public function languages(): array
    {
        if (!isset($this->languages)) {
            $this->languages = $this->sharedLanguages->all();
        }

        return $this->languages;
    }

    public function default(): Language
    {
        if (!isset($this->default)) {
            $languages = $this->sharedLanguages->all();
            $default = reset($languages);
            if (!$default instanceof Language) {
                throw new CannotUseSelectorException($this);
            }

            $this->default = $default;
        }

        return $this->default;
    }
}
