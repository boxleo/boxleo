<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PerformanceController extends Controller
{
    private function getUserID()
    {
        return auth()->user()->id;
    }

    public function telesalesReport()
    {
        $user_id = $this->getUserID();

        return view('performance.telesales_report', ['user_id' => $user_id]);
    }

    public function awards()
    {
        $user_id = $this->getUserID();

        return view('performance.awards', ['user_id' => $user_id]);
    }

    public function awardTypes()
    {
        $user_id = $this->getUserID();

        return view('performance.award_types', ['user_id' => $user_id]);
    }

    public function appraisals()
    {
        $user_id = $this->getUserID();

        return view('performance.appraisals', ['user_id' => $user_id]);
    }

    public function performanceEvaluations()
    {
        $user = auth()->user();
        $roles = $user->getRoleNames(); // Collection of role names
        $permissions = $user->getAllPermissions()->pluck('name'); // Collection of permission names

        Log::info('performanceEvaluations method called');

        return view('performance.performance_evaluations', compact('user', 'roles', 'permissions'));
    }
}
