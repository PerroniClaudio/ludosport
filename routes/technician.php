<?php

use Illuminate\Support\Facades\Route;

Route::prefix('technician')->middleware('auth')->middleware('role:tecnico')->group(function () {
    Route::get('events', [App\Http\Controllers\EventController::class, 'index'])->name('technician.events.index');
    Route::get('events/create', [App\Http\Controllers\EventController::class, 'create'])->name('technician.events.create');
    Route::post('events/create', [App\Http\Controllers\EventController::class, 'store'])->name('technician.events.store');
    Route::get('events/{event}', [App\Http\Controllers\EventController::class, 'edit'])->name('technician.events.edit');
    Route::post('events/{event}/description', [App\Http\Controllers\EventController::class, 'saveDescription'])->name('technician.events.save.description');
    Route::post('events/{event}/location', [App\Http\Controllers\EventController::class, 'saveLocation'])->name('technician.events.save.location');
    // Route::post('events', [App\Http\Controllers\EventController::class, 'store'])->name();
    // Route::put('events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name();
    // Route::delete('events/{event}', [App\Http\Controllers\EventController::class, 'destroy'])->name();
});
