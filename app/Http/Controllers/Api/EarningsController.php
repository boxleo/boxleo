<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Earning;
use Illuminate\Http\Request;
use App\Http\Requests\StoreEarningRequest;
use App\Http\Requests\UpdateEarningRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;



class EarningsController extends Controller
{
    //



    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $earnings = Earning::orderBy('label')->get();
            
            return response()->json([
                'success' => true,
                'data' => $earnings,
                'message' => 'Earnings retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving earnings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEarningRequest $request): JsonResponse
    {
        try {
            \Log::info('Creating earning with data:', $request->validated());

            $earning = Earning::create($request->validated());

            \Log::info('Earning created successfully:', ['id' => $earning->id]);

            return response()->json([
                'success' => true,
                'data' => $earning,
                'message' => 'Earning created successfully'
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating earning:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating earning: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Earning $earning): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $earning,
                'message' => 'Earning retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving earning: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(UpdateEarningRequest $request, Earning $earning): JsonResponse
{
    try {
        $earning->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $earning->fresh(),
            'message' => 'Earning updated successfully',
        ]);
    } catch (\Exception $e) {
        \Log::error('Earning update failed', [
            'id' => $earning->id,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error updating earning',
        ], 500);
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Earning $earning): JsonResponse
    {
        try {
            // Check if earning is being used in any payroll records
            // You might want to add this check based on your payroll structure
            // if ($earning->payrollRecords()->exists()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Cannot delete earning that is being used in payroll records'
            //     ], 422);
            // }

            $earning->delete();

            return response()->json([
                'success' => true,
                'message' => 'Earning deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting earning: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active earnings only
     */
    public function active(): JsonResponse
    {
        try {
            $earnings = Earning::where('active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $earnings,
                'message' => 'Active earnings retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving active earnings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle earning active status
     */
    public function toggleStatus(Earning $earning): JsonResponse
    {
        try {
            $earning->update(['active' => !$earning->active]);

            return response()->json([
                'success' => true,
                'data' => $earning->fresh(),
                'message' => 'Earning status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating earning status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get earnings by type (fixed or percentage)
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

            $earnings = Earning::where('type', $type)
                ->where('active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $earnings,
                'message' => ucfirst($type) . ' earnings retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving earnings by type: ' . $e->getMessage()
            ], 500);
        }
    }


}
