<?php

namespace App\Services\Payroll;

use App\Models\User;

interface PayrollCalculatorInterface
{
    /**
     * Initialize calculator with user data
     * 
     * @param User $user
     */
    public function __construct(User $user);

    /**
     * Calculate payroll details based on the user's data.
     * 
     * Returns array containing:
     * - basic_salary: Base salary amount
     * - house_allowance: Housing allowance if applicable
     * - transport_allowance: Transport allowance if applicable
     * - bonus: Any bonus payments
     * - gross_pay: Total gross pay
     * - deductions: All applicable deductions (PAYE, NSSF etc)
     * - total_deductions: Sum of all deductions
     * - net_pay: Final take-home amount
     *
     * @return array
     */
    public function calculate(): array;
}
