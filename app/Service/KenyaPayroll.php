<?php

namespace App\Services\Payroll\Countries;

use App\Models\User;
use App\Services\Payroll\PayrollCalculatorInterface;
use App\Models\StatutoryDeduction;
use App\Models\OtherDeduction;
use App\Models\Earning;
use App\Models\Payslip;

class KenyaPayroll implements PayrollCalculatorInterface
{
    protected User $user;
    protected const TAX_RELIEF = 2400;
    protected const NSSF_CAP = 2160;
    protected const NSSF_RATE = 0.06;
    protected const HOUSING_LEVY_RATE = 0.015;
    protected const SHIF_RATE = 0.0275;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    //     // class UserDetail extends Model
    // {


    //     protected $fillable = [
    //         "user_id", "kin", "kin_contact", "gender", "payment_mode", "bank_name", "bank_branch", "bank_account", "mpesa_no", "nhif_no","national_id", "nssf_no", "kra_pin","marital_status","spouse","spouse_no","staffID","nationality","country","region", "date_of_birth", "place_of_birth", "residential_address", "postal_address", "postal_code", "city", "county", "country_of_origin", "emergency_contact_name", "emergency_contact_relationship", "emergency_contact_phone", "emergency_contact_email"
    //     ];

    // table StatutoryDeduction

    // statutory deductions
    //  protected $fillable = [
    //     'payslip_id',
    //     'income_tax',
    //     'tax_relief',
    //     'paye',
    //     'nssf',
    //     'nhif',
    //     'housing_levy',
    // ];

    // other deductions 
    //  OtherDeduction extends Model

    // protected $fillable = [
    //     'payslip_id',
    //     'title',
    //     'amount',
    // ];
    // ];


    // EARNINGS/BENEFITS
    //   Schema::create('earnings', function (Blueprint $table) {


    // $table->id();
    // $table->foreignId('payslip_id')->constrained()->onDelete('cascade');
    // $table->string('label');
    // $table->decimal('amount', 10, 2);
    // $table->timestamps();

    // Basic Pay 32,000.00
    // House Allowance 5,000.00
    // Transport Allowance 3,000.00
    // Bonus 2,000.00
    // Housing Levy -600.00
    // S.H.I.F. -1,155.00
    // Gross Pay 40,245.0


    public function calculate(): array
    {
        // Get base values from user or defaults
        $basic = $this->user->salary ?? 32000;
        $house = $this->user->house_allowance ?? 5000;
        $transport = $this->user->transport_allowance ?? 3000;
        $bonus = $this->user->bonus ?? 2000;

        $gross = $basic + $house + $transport + $bonus;

        // Calculate statutory deductions
        $statutoryDeductions = $this->calculateStatutoryDeductions($gross);

        // Calculate other deductions
        $otherDeductions = $this->calculateOtherDeductions();

        // Calculate total deductions and net pay
        $totalDeductions = abs(
            $statutoryDeductions['housing_levy'] +
                $statutoryDeductions['shif'] +
                $statutoryDeductions['paye'] +
                $statutoryDeductions['nssf'] +
                $otherDeductions['canteen'] +
                $otherDeductions['welfare']
        );

        $netPay = $gross - $totalDeductions;

        // Prepare the result array
        return [
            'basic_salary' => $basic,
            'house_allowance' => $house,
            'transport_allowance' => $transport,
            'bonus' => $bonus,
            'gross_pay' => $gross,
            'housing_levy' => $statutoryDeductions['housing_levy'],
            'shif' => $statutoryDeductions['shif'],
            'income_tax' => $statutoryDeductions['income_tax'],
            'tax_relief' => $statutoryDeductions['tax_relief'],
            'paye' => $statutoryDeductions['paye'],
            'nssf' => $statutoryDeductions['nssf'],
            'canteen' => $otherDeductions['canteen'],
            'welfare' => $otherDeductions['welfare'],
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
        ];
    }

    protected function calculateStatutoryDeductions(float $gross): array
    {
        $housingLevy = - ($gross * self::HOUSING_LEVY_RATE);
        $nssf = -min($gross * self::NSSF_RATE, self::NSSF_CAP);
        $shif = - ($gross * self::SHIF_RATE);

        $incomeTax = $this->calculatePAYE($gross);
        $taxRelief = -self::TAX_RELIEF;
        $paye = $incomeTax + $taxRelief;

        return [
            'housing_levy' => $housingLevy,
            'nssf' => $nssf,
            'shif' => $shif,
            'income_tax' => $incomeTax,
            'tax_relief' => $taxRelief,
            'paye' => $paye
        ];
    }
    protected function calculateOtherDeductions(): array
    {
        $otherDeductions = OtherDeduction::where('user_id', $this->user->id)->get();

        $deductions = [
            'canteen' => 0,
            'welfare' => 0,
        ];

        foreach ($otherDeductions as $deduction) {
            if (strtolower($deduction->title) === 'canteen') {
                $deductions['canteen'] += $deduction->amount;
            } elseif (strtolower($deduction->title) === 'welfare') {
                $deductions['welfare'] += $deduction->amount;
            }
        }

        return $deductions;
    }

    protected function calculatePAYE(float $gross): float
    {
        $personalRelief = 2400;
        $tax = 0;
    
        if ($gross <= 24000) {
            $tax = $gross * 0.10;
        } elseif ($gross <= 32333) {
            $tax = (24000 * 0.10) + (($gross - 24000) * 0.25);
        } elseif ($gross <= 500000) {
            $tax = (24000 * 0.10) +
                   ((32333 - 24000) * 0.25) +
                   (($gross - 32333) * 0.30);
        } elseif ($gross <= 800000) {
            $tax = (24000 * 0.10) +
                   ((32333 - 24000) * 0.25) +
                   ((500000 - 32333) * 0.30) +
                   (($gross - 500000) * 0.325);
        } else {
            $tax = (24000 * 0.10) +
                   ((32333 - 24000) * 0.25) +
                   ((500000 - 32333) * 0.30) +
                   ((800000 - 500000) * 0.325) +
                   (($gross - 800000) * 0.35);
        }
    
        // Apply personal relief
        $tax -= $personalRelief;
    
        // Ensure tax is not negative
        return max(0, $tax);
    }
    


    protected function savePayslip(array $calculation): void
    {
        Payslip::create([
            'user_id' => $this->user->id,
            'basic_salary' => $calculation['basic_salary'],
            'house_allowance' => $calculation['house_allowance'],
            'transport_allowance' => $calculation['transport_allowance'],
            'bonus' => $calculation['bonus'],
            'gross_pay' => $calculation['gross_pay'],
            'housing_levy' => $calculation['housing_levy'],
            'shif' => $calculation['shif'],
            'nssf' => $calculation['nssf'],
            'paye' => $calculation['paye'],
            'tax_relief' => $calculation['tax_relief'],
            'canteen' => $calculation['canteen'],
            'welfare' => $calculation['welfare'],
            'total_deductions' => $calculation['total_deductions'],
            'net_pay' => $calculation['net_pay'],
            'payment_period' => now()->format('F Y'),
            'payment_status' => 'pending'
        ]);
    }
}
