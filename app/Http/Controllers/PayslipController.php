<?php

namespace App\Http\Controllers;

class PayslipController extends Controller
{
    private function getUserId(){
        return auth()->user()->id;
    }

    public function index()
    {
        return view('payslips.index',['user_id' => $this->getUserId()]);
    }

    public function employeePayslips()
    {
        return view('employee.payslips.index',['user_id' => $this->getUserId()]);
    }


    

}
