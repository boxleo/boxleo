<?php

use App\Http\Controllers\Api\DeductionsController;
use App\Http\Controllers\Api\EarningsController;
use App\Http\Controllers\Api\UserEarningController;
use App\Http\Controllers\PayrollApiController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\UserDeductionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketApiController;
use App\Http\Controllers\Api\RoleApiController;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\UnitApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\AwardApiController;
use App\Http\Controllers\Api\LeaveApiController;
use App\Http\Controllers\Api\MpesaApiController;
use App\Http\Controllers\Api\OfficeApiController;
use App\Http\Controllers\Api\PolicyApiController;
use App\Http\Controllers\Api\ReportApiController;
use App\Http\Controllers\Api\SalaryApiController;
use App\Http\Controllers\Api\EmployeesPerformance;
use App\Http\Controllers\Api\HolidayApiController;
use App\Http\Controllers\Api\ResourceApiController;
use App\Http\Controllers\Api\AwardTypeApiController;
use App\Http\Controllers\Api\ComplaintApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\LeaveTypeApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\DepartmentApiController;
use App\Http\Controllers\Api\PermissionApiController;
use App\Http\Controllers\Api\DesignationApiController;
use App\Http\Controllers\Api\AnnouncementApiController;
use App\Http\Controllers\Api\DisciplinaryApiController;
use App\Http\Controllers\Api\PerformanceApiEvaluation;
use App\Http\Controllers\Api\RequisitionApiController;
use App\Http\Controllers\PerformanceApiReportController;
use App\Http\Controllers\Api\UserDetailApiController;




Route::post('v1/syncZkteco', [AttendanceApiController::class, 'syncZkteco']);

