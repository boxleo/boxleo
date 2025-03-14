<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionLog;
use App\Models\User;
use App\Notifications\RequisitionApprovedNotification;
use App\Notifications\RequisitionCanceledNotification;
use App\Notifications\RequisitionCreatedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequisitionApiController extends Controller
{
    //


    public function deleteRequisition($id)
    {
        try {
            $requisition = Requisition::with('items')->findOrFail($id);

            if (!in_array($requisition->status, ['Pending', 'Canceled'])) {
                return response()->json([
                    'error' => 'Requisitions that are approved or in-progress cannot be deleted.'
                ], 400);
            }

            $requisition->items()->delete();
            $requisition->delete();

            Log::info('Requisition deleted successfully', ['requisition_id' => $requisition->id]);


            $this->logRequisitionAction($requisition, 'Deleted',  $requisition, $requisition->user_id);




            return response()->json([
                'message' => 'Requisition deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting requisition', ['exception' => $e->getMessage()]);

            return response()->json(['error' => 'Failed to delete requisition'], 500);
        }
    }


    public function show($id)
    {
        // Retrieve the requisition with related data, e.g., user and department
        $requisition = Requisition::with(['items', 'user.department'])->find($id);

        if (!$requisition) {
            return response()->json([
                'message' => 'Requisition not found',
            ], 404);
        }

        return response()->json([
            'data' => $requisition,
        ]);
    }

    public function index()
    {
        $requisitions = Requisition::with('items', 'user.department')
            ->withSum('items', 'total_cost')
            ->get();

        // $requisitions= Requisition::all();


        return response()->json(['requisitions' => $requisitions]);
    }

    public function updateRequisition(Request $request, $id)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.total_cost' => 'required|numeric|min:0',
            'special_instructions' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:Pending,Manager Approved,COO Approved,HR Approved,Finance Manager Approved,Approved',
        ]);

        try {
            // Find the requisition
            $requisition = Requisition::with('items')->findOrFail($id);

            // Update requisition details
            $requisition->special_instructions = $validated['special_instructions'] ?? $requisition->special_instructions;
            $requisition->status = $validated['status'] ?? $requisition->status;
            $requisition->save();

            // Update requisition items
            $requisition->items()->delete(); // Delete old items
            foreach ($validated['items'] as $item) {
                $requisition->items()->create([
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['total_cost'],
                ]);
            }

            // Log update
            Log::info('Requisition updated successfully', [
                'requisition_id' => $requisition->id,
                'user_id' => $requisition->user_id,
            ]);

            return response()->json([
                'message' => 'Requisition updated successfully',
                'requisition' => $requisition->load('items'),
            ], 200);
        } catch (\Exception $e) {
            // Log error
            Log::error('Error updating requisition', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to update requisition'], 500);
        }
    }


    public function cancelRequisition(Request $request, $id)
    {
        try {
            // Find the requisition
            $requisition = Requisition::findOrFail($id);

            // Ensure the requisition can be canceled
            if (!in_array($requisition->status, ['Pending', 'Manager Approved', 'COO Approved', 'HR Approved', 'Finance Manager Approved'])) {
                return response()->json(['error' => 'Requisition cannot be canceled in its current state'], 400);
            }

            // Update status to "Canceled"
            $requisition->status = 'Canceled';
            $requisition->comment = $request->input('comment'); // Store the comment in the column

            $requisition->save();

            // Notify the user and approvers if necessary
            $requisition->user->notify(new RequisitionCanceledNotification($requisition));
            Log::info('Requisition canceled successfully', [
                'requisition_id' => $requisition->id,
            ]);


            $this->logRequisitionAction($requisition, 'Canceled', json_encode($request->all()), $requisition->user_id);


            return response()->json(['message' => 'Requisition canceled successfully'], 200);
        } catch (\Exception $e) {
            // Log error
            Log::error('Error canceling requisition', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to cancel requisition'], 500);
        }
    }


    public function store(Request $request)
    {

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.total_cost' => 'required|numeric|min:0',
            'special_instructions' => 'nullable|string|max:500',
            'user_id' => 'required|exists:users,id',
        ]);

        // find user with department 


        $user = User::with('department')->findOrFail($validated['user_id']);
        $departmentId = $user->department->id ?? null;

        if (!$departmentId) {
            return response()->json([
                'error' => 'The user is not associated with any department.',
            ], 422);
        }

        try {
            // Create the requisition
            $requisition = Requisition::create([
                'user_id' => $validated['user_id'],
                'special_instructions' => $validated['special_instructions'] ?? null,
                'status' => 'Pending',
                'department_id' => $departmentId,
                'unit_id' => $user->unit_id,
                'office_id' => $user->office_id,


            ]);

            // Attach requisition items
            foreach ($validated['items'] as $item) {
                $requisition->items()->create([
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['total_cost'],
                ]);
            }



            $manager = User::where('department_id', $departmentId)
                ->where('designation_id', 1)
                ->first();

            if ($manager) {
                $manager->notify(new RequisitionCreatedNotification($requisition));
            } else {
                Log::warning('No department manager found for department', ['department_id' => $departmentId]);
            }


            // Log creation
            Log::info('Requisition created successfully', [
                'requisition_id' => $requisition->id,
                'user_id' => $requisition->user_id,
            ]);


            // Log creation action
            $this->logRequisitionAction($requisition, 'Created', json_encode($request->all()), $requisition->user_id);

            return response()->json([
                'message' => 'Requisition created successfully',
                'requisition' => $requisition->load('items'),
            ], 201);
        } catch (\Exception $e) {
            // Log error
            Log::error('Error creating requisition', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to create requisition',
            ], 500);
        }
    }



    // public function approveRequisition(Request $request, Requisition $requisition)
    // {
    //     try {
    //         Log::info('Approve Requisition Request Received', [
    //             'userId' => $request->input('user_id'),
    //             'requisitionId' => $requisition->id,
    //             'requestData' => $request->all(),
    //         ]);

    //         $userId = $request->input('user_id');
    //         $approver = User::find($userId);

    //         // find request 
    //         // $requisition= Requisition::find($requisition->id);

    //         $requisition = Requisition::find($request->input('requisition_id'));


    //         //  dd($requisition);

    //         if (!$approver) {
    //             Log::warning('Approver not found', ['userId' => $userId]);
    //             return response()->json(['error' => 'Approver not found'], 404);
    //         }

    //         $approverDepartment = $approver->department_id;
    //         $requestDepartment = $requisition->department_id;

    //         Log::info('Approver Retrieved', ['approver' => $approver]);
    //         Log::info('Department Check', [
    //             'approverDepartment' => $approverDepartment,
    //             'requisitionDepartment' => $requestDepartment,
    //         ]);

    //         if ($approver->is_line_manager === 1 || ($approver->designation_id === 1 && $requisition->status === 'Pending')) {
    //             if ($approverDepartment === $requestDepartment) {
    //                 $requisition->status = 'Manager Approved';
    //                 $requisition->is_line_manager = 1;
    //                 Log::info('Requisition Status Updated', ['newStatus' => 'Manager Approved']);


    //             } else {
    //                 Log::warning('Department Mismatch');
    //                 return response()->json(['error' => 'You can only approve requests in your department'], 403);
    //             }
    //         } elseif ($approver->is_coo === 1 && $requisition->status === 'Manager Approved') {
    //             $requisition->status = 'COO Approved';
    //             $requisition->is_coo = 1;
    //             Log::info('Requisition Status Updated', ['newStatus' => 'COO Approved']);
    //         } elseif ($approver->is_hr === 1 && $requisition->status === 'COO Approved') {
    //             $requisition->status = 'HR Approved';
    //             $requisition->is_hr = 1;

    //             Log::info('Requisition Status Updated', ['newStatus' => 'HR Approved']);
    //         } elseif ($approver->is_finance_manager === 1 && $requisition->status === 'HR Approved') {
    //             $requisition->status = 'Finance Manager Approved';
    //             $requisition->is_finance_manager = 1;

    //             Log::info('Requisition Status Updated', ['newStatus' => 'Finance Manager Approved']);
    //         } elseif ($approver->is_cfo === 1 && $requisition->status === 'Finance Manager Approved') {
    //             $requisition->status = 'Approved';
    //             $requisition->is_cfo = 1;


    //             // Send the Requisition Approved notification to the user 
    //             // include finance team members  
    //             //   
    //             $requisition->user->notify(new RequisitionApprovedNotification($requisition));
    //             Log::info('Requisition Approved Notification Sent');
    //         } else {
    //             Log::warning('Unauthorized Approval Attempt');
    //             return response()->json(['error' => 'Unauthorized'], 403);
    //         }



    //         $requisition->comment = $request->input('comment'); 

    //         $requisition->save();


    //           // Log the saved comment
    //     Log::info('Requisition Comment Saved', [
    //         'requisition_id' => $requisition->id,
    //         'comment' => $requisition->comment,
    //     ]);



    //         // Check if there's a next approver
    //         if ($requisition->status !== 'Approved') {
    //             $nextApprover = $this->getNextApprover($requisition);
    //             if ($nextApprover) {
    //                 $nextApprover->notify(new RequisitionCreatedNotification($requisition));
    //                 Log::info('Next Approver Notified', ['nextApproverId' => $nextApprover->id]);
    //             }
    //         }

    //         return response()->json(['message' => 'Requisition approved successfully'], 200);
    //     } catch (\Exception $e) {
    //         Log::error('Error Approving Requisition', [
    //             'exceptionMessage' => $e->getMessage(),
    //             'stackTrace' => $e->getTraceAsString(),
    //         ]);

    //         return response()->json(['error' => 'Failed to approve requisition'], 500);
    //     }
    // }




    public function approveRequisition(Request $request, Requisition $requisition)
    {
        try {
            Log::info('Approve Requisition Request Received', [
                'userId' => $request->input('user_id'),
                'requisitionId' => $requisition->id,
                'requestData' => $request->all(),
            ]);

            // type of requistion main  go through all steps  subsdiray manager then finance manager

            $userId = $request->input('user_id');
            $approver = User::find($userId);

            $requisition = Requisition::find($request->input('requisition_id'));

            if (!$approver) {
                Log::warning('Approver not found', ['userId' => $userId]);
                return response()->json(['error' => 'Approver not found'], 404);
            }

            $approverDepartment = $approver->department_id;
            $requestDepartment = $requisition->department_id;

            Log::info('Approver Retrieved', ['approver' => $approver]);
            Log::info('Department Check', [
                'approverDepartment' => $approverDepartment,
                'requisitionDepartment' => $requestDepartment,
            ]);

            $details = $request->input('comment'); // Capture the approver's comment

            if ($approver->is_line_manager === 1 || ($approver->designation_id === 1 && $requisition->status === 'Pending')) {
                if ($approverDepartment === $requestDepartment) {
                    $requisition->status = 'Manager Approved';
                    $requisition->is_line_manager = 1;

                    // Log the approval action
                    $this->logRequisitionAction($requisition, 'Manager Approved', $details, $userId);
                    Log::info('Requisition Status Updated', ['newStatus' => 'Manager Approved']);
                } else {
                    Log::warning('Department Mismatch');
                    return response()->json(['error' => 'You can only approve requests in your department'], 403);
                }
            } elseif ($approver->is_hr === 1 && $requisition->status === 'Manager Approved') {
                $requisition->status = 'HR Approved';
                $requisition->is_hr = 1;

                // Log the COO approval
                $this->logRequisitionAction($requisition, 'HR Approved', $details, $userId);
                Log::info('Requisition Status Updated', ['newStatus' => 'HR Approved']);

            } elseif ($approver->is_finance_manager === 1 && $requisition->status === 'HR Approved') {
                $requisition->status = 'Finance Manager Approved';
                $requisition->is_finance_manager = 1;

                // Log the HR approval
                $this->logRequisitionAction($requisition, 'Finance Manager Approved', $details, $userId);
                Log::info('Requisition Status Updated', ['newStatus' => 'Finance Manager Approved']);
            } elseif ($approver->is_coo === 1 && $requisition->status === 'Finance Manager Approved') {
                $requisition->status = 'COO Approved';
                $requisition->is_coo = 1;

                // Log the Finance Manager approval
                $this->logRequisitionAction($requisition, 'COO Approved', $details, $userId);
                Log::info('Requisition Status Updated', ['newStatus' => 'COO Approved']);

            } elseif ($approver->is_cfo === 1 && $requisition->status === 'COO Approved') {
                $requisition->status = 'Approved';
                $requisition->is_cfo = 1;

                // Log the CFO approval
                $this->logRequisitionAction($requisition, 'Approved', $details, $userId);

                // Send the Requisition Approved notification
                // include finance team members
                $requisition->user->notify(new RequisitionApprovedNotification($requisition));

                Log::info('Requisition Approved Notification Sent');

                Log::info('Notifying finance team members', ['requisition_id' => $requisition->id]);

                $financeTeam = User::where('department_id', 2)->where('unit_id', 1)->get();

                // Notify all finance team members
                foreach ($financeTeam as $financeUser) {
                    $financeUser->notify(new RequisitionApprovedNotification($requisition));
                    Log::info('Finance team member notified', ['user_id' => $financeUser->id]);
                }
            } else {
                Log::warning('Unauthorized Approval Attempt');
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Save any comment provided by the approver
            $requisition->comment = $request->input('comment');
            $requisition->save();

            // Log the saved comment
            Log::info('Requisition Comment Saved', [
                'requisition_id' => $requisition->id,
                'comment' => $requisition->comment,
            ]);

            // Check if there's a next approver
            if ($requisition->status !== 'Approved') {
                $nextApprover = $this->getNextApprover($requisition);
                if ($nextApprover) {
                    $nextApprover->notify(new RequisitionCreatedNotification($requisition));
                    Log::info('Next Approver Notified', ['nextApproverId' => $nextApprover->id]);
                }
            }

            return response()->json(['message' => 'Requisition approved successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error Approving Requisition', [
                'exceptionMessage' => $e->getMessage(),
                'stackTrace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to approve requisition'], 500);
        }
    }


    private function getNextApprover($requisition)
    {
        $nextRole = [
            'Manager Approved' => 'is_hr',
            'HR Approved' => 'is_finance_manager',
            'Finance Manager Approved' => 'is_coo',
            'COO Approved' => 'is_cfo',
            'Approved' => 'is_cfo',
        ];


        $currentStatus = $requisition->status;
        if (isset($nextRole[$currentStatus])) {
            return User::where($nextRole[$currentStatus], true)->first();
        }

        return null;
    }


    protected function logRequisitionAction(Requisition $requisition, $action, $details = null, $userId = null)
    {
        Log::debug('logRequisitionAction invoked', [
            'requisition_id' => $requisition->id,
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
        ]);

        try {
            RequisitionLog::create([
                'requisition_id' => $requisition->id,
                'user_id' => $userId,
                'action' => $action,
                'details' => $details,
            ]);

            Log::info('Requisition Action Logged', [
                'requisition_id' => $requisition->id,
                'user_id' => $userId,
                'action' => $action,
                'details' => $details,
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging requisition action', [
                'exception' => $e->getMessage(),
                'requisition_id' => $requisition->id,
                'user_id' => $userId,
                'action' => $action,
                'details' => $details,
            ]);
        }
    }



    public function requisitionLogs($id)
    {
        try {
            // Log the received ID for debugging
            Log::info('Fetching requisition logs', ['requisition_id' => $id]);


            //  $logs =RequisitionLog::all();
            // Fetch logs for the given requisition and include user data
            $logs = RequisitionLog::where('requisition_id', $id)->get();

            // Log the fetched logs for debugging
            Log::info('Requisition logs retrieved', ['logs' => $logs]);

            // Format logs for better readability
            $formattedLogs = $logs->map(function ($log) {
                if ($log->user) {
                    $fullName = $log->user->firstname . ' ' . $log->user->lastname;

                    return [
                        'user' => $fullName,
                        'action' => $log->action,
                        'details' => $log->details,
                        'time' => $log->created_at->toDateTimeString(),
                    ];
                } else {
                    return [
                        'user' => 'Unknown User',
                        'action' => $log->action,
                        'details' => $log->details,
                        'time' => $log->created_at->toDateTimeString(),
                    ];
                }
            });

            // Log the formatted logs for additional visibility
            Log::info('Formatted requisition logs', ['formatted_logs' => $formattedLogs]);

            return response()->json(['logs' => $formattedLogs], 200);
        } catch (\Exception $e) {
            // Log the error details
            Log::error('Error fetching requisition logs', [
                'requisition_id' => $id,
                'exception_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to fetch requisition logs'], 500);
        }
    }


    public function generatePdf($id)
    {
        try {
            // Retrieve the requisition with related data
            $requisition = Requisition::with([
                'items',
                'user.department',
                'requisitionLogs'
                // 'requisition'
            ])->find($id);

            // return response()->json($requisition);



            if (!$requisition) {
                return response()->json([
                    'message' => 'Requisition not found',
                ], 404);
            }

            // Generate PDF content using a view
            Log::info('Rendering View for PDF', ['view' => 'requisition.pdf']);

            $pdf = Pdf::loadView('requisitions.pdf', compact('requisition'));
            Log::info('PDF Successfully Generated', ['requisition_id' => $requisition->id]);


            // Stream the generated PDF
            return $pdf->stream("Requisition_{$requisition->id}.pdf");
        } catch (\Exception $e) {
            Log::error('Error generating requisition PDF', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to generate requisition PDF'], 500);
        }
    }
}
