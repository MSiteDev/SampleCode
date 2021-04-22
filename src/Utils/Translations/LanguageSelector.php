<?php

namespace App\Utils\Translations;

use App\Model\Entity\Language;

interface LanguageSelector
{
    /** @return Language[] */
    public function languages(): array;

    public function default(): Language;
}
