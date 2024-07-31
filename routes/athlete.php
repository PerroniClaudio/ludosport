<?php

use Illuminate\Support\Facades\Route;

Route::prefix('athlete')->middleware('auth')->middleware('role:admin,athlete')->group(function () {
    Route::get('announcements', [App\Http\Controllers\AnnouncementController::class, 'ownRoles'])->name('athlete.announcements.index');
    Route::post('announcements/{announcement}/seen', [App\Http\Controllers\AnnouncementController::class, 'setSeen'])->name('athlete.announcements.seen');
});
