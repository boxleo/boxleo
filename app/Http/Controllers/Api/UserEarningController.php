<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreEarningRequest;
use App\Http\Requests\StoreUserEarningRequest;
use App\Http\Requests\UpdateUserEarningRequest;
use App\Models\UserEarning;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Log;

class UserEarningController extends Controller
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

    public function store(StoreUserEarningRequest $request): JsonResponse
    {
        $userId = auth()->user()->id;
        Log::info('User ID: ' . $userId);

        // Process earnings
        foreach ($request->input('earnings', []) as $earning) {
            UserEarning::updateOrCreate(
                [
                    'user_id' => $userId,
                    'earning_id' => $earning['earning_id']
                ],
                [
                    'amount' => $earning['amount'],
                    // 'type' => $earning['type']
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
    public function show(UserEarning $userEarning)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserEarning $userEarning)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserEarningRequest $request, UserEarning $userEarning)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserEarning $userEarning)
    {
        //
    }
}
