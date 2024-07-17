<?php

use Illuminate\Support\Facades\Route;

Route::prefix('rector')->middleware(['auth', 'role:rector'])->group(function () {
    Route::get('/fees', 'App\Http\Controllers\FeeController@index')->name('fees.index');
    Route::get('/fees/purchase', 'App\Http\Controllers\FeeController@create')->name('dean.fees.purchase');
    Route::get('/invoices/user-data/{user}', [App\Http\Controllers\UserController::class, 'invoiceData'])->name('users.invoices.get');
    Route::post('/invoices/store', [App\Http\Controllers\UserController::class, 'saveInvoice'])->name('users.invoices.store');
});
