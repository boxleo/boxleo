<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeductionRequest;
use App\Http\Requests\StoreUserDeductionRequest;
use App\Http\Requests\UpdateUserDeductionRequest;
use App\Models\UserDeduction;
use Illuminate\Http\JsonResponse;

class UserDeductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDeductionRequest $request): JsonResponse
    {
        $userId = $request->input('user_id');
       
        // Process deductions
        foreach ($request->input('deductions', []) as $deduction) {
            UserDeduction::updateOrCreate(
                [
                    'user_id' => $userId,
                    'deduction_id' => $deduction['deduction_id']
                ],
                [
                    'amount' => $deduction['amount'],
                    'type' => $deduction['type']
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Salary information saved successfully.'
        ]);
    }




    /**
     * Display the specified resource.
     */
    public function show(UserDeduction $userDeduction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserDeduction $userDeduction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserDeductionRequest $request, UserDeduction $userDeduction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserDeduction $userDeduction)
    {
        //
    }
}
