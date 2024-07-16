<?php

use Illuminate\Support\Facades\Route;

Route::prefix('dean')->middleware('auth')->middleware('role:dean')->group(function () {
  // Sblocco una route alla volta, mano a mano che le implemento

    /** Users */

    Route::group([], function () {
      Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('dean.users.index');
      // Route::get('/users/filter', [App\Http\Controllers\UserController::class, 'filter'])->name('users.filter');
      // Route::get('/users/filter/result', [App\Http\Controllers\UserController::class, 'filterResult'])->name('users.filter.result');
      Route::get('/users/search', [App\Http\Controllers\UserController::class, 'search'])->name('dean.users.search');
      Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('dean.users.create');
      Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'edit'])->name('dean.users.edit');
      // Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
      Route::post('/users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('dean.users.update');
      // Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.disable');
      // Route::get('/nation/{nation}/academies', [App\Http\Controllers\NationController::class, 'academies'])->name('nation.academies.index');
      // Route::get('/academy/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'schools'])->name('academies.schools.index');
      Route::put('/users/{user}/picture', [App\Http\Controllers\UserController::class, 'picture'])->name('dean.users.picture.update');
    });

    /** Ruoli */

    Route::group([], function () {
      Route::get('/custom-roles', [App\Http\Controllers\RoleController::class, 'index'])->name('dean.roles.index');
      Route::get('/custom-roles/search', [App\Http\Controllers\RoleController::class, 'search'])->name('dean.roles.search');
      Route::post('/custom-roles/assign', [App\Http\Controllers\RoleController::class, 'assign'])->name('dean.roles.assign');
      Route::post('/custom-roles', [App\Http\Controllers\RoleController::class, 'store'])->name('dean.roles.store');
    });
    
    /** Scuole */
    
    Route::get('school', [App\Http\Controllers\SchoolController::class, 'index'])->name('dean.school.index'); //reindirizza all'edit della scuola principale
    
    Route::group([], function () {
      // Route::get('/schools', [App\Http\Controllers\SchoolController::class, 'index'])->name('dean.schools.index');
      // Route::get('/schools/create', [App\Http\Controllers\SchoolController::class, 'create'])->name('dean.schools.create');
      // Route::get('/schools/all', [App\Http\Controllers\SchoolController::class, 'all'])->name('dean.schools.all');
      // Route::get('/schools/academy', [App\Http\Controllers\SchoolController::class, 'getByAcademy'])->name('dean.schools.academy');
      // Route::get('/schools/search', [App\Http\Controllers\SchoolController::class, 'search'])->name('dean.schools.search');
      // Route::get('/schools/{school}/athletes-data', [App\Http\Controllers\SchoolController::class, 'athletesDataForSchool'])->name('dean.schools.athletes-data');
      // Route::get('/schools/{school}/athletes-clan-data', [App\Http\Controllers\SchoolController::class, 'athletesClanDataForSchool'])->name('dean.schools.athletes-school-data');
      // Route::get('/schools/{school}/athletes-year-data', [App\Http\Controllers\SchoolController::class, 'getAthletesNumberPerYear'])->name('dean.schools.athletes-year-data');
      

      Route::get('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'edit'])->name('dean.schools.edit');
      // Route::delete('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'destroy'])->name('dean.schools.disable');

      // Route::post('/schools', [App\Http\Controllers\SchoolController::class, 'store'])->name('dean.schools.store');
      // Route::post('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'update'])->name('dean.schools.update');


      Route::post('/schools/{school}/users/create', [App\Http\Controllers\UserController::class, 'storeForSchool'])->name('dean.schools.users.create');
      Route::post('/schools/{school}/clan/create', [App\Http\Controllers\ClanController::class, 'storeForSchool'])->name('dean.schools.clan.create');

      Route::post('/schools/{school}/clans', [App\Http\Controllers\SchoolController::class, 'addClan'])->name('dean.schools.clans.store');
      Route::post('/schools/{school}/personnel', [App\Http\Controllers\SchoolController::class, 'addPersonnel'])->name('dean.schools.personnel.store');
      Route::post('/schools/{school}/athlete', [App\Http\Controllers\SchoolController::class, 'addAthlete'])->name('dean.schools.athlete.store');
    });
    

    /** Clan */
    
    Route::group([], function () {
      Route::get('/courses', [App\Http\Controllers\ClanController::class, 'index'])->name('dean.clans.index');
      // Route::get('/courses/create', [App\Http\Controllers\ClanController::class, 'create'])->name('dean.clans.create');
  
      // Route::get('/courses/all', [App\Http\Controllers\ClanController::class, 'all'])->name('dean.clans.all');
      // Route::get('/courses/search', [App\Http\Controllers\ClanController::class, 'search'])->name('dean.clans.search');
      // Route::get('/courses/school', [App\Http\Controllers\ClanController::class, 'getBySchool'])->name('dean.clans.school');
  
      Route::get('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'edit'])->name('dean.clans.edit');
      // Route::delete('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'destroy'])->name('dean.clans.disable');
  
      Route::post('/courses', [App\Http\Controllers\ClanController::class, 'store'])->name('dean.clans.store');
      Route::post('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'update'])->name('dean.clans.update');
      // Route::post('/courses/{clan}/user/create', [App\Http\Controllers\UserController::class, 'storeForClan'])->name('dean.clans.users.create');
      // Route::post('/courses/{clan}/instructors', [App\Http\Controllers\ClanController::class, 'addInstructor'])->name('dean.clans.instructors.store');
      // Route::post('/courses/{clan}/athlete', [App\Http\Controllers\ClanController::class, 'addAthlete'])->name('dean.clans.athletes.store');
    });

  
  
    /** Eventi */


    // Route::get('/events/location', [App\Http\Controllers\EventController::class, 'getLocationData'])->name('events.location');
    // Route::get('/events/coordinates', [App\Http\Controllers\EventController::class, 'coordinates'])->name('events.coordinates');

    Route::group(['middleware' => ['auth', 'role:admin']], function () {
        // Route::get('/events', [App\Http\Controllers\EventController::class, 'index'])->name('events.index');
        // Route::get('/events/calendar', [App\Http\Controllers\EventController::class, 'calendar'])->name('events.calendar');
        // Route::get('/events/create', [App\Http\Controllers\EventController::class, 'create'])->name('events.create');

        //Tipi 

        // Route::get('/event-types', [App\Http\Controllers\EventTypeController::class, 'index'])->name('events.list_types');
        // Route::post('/event-types/create', [App\Http\Controllers\EventTypeController::class, 'store'])->name('events.new_type');
        // Route::post('/event-types/{eventType}/associate', [App\Http\Controllers\EventTypeController::class, 'associate_event'])->name('events.associate_event');
        // Route::post('/event-types/{eventType}', [App\Http\Controllers\EventTypeController::class, 'update'])->name('events.update_type');
        // Route::delete('/event-types/{eventType}', [App\Http\Controllers\EventTypeController::class, 'destroy'])->name('events.type_disable');
        // Route::get('/event-types/json', [App\Http\Controllers\EventTypeController::class, 'list'])->name('events.types');
        // Route::get('/event-types/{eventType}', [App\Http\Controllers\EventTypeController::class, 'edit'])->name('events.edit_type');

        // Route::get('/events/{event}/review', [App\Http\Controllers\EventController::class, 'review'])->name('events.review');
        // Route::get('/events/all', [App\Http\Controllers\EventController::class, 'all'])->name('events.all');
        // Route::get('/events/search', [App\Http\Controllers\EventController::class, 'search'])->name('events.search');
        // Route::get('/events/{event}', [App\Http\Controllers\EventController::class, 'edit'])->name('events.edit');
        // Route::delete('/events/{event}', [App\Http\Controllers\EventController::class, 'destroy'])->name('events.disable');

        // Route::post('/events', [App\Http\Controllers\EventController::class, 'store'])->name('events.store');
        // Route::post('/events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name('events.update');
        // Route::post('/events/{event}/reject', [App\Http\Controllers\EventController::class, 'reject'])->name('events.reject');
        // Route::post('/events/{event}/approve', [App\Http\Controllers\EventController::class, 'approve'])->name('events.approve');
        // Route::post('/events/{event}/publish', [App\Http\Controllers\EventController::class, 'publish'])->name('events.publish');
        // Route::post('/events/{event}/participants', [App\Http\Controllers\EventController::class, 'addParticipant'])->name('events.participants.store');
        // Route::post('/events/{event}/results', [App\Http\Controllers\EventController::class, 'addResult'])->name('events.results.store');
    });

    /** Imports */

    Route::group(['middleware' => ['auth', 'role:admin']], function () {

        // Route::get('/imports', [App\Http\Controllers\ImportController::class, 'index'])->name('imports.index');
        // Route::get('/imports/create', [App\Http\Controllers\ImportController::class, 'create'])->name('imports.create');
        // Route::delete('/imports/{import}', [App\Http\Controllers\ImportController::class, 'destroy'])->name('imports.disable');


        // Route::post('/imports', [App\Http\Controllers\ImportController::class, 'store'])->name('imports.store');
        // Route::post('/imports/{import}', [App\Http\Controllers\ImportController::class, 'update'])->name('imports.update');
        // Route::post('/imports/{import}/download', [App\Http\Controllers\ImportController::class, 'download'])->name('imports.download');

        // Route::get('/imports/template', [App\Http\Controllers\ImportController::class, 'template'])->name('imports.template');
    });

    /** Exports */

    Route::group(['middleware' => ['auth', 'role:admin']], function () {
        // Route::get('/exports', [App\Http\Controllers\ExportController::class, 'index'])->name('exports.index');
        // Route::get('/exports/create', [App\Http\Controllers\ExportController::class, 'create'])->name('exports.create');
        // Route::get('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('exports.download');
        // Route::delete('/exports/{export}', [App\Http\Controllers\ExportController::class, 'destroy'])->name('exports.disable');

        // Route::post('/exports', [App\Http\Controllers\ExportController::class, 'store'])->name('exports.store');
        // Route::post('/exports/{export}', [App\Http\Controllers\ExportController::class, 'update'])->name('exports.update');
        // Route::post('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('exports.download');
    });


    /** Rankings and Charts */

    Route::group(['middleware' => ['auth', 'role:admin']], function () {
        // Route::get('/rankings', [App\Http\Controllers\ChartController::class, 'index'])->name('rankings.index');
        // Route::get('/rankings/paginate', [App\Http\Controllers\ChartController::class, 'paginate'])->name('rankings.paginate');
    });

    /** Annunci */

    Route::group(['middleware' => ['auth', 'role:admin']], function () {
        // Route::get('/announcements', [App\Http\Controllers\AnnouncementController::class, 'index'])->name('announcements.index');
        // Route::get('/announcements/create', [App\Http\Controllers\AnnouncementController::class, 'create'])->name('announcements.create');
        // Route::get('/announcements/{announcement}', [App\Http\Controllers\AnnouncementController::class, 'edit'])->name('announcements.edit');
        // Route::delete('/announcements/{announcement}', [App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('announcements.disable');

        // Route::post('/announcements', [App\Http\Controllers\AnnouncementController::class, 'store'])->name('announcements.store');
        // Route::post('/announcements/{announcement}', [App\Http\Controllers\AnnouncementController::class, 'update'])->name('announcements.update');
    });

    /** Ruoli */

    Route::group(['middleware' => ['auth', 'role:admin']], function () {
        // Route::get('/custom-roles', [App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');
        // Route::get('/custom-roles/search', [App\Http\Controllers\RoleController::class, 'search'])->name('roles.search');
        // Route::post('/custom-roles/assign', [App\Http\Controllers\RoleController::class, 'assign'])->name('roles.assign');
        // Route::post('/custom-roles', [App\Http\Controllers\RoleController::class, 'store'])->name('roles.store');
    });
});