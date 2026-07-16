<?php

declare(strict_types=1);

use Hekal\FlagDeck\Http\Controllers\FlagController;
use Illuminate\Support\Facades\Route;

Route::get('flags', [FlagController::class, 'index'])->name('flagdeck.flags.index');
Route::post('flags', [FlagController::class, 'store'])->name('flagdeck.flags.store');
Route::get('flags/{key}', [FlagController::class, 'show'])->name('flagdeck.flags.show');
Route::put('flags/{key}', [FlagController::class, 'update'])->name('flagdeck.flags.update');
Route::patch('flags/{key}', [FlagController::class, 'update'])->name('flagdeck.flags.patch');
Route::delete('flags/{key}', [FlagController::class, 'destroy'])->name('flagdeck.flags.destroy');
Route::get('evaluate/{key}', [FlagController::class, 'evaluate'])->name('flagdeck.evaluate');
