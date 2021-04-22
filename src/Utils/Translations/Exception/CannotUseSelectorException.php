<?php

namespace App\Utils\Translations\Exception;

use App\Utils\Translations\LanguageSelector;
use RuntimeException;

final class CannotUseSelectorException extends RuntimeException
{
    public function __construct(LanguageSelector $selector)
    {
        parent::__construct(
            sprintf('Cannot use "%s" selector.', get_class($selector))
        );
    }
}
