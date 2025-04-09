<?php

namespace App\Http\Controllers;

class ComplaintController extends Controller
{
    // private function getUserId()
    // {
    //     return auth()->user()->id;
    // }
    // public function index()
    // {
    //     return view('complaints.index');
    // }

    // public function employeeComplaints()
    // {


    //     $user = auth()->user();
    //     $roles = $user->getRoleNames(); // Collection of role names
    //     $permissions = $user->getAllPermissions()->pluck('name'); // Collection of permission names
    
    //     // return view('employee.complaints.index', ['user_id' => $this->getUserId()]);



    //     return view('employee.complaints.index', compact('user', 'roles', 'permissions'));

    // }


    public function employeeComplaints()
    {


        $user = auth()->user();
        // $user = optional(auth()->user());

        $roles = $user->getRoleNames(); // Collection of role names
        $permissions = $user->getAllPermissions()->pluck('name'); // Collection of permission names

        // $roles = $user ? $user->getRoleNames() : collect(); 
        // $permissions = $user ? collect($user->getAllPermissions())->pluck('name') : collect(); 
    
    
        return view('employee.complaints.index', compact('user', 'roles', 'permissions'));

    }


}
