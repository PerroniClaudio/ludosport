<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    // return view('welcome');
    return view('homepage');
})->name('homepage');




Route::get('/shop', function () {
    return view('website.shop');
})->name('shop');

Route::get('/user-search', function () {
    return view('website.user-search');
})->name('user-search');

Route::get('/athlete-profile/{id}', function () {
    return view('website.athlete-profile');
})->name('athlete-profile');

/** Academy Map */

Route::get('/academies-map', [App\Http\Controllers\SchoolController::class, 'schoolsMap'])->name('schools-map');
Route::get('/school-profile/{school:slug}', [App\Http\Controllers\SchoolController::class, 'detail'])->name('school-profile');
Route::get('/academy-image/{academy}', [App\Http\Controllers\AcademyController::class, 'academyImage'])->name('academy-image');
Route::middleware('throttle:rate_limit,1')->get('/schools-search', [App\Http\Controllers\SchoolController::class, 'searchSchools'])->name('schools-search');

/** Rankings */

Route::prefix('/website-rankings')->group(function () {

    Route::get('/', [App\Http\Controllers\EventController::class, 'rankings'])->name('rankings-website');

    Route::get('/general', [App\Http\Controllers\EventController::class, 'general'])->name('rankings-events-results');
    Route::get('/events/list', [App\Http\Controllers\EventController::class, 'list'])->name('rankings-events-list');
    Route::get('/events/{event}/rankings', [App\Http\Controllers\EventController::class, 'eventResult'])->name('rankings-events-show');
    Route::get('/nation/{nation_id}/rankings', [App\Http\Controllers\EventController::class, 'nation'])->name('rankings-events-nation');
});

Route::prefix('/shop')->group(function () {
    Route::get('/', [App\Http\Controllers\ShopController::class, 'shop'])->name('shop');
    Route::get('/activate-membership', [App\Http\Controllers\ShopController::class, 'activate'])->middleware('auth')->name('shop-activate-membership');
    Route::get('/invoices/user-data/{user}', [App\Http\Controllers\UserController::class, 'invoiceData'])->middleware('auth')->name('users.invoices.get');
    Route::get('/fees/stripe/checkout', [App\Http\Controllers\FeeController::class, 'userCheckoutStripe'])->middleware('auth')->name('shop.fees.stripe-checkout');
    Route::post('/invoices/store', [App\Http\Controllers\UserController::class, 'saveInvoice'])->name('shop.invoices.store');


    Route::get('/fees/success', [App\Http\Controllers\FeeController::class, 'successUser'])->middleware('auth')->name('shop.fees.success');
    Route::get('/fees/cancel', [App\Http\Controllers\FeeController::class, 'cancelUser'])->middleware('auth')->name('shop.fees.cancel');

    # PayPal

    Route::post('/fees/paypal/checkout', [App\Http\Controllers\FeeController::class, 'userCheckoutPaypal'])->middleware('auth')->name('shop.fees.paypal-checkout');
    Route::get('/fees/paypal/success', [App\Http\Controllers\FeeController::class, 'successUserPaypal'])->middleware('auth')->name('shop.fees.paypal-success');
    Route::get('/fees/paypal/cancel', [App\Http\Controllers\FeeController::class, 'cancelUserPaypal'])->middleware('auth')->name('shop.fees.paypal-cancel');

    # Wire Transfer

    Route::get('/fees/wire-transfer', [App\Http\Controllers\FeeController::class, 'userCheckoutWireTransfer'])->middleware('auth')->name('shop.fees.wire-transfer');

    Route::get('/wire-transfer/{order}/success', [App\Http\Controllers\OrderController::class, 'successUserWireTransfer'])->middleware('auth')->name('shop.wire-transfer-success');
});

Route::group(['middleware' => ['auth']], function () {
    Route::get('/events-list', [App\Http\Controllers\EventController::class, 'eventsList'])->name('events-list');
    Route::get('/events-detail/{event:slug}', [App\Http\Controllers\EventController::class, 'show'])->name('event-detail');
    Route::get('/event-purchase/{event:id}', [App\Http\Controllers\EventController::class, 'purchase'])->name('event-purchase');

    Route::get('/shop/event/{event}/stripe/checkout', [App\Http\Controllers\EventController::class, 'userCheckoutStripe'])->middleware('auth')->name('shop.events.stripe-checkout');
    Route::get('/shop/event/success', [App\Http\Controllers\EventController::class, 'successUser'])->middleware('auth')->name('shop.event.success');
    Route::get('/shop/event/cancel', [App\Http\Controllers\EventController::class, 'cancelUser'])->middleware('auth')->name('shop.event.cancel');
    Route::get('/shop/event/{event}/stripe/preauth', [App\Http\Controllers\EventController::class, 'userPreauthorizeStripe'])->middleware('auth')->name('shop.event.stripe-preauth');
    // Route::get('/shop/event/stripe/preauth-success', [App\Http\Controllers\EventController::class, 'preauthSuccessUserStripe'])->middleware('auth')->name('shop.event.stripe-preauth-success');
    Route::get('/shop/event/stripe/preauth-cancel', [App\Http\Controllers\EventController::class, 'preauthCancelUserStripe'])->middleware('auth')->name('shop.event.stripe-preauth-cancel');

    Route::post('/shop/event/{event}/paypal/checkout', [App\Http\Controllers\EventController::class, 'userCheckoutPaypal'])->middleware('auth')->name('shop.events.paypal-checkout');
    Route::get('/shop/event/paypal/success', [App\Http\Controllers\EventController::class, 'successUserPaypal'])->middleware('auth')->name('shop.event.paypal-success');
    Route::post('/shop/event/{event}/paypal/preauth', [App\Http\Controllers\EventController::class, 'userPreauthorizePaypal'])->middleware('auth')->name('shop.event.paypal-preauth');
    Route::get('/shop/event/paypal/preauth-success', [App\Http\Controllers\EventController::class, 'preauthSuccessUserPaypal'])->middleware('auth')->name('shop.event.paypal-preauth-success');
    Route::get('/shop/event/paypal/preauth-cancel', [App\Http\Controllers\EventController::class, 'preauthCancelUserPaypal'])->middleware('auth')->name('shop.event.paypal-preauth-cancel');
    Route::get('/shop/event/paypal/cancel', [App\Http\Controllers\EventController::class, 'cancelUserPaypal'])->middleware('auth')->name('shop.event.paypal-cancel');
});

Route::group(['middleware' => ['auth']], function () {
    // Route::get('/events-list', function () {
    //     return view('website.events-list');
    // })->name('events-list');
    Route::get('/my-profile', function () {
        return view('website.my-profile');
    })->name('my-profile');
});

Route::middleware('throttle:rate_limit,1')->get('/website-users/search', [App\Http\Controllers\UserController::class, 'searchJson'])->name('website-users-search');
Route::get('/profile-picture/{user}', [App\Http\Controllers\UserController::class, 'propic'])->name('profile-picture');
Route::get('/website-users/{user:battle_name}', [App\Http\Controllers\UserController::class, 'show'])->name('website-users-show');
