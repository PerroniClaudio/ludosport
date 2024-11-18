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

    /** Accademie */

    Route::group([], function () {
        Route::get('/academies/all', [App\Http\Controllers\AcademyController::class, 'all'])->name('technician.academies.all');
        Route::get('/academies/search', [App\Http\Controllers\AcademyController::class, 'search'])->name('technician.academies.search');
    });

    /** Scuole */

    Route::group([], function () {
        Route::get('/schools/all', [App\Http\Controllers\SchoolController::class, 'all'])->name('technician.schools.all');
    });


    /** Clan */

    Route::group([], function () {
        Route::get('/courses/all', [App\Http\Controllers\ClanController::class, 'all'])->name('technician.clans.all');
        Route::get('/courses/search', [App\Http\Controllers\ClanController::class, 'search'])->name('technician.clans.search');
    });

    /** Events */

    Route::group([], function () {
        Route::get('/events/all', [App\Http\Controllers\EventController::class, 'all'])->name('technician.events.all');
        Route::get('/events/search', [App\Http\Controllers\EventController::class, 'search'])->name('technician.events.search');
        Route::get('events', [App\Http\Controllers\EventController::class, 'index'])->name('technician.events.index');
        Route::get('dashboard-events', [App\Http\Controllers\EventController::class, 'dashboardEvents'])->name('technician.events.dashboard');
        // Route::get('events/create', [App\Http\Controllers\EventController::class, 'create'])->name('technician.events.create');
        // Route::post('events/create', [App\Http\Controllers\EventController::class, 'store'])->name('technician.events.store');
        Route::get('events/{event}', [App\Http\Controllers\EventController::class, 'edit'])->name('technician.events.edit');
        // Route::post('events/{event}/description', [App\Http\Controllers\EventController::class, 'saveDescription'])->name('technician.events.save.description');
        // Route::post('events/{event}/location', [App\Http\Controllers\EventController::class, 'saveLocation'])->name('technician.events.save.location');
        // Route::put('events/{event}/thumbnail', [App\Http\Controllers\EventController::class, 'updateThumbnail'])->name('technician.events.update.thumbnail');
        Route::post('events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name('technician.events.update');

        Route::get('events/{event}/participants', [App\Http\Controllers\EventController::class, 'participants'])->name('technician.events.participants');
        Route::get('events/{event}/available-users', [App\Http\Controllers\EventController::class, 'available'])->name('technician.events.available');
        Route::post('add-participants', [App\Http\Controllers\EventController::class, 'selectParticipants'])->name('technician.events.participants.add');
        Route::get('events/{event}/participants/export', [App\Http\Controllers\EventController::class, 'exportParticipants'])->name('technician.events.participants.export');
        Route::get('events/{event}/personnel', [App\Http\Controllers\EventController::class, 'personnel'])->name('technician.events.personnel');

        Route::get('/event-types/json', [App\Http\Controllers\EventTypeController::class, 'list'])->name('technician.events.types');
    });

    /** Imports */

    Route::group([], function () {
        Route::get('/imports', [App\Http\Controllers\ImportController::class, 'index'])->name('technician.imports.index');
        Route::get('/imports/create', [App\Http\Controllers\ImportController::class, 'create'])->name('technician.imports.create');
        // Route::delete('/imports/{import}', [App\Http\Controllers\ImportController::class, 'destroy'])->name('technician.imports.disable');

        Route::post('/imports', [App\Http\Controllers\ImportController::class, 'store'])->name('technician.imports.store');
        Route::post('/imports/{import}', [App\Http\Controllers\ImportController::class, 'update'])->name('technician.imports.update');
        Route::get('/imports/{import}/download', [App\Http\Controllers\ImportController::class, 'download'])->name('technician.imports.download');

        Route::get('/imports/template', [App\Http\Controllers\ImportController::class, 'template'])->name('technician.imports.template');
    });

    /** Exports */

    Route::group([], function () {
        Route::get('/exports', [App\Http\Controllers\ExportController::class, 'index'])->name('technician.exports.index');
        Route::get('/exports/create', [App\Http\Controllers\ExportController::class, 'create'])->name('technician.exports.create');
        Route::get('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('technician.exports.download');
        // Route::delete('/exports/{export}', [App\Http\Controllers\ExportController::class, 'destroy'])->name('technician.exports.disable');

        Route::post('/exports', [App\Http\Controllers\ExportController::class, 'store'])->name('technician.exports.store');
        Route::post('/exports/{export}', [App\Http\Controllers\ExportController::class, 'update'])->name('technician.exports.update');
        // Route::post('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('technician.exports.download');
    });

    Route::get('announcements', [App\Http\Controllers\AnnouncementController::class, 'ownRoles'])->name('technician.announcements.index');
    Route::post('announcements/{announcement}/seen', [App\Http\Controllers\AnnouncementController::class, 'setSeen'])->name('technician.announcements.seen');

    Route::get('/schools/academy', [App\Http\Controllers\SchoolController::class, 'getByAcademy'])->name('technician.schools.academy');
    Route::get('/courses/school', [App\Http\Controllers\ClanController::class, 'getBySchool'])->name('technician.clans.school');
});
