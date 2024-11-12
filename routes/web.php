<?php

use App\Http\Controllers\ProfileController;
use GPBMetadata\Google\Protobuf\Api;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [App\Http\Controllers\UserController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/role-select', [App\Http\Controllers\UserController::class, 'roleSelector'])->middleware(['auth', 'verified'])->name('role-selector');

/** Assets */

Route::get('/logo', [App\Http\Controllers\AssetController::class, 'logo'])->name('logo');
Route::get('/logo-saber', [App\Http\Controllers\AssetController::class, 'logoSaber'])->name('logo-saber');
Route::get('/logo-saber-k', [App\Http\Controllers\AssetController::class, 'logoSaberK'])->name('logo-saber-k');
Route::get('/warriors', [App\Http\Controllers\AssetController::class, 'warriors'])->name('warriors');
Route::get('/spada-home', [App\Http\Controllers\AssetController::class, 'spadaHome'])->name('spada-home');
Route::get('/bollino', [App\Http\Controllers\AssetController::class, 'bollino'])->name('bollino');
Route::get('/nation/{nation}/flag', [App\Http\Controllers\AssetController::class, 'nationFlag'])->name('nation-flag');
Route::get('/ranks/{rank}/image', [App\Http\Controllers\AssetController::class, 'rankImage'])->name('rank-image');
Route::get('/weapon-form/{weapon:id}/image', [App\Http\Controllers\AssetController::class, 'weaponFormImage'])->name('weapon-form-image');
Route::get('/favicon', [App\Http\Controllers\AssetController::class, 'favicon'])->name('favicon');
Route::get('/user/{user}/profile-picture', [App\Http\Controllers\UserController::class, 'propic'])->name('user.profile-picture-show');


Route::middleware('auth')->group(function () {
    Route::post('/profile/role', [App\Http\Controllers\UserController::class, 'setUserRoleForSession'])->name('profile.role.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/profile/{user}/picture', [App\Http\Controllers\UserController::class, 'userUploadPicture'])->name('users.update-pfp');

    Route::post('/invoices/store', [App\Http\Controllers\UserController::class, 'saveInvoice'])->name('users.invoices.store');
    Route::post('/invoices/update', [App\Http\Controllers\UserController::class, 'updateInvoice'])->name('users.invoices.update');

    Route::get('/imports/download/{import}', [App\Http\Controllers\ImportController::class, 'download'])->name('imports.download');
});

/** Eliminati */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/deleted-elements', [App\Http\Controllers\DeletedElementController::class, 'index'])->name('deleted-elements.index');
    Route::post('/deleted-elements', [App\Http\Controllers\DeletedElementController::class, 'restore'])->name('deleted-elements.restore');
});

