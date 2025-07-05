<?php


use App\Http\Controllers\Branches\BranchController;
use App\Http\Controllers\Courses\CoursesController;
use App\Http\Controllers\Enrollment\EnrollmentController;
use App\Http\Controllers\Reference\ReferenceController;
use App\Http\Controllers\students\StudentRegistrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('branches')->group(function () {
    Route::get('/', [BranchController::class, 'index'])->name('branches.index');
    Route::post('/', [BranchController::class, 'store'])->name('branches.store');
    Route::put('/{id}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('/{id}', [BranchController::class, 'destroy'])->name('branches.destroy');
});

Route::prefix('courses')->group(function () {
    Route::get('/', [CoursesController::class, 'index'])->name('courses.index');
    Route::post('/', [CoursesController::class, 'store'])->name('courses.store');
    Route::put('/{id}', [CoursesController::class, 'update'])->name('courses.update');
    Route::delete('/{id}', [CoursesController::class, 'destroy'])->name('courses.destroy');

});

Route::prefix('reference')->group(function () {
    Route::get('/', [ReferenceController::class, 'index'])->name('reference.index');
    Route::post('/', [ReferenceController::class, 'store'])->name('reference.store');
    Route::put('/{id}', [ReferenceController::class, 'update'])->name('reference.update');
    Route::delete('/{id}', [ReferenceController::class, 'destroy'])->name('reference.destroy');

});

Route::prefix('students')->group(function () {
    Route::get('/', [StudentRegistrationController::class, 'index'])->name('students.index');
    Route::post('/', [StudentRegistrationController::class, 'store'])->name('students.store');
    Route::put('/{id}', [StudentRegistrationController::class, 'update'])->name('students.update');
    Route::delete('/{id}', [StudentRegistrationController::class, 'destroy'])->name('students.destroy');

});

Route::prefix('enrollments')->group(function () {
    Route::get('/', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::post('/', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::put('/{id}', [EnrollmentController::class, 'update'])->name('enrollments.update');
    Route::delete('/{id}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');

});
