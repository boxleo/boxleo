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

    public function generatePayslip(array $calculation): array
    {
        return [
            'employee_name' => $this->user->name,
            'employee_id' => $this->user->employee_id,
            'tax_pin' => $this->user->tax_pin,
            'period' => now()->format('F Y'),
            'calculation_date' => now()->format('Y-m-d H:i:s'),
            'earnings' => [
                'basic_salary' => $calculation['basic_salary'],
                'house_allowance' => $calculation['house_allowance'],
                'transport_allowance' => $calculation['transport_allowance'],
                'bonus' => $calculation['bonus'],
                'gross_pay' => $calculation['gross_pay']
            ],
            'deductions' => [
                'housing_levy' => $calculation['housing_levy'],
                'shif' => $calculation['shif'],
                'paye' => $calculation['paye'],
                'nssf' => $calculation['nssf'],
                'canteen' => $calculation['canteen'],
                'welfare' => $calculation['welfare'],
                'total_deductions' => $calculation['total_deductions']
            ],
            'net_pay' => $calculation['net_pay']
        ];
    }
}
