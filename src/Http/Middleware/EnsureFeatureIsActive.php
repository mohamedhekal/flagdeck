<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Http\Middleware;

use Closure;
use Hekal\FlagDeck\Services\FeatureManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureFeatureIsActive
{
    public function __construct(
        private readonly FeatureManager $features,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $flagKey): Response
    {
        if ($this->features->inactive($flagKey)) {
            abort(
                (int) config('flagdeck.middleware.abort_code', 403),
                (string) config('flagdeck.middleware.abort_message', 'This feature is not available.'),
            );
        }

        return $next($request);
    }
}
