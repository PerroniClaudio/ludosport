<?php

use Illuminate\Support\Facades\Route;

// Route::prefix('instructor')->middleware('auth')->middleware('role:tecnico')->group(function () {
Route::prefix('instructor')->middleware('auth')->middleware('role:admin,instructor')->group(function () {

    /** Users */

    Route::group([], function () {
        Route::get('/users', [App\Http\Controllers\PaginatedUserController::class, 'index'])->name('instructor.users.index');
        Route::get('/users/filter', [App\Http\Controllers\UserController::class, 'filter'])->name('instructor.users.filter');
        Route::get('/users/filter/result', [App\Http\Controllers\UserController::class, 'filterResult'])->name('instructor.users.filter.result');
        Route::get('/users/search', [App\Http\Controllers\UserController::class, 'search'])->name('instructor.users.search');
        Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('instructor.users.create');
        Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'edit'])->name('instructor.users.edit');

        Route::post('/users/{user}/languages', [App\Http\Controllers\UserController::class, 'languages'])->name('instructor.users.languages.store');
        Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('instructor.users.store');
        Route::post('/users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('instructor.users.update');
        Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('instructor.users.disable');
        Route::get('/nation/{nation}/academies', [App\Http\Controllers\NationController::class, 'academies'])->name('instructor.nation.academies.index');
        Route::get('/academy/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'schools'])->name('instructor.academies.schools.index');
        Route::put('/users/{user}/picture', [App\Http\Controllers\UserController::class, 'picture'])->name('instructor.users.picture.update');
    });

    /** Events */

    Route::group([], function () {
        Route::get('events', [App\Http\Controllers\EventController::class, 'index'])->name('instructor.events.index');
        Route::get('events/create', [App\Http\Controllers\EventController::class, 'create'])->name('instructor.events.create');
        Route::post('events/create', [App\Http\Controllers\EventController::class, 'store'])->name('instructor.events.store');
        Route::get('events/{event}', [App\Http\Controllers\EventController::class, 'edit'])->name('instructor.events.edit');
        Route::post('events/{event}/description', [App\Http\Controllers\EventController::class, 'saveDescription'])->name('instructor.events.save.description');
        Route::post('events/{event}/location', [App\Http\Controllers\EventController::class, 'saveLocation'])->name('instructor.events.save.location');
        Route::put('events/{event}/thumbnail', [App\Http\Controllers\EventController::class, 'updateThumbnail'])->name('instructor.events.update.thumbnail');
        Route::post('events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name('instructor.events.update');

        Route::get('events/{event}/participants', [App\Http\Controllers\EventController::class, 'participants'])->name('instructor.events.participants');
        Route::get('events/{event}/available-users', [App\Http\Controllers\EventController::class, 'available'])->name('instructor.events.available');

        Route::post('add-participants', [App\Http\Controllers\EventController::class, 'selectParticipants'])->name('instructor.events.participants.add');
        Route::get('events/{event}/participants/export', [App\Http\Controllers\EventController::class, 'exportParticipants'])->name('instructor.events.participants.export');
    });

    /** Clan */

    Route::group([], function () {
        Route::get('/courses', [App\Http\Controllers\ClanController::class, 'index'])->name('instructor.clans.index');
        Route::get('/courses/create', [App\Http\Controllers\ClanController::class, 'create'])->name('instructor.clans.create');

        Route::get('/courses/all', [App\Http\Controllers\ClanController::class, 'all'])->name('instructor.clans.all');
        Route::get('/courses/search', [App\Http\Controllers\ClanController::class, 'search'])->name('instructor.clans.search');
        Route::get('/courses/school', [App\Http\Controllers\ClanController::class, 'getBySchool'])->name('instructor.clans.school');

        Route::get('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'edit'])->name('instructor.clans.edit');
        Route::delete('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'destroy'])->name('instructor.clans.disable');

        Route::post('/courses', [App\Http\Controllers\ClanController::class, 'store'])->name('instructor.clans.store');
        Route::post('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'update'])->name('instructor.clans.update');
        Route::post('/courses/{clan}/user/create', [App\Http\Controllers\UserController::class, 'storeForClan'])->name('instructor.clans.users.create');
        Route::post('/courses/{clan}/add-instructors', [App\Http\Controllers\ClanController::class, 'addInstructor'])->name('instructor.clans.instructors.store');
        Route::post('/courses/{clan}/remove-instructors', [App\Http\Controllers\ClanController::class, 'removeInstructor'])->name('instructor.clans.instructors.remove');
        Route::post('/courses/{clan}/add-athlete', [App\Http\Controllers\ClanController::class, 'addAthlete'])->name('instructor.clans.athletes.store');
        Route::post('/courses/{clan}/remove-athlete', [App\Http\Controllers\ClanController::class, 'removeAthlete'])->name('instructor.clans.athletes.remove');
    });

    Route::get('announcements', [App\Http\Controllers\AnnouncementController::class, 'ownRoles'])->name('instructor.announcements.index');
    Route::post('announcements/{announcement}/seen', [App\Http\Controllers\AnnouncementController::class, 'setSeen'])->name('instructor.announcements.seen');

    Route::get('/schools/academy', [App\Http\Controllers\SchoolController::class, 'getByAcademy'])->name('instructor.schools.academy');
    Route::get('/courses/school', [App\Http\Controllers\ClanController::class, 'getBySchool'])->name('instructor.clans.school');
});
