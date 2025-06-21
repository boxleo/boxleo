<?php

namespace App\Http\Controllers;

use App\Models\User;

class PayrollController extends Controller
{

    public function index()
    {
        return view('payroll.index');
    }


    public function settings(){
        return view('payroll.settings');}


     // fetch user payslip with user info

    public function payslipWithUser($id)
    {
        return User::with('userdetails','earnings','deductions')->findOrFail($id);

    }   

}
