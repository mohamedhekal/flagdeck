<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\DTOs;

final readonly class FlagDefinition
{
    /**
     * @param  list<string>|null  $environments
     * @param  list<string>|null  $includeUserIds
     * @param  list<string>|null  $excludeUserIds
     * @param  list<string>|null  $includeTenantIds
     * @param  list<string>|null  $excludeTenantIds
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $key,
        public string $name,
        public bool $isEnabled,
        public ?string $description = null,
        public ?int $percentage = null,
        public ?array $environments = null,
        public ?array $includeUserIds = null,
        public ?array $excludeUserIds = null,
        public ?array $includeTenantIds = null,
        public ?array $excludeTenantIds = null,
        public ?array $metadata = null,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            key: (string) $row['key'],
            name: (string) ($row['name'] ?? $row['key']),
            isEnabled: (bool) ($row['is_enabled'] ?? false),
            description: isset($row['description']) ? (string) $row['description'] : null,
            percentage: isset($row['percentage']) ? (int) $row['percentage'] : null,
            environments: self::stringList($row['environments'] ?? null),
            includeUserIds: self::stringList($row['include_user_ids'] ?? null),
            excludeUserIds: self::stringList($row['exclude_user_ids'] ?? null),
            includeTenantIds: self::stringList($row['include_tenant_ids'] ?? null),
            excludeTenantIds: self::stringList($row['exclude_tenant_ids'] ?? null),
            metadata: is_array($row['metadata'] ?? null) ? $row['metadata'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'is_enabled' => $this->isEnabled,
            'percentage' => $this->percentage,
            'environments' => $this->environments,
            'include_user_ids' => $this->includeUserIds,
            'exclude_user_ids' => $this->excludeUserIds,
            'include_tenant_ids' => $this->includeTenantIds,
            'exclude_tenant_ids' => $this->excludeTenantIds,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * @return list<string>|null
     */
    private static function stringList(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($value)) {
            return null;
        }

        $list = [];
        foreach ($value as $item) {
            $list[] = (string) $item;
        }

        return $list;
    }
}