Route::middleware('auth:sanctum')->group(function () {
  //users
  Route::get('v1/users', [UserApiController::class, 'index']);
  Route::get('v1/users/{user}', [UserApiController::class, 'show']);
  Route::post('v1/filter-users', [UserApiController::class, 'employeesFilter']);
  Route::post('v1/users', [UserApiController::class, 'store']);
  Route::put('v1/users/{id}/toggle-status', [UserApiController::class, 'toggleAccount']);
  Route::delete('v1/users/{user}', [UserApiController::class, 'destroy']);
  Route::put('v1/users/{userId}/switch-role', [UserApiController::class, 'switchRole']);
  Route::put('v1/users/update/{user}', [UserApiController::class, 'update']);
  Route::get('v1/telesale-agents', [UserApiController::class, 'telesaleAgents']);
  Route::get('v1/team', [UserApiController::class, 'getTeam']);

  //   User details
  Route::get('v1/user-details', [UserDetailApiController::class, 'index']);
  Route::post('v1/user-details', [UserDetailApiController::class, 'store']);
  Route::delete('v1/user-details/{user}', [UserDetailApiController::class, 'destroy']);
  Route::put('v1/user-details/{user}', [UserDetailApiController::class, 'update']);
  Route::get('v1/user-details/{id}', [UserDetailApiController::class, 'show']);




  //dashboard
  Route::get('v1/dashboard', [DashboardApiController::class, 'index']);
  Route::get('v1/dashboard/{user}', [DashboardApiController::class, 'userStatistics']);
  Route::get('v1/analytics/{id}', [DashboardApiController::class, 'getUserAnalytics']);

  //branches
  Route::get('v1/branches', [UnitApiController::class, 'index']);
  Route::post('v1/branches', [UnitApiController::class, 'store']);
  Route::put('v1/branches/{unit}', [UnitApiController::class, 'update']);
  Route::delete('v1/branches/{unit}', [UnitApiController::class, 'destroy']);
  Route::get('v1/timezones', [UnitApiController::class, 'timeZones']);
  Route::get('v1/countries', [UnitApiController::class, 'countries']);


  //roles and permissions
  Route::get('v1/permissions', [PermissionApiController::class, 'index']);
  Route::get('v1/permissions/{userId}', [PermissionApiController::class, 'getUserPermissions']);
  Route::put('v1/users/{userId}/update-permissions', [PermissionApiController::class, 'updateUserPermissions']);
  Route::get('v1/roles', [RoleApiController::class, 'index']);
  // Route::post('v1/roles', [RoleApiController::class, 'store']);
  // storePermission
  Route::post('/v1/permissions', [PermissionApiController::class, 'storePermission']);
  Route::delete('v1/permissions/{permissionId}', [PermissionApiController::class, 'destroyPermission']);

  //tasks
  Route::get('v1/tasks', [TaskApiController::class, 'index']);
  Route::post('v1/tasks', [TaskApiController::class, 'store']);

  //offices
  Route::get('v1/offices', [OfficeApiController::class, 'index']);
  Route::post('v1/offices', [OfficeApiController::class, 'store']);
  Route::put('v1/offices/{office}', [OfficeApiController::class, 'update']);
  Route::delete('v1/offices/{office}', [OfficeApiController::class, 'destroy']);

  //departments
  Route::get('v1/departments', [DepartmentApiController::class, 'index']);
  Route::post('v1/departments', [DepartmentApiController::class, 'store']);
  Route::put('v1/departments/{department}', [DepartmentApiController::class, 'update']);
  Route::delete('v1/departments/{department}', [DepartmentApiController::class, 'destroy']);

  //designations
  Route::get('v1/designations', [DesignationApiController::class, 'index']);
  Route::post('v1/designations', [DesignationApiController::class, 'store']);
  Route::put('v1/designations/{designation}', [DesignationApiController::class, 'update']);
  Route::delete('v1/designations/{designation}', [DesignationApiController::class, 'destroy']);

  //disciplinaries
  Route::get('v1/disciplinaries', [DisciplinaryApiController::class, 'index']);
  Route::post('v1/disciplinaries', [DisciplinaryApiController::class, 'store']);
  Route::put('v1/disciplinaries/{disciplinary}', [DisciplinaryApiController::class, 'update']);
  Route::delete('v1/disciplinaries/{disciplinary}', [DisciplinaryApiController::class, 'destroy']);

  //announcements
  //   AnnouncementApiController
  Route::get('v1/announcements', [AnnouncementApiController::class, 'index']);
  Route::post('v1/announcements', [AnnouncementApiController::class, 'store']);
  Route::put('v1/announcements/{announcement}', [AnnouncementApiController::class, 'update']);
  Route::delete('v1/announcements/{announcement}', [AnnouncementApiController::class, 'destroy']);
  Route::post('v1/announcements/{id}/notify', [AnnouncementApiController::class, 'sendNotifications']);
  Route::get('/v1/attachments/{id}/download', [AnnouncementApiController::class, 'downloadAttachment']);

  //attendance
  Route::get('v1/attendances', [AttendanceApiController::class, 'index']);
  Route::get('v1/attendances/{user_id}', [AttendanceApiController::class, 'userAttendance']);
  Route::post('v1/attendances', [AttendanceApiController::class, 'store']);
  Route::put('v1/attendances/{attendance}', [AttendanceApiController::class, 'update']);
  Route::delete('v1/attendances/{attendance}', [AttendanceApiController::class, 'destroy']);
  Route::get('v1/attendance-analytics', [AttendanceApiController::class, 'attendanceAnalytics']);
  Route::put('v1/attendances/update/status', [AttendanceApiController::class, 'updateStatus']);
  Route::get('v1/attendances/{user_id}/logs', [AttendanceApiController::class, 'attendanceLogs']);
  Route::get('v1/user-timezone', [AttendanceApiController::class, 'Usertimezone']);
  // 
  // Route::post('v1/syncZkteco', [AttendanceApiController::class, 'syncZkteco']);

  //leave
  Route::get('v1/leaves', [LeaveApiController::class, 'index']);
  Route::get('v1/leaves/{user_id}', [LeaveApiController::class, 'userLeaves']);
  Route::get('v1/leaves/{leave}/logs', [LeaveApiController::class, 'leaveLogs']);
  Route::post('v1/leaves', [LeaveApiController::class, 'store']);
  Route::get('v1/leave-statistics', [LeaveApiController::class, 'statistics']);
  Route::post('v1/leaves/print', [LeaveApiController::class, 'leaveForm']);
  Route::get('v1/leave-balances', [LeaveApiController::class, 'leaveBalances']);
  Route::post('v1/leave-balances', [LeaveApiController::class, 'createLeaveBalances']);
  Route::delete('v1/leave-balances/{id}', [LeaveApiController::class, 'deteteLeaveBalance']);
  Route::put('v1/leave-balances/{id}', [LeaveApiController::class, 'updateLeaveBalance']);
  Route::post('v1/leaves/generate-excel', [LeaveApiController::class, 'generateExcel']);
  Route::post('v1/leaves/generate-pdf', [LeaveApiController::class, 'generatePdf']);
  Route::put('v1/leaves/{leave}/approve', [LeaveApiController::class, 'approveLeave']);
  Route::put('v1/leaves/{leave}/cancel', [LeaveApiController::class, 'cancelLeave']);
  Route::put('v1/leaves/cancel/{leave}', [LeaveApiController::class, 'userLeaveCancel']);
  Route::post('v1/team-leaves', [LeaveApiController::class, 'teamLeaves']);
  Route::put('v1/leaves/{leave}', [LeaveApiController::class, 'update']);
  Route::delete('v1/leaves/{leave}', [LeaveApiController::class, 'destroy']);
  //  /api/v1/leaves/bulk-approve and /api/v1/leaves/bulk-cancel
  // Route::put('v1/leaves/bulk-approve', [LeaveApiController::class, 'bulkApprove']);
  // Route::put('v1/leaves/bulk-cancel', [LeaveApiController::class, 'bulkCancel']);

  Route::put('v1/leaves-bulk-approve', [LeaveApiController::class, 'bulkApprove'])->name('leaves.bulk.approve');
  Route::put('v1/leaves-bulk-cancel', [LeaveApiController::class, 'bulkCancel'])->name('leaves.bulk.cancel');

  Route::get('v1/holidays', [HolidayApiController::class, 'index']);
  Route::post('v1/holidays', [HolidayApiController::class, 'store']);
  Route::put('v1/holidays/{id}', [HolidayApiController::class, 'update']);
  Route::delete('v1/holidays/{id}', [HolidayApiController::class, 'destroy']);

  //leave types
  Route::get('v1/leave-types', [LeaveTypeApiController::class, 'index']);
  Route::post('v1/leave-types', [LeaveTypeApiController::class, 'store']);
  Route::delete('v1/leave-types/{leaveType}', [LeaveTypeApiController::class, 'destroy']);

  //policies
  Route::get('v1/policies', [PolicyApiController::class, 'index']);
  Route::post('v1/policies', [PolicyApiController::class, 'store']);
  Route::put('v1/policies/{policy}', [PolicyApiController::class, 'update']);
  Route::delete('v1/policies/{policy}', [PolicyApiController::class, 'destroy']);

  //salaries
  Route::get('v1/salaries', [SalaryApiController::class, 'index']);
  Route::post('v1/salaries', [SalaryApiController::class, 'store']);
  Route::put('v1/salaries/{salary}', [SalaryApiController::class, 'update']);
  Route::delete('v1/salaries/{salary}', [SalaryApiController::class, 'destroy']);

  //resources
  Route::get('/v1/resources', [ResourceApiController::class, 'index']);
  Route::post('/v1/resources', [ResourceApiController::class, 'store']);
  Route::put('/v1/resources/{asset}', [ResourceApiController::class, 'update']);
  Route::delete('/v1/resources/{asset}', [ResourceApiController::class, 'destroy']);
  Route::post('/v1/resources/{asset}/clear', [ResourceApiController::class, 'clearAsset']);
  Route::put('/v1/resources/{asset}/reassign', [ResourceApiController::class, 'reassignAsset']);
  Route::get('v1/resource-logs/{id}', [ResourceApiController::class, 'resourceLogs']);
  Route::post('v1/resources/import', [ResourceApiController::class, 'upload']);



  //tickets
  Route::get('/v1/tickets', [TicketApiController::class, 'index']);
  Route::post('/v1/tickets', [TicketApiController::class, 'store']);
  Route::put('/v1/tickets/{ticket}', [TicketApiController::class, 'update']);
  Route::delete('/v1/tickets/{ticket}', [TicketApiController::class, 'destroy']);
  Route::put('/v1/tickets/{ticket}/assign', [TicketApiController::class, 'assignUser']);
  Route::put('/v1/tickets/{ticket}/close', [TicketApiController::class, 'markAsClosed']);
  Route::post('/v1/tickets/{ticket}/comments', [TicketApiController::class, 'addComment']);
  Route::get('/v1/ticket-comments/{ticket}', [TicketApiController::class, 'ticketComments']);

  //complaints
  Route::get('/v1/voices', [ComplaintApiController::class, 'index']);
  Route::post('/v1/voices', [ComplaintApiController::class, 'store']);
  Route::put('/v1/voices/{voice}', [ComplaintApiController::class, 'update']);
  Route::delete('/v1/voices/{voice}', [ComplaintApiController::class, 'destroy']);
  Route::get('/v1/voices/{id}', [ComplaintApiController::class, 'voiceLogs']);

  //reports
  Route::post('v1/attendance-report', [ReportApiController::class, 'attendanceReport']);
  Route::post('v1/leave-report', [ReportApiController::class, 'leaveReport']);
  Route::post('v1/attendance-report/excel', [ReportApiController::class, 'attendanceExcelReport']);
  Route::post('v1/leave-report/excel', [ReportApiController::class, 'leaveExcelReport']);
  Route::get('v1/agent-performance', [EmployeesPerformance::class, 'agentPerformance']);
  Route::get('v1/asset-report', [ReportApiController::class, 'assetReport']);
  // Route::post('v1/asset-report', [ReportApiController::class, 'assetReport']);


  //Awards
  Route::get('v1/awards', [AwardApiController::class, 'index']);
  Route::post('v1/awards', [AwardApiController::class, 'store']);
  Route::put('v1/awards/{award}', [AwardApiController::class, 'update']);
  Route::delete('v1/awards/{award}', [AwardApiController::class, 'destroy']);

  //Award Types
  Route::get('v1/award-types', [AwardTypeApiController::class, 'index']);
  Route::post('v1/award-types', [AwardTypeApiController::class, 'store']);
  Route::put('v1/award-types/{awardType}', [AwardTypeApiController::class, 'update']);
  Route::delete('v1/award-types/{awardType}', [AwardTypeApiController::class, 'destroy']);
});

