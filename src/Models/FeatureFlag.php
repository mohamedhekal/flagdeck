<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string|null $description
 * @property bool $is_enabled
 * @property int|null $percentage
 * @property array<int, string>|null $environments
 * @property array<int, string>|null $include_user_ids
 * @property array<int, string>|null $exclude_user_ids
 * @property array<int, string>|null $include_tenant_ids
 * @property array<int, string>|null $exclude_tenant_ids
 * @property array<string, mixed>|null $metadata
 */
class FeatureFlag extends Model
{
    protected $table = 'feature_flags';

    protected $fillable = [
        'key',
        'name',
        'description',
        'is_enabled',
        'percentage',
        'environments',
        'include_user_ids',
        'exclude_user_ids',
        'include_tenant_ids',
        'exclude_tenant_ids',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'percentage' => 'integer',
            'environments' => 'array',
            'include_user_ids' => 'array',
            'exclude_user_ids' => 'array',
            'include_tenant_ids' => 'array',
            'exclude_tenant_ids' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * @return HasMany<FeatureFlagAudit, $this>
     */
    public function audits(): HasMany
    {
        return $this->hasMany(FeatureFlagAudit::class);
    }
}
