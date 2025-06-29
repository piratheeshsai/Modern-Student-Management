<?php

namespace App\Http\Controllers\Branches;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    //

    public function index(): JsonResponse
    {
        try {
            $branches = Branch::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Branches retrieved successfully',
                'data' => $branches
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve branches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:branches,name',
                'location' => 'required|string|max:255',
            ], [
                'name.required' => 'Branch name is required',
                'name.unique' => 'Branch name already exists',
                'name.max' => 'Branch name cannot exceed 255 characters',
                'location.required' => 'Location is required',
                'location.max' => 'Location cannot exceed 255 characters',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create the branch
            $branch = Branch::create([
                'name' => trim($request->name),
                'location' => trim($request->location),


            ]);

            return response()->json([
                'success' => true,
                'message' => 'Branch created successfully',
                'data' => $branch
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create branch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $branch = Branch::findOrFail($id);

            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:branches,name,' . $id,
                'location' => 'required|string|max:255',
            ], [
                'name.required' => 'Branch name is required',
                'name.unique' => 'Branch name already exists',
                'name.max' => 'Branch name cannot exceed 255 characters',
                'location.required' => 'Location is required',
                'location.max' => 'Location cannot exceed 255 characters',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update the branch
            $branch->update([
                'name' => trim($request->name),
                'location' => trim($request->location),

            ]);

            return response()->json([
                'success' => true,
                'message' => 'Branch updated successfully',
                'data' => $branch->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update branch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $branch = Branch::findOrFail($id);

            // Soft delete or hard delete based on your requirement
            $branch->delete();

            return response()->json([
                'success' => true,
                'message' => 'Branch deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete branch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