/** Users */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/users/filter', [App\Http\Controllers\UserController::class, 'filter'])->name('users.filter');
    Route::get('/users/filter/result', [App\Http\Controllers\UserController::class, 'filterResult'])->name('users.filter.result');
    Route::get('/users/search', [App\Http\Controllers\UserController::class, 'search'])->name('users.search');
    Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');

    Route::post('/users/associate-school', [App\Http\Controllers\UserController::class, 'associateSchool'])->name('users.associate-school');
    Route::post('/users/associate-academy', [App\Http\Controllers\UserController::class, 'associateAcademy'])->name('users.associate-academy');
    Route::post('/users/remove-school', [App\Http\Controllers\UserController::class, 'removeSchool'])->name('users.remove-school');
    Route::post('/users/remove-academy', [App\Http\Controllers\UserController::class, 'removeAcademy'])->name('users.remove-academy');

    Route::get('/world-athletes-data', [App\Http\Controllers\UserController::class, 'athletesDataForWorld'])->name('users.world-athletes-data');
    Route::get('/world-athletes-data-list', [App\Http\Controllers\UserController::class, 'athletesDataWorldList'])->name('users.world-athletes-data-list');
    Route::get('/world-athletes-year-data', [App\Http\Controllers\UserController::class, 'getWorldAthletesNumberPerYear'])->name('users.world-athletes-year-data');

    // Prima del post '/users/{user}' perche' altrimenti non funziona
    Route::post('/users/set-main-institution', [App\Http\Controllers\UserController::class, 'setMainInstitution'])->name('users.set-main-institution');

    Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::post('/users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.disable');
    Route::get('/nation/{nation}/academies', [App\Http\Controllers\NationController::class, 'academies'])->name('nation.academies.index');
    Route::get('/academy/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'schools'])->name('academies.schools.index');
    Route::put('/users/{user}/picture', [App\Http\Controllers\UserController::class, 'picture'])->name('users.picture.update');

    Route::post('/users/{user}/weapon-forms-personnel', [App\Http\Controllers\UserController::class, 'editWeaponFormsPersonnel'])->name('user.weapon-forms-personnel.store');
    Route::post('/users/{user}/weapon-forms-athlete', [App\Http\Controllers\UserController::class, 'editWeaponFormsAthlete'])->name('user.weapon-forms-athlete.store');

    Route::post('/user/{user}/reset-password', [App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.reset-password');
});

Route::post('/users/{user}/languages', [App\Http\Controllers\UserController::class, 'languages'])->name('users.languages.store');

/** Nazioni */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/nations', [App\Http\Controllers\NationController::class, 'index'])->name('nations.index');
    Route::get('/nations/{nation}', [App\Http\Controllers\NationController::class, 'edit'])->name('nations.edit');
    Route::post('/nations/{nation}', [App\Http\Controllers\NationController::class, 'update'])->name('nations.update');

    Route::post('/nations/{nation}/academies/create', [App\Http\Controllers\AcademyController::class, 'storenation'])->name('nations.academies.create');
    Route::post('/nations/{nation}/academies', [App\Http\Controllers\NationController::class, 'associateAcademy'])->name('nations.academies.store');
    Route::put('/nations/{nation}/flag', [App\Http\Controllers\NationController::class, 'updateFlag'])->name('nations.flag.update');

    Route::get('/nations/{nation}/users-search', [App\Http\Controllers\NationController::class, 'searchUsers'])->name('nations.users-search');

    Route::get('/nations/{nation}/athletes-year-data', [App\Http\Controllers\NationController::class, 'getNationAthletesNumberPerYear'])->name('nations.athletes-year-data');
});

/** Accademie */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/academies', [App\Http\Controllers\AcademyController::class, 'index'])->name('academies.index');
    Route::get('/academies/create', [App\Http\Controllers\AcademyController::class, 'create'])->name('academies.create');
    Route::get('/academies/all', [App\Http\Controllers\AcademyController::class, 'all'])->name('academies.all');
    Route::get('/academies/search', [App\Http\Controllers\AcademyController::class, 'search'])->name('academies.search');
    Route::get('/academies/{academy}/athletes-data', [App\Http\Controllers\AcademyController::class, 'athletesDataForAcademy'])->name('academies.athletes-data');
    Route::get('/academies/{academy}/athletes-school-data', [App\Http\Controllers\AcademyController::class, 'athletesSchoolDataForAcademy'])->name('academies.athletes-school-data');
    Route::get('/academies/{academy}/athletes-year-data', [App\Http\Controllers\AcademyController::class, 'getAthletesNumberPerYear'])->name('academies.athletes-year-data');

    Route::get('/academies/{academy}', [App\Http\Controllers\AcademyController::class, 'edit'])->name('academies.edit');
    Route::delete('/academies/{academy}', [App\Http\Controllers\AcademyController::class, 'destroy'])->name('academies.disable');

    Route::post('/academies', [App\Http\Controllers\AcademyController::class, 'store'])->name('academies.store');
    Route::post('/academies/{academy}', [App\Http\Controllers\AcademyController::class, 'update'])->name('academies.update');
    Route::post('/academies/{academy/schools/create', [App\Http\Controllers\SchoolController::class, 'storeacademy'])->name('academies.schools.create');
    Route::post('/academies/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'addSchool'])->name('academies.schools.store');
    Route::post('/academies/{academy}/users/create', [App\Http\Controllers\UserController::class, 'storeForAcademy'])->name('academies.users.create');
    Route::post('/academies/{academy}/personnel', [App\Http\Controllers\AcademyController::class, 'addPersonnel'])->name('academies.personnel.store');
    Route::post('/academies/{academy}/athlete', [App\Http\Controllers\AcademyController::class, 'addAthlete'])->name('academies.athlete.store');

    Route::get('/academies/{academy}/users-search', [App\Http\Controllers\AcademyController::class, 'searchUsers'])->name('academies.users-search');
    Route::put('/academies/{academy}/picture', [App\Http\Controllers\AcademyController::class, 'picture'])->name('academies.picture.update');
});

