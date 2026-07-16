<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Exceptions;

use InvalidArgumentException;

class InvalidFlagConfigurationException extends InvalidArgumentException
{
    public static function because(string $reason): self
    {
        return new self($reason);
    }
}
