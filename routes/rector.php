<?php

use Illuminate\Support\Facades\Route;

Route::prefix('rector')->middleware(['auth', 'role:rector'])->group(function () {
    Route::get('/fees', 'App\Http\Controllers\FeeController@index')->name('fees.index');
    Route::get('/fees/purchase', 'App\Http\Controllers\FeeController@create')->name('dean.fees.purchase');
    Route::get('/invoices/user-data/{user}', [App\Http\Controllers\UserController::class, 'invoiceData'])->name('users.invoices.get');
    Route::post('/invoices/store', [App\Http\Controllers\UserController::class, 'saveInvoice'])->name('users.invoices.store');


    #Stripe 

    Route::get('/fees/stripe-checkout', [App\Http\Controllers\FeeController::class, 'checkoutStripe'])->name('fees.checkout');
    Route::get('/fees/success', [App\Http\Controllers\FeeController::class, 'success'])->name('fees.success');
    Route::get('/fees/cancel', [App\Http\Controllers\FeeController::class, 'cancel'])->name('fees.cancel');
    Route::get('/fees/extimate', [App\Http\Controllers\FeeController::class, 'extimateFeeConsumption'])->name('fees.extimate');
    Route::post('/fees/associate', [App\Http\Controllers\FeeController::class, 'associateFeesToUsers'])->name('fees.associate');
});
