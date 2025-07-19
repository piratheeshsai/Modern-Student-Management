<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Payment::with(['enrollment.student', 'enrollment.course', 'enrollment.branch']);

            // Debug: Log the incoming request
            Log::info('Payment filter request:', [
                'filters' => $request->get('filters'),
                'all_params' => $request->all()
            ]);

            // Handle filters
            if ($request->has('filters')) {
                $filters = json_decode($request->get('filters'), true);

                // Debug: Log parsed filters
                Log::info('Parsed filters:', $filters);

                // Global search
                if (isset($filters['global']['value']) && !empty($filters['global']['value'])) {
                    $globalSearch = $filters['global']['value'];
                    $query->where(function ($q) use ($globalSearch) {
                        $q->whereHas('enrollment.student', function ($studentQuery) use ($globalSearch) {
                            $studentQuery->where('first_name', 'like', "%{$globalSearch}%")
                                ->orWhere('last_name', 'like', "%{$globalSearch}%")
                                ->orWhere('student_no', 'like', "%{$globalSearch}%")
                                ->orWhere('email', 'like', "%{$globalSearch}%");
                        })
                        ->orWhere('amount', 'like', "%{$globalSearch}%")
                        ->orWhere('payment_type', 'like', "%{$globalSearch}%")
                        ->orWhere('status', 'like', "%{$globalSearch}%");
                    });
                }

                // Date range filtering - SIMPLIFIED VERSION
                $hasDateFilter = false;

                if (isset($filters['date_from']['value']) && !empty($filters['date_from']['value'])) {
                    $dateFrom = $filters['date_from']['value'];
                    $dateFilterType = $filters['date_filter_type']['value'] ?? 'due_date';

                    Log::info('Date FROM filter:', ['date' => $dateFrom, 'type' => $dateFilterType]);

                    if ($dateFilterType === 'both') {
                        $query->where(function($q) use ($dateFrom) {
                            $q->whereDate('due_date', '>=', $dateFrom)
                              ->orWhereDate('paid_date', '>=', $dateFrom);
                        });
                    } elseif ($dateFilterType === 'paid_date') {
                        $query->whereDate('paid_date', '>=', $dateFrom);
                    } else { // due_date
                        $query->whereDate('due_date', '>=', $dateFrom);
                    }
                    $hasDateFilter = true;
                }

                if (isset($filters['date_to']['value']) && !empty($filters['date_to']['value'])) {
                    $dateTo = $filters['date_to']['value'];
                    $dateFilterType = $filters['date_filter_type']['value'] ?? 'due_date';

                    Log::info('Date TO filter:', ['date' => $dateTo, 'type' => $dateFilterType]);

                    if ($dateFilterType === 'both') {
                        $query->where(function($q) use ($dateTo) {
                            $q->whereDate('due_date', '<=', $dateTo)
                              ->orWhereDate('paid_date', '<=', $dateTo);
                        });
                    } elseif ($dateFilterType === 'paid_date') {
                        $query->whereDate('paid_date', '<=', $dateTo);
                    } else { // due_date
                        $query->whereDate('due_date', '<=', $dateTo);
                    }
                    $hasDateFilter = true;
                }

                // Other filters
                if (isset($filters['student']['value']) && !empty($filters['student']['value'])) {
                    $studentSearch = $filters['student']['value'];
                    $query->whereHas('enrollment.student', function ($studentQuery) use ($studentSearch) {
                        $studentQuery->where('first_name', 'like', "%{$studentSearch}%")
                            ->orWhere('last_name', 'like', "%{$studentSearch}%")
                            ->orWhere('student_no', 'like', "%{$studentSearch}%")
                            ->orWhere('email', 'like', "%{$studentSearch}%");
                    });
                }

                if (isset($filters['status']['value']) && !empty($filters['status']['value'])) {
                    $query->where('status', $filters['status']['value']);
                }

                if (isset($filters['payment_type']['value']) && !empty($filters['payment_type']['value'])) {
                    $query->where('payment_type', $filters['payment_type']['value']);
                }

                if (isset($filters['course']['value']) && !empty($filters['course']['value'])) {
                    $query->whereHas('enrollment.course', function ($courseQuery) use ($filters) {
                        $courseQuery->where('name', 'like', "%{$filters['course']['value']}%");
                    });
                }

                if (isset($filters['branch']['value']) && !empty($filters['branch']['value'])) {
                    $query->whereHas('enrollment.branch', function ($branchQuery) use ($filters) {
                        $branchQuery->where('name', 'like', "%{$filters['branch']['value']}%");
                    });
                }
            }

            // Handle sorting
            $sortField = $request->get('sort_field', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            if ($sortField === 'student.full_name') {
                $query->join('enrollments', 'payments.enrollment_id', '=', 'enrollments.id')
                    ->join('students', 'enrollments.student_id', '=', 'students.id')
                    ->orderByRaw("CONCAT(students.first_name, ' ', students.last_name) $sortOrder")
                    ->select('payments.*');
            } elseif ($sortField === 'course.name') {
                $query->join('enrollments', 'payments.enrollment_id', '=', 'enrollments.id')
                    ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                    ->orderBy('courses.name', $sortOrder)
                    ->select('payments.*');
            } elseif ($sortField === 'branch.name') {
                $query->join('enrollments', 'payments.enrollment_id', '=', 'enrollments.id')
                    ->join('branches', 'enrollments.branch_id', '=', 'branches.id')
                    ->orderBy('branches.name', $sortOrder)
                    ->select('payments.*');
            } else {
                $query->orderBy($sortField, $sortOrder);
            }

            // Debug: Log the final SQL query
            Log::info('Final SQL Query:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $payments = $query->paginate($perPage, ['*'], 'page', $page);

            // Debug: Log results count
            Log::info('Query results:', ['total' => $payments->total(), 'current_page_count' => $payments->count()]);

            // Transform data for frontend
            $transformedData = $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_type' => $payment->payment_type,
                    'due_date' => $payment->due_date,
                    'paid_date' => $payment->paid_date,
                    'status' => $payment->status,
                    'notes' => $payment->notes,
                    'refund_amount' => $payment->refund_amount,
                    'refund_reason' => $payment->refund_reason,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at,
                    'enrollment' => $payment->enrollment ? [
                        'id' => $payment->enrollment->id,
                        'enrollment_date' => $payment->enrollment->enrollment_date,
                        'status' => $payment->enrollment->status,
                        'student' => $payment->enrollment->student ? [
                            'id' => $payment->enrollment->student->id,
                            'student_no' => $payment->enrollment->student->student_no,
                            'first_name' => $payment->enrollment->student->first_name,
                            'last_name' => $payment->enrollment->student->last_name,
                            'full_name' => $payment->enrollment->student->first_name . ' ' . $payment->enrollment->student->last_name,
                            'email' => $payment->enrollment->student->email,
                            'phone' => $payment->enrollment->student->phone ?? null,
                        ] : null,
                        'course' => $payment->enrollment->course ? [
                            'id' => $payment->enrollment->course->id,
                            'name' => $payment->enrollment->course->name,
                            'code' => $payment->enrollment->course->code ?? null,
                            'duration' => $payment->enrollment->course->duration ?? null,
                            'fee' => $payment->enrollment->course->fee ?? null,
                        ] : null,
                        'branch' => $payment->enrollment->branch ? [
                            'id' => $payment->enrollment->branch->id,
                            'name' => $payment->enrollment->branch->name,
                            'address' => $payment->enrollment->branch->address ?? null,
                        ] : null,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedData,
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
            ]);

        } catch (\Exception $e) {
            Log::error('Payments index error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payments',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
