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

Route::get('/enrollments', function () {
    return Inertia::render('Enrollment/enrollments');
});


Route::get('/students-register/{id}', function ($id) {
    // Fetch the student by $id and pass to the page
    $student = \App\Models\Student::with(['course', 'branch', 'referralSource'])->findOrFail($id);
    return Inertia::render('Students/register', [
        'student' => $student,
        'isEditing' => true,
    ]);
});



Route::get('/students', function () {
    return Inertia::render('Students/index');
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
