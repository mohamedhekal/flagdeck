<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Http\Controllers;

use Hekal\FlagDeck\DTOs\EvaluationContext;
use Hekal\FlagDeck\Exceptions\FlagNotFoundException;
use Hekal\FlagDeck\Http\Requests\StoreFlagRequest;
use Hekal\FlagDeck\Http\Requests\UpdateFlagRequest;
use Hekal\FlagDeck\Http\Resources\FlagResource;
use Hekal\FlagDeck\Services\FeatureManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

final class FlagController extends Controller
{
    public function __construct(
        private readonly FeatureManager $features,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return FlagResource::collection($this->features->all());
    }

    public function store(StoreFlagRequest $request): JsonResponse
    {
        $flag = $this->features->create($request->validated());

        return (new FlagResource($flag))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $key): FlagResource
    {
        $flag = $this->features->get($key);

        if ($flag === null) {
            throw FlagNotFoundException::forKey($key);
        }

        return new FlagResource($flag);
    }

    public function update(UpdateFlagRequest $request, string $key): FlagResource
    {
        $flag = $this->features->update($key, $request->validated());

        return new FlagResource($flag);
    }

    public function destroy(string $key): JsonResponse
    {
        $this->features->delete($key);

        return response()->json(null, 204);
    }

    public function evaluate(Request $request, string $key): JsonResponse
    {
        if (! config('flagdeck.api.allow_evaluate')) {
            abort(404);
        }

        $context = EvaluationContext::fromArray([
            'user_id' => $request->query('user_id'),
            'tenant_id' => $request->query('tenant_id'),
            'anonymous_id' => $request->query('anonymous_id'),
            'environment' => $request->query('environment'),
        ]);

        return response()->json([
            'key' => $key,
            'active' => $this->features->active($key, $context),
        ]);
    }
}
