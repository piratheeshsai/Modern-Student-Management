<?php

namespace App\Http\Controllers\Courses;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CoursesController extends Controller
{
    //

    public function index(): JsonResponse
    {
        try {

            $courses = Course::orderBy('created_at', 'desc')->get();
            return response()->json([
                'success'=> true,
                'message' => 'Courses retrieved successfully',
                'data' => $courses],
                200);
        }
        catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve courses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:Courses,name',
                'duration' => 'required|string|max:15',
                'fees' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:500',
            ], [
                'name.required' => 'Course name is required',
                'name.unique' => 'Course name already exists',
                'name.max' => 'Course name cannot exceed 255 characters',
                'duration.required' => 'Location is required',
                'duration.max' => 'Location cannot exceed 15 characters',
                'fees.required' => 'Location is required',
                'fees.max' => 'Location cannot exceed 15 characters',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create the Course
            $Course = Course::create([
                'name' => trim($request->name),
                'duration' => trim($request->duration),
                'fees' => trim($request->fees),
                'description' => trim($request->description),


            ]);

            return response()->json([
                'success' => true,
                'message' => 'Course created successfully',
                'data' => $Course
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $Course = Course::findOrFail($id);

            // Validate the request
            $validator = Validator::make($request->all(), [
               'name' => 'required|string|max:255|unique:Courses,name,' . $id,
                'duration' => 'required|string|max:15',
                'fees' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:500',
            ], [
                'name.required' => 'Course name is required',
                'name.unique' => 'Course name already exists',
                'name.max' => 'Course name cannot exceed 255 characters',
                'duration.required' => 'Location is required',
                'duration.max' => 'Location cannot exceed 15 characters',
                'fees.required' => 'Location is required',
                'fees.max' => 'Location cannot exceed 15 characters',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update the Course
           $Course->update([
                'name' => trim($request->name),
                'duration' => trim($request->duration),
                'fees' => trim($request->fees),
                'description' => trim($request->description),
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Course updated successfully',
                'data' => $Course->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $Course = Course::findOrFail($id);

            // Soft delete or hard delete based on your requirement
            $Course->delete();

            return response()->json([
                'success' => true,
                'message' => 'Course deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Course',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
