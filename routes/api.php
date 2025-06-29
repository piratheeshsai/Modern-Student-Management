<?php


use App\Http\Controllers\Branches\BranchController;
use App\Http\Controllers\Courses\CoursesController;
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
    Route::delete('/{id}', [CoursesController::class, 'destroy'])->name('branches.destroy');

});
