<?php

namespace App\Utils\Translations\Model;

use App\Model\Entity\Language;
use App\Utils\Translations\Exception\TranslationNotFound;
use App\Utils\Translations\Selectors\ComposedLanguageSelector;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\Proxy;
use RuntimeException;

abstract class AbstractTranslatable
{
    protected ComposedLanguageSelector $languageSelector;

    /** @return Collection|AbstractTranslation[] */
    abstract public function translations(): Collection;

    final public function getTranslation(): AbstractTranslation
    {
        foreach ($this->getLanguageSelector()->languages() as $language) {
            try {
                return $this->translatedTo($language);
            } catch (TranslationNotFound $e) {
                continue;
            }
        }

        throw new TranslationNotFound();
    }

    final public function hasTranslationFor(Language $language): bool
    {
        foreach ($this->translations() as $translation) {
            if ($translation->getLanguage()->equals($language)) {
                return true;
            }
        }

        return false;
    }

    final public function translatedTo(Language $language): AbstractTranslation
    {
        foreach ($this->translations() as $translation) {
            if ($translation->getLanguage()->equals($language)) {
                return $translation;
            }
        }

        throw new TranslationNotFound();
    }

    final public function setLanguageSelector(ComposedLanguageSelector $languageSelector): void
    {
        if (isset($this->languageSelector) && $this->languageSelector !== $languageSelector) {
            throw new RuntimeException('Cannot override language selector');
        }
        $this->languageSelector = $languageSelector;
    }

    final public function getLanguageSelector(): ComposedLanguageSelector
    {
        if (!isset($this->languageSelector)) {
            if ($this instanceof Proxy) {
                $this->__load();
            } else {
                throw new RuntimeException('Language selector not injected.');
            }
        }

        return $this->languageSelector;
    }

    abstract public function setTranslation(Language $language): void;
}
