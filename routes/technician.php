<?php

use Illuminate\Support\Facades\Route;

Route::prefix('technician')->middleware('auth')->middleware('role:tecnico')->group(function () {
    Route::get('events', [App\Http\Controllers\EventController::class, 'index'])->name('technician.events.index');
    // Route::get('events/{event}', [App\Http\Controllers\EventController::class, 'show'])->name();
    // Route::post('events', [App\Http\Controllers\EventController::class, 'store'])->name();
    // Route::put('events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name();
    // Route::delete('events/{event}', [App\Http\Controllers\EventController::class, 'destroy'])->name();
});
