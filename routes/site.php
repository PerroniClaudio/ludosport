<?php

use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    // return view('welcome');
    return view('homepage');
})->name('homepage');

Route::get('/academies-map', function () {
    return view('website.academies-map');
})->name('academies-map');

Route::get('/rankings-website', function () {
    return view('website.rankings');
})->name('rankings-website');

Route::get('/shop', function () {
    return view('website.shop');
})->name('shop');

Route::get('/user-search', function () {
    return view('website.user-search');
})->name('user-search');

Route::get('/athlete-profile/{id}', function () {
    return view('website.athlete-profile');
})->name('athlete-profile');

Route::get('/academy-profile/{id}', function () {
    return view('website.academy-profile');
})->name('academy-profile');

Route::group(['middleware' => ['auth']], function () {
    // Route::get('/events-list', function () {
    //     return view('website.events-list');
    // })->name('events-list');
    Route::get('/my-profile', function () {
        return view('website.my-profile');
    })->name('my-profile');
});


Route::get('/academies-search', [App\Http\Controllers\AcademyController::class, 'searchAcademies'])->name('academies-search');
