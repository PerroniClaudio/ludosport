<?php

use Illuminate\Support\Facades\Route;

Route::prefix('rector')->middleware(['auth', 'role:admin,rector'])->group(function () {
  Route::get('/fees', 'App\Http\Controllers\FeeController@index')->name('fees.index');
  Route::get('/fees/purchase', 'App\Http\Controllers\FeeController@create')->name('dean.fees.purchase');
  Route::get('/invoices/user-data/{user}', [App\Http\Controllers\UserController::class, 'invoiceData'])->name('users.invoices.get');
  Route::post('/invoices/store', [App\Http\Controllers\UserController::class, 'saveInvoice'])->name('users.invoices.store');


  #Stripe 

  Route::get('/fees/stripe/checkout', [App\Http\Controllers\FeeController::class, 'checkoutStripe'])->name('fees.checkout');
  Route::get('/fees/success', [App\Http\Controllers\FeeController::class, 'success'])->name('fees.success');
  Route::get('/fees/cancel', [App\Http\Controllers\FeeController::class, 'cancel'])->name('fees.cancel');

  # Paypal 

  Route::post('/fees/paypal/checkout', [App\Http\Controllers\FeeController::class, 'checkoutPaypal'])->name('fees.checkout-paypal');
  Route::get('/fees/paypal/success', [App\Http\Controllers\FeeController::class, 'successPaypal'])->name('fees.paypal-success');
  Route::get('/fees/paypal/cancel', [App\Http\Controllers\FeeController::class, 'cancelPaypal'])->name('fees.paypal-cancel');

  Route::get('/fees/stripe-checkout', [App\Http\Controllers\FeeController::class, 'checkoutStripe'])->name('fees.checkout');

  Route::get('/fees/extimate', [App\Http\Controllers\FeeController::class, 'extimateFeeConsumption'])->name('fees.extimate');
  Route::post('/fees/associate', [App\Http\Controllers\FeeController::class, 'associateFeesToUsers'])->name('fees.associate');

  /** Users */

  Route::group([], function () {
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('rector.users.index');
    Route::get('/users/filter', [App\Http\Controllers\UserController::class, 'filter'])->name('rector.users.filter');
    Route::get('/users/filter/result', [App\Http\Controllers\UserController::class, 'filterResult'])->name('rector.users.filter.result');
    Route::get('/users/search', [App\Http\Controllers\UserController::class, 'search'])->name('rector.users.search');
    Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('rector.users.create');
    Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'edit'])->name('rector.users.edit');
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('rector.users.store');
    Route::post('/users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('rector.users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('rector.users.disable');
    Route::put('/users/{user}/picture', [App\Http\Controllers\UserController::class, 'picture'])->name('rector.users.picture.update');
    Route::get('/nation/{nation}/academies', [App\Http\Controllers\NationController::class, 'academies'])->name('rector.nation.academies.index');
    // Route::get('/academy/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'schools'])->name('rector.academies.schools.index');
  });

  /** Ruoli */

  Route::group([], function () {
    Route::get('/custom-roles', [App\Http\Controllers\RoleController::class, 'index'])->name('rector.roles.index');
    Route::get('/custom-roles/search', [App\Http\Controllers\RoleController::class, 'search'])->name('rector.roles.search');
    Route::post('/custom-roles/assign', [App\Http\Controllers\RoleController::class, 'assign'])->name('rector.roles.assign');
    Route::post('/custom-roles', [App\Http\Controllers\RoleController::class, 'store'])->name('rector.roles.store');
  });

  /** Accademie */

  Route::group(['middleware' => ['auth', 'role:admin,rector']], function () {
    Route::get('/academies', [App\Http\Controllers\AcademyController::class, 'index'])->name('rector.academies.index');
    // Route::get('/academies/create', [App\Http\Controllers\AcademyController::class, 'create'])->name('rector.academies.create');
    // Route::get('/academies/all', [App\Http\Controllers\AcademyController::class, 'all'])->name('rector.academies.all');
    // Route::get('/academies/search', [App\Http\Controllers\AcademyController::class, 'search'])->name('rector.academies.search');
    Route::get('/academies/{academy}/athletes-data', [App\Http\Controllers\AcademyController::class, 'athletesDataForAcademy'])->name('rector.academies.athletes-data');
    Route::get('/academies/{academy}/athletes-school-data', [App\Http\Controllers\AcademyController::class, 'athletesSchoolDataForAcademy'])->name('rector.academies.athletes-school-data');
    Route::get('/academies/{academy}/athletes-year-data', [App\Http\Controllers\AcademyController::class, 'getAthletesNumberPerYear'])->name('rector.academies.athletes-year-data');

    Route::get('/academies/{academy}', [App\Http\Controllers\AcademyController::class, 'edit'])->name('rector.academies.edit');
    // Route::delete('/academies/{academy}', [App\Http\Controllers\AcademyController::class, 'destroy'])->name('rector.academies.disable');

    // Route::post('/academies', [App\Http\Controllers\AcademyController::class, 'store'])->name('rector.academies.store');
    // Route::post('/academies/{academy}', [App\Http\Controllers\AcademyController::class, 'update'])->name('rector.academies.update');
    Route::post('/academies/{academy}/schools/create', [App\Http\Controllers\SchoolController::class, 'storeacademy'])->name('rector.academies.schools.create');
    Route::post('/academies/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'addSchool'])->name('rector.academies.schools.store');
    Route::post('/academies/{academy}/users/create', [App\Http\Controllers\UserController::class, 'storeForAcademy'])->name('rector.academies.users.create');
    Route::post('/academies/{academy}/personnel', [App\Http\Controllers\AcademyController::class, 'addPersonnel'])->name('rector.academies.personnel.store');
    Route::post('/academies/{academy}/athlete', [App\Http\Controllers\AcademyController::class, 'addAthlete'])->name('rector.academies.athlete.store');

    Route::get('/academies/{academy}/users-search', [App\Http\Controllers\AcademyController::class, 'searchUsers'])->name('rector.academies.users-search');
  });

  // Route::get('/academies/{academy}', [App\Http\Controllers\AcademyController::class, 'show'])->name('rector.academies.show');

  /** Scuole */

  Route::group([], function () {
    Route::get('/schools/all', [App\Http\Controllers\SchoolController::class, 'all'])->name('rector.schools.all');
    Route::get('/schools/academy', [App\Http\Controllers\SchoolController::class, 'getByAcademy'])->name('rector.schools.academy');
    Route::get('/schools', [App\Http\Controllers\SchoolController::class, 'index'])->name('rector.schools.index');
    Route::get('/schools/create', [App\Http\Controllers\SchoolController::class, 'create'])->name('rector.schools.create');
    Route::get('/schools/search', [App\Http\Controllers\SchoolController::class, 'search'])->name('rector.schools.search');

    Route::get('/schools/{school}/athletes-data', [App\Http\Controllers\SchoolController::class, 'athletesDataForSchool'])->name('rector.schools.athletes-data');
    Route::get('/schools/{school}/athletes-clan-data', [App\Http\Controllers\SchoolController::class, 'athletesClanDataForSchool'])->name('rector.schools.athletes-school-data');
    Route::get('/schools/{school}/athletes-year-data', [App\Http\Controllers\SchoolController::class, 'getAthletesNumberPerYear'])->name('rector.schools.athletes-year-data');


    Route::get('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'edit'])->name('rector.schools.edit');
    Route::delete('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'destroy'])->name('rector.schools.disable');
    Route::post('/schools', [App\Http\Controllers\SchoolController::class, 'store'])->name('rector.schools.store');

    Route::post('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'update'])->name('rector.schools.update');


    Route::post('/schools/{school}/users/create', [App\Http\Controllers\UserController::class, 'storeForSchool'])->name('rector.schools.users.create');
    Route::post('/schools/{school}/clan/create', [App\Http\Controllers\ClanController::class, 'storeForSchool'])->name('rector.schools.clan.create');

    Route::post('/schools/{school}/clans', [App\Http\Controllers\SchoolController::class, 'addClan'])->name('rector.schools.clans.store');
    Route::post('/schools/{school}/personnel', [App\Http\Controllers\SchoolController::class, 'addPersonnel'])->name('rector.schools.personnel.store');
    Route::post('/schools/{school}/athlete', [App\Http\Controllers\SchoolController::class, 'addAthlete'])->name('rector.schools.athlete.store');

    Route::get('/schools/{school}/users-search', [App\Http\Controllers\SchoolController::class, 'searchUsers'])->name('rector.schools.users-search');
  });


  /** Clan */

  Route::group([], function () {
    Route::get('/courses', [App\Http\Controllers\ClanController::class, 'index'])->name('rector.clans.index');
    Route::get('/courses/school', [App\Http\Controllers\ClanController::class, 'getBySchool'])->name('rector.clans.school');
    // Route::get('/courses/all', [App\Http\Controllers\ClanController::class, 'all'])->name('rector.clans.all');

    Route::get('/courses/create', [App\Http\Controllers\ClanController::class, 'create'])->name('rector.clans.create');
    // Route::get('/courses/search', [App\Http\Controllers\ClanController::class, 'search'])->name('rector.clans.search');

    Route::get('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'edit'])->name('rector.clans.edit');
    Route::delete('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'destroy'])->name('rector.clans.disable');

    Route::post('/courses', [App\Http\Controllers\ClanController::class, 'store'])->name('rector.clans.store');
    Route::post('/courses/{clan}', [App\Http\Controllers\ClanController::class, 'update'])->name('rector.clans.update');
    Route::post('/courses/{clan}/user/create', [App\Http\Controllers\UserController::class, 'storeForClan'])->name('rector.clans.users.create');
    Route::post('/courses/{clan}/instructors', [App\Http\Controllers\ClanController::class, 'addInstructor'])->name('rector.clans.instructors.store');
    Route::post('/courses/{clan}/athlete', [App\Http\Controllers\ClanController::class, 'addAthlete'])->name('rector.clans.athletes.store');
  });



  /** Eventi */


  // Route::get('/events/location', [App\Http\Controllers\EventController::class, 'getLocationData'])->name('rector.events.location');
  // Route::get('/events/coordinates', [App\Http\Controllers\EventController::class, 'coordinates'])->name('rector.events.coordinates');

  Route::group([], function () {
    Route::get('/events', [App\Http\Controllers\EventController::class, 'index'])->name('rector.events.index');
    Route::get('/events/calendar', [App\Http\Controllers\EventController::class, 'calendar'])->name('rector.events.calendar');
    Route::get('/events/create', [App\Http\Controllers\EventController::class, 'create'])->name('rector.events.create');

    // Route::get('/events/{event}/review', [App\Http\Controllers\EventController::class, 'review'])->name('rector.events.review');
    // Route::get('/events/all', [App\Http\Controllers\EventController::class, 'all'])->name('rector.events.all');
    // Route::get('/events/search', [App\Http\Controllers\EventController::class, 'search'])->name('rector.events.search');
    Route::get('/events/{event}', [App\Http\Controllers\EventController::class, 'edit'])->name('rector.events.edit');
    // Route::delete('/events/{event}', [App\Http\Controllers\EventController::class, 'destroy'])->name('rector.events.disable');

    Route::post('/events', [App\Http\Controllers\EventController::class, 'store'])->name('rector.events.store');
    Route::post('/events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name('rector.events.update');
    // Route::post('/events/{event}/reject', [App\Http\Controllers\EventController::class, 'reject'])->name('rector.events.reject');
    // Route::post('/events/{event}/approve', [App\Http\Controllers\EventController::class, 'approve'])->name('rector.events.approve');
    // Route::post('/events/{event}/publish', [App\Http\Controllers\EventController::class, 'publish'])->name('rector.events.publish');

    // Route::post('/events/{event}/participants', [App\Http\Controllers\EventController::class, 'addParticipant'])->name('rector.events.participants.store');
    // Route::post('/events/{event}/results', [App\Http\Controllers\EventController::class, 'addResult'])->name('rector.events.results.store');

    // Presi da technician. alcuni mancano in admin
    Route::post('events/{event}/description', [App\Http\Controllers\EventController::class, 'saveDescription'])->name('rector.events.save.description');
    Route::post('events/{event}/location', [App\Http\Controllers\EventController::class, 'saveLocation'])->name('rector.events.save.location');
    Route::put('events/{event}/thumbnail', [App\Http\Controllers\EventController::class, 'updateThumbnail'])->name('rector.events.update.thumbnail');

    Route::get('events/{event}/participants', [App\Http\Controllers\EventController::class, 'participants'])->name('rector.events.participants');
    Route::get('events/{event}/available-users', [App\Http\Controllers\EventController::class, 'available'])->name('rector.events.available');

    Route::post('add-participants', [App\Http\Controllers\EventController::class, 'selectParticipants'])->name('rector.events.participants.add');
    Route::get('events/{event}/participants/export', [App\Http\Controllers\EventController::class, 'exportParticipants'])->name('rector.events.participants.export');

    //Tipi (solo l'admin può modificare i tipi di evento, per evitare la creazione indiscriminata di tipi)

    // Route::get('/event-types', [App\Http\Controllers\EventTypeController::class, 'index'])->name('rector.events.list_types');
    // Route::post('/event-types/create', [App\Http\Controllers\EventTypeController::class, 'store'])->name('rector.events.new_type');
    // Route::post('/event-types/{eventType}/associate', [App\Http\Controllers\EventTypeController::class, 'associate_event'])->name('rector.events.associate_event');
    // Route::post('/event-types/{eventType}', [App\Http\Controllers\EventTypeController::class, 'update'])->name('rector.events.update_type');
    // Route::delete('/event-types/{eventType}', [App\Http\Controllers\EventTypeController::class, 'destroy'])->name('rector.events.type_disable');
    Route::get('/event-types/json', [App\Http\Controllers\EventTypeController::class, 'list'])->name('rector.events.types');
    // Route::get('/event-types/{eventType}', [App\Http\Controllers\EventTypeController::class, 'edit'])->name('rector.events.edit_type');
  });

  /** Imports */

  Route::group([], function () {

    // Route::get('/imports', [App\Http\Controllers\ImportController::class, 'index'])->name('rector.imports.index');
    // Route::get('/imports/create', [App\Http\Controllers\ImportController::class, 'create'])->name('rector.imports.create');
    // Route::delete('/imports/{import}', [App\Http\Controllers\ImportController::class, 'destroy'])->name('rector.imports.disable');


    // Route::post('/imports', [App\Http\Controllers\ImportController::class, 'store'])->name('rector.imports.store');
    // Route::post('/imports/{import}', [App\Http\Controllers\ImportController::class, 'update'])->name('rector.imports.update');
    // Route::post('/imports/{import}/download', [App\Http\Controllers\ImportController::class, 'download'])->name('rector.imports.download');

    // Route::get('/imports/template', [App\Http\Controllers\ImportController::class, 'template'])->name('rector.imports.template');
  });

  /** Exports */

  Route::group([], function () {
    // Route::get('/exports', [App\Http\Controllers\ExportController::class, 'index'])->name('rector.exports.index');
    // Route::get('/exports/create', [App\Http\Controllers\ExportController::class, 'create'])->name('rector.exports.create');
    // Route::get('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('rector.exports.download');
    // Route::delete('/exports/{export}', [App\Http\Controllers\ExportController::class, 'destroy'])->name('rector.exports.disable');

    // Route::post('/exports', [App\Http\Controllers\ExportController::class, 'store'])->name('rector.exports.store');
    // Route::post('/exports/{export}', [App\Http\Controllers\ExportController::class, 'update'])->name('rector.exports.update');
    // Route::post('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('rector.exports.download');
  });


  /** Rankings and Charts */

  Route::group([], function () {
    // Route::get('/rankings', [App\Http\Controllers\ChartController::class, 'index'])->name('rector.rankings.index');
    // Route::get('/rankings/paginate', [App\Http\Controllers\ChartController::class, 'paginate'])->name('rector.rankings.paginate');
  });

  /** Annunci */

  Route::group([], function () {
    Route::get('announcements', [App\Http\Controllers\AnnouncementController::class, 'ownRoles'])->name('rector.announcements.index');
    Route::post('announcements/{announcement}/seen', [App\Http\Controllers\AnnouncementController::class, 'setSeen'])->name('rector.announcements.seen');
  });
});
