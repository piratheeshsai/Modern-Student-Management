<?php

namespace App\Http\Controllers\students;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentRegistrationController extends Controller
{
    //
    public function index(): JsonResponse
    {
        try {
            // Fetch all students
            $students = Student::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Students retrieved successfully',
                'data' => $students
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve students',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
