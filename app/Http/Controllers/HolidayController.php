<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class HolidayController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('holidays.index', compact('user'));
    }
}
