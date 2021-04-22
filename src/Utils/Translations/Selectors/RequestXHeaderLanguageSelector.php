<?php

namespace App\Utils\Translations\Selectors;

use App\Model\Entity\Language;
use App\Utils\Translations\Exception\CannotUseSelectorException;
use App\Utils\Translations\LanguagesBag;
use App\Utils\Translations\LanguageSelector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestXHeaderLanguageSelector implements LanguageSelector
{
    private const HEADER_NAME = 'X-SELECTED-LANG';
    private RequestStack $requestStack;
    private LanguagesBag $sharedLanguages;
    private Language $default;
    /** @var Language[] */
    private array $languages;

    public function __construct(LanguagesBag $languages, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->sharedLanguages = $languages;
    }

    public function languages(): array
    {
        if (!isset($this->languages)) {
            $this->languages = $this->sharedLanguages->withFirst($this->default());
        }

        return $this->languages;
    }

    public function default(): Language
    {
        if (!isset($this->default)) {
            $request = $this->requestStack->getMasterRequest();
            if (!$request instanceof Request) {
                throw new CannotUseSelectorException($this);
            }
            if (!$this->requestStack->getMasterRequest()->headers->has(self::HEADER_NAME)) {
                throw new CannotUseSelectorException($this);
            }

            $headerCode = strtolower($this->requestStack->getMasterRequest()->headers->get(self::HEADER_NAME));
            $filtered = array_filter($this->sharedLanguages->all(), function (Language $language) use ($headerCode) {
                return strtolower($language->getShortcut()) === $headerCode;
            });

            $default = reset($filtered);

            if (!$default instanceof Language) {
                throw new CannotUseSelectorException($this);
            }

            $this->default = $default;
        }

        return $this->default;
    }
}
