<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\HodDepartment;
use App\Models\ManagerDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DepartmentApiController extends Controller
{
    // public function index()
    // {
    //     $departments = Department::with(['users','hods'])->get()->map(function ($department) {
    //         return [
    //             'id' => $department->id,
    //             'name' => $department->name,
    //             // 'hod' => $department->hod ? $department->hod->firstname . ' ' . $department->hod->lastname : 'Null',
    //             // 'manager' => $department->manager ? $department->manager->firstname . ' ' . $department->manager->lastname : 'Null',
    //             'users' => $department->users->map(function ($user) {
    //                 return [
    //                     'id' => $user->id,
    //                     'name' => $user->firstname . ' ' . $user->lastname,
    //                     'email' => $user->email,
    //                 ];
    //             })->toArray(),
    //         ];
    //     });

    //     return response()->json(['departments' => $departments]);
    // }


    public function index()
    {
        $departments = Department::with(['users', 'hods', 'managers'])->get()->map(function ($department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
                'users' => $department->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->firstname . ' ' . $user->lastname,
                        'email' => $user->email,
                    ];
                })->toArray(),
                'managers' => $department->managers->map(function ($manager) {
                    return [
                        'id' => $manager->id,
                        'name' => $manager->firstname . ' ' . $manager->lastname,
                        'email' => $manager->email,
                    ];
                })->toArray(),
                'managers_display'=> $department->managers->map(fn($manager) => $manager->firstname . ' ' . $manager->lastname)->join(', '),

                'hods' => $department->hods->map(function ($hod) {
                    return [
                        'id' => $hod->id,
                        'name' => $hod->firstname . ' ' . $hod->lastname,
                        'email' => $hod->email,
                    ];
                })->toArray(),
                'hods_display' => $department->hods->map(fn($hod) => $hod->firstname . ' ' . $hod->lastname)->join(', '), 
            ];
        });

        return response()->json(['departments' => $departments]);
    }


    // public function store(Request $request)
    // {
    //     Log::info('Store Department Request Received', ['request' => $request->all()]);

    //     $this->validate(
    //         $request,
    //         [
    //             'name' => 'required|max:100',
    //             'hod_id' => 'required|exists:users,id',
    //             'manager_id' => 'required|exists:users,id',
    //         ]
    //     );

    //     try {
    //         $department = Department::create($request->all());
    //         Log::info('Department Created Successfully', ['department' => $department]);

    //         return response()->json(['Success' => 'Department Created Successfully']);
    //     } catch (\Exception $e) {
    //         Log::error('Error Creating Department', ['error' => $e->getMessage()]);
    //         return response()->json(['Error' => 'Failed to Create Department'], 500);
    //     }
    // }



    
    
    public function store(Request $request)
    {
        Log::info('Store Department Request Received', ['request' => $request->all()]);
    
        $this->validate(
            $request,
            [
                'name' => 'required|max:100',
                'hod_id' => 'nullable|exists:users,id', // Changed to nullable to match frontend
                'manager_id' => 'nullable|exists:users,id', // Changed to nullable to match frontend
            ]
        );
    
        try {
            // Create the department first
            $department = Department::create([
                'name' => $request->input('name'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // If HOD ID is provided, create HOD department relationship
            if ($request->filled('hod_id')) {
                HodDepartment::create([
                    'user_id' => $request->input('hod_id'),
                    'department_id' => $department->id,
                ]);
            }
            
            // If Manager ID is provided, create Manager department relationship
            if ($request->filled('manager_id')) {
                ManagerDepartment::create([
                    'user_id' => $request->input('manager_id'),
                    'department_id' => $department->id,
                ]);
            }
    
            Log::info('Department Created Successfully', ['department' => $department]);
            return response()->json(['Success' => 'Department Created Successfully']);
        } catch (\Exception $e) {
            Log::error('Error Creating Department', ['error' => $e->getMessage()]);
            return response()->json(['Error' => 'Failed to Create Department: ' . $e->getMessage()], 500);
        }
    }




    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hod_id' => 'required|exists:users,id',
            'manager_id' => 'required|exists:users,id',
        ]);

        // Update department name
        $department->update([
            'name' => $request->input('name'),
            'updated_at' => now(),
        ]);

        // Check if the HOD department record exists
        $hodDepartment = HodDepartment::where('department_id', $department->id)->first();
        $managerDepartment = ManagerDepartment::where('department_id', $department->id)->first();

        if (!$hodDepartment) {
            // If it doesn't exist, create a new record
            HodDepartment::create([
                'user_id' => $request->input('hod_id'), // HOD User ID
                'department_id' => $department->id,
            ]);
        } else {
            // If it exists, update the HOD
            $hodDepartment->update([
                'user_id' => $request->input('hod_id'),
            ]);
        }

        if (!$managerDepartment) {
            // If it doesn't exist, create a new record
            ManagerDepartment::create([
                'user_id' => $request->input('manager_id'), // Manager User ID
                'department_id' => $department->id,
            ]);
        } else {
            // If it exists, update the Manager
            $managerDepartment->update([
                'user_id' => $request->input('manager_id'),
            ]);
        }

        return response()->json(['message' => 'Department, HOD, and Manager updated successfully'], 200);
    }


    public function destroy(Department $department)
    {
        $department->delete();

        return response()->json(['message' => 'Department deleted successfully']);
    }
}
