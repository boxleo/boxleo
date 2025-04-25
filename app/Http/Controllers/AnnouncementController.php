<?php

namespace App\Http\Controllers;


class AnnouncementController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $roles = $user->getRoleNames(); // Collection of role names
        $permissions = $user->getAllPermissions()->pluck('name'); // Collection of permission names
    
        return view('announcements.index',compact('user', 'roles', 'permissions'));
    }
}
