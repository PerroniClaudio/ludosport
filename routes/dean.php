<?php

use Illuminate\Support\Facades\Route;

Route::prefix('dean')->middleware('auth')->middleware('role:dean')->group(function () {
  // Sblocco una route alla volta, mano a mano che le implemento

    /** Users */

    Route::group([], function () {
      // Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
      // Route::get('/users/filter', [App\Http\Controllers\UserController::class, 'filter'])->name('users.filter');
      // Route::get('/users/filter/result', [App\Http\Controllers\UserController::class, 'filterResult'])->name('users.filter.result');
      // Route::get('/users/search', [App\Http\Controllers\UserController::class, 'search'])->name('users.search');
      // Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
      Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'edit'])->name('dean.users.edit');
      // Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
      // Route::post('/users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
      // Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.disable');
      // Route::get('/nation/{nation}/academies', [App\Http\Controllers\NationController::class, 'academies'])->name('nation.academies.index');
      // Route::get('/academy/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'schools'])->name('academies.schools.index');
      // Route::put('/users/{user}/picture', [App\Http\Controllers\UserController::class, 'picture'])->name('users.picture.update');
    });
    
    /** Scuole */
    
    Route::get('school', [App\Http\Controllers\SchoolController::class, 'index'])->name('dean.school.index'); //reindirizza all'edit della scuola principale
    
    Route::group([], function () {
      // Route::get('/schools', [App\Http\Controllers\SchoolController::class, 'index'])->name('schools.index');
      // Route::get('/schools/create', [App\Http\Controllers\SchoolController::class, 'create'])->name('schools.create');
      // Route::get('/schools/all', [App\Http\Controllers\SchoolController::class, 'all'])->name('schools.all');
      // Route::get('/schools/academy', [App\Http\Controllers\SchoolController::class, 'getByAcademy'])->name('schools.academy');
      // Route::get('/schools/search', [App\Http\Controllers\SchoolController::class, 'search'])->name('schools.search');
      // Route::get('/schools/{school}/athletes-data', [App\Http\Controllers\SchoolController::class, 'athletesDataForSchool'])->name('schools.athletes-data');
      // Route::get('/schools/{school}/athletes-clan-data', [App\Http\Controllers\SchoolController::class, 'athletesClanDataForSchool'])->name('schools.athletes-school-data');
      // Route::get('/schools/{school}/athletes-year-data', [App\Http\Controllers\SchoolController::class, 'getAthletesNumberPerYear'])->name('schools.athletes-year-data');
      

      // Route::get('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'edit'])->name('schools.edit');
      // Route::delete('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'destroy'])->name('schools.disable');

      // Route::post('/schools', [App\Http\Controllers\SchoolController::class, 'store'])->name('schools.store');
      // Route::post('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'update'])->name('schools.update');


      Route::post('/schools/{school}/users/create', [App\Http\Controllers\UserController::class, 'storeForSchool'])->name('dean.schools.users.create');
      // Route::post('/schools/{school}/clan/create', [App\Http\Controllers\ClanController::class, 'storeForSchool'])->name('schools.clan.create');

      // Route::post('/schools/{school}/clans', [App\Http\Controllers\SchoolController::class, 'addClan'])->name('schools.clans.store');
      // Route::post('/schools/{school}/personnel', [App\Http\Controllers\SchoolController::class, 'addPersonnel'])->name('schools.personnel.store');
      // Route::post('/schools/{school}/athlete', [App\Http\Controllers\SchoolController::class, 'addAthlete'])->name('schools.athlete.store');
    });
    

    /** Clan */
    
    Route::group([], function () {
      // Route::get('/courses', [App\Http\Controllers\ClanController::class, 'index'])->name('clans.index');
      // Route::get('/courses/create', [App\Http\Controllers\ClanController::class, 'create'])->name('clans.create');
  
      // Route::get('/courses/all', [App\Http\Controllers\ClanController::class, 'all'])->name('clans.all');
      // Route::get('/courses/search', [App\Http\Controllers\ClanController::class, 'search'])->name('clans.search');
      // Route::get('/courses/school', [App\Http\Controllers\ClanController::class, 'getBySchool'])->name('clans.school');
  
      // Route::get('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'edit'])->name('clans.edit');
      // Route::delete('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'destroy'])->name('clans.disable');
  
      // Route::post('/courses', [App\Http\Controllers\ClanController::class, 'store'])->name('clans.store');
      // Route::post('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'update'])->name('clans.update');
      // Route::post('/courses/{clan}/user/create', [App\Http\Controllers\UserController::class, 'storeForClan'])->name('clans.users.create');
      // Route::post('/courses/{clan}/instructors', [App\Http\Controllers\ClanController::class, 'addInstructor'])->name('clans.instructors.store');
      // Route::post('/courses/{clan}/athlete', [App\Http\Controllers\ClanController::class, 'addAthlete'])->name('clans.athletes.store');
    });

  
  
    // Route::get('events', [App\Http\Controllers\EventController::class, 'index'])->name('technician.events.index');
    // Route::get('events/create', [App\Http\Controllers\EventController::class, 'create'])->name('technician.events.create');
    // Route::post('events/create', [App\Http\Controllers\EventController::class, 'store'])->name('technician.events.store');
    // Route::get('events/{event}', [App\Http\Controllers\EventController::class, 'edit'])->name('technician.events.edit');
    // Route::post('events/{event}/description', [App\Http\Controllers\EventController::class, 'saveDescription'])->name('technician.events.save.description');
    // Route::post('events/{event}/location', [App\Http\Controllers\EventController::class, 'saveLocation'])->name('technician.events.save.location');
    // Route::put('events/{event}/thumbnail', [App\Http\Controllers\EventController::class, 'updateThumbnail'])->name('technician.events.update.thumbnail');
    // Route::post('events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name('technician.events.update');

    // Route::get('events/{event}/participants', [App\Http\Controllers\EventController::class, 'participants'])->name('technician.events.participants');
    // Route::get('events/{event}/available-users', [App\Http\Controllers\EventController::class, 'available'])->name('technician.events.available');

    // Route::post('add-participants', [App\Http\Controllers\EventController::class, 'selectParticipants'])->name('technician.events.participants.add');
    // Route::get('events/{event}/participants/export', [App\Http\Controllers\EventController::class, 'exportParticipants'])->name('technician.events.participants.export');
    // Route::post('events', [App\Http\Controllers\EventController::class, 'store'])->name();
    // Route::put('events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name();
    // Route::delete('events/{event}', [App\Http\Controllers\EventController::class, 'destroy'])->name();
});