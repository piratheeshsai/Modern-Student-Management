<?php

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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


    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:Active,Completed,Cancelled',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
            'payment_plan' => 'required|in:full,2-instalments,3-instalments,4-instalments',
            'advance_amount' => 'required_if:payment_plan,2-instalments,3-instalments,4-instalments|numeric|min:0',
            'notes' => 'nullable|string',
            'payments' => 'required|array|min:1',
            'payments.*.type' => 'required|string',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.due_date' => 'required|date',
            'payments.*.status' => 'required|in:Paid,Pending',
        ]);

        DB::beginTransaction();

        try {
            // Get course details
            $course = Course::findOrFail($request->course_id);

            // Calculate discount amount
            $discountAmount = 0;
            if ($request->discount_type === 'percentage') {
                $discountAmount = ($course->fees * $request->discount_value) / 100;
            } else {
                $discountAmount = $request->discount_value;
            }

            // Calculate total amount
            $totalAmount = $course->fees - $discountAmount;

            // Create enrollment
            $enrollment = Enrollment::create([
                'student_id' => $request->student_id,
                'course_id' => $request->course_id,
                'enrollment_date' => $request->enrollment_date,
                'status' => strtolower($request->status), // Convert to lowercase to match schema default
                'branch_id' => Auth::user()->branch_id ?? 1, // Assuming user has branch_id or default to 1
                'created_by' => Auth::id(),
                'discount_value' => $discountAmount,
                'discount_type' => $request->discount_type,
                'total_amount' => $totalAmount,
                'payment_type' => $request->payment_plan, //
                'note' => $request->notes,

            ]);

            // Create payment records
            foreach ($request->payments as $paymentData) {
                Payment::create([
                    'enrollment_id' => $enrollment->id,
                    'amount' => $paymentData['amount'],
                    'payment_type' => $paymentData['type'],
                    'due_date' => $paymentData['due_date'],
                    'status' => strtolower($paymentData['status']), // Convert to lowercase
                    'payment_date' => $paymentData['status'] === 'Paid' ? now() : null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enrollment created successfully',
                'data' => $enrollment->load('student', 'course', 'payments')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create enrollment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
