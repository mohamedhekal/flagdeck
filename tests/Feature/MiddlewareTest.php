<?php

declare(strict_types=1);

use Hekal\FlagDeck\Facades\FlagDeck;
use Illuminate\Support\Facades\Route;

it('allows the request when the feature is active', function () {
    FlagDeck::create([
        'key' => 'ops.panel',
        'name' => 'Ops Panel',
        'is_enabled' => true,
    ]);

    Route::middleware('feature:ops.panel')->get('/flagdeck-test-ops', fn () => response('ok'));

    $this->get('/flagdeck-test-ops')->assertOk();
});

it('aborts when the feature is inactive', function () {
    FlagDeck::create([
        'key' => 'ops.panel',
        'name' => 'Ops Panel',
        'is_enabled' => false,
    ]);

    Route::middleware('feature:ops.panel')->get('/flagdeck-test-ops-denied', fn () => response('ok'));

    $this->get('/flagdeck-test-ops-denied')->assertForbidden();
});
