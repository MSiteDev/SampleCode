<?php

namespace App\Model\Exception;

final class LanguageNotFound extends \DomainException
{
    public function __construct(string $languageId)
    {
        parent::__construct(sprintf('Language with "%s" not found', $languageId));
    }
}