Route::post('/v1/test-sms', [LeaveApiController::class, 'testSms']);


Route::post('v1/confirmation', [MpesaApiController::class, 'confirmation']);
Route::post('v1/validation', [MpesaApiController::class, 'validation']);
Route::get('v1/register-url', [MpesaApiController::class, 'registerUrl']);
Route::get('v1/stk-push', [MpesaApiController::class, 'initiateSTKPush']);
Route::get('v1/simulate', [MpesaApiController::class, 'simulate']);




Route::get('/v1/server-time', [AttendanceApiController::class, 'fetchServerTime']);

// v1/requisitions
Route::get('v1/requisitions', [RequisitionApiController::class, 'index']);
Route::post('v1/requisitions', [RequisitionApiController::class, 'store']);
Route::post('v1/approve-requisition', [RequisitionApiController::class, 'approveRequisition']);
Route::post('v1/cancel-requisition/{id}', [RequisitionApiController::class, 'cancelRequisition']);
Route::put('v1/update/{id}', [RequisitionApiController::class, 'update']);
Route::delete('v1/delete-requisition/{id}', [RequisitionApiController::class, 'deleteRequisition']);

Route::get('v1/requisition/{id}', [RequisitionApiController::class, 'show']);

Route::get('v1/requisitions-logs/{id}', [RequisitionApiController::class, 'requisitionLogs']);
Route::get('v1/requisitions/{id}/pdf', [RequisitionApiController::class, 'generatePdf']);
Route::post('v1/filter-requisitions', [RequisitionApiController::class, 'filter']);
Route::post('/v1/download-requisitions-report', [RequisitionApiController::class, 'downloadRequisitionsReport']);
Route::post('/v1/accounts', [RequisitionApiController::class, 'saveAccount']);
Route::get('/v1/accounts', [RequisitionApiController::class, 'fetchAccounts']);




