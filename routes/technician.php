<?php

use Illuminate\Support\Facades\Route;

Route::prefix('technician')->middleware('auth')->middleware('role:tecnico')->group(function () {
    Route::get('events', [App\Http\Controllers\EventController::class, 'index'])->name('technician.events.index');
    Route::get('events/create', [App\Http\Controllers\EventController::class, 'create'])->name('technician.events.create');
    Route::post('events/create', [App\Http\Controllers\EventController::class, 'store'])->name('technician.events.store');
    Route::get('events/{event}', [App\Http\Controllers\EventController::class, 'edit'])->name('technician.events.edit');
    Route::post('events/{event}/description', [App\Http\Controllers\EventController::class, 'saveDescription'])->name('technician.events.save.description');
    Route::post('events/{event}/location', [App\Http\Controllers\EventController::class, 'saveLocation'])->name('technician.events.save.location');
    Route::put('events/{event}/thumbnail', [App\Http\Controllers\EventController::class, 'updateThumbnail'])->name('technician.events.update.thumbnail');
    Route::post('events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name('technician.events.update');

    Route::get('events/{event}/participants', [App\Http\Controllers\EventController::class, 'participants'])->name('technician.events.participants');
    Route::get('events/{event}/available-users', [App\Http\Controllers\EventController::class, 'available'])->name('technician.events.available');

    Route::post('add-participants', [App\Http\Controllers\EventController::class, 'selectParticipants'])->name('technician.events.participants.add');
    Route::get('events/{event}/participants/export', [App\Http\Controllers\EventController::class, 'exportParticipants'])->name('technician.events.participants.export');

    Route::get('announcements', [App\Http\Controllers\AnnouncementController::class, 'technician'])->name('technician.announcements.index');
    Route::post('announcements/{announcement}/seen', [App\Http\Controllers\AnnouncementController::class, 'setSeen'])->name('technician.announcements.seen');

    // Route::post('events', [App\Http\Controllers\EventController::class, 'store'])->name();
    // Route::put('events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name();
    // Route::delete('events/{event}', [App\Http\Controllers\EventController::class, 'destroy'])->name();
});
