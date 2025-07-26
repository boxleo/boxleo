<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayrollApiController extends Controller
{



    // // fetch user payslip with user info

    // public function payslipWithUser($id)
    // {
    //     return Payslip::with('user')->findOrFail($id);
    // }   


    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Payslip::with('user')->get(); // return payslips with user info

    }

    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        return Payslip::with('user')->findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //


        Payslip::destroy($id);
        return response()->json(['message' => 'Payslip deleted']);
    }


 public function generatePayslip(Request $request)
    {


        Log::info('Generating payslip for user: ' . $request->all());
        // Logic to generate payslip
        // This is a placeholder, implement your logic here
        $userId = $request->input('user_id');
        $month = $request->input('month');
        $year = $request->input('year');

        // Example logic to create a payslip
        $payslip = Payslip::create([
            'user_id' => $userId,
            'month' => $month,
            'year' => $year,
            'amount' => 0, // Calculate the amount based on earnings and deductions
        ]);

        return response()->json($payslip);
    }
}
