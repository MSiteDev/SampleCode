<?php

namespace App\Utils\Translations\Selectors;

use App\Model\Entity\Language;
use App\Utils\Translations\LanguagesBag;

final class DefinedSelectorFactory
{
    private LanguagesBag $languagesBag;
    /** @var DefinedLanguageSelector[] */
    private array $generated = [];

    public function __construct(LanguagesBag $languagesBag)
    {
        $this->languagesBag = $languagesBag;
    }

    public function generate(Language $language): DefinedLanguageSelector
    {
        $langId = $language->getId()->toString();

        if (!array_key_exists($langId, $this->generated)) {
            $this->generated[$langId] = $this->create($language);
        }

        return $this->generated[$langId];
    }

    private function create(Language $language): DefinedLanguageSelector
    {
        return new DefinedLanguageSelector($this->languagesBag, $language);
    }
}
