<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deduction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Http\Requests\StoreDeductionRequest;
use App\Http\Requests\UpdateDeductionRequest;
use Illuminate\Validation\Rule;

class DeductionsController extends Controller
{
    //


    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $deductions = Deduction::orderBy('label')->get();
            
            return response()->json([
                'success' => true,
                'data' => $deductions,
                'message' => 'Deductions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving deductions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    
    public function store(StoreDeductionRequest $request): JsonResponse
{
    try {
        $deduction = Deduction::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $deduction,
            'message' => 'Deduction created successfully'
        ], 201);

    } catch (\Exception $e) {
        \Log::error('Deduction creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error creating deduction'
        ], 500);
    }
}


    /**
     * Display the specified resource.
     */
    public function show(Deduction $deduction): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $deduction,
                'message' => 'Deduction retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving deduction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
public function update(UpdateDeductionRequest $request, Deduction $deduction): JsonResponse
{
    try {
        $validated = $request->validated();
        $deduction->update($validated);

        return response()->json([
            'success' => true,
            'data' => $deduction,
            'message' => 'Deduction updated successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error updating deduction: ' . $e->getMessage()
        ], 500);
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deduction $deduction): JsonResponse
    {
        try {
            // Check if deduction is mandatory
            if ($deduction->mandatory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete mandatory deduction'
                ], 422);
            }

            // Check if deduction is being used in any payroll records
            // You might want to add this check based on your payroll structure
            // if ($deduction->payrollRecords()->exists()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Cannot delete deduction that is being used in payroll records'
            //     ], 422);
            // }

            $deduction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Deduction deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting deduction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active deductions only
     */
    public function active(): JsonResponse
    {
        try {
            $deductions = Deduction::where('active', true)
                ->orderBy('label')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $deductions,
                'message' => 'Active deductions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving active deductions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mandatory deductions only
     */
    public function mandatory(): JsonResponse
    {
        try {
            $deductions = Deduction::where('mandatory', true)
                ->where('active', true)
                ->orderBy('label')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $deductions,
                'message' => 'Mandatory deductions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving mandatory deductions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get optional deductions only
     */
    public function optional(): JsonResponse
    {
        try {
            $deductions = Deduction::where('mandatory', false)
                ->where('active', true)
                ->orderBy('label')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $deductions,
                'message' => 'Optional deductions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving optional deductions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax deductible deductions only
     */
    public function taxDeductible(): JsonResponse
    {
        try {
            $deductions = Deduction::where('tax_deductible', true)
                ->where('active', true)
                ->orderBy('label')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $deductions,
                'message' => 'Tax deductible deductions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving tax deductible deductions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle deduction active status
     */
    public function toggleStatus(Deduction $deduction): JsonResponse
    {
        try {
            $deduction->update(['active' => !$deduction->active]);

            return response()->json([
                'success' => true,
                'data' => $deduction->fresh(),
                'message' => 'Deduction status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating deduction status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deductions by type (fixed or percentage)
     */
    public function getByType(string $type): JsonResponse
    {
        try {
            if (!in_array($type, ['fixed', 'percentage'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid type. Must be either "fixed" or "percentage"'
                ], 400);
            }

            $deductions = Deduction::where('type', $type)
                ->where('active', true)
                ->orderBy('label')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $deductions,
                'message' => ucfirst($type) . ' deductions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving deductions by type: ' . $e->getMessage()
            ], 500);
        }
    }

    
}
