<?php

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Enrollment::with(['student', 'course', 'branch', 'referralSource']);

            // Handle filters
            if ($request->has('filters')) {
                $filters = json_decode($request->get('filters'), true);

                // Global search
                if (isset($filters['global']['value']) && !empty($filters['global']['value'])) {
                    $globalSearch = $filters['global']['value'];
                    $query->where(function ($q) use ($globalSearch) {
                        $q->whereHas('student', function ($studentQuery) use ($globalSearch) {
                            $studentQuery->where('first_name', 'like', "%{$globalSearch}%")
                                         ->orWhere('last_name', 'like', "%{$globalSearch}%")
                                         ->orWhere('student_no', 'like', "%{$globalSearch}%")
                                         ->orWhere('email', 'like', "%{$globalSearch}%");
                        })
                        ->orWhereHas('course', function ($courseQuery) use ($globalSearch) {
                            $courseQuery->where('name', 'like', "%{$globalSearch}%");
                        })
                        ->orWhereHas('branch', function ($branchQuery) use ($globalSearch) {
                            $branchQuery->where('name', 'like', "%{$globalSearch}%");
                        });
                    });
                }

                // Enrollment-specific filters
                if (isset($filters['status']['value']) && !empty($filters['status']['value'])) {
                    $query->where('status', $filters['status']['value']);
                }
                if (isset($filters['enrollment_date']['value']) && !empty($filters['enrollment_date']['value'])) {
                    $query->whereDate('enrollment_date', $filters['enrollment_date']['value']);
                }
                if (isset($filters['course.name']['value']) && !empty($filters['course.name']['value'])) {
                    $query->whereHas('course', function ($q) use ($filters) {
                        $q->where('name', $filters['course.name']['value']);
                    });
                }
                if (isset($filters['branch.name']['value']) && !empty($filters['branch.name']['value'])) {
                    $query->whereHas('branch', function ($q) use ($filters) {
                        $q->where('name', $filters['branch.name']['value']);
                    });
                }
            }

            // Handle sorting
            if ($request->has('sort_field') && !empty($request->get('sort_field'))) {
                $sortField = $request->get('sort_field');
                $sortOrder = $request->get('sort_order', 'asc');

                if ($sortField === 'course.name') {
                    $query->join('courses', 'enrollments.course_id', '=', 'courses.id')
                          ->orderBy('courses.name', $sortOrder)
                          ->select('enrollments.*');
                } elseif ($sortField === 'branch.name') {
                    $query->join('branches', 'enrollments.branch_id', '=', 'branches.id')
                          ->orderBy('branches.name', $sortOrder)
                          ->select('enrollments.*');
                } elseif ($sortField === 'student.full_name') {
                    $query->join('students', 'enrollments.student_id', '=', 'students.id')
                          ->orderByRaw("CONCAT(students.first_name, ' ', students.last_name) $sortOrder")
                          ->select('enrollments.*');
                } else {
                    $query->orderBy($sortField, $sortOrder);
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $enrollments = $query->paginate($perPage, ['*'], 'page', $page);

            // Transform data for frontend
            $transformedData = $enrollments->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'status' => $enrollment->status,
                    'enrollment_date' => $enrollment->enrollment_date,
                    'created_at' => $enrollment->created_at,
                    'student' => $enrollment->student ? [
                        'id' => $enrollment->student->id,
                        'student_no' => $enrollment->student->student_no,
                        'first_name' => $enrollment->student->first_name,
                        'last_name' => $enrollment->student->last_name,
                        'full_name' => $enrollment->student->first_name . ' ' . $enrollment->student->last_name,
                        'email' => $enrollment->student->email,
                    ] : null,
                    'course' => $enrollment->course ? [
                        'id' => $enrollment->course->id,
                        'name' => $enrollment->course->name,
                    ] : null,
                    'branch' => $enrollment->branch ? [
                        'id' => $enrollment->branch->id,
                        'name' => $enrollment->branch->name,
                    ] : null,
                    'referralSource' => $enrollment->referralSource ? [
                        'id' => $enrollment->referralSource->id,
                        'name' => $enrollment->referralSource->name,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedData,
                'total' => $enrollments->total(),
                'per_page' => $enrollments->perPage(),
                'current_page' => $enrollments->currentPage(),
                'last_page' => $enrollments->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Enrollments index error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load enrollments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
