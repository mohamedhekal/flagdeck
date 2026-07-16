<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Http\Resources;

use Hekal\FlagDeck\DTOs\FlagDefinition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FlagDefinition
 */
final class FlagResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var FlagDefinition $flag */
        $flag = $this->resource;

        return $flag->toArray();
    }
}
