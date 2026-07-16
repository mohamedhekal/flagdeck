<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Exceptions;

use RuntimeException;

class FlagNotFoundException extends RuntimeException
{
    public static function forKey(string $key): self
    {
        return new self("Feature flag [{$key}] was not found.");
    }

    public static function inactive(string $key): self
    {
        return new self("Feature flag [{$key}] is not active for the current context.");
    }
}
