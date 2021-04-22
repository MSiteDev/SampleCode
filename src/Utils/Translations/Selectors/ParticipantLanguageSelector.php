<?php

namespace App\Utils\Translations\Selectors;

use App\Model\Entity\Language;
use App\Utils\Translations\Exception\CannotUseSelectorException;
use App\Utils\Translations\LanguagesBag;
use App\Utils\Translations\LanguageSelector;
use App\Utils\User\LoggedUser;

final class ParticipantLanguageSelector implements LanguageSelector
{
    private LanguagesBag $sharedLanguages;
    /** @var Language[] */
    private array $languages;
    private LoggedUser $loggedUser;
    private Language $default;

    public function __construct(LanguagesBag $sharedLanguages, LoggedUser $loggedUser)
    {
        $this->sharedLanguages = $sharedLanguages;
        $this->loggedUser = $loggedUser;
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
            if (!$this->loggedUser->isLoggedIn()) {
                throw new CannotUseSelectorException($this);
            }
            $default = $this->loggedUser->getUser()->getLanguage();
            if ($default === null) {
                throw new CannotUseSelectorException($this);
            }

            $this->default = $default;
        }

        return $this->default;
    }
}
