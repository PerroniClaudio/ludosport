<?php

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;


Route::get('/dashboard', function () {

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/role-select', function () {

    $user = auth()->user();
    $user = User::find($user->id);
    $roles = $user->roles()->get();

    return view('role-selector', [
        'roles' => $roles
    ]);
})->middleware(['auth', 'verified'])->name('role-selector');

Route::middleware('auth')->group(function () {
    Route::post('/profile/role', [App\Http\Controllers\UserController::class, 'setUserRoleForSession'])->name('profile.role.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/** Users */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/users/search', [App\Http\Controllers\UserController::class, 'search'])->name('users.search');
    Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::post('/users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.disable');
    Route::get('/nation/{nation}/academies', [App\Http\Controllers\NationController::class, 'academies'])->name('nation.academies.index');
    Route::get('/academy/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'schools'])->name('academies.schools.index');
    Route::put('/users/{user}/picture', [App\Http\Controllers\UserController::class, 'picture'])->name('users.picture.update');
});

/** Nazioni */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/nations', [App\Http\Controllers\NationController::class, 'index'])->name('nations.index');
    Route::get('/nations/{nation}', [App\Http\Controllers\NationController::class, 'edit'])->name('nations.edit');
    Route::post('/nations/{nation}', [App\Http\Controllers\NationController::class, 'update'])->name('nations.update');

    Route::post('/nations/{nation}/academies/create', [App\Http\Controllers\AcademyController::class, 'storenation'])->name('nations.academies.create');
    Route::post('/nations/{nation}/academies', [App\Http\Controllers\NationController::class, 'associateAcademy'])->name('nations.academies.store');
    Route::put('/nations/{nation}/flag', [App\Http\Controllers\NationController::class, 'updateFlag'])->name('nations.flag.update');
});

/** Accademie */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/academies', [App\Http\Controllers\AcademyController::class, 'index'])->name('academies.index');
    Route::get('/academies/create', [App\Http\Controllers\AcademyController::class, 'create'])->name('academies.create');
    Route::get('/academies/all', [App\Http\Controllers\AcademyController::class, 'all'])->name('academies.all');
    Route::get('/academies/search', [App\Http\Controllers\AcademyController::class, 'search'])->name('academies.search');
    Route::get('/academies/{academy}', [App\Http\Controllers\AcademyController::class, 'edit'])->name('academies.edit');
    Route::delete('/academies/{academy}', [App\Http\Controllers\AcademyController::class, 'destroy'])->name('academies.disable');

    Route::post('/academies', [App\Http\Controllers\AcademyController::class, 'store'])->name('academies.store');
    Route::post('/academies/{academy}', [App\Http\Controllers\AcademyController::class, 'update'])->name('academies.update');
    Route::post('/academies/{academy/schools/create', [App\Http\Controllers\SchoolController::class, 'storeacademy'])->name('academies.schools.create');
    Route::post('/academies/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'addSchool'])->name('academies.schools.store');
    Route::post('/academies/{academy}/users/create', [App\Http\Controllers\UserController::class, 'storeForAcademy'])->name('academies.users.create');
    Route::post('/academies/{academy}/personnel', [App\Http\Controllers\AcademyController::class, 'addPersonnel'])->name('academies.personnel.store');
    Route::post('/academies/{academy}/athlete', [App\Http\Controllers\AcademyController::class, 'addAthlete'])->name('academies.athlete.store');
});

