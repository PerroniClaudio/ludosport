<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {

    $rows = [];

    for ($i = 0; $i < 50; $i++) {
        $rows[] = [
            "name" => fake()->name(),
            "email" => fake()->email(),
        ];
    }

    return view('dashboard', [
        'rows' => $rows
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');

    Route::get('/nation/{nation}/academies', [App\Http\Controllers\NationController::class, 'academies'])->name('nation.academies.index');
    Route::get('/academy/{academy}/schools', [App\Http\Controllers\AcademyController::class, 'schools'])->name('academies.schools.index');

});

require __DIR__ . '/auth.php';
