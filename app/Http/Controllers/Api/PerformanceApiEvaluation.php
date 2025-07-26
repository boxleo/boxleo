<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\PerformanceEvaluation;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerformanceApiEvaluation extends Controller
{
    /**
     * Store monthly performance evaluation
     */
    public function store(Request $request)
    {
        $rules = $this->rules();

        // Add conditional validation for reports_submitted and leadership
        $rules['reports_submitted'] = ['nullable', 'integer', 'min:0', 'max:10', function ($attribute, $value, $fail) use ($request) {
            $user = User::find($request->input('user_id'));
            if ($user && $user->designation_id == 1 && !is_null($value)) {
                $fail('Reports submitted must be null for Managers.');
            }
            if ($user && $user->designation_id != 1 && is_null($value)) {
                $fail('Reports submitted is required for non-Managers.');
            }
        }];

        $rules['leadership'] = ['nullable', 'integer', 'min:0', 'max:10', function ($attribute, $value, $fail) use ($request) {
            $user = User::find($request->input('user_id'));
            if ($user && $user->designation_id == 1 && is_null($value)) {
                $fail('Leadership is required for Managers.');
            }
            if ($user && $user->designation_id != 1 && !is_null($value)) {
                $fail('Leadership must be null for non-Managers.');
            }
            if ($user && $user->designation_id == 1 && $value === 0) {
                $fail('Leadership cannot be zero for Managers.');
            }
        }];

        $validatedData = $request->validate($rules);

        // Fetch user to verify existence
        $user = User::find($validatedData['user_id']);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Calculate total_score and percentage server-side
        $validatedData['total_score'] = $this->calculateTotalScore($validatedData, $user->designation_id == 1);
        $validatedData['percentage'] = $this->calculatePercentage($validatedData, $user->designation_id == 1);

        $performance = new PerformanceEvaluation($validatedData);
        $performance->evaluation_date = now();
        $performance->evaluator_id = $request->user()->id ?? null;
        $performance->save();

        return response()->json([
            'message' => 'Performance evaluation stored successfully',
            'evaluation' => $performance->load('user', 'evaluator')
        ], 201);
    }

    /**
     * Update monthly performance evaluation
     */
    public function update(Request $request, $id)
    {
        $rules = $this->rules();

        // Add conditional validation for reports_submitted and leadership
        $rules['reports_submitted'] = ['nullable', 'integer', 'min:0', 'max:10', function ($attribute, $value, $fail) use ($request) {
            $user = User::find($request->input('user_id'));
            if ($user && $user->designation_id == 1 && !is_null($value)) {
                $fail('Reports submitted must be null for Managers.');
            }
            if ($user && $user->designation_id != 1 && is_null($value)) {
                $fail('Reports submitted is required for non-Managers.');
            }
        }];

        $rules['leadership'] = ['nullable', 'integer', 'min:0', 'max:10', function ($attribute, $value, $fail) use ($request) {
            $user = User::find($request->input('user_id'));
            if ($user && $user->designation_id == 1 && is_null($value)) {
                $fail('Leadership is required for Managers.');
            }
            if ($user && $user->designation_id != 1 && !is_null($value)) {
                $fail('Leadership must be null for non-Managers.');
            }
            if ($user && $user->designation_id == 1 && $value === 0) {
                $fail('Leadership cannot be zero for Managers.');
            }
        }];

        $validatedData = $request->validate($rules);

        $performance = PerformanceEvaluation::find($id);
        if (!$performance) {
            return response()->json(['message' => 'Performance evaluation not found'], 404);
        }

        // Fetch user to verify existence
        $user = User::find($validatedData['user_id']);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Calculate total_score and percentage server-side
        $validatedData['total_score'] = $this->calculateTotalScore($validatedData, $user->designation_id == 1);
        $validatedData['percentage'] = $this->calculatePercentage($validatedData, $user->designation_id == 1);

        $performance->update($validatedData);

        return response()->json([
            'message' => 'Performance evaluation updated successfully',
            'evaluation' => $performance->load('user', 'evaluator')
        ], 200);
    }

    /**
     * Delete monthly performance evaluation
     */
    public function destroy($id)
    {
        $performance = PerformanceEvaluation::find($id);
        if (!$performance) {
            return response()->json(['message' => 'Performance evaluation not found'], 404);
        }
        $performance->delete();

        return response()->json(['message' => 'Performance evaluation deleted successfully'], 200);
    }

    /**
     * Fetch performance evaluations based on user role
     */
    public function index(Request $request)
    {
        Log::info('Fetching performance evaluations', ['user_id' => $request->user()->id]);

        $user = $request->user();
        $roles = $user->getRoleNames();

        Log::info('User roles', [
            'user_id' => $user->id,
            'roles' => $roles
        ]);

        $evaluations = collect();

        switch (true) {
            case $user->is_hr:
                Log::info('Role: HR');
                $evaluations = PerformanceEvaluation::with(['user.unit', 'user.department', 'evaluator'])->get();
                break;

            case ($user->designation_id == 1):
                Log::info('Role: Manager');
                $departmentIds = $user->managerDepartments->pluck('id');

                if ($departmentIds->isNotEmpty()) {
                    $userIds = User::whereIn('department_id', $departmentIds)->pluck('id');
                    Log::info('Manager oversees users', ['user_ids' => $userIds]);
                } else {
                    Log::warning('Manager has no departments, using unit fallback');
                    $userIds = User::where('unit_id', $user->unit_id)
                        ->where('id', '!=', $user->id)
                        ->pluck('id');
                }

                $evaluations = PerformanceEvaluation::whereIn('user_id', $userIds)
                    ->with(['user.unit', 'user.department', 'evaluator'])
                    ->get();
                break;

            case $user->is_hod:
                Log::info('Role: HOD');
                $hodDeptIds = $user->hodDepartments->pluck('id');

                if ($hodDeptIds->isEmpty()) {
                    Log::warning('HOD has no assigned departments');
                    return response()->json(['message' => 'Unauthorized - No departments assigned'], 403);
                }

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
                    ->with(['user.unit', 'user.department', 'evaluator'])
                    ->get();
                break;

            default:
                Log::info('Role: Regular User');
                $evaluations = PerformanceEvaluation::where('user_id', $user->id)
                    ->with(['user.unit', 'user.department', 'evaluator'])
                    ->get();
                break;
        }

        Log::info('Performance evaluations fetched', ['count' => $evaluations->count()]);
        return response()->json(['evaluations' => $evaluations]);
    }

    /**
     * Calculate attendance percentage
     */
    public function attendance($userId, $year, $month)
    {
        $startOfMonth = Carbon::createFromDate($year, $month)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month)->endOfMonth();

        $workingDays = CarbonPeriod::create($startOfMonth, $endOfMonth)
            ->filter(function (Carbon $date) {
                return $date->isWeekday();
            })
            ->count();

        $presentDays = Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->where('is_present', 1)
            ->count();

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
     */
    public function filterEvaluations(Request $request)
    {
        Log::info('Filtering performance evaluations', ['request' => $request->all()]);

        try {
            $query = PerformanceEvaluation::with(['user.unit', 'user.department', 'evaluator'])
                ->whereNull('deleted_at');

            if ($request->has('unit_id') && $request->unit_id) {
                Log::info('Applying unit filter', ['unit_id' => $request->unit_id]);
                $userIds = User::whereIn('unit_id', (array) $request->unit_id)->pluck('id');
                Log::debug('User IDs for unit filter', ['user_ids' => $userIds]);
                $query->whereIn('user_id', $userIds);
            }

            if ($request->has('department_id') && $request->department_id) {
                Log::info('Applying department filter', ['department_id' => $request->department_id]);
                $userIds = User::whereIn('department_id', (array) $request->department_id)->pluck('id');
                Log::debug('User IDs for department filter', ['user_ids' => $userIds]);
                $query->whereIn('user_id', $userIds);
            }

            if ($request->has('user_id') && $request->user_id) {
                Log::info('Applying user filter', ['user_id' => $request->user_id]);
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('evaluator_id') && $request->evaluator_id) {
                Log::info('Applying evaluator filter', ['evaluator_id' => $request->evaluator_id]);
                $query->where('evaluator_id', $request->evaluator_id);
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                Log::info('Applying date range filter', [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date
                ]);
                $query->whereBetween('evaluation_date', [$request->start_date, $request->end_date]);
            }

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
     */
    public function getFilterOptions()
    {
        Log::info('Fetching filter options for employees, evaluators, units, and departments');

        $employees = User::select('id', 'firstname', 'lastname', 'designation_id')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'fullName' => $user->firstname . ' ' . $user->lastname,
                    'designation_id' => $user->designation_id
                ];
            });
        Log::info('Employees fetched', ['count' => $employees->count()]);

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

        $departments = Department::select('id', 'name')
            ->whereNull('deleted_at')
            ->get();
        Log::info('Departments fetched', ['count' => $departments->count()]);

        $units = Unit::select('id', 'name')
            ->whereNull('deleted_at')
            ->get();
        Log::info('Units fetched', ['count' => $units->count()]);

        return response()->json([
            'status' => 'success',
            'employees' => $employees,
            'evaluators' => $evaluators,
            'departments' => $departments,
            'units' => $units
        ]);
    }

    /**
     * Validation rules for performance evaluation
     */
    protected function rules()
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'unit_id' => 'required|integer|exists:units,id',
            'department_id' => 'required|integer|exists:departments,id',
            'attendance' => 'required|integer|min:0|max:10',
            'problems_solved' => 'required|integer|min:0|max:10',
            'knowledge_of_work' => 'required|integer|min:0|max:10',
            'team_work' => 'required|integer|min:0|max:10',
            'reliability_visibility' => 'required|integer|min:0|max:10',
            'productivity' => 'required|integer|min:0|max:10',
            'discipline' => 'required|integer|min:0|max:10',
            'quality_of_work' => 'required|integer|min:0|max:10',
            'communication' => 'required|integer|min:0|max:10',
            'total_score' => 'nullable|integer',
            'percentage' => 'nullable|numeric',
        ];
    }

    /**
     * Calculate total score based on fields
     */
    protected function calculateTotalScore($data, $isManager)
    {
        $fields = [
            'attendance',
            'problems_solved',
            'knowledge_of_work',
            'team_work',
            'reliability_visibility',
            'productivity',
            'discipline',
            'quality_of_work',
            'communication'
        ];

        if (!$isManager) {
            $fields[] = 'reports_submitted';
        } else {
            $fields[] = 'leadership';
        }

        $total = 0;
        foreach ($fields as $field) {
            $total += isset($data[$field]) && is_numeric($data[$field]) ? (int)$data[$field] : 0;
        }

        return $total;
    }

    /**
     * Calculate percentage based on total score
     */
    protected function calculatePercentage($data, $isManager)
    {
        $fields = [
            'attendance',
            'problems_solved',
            'knowledge_of_work',
            'team_work',
            'reliability_visibility',
            'productivity',
            'discipline',
            'quality_of_work',
            'communication'
        ];

        if (!$isManager) {
            $fields[] = 'reports_submitted';
        } else {
            $fields[] = 'leadership';
        }

        $validFields = 0;
        $total = 0;
        foreach ($fields as $field) {
            if (isset($data[$field]) && is_numeric($data[$field])) {
                $total += (int)$data[$field];
                $validFields++;
            }
        }

        $maxPossibleScore = $validFields * 10;
        return $maxPossibleScore > 0 ? round(($total / $maxPossibleScore) * 100, 2) : 0;
    }
}
