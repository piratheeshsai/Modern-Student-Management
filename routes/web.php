<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Branches\BranchController;
use App\Http\Controllers\Courses\CoursesController;
use App\Http\Controllers\Enrollment\EnrollmentController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Reference\ReferenceController;
use App\Http\Controllers\students\StudentRegistrationController;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Add your page routes (if you have any)

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/students', function () {
        return Inertia::render('Students/index');
    })->name('students.page');


    Route::get('/students-register', function () {
        return Inertia::render('Students/register');
    });

    Route::get('/branches', function () {
        return Inertia::render('Branches/index');
    })->name('branches.page');

    Route::get('/courses', function () {
        return Inertia::render('Course/index');
    })->name('courses.page');



    Route::get('/payments', function () {
           return Inertia::render('Payments/index');
       })->name('payments.page');


 Route::get('/attendance', function () {
           return Inertia::render('Attendance/index');
       })->name('attendance.page');


    Route::get('/reference', function () {
        return Inertia::render('Reference/reference');
    })->name('reference.page');

    Route::get('/students-register/{id}', function ($id) {
        // Fetch the student by $id and pass to the page
        $student = Student::with(['course', 'branch', 'referralSource'])->findOrFail($id);
        return Inertia::render('Students/register', [
            'student' => $student,
            'isEditing' => true,
        ]);
    });



    Route::get('/enrollments', function () {
        return Inertia::render('Enrollment/enrollments');
    })->name('enrollments.page');

    Route::get('/enrollments/create', function () {
        return Inertia::render('Enrollment/create');
    })->name('enrollments.create');


    Route::get('/enrollments/{id}/edit', function ($id) {
        // Fetch the enrollment and pass to the page
        $enrollment = Enrollment::with(['student', 'course', 'branch', 'payments'])->findOrFail($id);
        return Inertia::render('Enrollment/create', [
            'enrollment' => $enrollment,
            'mode' => 'edit',
        ]);
    })->name('enrollments.edit.page');

    Route::get('/enrollments/{id}/view', function ($id) {
        $enrollment = Enrollment::with(['student', 'course', 'branch', 'payments'])->findOrFail($id);
        return Inertia::render('Enrollment/create', [
            'enrollment' => $enrollment,
            'mode' => 'view',
        ]);
    })->name('enrollments.view.page');




});

// Public API routes (no auth required) - for reading data
Route::prefix('api')->group(function () {
    Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
    Route::get('courses', [CoursesController::class, 'index'])->name('courses.index');
    Route::get('students', [StudentRegistrationController::class, 'index'])->name('students.index');
    Route::get('reference', [ReferenceController::class, 'index'])->name('reference.index');
    Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
});

// Protected API routes (auth required) - for data manipulation
Route::middleware(['auth'])->prefix('api')->group(function () {

    // Branches management
    Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
    Route::put('branches/{id}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('branches/{id}', [BranchController::class, 'destroy'])->name('branches.destroy');

    // Courses management
    Route::post('courses', [CoursesController::class, 'store'])->name('courses.store');
    Route::put('courses/{id}', [CoursesController::class, 'update'])->name('courses.update');
    Route::delete('courses/{id}', [CoursesController::class, 'destroy'])->name('courses.destroy');

    // Reference management
    Route::post('reference', [ReferenceController::class, 'store'])->name('reference.store');
    Route::put('reference/{id}', [ReferenceController::class, 'update'])->name('reference.update');
    Route::delete('reference/{id}', [ReferenceController::class, 'destroy'])->name('reference.destroy');

    // Students management
    Route::post('students', [StudentRegistrationController::class, 'store'])->name('students.store');
    Route::put('students/{id}', [StudentRegistrationController::class, 'update'])->name('students.update');
    Route::delete('students/{id}', [StudentRegistrationController::class, 'destroy'])->name('students.destroy');
    Route::patch('students/{student}/verify', [StudentRegistrationController::class, 'verify'])->name('students.verify');
    Route::patch('students/{student}/unverify', [StudentRegistrationController::class, 'unverify'])->name('students.unverify');

    // Enrollments management
    Route::post('enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::put('enrollments/{id}', [EnrollmentController::class, 'update'])->name('enrollments.update');
    Route::delete('enrollments/{id}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
    Route::get('enrollments/{id}', [EnrollmentController::class, 'show'])->name('enrollments.show');
    Route::get('enrollments/{id}/edit', [EnrollmentController::class, 'edit'])->name('enrollments.edit');
});

require __DIR__ . '/auth.php';
