<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateFlagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_enabled' => ['sometimes', 'boolean'],
            'percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'environments' => ['nullable', 'array'],
            'environments.*' => ['string'],
            'include_user_ids' => ['nullable', 'array'],
            'include_user_ids.*' => ['string'],
            'exclude_user_ids' => ['nullable', 'array'],
            'exclude_user_ids.*' => ['string'],
            'include_tenant_ids' => ['nullable', 'array'],
            'include_tenant_ids.*' => ['string'],
            'exclude_tenant_ids' => ['nullable', 'array'],
            'exclude_tenant_ids.*' => ['string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
