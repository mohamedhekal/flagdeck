<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Services;

use Hekal\FlagDeck\DTOs\EvaluationContext;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

final class ContextResolver
{
    public function resolve(?EvaluationContext $override = null): EvaluationContext
    {
        if ($override !== null) {
            return $override->environment !== null
                ? $override
                : $override->withEnvironment($this->environment());
        }

        return new EvaluationContext(
            userId: $this->userId(),
            tenantId: $this->tenantId(),
            anonymousId: $this->anonymousId(),
            environment: $this->environment(),
        );
    }

    private function userId(): ?string
    {
        $custom = config('flagdeck.context.user_id');

        if (is_callable($custom)) {
            $value = $custom();

            return $value !== null ? (string) $value : null;
        }

        /** @var Authenticatable|null $user */
        $user = Auth::user();

        return $user?->getAuthIdentifier() !== null
            ? (string) $user->getAuthIdentifier()
            : null;
    }

    private function tenantId(): ?string
    {
        $custom = config('flagdeck.context.tenant_id');

        if (is_callable($custom)) {
            $value = $custom();

            return $value !== null ? (string) $value : null;
        }

        // Common conventions — override via config for your tenancy package.
        if (function_exists('tenant') && tenant()) {
            $id = tenant()->getTenantKey();

            return $id !== null ? (string) $id : null;
        }

        if (app()->bound('currentTenant')) {
            $tenant = app('currentTenant');
            $id = is_object($tenant) && method_exists($tenant, 'getKey')
                ? $tenant->getKey()
                : (is_object($tenant) && isset($tenant->id) ? $tenant->id : null);

            return $id !== null ? (string) $id : null;
        }

        return null;
    }

    private function anonymousId(): ?string
    {
        $custom = config('flagdeck.context.anonymous_id');

        if (is_callable($custom)) {
            $value = $custom();

            return $value !== null ? (string) $value : null;
        }

        $ip = Request::ip();

        return $ip !== null ? 'ip:'.$ip : null;
    }

    private function environment(): string
    {
        return (string) config('flagdeck.environment', config('app.env', 'production'));
    }
}
