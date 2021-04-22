<?php

namespace App\Model\Repository;

use App\Model\Entity\Language;
use App\Model\Exception\LanguageNotFound;

interface Languages
{
    /** @return Language[] */
    public function all(): array;

    /** @throws LanguageNotFound */
    public function getById(string $id): Language;

    /** @throws LanguageNotFound */
    public function getByShortcut(string $code): Language;
}
