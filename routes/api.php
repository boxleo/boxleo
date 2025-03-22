<?php
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
// use App\Http\Controllers\Api\AnnouncementApiController;
use App\Http\Controllers\Api\DisciplinaryApiController;
use App\Http\Controllers\Api\PerformanceApiEvaluation;
use App\Http\Controllers\Api\RequisitionApiController;

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

  //dashboard
  Route::get('v1/dashboard', [DashboardApiController::class, 'index']);
  Route::get('v1/dashboard/{user}', [DashboardApiController::class, 'userStatistics']);
  Route::get('v1/analytics/{id}', [DashboardApiController::class, 'getUserAnalytics']);

  //branches
  Route::get('v1/branches', [UnitApiController::class, 'index']);
  Route::post('v1/branches', [UnitApiController::class, 'store']);
  Route::put('v1/branches/{unit}', [UnitApiController::class, 'update']);
  Route::delete('v1/branches/{unit}', [UnitApiController::class, 'destroy']);

  //roles and permissions
  Route::get('v1/permissions', [PermissionApiController::class, 'index']);
  Route::get('v1/permissions/{userId}', [PermissionApiController::class, 'getUserPermissions']);
  Route::put('v1/users/{userId}/update-permissions', [PermissionApiController::class, 'updateUserPermissions']);
  Route::get('v1/roles', [RoleApiController::class, 'index']);
  // Route::post('v1/roles', [RoleApiController::class, 'store']);
  // storePermission
  Route::post('/v1/permissions' ,[PermissionApiController::class, 'storePermission']);

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
  // Route::get('v1/announcements', [AnnouncementApiController::class, 'index']);
  // Route::post('v1/announcements', [AnnouncementApiController::class, 'store']);
  // Route::put('v1/announcements/{announcement}', [AnnouncementApiController::class, 'update']);
  // Route::delete('v1/announcements/{announcement}', [AnnouncementApiController::class, 'destroy']);

  //attendance
  Route::get('v1/attendances', [AttendanceApiController::class, 'index']);
  Route::get('v1/attendances/{user_id}', [AttendanceApiController::class, 'userAttendance']);
  Route::post('v1/attendances', [AttendanceApiController::class, 'store']);
  Route::put('v1/attendances/{attendance}', [AttendanceApiController::class, 'update']);
  Route::delete('v1/attendances/{attendance}', [AttendanceApiController::class, 'destroy']);
  Route::get('v1/attendance-analytics', [AttendanceApiController::class, 'attendanceAnalytics']);
  Route::put('v1/attendances/update/status', [AttendanceApiController::class, 'updateStatus']);

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

  Route::get('v1/holidays', [HolidayApiController::class, 'index']);
  Route::post('v1/holidays', [HolidayApiController::class, 'store']);
  Route::put('v1/holidays', [HolidayApiController::class, 'update']);
  Route::delete('v1/holidays', [HolidayApiController::class, 'destroy']);

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
// Route::get('v1/requisitions/{id}', [RequisitionApiController::class, 'show']);
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







