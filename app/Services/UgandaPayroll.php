<?php

namespace App\Services\Payroll\Countries;

use App\Models\User;
use App\Services\Payroll\PayrollCalculatorInterface;

class UgandaPayroll implements PayrollCalculatorInterface
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function calculate(): array
    {
        // Sample base values (ideally should be in DB or config)
        // from user contract or perdonal infomation
        $basic = $this->user->salary ?? 400000; // Example salary in UGX
        $housing = 50000;
        $transport = 30000;
        $bonus = 20000;

        $gross = $basic + $housing + $transport + $bonus;

        // NSSF (5% employee contribution)
        $nssf = -($gross * 0.05);

        // PAYE calculation
        $paye = $this->calculatePAYE($gross);

        // Other deductions
        $canteen = 5000;
        $welfare = 2000;

        $totalDeductions = abs($nssf + $paye + $canteen + $welfare);
        $netPay = $gross - $totalDeductions;

        return [
            'basic_salary' => $basic,
            'housing_allowance' => $housing,
            'transport_allowance' => $transport,
            'bonus' => $bonus,
            'gross_pay' => $gross,
            'nssf' => $nssf,
            'paye' => $paye,
            'canteen' => $canteen,
            'welfare' => $welfare,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
        ];
    }

    private function calculatePAYE($gross)
    {
        // Uganda PAYE tax bands (monthly)
        if ($gross <= 235000) {
            return 0;
        } elseif ($gross <= 335000) {
            return ($gross - 235000) * 0.1;
        } elseif ($gross <= 410000) {
            return 10000 + ($gross - 335000) * 0.2;
        } elseif ($gross <= 10000000) {
            return 25000 + ($gross - 410000) * 0.3;
        } else {
            // Over 10,000,000: extra 10% on the amount above 10M
            return 25000 + (10000000 - 410000) * 0.3 + ($gross - 10000000) * 0.4;
        }
    }
}