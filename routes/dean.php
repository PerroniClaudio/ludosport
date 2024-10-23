<?php

use Illuminate\Support\Facades\Route;

Route::prefix('dean')->middleware('auth')->middleware('role:admin,dean')->group(function () {
  // Sblocco una route alla volta, mano a mano che le implemento

  /** Users */

  Route::group([], function () {
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('dean.users.index');
    Route::get('/users/filter', [App\Http\Controllers\UserController::class, 'filter'])->name('dean.users.filter');
    Route::get('/users/filter/result', [App\Http\Controllers\UserController::class, 'filterResult'])->name('dean.users.filter.result');
    Route::get('/users/search', [App\Http\Controllers\UserController::class, 'search'])->name('dean.users.search');
    Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('dean.users.create');
    Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'edit'])->name('dean.users.edit');
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('dean.users.store');
    Route::post('/users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('dean.users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('dean.users.disable');
    Route::put('/users/{user}/picture', [App\Http\Controllers\UserController::class, 'picture'])->name('dean.users.picture.update');
    // Route::get('/nation/{nation}/academies', [App\Http\Controllers\NationController::class, 'academies'])->name('dean.nation.academies.index');
    // Route::get('/academy/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'schools'])->name('dean.academies.schools.index');
  });

  /** Ruoli */

  Route::group([], function () {
    Route::get('/custom-roles', [App\Http\Controllers\RoleController::class, 'index'])->name('dean.roles.index');
    Route::get('/custom-roles/search', [App\Http\Controllers\RoleController::class, 'search'])->name('dean.roles.search');
    Route::post('/custom-roles/assign', [App\Http\Controllers\RoleController::class, 'assign'])->name('dean.roles.assign');
    Route::post('/custom-roles', [App\Http\Controllers\RoleController::class, 'store'])->name('dean.roles.store');
  });

  /** Accademie */

  Route::group([], function () {
    Route::get('/academies/all', [App\Http\Controllers\AcademyController::class, 'all'])->name('dean.academies.all');
    Route::get('/academies/search', [App\Http\Controllers\AcademyController::class, 'search'])->name('dean.academies.search');
  });

  /** Eventi */

  Route::group([], function () {
    Route::get('/events/all', [App\Http\Controllers\EventController::class, 'all'])->name('dean.events.all');
    Route::get('/events/search', [App\Http\Controllers\EventController::class, 'search'])->name('dean.events.search');
  });

  // Route::get('/academies/{academy}', [App\Http\Controllers\AcademyController::class, 'show'])->name('dean.academies.show');

  /** Scuole */

  Route::get('school', [App\Http\Controllers\SchoolController::class, 'index'])->name('dean.school.index'); //reindirizza all'edit della scuola principale

  Route::group([], function () {
    Route::get('/schools/all', [App\Http\Controllers\SchoolController::class, 'all'])->name('dean.schools.all');
    Route::get('/schools/academy', [App\Http\Controllers\SchoolController::class, 'getByAcademy'])->name('dean.schools.academy');

    Route::get('/schools/{school}/athletes-data', [App\Http\Controllers\SchoolController::class, 'athletesDataForSchool'])->name('dean.schools.athletes-data');
    Route::get('/schools/{school}/athletes-clan-data', [App\Http\Controllers\SchoolController::class, 'athletesClanDataForSchool'])->name('dean.schools.athletes-school-data');
    Route::get('/schools/{school}/athletes-year-data', [App\Http\Controllers\SchoolController::class, 'getAthletesNumberPerYear'])->name('dean.schools.athletes-year-data');

    Route::get('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'edit'])->name('dean.schools.edit');

    Route::post('/schools/{school}/users/create', [App\Http\Controllers\UserController::class, 'storeForSchool'])->name('dean.schools.users.create');
    Route::post('/schools/{school}/clan/create', [App\Http\Controllers\ClanController::class, 'storeForSchool'])->name('dean.schools.clan.create');

    Route::post('/schools/{school}/clans', [App\Http\Controllers\SchoolController::class, 'addClan'])->name('dean.schools.clans.store');
    Route::post('/schools/{school}/personnel', [App\Http\Controllers\SchoolController::class, 'addPersonnel'])->name('dean.schools.personnel.store');
    Route::post('/schools/{school}/athlete', [App\Http\Controllers\SchoolController::class, 'addAthlete'])->name('dean.schools.athlete.store');
  });


  /** Clan */

  Route::group([], function () {
    Route::get('/courses', [App\Http\Controllers\ClanController::class, 'index'])->name('dean.clans.index');
    Route::get('/courses/school', [App\Http\Controllers\ClanController::class, 'getBySchool'])->name('dean.clans.school');
    Route::get('/courses/all', [App\Http\Controllers\ClanController::class, 'all'])->name('dean.clans.all');

    Route::get('/courses/create', [App\Http\Controllers\ClanController::class, 'create'])->name('dean.clans.create');
    Route::get('/courses/search', [App\Http\Controllers\ClanController::class, 'search'])->name('dean.clans.search');

    Route::get('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'edit'])->name('dean.clans.edit');
    Route::delete('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'destroy'])->name('dean.clans.disable');

    Route::post('/courses', [App\Http\Controllers\ClanController::class, 'store'])->name('dean.clans.store');
    Route::post('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'update'])->name('dean.clans.update');
    Route::post('/courses/{clan}/user/create', [App\Http\Controllers\UserController::class, 'storeForClan'])->name('dean.clans.users.create');
    Route::post('/courses/{clan}/instructors', [App\Http\Controllers\ClanController::class, 'addInstructor'])->name('dean.clans.instructors.store');
    Route::post('/courses/{clan}/athlete', [App\Http\Controllers\ClanController::class, 'addAthlete'])->name('dean.clans.athletes.store');
  });



  /** Eventi */

  // Questo gruppo di route (Eventi) qui commentate sono funzionanti ma il preside non è al momento abilitato al loro utilizzo
  Route::group([], function () {
    Route::get('/events', [App\Http\Controllers\EventController::class, 'index'])->name('dean.events.index');
    Route::get('/events/calendar', [App\Http\Controllers\EventController::class, 'calendar'])->name('dean.events.calendar');
    Route::get('/events/create', [App\Http\Controllers\EventController::class, 'create'])->name('dean.events.create');
    Route::get('/events/{event}', [App\Http\Controllers\EventController::class, 'edit'])->name('dean.events.edit');
    Route::post('/events', [App\Http\Controllers\EventController::class, 'store'])->name('dean.events.store');
    // Route::post('/events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name('dean.events.update');
    // Route::post('events/{event}/description', [App\Http\Controllers\EventController::class, 'saveDescription'])->name('dean.events.save.description');
    // Route::post('events/{event}/location', [App\Http\Controllers\EventController::class, 'saveLocation'])->name('dean.events.save.location');
    // Route::put('events/{event}/thumbnail', [App\Http\Controllers\EventController::class, 'updateThumbnail'])->name('dean.events.update.thumbnail');
    Route::get('events/{event}/participants', [App\Http\Controllers\EventController::class, 'participants'])->name('dean.events.participants');
    Route::get('events/{event}/available-users', [App\Http\Controllers\EventController::class, 'available'])->name('dean.events.available');
    Route::post('add-participants', [App\Http\Controllers\EventController::class, 'selectParticipants'])->name('dean.events.participants.add');
    Route::get('events/{event}/participants/export', [App\Http\Controllers\EventController::class, 'exportParticipants'])->name('dean.events.participants.export');
    Route::get('/event-types/json', [App\Http\Controllers\EventTypeController::class, 'list'])->name('dean.events.types');
    Route::get('events/{event}/personnel', [App\Http\Controllers\EventController::class, 'personnel'])->name('dean.events.personnel');
  });

  /** Imports */

  Route::group([], function () {
    Route::get('/imports', [App\Http\Controllers\ImportController::class, 'index'])->name('dean.imports.index');
    Route::get('/imports/create', [App\Http\Controllers\ImportController::class, 'create'])->name('dean.imports.create');
    // Route::delete('/imports/{import}', [App\Http\Controllers\ImportController::class, 'destroy'])->name('dean.imports.disable');

    Route::post('/imports', [App\Http\Controllers\ImportController::class, 'store'])->name('dean.imports.store');
    Route::post('/imports/{import}', [App\Http\Controllers\ImportController::class, 'update'])->name('dean.imports.update');
    Route::post('/imports/{import}/download', [App\Http\Controllers\ImportController::class, 'download'])->name('dean.imports.download');

    Route::get('/imports/template', [App\Http\Controllers\ImportController::class, 'template'])->name('dean.imports.template');
  });

  /** Exports */

  Route::group([], function () {
    Route::get('/exports', [App\Http\Controllers\ExportController::class, 'index'])->name('dean.exports.index');
    Route::get('/exports/create', [App\Http\Controllers\ExportController::class, 'create'])->name('dean.exports.create');
    Route::get('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('dean.exports.download');
    // Route::delete('/exports/{export}', [App\Http\Controllers\ExportController::class, 'destroy'])->name('dean.exports.disable');

    Route::post('/exports', [App\Http\Controllers\ExportController::class, 'store'])->name('dean.exports.store');
    Route::post('/exports/{export}', [App\Http\Controllers\ExportController::class, 'update'])->name('dean.exports.update');
    // Route::post('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('dean.exports.download');
  });


  /** Rankings and Charts */

  Route::group([], function () {
    // Route::get('/rankings', [App\Http\Controllers\ChartController::class, 'index'])->name('dean.rankings.index');
    // Route::get('/rankings/paginate', [App\Http\Controllers\ChartController::class, 'paginate'])->name('dean.rankings.paginate');
  });

  /** Annunci */

  Route::group([], function () {
    Route::get('announcements', [App\Http\Controllers\AnnouncementController::class, 'ownRoles'])->name('dean.announcements.index');
    Route::post('announcements/{announcement}/seen', [App\Http\Controllers\AnnouncementController::class, 'setSeen'])->name('dean.announcements.seen');
  });
});
