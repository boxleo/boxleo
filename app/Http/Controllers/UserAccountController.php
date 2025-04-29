<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserAccountController extends Controller
{
    // public function index()
    // {
    //     // $user = User::with(['unit', 'department', 'designation', 'office', 'permissions', 'roles'])->find(auth()->id());
    //     // return view('account.index', ['user' => $user]);
    
    // }


    public function index()
    {
        $user = User::with([
            'unit', 
            'department', 
            'designation', 
            'office', 
            'permissions', 
            'roles'
        ])->find(auth()->id());

        // Retrieve role names and permissions
        $roles = $user->getRoleNames(); // Returns a collection of role names
        $permissions = $user->getAllPermissions()->pluck('name'); // Returns a collection of permission names

           // Debugging: Dump the data before returning the view
    // dd([
    //     'user' => $user,
    //     'roles' => $roles,
    //     'permissions' => $permissions
    // ]);

        // Pass them to the view
        return view('account.index', [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }
}
