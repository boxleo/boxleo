<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PerformanceEvaluation;
use App\Models\User;
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
            // 'department_id' => 'required|integer',
            // 'evaluation_date' => 'required|date',
            'attendance' => 'required|integer',
            'problems_solved' => 'required|integer',
            'reports_submitted' => 'required|integer',
            'knowledge_of_work' => 'required|integer',
            'team_work' => 'required|integer',
            'reliability_visibility' => 'required|integer',
            'productivity' => 'required|integer',
            'discipline' => 'required|integer',
            'quality_of_work' => 'required|integer',
            'communication' => 'required|integer',
            'total_score' => 'required|integer',
            'percentage' => 'required|numeric',
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
            // 'department_id' => 'required|integer',
            // 'evaluation_date' => 'required|date',
            'attendance' => 'required|integer',
            'problems_solved' => 'required|integer',
            'reports_submitted' => 'required|integer',
            'knowledge_of_work' => 'required|integer',
            'team_work' => 'required|integer',
            'reliability_visibility' => 'required|integer',
            'productivity' => 'required|integer',
            'discipline' => 'required|integer',
            'quality_of_work' => 'required|integer',
            'communication' => 'required|integer',
            'total_score' => 'required|integer',
            'percentage' => 'required|numeric',
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

    //     //  Initialize $evaluations to avoid 'Undefined variable' error
    //     $evaluations = PerformanceEvaluation::where('user_id', $user->id)->with('user')->get(); // Default: own evaluations

    //     if ($user->is_hr) {
    //         Log::info('Role is HR');
    //         $evaluations = PerformanceEvaluation::with('user')->get();
    //     } elseif ($user->designation && $user->designation->name === 'manager') {
    //         Log::info('Role is Manager');
    //         $evaluations = PerformanceEvaluation::whereIn('department_id', $user->managerDepartments->pluck('id'))->with('user')->get();
    //     } elseif ($user->is_hod) {
    //         Log::info('Role is HOD');
    //         $departments = $user->hodDepartments->pluck('id');
    //         if ($departments->isNotEmpty()) {
    //             $evaluations = PerformanceEvaluation::whereIn('department_id', $departments)->with('user')->get();
    //         } else {
    //             Log::warning('HOD has no assigned departments');
    //             return response()->json(['message' => 'Unauthorized - No departments assigned'], 403);
    //         }
    //     }

    //     Log::info('Performance evaluations fetched successfully', ['count' => $evaluations->count()]);
    //     return response()->json(['evaluations' => $evaluations]);
    // }



    public function index(Request $request)
    {
        Log::info('Fetching performance evaluations', ['user_id' => $request->user()->id]);

        $user = $request->user();
        $roles = $user->getRoleNames(); // Get roles as a collection

        Log::info('Fetching performance evaluations', [
            'user_id' => $user->id,
            'roles' => $roles
        ]);

        // Initialize $evaluations as null
        $evaluations = null;

        switch (true) {
            case $user->is_hr:
                Log::info('Role is HR');
                $evaluations = PerformanceEvaluation::with('user')->get();
                break;

            case ($user->designation_id == 1):
                Log::info('Role is Manager');
                // fetch only the managerDepartments


                //    $evaluations = PerformanceEvaluation::with('user.department')->get();

                $departmentIds = $user->managerDepartments->pluck('id');

                if ($departmentIds->isNotEmpty()) {
                    // Get all users in these departments
                    $userIds = User::whereIn('department_id', $departmentIds)->pluck('id');

                    Log::info('Manager oversees users', ['user_ids' => $userIds]);

                    // Fetch evaluations only for users in these departments
                    $evaluations = PerformanceEvaluation::whereIn('user_id', $userIds)->with('user.department')->get();
                } else {
                    Log::warning('Manager has no assigned departments');
                    return response()->json(['message' => 'Unauthorized - No departments assigned'], 403);
                }

                break;

            case $user->is_hod:
                Log::info('Role is HOD');
                $departmentIds = $user->hodDepartments->pluck('id');

                $userIds = User::whereIn('department_id', $departmentIds)->pluck('id');

                // return response()->json($departments);
                if ($departmentIds->isNotEmpty()) {
                    // evaluations of user with department that exist in $departments = $user->hodDepartments->pluck('id');
                    // $evaluations = PerformanceEvaluation::with('user')->get();
                    $evaluations = PerformanceEvaluation::whereIn('user_id', $userIds)->with('user.department')->get();

                } else {
                    Log::warning('HOD has no assigned departments');
                    return response()->json(['message' => 'Unauthorized - No departments assigned'], 403);
                }
                break;

            default:
                // Default case: authenticated user who is not manager, HR, or HOD
                $evaluations = PerformanceEvaluation::where('user_id', $user->id)->with('user')->get();
                break;
        }

        Log::info('Performance evaluations fetched successfully', ['count' => $evaluations->count()]);
        return response()->json(['evaluations' => $evaluations]);
    }
}
