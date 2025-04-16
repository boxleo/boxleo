import './bootstrap'
import { createApp } from 'vue'
import ToastrPlugin from './toastr-plugin'
import VCalendar from 'v-calendar'
import 'v-calendar/style.css'

// Vuetify
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({
  components,
  directives,
})

const app = createApp({})

app.use(VCalendar, {})
app.use(vuetify)
app.use(ToastrPlugin)

//import components
import AdminDashboard from './components/dashboards/AdminDashboard.vue'
import EmployeeDashboard from './components/dashboards/EmployeeDashboard.vue'
import Announcements from './components/announcements/Announcements.vue'
import Employees from './components/employees/Employees.vue'
import Directory from './components/employees/Directory.vue'
import EmployeeAccount from './components/employee/EmployeeAccount.vue'
import EmployeeSettings from './components/employee/EmployeeSettings.vue'
import Units from './components/units/Units.vue'
import Offices from './components/offices/Offices.vue'
import Departments from './components/departments/Departments.vue'
import Designations from './components/designations/Designations.vue'
import Attendances from './components/attendances/Attendances.vue'
import AttendanceAnalytics from './components/attendances/AttendanceAnalytics.vue'
import AttendanceReport from './components/reports/AttendanceReport.vue'
import Overtime from './components/attendances/Overtime.vue'
import EmployeeAttendance from './components/employee/EmployeeAttendance.vue'
import EmployeeTeam from './components/employee/EmployeeTeam.vue'
import EmployeeTickets from './components/employee/EmployeeTickets.vue'
import EmployeeComplaints from './components/employee/EmployeeComplaints.vue'
import EmployeeLoans from './components/employee/EmployeeLoans.vue'
import EmployeeNotifications from './components/employee/EmployeeNotifications.vue'
import EmployeeTimeline from './components/employee/EmployeeTimeline.vue'
import Leaves from './components/leaves/Leaves.vue'
import LeaveRequests from './components/leaves/LeaveRequests.vue'
import LeaveStatistics from './components/leaves/LeaveStatistics.vue'
import TeamLeaves from './components/leaves/TeamLeaves.vue'
import LeaveAssign from './components/leaves/LeaveAssign.vue'
import LeaveBalances from './components/leaves/LeaveBalances.vue'
import LeaveTypes from './components/leaves/LeaveTypes.vue'
import Holidays from './components/holidays/Holidays.vue'
import EmployeeLeaves from './components/leaves/EmployeeLeaves.vue'
import Policies from './components/policies/Policies.vue'
import Resources from './components/resources/Resources.vue'
import Payrolls from './components/payrolls/Payrolls.vue'
import Tasks from './components/tasks/Tasks.vue'
import Disciplinary from './components/disciplinaries/Disciplinary.vue'
import Settings from './components/settings/Settings.vue'
import TelesalesReport from './components/performance/TelesalesReport.vue'
import LeaveReport from './components/reports/LeaveReport.vue'
import Awards from './components/performance/Awards.vue'
import AwardTypes from './components/performance/AwardTypes.vue'
import Appraisals from './components/performance/Appraisals.vue'
import Tickets from './components/tickets/Tickets.vue'
// import Complaints from './components/complaints/Complaints.vue'
import Requisition from './components/requisitions/Requisition.vue'
import Permissions from './components/permissions/Permissions.vue'
import Roles from './components/roles/Roles.vue'
import PerformanceEvaluation from './components/performance/PerformanceEvaluation.vue'





//register components
app.component('admin-dashboard', AdminDashboard)
app.component('employee-dashboard', EmployeeDashboard)
app.component('announcements', Announcements)
app.component('employees', Employees)
app.component('directory', Directory)
app.component('units', Units)
app.component('offices', Offices)
app.component('departments', Departments)
app.component('designations', Designations)
app.component('policies', Policies)
app.component('leave-types', LeaveTypes)
app.component('employee-leaves', EmployeeLeaves)
app.component('attendances', Attendances)
app.component('attendance-analytics', AttendanceAnalytics)
app.component('attendance-report', AttendanceReport)
app.component('employee-attendance', EmployeeAttendance)
app.component('employee-team', EmployeeTeam)
app.component('employee-tickets', EmployeeTickets)
app.component('employee-complaints', EmployeeComplaints)
app.component('employee-loans', EmployeeLoans)
app.component('employee-account', EmployeeAccount)
app.component('employee-settings', EmployeeSettings)
app.component('employee-notifications', EmployeeNotifications)
app.component('employee-timeline', EmployeeTimeline)
app.component('leaves', Leaves)
app.component('leave-requests', LeaveRequests)
app.component('leave-statistics', LeaveStatistics)
app.component('team-leaves', TeamLeaves)
app.component('leave-assign', LeaveAssign)
app.component('leave-balances', LeaveBalances)
app.component('holidays', Holidays)
app.component('payrolls', Payrolls)
app.component('tasks', Tasks)
app.component('resources', Resources)
app.component('disciplinary', Disciplinary)
app.component('settings', Settings)
app.component('telesales-report', TelesalesReport)
app.component('leave-report', LeaveReport)
app.component('awards', Awards)
app.component('award-types', AwardTypes)
app.component('overtime', Overtime)
app.component('tickets', Tickets)
// app.component('complaints', Complaints)
app.component('appraisals', Appraisals)
app.component('requisition',Requisition)
app.component('permissions',Permissions)
app.component('roles',Roles)
app.component('performance-evaluation',PerformanceEvaluation)


// Recruitment Dashboard
import RecruitmentDashboard from './components/recruitment/Dashboard.vue';
app.component('dashboard', RecruitmentDashboard);

// Jobs
import JobList from './components/recruitment/Jobs/JobList.vue';
// import CreateJob from './components/recruitment/Jobs/CreateJob.vue';
app.component('job-list', JobList);
// app.component('create-job', CreateJob);

// Applications
import ApplicationList from './components/recruitment/Applications/ApplicationList.vue';
app.component('application-list', ApplicationList);

// Applicants
import ApplicantList from './components/recruitment/Applicants/ApplicantList.vue';
// import Shortlist from './components/recruitment/Applicants/Shortlist.vue';
app.component('applicant-list', ApplicantList);
// app.component('shortlist', Shortlist);

// Onboarding
// import OnboardingList from './components/recruitment/Onboarding/OnboardingList.vue';
// app.component('onboarding-list', OnboardingList);

// Dashboard Card Partial (optional if you register locally instead)
// import DashboardCard from './components/recruitment/partials/DashboardCard.vue';
// This is usually registered locally inside Dashboard.vue



app.mount('#app')