/** Scuole */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/schools', [App\Http\Controllers\SchoolController::class, 'index'])->name('schools.index');
    Route::get('/schools/create', [App\Http\Controllers\SchoolController::class, 'create'])->name('schools.create');
    Route::get('/schools/all', [App\Http\Controllers\SchoolController::class, 'all'])->name('schools.all');
    Route::get('/schools/academy', [App\Http\Controllers\SchoolController::class, 'getByAcademy'])->name('schools.academy');
    Route::get('/schools/search', [App\Http\Controllers\SchoolController::class, 'search'])->name('schools.search');
    Route::get('/schools/{school}/athletes-data', [App\Http\Controllers\SchoolController::class, 'athletesDataForSchool'])->name('schools.athletes-data');
    Route::get('/schools/{school}/athletes-clan-data', [App\Http\Controllers\SchoolController::class, 'athletesClanDataForSchool'])->name('schools.athletes-school-data');
    Route::get('/schools/{school}/athletes-year-data', [App\Http\Controllers\SchoolController::class, 'getAthletesNumberPerYear'])->name('schools.athletes-year-data');

    Route::get('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'edit'])->name('schools.edit');
    Route::delete('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'destroy'])->name('schools.disable');

    Route::post('/schools', [App\Http\Controllers\SchoolController::class, 'store'])->name('schools.store');
    Route::post('/schools/{school}', [App\Http\Controllers\SchoolController::class, 'update'])->name('schools.update');


    Route::post('/schools/{school}/users/create', [App\Http\Controllers\UserController::class, 'storeForSchool'])->name('schools.users.create');
    Route::post('/schools/{school}/clan/create', [App\Http\Controllers\ClanController::class, 'storeForSchool'])->name('schools.clan.create');

    Route::post('/schools/{school}/clans', [App\Http\Controllers\SchoolController::class, 'addClan'])->name('schools.clans.store');
    Route::post('/schools/{school}/personnel', [App\Http\Controllers\SchoolController::class, 'addPersonnel'])->name('schools.personnel.store');
    Route::post('/schools/{school}/athlete', [App\Http\Controllers\SchoolController::class, 'addAthlete'])->name('schools.athlete.store');

    Route::get('/schools/{school}/users-search', [App\Http\Controllers\SchoolController::class, 'searchUsers'])->name('schools.users-search');
});

Route::group(['middleware' => ['auth']], function () {
    Route::get('/verify-address', [App\Http\Controllers\SchoolController::class, 'verifyAddress'])->name('schools.verify-address');
});

