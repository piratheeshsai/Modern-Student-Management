<?php

namespace App\Http\Controllers\Reference;

use App\Http\Controllers\Controller;
use App\Models\ReferralSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReferenceController extends Controller
{
    //
    public function index(): JsonResponse
    {
        try {

            // Fetch all references
            $references = ReferralSource::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Referral source retrieved successfully',
                'data' => $references
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Referral source',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:referral_sources,name',
                'type' => 'required|string|max:60',
                'contact_info' => 'required|string|max:100',
            ], [
                'name.required' => 'Referral source name is required',
                'name.unique' => 'Referral source name already exists',
                'name.max' => 'Referral source name cannot exceed 100 characters',
                'type.required' => 'Type is required',
                'type.max' => 'Type cannot exceed 255 characters',
                'contact_info.required' => 'Contact information is required',
                'contact_info.max' => 'Contact information cannot exceed 100 characters',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create the branch
            $references = ReferralSource::create([
                'name' => trim($request->name),
                'type' => trim($request->type),
                'contact_info' => trim($request->contact_info),


            ]);

            return response()->json([
                'success' => true,
                'message' => 'Referral source created successfully',
                'data' => $references
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Referral source',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:referral_sources,name,' . $id,
                'type' => 'required|string|max:60',
                'contact_info' => 'required|string|max:100',
            ], [
                'name.required' => 'Referral source name is required',
                'name.unique' => 'Referral source name already exists',
                'name.max' => 'Referral source name cannot exceed 100 characters',
                'type.required' => 'Type is required',
                'type.max' => 'Type cannot exceed 255 characters',
                'contact_info.required' => 'Contact information is required',
                'contact_info.max' => 'Contact information cannot exceed 100 characters',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the referral source
            $reference = ReferralSource::findOrFail($id);

            // Update the referral source
            $reference->update([
                'name' => trim($request->name),
                'type' => trim($request->type),
                'contact_info' => trim($request->contact_info),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Referral source updated successfully',
                'data' => $reference
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Referral source',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            // Find the referral source
            $reference = ReferralSource::findOrFail($id);

            // Delete the referral source
            $reference->delete();

            return response()->json([
                'success' => true,
                'message' => 'Referral source deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Referral source',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
