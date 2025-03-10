<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <!-- Logo Section -->
        <div class="logo-container">
            <a href="/dashboard">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Boxleo Logo" class="sidebar-logo" width="150">
            </a>
        </div>
    </div>
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            @can('view_admin_panel')
                <ul>
                    <li>
                        <a href="/dashboard">
                            <i class="la la-dashboard"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#apps-menu"><i
                                class="la la-cube"></i>
                            <span>Structure</span> <span class="menu-arrow"></span></a>
                        <ul id="apps-menu" class="collapse">
                            <li><a href="/branches">Branches</a></li>
                            <li><a href="/offices">Offices</a></li>
                            <li><a href="/departments">Departments</a></li>
                            <li><a href="/designations">Designations</a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#employee-menu"><i
                                class="la la-users"></i>
                            <span>Employees</span> <span class="menu-arrow"></span></a>
                        <ul id="employee-menu" class="collapse">
                            <li><a href="/employees"> Employees </a></li>
                            @can('is_developer')
                                <li><a href="/roles"> Roles </a></li>
                                <li><a href="/permissions"> Permissions </a></li>
                            @endcan
                            <li><a href="/directory">Directory</a></li>
                            <li><a href="/disciplinaries">Disciplinaries</a></li>
                            <li><a href="/policies">Policies</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#attendance-menu">
                            <i class="la la-list"></i>
                            <span> Attendances</span> <span class="menu-arrow"></span></a>
                        <ul id="attendance-menu" class="collapse">
                            <li><a href="/attendances">Attendances</a></li>
                            <li><a href="/biometrics">Biometrics</a></li>
                            <li><a href="/biometric-devices">Biometric Devices</a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#leave-menu"><i
                                class="la la-slack"></i>
                            <span>Leaves</span><span class="menu-arrow"></span></a>
                        <ul id="leave-menu" class="collapse">
                            <li><a href="/leave-requests">Leave Requests</a></li>
                            <li><a href="/leaves">All Leaves</a></li>
                            <li><a href="/leave-balances">Leave Balances</a></li>
                            <li><a href="/leave-types">Leave Types</a></li>
                            <li><a href="/holidays">Holidays</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="/resources"><i class="la la-suitcase"></i> <span>Resources</span></a>
                    </li>

                    <li>
                    <a href="/requisitions"><i class="la la-check-circle"></i> <span>Requisitions</span></a>

                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);" data-toggle="collapse" data-bs-target="#performance"><i
                                class="la la-bar-chart"></i>
                            <span>Performance</span> <span class="menu-arrow"></span></a>
                        <ul id="performance" class="collapse">
                            <li><a href="/telesales-report">Telesales report</a></li>
                            <li><a href="/awards">Awards</a></li>
                            <li><a href="/award-types">Award Types</a></li>
                            <li><a href="/appraisals">Appraisals </a></li>
                            <li><a href="/performance-evaluations">Evaluations </a></li>
                            
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#tickets-complaints"><i
                                class="la la-book"></i> <span>Tickets & Employee Voice</span> <span
                                class="menu-arrow"></span></a>
                        <ul id="tickets-complaints" class="collapse">
                            <li><a href="/tickets">Tickets</a></li>
                            <!-- <li><a href="/voice">Employee Voice</a></li> -->
                            <li><a href="/employee-voice">Employee Voice</a></li>

                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#reports"><i
                                class="la la-calendar"></i>
                            <span>Reports</span> <span class="menu-arrow"></span></a>
                        <ul id="reports" class="collapse">
                            <li><a href="/attendance-report">Attendance report</a></li>
                            <li><a href="/leave-report">Leave report</a></li>

                        </ul>
                    </li>

                    <li>
                        <a href="/announcements"><i class="la la-bell"></i> <span>Announcements</span></a>
                    </li>
                    <li>
                        <a href="/users"><i class="la la-user-plus"></i> <span>Users</span></a>
                    </li>
                    <li>
                        <a href="/settings"><i class="la la-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>

                </ul>
            @endcan
            @can('view_employee_panel')
                <ul class="navigation-menu">
                    <li>
                        <a href="/dashboard"><i class="la la-dashboard"></i> <span>Dashboard</span></a>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#my-account-menu">
                            <i class="la la-user"></i> <span>My Account</span> <span class="menu-arrow"></span>
                        </a>
                        <ul id="my-account-menu" class="collapse">
                            <li><a href="/account">My Account</a></li>
                            <li><a href="/employee-attendance">Attendances</a></li>
                            <!-- <li><a href="/employee-assets">Assets Assigned</a></li> -->
                            <li><a href="/resources">Assets Assigned</a></li>
                            <li><a href="/requisitions">Requisitions</a></li>


                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="#" data-toggle="collapse" data-target="#leave-menu">
                            <i class="la la-calendar"></i> <span>Leaves</span> <span class="menu-arrow"></span>
                        </a>
                        <ul id="leave-menu" class="collapse">
                            <li><a href="/employee-leaves">Leaves</a></li>
                            @can('view_team_leaves')
                                <li><a href="/team-leaves">Approve Leaves</a></li>
                            @endcan
                            <li><a href="/holidays">Holiday Calendar</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="/employee-team"><i class="la la-users"></i> <span>Teams</span></a>
                    </li>

                    <li class="submenu">
                        <a href="#" data-toggle="collapse" data-target="#self-service-menu">
                            <i class="la la-cogs"></i> <span>Self Service</span> <span class="menu-arrow"></span>
                        </a>
                        <ul id="self-service-menu" class="collapse">
                            <li><a href="/employee-attendance">Attendance</a></li>
                            <li><a href="/employee-payslips">Payslips</a></li>
                            <li><a href="/employee-tickets">Tickets</a></li>
                            <li><a href="/employee-loans">Welfare loan</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#tickets-complaints">
                            <i class="la la-book"></i> <span>Tickets & Employee Voice</span> <span
                                class="menu-arrow"></span></a>
                        <ul id="tickets-complaints" class="collapse">
                            <li><a href="/employee-tickets">Tickets</a></li>
                            <li><a href="/employee-voice">Employee Voice</a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);" data-toggle="collapse" data-bs-target="#performance"><i
                                class="la la-bar-chart"></i>
                            <span>Performance</span> <span class="menu-arrow"></span></a>
                        <ul id="performance" class="collapse">
                            <!-- <li><a href="/telesales-report">Telesales report</a></li>
                            <li><a href="/awards">Awards</a></li>
                            <li><a href="/award-types">Award Types</a></li>
                            <li><a href="/appraisals">Appraisals </a></li> -->
                            <li><a href="/performance-evaluations">Evaluations </a></li>
                            
                        </ul>
                    </li>
                    <li>
                        <a href="/employee-settings"><i class="la la-cog"></i> <span>Settings</span></a>
                    </li>
                    <li>
                        <a href="/employee-notifications"><i class="la la-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                    <li>
                        <a href="/employee-timeline"><i class="la la-book"></i> <span>Timeline & Updates</span></a>
                    </li>
                </ul>
            @endcan
        </div>
    </div>
</aside>
