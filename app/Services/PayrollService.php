<?php

namespace App\Services\Payroll;

use App\Models\User;
use App\Service\KenyaPayroll;
use App\Service\UgandaPayroll;
use App\Service\PayrollCalculatorInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    protected User $user;
    protected const CACHE_TTL = 3600; // 1 hour cache

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function calculate(): array
    {
        try {
            $this->validateUser();

            // Try to get from cache first
            $cacheKey = "payroll_calculation_{$this->user->id}";
            if ($cachedResult = Cache::get($cacheKey)) {
                Log::info("Retrieved payroll calculation from cache", [
                    'user_id' => $this->user->id
                ]);
                return $cachedResult;
            }

            $calculator = $this->getCalculator();
            $result = $calculator->calculate();

            // Store in cache
            Cache::put($cacheKey, $result, self::CACHE_TTL);

            // Save calculation history
            $this->saveCalculationHistory($result);

            $this->logCalculation($result);

            return $result;
        } catch (Exception $e) {
            Log::error("Payroll calculation failed: " . $e->getMessage(), [
                'user_id' => $this->user->id,
                'country_code' => $this->user->country->code ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    protected function getCalculator(): PayrollCalculatorInterface
    {
        $countryCode = strtoupper($this->user->country->code ?? 'KE');

        return match ($countryCode) {
            'KE' => new KenyaPayroll($this->user),
            'UG' => new UgandaPayroll($this->user),
            // 'TZ' => new TanzaniaPayroll($this->user),
            // 'RW' => new RwandaPayroll($this->user),
            default => throw new Exception("Payroll calculation not supported for country: $countryCode"),
        };
    }

    protected function validateUser(): void
    {
        if (!$this->user->country || !$this->user->country->code) {
            throw new Exception("User country information is missing.");
        }

        if (!$this->user->salary) {
            throw new Exception("User salary information is missing.");
        }

        if (!$this->user->employment_status) {
            throw new Exception("User employment status is missing.");
        }

        if (!$this->user->tax_pin) {
            throw new Exception("User tax PIN is missing.");
        }
    }

    protected function logCalculation(array $result): void
    {
        Log::info("Payroll calculation completed successfully.", [
            'user_id' => $this->user->id,
            'country_code' => $this->user->country->code ?? 'unknown',
            'result' => $result,
            'calculated_at' => now()->toDateTimeString()
        ]);
    }

    protected function saveCalculationHistory(array $result): void
    {
        try {
            DB::table('payroll_calculations')->insert([
                'user_id' => $this->user->id,
                'country_code' => $this->user->country->code,
                'calculation_data' => json_encode($result),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (Exception $e) {
            Log::warning("Failed to save payroll calculation history: " . $e->getMessage(), [
                'user_id' => $this->user->id
            ]);
        }
    }

    public function clearCalculationCache(): bool
    {
        $cacheKey = "payroll_calculation_{$this->user->id}";
        return Cache::forget($cacheKey);
    }

    public function getCalculationHistory(int $limit = 10): array
    {
        return DB::table('payroll_calculations')
            ->where('user_id', $this->user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function validateTaxCompliance(): bool
    {
        // Add tax compliance validation logic here
        // This could check if the user has valid tax documents
        // or if they're compliant with tax regulations
        return true;
    }

    // public function generatePayslip(array $calculation): array
    // {
    //     return [
    //         'employee_name' => $this->user->name,
    //         'employee_id' => $this->user->employee_id,
    //         'tax_pin' => $this->user->tax_pin,
    //         'period' => now()->format('F Y'),
    //         'calculation_date' => now()->format('Y-m-d H:i:s'),
    //         'earnings' => [
    //             'basic_salary' => $calculation['basic_salary'],
    //             'house_allowance' => $calculation['house_allowance'],
    //             'transport_allowance' => $calculation['transport_allowance'],
    //             'bonus' => $calculation['bonus'],
    //             'gross_pay' => $calculation['gross_pay']
    //         ],
    //         'deductions' => [
    //             'housing_levy' => $calculation['housing_levy'],
    //             'shif' => $calculation['shif'],
    //             'paye' => $calculation['paye'],
    //             'nssf' => $calculation['nssf'],
    //             'canteen' => $calculation['canteen'],
    //             'welfare' => $calculation['welfare'],
    //             'total_deductions' => $calculation['total_deductions']
    //         ],
    //         'net_pay' => $calculation['net_pay']
    //     ];
    // }
    // 1. Calculate Gross Pay
    public function calculateGrossPay(Employee $employee): float
    {
        return $employee->basic_salary +
            $employee->allowances +
            $employee->bonuses +
            $this->processOvertime($employee);
    }

    // 2. Calculate Statutory Deductions
    public function calculateStatutoryDeductions(Employee $employee, float $gross): array
    {
        $paye = $this->calculatePAYE($gross);
        $nssf = min(0.06 * $gross, 1080); // As per NSSF Tier 1+2
        $nhif = $this->getNHIFRate($gross);
        $helb = $employee->helb_loan_deduction ?? 0;

        return [
            'PAYE' => $paye,
            'NSSF' => $nssf,
            'NHIF' => $nhif,
            'HELB' => $helb,
        ];
    }

    // 3. Calculate Custom Deductions
    public function calculateCustomDeductions(Employee $employee): float
    {
        return $employee->insurance_deduction +
            $employee->salary_advance +
            $employee->welfare_fund;
    }

    // 4. Compute Net Pay
    public function computeNetPay(float $gross, array $statutory, float $custom): float
    {
        return $gross - array_sum($statutory) - $custom;
    }

    // 5. Process Overtime
    public function processOvertime(Employee $employee): float
    {
        $rate = $employee->overtime_rate ?? 500;
        return $employee->overtime_hours * $rate;
    }

    // 6. Support Multiple Pay Cycles
    public function supportMultiplePayCycles(string $cycle): array
    {
        switch ($cycle) {
            case 'monthly':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'weekly':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            default:
                return [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()];
        }
    }

    // 7. Generate Payslip
    public function generatePayslip(Employee $employee, float $gross, array $deductions, float $net): Payslip
    {
        return Payslip::create([
            'employee_id' => $employee->id,
            'gross_salary' => $gross,
            'deductions' => json_encode($deductions),
            'net_salary' => $net,
            'period' => Carbon::now()->format('F Y'),
        ]);
    }

    // 8. Automate Salary Disbursements
    public function automateSalaryDisbursement(Payslip $payslip): array
    {
        return [
            'bank_account' => $payslip->employee->bank_account,
            'amount' => $payslip->net_salary,
            'reference' => 'Payroll-' . now()->format('Ym'),
        ];
    }

    // 9. Roll Back Payroll
    public function rollbackPayroll(Payroll $payroll): bool
    {
        foreach ($payroll->payslips as $payslip) {
            $payslip->delete();
        }
        return $payroll->delete();
    }

    // 10. Validate Payroll Data
    public function validatePayrollData(Employee $employee, float $net): bool
    {
        return $net >= 0 && $employee->is_active;
    }

    // Helper: Calculate PAYE
    private function calculatePAYE(float $gross): float
    {
        $bands = [
            [0, 14298, 0.10],
            [14298, 23885, 0.15],
            [23885, 33472, 0.20],
            [33472, 42059, 0.25],
            [42059, INF, 0.30],
        ];
        $relief = 2400;
        $tax = 0;

        foreach ($bands as [$min, $max, $rate]) {
            if ($gross > $min) {
                $taxable = min($gross, $max) - $min;
                $tax += $taxable * $rate;
            }
        }

        return max(0, $tax - $relief);
    }

    // Helper: NHIF Rate (simplified)
    private function getNHIFRate(float $gross): float
    {
        if ($gross <= 5999)
            return 150;
        if ($gross <= 7999)
            return 300;
        if ($gross <= 11999)
            return 400;
        if ($gross <= 14999)
            return 500;
        if ($gross <= 19999)
            return 600;
        return 1700; // max for over ~100,000
    }
    public function getEarningsBreakdown(User $user): array
    {
        $earnings = $user->earnings()
            ->selectRaw('label, SUM(amount) as total')
            ->groupBy('label')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get();


        $formatted = [];
        $total = 0;

        foreach ($earnings as $earning) {
            $formatted[$earning->label] = (float) $earning->total;
            $total += $earning->total;
        }

        $formatted['Total Earnings'] = $total;

        return $formatted;
    }



public function getDeductionsBreakdown(User $user): array
{
    $deductions = $user->deductions()
        ->whereNull('deleted_at') // exclude soft-deleted records
        ->selectRaw('name, SUM(CAST(amount AS DECIMAL(10,2))) as total')
        ->groupBy('name')
        ->get();

    $formatted = [];
    $total = 0;

    foreach ($deductions as $deduction) {
        $formatted[$deduction->name] = (float) $deduction->total;
        $total += $deduction->total;
    }

    $formatted['Total Deductions'] = $total;

    return $formatted;
}



}

