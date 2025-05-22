<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\PerformanceEvaluation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerformanceApiEvaluation extends Controller
{
    //
    // store monthly performance evaluation
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            // 'evaluator_id' => 'required|integer',
            'unit_id' => 'required|integer',
            'department_id' => 'required|integer',
            // 'evaluation_date' => 'required|date',
            'attendance' => 'required|integer|min:0|max:10',
            'problems_solved' => 'required|integer|min:0|max:10',
            'reports_submitted' => 'required|integer|min:0|max:10',
            'knowledge_of_work' => 'required|integer|min:0|max:10',
            'team_work' => 'required|integer|min:0|max:10',
            'reliability_visibility' => 'required|integer|min:0|max:10',
            'productivity' => 'required|integer|min:0|max:10',
            'discipline' => 'required|integer|min:0|max:10',
            'quality_of_work' => 'required|integer|min:0|max:10',
            'communication' => 'required|integer|min:0|max:10',
            'total_score' => 'required|integer',
            'percentage' => 'required|numeric',
            'leadership' => 'nullable|numeric|min:0|max:10',
        ]);

        // Assuming you have a model named PerformanceEvaluation
        $performance = new PerformanceEvaluation($validatedData);
        $performance->save();

        return response()->json(['message' => 'Performance evaluation stored successfully'], 201);
    }

    // update monthly performance evaluation
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            // 'evaluator_id' => 'required|integer',
            'unit_id' => 'required|integer',
            'department_id' => 'required|integer',
            // 'evaluation_date' => 'required|date',
            'attendance' => 'required|integer|min:0|max:10',
            'problems_solved' => 'required|integer|min:0|max:10',
            'reports_submitted' => 'required|integer|min:0|max:10',
            'knowledge_of_work' => 'required|integer|min:0|max:10',
            'team_work' => 'required|integer|min:0|max:10',
            'reliability_visibility' => 'required|integer|min:0|max:10',
            'productivity' => 'required|integer|min:0|max:10',
            'discipline' => 'required|integer|min:0|max:10',
            'quality_of_work' => 'required|integer|min:0|max:10',
            'communication' => 'required|integer|min:0|max:10',
            'total_score' => 'required|integer',
            'percentage' => 'required|numeric',
            'leadership' => 'nullable|numeric|min:0|max:10',

        ]);

        // Assuming you have a model named EmployeePerformance
        $performance = PerformanceEvaluation::find($id);
        if (!$performance) {
            return response()->json(['message' => 'Performance evaluation not found'], 404);
        }
        $performance->update($validatedData);

        return response()->json(['message' => 'Performance evaluation updated successfully'], 200);
    }

    // delete monthly performance evaluation
    public function destroy($id)
    {
        $performance = PerformanceEvaluation::find($id);
        $performance->delete();

        return response()->json(['message' => 'Performance evaluation deleted successfully'], 200);
    }



    // public function index(Request $request)
    // {
    //     Log::info('Fetching performance evaluations', ['user_id' => $request->user()->id]);

    //     $user = $request->user();
    //     $roles = $user->getRoleNames(); // Get roles as a collection

    //     Log::info('Fetching performance evaluations', [
    //         'user_id' => $user->id,
    //         'roles' => $roles
    //     ]);

    //     // Initialize $evaluations as null
    //     $evaluations = null;

    //     switch (true) {
    //         case $user->is_hr:
    //             Log::info('Role is HR');
    //             $evaluations = PerformanceEvaluation::with('user')->get();
    //             break;

    //         case ($user->designation_id == 1):
    //             Log::info('Role is Manager');
    //             // fetch only the managerDepartments


    //             //    $evaluations = PerformanceEvaluation::with('user.department')->get();

    //             $departmentIds = $user->managerDepartments->pluck('id');

    //             if ($departmentIds->isNotEmpty()) {
    //                 // Get all users in these departments
    //                 $userIds = User::whereIn('department_id', $departmentIds)->pluck('id');
    //                 // inlcude users of same unit if the manager is does not have any managerDerpartment

    //                 Log::info('Manager oversees users', ['user_ids' => $userIds]);

    //                 // Fetch evaluations only for users in these departments
    //                 $evaluations = PerformanceEvaluation::whereIn('user_id', $userIds)->with('user.department')->get();
    //             } else {
    //                 Log::warning('Manager has no assigned departments');
    //                 return response()->json(['message' => 'Unauthorized - No departments assigned'], 403);
    //             }

    //             break;

    //         case $user->is_hod:
    //             Log::info('Role is HOD');
    //             $departmentIds = $user->hodDepartments->pluck('id');

    //             $userIds = User::whereIn('department_id', $departmentIds)->pluck('id');

    //             // return response()->json($departments);
    //             if ($departmentIds->isNotEmpty()) {
    //                 // evaluations of user with department that exist in $departments = $user->hodDepartments->pluck('id');
    //                 // $evaluations = PerformanceEvaluation::with('user')->get();
    //                 $evaluations = PerformanceEvaluation::whereIn('user_id', $userIds)->with('user.department')->get();


    //             } else {
    //                 Log::warning('HOD has no assigned departments');
    //                 return response()->json(['message' => 'Unauthorized - No departments assigned'], 403);
    //             }
    //             break;

    //         default:
    //             // Default case: authenticated user who is not manager, HR, or HODq
    //             $evaluations = PerformanceEvaluation::where('user_id', $user->id)->with('user')->get();
    //             break;
    //     }

    //     Log::info('Performance evaluations fetched successfully', ['count' => $evaluations->count()]);
    //     return response()->json(['evaluations' => $evaluations]);
    // }




    public function index(Request $request)
    {
        Log::info('Fetching performance evaluations', ['user_id' => $request->user()->id]);

        $user = $request->user();
        $roles = $user->getRoleNames();

        Log::info('User roles', [
            'user_id' => $user->id,
            'roles' => $roles
        ]);

        $evaluations = collect(); // default empty collection

        switch (true) {
            case $user->is_hr:
                Log::info('Role: HR');

                // Fetch evaluations with relationships for better frontend rendering
                $evaluations = PerformanceEvaluation::with('user.department')->get();

                Log::info('Evaluations fetched', ['count' => $evaluations->count()]);
                break;

            case ($user->designation_id == 1): // Manager
                Log::info('Role: Manager');
                $departmentIds = $user->managerDepartments->pluck('id');

                if ($departmentIds->isNotEmpty()) {
                    $userIds = User::whereIn('department_id', $departmentIds)->pluck('id');
                    Log::info('Manager oversees users', ['user_ids' => $userIds]);
                } else {
                    // Fallback to users in the same unit
                    Log::warning('Manager has no departments, using unit fallback');
                    $userIds = User::where('unit_id', $user->unit_id)
                        ->where('id', '!=', $user->id)
                        ->pluck('id');
                }

                $evaluations = PerformanceEvaluation::whereIn('user_id', $userIds)
                    ->with('user.department')
                    ->get();
                break;

            case $user->is_hod:
                Log::info('Role: HOD');
                $hodDeptIds = $user->hodDepartments->pluck('id');

                if ($hodDeptIds->isEmpty()) {
                    Log::warning('HOD has no assigned departments');
                    return response()->json(['message' => 'Unauthorized - No departments assigned'], 403);
                }

                // Include:
                // - Users who manage departments under the HOD
                // - Managers with no departments (designation_id == 1)
                $userIds = User::where(function ($query) use ($hodDeptIds) {
                    $query->whereHas('managerDepartments', function ($q) use ($hodDeptIds) {
                        $q->whereIn('department_id', $hodDeptIds);
                    })
                        ->orWhere(function ($q) {
                            $q->doesntHave('managerDepartments')
                                ->where('designation_id', 1);
                        });
                })->pluck('id');

                Log::info('HOD oversees users', ['user_ids' => $userIds]);

                $evaluations = PerformanceEvaluation::whereIn('user_id', $userIds)
                    ->with('user.department')
                    ->get();
                break;

            default:
                Log::info('Role: Regular User');
                $evaluations = PerformanceEvaluation::where('user_id', $user->id)
                    ->with('user.department')
                    ->get();
                break;
        }

        Log::info('Performance evaluations fetched', ['count' => $evaluations->count()]);
        return response()->json(['evaluations' => $evaluations]);
    }




    public function attendance($userId, $year, $month)
    {
        // 1. Get first and last day of the month
        $startOfMonth = Carbon::createFromDate($year, $month)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month)->endOfMonth();

        // 2. Count total weekdays (Mon-Fri) in the month (working days) please note we work on Saturday too.
        $workingDays = CarbonPeriod::create($startOfMonth, $endOfMonth)
            ->filter(function (Carbon $date) {
                return $date->isWeekday();
            })
            ->count();

        // 3. Count days the user was present in that period
        $presentDays =  Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->where('is_present', 1)
            ->count();

        // 4. Calculate attendance percentage
        $attendancePercentage = $workingDays > 0
            ? round(($presentDays / $workingDays) * 100, 2)
            : 0;

        return [
            'user_id' => $userId,
            'month' => $month,
            'year' => $year,
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'attendance_percentage' => $attendancePercentage,
        ];
    }



    /**
     * Filter performance evaluations based on criteria
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterEvaluations(Request $request)
    {
        Log::info('Filtering performance evaluations', ['request' => $request->all()]);

        try {
            // Start with a base query
            $query = PerformanceEvaluation::with(['user.unit', 'user.department', 'evaluator'])
                ->whereNull('deleted_at');

            // Apply unit filter (supports multiple units)
            if ($request->has('unit_id') && $request->unit_id) {
                Log::info('Applying unit filter', ['unit_id' => $request->unit_id]);
                $userIds = User::whereIn('unit_id', (array) $request->unit_id)->pluck('id');
                Log::debug('User IDs for unit filter', ['user_ids' => $userIds]);
                $query->whereIn('user_id', $userIds);
            }

            // Apply department filter (supports multiple departments)
            if ($request->has('department_id') && $request->department_id) {
                Log::info('Applying department filter', ['department_id' => $request->department_id]);
                $userIds = User::whereIn('department_id', (array) $request->department_id)->pluck('id');
                Log::debug('User IDs for department filter', ['user_ids' => $userIds]);
                $query->whereIn('user_id', $userIds);
            }

            // Apply evaluation date filter
            // if ($request->has('evaluation_date') && $request->evaluation_date) {
            //     Log::info('Applying evaluation date filter', ['evaluation_date' => $request->evaluation_date]);
            //     $query->whereDate('evaluation_date', $request->evaluation_date);
            // }

            // Apply user filter
            if ($request->has('user_id') && $request->user_id) {
                Log::info('Applying user filter', ['user_id' => $request->user_id]);
                $query->where('user_id', $request->user_id);
            }

            // Apply evaluator filter
            if ($request->has('evaluator_id') && $request->evaluator_id) {
                Log::info('Applying evaluator filter', ['evaluator_id' => $request->evaluator_id]);
                $query->where('evaluator_id', $request->evaluator_id);
            }

            // Apply date range filter
            if ($request->has('start_date') && $request->has('end_date')) {
                Log::info('Applying date range filter', [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date
                ]);
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            }

            // Get filtered evaluations
            $evaluations = $query->get();
            Log::info('Filtered evaluations retrieved', ['count' => $evaluations->count()]);

            return response()->json(['evaluations' => $evaluations]);
        } catch (\Exception $e) {
            Log::error('Error filtering evaluations', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'An error occurred while filtering evaluations'], 500);
        }
    }

    /**
     * Get employees for the filter dropdown
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilterOptions()
    {
        Log::info('Fetching filter options for employees, evaluators, and departments');

        // Get list of all employees
        $employees = User::select('id', 'firstname', 'lastname')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'fullName' => $user->firstname . ' ' . $user->lastname
                ];
            });
        Log::info('Employees fetched', ['count' => $employees->count()]);

        // Get list of all evaluators
        $evaluators = User::select('id', 'firstname', 'lastname')
            ->whereHas('evaluatorPerformances')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'fullName' => $user->firstname . ' ' . $user->lastname
                ];
            });
        Log::info('Evaluators fetched', ['count' => $evaluators->count()]);

        // Get departments
        $departments = Department::select('id', 'name')
            ->whereNull('deleted_at')
            ->get();
        Log::info('Departments fetched', ['count' => $departments->count()]);

        return response()->json([
            'status' => 'success',
            'employees' => $employees,
            'evaluators' => $evaluators,
            'departments' => $departments
        ]);
    }

    public function rules()
    {
        return [
            'attendance'         => 'required|integer|min:0|max:10',
            'problems_solved'    => 'required|integer|min:0|max:10',
            'reports_submitted'  => 'required|integer|min:0|max:10',
            'knowledge_of_work'  => 'required|integer|min:0|max:10',
            'team_work'          => 'required|integer|min:0|max:10',
            'reliability_visibility' => 'required|integer|min:0|max:10',
            'productivity'       => 'required|integer|min:0|max:10',
            'discipline'         => 'required|integer|min:0|max:10',
            'quality_of_work'    => 'required|integer|min:0|max:10',
            'communication'      => 'required|integer|min:0|max:10',
            'leadership'         => 'required|integer|min:0|max:10',
        ];
    }

}
