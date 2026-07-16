<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Contracts;

use Hekal\FlagDeck\DTOs\FlagDefinition;

interface AuditLoggerInterface
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function log(
        string $action,
        string $flagKey,
        ?FlagDefinition $flag,
        ?array $before,
        ?array $after,
    ): void;
}
