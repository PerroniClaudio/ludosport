<?php

use Illuminate\Support\Facades\Route;

// Route::prefix('technician')->middleware('auth')->middleware('role:tecnico')->group(function () {
Route::prefix('technician')->middleware('auth')->middleware('role:admin,technician')->group(function () {

    /** Users */

    Route::group([], function () {
        Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('technician.users.index');
        Route::get('/users/filter', [App\Http\Controllers\UserController::class, 'filter'])->name('technician.users.filter');
        Route::get('/users/filter/result', [App\Http\Controllers\UserController::class, 'filterResult'])->name('technician.users.filter.result');
        Route::get('/users/search', [App\Http\Controllers\UserController::class, 'search'])->name('technician.users.search');
        Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('technician.users.create');
        Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'edit'])->name('technician.users.edit');

        Route::post('/users/{user}/languages', [App\Http\Controllers\UserController::class, 'languages'])->name('technician.users.languages.store');
        Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('technician.users.store');
        Route::post('/users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('technician.users.update');
        Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('technician.users.disable');
        Route::get('/nation/{nation}/academies', [App\Http\Controllers\NationController::class, 'academies'])->name('technician.nation.academies.index');
        Route::get('/academy/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'schools'])->name('technician.academies.schools.index');
        Route::put('/users/{user}/picture', [App\Http\Controllers\UserController::class, 'picture'])->name('technician.users.picture.update');
    });

    /** Events */

    Route::group([], function () {
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
    });

    Route::get('announcements', [App\Http\Controllers\AnnouncementController::class, 'ownRoles'])->name('technician.announcements.index');
    Route::post('announcements/{announcement}/seen', [App\Http\Controllers\AnnouncementController::class, 'setSeen'])->name('technician.announcements.seen');

    Route::get('/schools/academy', [App\Http\Controllers\SchoolController::class, 'getByAcademy'])->name('technician.schools.academy');
    Route::get('/courses/school', [App\Http\Controllers\ClanController::class, 'getBySchool'])->name('technician.clans.school');
});
