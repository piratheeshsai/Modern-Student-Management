<?php

namespace App\Http\Controllers\students;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentRegistrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
       try {
            $query = Student::with(['course', 'branch', 'referralSource']);

            // Handle filters
            if ($request->has('filters')) {
                $filters = json_decode($request->get('filters'), true);

                // Debug log
                Log::info('Received filters:', $filters);

                // Handle global search
                if (isset($filters['global']['value']) && !empty($filters['global']['value'])) {
                    $globalSearch = $filters['global']['value'];
                    $query->where(function ($q) use ($globalSearch) {
                        $q->where('student_no', 'like', "%{$globalSearch}%")
                          ->orWhere('first_name', 'like', "%{$globalSearch}%")
                          ->orWhere('last_name', 'like', "%{$globalSearch}%")
                          ->orWhere('email', 'like', "%{$globalSearch}%")
                          ->orWhere('mobile', 'like', "%{$globalSearch}%")
                          ->orWhereHas('course', function ($courseQuery) use ($globalSearch) {
                              $courseQuery->where('name', 'like', "%{$globalSearch}%");
                          })
                          ->orWhereHas('branch', function ($branchQuery) use ($globalSearch) {
                              $branchQuery->where('name', 'like', "%{$globalSearch}%");
                          });
                    });
                }

                // Handle specific field filters
                if (isset($filters['student_no']['value']) && !empty($filters['student_no']['value'])) {
                    $query->where('student_no', 'like', '%' . $filters['student_no']['value'] . '%');
                }

                if (isset($filters['full_name']['value']) && !empty($filters['full_name']['value'])) {
                    $searchName = $filters['full_name']['value'];
                    $query->where(function ($q) use ($searchName) {
                        $q->where('first_name', 'like', "%{$searchName}%")
                          ->orWhere('last_name', 'like', "%{$searchName}%");
                    });
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

                if (isset($filters['is_approved']['value']) && $filters['is_approved']['value'] !== null) {
                    $query->where('is_approved', $filters['is_approved']['value']);
                }

                if (isset($filters['created_at']['value']) && !empty($filters['created_at']['value'])) {
                    $date = $filters['created_at']['value'];
                    $query->whereDate('created_at', $date);
                }
            }

            // Handle sorting
            if ($request->has('sort_field') && !empty($request->get('sort_field'))) {
                $sortField = $request->get('sort_field');
                $sortOrder = $request->get('sort_order', 'asc');

                Log::info('Sorting applied:', ['field' => $sortField, 'order' => $sortOrder]);

                // Handle relationship sorting
                if ($sortField === 'course.name') {
                    $query->join('courses', 'students.course_id', '=', 'courses.id')
                          ->orderBy('courses.name', $sortOrder)
                          ->select('students.*');
                } elseif ($sortField === 'branch.name') {
                    $query->join('branches', 'students.branch_id', '=', 'branches.id')
                          ->orderBy('branches.name', $sortOrder)
                          ->select('students.*');
                } elseif ($sortField === 'full_name') {
                    // Handle full name sorting
                    $query->orderByRaw("CONCAT(first_name, ' ', last_name) $sortOrder");
                } else {
                    $query->orderBy($sortField, $sortOrder);
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Handle pagination
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $students = $query->paginate($perPage, ['*'], 'page', $page);

            // Transform data for frontend
            $transformedData = $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'student_no' => $student->student_no,
                    'title' => $student->title,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'full_name' => $student->first_name . ' ' . $student->last_name,
                    'id_type' => $student->id_type,
                    'id_no' => $student->id_no,
                    'dob' => $student->dob,
                    'email' => $student->email,
                    'mobile' => $student->mobile,
                    'phone_residence' => $student->phone_residence,
                    'phone_whatsapp' => $student->phone_whatsapp,
                    'address' => $student->address,
                    'school_name' => $student->school_name,
                    'company_name' => $student->company_name,
                    'qualification' => $student->qualification,
                    'is_approved' => $student->is_approved,
                    'is_active' => $student->is_active,
                    'created_at' => $student->created_at,
                    'course' => $student->course ? [
                        'id' => $student->course->id,
                        'name' => $student->course->name,
                    ] : null,
                    'branch' => $student->branch ? [
                        'id' => $student->branch->id,
                        'name' => $student->branch->name,
                    ] : null,
                    'referralSource' => $student->referralSource ? [
                        'id' => $student->referralSource->id,
                        'name' => $student->referralSource->name,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedData,
                'total' => $students->total(),
                'per_page' => $students->perPage(),
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
            ]);

        } catch (\Exception $e) {
            Log::error('Students index error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load students',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Log the incoming request data for debugging
            Log::info('Student registration request data:', $request->all());

            // Use Validator facade for custom messages
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|in:Mr,Ms,Mrs,Miss,Rev,Dr',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'id_type' => 'required|string|in:NIC,P/P,Postal',
                'id_no' => 'required|string|max:50|unique:students,id_no',
                'dob' => 'required|date|before:today',
                'address' => 'required|string|max:500',
                'school_name' => 'nullable|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'course_id' => 'required|integer|exists:courses,id',
                'branch_id' => 'required|integer|exists:branches,id',
                'referral_source_id' => 'nullable|integer|exists:referral_sources,id',
                'email' => 'required|email|unique:students,email|max:255',
                'mobile' => 'required|string|max:15',
                'phone_residence' => 'nullable|string|max:15',
                'phone_whatsapp' => 'nullable|string|max:15',
                'qualification' => 'nullable|string|max:100',
                'preferred_contacts' => 'nullable|array',
                'preferred_contacts.*' => 'string|in:email_personal,phone_res,phone_mobile,whatsapp',
            ], [
                // Title validation messages
                'title.required' => 'Title is required',
                'title.in' => 'Please select a valid title (Mr, Ms, Mrs, Miss, Rev, Dr)',

                // Name validation messages
                'first_name.required' => 'First name is required',
                'first_name.max' => 'First name cannot exceed 255 characters',
                'last_name.required' => 'Last name is required',
                'last_name.max' => 'Last name cannot exceed 255 characters',

                // ID validation messages
                'id_type.required' => 'ID type is required',
                'id_type.in' => 'Please select a valid ID type (NIC, P/P, Postal)',
                'id_no.required' => 'ID number is required',
                'id_no.unique' => 'This ID number is already registered',
                'id_no.max' => 'ID number cannot exceed 50 characters',

                // Date validation messages
                'dob.required' => 'Date of birth is required',
                'dob.date' => 'Please enter a valid date of birth',
                'dob.before' => 'Date of birth must be before today',

                // Address validation messages
                'address.required' => 'Home address is required',
                'address.max' => 'Address cannot exceed 500 characters',

                // School and company validation messages
                'school_name.max' => 'School name cannot exceed 255 characters',
                'company_name.max' => 'Company name cannot exceed 255 characters',

                // Course and branch validation messages
                'course_id.required' => 'Please select a course',
                'course_id.integer' => 'Invalid course selection',
                'course_id.exists' => 'Selected course does not exist',
                'branch_id.required' => 'Please select a branch',
                'branch_id.integer' => 'Invalid branch selection',
                'branch_id.exists' => 'Selected branch does not exist',

                // Referral source validation messages
                'referral_source_id.integer' => 'Invalid referral source selection',
                'referral_source_id.exists' => 'Selected referral source does not exist',

                // Email validation messages
                'email.required' => 'Email address is required',
                'email.email' => 'Please enter a valid email address',
                'email.unique' => 'This email address is already registered',
                'email.max' => 'Email address cannot exceed 255 characters',

                // Phone validation messages
                'mobile.required' => 'Mobile number is required',
                'mobile.max' => 'Mobile number cannot exceed 15 characters',
                'phone_residence.max' => 'Residence phone cannot exceed 15 characters',
                'phone_whatsapp.max' => 'WhatsApp number cannot exceed 15 characters',

                // Qualification validation messages
                'qualification.max' => 'Qualification cannot exceed 100 characters',

                // Preferred contacts validation messages
                'preferred_contacts.array' => 'Invalid preferred contacts format',
                'preferred_contacts.*.in' => 'Invalid preferred contact option',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();

            // Log validated data
            Log::info('Validated data:', $validatedData);

            // Generate student number
            $studentNo = 'STU' . date('Y') . str_pad(Student::count() + 1, 4, '0', STR_PAD_LEFT);

            // Create a new student record
            $student = Student::create([
                'student_no' => $studentNo,
                'title' => $validatedData['title'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'id_type' => $validatedData['id_type'],
                'id_no' => $validatedData['id_no'],
                'dob' => $validatedData['dob'],
                'address' => $validatedData['address'],
                'school_name' => $validatedData['school_name'] ?? null,
                'company_name' => $validatedData['company_name'] ?? null,
                'course_id' => $validatedData['course_id'],
                'branch_id' => $validatedData['branch_id'],
                'referral_source_id' => $validatedData['referral_source_id'] ?? null,
                'email' => $validatedData['email'],
                'mobile' => $validatedData['mobile'],
                'phone_residence' => $validatedData['phone_residence'] ?? null,
                'phone_whatsapp' => $validatedData['phone_whatsapp'] ?? null,
                'qualification' => $validatedData['qualification'] ?? null,
                'is_approved' => false,
                'is_active' => false,
            ]);

            // Load relationships for response
            $student->load(['course', 'branch', 'referralSource']);

            return response()->json([
                'success' => true,
                'message' => 'Student registered successfully',
                'data' => $student
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to register student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $student = Student::with(['course', 'branch', 'referralSource'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Student retrieved successfully',
                'data' => $student
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $student = Student::findOrFail($id);

            // Validate the request data
            $validatedData = $request->validate([
                'title' => 'required|string|in:Mr,Ms,Mrs,Miss,Rev,Dr',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'id_type' => 'required|string|in:NIC,P/P,Postal',
                'id_no' => 'required|string|max:50|unique:students,id_no,' . $id,
                'dob' => 'required|date|before:today',
                'address' => 'required|string|max:500',
                'school_name' => 'nullable|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'course_id' => 'required|exists:courses,id',
                'branch_id' => 'required|exists:branches,id',
                'referral_source_id' => 'nullable|exists:referral_sources,id',
                'email' => 'required|email|unique:students,email,' . $id . '|max:255',
                'mobile' => 'required|string|max:15',
                'phone_residence' => 'nullable|string|max:15',
                'phone_whatsapp' => 'nullable|string|max:15',
                'qualification' => 'nullable|string|max:100',
                'preferred_contacts' => 'nullable|array',
                'preferred_contacts.*' => 'string|in:email_personal,phone_res,phone_mobile,whatsapp',
            ]);

            // Update the student record
            $student->update([
                'title' => $validatedData['title'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'id_type' => $validatedData['id_type'],
                'id_no' => $validatedData['id_no'],
                'dob' => $validatedData['dob'],
                'address' => $validatedData['address'],
                'school_name' => $validatedData['school_name'],
                'company_name' => $validatedData['company_name'],
                'course_id' => $validatedData['course_id'],
                'branch_id' => $validatedData['branch_id'],
                'referral_source_id' => $validatedData['referral_source_id'],
                'email' => $validatedData['email'],
                'mobile' => $validatedData['mobile'],
                'phone_residence' => $validatedData['phone_residence'],
                'phone_whatsapp' => $validatedData['phone_whatsapp'],
                'qualification' => $validatedData['qualification'],
            ]);

            // Load relationships for response
            $student->load(['course', 'branch', 'referralSource']);

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'data' => $student
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $student = Student::findOrFail($id);
            $student->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
