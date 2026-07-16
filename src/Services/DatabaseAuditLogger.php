<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Services;

use Hekal\FlagDeck\Contracts\AuditLoggerInterface;
use Hekal\FlagDeck\DTOs\FlagDefinition;
use Hekal\FlagDeck\Models\FeatureFlag;
use Hekal\FlagDeck\Models\FeatureFlagAudit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

final class DatabaseAuditLogger implements AuditLoggerInterface
{
    public function __construct(
        private readonly bool $enabled = true,
    ) {}

    public function log(
        string $action,
        string $flagKey,
        ?FlagDefinition $flag,
        ?array $before,
        ?array $after,
    ): void {
        if (! $this->enabled) {
            return;
        }

        $flagId = null;
        if ($flag !== null) {
            $flagId = FeatureFlag::query()->where('key', $flag->key)->value('id');
        }

        /** @var Authenticatable|null $actor */
        $actor = Auth::user();

        FeatureFlagAudit::query()->create([
            'feature_flag_id' => $flagId,
            'flag_key' => $flagKey,
            'action' => $action,
            'before' => $before,
            'after' => $after,
            'actor_type' => $actor ? $actor::class : null,
            'actor_id' => $actor?->getAuthIdentifier(),
            'ip' => Request::ip(),
        ]);
    }
}