// performance evalution
Route::get('v1/performance-evaluations', [PerformanceApiEvaluation::class, 'index']);
Route::post('v1/performance-evaluations', [PerformanceApiEvaluation::class, 'store']);
Route::put('v1/performance-evaluations/{id}', [PerformanceApiEvaluation::class, 'update']);
Route::delete('v1/performance-evaluations/{id}', [PerformanceApiEvaluation::class, 'destroy']);
Route::get('v1/performance-evaluations/filter', [PerformanceApiEvaluation::class, 'filterEvaluations']);
Route::get('v1/performance-evaluations/filter-options', [PerformanceApiEvaluation::class, 'getFilterOptions']);


// Performance report routes
Route::post('v1/performance-reports/export', [PerformanceApiReportController::class, 'exportPerformanceEvaluations']);
Route::post('v1/performance-reports/ranked-employees', [PerformanceApiReportController::class, 'exportRankedEmployees']);


Route::get('v1/payslips/{id}/with-user', [PayrollController::class, 'payslipWithUser']);
Route::get('v1/generate-payslip', [PayrollApiController::class, 'generatePayslip']);



// Earnings Routes
Route::prefix('v1/earnings')->group(function () {
  // Standard CRUD routes
  Route::get('/', [EarningsController::class, 'index'])->name('earnings.index');
  Route::post('/', [EarningsController::class, 'store'])->name('earnings.store');
  Route::get('/earning}', [EarningsController::class, 'show'])->name('earnings.show');
  Route::put('/{earning}', [EarningsController::class, 'update'])->name('earnings.update');
  Route::patch('/{earning}', [EarningsController::class, 'update'])->name('earnings.patch');
  Route::delete('/{earning}', [EarningsController::class, 'destroy'])->name('earnings.destroy');

  // Additional filter routes
  Route::get('/filter/active', [EarningsController::class, 'active'])->name('earnings.active');
  Route::get('/filter/type/{type}', [EarningsController::class, 'getByType'])->name('earnings.by-type');
  Route::patch('/{earning}/toggle-status', [EarningsController::class, 'toggleStatus'])->name('earnings.toggle-status');



  Route::post('/user-earnings', [UserEarningController::class, 'store']);
  Route::put('/user-earnings/{userEarning}', [UserEarningController::class, 'update']);

});