/** Clan */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/courses', [App\Http\Controllers\ClanController::class, 'index'])->name('clans.index');
    Route::get('/courses/create', [App\Http\Controllers\ClanController::class, 'create'])->name('clans.create');

    Route::get('/courses/all', [App\Http\Controllers\ClanController::class, 'all'])->name('clans.all');
    Route::get('/courses/search', [App\Http\Controllers\ClanController::class, 'search'])->name('clans.search');
    Route::get('/courses/school', [App\Http\Controllers\ClanController::class, 'getBySchool'])->name('clans.school');

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
    Route::delete('/events/{event}', [App\Http\Controllers\EventController::class, 'destroy'])->name('events.disable');

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
    Route::post('/events/{event}/reject', [App\Http\Controllers\EventController::class, 'reject'])->name('events.reject');
    Route::post('/events/{event}/approve', [App\Http\Controllers\EventController::class, 'approve'])->name('events.approve');
    Route::post('/events/{event}/publish', [App\Http\Controllers\EventController::class, 'publish'])->name('events.publish');
    Route::post('/events/{event}/participants', [App\Http\Controllers\EventController::class, 'addParticipant'])->name('events.participants.store');
    Route::post('/events/{event}/results', [App\Http\Controllers\EventController::class, 'addResult'])->name('events.results.store');

    Route::post('events/{event}/description', [App\Http\Controllers\EventController::class, 'saveDescription'])->name('events.save.description');
    Route::post('events/{event}/location', [App\Http\Controllers\EventController::class, 'saveLocation'])->name('events.save.location');
    Route::put('events/{event}/thumbnail', [App\Http\Controllers\EventController::class, 'updateThumbnail'])->name('events.update.thumbnail');

    Route::get('events/{event}/participants', [App\Http\Controllers\EventController::class, 'participants'])->name('events.participants');
    Route::get('events/{event}/available-users', [App\Http\Controllers\EventController::class, 'available'])->name('events.available');
    Route::get('events/{event}/available-personnel', [App\Http\Controllers\EventController::class, 'availablePersonnel'])->name('events.available_personnel');
    Route::get('events/{event}/personnel', [App\Http\Controllers\EventController::class, 'personnel'])->name('events.personnel');
    Route::post('events/{event}/add-personnel', [App\Http\Controllers\EventController::class, 'addPersonnel'])->name('events.add_personnel');

    Route::post('add-participants', [App\Http\Controllers\EventController::class, 'selectParticipants'])->name('events.participants.add');
    Route::get('events/{event}/participants/export', [App\Http\Controllers\EventController::class, 'exportParticipants'])->name('events.participants.export');

    Route::post('/submit-enabling-result/{event}', [App\Http\Controllers\EventController::class, 'confirmEventInstructorResult'])->name('events.instructor_result');
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
    Route::get('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('exports.download');
    Route::delete('/exports/{export}', [App\Http\Controllers\ExportController::class, 'destroy'])->name('exports.disable');

    Route::post('/exports', [App\Http\Controllers\ExportController::class, 'store'])->name('exports.store');
    Route::post('/exports/{export}', [App\Http\Controllers\ExportController::class, 'update'])->name('exports.update');
    // Route::post('/exports/{export}/download', [App\Http\Controllers\ExportController::class, 'download'])->name('exports.download');
});


/** Rankings and Charts */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/rankings', [App\Http\Controllers\ChartController::class, 'index'])->name('rankings.index');
    Route::get('/rankings/paginate', [App\Http\Controllers\ChartController::class, 'paginate'])->name('rankings.paginate');
});

/** Ranks requests */

Route::group(['middleware' => ['auth', 'role:admin']], function () {

    Route::get('/rank-requests', [App\Http\Controllers\RankController::class, 'requests'])->name('rank-requests.index');
    Route::get('/pending-rank-requests', [App\Http\Controllers\RankController::class, 'countPendingRequests'])->name('pending-rank-requests.index');
    Route::get('/rank-requests/approve-all', [App\Http\Controllers\RankController::class, 'acceptAllRequests'])->name('rank-requests.approve-all');

    Route::get('/rank-requests/{request}/approve', [App\Http\Controllers\RankController::class, 'acceptRequest'])->name('rank-requests.approve');
    Route::get('/rank-requests/{request}/reject', [App\Http\Controllers\RankController::class, 'rejectRequest'])->name('rank-requests.reject');
    Route::get('/rank-requests/{request}/delete', [App\Http\Controllers\RankController::class, 'deleteRequest'])->name('rank-requests.delete');
});


