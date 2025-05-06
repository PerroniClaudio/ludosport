<?php

use Illuminate\Support\Facades\Route;

Route::prefix('manager')->middleware('auth')->middleware('role:admin,manager')->group(function () {
  // Sblocco una route alla volta, mano a mano che le implemento

  /** Users */

  Route::group([], function () {
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('manager.users.index');
    Route::get('/users/filter', [App\Http\Controllers\UserController::class, 'filter'])->name('manager.users.filter');
    Route::get('/users/filter/result', [App\Http\Controllers\UserController::class, 'filterResult'])->name('manager.users.filter.result');
    Route::get('/users/search', [App\Http\Controllers\UserController::class, 'search'])->name('manager.users.search');
    Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('manager.users.create');
    Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'edit'])->name('manager.users.edit');
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('manager.users.store');
    Route::post('/users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('manager.users.update');
    Route::post('/user-roles/{user}', [App\Http\Controllers\UserController::class, 'updateRoles'])->name('manager.users.roles-update');
    Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('manager.users.disable');
    Route::put('/users/{user}/picture', [App\Http\Controllers\UserController::class, 'picture'])->name('manager.users.picture.update');
    // Route::get('/nation/{nation}/academies', [App\Http\Controllers\NationController::class, 'academies'])->name('manager.nation.academies.index');
    // Route::get('/academy/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'schools'])->name('manager.academies.schools.index');
  });

  /** Ruoli */

  Route::group([], function () {
    Route::get('/custom-roles', [App\Http\Controllers\RoleController::class, 'index'])->name('manager.roles.index');
    Route::get('/custom-roles/search', [App\Http\Controllers\RoleController::class, 'search'])->name('manager.roles.search');
    Route::post('/custom-roles/assign', [App\Http\Controllers\RoleController::class, 'assign'])->name('manager.roles.assign');
    Route::post('/custom-roles', [App\Http\Controllers\RoleController::class, 'store'])->name('manager.roles.store');
  });

  /** Accademie */

  Route::group([], function () {
    Route::get('/academies/all', [App\Http\Controllers\AcademyController::class, 'all'])->name('manager.academies.all');
    Route::get('/academies/search', [App\Http\Controllers\AcademyController::class, 'search'])->name('manager.academies.search');
  });

  // Route::get('/academies/{academy}', [App\Http\Controllers\AcademyController::class, 'show'])->name('manager.academies.show');

  /** Scuole */

  Route::get('school', [App\Http\Controllers\SchoolController::class, 'index'])->name('manager.school.index'); //reindirizza all'edit della scuola principale

  Route::group([], function () {
    Route::get('/schools/all', [App\Http\Controllers\SchoolController::class, 'all'])->name('manager.schools.all');
    Route::get('/schools/academy', [App\Http\Controllers\SchoolController::class, 'getByAcademy'])->name('manager.schools.academy');
    // Route::get('/schools', [App\Http\Controllers\SchoolController::class, 'index'])->name('manager.schools.index');
    // Route::get('/schools/create', [App\Http\Controllers\SchoolController::class, 'create'])->name('manager.schools.create');
    // Route::get('/schools/search', [App\Http\Controllers\SchoolController::class, 'search'])->name('manager.schools.search');

    Route::get('/schools/{school}/athletes-data', [App\Http\Controllers\SchoolController::class, 'athletesDataForSchool'])->name('manager.schools.athletes-data');
    Route::get('/schools/{school}/athletes-clan-data', [App\Http\Controllers\SchoolController::class, 'athletesClanDataForSchool'])->name('manager.schools.athletes-school-data');
    Route::get('/schools/{school}/athletes-year-data', [App\Http\Controllers\SchoolController::class, 'getAthletesNumberPerYear'])->name('manager.schools.athletes-year-data');


    Route::get('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'edit'])->name('manager.schools.edit');
    // Route::delete('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'destroy'])->name('manager.schools.disable');
    // Route::post('/schools', [App\Http\Controllers\SchoolController::class, 'store'])->name('manager.schools.store');

    // Route::post('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'update'])->name('manager.schools.update');


    Route::post('/schools/{school}/users/create', [App\Http\Controllers\UserController::class, 'storeForSchool'])->name('manager.schools.users.create');
    Route::post('/schools/{school}/clan/create', [App\Http\Controllers\ClanController::class, 'storeForSchool'])->name('manager.schools.clan.create');

    Route::post('/schools/{school}/clans', [App\Http\Controllers\SchoolController::class, 'addClan'])->name('manager.schools.clans.store');
    Route::post('/schools/{school}/add-personnel', [App\Http\Controllers\SchoolController::class, 'addPersonnel'])->name('manager.schools.personnel.store');
    Route::post('/schools/{school}/remove-personnel', [App\Http\Controllers\SchoolController::class, 'removePersonnel'])->name('manager.schools.personnel.remove');
    Route::post('/schools/{school}/athlete', [App\Http\Controllers\SchoolController::class, 'addAthlete'])->name('manager.schools.athlete.store');
    Route::post('/schools/{school}/remove-athlete', [App\Http\Controllers\SchoolController::class, 'removeAthlete'])->name('manager.schools.athlete.remove');

    Route::post('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'update'])->name('manager.schools.update');
  });


  /** Clan */

  Route::group([], function () {
    Route::get('/courses', [App\Http\Controllers\ClanController::class, 'index'])->name('manager.clans.index');
    Route::get('/courses/school', [App\Http\Controllers\ClanController::class, 'getBySchool'])->name('manager.clans.school');
    Route::get('/courses/all', [App\Http\Controllers\ClanController::class, 'all'])->name('manager.clans.all');

    Route::get('/courses/create', [App\Http\Controllers\ClanController::class, 'create'])->name('manager.clans.create');
    Route::get('/courses/search', [App\Http\Controllers\ClanController::class, 'search'])->name('manager.clans.search');

    Route::get('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'edit'])->name('manager.clans.edit');
    Route::delete('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'destroy'])->name('manager.clans.disable');

    Route::post('/courses', [App\Http\Controllers\ClanController::class, 'store'])->name('manager.clans.store');
    Route::post('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'update'])->name('manager.clans.update');
    Route::post('/courses/{clan}/user/create', [App\Http\Controllers\UserController::class, 'storeForClan'])->name('manager.clans.users.create');
    Route::post('/courses/{clan}/add-instructors', [App\Http\Controllers\ClanController::class, 'addInstructor'])->name('manager.clans.instructors.store');
    Route::post('/courses/{clan}/remove-instructors', [App\Http\Controllers\ClanController::class, 'removeInstructor'])->name('manager.clans.instructors.remove');
    Route::post('/courses/{clan}/add-athlete', [App\Http\Controllers\ClanController::class, 'addAthlete'])->name('manager.clans.athletes.store');
    Route::post('/courses/{clan}/remove-athlete', [App\Http\Controllers\ClanController::class, 'removeAthlete'])->name('manager.clans.athletes.remove');
  });



  /** Eventi */

  // Questo gruppo di route (Eventi) qui commentate sono funzionanti ma il manager non è al momento abilitato al loro utilizzo
  Route::group([], function () {
    Route::get('/events/all', [App\Http\Controllers\EventController::class, 'all'])->name('manager.events.all');
    Route::get('/events/search', [App\Http\Controllers\EventController::class, 'search'])->name('manager.events.search');
    Route::get('/events', [App\Http\Controllers\EventController::class, 'index'])->name('manager.events.index');
    Route::get('/events/calendar', [App\Http\Controllers\EventController::class, 'calendar'])->name('manager.events.calendar');
    // Route::get('/events/create', [App\Http\Controllers\EventController::class, 'create'])->name('manager.events.create');
    Route::get('/events/{event}', [App\Http\Controllers\EventController::class, 'edit'])->name('manager.events.edit');
    // Route::post('/events', [App\Http\Controllers\EventController::class, 'store'])->name('manager.events.store');
    // Route::post('/events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name('manager.events.update');
    // Route::post('events/{event}/description', [App\Http\Controllers\EventController::class, 'saveDescription'])->name('manager.events.save.description');
    // Route::post('events/{event}/location', [App\Http\Controllers\EventController::class, 'saveLocation'])->name('manager.events.save.location');
    // Route::put('events/{event}/thumbnail', [App\Http\Controllers\EventController::class, 'updateThumbnail'])->name('manager.events.update.thumbnail');
    Route::get('events/{event}/participants', [App\Http\Controllers\EventController::class, 'participants'])->name('manager.events.participants');
    Route::get('events/{event}/available-users', [App\Http\Controllers\EventController::class, 'available'])->name('manager.events.available');
    Route::post('add-participants', [App\Http\Controllers\EventController::class, 'selectParticipants'])->name('manager.events.participants.add');
    Route::get('events/{event}/participants/export', [App\Http\Controllers\EventController::class, 'exportParticipants'])->name('manager.events.participants.export');
    Route::get('/event-types/json', [App\Http\Controllers\EventTypeController::class, 'list'])->name('manager.events.types');
    Route::get('events/{event}/personnel', [App\Http\Controllers\EventController::class, 'personnel'])->name('manager.events.personnel');
  });

  /** Imports */

  Route::group([], function () {

    Route::get('/imports', [App\Http\Controllers\ImportController::class, 'index'])->name('manager.imports.index');
    Route::get('/imports/create', [App\Http\Controllers\ImportController::class, 'create'])->name('manager.imports.create');
    // Route::delete('/imports/{import}', [App\Http\Controllers\ImportController::class, 'destroy'])->name('manager.imports.disable');


    Route::post('/imports', [App\Http\Controllers\ImportController::class, 'store'])->name('manager.imports.store');
    Route::post('/imports/{import}', [App\Http\Controllers\ImportController::class, 'update'])->name('manager.imports.update');
    Route::get('/imports/{import}/download', [App\Http\Controllers\ImportController::class, 'download'])->name('manager.imports.download');

    Route::get('/imports/template', [App\Http\Controllers\ImportController::class, 'template'])->name('manager.imports.template');
  });

  /** Exports */

  Route::group([], function () {
    Route::get('/exports', [App\Http\Controllers\ExportController::class, 'index'])->name('manager.exports.index');
    Route::get('/exports/create', [App\Http\Controllers\ExportController::class, 'create'])->name('manager.exports.create');
    Route::get('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('manager.exports.download');
    // Route::delete('/exports/{export}', [App\Http\Controllers\ExportController::class, 'destroy'])->name('manager.exports.disable');

    Route::post('/exports', [App\Http\Controllers\ExportController::class, 'store'])->name('manager.exports.store');
    Route::post('/exports/{export}', [App\Http\Controllers\ExportController::class, 'update'])->name('manager.exports.update');
    // Route::post('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('manager.exports.download');
  });


  /** Rankings and Charts */

  Route::group([], function () {
    // Route::get('/rankings', [App\Http\Controllers\ChartController::class, 'index'])->name('manager.rankings.index');
    // Route::get('/rankings/paginate', [App\Http\Controllers\ChartController::class, 'paginate'])->name('manager.rankings.paginate');
  });

  /** Annunci */

  Route::group([], function () {
    Route::get('announcements', [App\Http\Controllers\AnnouncementController::class, 'ownRoles'])->name('manager.announcements.index');
    Route::post('announcements/{announcement}/seen', [App\Http\Controllers\AnnouncementController::class, 'setSeen'])->name('manager.announcements.seen');
  });
});
