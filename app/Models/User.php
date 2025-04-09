<?php

namespace App\Models;

use App\Models\Salary;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

use Octopy\Impersonate\Concerns\HasImpersonation;



class User extends Authenticatable
{
  use HasApiTokens, SoftDeletes, Notifiable, HasRoles, HasPermissions, HasPermissions, HasImpersonation;


  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */

  protected $fillable = [
    'firstname',
    'lastname',
    'email',
    'phone',
    'password',
    'employment_date',
    'staffID',
    'gender',
    'date_of_birth',
    'national_id',
    'is_enabled',
    'department_id',
    'designation_id',
    'unit_id',
    'super_admin',
    'office_id',
    'avatar',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
  ];

  public function unit()
  {
    return $this->belongsTo(Unit::class, 'unit_id');
  }

  public function office()
  {
    return $this->belongsTo(Office::class);
  }

  public function designation()
  {
    return $this->belongsTo(Designation::class);
  }

  public function salary()
  {
    return $this->hasOne(Salary::class);
  }

  public function user_detail()
  {
    return $this->hasOne(UserDetail::class);
  }

  public function leaveBalances()
  {
    return $this->hasMany(LeaveBalance::class);
  }

  public function attendances()
  {

    return $this->hasMany(Attendance::class);
  }

  public function complaint()
  {
    return $this->hasMany(Complaint::class);
  }


  public function complaints()
  {
      return $this->belongsToMany(Complaint::class, 'complaint_user')->withPivot('role')->withTimestamps();
  }

  public function tickets()
  {
    return $this->hasMany(Ticket::class);
  }

  public function ticketComments()
  {
    return $this->hasMany(TicketComment::class);
  }

  public function trainings()
  {
    return $this->belongsToMany(Training::class, 'training_user', 'user_id', 'training_id')
      ->withTimestamps();
  }

  public function disciplinaries()
  {
    return $this->hasMany(Disciplinary::class, 'user_id');
  }

  public function department()
  {
    return $this->belongsTo(Department::class);
  }

  public function departments()
  {
      return $this->belongsToMany(Department::class, 'hod_departments');
  }

  public function leaves()
  {
    return $this->hasMany(Leave::class);
  }

  public function calculateLeaveTaken($leaveName)
  {
    $leaveTaken = 0;

    // Retrieve leave balance for the specified leave type
    $leaveBalance = $this->leaveBalances()->whereHas('leaveType', function ($query) use ($leaveName) {
      $query->where(\Illuminate\Support\Facades\DB::raw('LOWER(name)'), 'LIKE', '%' . strtolower($leaveName) . '%');
    })->first();

    // If leave balance is found, calculate leave taken
    if ($leaveBalance) {
      $leaveType = $leaveBalance->leaveType;
      $leaveTaken = $leaveType->days - $leaveBalance->balance;
      $leaveTaken = max($leaveTaken, 0); // Ensure leave taken is not negative
    }

    return $leaveTaken;
  }

  public function activityLogs()
  {
    return $this->hasMany(ActivityLog::class);
  }

  /**
   * Get the display text for impersonation.
   *
   * @return string
   */
  public function getImpersonateDisplayText(): string
  {
    return $this->firstname; // Adjust this to the attribute you want to display
  }

  /**
   * Get the fields used for impersonation search.
   *
   * @return array
   */
  public function getImpersonateSearchField(): array
  {
    return ['name', 'email']; // Adjust these fields as needed
  }

  public function canImpersonate($target): bool
  {
    // Define logic to determine if the user can impersonate the target
    return $this->hasRole('admin'); // Example condition
  }

  public function canBeImpersonated(): bool
  {
    // Define logic to determine if the user can be impersonated
    return !$this->hasRole('admin'); // Example condition
  }


   // Many-to-Many relationship for HODs
   public function hodDepartments()
   {
       return $this->belongsToMany(Department::class, 'hod_departments', 'user_id', 'department_id');
   }


   public function managerDepartments()
   {
       return $this->belongsToMany(Department::class, 'manager_departments', 'user_id', 'department_id');
   }


  //  public function fullName()
  //  {
  //      return "{$this->firstname} {$this->lastname}";
  //  }



   // Many-to-Many: Complaints where the user is addressed
   public function addressedComplaints()
   {
       return $this->belongsToMany(Complaint::class, 'complaint_user')
                   ->wherePivot('role', 'addressed_to');
   }

   // Many-to-Many: Complaints where the user is a follower
   public function followedComplaints()
   {
       return $this->belongsToMany(Complaint::class, 'complaint_user')
                   ->wherePivot('role', 'follower');
   }

}
