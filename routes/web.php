<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DisciplinaryController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaxesController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserAccountController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::group(['middleware' => ['guest']], function () {
    Route::get('register', [RegisterController::class, 'index'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
    Route::get('login', [LoginController::class, 'index'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('forgot-password', [ForgotPasswordController::class, 'index'])->name('forgot-password');
    Route::post('forgot-password', [ForgotPasswordController::class, 'reset']);
    Route::get('/', function () {
        return redirect()->route('login');
    })->name('home');
    // Route::get('/employee-voice', [ComplaintController::class, 'employeeComplaints']);

});

// Admin routes
Route::middleware(['role:admin'])->group(function () {
    Route::get('/holidays', [HolidayController::class, 'index']);
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/branches', [UnitController::class, 'index']);
    Route::get('/offices', [OfficeController::class, 'index']);
    Route::get('/designations', [DesignationController::class, 'index']);
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves');
    Route::get('/leave-requests', [LeaveController::class, 'leaveRequests']);
    Route::get('/leave-balances', [LeaveController::class, 'leaveBalances']);
    Route::get('/leave-types', [LeaveTypeController::class, 'index']);
    Route::get('/policies', [PolicyController::class, 'index']);
    Route::get('/performance', [PolicyController::class, 'index']);
    Route::get('/disciplinaries', [DisciplinaryController::class, 'index']);
    Route::get('/payroll', [PayrollController::class, 'index']);
    Route::get('/salarys', [SalaryController::class, 'index']);
    Route::get('/taxes', [TaxesController::class, 'index']);
    Route::get('/employees/basic-info/{id}', [UserController::class, 'basicInfo']);
    Route::get('/employees/personal-info/{id}', [UserController::class, 'personalInfo']);
    Route::get('/employees/contact-info/{id}', [UserController::class, 'contactInfo']);
    Route::get('/employees/job-info/{id}', [UserController::class, 'jobInfo']);
    Route::get('/employees/salary-info/{id}', [UserController::class, 'salaryInfo']);
    Route::get('/employees/tax-info/{id}', [UserController::class, 'taxInfo']);
    Route::get('/directory', [UserController::class, 'directory']);
    Route::put('/employees/personal-info/{id}', [UserController::class, 'UpdatepersonalInfo'])->name('employee-info.update');
    Route::put('/employees/contact-info/{id}', [UserController::class, 'UpdateContactInfo']);
    Route::put('/employees/job-info/{id}', [UserController::class, 'UpdateJobInfo']);
    Route::put('/employees/salary-info/{id}', [UserController::class, 'UpdateSalaryInfo']);
    Route::get('/employees', [UserController::class, 'index']);
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/voice', [ComplaintController::class, 'index']);
    Route::get('/overtime', [OvertimeController::class, 'index']);
    Route::get('/tasks', [TaskController::class, 'index'])->name('projects');
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs');
    // Route::get('/resources', [ResourceController::class, 'index']);
    Route::get('/trainings', [TrainingController::class, 'index']);
    Route::get('/users', [UserController::class, 'index'])->name("users");
    Route::get('/biometrics', [UserController::class, 'biometrics'])->name("biometrics");
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity');
    Route::get('/clear-activity', [ActivityController::class, 'markAsRead'])->name('clear-all');
    Route::get('/print-payslip/{id}', [PayrollController::class, 'printPayslip']);
    Route::get('/attendance-summary', [AttendanceController::class, 'index'])->name('attendanceSummary');
    Route::get('/attendance-analytics', [AttendanceController::class, 'analytics']);
    Route::get('/leave-summary', [LeaveController::class, 'leaveSummary'])->name('leaveSummary');
    Route::get('/leave-analytics', [LeaveController::class, 'leaveAnalytics'])->name('leaveAnalytics');
    Route::get('/attendance-report', [ReportController::class, 'attendance']);
    Route::get('/leave-report', [ReportController::class, 'leave']);


    Route::get('/telesales-report', [PerformanceController::class, 'telesalesReport']);
    Route::get('/awards', [PerformanceController::class, 'awards']);
    Route::get('/award-types', [PerformanceController::class, 'awardTypes']);
    Route::get('/appraisals', [PerformanceController::class, 'appraisals']);
    // 
    // Route::get('/performance-evaluations', [PerformanceController::class, 'performanceEvaluations'])->name('performance-evaluations');
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/permissions', [PermissionController::class, 'index']);

 
});

// Employee routes
Route::middleware(['role:employee'])->group(function () {
    Route::get('/employee-leaves', [LeaveController::class, 'employeeLeaves']);
    Route::get('/team-leaves', [LeaveController::class, 'teamLeaves']);
    Route::get('/employee-attendance', [AttendanceController::class, 'employeeAttendances']);
    Route::get('/employee-team', [UserController::class, 'employeeTeam']);
    Route::get('/employee-profile', [UserController::class, 'employeeProfile']);
    // Route::get('/employee-voice', [ComplaintController::class, 'employeeComplaints']);
    Route::get('/employee-tickets', [TicketController::class, 'employeeTickets']);
    Route::get('/employee-resources', [ResourceController::class, 'employeeResources']);
    Route::get('/employee-payslips', [PayslipController::class, 'employeePayslips']);
    Route::get('/employee-loans', [LoanController::class, 'employeeLoans'])->name('employeeLoans');
    Route::get('/employee-notifications', [NotificationController::class, 'employeeNofications']);
    Route::get('/employee-settings', [SettingsController::class, 'employeeSettings']);
    Route::get('/employee-timeline', [TimelineController::class, 'employeeTimeline']);

});

// shared auth routes
Route::group(['middleware' => ['auth']], function () {
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/account', [UserAccountController::class, 'index'])->name('profile');
    Route::get('/company-profile', [CompanyController::class, 'companyProfile']);
    Route::get('/company-policies', [CompanyController::class, 'companyPolicies']);
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/timeline', [UserController::class, 'timeline']);
    // requestions
    Route::get('/requisitions', [RequisitionController::class, 'index']);
    Route::get('/resources', [ResourceController::class, 'index']);
    Route::get('/employee-voice', [ComplaintController::class, 'employeeComplaints']);

    // performance
    Route::get('/performance-evaluations', [PerformanceController::class, 'performanceEvaluations'])->name('performance-evaluations');



    // impersonate 
    Route::get('/impersonate/{id}', function ($id) {
        $user = auth()->user();
        $target = User::findOrFail($id);
        
        if ($user->canImpersonate($target)) {
            $user->impersonate($target);
            return redirect('/dashboard'); 
        }

        return abort(403);
    })->name('impersonate.start');

    Route::get('/impersonate/leave', function () {
        auth()->user()->leaveImpersonation();
        return redirect('/dashboard'); 
    })->name('impersonate.leave');


});

// block register route
Route::middleware(['no-register'])->group(function () {
    Auth::routes();
});
