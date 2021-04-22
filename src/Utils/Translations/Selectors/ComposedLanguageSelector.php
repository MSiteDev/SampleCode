<?php

namespace App\Utils\Translations\Selectors;

use App\Model\Entity\Language;
use App\Utils\Translations\Exception\CannotUseSelectorException;
use App\Utils\Translations\LanguageSelector;

final class ComposedLanguageSelector implements LanguageSelector
{
    /** @var LanguageSelector[] */
    private array $selectors;
    private LanguageSelector $selector;
    private ?LanguageSelector $externalTranslator = null;

    public function __construct(LanguageSelector ...$selectors)
    {
        $this->selectors = $selectors;
    }

    private function selector(): LanguageSelector
    {
        if (!is_null($this->externalTranslator)) {
            return $this->externalTranslator;
        }

        if (!isset($this->selector)) {
            foreach ($this->selectors as $selector) {
                try {
                    $selector->languages();
                    $selector->default();

                    return $this->selector = $selector;
                } catch (CannotUseSelectorException $exception) {
                }
            }

            throw new CannotUseSelectorException($this);
        }

        return $this->selector;
    }

    public function languages(): array
    {
        return $this->selector()->languages();
    }

    public function default(): Language
    {
        return $this->selector()->default();
    }

    public function setExternalTranslator(?LanguageSelector $externalTranslator): void
    {
        $this->externalTranslator = $externalTranslator;
    }

    public function resetExternalTranslator(): void
    {
        $this->externalTranslator = null;
    }
}
