<template>
  <v-container>
    <!-- Welcome Banner -->
    <v-row>
      <v-col cols="12">
        <v-card color="primary" class="text-center mb-4">
          <v-card-text>
            <h2 class="text-h5 text-white mb-0">
              Welcome back, {{ user.firstname }}!
            </h2>
            <p class="text-subtitle-2 text-white mb-0">Employee Self-Service Portal</p>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Dashboard Overview Cards -->
    <v-row>
      <!-- Employee Profile -->
      <v-col cols="12" md="6">
        <v-card class="h-100" elevation="2">
          <v-card-item>
            <v-card-title class="pb-1">
              <v-icon color="primary" class="me-2">mdi-account-circle</v-icon>
              Profile
            </v-card-title>
          </v-card-item>
          
          <v-card-text>
            <v-row align="center">
              <v-col cols="12" sm="auto" class="text-center">
                <v-avatar color="primary" size="100">
                  <v-img
                    :src="user.gender === 'Male' ? '/assets/img/male-avatar.svg' : '/assets/img/female-avatar.png'"
                    cover
                  ></v-img>
                </v-avatar>
              </v-col>
              
              <v-col>
                <h3 class="text-h5 font-weight-bold">{{ user.firstname }} {{ user.lastname }}</h3>
                <div class="text-subtitle-1 d-flex align-center">
                  <v-icon size="small" class="me-2">mdi-account-tie</v-icon>
                  {{ user.designation.name }}
                </div>
                <div class="text-subtitle-2 d-flex align-center">
                  <v-icon size="small" class="me-2">mdi-domain</v-icon>
                  {{ user.department.name }}
                </div>
                <div class="text-subtitle-2 d-flex align-center">
                  <v-icon size="small" class="me-2">mdi-map-marker</v-icon>
                  {{ user.office.name }}, {{ user.unit.name }}
                </div>
              </v-col>
            </v-row>
            
            <v-divider class="my-3"></v-divider>
            
            <div class="d-flex align-center mb-2">
              <v-icon size="small" color="blue" class="me-3">mdi-email</v-icon>
              <span>{{ user.email }}</span>
            </div>
            
            <div class="d-flex align-center mb-2">
              <v-icon size="small" color="green" class="me-3">mdi-phone</v-icon>
              <span>{{ user.phone }}</span>
            </div>
            
            <div class="d-flex align-center">
              <v-icon size="small" color="orange" class="me-3">mdi-calendar-check</v-icon>
              <span>Work Anniversary: {{ user.work_anniversary ?? 'Not Available' }}</span>
            </div>
          </v-card-text>
          
          <v-card-actions>
            <v-btn color="primary" variant="text" block prepend-icon="mdi-account-edit">
              Edit Profile
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>

      <!-- Leave Management -->
      <v-col cols="12" md="6">
        <v-card class="h-100" elevation="2">
          <v-card-item>
            <v-card-title class="pb-1">
              <v-icon color="primary" class="me-2">mdi-calendar-multiple</v-icon>
              Leave Management
            </v-card-title>
          </v-card-item>
          
          <v-card-text>
            <v-row>
              <v-col cols="12" sm="6">
                <v-card variant="outlined" class="mb-2">
                  <v-card-text class="text-center">
                    <div class="text-h5 font-weight-bold text-primary">{{ statistics.annualLeaveDaysAssigned }}</div>
                    <div class="text-caption">Total Days</div>
                  </v-card-text>
                </v-card>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-card variant="outlined" class="mb-2">
                  <v-card-text class="text-center">
                    <div class="text-h5 font-weight-bold text-success">{{ statistics.leaveBalance }}</div>
                    <div class="text-caption">Available Days</div>
                  </v-card-text>
                </v-card>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-card variant="outlined">
                  <v-card-text class="text-center">
                    <div class="text-h5 font-weight-bold text-info">{{ statistics.leaveTaken }}</div>
                    <div class="text-caption">Days Taken</div>
                  </v-card-text>
                </v-card>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-card variant="outlined">
                  <v-card-text class="text-center">
                    <div class="text-h5 font-weight-bold text-warning">{{ statistics.leaveRequests }}</div>
                    <div class="text-caption">Pending Requests</div>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-card-text>
          
          <v-card-actions>
            <v-btn color="primary" block prepend-icon="mdi-calendar-plus" @click="applyLeaveModal = true">
              Apply for Leave
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- Second Row Cards -->
    <v-row class="mt-4">
      <!-- Performance Card -->
      <v-col cols="12" md="4">
        <v-card elevation="2" height="100%">
          <v-card-item>
            <v-card-title class="pb-1">
              <v-icon color="primary" class="me-2">mdi-chart-line</v-icon>
              Performance & Analytics
            </v-card-title>
          </v-card-item>
          
          <v-card-text>
            <v-list density="compact">
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="amber-darken-2">mdi-trophy</v-icon>
                </template>
                <v-list-item-title>Recent Awards</v-list-item-title>
                <v-list-item-subtitle class="text-right">---</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="blue">mdi-chart-line</v-icon>
                </template>
                <v-list-item-title>Monthly Performance</v-list-item-title>
                <v-list-item-subtitle class="text-right">--% Avg</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="green">mdi-calendar-check</v-icon>
                </template>
                <v-list-item-title>Average Presence</v-list-item-title>
                <v-list-item-subtitle class="text-right">--%</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="indigo">mdi-clock-check</v-icon>
                </template>
                <v-list-item-title>Early Attendance</v-list-item-title>
                <v-list-item-subtitle class="text-right">--%</v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
          
          <v-card-actions>
            <v-btn color="primary" variant="text" block prepend-icon="mdi-file-chart">
              View Full Report
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>

      <!-- HR Contact Card -->
      <v-col cols="12" md="4">
        <v-card elevation="2" height="100%">
          <v-card-item>
            <v-card-title class="pb-1">
              <v-icon color="primary" class="me-2">mdi-account-supervisor</v-icon>
              HR Contact
            </v-card-title>
          </v-card-item>
          
          <v-card-text>
            <div class="d-flex align-center mb-4">
              <v-avatar color="primary" class="me-4">
                <v-icon color="white">mdi-account-tie</v-icon>
              </v-avatar>
              <div>
                <div class="text-h6">{{ hr.firstname }} {{ hr.lastname }}</div>
                <div class="text-subtitle-2">HR Manager</div>
              </div>
            </div>
            
            <v-list density="compact">
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="blue">mdi-email</v-icon>
                </template>
                <v-list-item-title>{{ hr.email }}</v-list-item-title>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="green">mdi-phone</v-icon>
                </template>
                <v-list-item-title>{{ hr.phone }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-card-text>
          
          <v-card-actions>
            <v-btn color="primary" block prepend-icon="mdi-chat">
              Contact HR
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>

      <!-- Payslip Card -->
      <v-col cols="12" md="4">
        <v-card elevation="2" height="100%">
          <v-card-item>
            <v-card-title class="pb-1">
              <v-icon color="primary" class="me-2">mdi-cash-multiple</v-icon>
              Payslip & Documents
            </v-card-title>
          </v-card-item>
          
          <v-card-text>
            <v-card variant="outlined" class="mb-4">
              <v-card-text class="text-center">
                <v-icon size="large" color="green">mdi-file-document</v-icon>
                <div class="text-h6 mt-2">Latest Payslip</div>
                <div class="text-subtitle-1">{{ getPreviousMonth() }}</div>
              </v-card-text>
            </v-card>
            
            <v-btn block color="success" class="mb-2" prepend-icon="mdi-download">
              Download Payslip
            </v-btn>
            
            <v-btn block color="info" variant="outlined" prepend-icon="mdi-history">
              View History
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Quick Access Section -->
    <v-row class="mt-4">
      <v-col cols="12">
        <v-card elevation="2">
          <v-card-text>
            <div class="text-h6 mb-2">Quick Access</div>
            <v-row>
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="primary" block height="80" class="text-center">
                  <div>
                    <v-icon>mdi-calendar</v-icon>
                    <div class="text-caption mt-1">Attendance</div>
                  </div>
                </v-btn>
              </v-col>
              
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="error" block height="80" class="text-center">
                  <div>
                    <v-icon>mdi-file-certificate</v-icon>
                    <div class="text-caption mt-1">Certificates</div>
                  </div>
                </v-btn>
              </v-col>
              
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="success" block height="80" class="text-center">
                  <div>
                    <v-icon>mdi-school</v-icon>
                    <div class="text-caption mt-1">Training</div>
                  </div>
                </v-btn>
              </v-col>
              
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="warning" block height="80" class="text-center">
                  <div>
                    <v-icon>mdi-account-group</v-icon>
                    <div class="text-caption mt-1">Team</div>
                  </div>
                </v-btn>
              </v-col>
              
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="info" block height="80" class="text-center">
                  <div>
                    <v-icon>mdi-notebook</v-icon>
                    <div class="text-caption mt-1">Policies</div>
                  </div>
                </v-btn>
              </v-col>
              
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="secondary" block height="80" class="text-center">
                  <div>
                    <v-icon>mdi-cog</v-icon>
                    <div class="text-caption mt-1">Settings</div>
                  </div>
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Apply Leave Dialog -->
    <v-dialog v-model="applyLeaveModal" max-width="800" persistent>
      <v-card>
        <v-toolbar color="primary" density="compact">
          <v-toolbar-title>Apply for Leave</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn icon @click="applyLeaveModal = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>

        <v-card-text class="pt-4">
          <v-form ref="createLeaveForm">
            <v-row>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="newLeave.leave_type_id"
                  :items="leaveTypes"
                  label="Type of Leave"
                  item-value="id"
                  item-title="name"
                  variant="outlined"
                  clearable
                  :rules="[v => !!v || 'Leave type is required']"
                  prepend-inner-icon="mdi-format-list-bulleted"
                ></v-select>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="newLeave.days"
                  label="Number of Days"
                  type="number"
                  variant="outlined"
                  :rules="[v => !!v || 'Days are required', v => v > 0 || 'Days must be greater than 0']"
                  prepend-inner-icon="mdi-numeric"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="newLeave.from"
                  label="Start Date"
                  type="date"
                  variant="outlined"
                  :rules="[v => !!v || 'Start date is required']"
                  prepend-inner-icon="mdi-calendar-start"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="newLeave.to"
                  label="End Date"
                  type="date"
                  variant="outlined"
                  :rules="[v => !!v || 'End date is required']"
                  prepend-inner-icon="mdi-calendar-end"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-select
                  v-model="newLeave.manager"
                  :items="managers"
                  label="Line Manager"
                  item-value="id"
                  item-title="fullname"
                  variant="outlined"
                  clearable
                  :rules="[v => !!v || 'Line manager is required']"
                  prepend-inner-icon="mdi-account-supervisor"
                ></v-select>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-select
                  v-model="newLeave.hod"
                  :items="hods"
                  label="Head of Department"
                  item-value="id"
                  item-title="fullname"
                  variant="outlined"
                  clearable
                  :rules="[v => !!v || 'HOD is required']"
                  prepend-inner-icon="mdi-account-tie"
                ></v-select>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="newLeave.phone"
                  label="Contact Phone"
                  type="text"
                  placeholder="254..."
                  variant="outlined"
                  :rules="[v => !!v || 'Phone number is required', v => /^[+254]\d{9,12}$/.test(v) || 'Invalid phone number']"
                  prepend-inner-icon="mdi-phone"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-file-input
                  v-model="newLeave.document"
                  label="Supporting Document (Optional)"
                  accept=".pdf, .doc, .docx, .jpeg, .jpg, .png"
                  variant="outlined"
                  prepend-icon="mdi-paperclip"
                  clearable
                  show-size
                ></v-file-input>
              </v-col>
              
              <v-col cols="12">
                <v-textarea
                  v-model="newLeave.comment"
                  label="Additional Comments"
                  variant="outlined"
                  counter
                  clearable
                  prepend-inner-icon="mdi-comment-text"
                  auto-grow
                  rows="3"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn color="error" variant="text" @click="applyLeaveModal = false" class="me-2">
            Cancel
          </v-btn>
          <v-btn 
            color="primary" 
            @click="submitNewLeave"
            :loading="isLoading"
            :disabled="isLoading"
          >
            Submit Application
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
export default {
  name: 'EmployeeDashboard',
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      base_url: '/',
      applyLeaveModal: false,
      statistics: {
        teamMembers: 0,
        earlyAttendances: 0,
        lateAttendances: 0,
        annualLeaveDaysAssigned: 0,
        leaveBalance: 0,
        leaveTaken: 0,
        leaveRequests: 0,
        notifications: 0,
        totalNotifications: 0,
        teamMembersOnLeave: 0,
        totalAppliedLeaves: 0,
        totalApprovedLeaves: 0,
        recentLeave: '',
      },
      hr: {
        firstname: '',
        lastname: '',
        email: '',
        phone: ''
      },
      users: [],
      managers: [],
      hods: [],
      leaveTypes: [],
      newLeave: {
        leave_type_id: null,
        from: null,
        to: null,
        phone: null,
        days: null,
        manager: null,
        hod: null,
        document: null,
        comment: null,
      },
      isLoading: false,
    };
  },
  mounted() {
    this.fetchDashboardStatistics();
    this.fetchUsers();
    this.fetchLeaveTypes();
  },
  methods: {
    getPreviousMonth() {
      const now = new Date();
      const previousMonth = new Date(now.getFullYear(), now.getMonth() - 1);

      // Format as "Month Year", e.g., "July 2024"
      const month = previousMonth.toLocaleString('default', { month: 'long' });
      const year = previousMonth.getFullYear();

      return `${month} ${year}`;
    },
    fetchUsers() {
      const apiUrl = this.base_url + `api/v1/users`;
      axios.get(apiUrl)
        .then(response => {
          this.users = response.data.users.map(user => ({
            ...user,
            fullname: `${user.firstname} ${user.lastname}`,
          }));

          this.hods = this.users.filter(user => user.is_hod === 1);
          this.managers = this.users.filter(user => user.designation_id === 1);
        })
        .catch(error => {
          console.error('Error fetching users:', error);
        });
    },
    fetchLeaveTypes() {
      const apiUrl = this.base_url + `api/v1/leave-types`;
      axios.get(apiUrl)
        .then(response => {
          this.leaveTypes = response.data.leaveTypes;
        })
        .catch(error => {
          console.error('Error fetching leave Types:', error);
        });
    },
    fetchDashboardStatistics() {
      axios
        .get(`/api/v1/dashboard/${this.user.id}`)
        .then((response) => {
          this.statistics = response.data;
          this.hr = response.data.hr;
        })
        .catch((error) => {
          console.error('Error fetching dashboard statistics:', error);
        });
    },
    submitNewLeave() {
      if (this.$refs.createLeaveForm.validate()) {
        this.isLoading = true;
        const formData = new FormData();
        formData.append('user_id', this.user.id);
        formData.append('leave_type_id', this.newLeave.leave_type_id);
        formData.append('from', this.newLeave.from);
        formData.append('to', this.newLeave.to);
        formData.append('phone', this.newLeave.phone);
        formData.append('days', this.newLeave.days);
        formData.append('manager', this.newLeave.manager);
        formData.append('hod', this.newLeave.hod);
        formData.append('comment', this.newLeave.comment);
        if (this.newLeave.document && this.newLeave.document[0]) {
          formData.append('document', this.newLeave.document[0]);
        }

        axios.post('/api/v1/leaves', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
          .then(response => {
            this.isLoading = false;
            this.$toastr.success(response.data.message);
            this.applyLeaveModal = false;
            this.fetchDashboardStatistics(); // Refresh statistics after leave application
          })
          .catch(error => {
            this.isLoading = false;
            if (error.response && error.response.data && error.response.data.error) {
              this.$toastr.error(error.response.data.error);
            } else {
              this.$toastr.error(error.message);
            }
          });
      }
    },
  },
};
</script>