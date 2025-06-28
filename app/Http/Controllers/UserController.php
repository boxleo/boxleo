<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
  private function getUser()
  {
    return auth()->user();
  }

  public function index()
  {

    $user = auth()->user();
    // Retrieve role names and permissions
    $roles = $user->getRoleNames(); // Returns a collection of role names
    $permissions = $user->getAllPermissions()->pluck('name'); // Returns a collection of permission names

    return view(
      'users.index',


      [
        'user' => $user,
        'roles' => $roles,
        'permissions' => $permissions
      ]
    );
  }

  public function directory()
  {
    return view('users.directory');
  }

  public function employeeTeam()
  {
    return view('employee.teams.index', ['user' => $this->getUser()]);
  }

  public function timeline()
  {
    return view('timeline.index', ['user' => $this->getUser()]);
  }

  public function employeeSettings()
  {
    return view('employee.settings.index', ['user' => $this->getUser()]);
  }

  public function biometrics()
  {
    return view('users.biometrics');
  }

}
