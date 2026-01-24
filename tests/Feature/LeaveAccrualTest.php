<?php

namespace Tests\Feature;

use App\Jobs\AddMonthlyLeaveBalance;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveAccrualTest extends TestCase
{
    // use RefreshDatabase; // Commented out to avoid wiping the existing seeded DB, will use manual cleanup

    public function test_leave_accrual_adds_1_75_days()
    {
        // 1. Setup
        $unit = \App\Models\Unit::create(['name' => 'IT Unit', 'phone' => '123', 'address' => 'Test Address']);
        $department = \App\Models\Department::create(['name' => 'IT Dept']);
        $designation = \App\Models\Designation::create(['name' => 'Developer']);
        $office = \App\Models\Office::create(['name' => 'Main Office', 'unit_id' => $unit->id]);

        $user = User::create([
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
            'employment_date' => now(), 
            'unit_id' => $unit->id,
            'office_id' => $office->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
        ]);

        $leaveType = LeaveType::firstOrCreate(['id' => 1], ['name' => 'Annual Leave', 'days' => 0]);
        
        $balance = LeaveBalance::create([
            'user_id' => $user->id,
            'leave_type_id' => 1,
            'balance' => 0,
            'allocated' => 0,
            'taken' => 0,
        ]);

        // 2. Run Job
        $job = new AddMonthlyLeaveBalance();
        $job->handle();

        // 3. Verify
        $balance->refresh();
        $this->assertEquals(1.75, $balance->balance);
        $this->assertEquals(1.75, $balance->allocated);

        // 4. Run Job Again (Next Month)
        $job->handle();
        $balance->refresh();
        $this->assertEquals(3.50, $balance->balance);
        $this->assertEquals(3.50, $balance->allocated);

        // Cleanup
        $balance->forceDelete();
        $user->forceDelete();
        $unit->forceDelete();
        $department->forceDelete();
        $designation->forceDelete();
        $office->forceDelete();
    }

    public function test_negative_balance_logic()
    {
        // 1. Setup
        $unit = \App\Models\Unit::create(['name' => 'IT Unit 2', 'phone' => '1234', 'address' => 'Test Address 2']);
        $department = \App\Models\Department::create(['name' => 'IT Dept 2']);
        $designation = \App\Models\Designation::create(['name' => 'Developer 2']);
        $office = \App\Models\Office::create(['name' => 'Main Office 2', 'unit_id' => $unit->id]);

        $user = User::create([
            'firstname' => 'Test2',
            'lastname' => 'User2',
            'email' => 'test2@example.com',
            'phone' => '0987654321',
            'password' => bcrypt('password'),
            'employment_date' => now(), 
            'unit_id' => $unit->id,
            'office_id' => $office->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
        ]);

        $leaveType = LeaveType::firstOrCreate(['id' => 1], ['name' => 'Annual Leave', 'days' => 0]);
        
        $balance = LeaveBalance::create([
            'user_id' => $user->id,
            'leave_type_id' => 1,
            'balance' => 2.00, // Starts with 2 days
            'allocated' => 2.00,
            'taken' => 0,
        ]);

        // 2. Simulate Taking Leave (Directly decrementing as Controller does)
        // Controller logic: $userLeaveBalance->decrement('balance', 3);
        $balance->decrement('balance', 3.00);
        $balance->refresh();

        $this->assertEquals(-1.00, $balance->balance); // Assert Negative Balance

        // 3. Run Accrual Job (Next Month)
        $job = new AddMonthlyLeaveBalance();
        $job->handle();

        // 4. Verify Recovery
        $balance->refresh();
        $this->assertEquals(0.75, $balance->balance); // -1.00 + 1.75 = 0.75
        
        // Cleanup
        $balance->forceDelete();
        $user->forceDelete();
        $unit->forceDelete();
        $department->forceDelete();
        $designation->forceDelete();
        $office->forceDelete();
    }
}