// Deductions Routes
Route::prefix('v1/deductions')->group(function () {
  // Standard CRUD routes
  Route::get('/', [DeductionsController::class, 'index'])->name('deductions.index');
  Route::post('/', [DeductionsController::class, 'store'])->name('deductions.store');
  Route::get('/{deduction}', [DeductionsController::class, 'show'])->name('deductions.show');
  Route::put('/{deduction}', [DeductionsController::class, 'update'])->name('deductions.update');
  Route::patch('/{deduction}', [DeductionsController::class, 'update'])->name('deductions.patch');
  Route::delete('/{deduction}', [DeductionsController::class, 'destroy'])->name('deductions.destroy');

  // Additional filter routes
  Route::get('/filter/active', [DeductionsController::class, 'active'])->name('deductions.active');
  Route::get('/filter/mandatory', [DeductionsController::class, 'mandatory'])->name('deductions.mandatory');
  Route::get('/filter/optional', [DeductionsController::class, 'optional'])->name('deductions.optional');
  Route::get('/filter/tax-deductible', [DeductionsController::class, 'taxDeductible'])->name('deductions.tax-deductible');
  Route::get('/filter/type/{type}', [DeductionsController::class, 'getByType'])->name('deductions.by-type');
  Route::patch('/{deduction}/toggle-status', [DeductionsController::class, 'toggleStatus'])->name('deductions.toggle-status');


  Route::post('/user-deductions', [UserDeductionController::class, 'store']);
  Route::put('/user-deductions/{userDeduction}', [UserDeductionController::class, 'update']);

});





