<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Util\SMSUtil;
use App\Http\Controllers\Util\TZSMSUtil;
use App\Http\Controllers\Util\UGSMSUtil;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\LeaveApprovalNotification;
use App\Notifications\LeaveCreatedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Notifications\TaskAssignedNotification;


class LeaveApiController extends Controller
{

  public function index(Request $request)
  {
    Log::info('Fetching leave records', ['user_id' => auth()->id(), 'request_params' => $request->all()]);

    $query = Leave::with('leave_type', 'user.department', 'user.unit', 'tasks', 'tasks.assignee')
      ->whereHas('user', function ($query) {
        $query->whereNull('deleted_at');
      });

    $user = auth()->user();
    $unitId = $user->unit_id;
    $isHod = $user->is_hod;
    $isHr = $user->is_hr; // Assuming HR role check

    Log::info('User Role Check', ['isHod' => $isHod, 'isHr' => $isHr, 'unitId' => $unitId]);

    // If the user is not HR or HOD, restrict leaves to their unit
    if (!$isHod && !$isHr) {
      Log::info('User is NOT HOD/HR. Filtering leaves by unit', ['unit_id' => $unitId]);
      $query->whereHas('user', function ($q) use ($unitId) {
        $q->where('unit_id', $unitId);
      });
    }

    if ($isHod) {
      $hodDepartmentIds = $user->departments?->pluck('id')->toArray() ?? [];
      Log::info('HOD Access: Filtering leaves by department', ['hodDepartmentIds' => $hodDepartmentIds]);

      $query->whereHas('user', function ($query) use ($hodDepartmentIds) {
        $query->whereIn('department_id', $hodDepartmentIds);
      })->where('status', 'Hr Approved');
    }

    // Apply filters based on request parameters
    if ($request->has('user_ids') && is_array($request->input('user_ids'))) {
      $userIds = collect($request->input('user_ids'))->flatten()->toArray();
      Log::info('Filtering by user_ids', ['user_ids' => $userIds]);
      $query->whereIn('user_id', $userIds);
    }

    if ($request->has('unit_ids') && is_array($request->input('unit_ids'))) {
      Log::info('Filtering by unit_ids', ['unit_ids' => $request->input('unit_ids')]);
      $query->whereHas('user', function ($query) use ($request) {
        $query->whereIn('unit_id', $request->input('unit_ids'));
      });
    }

    if ($request->has('leave_type_ids') && is_array($request->input('leave_type_ids'))) {
      Log::info('Filtering by leave_type_ids', ['leave_type_ids' => $request->input('leave_type_ids')]);
      $query->whereIn('leave_type_id', $request->input('leave_type_ids'));
    }

    if ($request->has('application_date')) {
      $applicationDate = $request->input('application_date');
      Log::info('Filtering by application_date', ['application_date' => $applicationDate]);
      $query->whereDate('created_at', $applicationDate);
    }

    if ($request->has('status')) {
      $status = $request->input('status');
      Log::info('Filtering by status', ['status' => $status]);
      $query->where('status', $status);
    }

    if ($request->has('from')) {
      $startDate = $request->input('from');
      Log::info('Filtering by from date', ['from' => $startDate]);
      $query->whereDate('from', $startDate);
    }

    if ($request->has('statuses') && is_array($request->input('statuses'))) {
      Log::info('Filtering by multiple statuses', ['statuses' => $request->input('statuses')]);
      $query->whereIn('status', $request->input('statuses'));
    }

    // Log the final query (useful for debugging)
    Log::info('Final Leave Query', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

    $leaves = $query->orderBy('created_at', 'desc')->get();

    // Append full name for each user
    $leaves = $leaves->map(function ($leave) {
      $leave->user->name = $leave->user->firstname . ' ' . $leave->user->lastname;
      return $leave;
    });

    Log::info('Leaves data fetched', ['leaves' => $leaves]);
    Log::info('Leaves fetched successfully', ['leave_count' => count($leaves)]);
    Log::info('Leaves as array', ['leaves_array' => $leaves->toArray()]);

    return response()->json(['leaves' => $leaves]);
  }


  public function userLeaves(Request $request)
  {

    $leaves = Leave::with('leave_type', 'tasks', 'tasks.assignee', 'user')
      ->orderBy('created_at', 'desc')
      ->where('user_id', $request->user_id)
      ->get();

    return response()->json(['leaves' => $leaves]);
  }

  public function leaveBalances(Request $request)
  {
    $query = LeaveBalance::with('user', 'leaveType');

    if ($request->has('user_ids')) {
      $userIds = explode(',', $request->get('user_ids'));
      $query->whereIn('user_id', $userIds);
    }

    if ($request->has('leave_type_ids')) {
      $leaveTypeIds = explode(',', $request->get('leave_type_ids'));
      $query->whereIn('leave_type_id', $leaveTypeIds);
    }

    if ($request->has('country_ids')) {
      $countryIds = explode(',', $request->get('country_ids'));
      $query->whereIn('user_id', function ($subQuery) use ($countryIds) {
        $subQuery->select('id')
          ->from('users')
          ->whereIn('unit_id', $countryIds);
      });
    }

    $leaveBalances = $query->get();

    return response()->json(['leaveBalances' => $leaveBalances]);
  }

  public function createLeaveBalances(Request $request)
  {

    $validatedData = $request->validate([
      'user_id' => 'required|exists:users,id',
      'leave_type_id' => 'required|exists:leave_types,id',
      'balance' => 'required',
    ]);

    $leaveBalance = LeaveBalance::create($validatedData);

    return response()->json(['message' => 'Leave balance created successfully', 'data' => $leaveBalance]);
  }

  public function deteteLeaveBalance(Request $request)
  {
    $leaveBalanceId = $request->id;

    $leaveBalance = LeaveBalance::find($leaveBalanceId);

    if (!$leaveBalance) {
      return response()->json(['message' => 'Leave balance not found.'], 404);
    }

    try {
      $leaveBalance->delete();
      return response()->json(['message' => 'Leave balance deleted successfully.'], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Failed to delete leave balance.'], 500);
    }
  }

  public function updateLeaveBalance(Request $request, $id)
  {
    $validatedData = $request->validate([
      'user_id' => 'required|exists:users,id',
      'leave_type_id' => 'required|exists:leave_types,id',
      'allocated' => 'required|numeric|min:0',
      'taken' => 'required|numeric|min:0',
      'balance' => 'required',
    ]);

    $leaveBalance = LeaveBalance::findOrFail($id);

    $leaveBalance->update($validatedData);

    return response()->json(['message' => 'Leave balance updated successfully', 'data' => $leaveBalance]);
  }

  public function teamLeaves(Request $request)
  {
    $userId = $request->input('userId');
    $user = User::find($userId);

    if (!$user) {
      return response()->json(['error' => 'User not found'], 404);
    }

    // $departmentId = $user->department_id;
    $departmentId = $user->managerDepartments->pluck('id');
    $unitId = $user->unit_id;

    $leaves = Leave::with(['user', 'leave_type'])
      ->whereHas('user', function ($query) use ($departmentId, $unitId) {
        $query->whereIn('department_id', $departmentId)
          ->where('unit_id', $unitId);
      })
      ->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END") // Prioritize 'pending' status

      ->orderBy('created_at', 'desc')
      ->get();

    return response()->json(['leaves' => $leaves]);
  }




  public function store(Request $request)
  {
    Log::info('Leave application request received', ['request_data' => $request->all()]);

    $this->validate($request, [
      'user_id' => 'required',
      'leave_type_id' => 'required',
      'from' => 'required|date',
      'to' => 'required|date|after_or_equal:from',
      'phone' => 'required|string|min:10',
      'days' => 'required|integer',
      'hod' => 'required|exists:users,id',
      'manager' => 'required|exists:users,id',
      'document' => 'nullable|file|mimes:pdf,doc,docx,jpeg,jpg,png',
    ]);


    // $test=$request->all();
    // dd($test);
    Log::info('Validation passed');

    $startDate = new DateTime($request->from);
    $endDate = new DateTime($request->to);
    $holidays = ['12-25', '12-26', '01-01']; // Christmas, Boxing Day, New Year
    $leaveDays = 0;

    while ($startDate <= $endDate) {
      $isSunday = $startDate->format('N') == 7;
      if (!$isSunday && !in_array($startDate->format('m-d'), $holidays)) {
        $leaveDays++;
      }
      $startDate->modify('+1 day');
    }

    Log::info('Calculated leave days', ['leave_days' => $leaveDays]);

    if ($leaveDays != $request->days) {
      Log::warning('Leave days mismatch', ['expected' => $request->days, 'calculated' => $leaveDays]);
      return response()->json(['error' => 'The number of leave days does not match the provided days.'], 400);
    }

    $managerUser = User::find($request->manager);

    if (!$managerUser) {
      Log::error('Manager not found', ['manager_id' => $request->manager]);
      return response()->json(['error' => 'Manager not found.'], 404);
    }

    $documentName = null;
    if ($request->hasFile('document')) {
      $documentName = time() . '.' . $request->file('document')->extension();
      $request->file('document')->storeAs('leave-documents', $documentName, 'local');
      Log::info('Document uploaded', ['document' => $documentName]);
    }

    $leave = Leave::create([
      'user_id' => $request->user_id,
      'leave_type_id' => $request->leave_type_id,
      'from' => $request->from,
      'to' => $request->to,
      'days' => $leaveDays,
      'phone' => $request->phone,
      'comment' => $request->comment,
      'status' => 'Pending',
      'document' => $documentName,
    ]);

    Log::info('Leave application created', ['leave_id' => $leave->id]);

    // 💡 Save delegated tasks
    if ($request->filled('delegatedTasks')) {
      try {
        $tasks = is_string($request->delegatedTasks)
          ? json_decode($request->delegatedTasks, true)
          : $request->delegatedTasks;

        if (is_array($tasks)) {
          foreach ($tasks as $task) {
            if (!empty($task['assignee_id']) && !empty($task['task_description'])) {
              $leave->tasks()->create([
                'assignee_id' => $task['assignee_id'],
                'task_description' => $task['task_description'],
              ]);
            }
          }
          Log::info('Delegated tasks saved', ['task_count' => count($tasks)]);
        } else {
          Log::warning('Delegated tasks is not an array', ['value' => $request->delegatedTasks]);
        }
      } catch (\Exception $e) {
        Log::error('Failed to save delegated tasks', ['error' => $e->getMessage()]);
      }
    }


    $leaveType = LeaveType::find($request->leave_type_id);
    if ($leaveType) {
      $userLeaveBalance = LeaveBalance::firstOrCreate([
        'user_id' => $request->user_id,
        'leave_type_id' => $request->leave_type_id,
      ], [
        'balance' => $leaveType->days,
        'taken' => 0,
      ]);

      $userLeaveBalance->decrement('balance', $leaveDays);
      $userLeaveBalance->increment('taken', $leaveDays);

      Log::info('Leave balance updated', [
        'user_id' => $request->user_id,
        'leave_type_id' => $request->leave_type_id,
        'new_balance' => $userLeaveBalance->balance,
        'taken' => $userLeaveBalance->taken,
      ]);
    } else {
      Log::error('Leave type not found', ['leave_type_id' => $request->leave_type_id]);
      return response()->json(['error' => 'Leave type not found.'], 404);
    }

    $this->logLeaveAction($leave, 'created', $request->user_id);

    // Notify the manager first
    $managerUser->notify(new LeaveCreatedNotification($leave));
    Log::info('Notification sent to manager', ['email' => $managerUser->email]);

    return response()->json(['message' => 'Leave application submitted successfully!']);
  }



  // public function approveLeave(Request $request, Leave $leave)
  // {
  //   try {
  //     Log::info('Approve Leave Request', [
  //       'userId' => $request->input('userId'),
  //       'leaveId' => $leave->id,
  //       'requestData' => $request->all()
  //     ]);



  //     // new code
  //     $user = User::findOrFail($request->userId);

  //     // return $user;
  //     $designation = $user->designation ? strtolower($user->designation->name) : null;
  //     $country = $user->office ? strtolower($user->office->country ?? 'kenya') : 'kenya';

  //     // $customApproval = false;

  //     if (in_array($designation, ['intern', 'assistant']) && $country !== 'kenya') {
  //       // $customApproval = true;
  //       $leave->status = 'Manager Approval Only';
  //       Log::info('Custom approval route for intern/assistant outside Kenya');
  //     }


  //     $isInternOrAssistantOutsideKenya = in_array(strtolower($leave->user->designation->name ?? ''), ['intern', 'assistant']) &&
  //       strtolower($leave->user->office->country ?? 'kenya') !== 'kenya';

  //     $userId = $request->input('userId');
  //     $approver = User::find($userId);





  //     // approver heads mutiple departments and can only approve leave for their departments


  //     // // Many-to-Many relationship for HODs
  //     //  public function hodDepartments()
  //     //  {
  //     //      return $this->belongsToMany(Department::class, 'hod_departments', 'user_id', 'department_id');
  //     //  }


  //     if (!$approver) {
  //       return response()->json(['error' => 'Approver not found'], 404);
  //     }

  //     switch ($leave->status) {
  //       case 'Pending':
  //         if ($approver->designation_id === 1 || ($approver->is_hr === 1 && $approver->department_id === 1)) {
  //           $leave->status = 'Manager Approved';
  //           $this->logLeaveAction($leave, 'Manager Approved', $userId);
  //           $this->notifyNextApprover($leave, 'HR');
  //         } else {
  //           return response()->json(['error' => 'Unauthorized'], 403);
  //         }
  //         break;

  //       case 'Manager Approved':
  //         if ($approver->is_hr === 1) {
  //           $leave->status = 'Hr Approved';
  //           $this->logLeaveAction($leave, 'Hr Approved', $userId);
  //           $this->notifyNextApprover($leave, 'HOD');
  //         } else {
  //           return response()->json(['error' => 'Unauthorized'], 403);
  //         }
  //         break;

  //       case 'Hr Approved':
  //         if ($approver->is_hod === 1) {
  //           $leave->status = 'Approved';
  //           $this->logLeaveAction($leave, 'Approved', $userId);
  //           $this->notifyEmployee($leave);
  //           $this->notifyTaskAssignees($leave);
  //         } else {
  //           return response()->json(['error' => 'Unauthorized'], 403);
  //         }
  //         break;

  //       default:
  //         return response()->json(['error' => 'Invalid leave status'], 400);
  //     }

  //     $leave->save();
  //     return response()->json(['message' => 'Leave approved successfully'], 200);
  //   } catch (\Exception $e) {
  //     Log::error('Error approving leave', ['exception' => $e]);
  //     return response()->json(['error' => 'Failed to approve leave'], 500);
  //   }
  // }




  // private function notifyNextApprover(Leave $leave, string $role)
  // {
  //   $nextApprovers = match ($role) {
  //     'HR' => User::where('is_hr', 1)->get(),  // Get all HRs
  //     'HOD' => User::whereHas('hodDepartments', function ($query) use ($leave) {
  //       $query->where('department_id', $leave->user->department_id);
  //     })->get(),  // Get HODs ONLY for the leave applicant’s department
  //     default => collect(),
  //   };

  //   foreach ($nextApprovers as $approver) {
  //     $approver->notify(new LeaveCreatedNotification($leave));
  //     Log::info("Notified HOD (User ID: {$approver->id}) for Department ID: {$leave->user->department_id} regarding Leave ID: {$leave->id}");
  //   }
  // }


  // /**
  //  * Notify the employee upon final approval
  //  */
  // private function notifyEmployee(Leave $leave)
  // {
  //   $message = "Hello {$leave->user->firstname}, Your leave request from {$leave->from} to {$leave->to} has been approved.";
  //   $smsUtil = match ($leave->user->unit_id) {
  //     2 => new UGSMSUtil(),
  //     3 => new TZSMSUtil(),
  //     default => new SMSUtil(),
  //   };

  //   try {
  //     $smsUtil->sendSMS($leave->phone, $message);
  //     $leave->user->notify(new LeaveApprovalNotification($leave));

  //     Log::info("Sent leave approval email to employee {$leave->user->firstname} (Email: {$leave->user->email})");
  //     Log::info("Sent leave approval SMS to employee {$leave->user->firstname} (Phone: {$leave->phone})");
  //   } catch (\Exception $e) {
  //     Log::error("Failed to send SMS, sending email instead", ['exception' => $e]);

  //     // Send email as a fallback
  //     // $leave->user->notify(new LeaveCreatedNotification($leave));

  //     $leave->user->notify(new LeaveApprovalNotification($leave));

  //     Log::info("Sent leave approval email to employee {$leave->user->firstname} (Email: {$leave->user->email})");
  //   }
  // }

  // /**
  //  * Notify task assignees after leave approval
  //  */
  // private function notifyTaskAssignees(Leave $leave)
  // {
  //   // Get the delegated tasks
  //   $tasks = $leave->tasks ?? [];

  //   foreach ($tasks as $task) {
  //     // Skip tasks with no assignee
  //     if (empty($task['assignee_id'])) {
  //       continue;
  //     }

  //     // Find the assignee user
  //     $assignee = User::find($task['assignee_id']);

  //     if ($assignee) {
  //       // Notify the assignee through email
  //       $assignee->notify(new TaskAssignedNotification($leave, $task->toArray()));

  //       Log::info("Notified task assignee {$assignee->firstname} (User ID: {$assignee->id}) for Leave ID: {$leave->id}, Task: {$task['task_description']}");
  //     } else {
  //       Log::warning("Task assignee not found for User ID: {$task['assignee_id']} on Leave ID: {$leave->id}");
  //     }
  //   }
  // }






  public function approveLeave(Request $request, Leave $leave)
  {
    try {
      Log::info('Approve Leave Request', [
        'userId' => $request->input('userId'),
        'leaveId' => $leave->id,
        'requestData' => $request->all()
      ]);

      $userId = $request->input('userId');
      $approver = User::find($userId);

      if (!$approver) {
        return response()->json(['error' => 'Approver not found'], 404);
      }

      // Check if this is an intern/assistant outside Kenya
      $userCountry = strtolower($leave->user->unit->name ?? 'kenya');
      $userDesignation = strtolower($leave->user->designation->name ?? '');
      Log::info('Approving leave for user', [
        'user_country' => $userCountry,
        'user_designation' => $userDesignation,
        'user_id' => $leave->user->id
      ]);
      $isInternOrAssistantOutsideKenya = in_array($userDesignation, ['intern', 'assistant']) && $userCountry !== 'kenya';

      Log::info('Leave approval check', [
        'designation' => $leave->user->designation->name ?? 'unknown',
        'country' => $leave->user->unit->name ?? 'kenya',
        'isSpecialCase' => $isInternOrAssistantOutsideKenya
      ]);

      switch ($leave->status) {
        case 'Pending':
          // Only Country Manager (designation_id === 1) or HR can approve initial request
          if ($approver->designation_id === 1 || ($approver->is_hr === 1 && $approver->department_id === 1)) {
            if ($isInternOrAssistantOutsideKenya) {
              // Special case: Set custom status for interns/assistants outside Kenya
              $leave->status = 'Manager Approval Only';
              $this->logLeaveAction($leave, 'Manager Approved (Special Case)', $userId);
              $this->notifyNextApprover($leave, 'HR');
              Log::info('Applied special approval route for intern/assistant outside Kenya');
            } else {
              // Standard flow
              $leave->status = 'Manager Approved';
              $this->logLeaveAction($leave, 'Manager Approved', $userId);
              $this->notifyNextApprover($leave, 'HR');
            }
          } else {
            return response()->json(['error' => 'Unauthorized - Only Country Manager or HR can approve initial requests'], 403);
          }
          break;

        case 'Manager Approval Only':
          // Special case: Only HR can approve, then goes directly to final approval (bypasses HOD)
          if ($approver->is_hr === 1) {
            $leave->status = 'Approved';

            $this->logLeaveAction($leave, 'Final Approved (HR Only - Special Case)', $userId);
            $this->notifyEmployee($leave);
            $this->notifyTaskAssignees($leave);
            Log::info('Special case approval completed - HOD bypassed for intern/assistant outside Kenya');
          } else {
            return response()->json(['error' => 'Unauthorized - Only HR can approve this special case'], 403);
          }
          break;

        case 'Manager Approved':
          // Standard flow: HR approval required
          if ($approver->is_hr === 1) {
            $leave->status = 'Hr Approved';
            $this->logLeaveAction($leave, 'Hr Approved', $userId);
            $this->notifyNextApprover($leave, 'HOD');
          } else {
            return response()->json(['error' => 'Unauthorized - Only HR can approve at this stage'], 403);
          }
          break;

        case 'Hr Approved':
          // Standard flow: HOD approval required
          if ($approver->is_hod === 1) {
            // Verify HOD has authority over the leave applicant's department
            $hasAuthority = $approver->hodDepartments()
              ->where('department_id', $leave->user->department_id)
              ->exists();

            if ($hasAuthority) {
              $leave->status = 'Approved';
              $this->logLeaveAction($leave, 'Final Approved (Standard Flow)', $userId);
              $this->notifyEmployee($leave);
              $this->notifyTaskAssignees($leave);
              Log::info("HOD approval completed for Department ID: {$leave->user->department_id}");
            } else {
              return response()->json(['error' => 'Unauthorized - HOD does not have authority over this department'], 403);
            }
          } else {
            return response()->json(['error' => 'Unauthorized - Only HOD can approve at this stage'], 403);
          }
          break;

        default:
          return response()->json(['error' => 'Invalid leave status for approval'], 400);
      }

      $leave->save();

      $responseMessage = $isInternOrAssistantOutsideKenya && $leave->status === 'Approved'
        ? 'Leave approved successfully (Special approval process - HOD bypassed)'
        : 'Leave approved successfully';

      return response()->json(['message' => $responseMessage], 200);
    } catch (\Exception $e) {
      Log::error('Error approving leave', ['exception' => $e]);
      return response()->json(['error' => 'Failed to approve leave'], 500);
    }
  }

  /**
   * Enhanced notification method that handles special cases
   */
  private function notifyNextApprover(Leave $leave, string $role)
  {
    // Check if this is a special case that should skip HOD
    $isInternOrAssistantOutsideKenya = in_array(strtolower($leave->user->designation->name ?? ''), ['intern', 'assistant']) &&
      strtolower($leave->user->office->country ?? 'kenya') !== 'kenya';

    // Skip HOD notification for special cases
    if ($role === 'HOD' && $isInternOrAssistantOutsideKenya) {
      Log::info('Skipping HOD notification for intern/assistant outside Kenya (Leave ID: ' . $leave->id . ')');
      return;
    }

    $nextApprovers = match ($role) {
      'HR' => User::where('is_hr', 1)->get(),
      'HOD' => User::whereHas('hodDepartments', function ($query) use ($leave) {
        $query->where('department_id', $leave->user->department_id);
      })->get(),
      default => collect(),
    };

    foreach ($nextApprovers as $approver) {
      $approver->notify(new LeaveCreatedNotification($leave));

      if ($role === 'HR') {
        Log::info("Notified HR (User ID: {$approver->id}) regarding Leave ID: {$leave->id}");
      } else {
        Log::info("Notified HOD (User ID: {$approver->id}) for Department ID: {$leave->user->department_id} regarding Leave ID: {$leave->id}");
      }
    }
  }

  /**
   * Notify the employee upon final approval
   */
  private function notifyEmployee(Leave $leave)
  {
    $message = "Hello {$leave->user->firstname}, Your leave request from {$leave->from} to {$leave->to} has been approved.";
    $smsUtil = match ($leave->user->unit_id) {
      2 => new UGSMSUtil(),
      3 => new TZSMSUtil(),
      default => new SMSUtil(),
    };

    try {
      $smsUtil->sendSMS($leave->phone, $message);
      $leave->user->notify(new LeaveApprovalNotification($leave));

      Log::info("Sent leave approval email to employee {$leave->user->firstname} (Email: {$leave->user->email})");
      Log::info("Sent leave approval SMS to employee {$leave->user->firstname} (Phone: {$leave->phone})");
    } catch (\Exception $e) {
      Log::error("Failed to send SMS, sending email instead", ['exception' => $e]);

      $leave->user->notify(new LeaveApprovalNotification($leave));
      Log::info("Sent leave approval email to employee {$leave->user->firstname} (Email: {$leave->user->email})");
    }
  }

  /**
   * Notify task assignees after leave approval
   */
  private function notifyTaskAssignees(Leave $leave)
  {
    $tasks = $leave->tasks ?? [];

    foreach ($tasks as $task) {
      if (empty($task['assignee_id'])) {
        continue;
      }

      $assignee = User::find($task['assignee_id']);

      if ($assignee) {
        // $assignee->notify(new TaskAssignedNotification($leave, $task));
        $assignee->notify(new TaskAssignedNotification($leave, $task->toArray()));
        Log::info("Notified task assignee {$assignee->firstname} (User ID: {$assignee->id}) for Leave ID: {$leave->id}, Task: {$task['task_description']}");
      } else {
        Log::warning("Task assignee not found for User ID: {$task['assignee_id']} on Leave ID: {$leave->id}");
      }
    }
  }





  public function cancelLeave(Request $request, Leave $leave)
  {
    try {
      if ($leave->status === 'Cancelled') {
        return response()->json(['error' => 'Leave is already Cancelled'], 400);
      }

      if (!$leave->user) {
        Log::error('Error: Attempting to cancel leave with no associated user');
        throw new \Exception('Error: Attempting to cancel leave with no associated user');
      }

      $leave->status = 'Cancelled';
      $leave->is_active == 0;
      $leave->comment = $request->comment;
      $leave->save();

      $userLeaveBalance = LeaveBalance::where('user_id', $leave->user_id)
        ->where('leave_type_id', $leave->leave_type_id)
        ->first();

      if ($userLeaveBalance) {
        $userLeaveBalance->increment('balance', $leave->days);
      }

      $this->logLeaveAction($leave, 'Cancelled', $request->input('userId'));

      $sender_phone = $leave->phone;
      $employeeName = $leave->user->firstname;

      $admin = User::find($request->userId);
      $firstName = $admin ? $admin->firstname : 'anonymous';

      $cancelMessage = "Hi $employeeName, Your Leave application from $leave->from to $leave->to has been Cancelled by " . $firstName . ", Reason: " . $request->comment;

      $sms_util = new SMSUtil();
      $sms_util->sendSMS($sender_phone, $cancelMessage);

      return response()->json(['message' => 'Leave canceled successfully'], 200);
    } catch (\Exception $e) {
      Log::error($e);
      return response()->json(['error' => 'Failed to cancel leave'], 500);
    }
  }

  public function userLeaveCancel(Request $request, Leave $leave)
  {
    $user_id = $leave->user_id;

    try {

      $leave->status = 'Cancelled';
      $leave->comment = $request->comment;
      $leave->is_active == 0;
      $leave->save();

      $userLeaveBalance = LeaveBalance::where('user_id', $leave->user_id)
        ->where('leave_type_id', $leave->leave_type_id)
        ->first();

      if ($userLeaveBalance) {
        $userLeaveBalance->increment('balance', $leave->days);
      }

      $this->logLeaveAction($leave, 'Cancelled', $user_id);

      return response()->json(['message' => 'Leave canceled successfully'], 200);
    } catch (\Exception $e) {
      Log::debug($e);
      return response()->json(['error' => 'Failed to cancel leave'], 500);
    }
  }

  protected function logLeaveAction($leave, $action, $userId)
  {
    $leave->logs()->create([
      'action' => $action,
      'user_id' => $userId,
    ]);
  }

  public function leaveLogs(Leave $leave)
  {
    $logs = $leave->logs()->with('user')->get();
    $formattedLogs = $logs->map(function ($log) {
      if ($log->user) {
        $fullName = $log->user->firstname . ' ' . $log->user->lastname;

        return [
          'user' => $fullName,
          'action' => $log->action,
          'time' => $log->created_at->toDateTimeString(),
        ];
      } else {
        return [
          'user' => 'Unknown User',
          'action' => $log->action,
          'time' => $log->created_at->toDateTimeString(),
        ];
      }
    });

    return response()->json(['logs' => $formattedLogs]);
  }

  public function statistics()
  {
    $users = User::all();

    $leaveTypes = LeaveType::all();

    $allUsersLeaveStatistics = [];

    foreach ($users as $user) {
      $userLeaves = Leave::where('user_id', $user->id)->get();

      $leaveBalances = LeaveBalance::where('user_id', $user->id)->get();

      $userLeaveStatistics = [];

      foreach ($leaveTypes as $leaveType) {
        $userLeaveStatistics[] = [
          'leave_type' => $leaveType->name,
          'leave_taken' => $userLeaves->where('leave_type_id', $leaveType->id)->count(),
          'remaining_balance' => $leaveBalances->where('leave_type_id', $leaveType->id)->first()->balance ?? 0,
        ];
      }

      $allUsersLeaveStatistics[] = [
        'user_id' => $user->id,
        'user_name' => $user->name,
        'leave_statistics' => $userLeaveStatistics,
      ];
    }

    return response()->json(['leave_statistics' => $allUsersLeaveStatistics]);
  }

  public function update(Request $request, Leave $leave)
  {


    // return response($request->all());

    // Validate the request data
    $validatedData = $request->validate([
      // 'leave_type_id' => 'required',
      // 'from' => 'required|date',
      // 'to' => 'required|date',
      // 'days' => 'required|numeric',
      // 'comment' => 'nullable|string',

      'document' => 'required|file|mimes:jpg,png,pdf|max:2048', // Example validation

    ]);


    // log request 
    Log::info('Update leave request received', [
      'leave_id' => $leave->id,
      'request_data' => $request->all(),
      'user_id' => auth()->id()
    ]);


    // Handle document upload
    if ($request->hasFile('document')) {
      try {
        // Delete old document if exists
        if ($leave->document) {
          Storage::disk('public')->delete('leave/documents/' . $leave->document);
        }

        $file = $request->file('document');
        $documentName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->atte('leave/documents', $documentName, 'public');
        $validatedData['document'] = $documentName;

        Log::info('Document updated successfully', ['document' => $documentName]);
      } catch (\Exception $e) {
        Log::error('Document upload failed during update', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Failed to upload document.'], 500);
      }
    }

    $leave->update($validatedData);

    // 💡 Update delegated tasks
    if ($request->filled('delegatedTasks')) {
      try {
        $tasks = is_string($request->delegatedTasks)
          ? json_decode($request->delegatedTasks, true)
          : $request->delegatedTasks;

        $leave->tasks()->delete(); // Remove existing tasks

        if (is_array($tasks)) {
          foreach ($tasks as $task) {
            if (!empty($task['assignee_id']) && !empty($task['task_description'])) {
              $leave->tasks()->create([
                'assignee_id' => $task['assignee_id'],
                'task_description' => $task['task_description'],
              ]);
            }
          }
        }
      } catch (\Exception $e) {
        Log::error('Failed to update delegated tasks', ['error' => $e->getMessage()]);
      }
    }

    // 🔄 Load tasks and related models
    $leave->load('tasks.assignee', 'leave_type', 'user');


    return response()->json(['message' => 'Leave updated successfully'], 200);
  }

  public function destroy(Leave $leave)
  {
    $leave->delete();

    return response()->json(['message' => 'Leave deleted successfully']);
  }

  public function leaveForm(Request $request)
  {
    try {
      $leaveData = $request->leave;

      $user = User::with(['department', 'leaveBalances'])->findOrFail($leaveData['user']['id']);

      $pdf = PDF::loadView('leaves.leave_form', compact('leaveData', 'user'));

      return $pdf->download('invoice.pdf');
    } catch (\Exception $e) {
      return $e->getMessage();
    }
  }

  public function generateExcel(Request $request)
  {
    $request->input('leaves');

    return response()->json(['message' => 'Excel file generated successfully']);
  }

  public function generatePdf(Request $request)
  {

    return response()->json(['message' => 'PDF file generated successfully']);
  }




  public function bulkApprove(Request $request)
  {
    try {
      Log::info('Bulk Approve Leave Request', [
        'userId' => $request->input('userId'),
        'leaveIds' => $request->input('leaveIds'),
        'requestData' => $request->all()
      ]);

      $userId = $request->input('userId');
      $leaveIds = $request->input('leaveIds');
      $approver = User::find($userId);

      if (!$approver) {
        return response()->json(['error' => 'Approver not found'], 404);
      }

      if (!is_array($leaveIds) || empty($leaveIds)) {
        return response()->json(['error' => 'No leave IDs provided'], 400);
      }

      $results = [
        'success' => [],
        'failed' => []
      ];

      foreach ($leaveIds as $leaveId) {
        $leave = Leave::find($leaveId);

        if (!$leave) {
          $results['failed'][] = [
            'id' => $leaveId,
            'reason' => 'Leave not found'
          ];
          continue;
        }

        try {
          switch ($leave->status) {
            case 'Pending':
              if ($approver->designation_id === 1 || ($approver->is_hr === 1 && $approver->department_id === 1)) {
                $leave->status = 'Manager Approved';
                $this->logLeaveAction($leave, 'Manager Approved', $userId);
                $this->notifyNextApprover($leave, 'HR');
                $leave->save();
                $results['success'][] = $leaveId;
              } else {
                $results['failed'][] = [
                  'id' => $leaveId,
                  'reason' => 'Unauthorized'
                ];
              }
              break;

            case 'Manager Approved':
              if ($approver->is_hr === 1) {
                $leave->status = 'Hr Approved';
                $this->logLeaveAction($leave, 'Hr Approved', $userId);
                $this->notifyNextApprover($leave, 'HOD');
                $leave->save();
                $results['success'][] = $leaveId;
              } else {
                $results['failed'][] = [
                  'id' => $leaveId,
                  'reason' => 'Unauthorized'
                ];
              }
              break;

            case 'Hr Approved':
              if ($approver->is_hod === 1) {
                // Check if HOD can approve this leave (belongs to their department)
                $canApprove = $approver->hodDepartments()
                  ->where('departments.id', $leave->user->department_id)
                  ->exists();

                if ($canApprove) {
                  $leave->status = 'Approved';
                  $this->logLeaveAction($leave, 'Approved', $userId);
                  $this->notifyEmployee($leave);
                  $leave->save();
                  $results['success'][] = $leaveId;
                } else {
                  $results['failed'][] = [
                    'id' => $leaveId,
                    'reason' => 'Not authorized for this department'
                  ];
                }
              } else {
                $results['failed'][] = [
                  'id' => $leaveId,
                  'reason' => 'Unauthorized'
                ];
              }
              break;

            default:
              $results['failed'][] = [
                'id' => $leaveId,
                'reason' => 'Invalid leave status: ' . $leave->status
              ];
          }
        } catch (\Exception $e) {
          Log::error('Error processing leave in bulk approve', [
            'leaveId' => $leaveId,
            'exception' => $e
          ]);

          $results['failed'][] = [
            'id' => $leaveId,
            'reason' => 'Processing error'
          ];
        }
      }

      return response()->json([
        'message' => 'Bulk leave approval processed',
        'results' => $results
      ], 200);
    } catch (\Exception $e) {
      Log::error('Error in bulk approve leave', ['exception' => $e]);
      return response()->json(['error' => 'Failed to process bulk leave approval'], 500);
    }
  }

  public function bulkCancel(Request $request)
  {
    try {
      Log::info('Bulk Cancel Leave Request', [
        'userId' => $request->input('userId'),
        'leaveIds' => $request->input('leaveIds'),
        'comment' => $request->input('comment'),
        'requestData' => $request->all()
      ]);

      $userId = $request->input('userId');
      $leaveIds = $request->input('leaveIds');
      $comment = $request->input('comment');
      $admin = User::find($userId);

      if (!$admin) {
        return response()->json(['error' => 'User not found'], 404);
      }

      if (!is_array($leaveIds) || empty($leaveIds)) {
        return response()->json(['error' => 'No leave IDs provided'], 400);
      }

      $results = [
        'success' => [],
        'failed' => []
      ];

      foreach ($leaveIds as $leaveId) {
        $leave = Leave::find($leaveId);

        if (!$leave) {
          $results['failed'][] = [
            'id' => $leaveId,
            'reason' => 'Leave not found'
          ];
          continue;
        }

        try {
          if ($leave->status === 'Cancelled') {
            $results['failed'][] = [
              'id' => $leaveId,
              'reason' => 'Leave is already cancelled'
            ];
            continue;
          }

          if (!$leave->user) {
            $results['failed'][] = [
              'id' => $leaveId,
              'reason' => 'Leave has no associated user'
            ];
            continue;
          }

          $leave->status = 'Cancelled';
          $leave->is_active = 0;
          $leave->comment = $comment;
          $leave->save();

          $userLeaveBalance = LeaveBalance::where('user_id', $leave->user_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->first();

          if ($userLeaveBalance) {
            $userLeaveBalance->increment('balance', $leave->days);
          }

          $this->logLeaveAction($leave, 'Cancelled', $userId);

          $sender_phone = $leave->phone;
          $employeeName = $leave->user->firstname;
          $firstName = $admin ? $admin->firstname : 'anonymous';

          $cancelMessage = "Hi $employeeName, Your Leave application from $leave->from to $leave->to has been Cancelled by " . $firstName . ", Reason: " . $comment;

          $sms_util = new SMSUtil();
          $sms_util->sendSMS($sender_phone, $cancelMessage);

          $results['success'][] = $leaveId;
        } catch (\Exception $e) {
          Log::error('Error processing leave in bulk cancel', [
            'leaveId' => $leaveId,
            'exception' => $e
          ]);

          $results['failed'][] = [
            'id' => $leaveId,
            'reason' => 'Processing error'
          ];
        }
      }

      return response()->json([
        'message' => 'Bulk leave cancellation processed',
        'results' => $results
      ], 200);
    } catch (\Exception $e) {
      Log::error('Error in bulk cancel leave', ['exception' => $e]);
      return response()->json(['error' => 'Failed to process bulk leave cancellation'], 500);
    }
  }
}