/** Scuole */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/schools', [App\Http\Controllers\SchoolController::class, 'index'])->name('schools.index');
    Route::get('/schools/create', [App\Http\Controllers\SchoolController::class, 'create'])->name('schools.create');
    Route::get('/schools/all', [App\Http\Controllers\SchoolController::class, 'all'])->name('schools.all');
    Route::get('/schools/search', [App\Http\Controllers\SchoolController::class, 'search'])->name('schools.search');
    Route::get('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'edit'])->name('schools.edit');
    Route::delete('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'destroy'])->name('schools.disable');

    Route::post('/schools', [App\Http\Controllers\SchoolController::class, 'store'])->name('schools.store');
    Route::post('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'update'])->name('schools.update');


    Route::post('/schools/{school}/users/create', [App\Http\Controllers\UserController::class, 'storeForSchool'])->name('schools.users.create');
    Route::post('/schools/{school}/clan/create', [App\Http\Controllers\ClanController::class, 'storeForSchool'])->name('schools.clan.create');

    Route::post('/schools/{school}/clans', [App\Http\Controllers\SchoolController::class, 'addClan'])->name('schools.clans.store');
    Route::post('/schools/{school}/personnel', [App\Http\Controllers\SchoolController::class, 'addPersonnel'])->name('schools.personnel.store');
    Route::post('/schools/{school}/athlete', [App\Http\Controllers\SchoolController::class, 'addAthlete'])->name('schools.athlete.store');
});

/** Clan */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/courses', [App\Http\Controllers\ClanController::class, 'index'])->name('clans.index');
    Route::get('/courses/create', [App\Http\Controllers\ClanController::class, 'create'])->name('clans.create');

    Route::get('/courses/all', [App\Http\Controllers\ClanController::class, 'all'])->name('clans.all');
    Route::get('/courses/search', [App\Http\Controllers\ClanController::class, 'search'])->name('clans.search');

    Route::get('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'edit'])->name('clans.edit');
    Route::delete('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'destroy'])->name('clans.disable');

    Route::post('/courses', [App\Http\Controllers\ClanController::class, 'store'])->name('clans.store');
    Route::post('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'update'])->name('clans.update');
    Route::post('/courses/{clan}/user/create', [App\Http\Controllers\UserController::class, 'storeForClan'])->name('clans.users.create');
    Route::post('/courses/{clan}/instructors', [App\Http\Controllers\ClanController::class, 'addInstructor'])->name('clans.instructors.store');
    Route::post('/courses/{clan}/athlete', [App\Http\Controllers\ClanController::class, 'addAthlete'])->name('clans.athletes.store');
});

/** Eventi */


Route::get('/events/location', [App\Http\Controllers\EventController::class, 'getLocationData'])->name('events.location');
Route::get('/events/coordinates', [App\Http\Controllers\EventController::class, 'coordinates'])->name('events.coordinates');

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/events', [App\Http\Controllers\EventController::class, 'index'])->name('events.index');
    Route::get('/events/calendar', [App\Http\Controllers\EventController::class, 'calendar'])->name('events.calendar');
    Route::get('/events/create', [App\Http\Controllers\EventController::class, 'create'])->name('events.create');

    //Tipi 

    Route::get('/event-types', [App\Http\Controllers\EventTypeController::class, 'index'])->name('events.list_types');
    Route::post('/event-types/create', [App\Http\Controllers\EventTypeController::class, 'store'])->name('events.new_type');
    Route::post('/event-types/{eventType}/associate', [App\Http\Controllers\EventTypeController::class, 'associate_event'])->name('events.associate_event');
    Route::post('/event-types/{eventType}', [App\Http\Controllers\EventTypeController::class, 'update'])->name('events.update_type');
    Route::delete('/event-types/{eventType}', [App\Http\Controllers\EventTypeController::class, 'destroy'])->name('events.type_disable');
    Route::get('/event-types/json', [App\Http\Controllers\EventTypeController::class, 'list'])->name('events.types');
    Route::get('/event-types/{eventType}', [App\Http\Controllers\EventTypeController::class, 'edit'])->name('events.edit_type');

    Route::get('/events/{event}/review', [App\Http\Controllers\EventController::class, 'review'])->name('events.review');
    Route::get('/events/all', [App\Http\Controllers\EventController::class, 'all'])->name('events.all');
    Route::get('/events/search', [App\Http\Controllers\EventController::class, 'search'])->name('events.search');
    Route::get('/events/{event}', [App\Http\Controllers\EventController::class, 'edit'])->name('events.edit');
    Route::delete('/events/{event}', [App\Http\Controllers\EventController::class, 'destroy'])->name('events.disable');

    Route::post('/events', [App\Http\Controllers\EventController::class, 'store'])->name('events.store');
    Route::post('/events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name('events.update');
    Route::post('/events/{event}/approve', [App\Http\Controllers\EventController::class, 'approve'])->name('events.approve');
    Route::post('/events/{event}/publish', [App\Http\Controllers\EventController::class, 'publish'])->name('events.publish');
    Route::post('/events/{event}/participants', [App\Http\Controllers\EventController::class, 'addParticipant'])->name('events.participants.store');
    Route::post('/events/{event}/results', [App\Http\Controllers\EventController::class, 'addResult'])->name('events.results.store');
});

/** Imports */

Route::group(['middleware' => ['auth', 'role:admin']], function () {

    Route::get('/imports', [App\Http\Controllers\ImportController::class, 'index'])->name('imports.index');
    Route::get('/imports/create', [App\Http\Controllers\ImportController::class, 'create'])->name('imports.create');
    Route::delete('/imports/{import}', [App\Http\Controllers\ImportController::class, 'destroy'])->name('imports.disable');


    Route::post('/imports', [App\Http\Controllers\ImportController::class, 'store'])->name('imports.store');
    Route::post('/imports/{import}', [App\Http\Controllers\ImportController::class, 'update'])->name('imports.update');
    Route::post('/imports/{import}/download', [App\Http\Controllers\ImportController::class, 'download'])->name('imports.download');

    Route::get('/imports/template', [App\Http\Controllers\ImportController::class, 'template'])->name('imports.template');
});

/** Exports */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/exports', [App\Http\Controllers\ExportController::class, 'index'])->name('exports.index');
    Route::get('/exports/create', [App\Http\Controllers\ExportController::class, 'create'])->name('exports.create');
    Route::delete('/exports/{export}', [App\Http\Controllers\ExportController::class, 'destroy'])->name('exports.disable');

    Route::post('/exports', [App\Http\Controllers\ExportController::class, 'store'])->name('exports.store');
    Route::post('/exports/{export}', [App\Http\Controllers\ExportController::class, 'update'])->name('exports.update');
    Route::post('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('exports.download');
});


/** Rankings and Charts */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/rankings', [App\Http\Controllers\ChartController::class, 'index'])->name('rankings.index');
    Route::get('/rankings/paginate', [App\Http\Controllers\ChartController::class, 'paginate'])->name('rankings.paginate');
});




/** Script */

require __DIR__ . '/auth.php';
require __DIR__ . '/technician.php';
require __DIR__ . '/site.php';
