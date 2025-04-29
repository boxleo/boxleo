<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserDetail; 
use App\Http\Resources\UserDetailResource;

// ← IMPORT these from App\Http\Requests, not from App\Http\Controllers\Api
use App\Http\Requests\StoreUserDetailRequest;
use App\Http\Requests\UpdateUserDetailRequest;             // ← add this


class UserDetailApiController extends Controller
{
    //




   public function index()
   {
       $userDetails = UserDetail::all();
       return UserDetailResource::collection($userDetails);
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(StoreUserDetailRequest $request)
   {
       $userDetail = UserDetail::create($request->validated());
       return new UserDetailResource($userDetail);
   }

   /**
    * Display the specified resource.
    */
   public function show(string $id)
   {



    $userDetail=UserDetail::where('user_id',$id)->first();
// return response()->json($id);
    //    $userDetail = UserDetail::findOrFail($id);

       
       return new UserDetailResource($userDetail);
    // return response()->json(
    //     [
    //         'userDetail' => $userDetail,
    //         'message' => 'User detail retrieved successfully.',
    //         'status' => 200
    //     ]
    // );
   }

// public function show(string $id)
// {
    
//     return 0;}

   /**
    * Update the specified resource in storage.
    */
   public function update(UpdateUserDetailRequest $request, string $id)
   {
    //    $userDetail = UserDetail::firstOrCreate(['id' => $id], $request->validated());
    //    $userDetail->update($request->validated());

    //    return new UserDetailResource($userDetail);

    $data = $request->validated();

    // Ensure user_id is explicitly set
    if (!isset($data['user_id'])) {
        $data['user_id'] = auth()->id(); // Or fallback if needed
    }

    $userDetail = UserDetail::updateOrCreate(
        ['user_id' => $data['user_id']], // Use user_id to look up the record
        $data
    );

    return new UserDetailResource($userDetail);
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(string $id)
   {
       $userDetail = UserDetail::findOrFail($id);
       $userDetail->delete();

       return response()->json([
           'message' => 'User detail deleted successfully.'
       ]);
   }
}