<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
    //



    public function dashboard()
    {
        return view('recruitment.dashboard');
    }

    public function jobs()
    {
        return view('recruitment.jobs.index');
    }

    public function createJob()
    {
        return view('recruitment.jobs.create');
    }

    public function applications()
    {
        return view('recruitment.applications.index');
    }

    public function shortlist()
    {
        return view('recruitment.applicants.shortlist');
    }

    public function applicants()
    {
        return view('recruitment.applicants.index');
    }

    public function onboarding()
    {
        return view('recruitment.onboarding.index');
    }
}
