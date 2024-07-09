<?php

use Illuminate\Support\Facades\Route;

Route::prefix('athlete')->middleware('auth')->middleware('role:athlete')->middleware('role:admin')->group(function () {
    Route::get('announcements', [App\Http\Controllers\AnnouncementController::class, 'athlete'])->name('athlete.announcements.index');
    Route::post('announcements/{announcement}/seen', [App\Http\Controllers\AnnouncementController::class, 'setSeen'])->name('athlete.announcements.seen');
});
