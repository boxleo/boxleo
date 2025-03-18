<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\UserLoginDetailsMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class UserApiController extends Controller
{

  // public function index(Request $request)
  // {
  //   $departmentId = $request->query('department_id');

  //   $query = User::with('department', 'unit', 'office', 'designation', 'roles')
  //     ->orderBy('created_at');

  //   if ($departmentId) {
  //     $query->where('department_id', $departmentId);
  //   }

  //   $users = $query->get();

  //   foreach ($users as $user) {
  //     $userRoles = $user->roles->pluck('name')->toArray();
  //     $primaryRole = !empty($userRoles) ? $userRoles[0] : '';
  //     $user->setAttribute('role', $primaryRole);
  //   }

  //   return response()->json(['users' => $users]);
  // }




  public function index(Request $request)
{
    $departmentId = $request->query('department_id');

    $query = User::with('department', 'unit', 'office', 'designation', 'roles')
        ->orderBy('created_at');

    if ($departmentId) {
        $query->where('department_id', $departmentId);
    }

    $users = $query->get();

    foreach ($users as $user) {
        $userRoles = $user->roles->pluck('name')->toArray();
        $primaryRole = !empty($userRoles) ? $userRoles[0] : '';
        $user->setAttribute('role', $primaryRole);

        // Append the impersonate URL
        if (auth()->user()->canImpersonate($user)) {
            $user->setAttribute('impersonate_url', route('impersonate.start', ['id' => $user->id]));
        } else {
            $user->setAttribute('impersonate_url', null);
        }
    }

    return response()->json(['users' => $users]);
}

  public function show(User $user)
  {
    // Eager load the roles relationship
    $userWithRole = $user->load('roles');

    // Retrieve the roles assigned to the user and pluck them as a property
    $userRoles = $user->roles->pluck('name')->toArray();

    // Set the 'role' property in the user JSON response
    $userWithRole['role'] = $userRoles;

    // Return the user with the role as a property in the JSON response
    return response()->json(['user' => $userWithRole]);
  }

  public function store(Request $request)
  {
    $this->validate($request, [
      'first_name' => 'required|max:100',
      'last_name' => 'required|max:100',
      'email' => 'required|email',
      'unit_id' => 'required',
      'office_id' => 'required',
      'department_id' => 'required',
      'designation_id' => 'required',
      'role' => 'required',
      'gender' => 'required|string|in:Male,Female',
      'avatar' => 'nullable|file|image|mimes:jpg,jpeg,png,gif',
    ]);

    $imageName = null;
    if ($request->hasFile('avatar')) {
      $imageName = time() . '.' . $request->avatar->extension();
      $request->avatar->move(public_path('storage/users'), $imageName);
    }

    $randomPassword = Str::random(8);

    $user = User::create([
      'firstname' => $request->first_name,
      'lastname' => $request->last_name,
      'phone' => $request->phone,
      'email' => $request->email,
      'unit_id' => $request->unit_id,
      'office_id' => $request->office_id,
      'gender' => $request->gender,
      'department_id' => $request->department_id,
      'designation_id' => $request->designation_id,
      'password' => Hash::make($randomPassword),
      'avatar' => $imageName,
      'is_enabled' => true,
    ]);

    $user->user_detail()->create();

    if ($request->has('role')) {
      $user->assignRole($request->role);

      $permissions = [];

      switch ($request->role) {
        case 'admin':
          $permissions = [
            'view_admin_panel',
            'create_resource',
            'edit_resource',
            'delete_resource',
            'view_employee_profile',
            'edit_user',
          ];
          break;
        case 'employee':
          $permissions = [
            'view_employee_panel',
          ];

          if ($request->role == 'employee' && $request->designation_id == 1) {
            array_push($permissions, 'view_team_leaves');

          }
          ;
          break;

        default:
          break;
      }

      $user->syncPermissions($permissions);
    }
    if ($request->has('send_logins') && $request->input('send_logins') === true) {
      Mail::to($user->email)->send(new UserLoginDetailsMail($user, $randomPassword));
    }

    return response()->json(['Success' => 'Employee Created Successifully']);
  }

  public function switchRole(Request $request, $userId)
  {
    $this->validate($request, [
      'role' => 'required',
    ]);

    $user = User::findOrFail($userId);

    // Revoke all roles
    $user->roles()->detach();

    // Assign the new role
    $user->assignRole($request->role);

    // Update permissions based on the new role
    $permissions = [];

    switch ($request->role) {
      case 'admin':
        $permissions = [
          'view_admin_panel',
          'create_resource',
          'edit_resource',
          'delete_resource',
          'view_employee_profile',
          'edit_user',
        ];
        break;
      case 'employee':
        $permissions = [
          'view_employee_panel',
        ];

        if ($user->designation_id == 1) {
          array_push($permissions, 'view_team_leaves');
        }
        break;

      default:
        break;
    }

    $user->syncPermissions($permissions);

    return response()->json(['Success' => 'Role Switched Successfully']);
  }

  public function employeesFilter(Request $request)
  {

    $employee_id = $request->employee_id;
    $unit_id = $request->unit_id;
    $office_id = $request->office_id;
    $department_id = $request->department_id;
    $designation_id = $request->designation_id;
    $phone = $request->phone;

    $employees = User::query();

    if ($employee_id != 'all') {
      $employees->where('id', $employee_id);
    }

    if ($unit_id != 'all') {
      $employees->where('unit_id', $unit_id);
    }

    if ($office_id != 'all') {
      $employees->where('office_id', $office_id);
    }

    if ($department_id != 'all') {
      $employees->where('department_id', $department_id);
    }

    if ($designation_id != 'all') {
      $employees->where('designation_id', $designation_id);
    }

    if ($phone) {
      $employees->where('phone', $phone);
    }

    $employees->with('department', 'designation', 'unit', 'office');

    $filteredEmployees = $employees->get();

    return ($filteredEmployees);
  }

  public function toggleAccount($id)
  {
    try {
      $user = User::findOrFail($id);

      $user->is_enabled = !$user->is_enabled;

      $user->save();

      return response()->json(['message' => 'Account status toggled successfully', 'user' => $user], 200);
    } catch (\Exception $e) {
      Log::error('Error toggling account status: ' . $e->getMessage());

      return response()->json(['message' => 'Internal server error'], 500);
    }
  }

  // public function update(Request $request, User $user)
  // {
  //   $validatedData = $request->validate([
  //     'firstname' => 'required|string',
  //     'lastname' => 'required|string',
  //     'email' => 'required|email',
  //     'phone' => 'required|string',
  //     'unit_id' => 'required|exists:units,id',
  //     'office_id' => 'required|exists:offices,id',
  //     'department_id' => 'required|exists:departments,id',
  //     'designation_id' => 'required|exists:designations,id',
  //     'role' => 'required|string|in:admin,employee',
  //     'gender' => 'required|string|in:Male,Female',
  //   ]);

  //   $user->update($validatedData);

  //   if ($request->has('role')) {
  //     $user->syncPermissions([]);

  //     $permissions = [];

  //     switch ($request->role) {
  //       case 'admin':
  //         $permissions = [
  //           'view_admin_panel',
  //           'create_resource',
  //           'edit_resource',
  //           'delete_resource',
  //           'view_employee_profile',
  //           'edit_user',
  //         ];
  //         break;
  //       case 'employee':
  //         $permissions = [
  //           'view_employee_panel',
  //         ];

  //         if ($user->designation_id == 1) {
  //           $permissions[] = 'view_team_leaves';
  //         }
  //         break;
  //     }
  //     $user->syncPermissions($permissions);
  //   }

  //   $updatedUser = User::with('department', 'unit', 'office', 'designation', 'roles')->find($user->id);

  //   return response()->json(['message' => 'User updated successfully', 'user' => $updatedUser], 200);
  // }





public function update(Request $request, User $user)
{
    Log::info('User update request received', ['user_id' => $user->id, 'request_data' => $request->all()]);

    $validatedData = $request->validate([
        'firstname' => 'required|string',
        'lastname' => 'required|string',
        'email' => 'required|email',
        'phone' => 'required|string',
        'unit_id' => 'required|exists:units,id',
        'office_id' => 'required|exists:offices,id',
        'department_id' => 'required|exists:departments,id',
        'designation_id' => 'required|exists:designations,id',
        'role' => 'required|string|in:admin,employee',
        'gender' => 'required|string|in:Male,Female',
    ]);

    Log::info('User validation passed', ['validated_data' => $validatedData]);

    try {
        $user->update($validatedData);
        Log::info('User updated successfully', ['user_id' => $user->id]);

        if ($request->has('role')) {


          Log::info('Updating role for user', ['user_id' => $user->id, 'new_role' => $request->role]);

    // Sync the role to remove old ones and assign the new one
    $user->syncRoles($request->role);

    Log::info('Role updated successfully', ['user_id' => $user->id, 'current_roles' => $user->roles->pluck('name')]);
            Log::info('Syncing permissions for user', ['user_id' => $user->id, 'role' => $request->role]);

            $user->syncPermissions([]);

            $permissions = [];

            switch ($request->role) {
                case 'admin':
                    $permissions = [
                        'view_admin_panel',
                        'create_resource',
                        'edit_resource',
                        'delete_resource',
                        'view_employee_profile',
                        'edit_user',
                    ];
                    break;
                case 'employee':
                    $permissions = [
                        'view_employee_panel',
                    ];

                    if ($user->designation_id == 1) {
                        $permissions[] = 'view_team_leaves';
                    }
                    break;
            }

            Log::info('Assigning permissions', ['user_id' => $user->id, 'permissions' => $permissions]);

            $user->syncPermissions($permissions);
        }

        $updatedUser = User::with('department', 'unit', 'office', 'designation', 'roles')->find($user->id);

        Log::info('Returning updated user data', ['user_id' => $user->id, 'updated_user' => $updatedUser]);

        return response()->json(['message' => 'User updated successfully', 'user' => $updatedUser], 200);
    } catch (\Exception $e) {
        Log::error('Error updating user', [
            'user_id' => $user->id,
            'error_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json(['message' => 'Error updating user', 'error' => $e->getMessage()], 500);
    }
}



  public function destroy(User $user)
  {
    $user->delete();

    return response()->json(['message' => 'User deleted successfully']);
  }

  public function telesaleAgents()
  {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://app.boxleocourier.com/api/agents',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZTcyMGFiZGRkMDRlMGViNmJkZmVkZTFhNzgwZDc1ODVjNjhmODFhYmM3ZDJiMjE1OTNkNzY2Y2E1MDM1M2E3YTkxODE1YTMzNDE5YTY3MWQiLCJpYXQiOjE3MTkzMTU3MDkuOTM4OTA1LCJuYmYiOjE3MTkzMTU3MDkuOTM4OTA4LCJleHAiOjE3NTA4NTE3MDkuOTM1Nzk1LCJzdWIiOiI2MSIsInNjb3BlcyI6W119.Au5TPz9DIQePm_p5qbfXhfb7BM5wJr8cIKv0vUBJFE9_1RECRAzi6u8bLU9GuEYWvBFAQ-qkrdxKLYXohnHL0MfmGir3-gcd_ECLl61X36jYlmLBBhmGHBBp40NGn0m-xcS4X9Bpt86n32mJAbHBzSudVgvqm_LesbF7Xwipo6aztggW6Se49YcO3S02dlCSQwSsmybj_3v1n-Ycu8rv9X8QfVyfS-XDpEp4-Kr8sS05fLudtCx-_AnCYxIJsEfigYRw0k-XAl4sDmLM4rZb3PiIo0YrCMLeKCQWeN-b_Kr_locHi3c-ZGXYU7-VxrRMFlO84i5wDJ2v3Lm_faoWPpIwPlvjr_FzGBsG4r0jvCq4HIe4qo5n9fJiNUhW4oi3Q-xtJfNmQSxCOyABWBhrvCd2sp68Z27H02wOblDD8nwEEJjLquZ0dMNo5CzgB0Hx6YWW2yzF7hdYrQ3qekSSKeWtRhDr2NTOku1_y5Bl4D3Fq0_J5iAq7tQY_Ff_2E4PvB8-FnlxXyCY-UedvdSNg22sISrGAP3KpPQgKT1qEgqqijLC92TpP0TEjDaI_98ELgWRa_e55qlROJdtLj5-8M2WOMu1VQDeuqsZXGcl6A5c5nzN3Qa5nwHPsnJHs8k9IhMtN-tBB68gLo5dJk3UC1ihGkze9vUSZAn4WXpq_U0',
        'Cookie: XSRF-TOKEN=%3D; boxleo_session=eyJpdiI6InAzcklwU1BTZUJHaUY0dVdYTUhiVGc9PSIsInZhbHVlIjoiRmlnNWxsTjdnYzMrQlNuZFl1ZjJ1TmlXY3hObnB3cGZLbVRLZ3FHN2hjRUFiYlp4NzhTSUlrdGc0cnZwcDlHUytjOHdDZ2J4eE9yM3hLU3NvMkNUcXdXVy9yNkdwQXUwZVg3RjRpeXZCSzA1WHdydHlEeW5mSVJBdWJYRE1LRU0iLCJtYWMiOiI4N2RkYTY3ZWJhNTUzMzQ4MmZiZTRkODJiMGZmNDU5ZDliZGNiYjFhMTI1YTcwNDMwNTJkNjEyZWU4MzQxNDEzIiwidGFnIjoiIn0%3D',
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;

  }

}