Route::group(['middleware' => ['auth', 'role:instructor,rector,dean,technician']], function () {
    Route::get('/rank-request', [App\Http\Controllers\RankController::class, 'rankRequestForm'])->name('users.rank.request');
    Route::post('/rank-request', [App\Http\Controllers\RankController::class, 'newRequest'])->name('users.rank.request.create');
    Route::get('/users-select', [App\Http\Controllers\UserController::class, 'searchJson'])->name('users-select');
});

/** Annunci */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/announcements', [App\Http\Controllers\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/create', [App\Http\Controllers\AnnouncementController::class, 'create'])->name('announcements.create');
    Route::get('/announcements/{announcement}', [App\Http\Controllers\AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::delete('/announcements/{announcement}', [App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('announcements.disable');

    Route::post('/announcements', [App\Http\Controllers\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::post('/announcements/{announcement}', [App\Http\Controllers\AnnouncementController::class, 'update'])->name('announcements.update');
});

/** Ruoli */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/custom-roles', [App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');
    Route::get('/custom-roles/search', [App\Http\Controllers\RoleController::class, 'search'])->name('roles.search');
    Route::post('/custom-roles/assign', [App\Http\Controllers\RoleController::class, 'assign'])->name('roles.assign');
    Route::post('/custom-roles', [App\Http\Controllers\RoleController::class, 'store'])->name('roles.store');
});

/** Ordini */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::post('/orders-invoice/{order}', [App\Http\Controllers\OrderController::class, 'invoice'])->name('orders.update.invoice');

    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\OrderController::class, 'edit'])->name('orders.edit');
    Route::get('/orders/{order}/transaction-result', [App\Http\Controllers\OrderController::class, 'result'])->name('orders.transaction-result');

    Route::post('/orders/{order}/wire', [App\Http\Controllers\OrderController::class, 'approveWireTransfer'])->name('orders.approve-wire');
});

/** Forme armi */

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/weapon-forms', [App\Http\Controllers\WeaponFormController::class, 'index'])->name('weapon-forms.index');
    Route::get('/weapon-forms/create', [App\Http\Controllers\WeaponFormController::class, 'create'])->name('weapon-forms.create');
    Route::get('/weapon-forms/{weaponForm}', [App\Http\Controllers\WeaponFormController::class, 'edit'])->name('weapon-forms.edit');
    Route::post('/weapon-forms', [App\Http\Controllers\WeaponFormController::class, 'store'])->name('weapon-forms.store');
    Route::post('/weapon-forms/{weaponForm}/personnel', [App\Http\Controllers\WeaponFormController::class, 'addPersonnel'])->name('weapon-forms.personnel.store');
    Route::post('/weapon-forms/{weaponForm}/athletes', [App\Http\Controllers\WeaponFormController::class, 'addAthletes'])->name('weapon-forms.athletes.store');
    Route::post('/weapon-forms/{weaponForm}', [App\Http\Controllers\WeaponFormController::class, 'update'])->name('weapon-forms.update');

    Route::put('/weapon-forms/{weaponForm}/image', [App\Http\Controllers\AcademyController::class, 'image'])->name('weapon-forms.image.update');
});



/** Script */

require __DIR__ . '/auth.php';
require __DIR__ . '/technician.php';
require __DIR__ . '/athlete.php';
require __DIR__ . '/site.php';
require __DIR__ . '/dean.php';
require __DIR__ . '/manager.php';
require __DIR__ . '/rector.php';
require __DIR__ . '/instructor.php';
require __DIR__ . '/script.php';

Route::group([], function () {
    Route::get('/healthcheck', function () {
        return 'healthcheck';
    })->name('healthcheck');
});
