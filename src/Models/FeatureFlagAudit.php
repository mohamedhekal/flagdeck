<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int|null $feature_flag_id
 * @property string $flag_key
 * @property string $action
 * @property array<string, mixed>|null $before
 * @property array<string, mixed>|null $after
 * @property string|null $actor_type
 * @property int|string|null $actor_id
 * @property string|null $ip
 */
class FeatureFlagAudit extends Model
{
    protected $table = 'feature_flag_audits';

    protected $fillable = [
        'feature_flag_id',
        'flag_key',
        'action',
        'before',
        'after',
        'actor_type',
        'actor_id',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
        ];
    }

    /**
     * @return BelongsTo<FeatureFlag, $this>
     */
    public function flag(): BelongsTo
    {
        return $this->belongsTo(FeatureFlag::class, 'feature_flag_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
