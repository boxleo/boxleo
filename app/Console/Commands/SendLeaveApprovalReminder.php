<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Leave;
use App\Models\User;
use App\Notifications\LeaveApprovalNotification;
use App\Notifications\LeaveReminderNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;



class SendLeaveApprovalReminder extends Command
{
    protected $signature = 'leave:reminder';
    protected $description = 'Send reminders to approvers for pending leave requests';

    public function handle()
    {
        Log::info('Leave Reminder Command Started');

        // Fetch leave requests that were created today and are still pending approval
        $pendingLeaves = Leave::whereYear('created_at', Carbon::now()->year)
        ->where('status', '!=', 'approved')
        ->get();

        Log::info('Pending Leaves:', ['pendingLeaves' => $pendingLeaves]);

        if ($pendingLeaves->isEmpty()) {
            Log::info('No pending leave requests found.');
            return;
        }

        foreach ($pendingLeaves as $leave) {
            $this->processLeaveApproval($leave);
        }

        Log::info('Leave Reminder Command Completed');
    }

    private function processLeaveApproval(Leave $leave)
    {
        $requester = $leave->user;
        if (!$requester) {
            Log::warning("Leave request ID {$leave->id} has no valid requester.");
            return;
        }

        $departmentId = $requester->department_id;
        $unitId = $requester->unit_id;
        $approvers = collect();

        switch ($leave->status) {
            case 'Pending':
                // Notify Manager
                // $approvers = User::where('designation_id', 1)
                //     ->where('department_id', $departmentId)
                //     ->where('unit_id', $unitId)
                //     ->get();

                $approvers = User::whereHas('managerDepartments', function ($query) use ($departmentId) {
                             $query->where('department_id', $departmentId);
                         })
                           ->where('unit_id', $unitId)
                           ->get();
                break;

            case 'Manager Approved';
                // Notify HR
                $approvers = User::where('is_hr', true)->get();
                break;

            case 'Hr Approved':
                // Notify HOD
                // $approvers = User::where('is_hod', true)
                // ->where('department_id', $departmentId)
                // ->whereHas('hodDepartments')
                // ->get();


                $approvers = User::whereHas('hodDepartments', function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })->get();

                break;

            default:
                Log::info("Leave Request ID {$leave->id} is fully approved or does not require action.");
                return;
        }


        // Send notifications
        foreach ($approvers as $approver) {
            $approver->notify(new LeaveReminderNotification($leave));
            Log::info("Leave reminder sent to {$approver->designation_id} {$approver->firstname} (ID: {$approver->id}) for Leave Request ID: {$leave->id}");
        }
    }
}
