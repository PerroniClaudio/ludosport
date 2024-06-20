<?php

use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
    //return view('homepage');
});

Route::get('/academies-map', function () {
    return view('website.academies-map');
})->name('academies-map');

Route::get('/academies-search', [App\Http\Controllers\AcademyController::class, 'searchAcademies'])->name('academies-search');
