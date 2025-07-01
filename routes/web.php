<?php

use App\Http\Controllers\ProfileController;
use App\Models\Branch;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// Route::get('/branches', function () {
//     return Inertia::render('Branches', [
//         'branches' => Branch::all(),
//     ]);
// });

Route::get('/branches', function () {
    return Inertia::render('Branches/index');
});


Route::get('/reference', function () {
    return Inertia::render('Reference/reference');
});


Route::get('/students-register', function () {
    return Inertia::render('Students/register');
});


Route::get('/courses', function () {
    return Inertia::render('Course/index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
