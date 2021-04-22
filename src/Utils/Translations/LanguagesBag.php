<?php

namespace App\Utils\Translations;

use App\Model\Entity\Language;
use App\Model\Repository\Languages;

final class LanguagesBag
{
    private Languages $languagesRepository;
    /** @var Language[] */
    private array $languages;

    public function __construct(Languages $languagesRepository)
    {
        $this->languagesRepository = $languagesRepository;
    }

    /** @return Language[] */
    public function all(): array
    {
        if (!isset($this->languages)) {
            $this->languages = $this->languagesRepository->all();
        }

        return $this->languages;
    }

    /** @return Language[] */
    public function withFirst(Language $first): array
    {
        $languages = $this->all();
        usort(
            $languages,
            fn (Language $a, Language $b) => $b === $first ? 1 : 0
        );

        return $languages;
    }
}
