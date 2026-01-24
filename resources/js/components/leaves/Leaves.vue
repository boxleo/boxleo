<template>
  <v-layout>
    <v-navigation-drawer v-model="drawer" temporary location="right" width="500">
      <v-card>
        <v-card-title>Filter Employee Leaves</v-card-title>
        <v-card-text>
          <v-col class="mt-lg-5">
            <v-autocomplete v-model="filterOptions.user_ids" :items="users" label="Employee" variant="outlined"
              item-title="fullname" item-value="id" multiple clearable />
          </v-col>
          <v-col>
            <v-autocomplete v-model="filterOptions.leave_type_ids" :items="leaveTypes" label="Leave Type"
              variant="outlined" multiple item-title="name" item-value="id" clearable />
          </v-col>
          <v-col>
            <v-autocomplete v-model="filterOptions.unit_ids" :items="units" label="Country" variant="outlined" multiple
              item-title="name" item-value="id" clearable />
          </v-col>
          <v-col>
            <v-autocomplete v-model="filterOptions.statuses" :items="statusOptions" multiple label="Status"
              item-value="name" item-title="name" variant="outlined" clearable>
            </v-autocomplete>
          </v-col>
          <v-col>
            <v-select v-model="filterOptions.application_date" :items="applicationDateOptions" label="Application Date"
              item-value="name" item-title="name" variant="outlined" clearable>
            </v-select>
          </v-col>
          <v-col class="d-flex justify-end">
            <v-btn color="dark" @click.prevent="filterLeaves">
              <v-icon color="light">mdi-filter</v-icon>
            </v-btn>
          </v-col>
          <v-overlay :value="loading">
            <v-progress-circular indeterminate color="primary"></v-progress-circular>
          </v-overlay>
        </v-card-text>
      </v-card>
    </v-navigation-drawer>

    <v-main>
      <v-row>
        <div class="d-flex justify-center flex-wrap ml-3">
          <v-btn @click="filterManagerApproved" color="secondary" text class="m-1">
            <v-icon>mdi-account-tie</v-icon> Manager Confirmed: {{ stats.managerApproved }}
          </v-btn>

          <v-btn @click="filterHodApproved" color="primary" text class="m-1">
            <v-icon>mdi-account-supervisor</v-icon> HR Confirmed: {{ stats.hrApproved }}
          </v-btn>

          <v-btn @click="filterPending" color="warning" text class="m-1">
            <v-icon>mdi-clock</v-icon> Pending: {{ stats.pending }}
          </v-btn>

          <v-btn @click="filterCancelled" color="red" text class="m-1">
            <v-icon>mdi-cancel</v-icon> Cancelled: {{ stats.cancelled }}
          </v-btn>

          <v-btn @click="filterApproved" color="green" text class="m-1">
            <v-icon>mdi-check-circle</v-icon> Approved: {{ stats.approved }}
          </v-btn>
        </div>
      </v-row>

      <v-row>
        <v-col cols="12" md="8">
          <v-text-field v-model="search" label="Search" clearable @clear="clearSearch" prepend-inner-icon="mdi-magnify">
          </v-text-field>
        </v-col>
        <v-col cols="auto">
          <v-btn icon @click="assignLeaveModal = true">
            <v-tooltip activator="parent" location="top">Assign Leave
            </v-tooltip>
            <v-icon color="info" x-small>mdi-tooltip-plus</v-icon>
          </v-btn>
        </v-col>
        <v-col cols="auto">
          <v-btn icon @click="drawer = !drawer">
            <v-tooltip activator="parent" location="top">Filter Leaves
            </v-tooltip>
            <v-icon color="primary" x-small>mdi-filter</v-icon>
          </v-btn>
        </v-col>

        <v-col cols="auto">
          <v-btn icon @click="refreshLeaves">
            <v-tooltip activator="parent" location="top">Refresh Leaves
            </v-tooltip>
            <v-icon color="danger" x-small>mdi-refresh</v-icon>
          </v-btn>
        </v-col>
      </v-row>

      <v-card>
        <v-progress-linear v-if="loading" color="green" indeterminate></v-progress-linear>
        <v-data-table :headers="tableHeaders" item-key="id" :item-value="item => `${item.name}-${item.version}`"
          class="elevation-10" :items="leaves" :search="search" responsive show-select>
          <!-- Table header slot -->
          <template v-slot:header="{ props }">
            <tr>
              <!-- Table headers with specified alignment -->
              <th v-for="header in props.headers" :key="header.value" :align="header.align">{{
                header.title }}
              </th>
            </tr>
          </template>


          <!-- Table body slot -->
          <template v-slot:item="{ item, index }">
            <tr>
              <!-- Checkbox for each row -->
              <td>
                <v-checkbox v-model="selectedItems" :value="item.id" hide-details></v-checkbox>
              </td>
              <!-- Other table data -->
              <td><strong>{{ item.user.name }}</strong></td>
              <td>{{ item.leave_type.name.replace('_', ' ') }}</td>
              <td>{{ formatDate(item.created_at) }}</td>
              <td>{{ item.from }}</td>
              <td>{{ item.to }}</td>
              <td>{{ item.days }}</td>
              <td :style="getStatusClass(item.status)">
                <v-span>
                  <i class="mdi mdi-information"></i> {{ item.status }}
                </v-span>
              </td>
              <td>
                <a v-if="item.document" :href="getDocumentUrl(item.document)" target="_blank" :title="item.document">
                  <v-icon>mdi-cloud-download</v-icon>
                </a>
                {{ !item.document ? 'Null' : '' }}
              </td>

              <td>
                <!-- Action icons -->
                <v-icon @click="openLogsModal(item)" color="history" style="margin-right: 8px;"
                  title="View Logs">mdi-history</v-icon>
                <v-icon @click="viewLeave(item)" color="info" style="margin-right: 8px;"
                  title="View Leave">mdi-eye-check-outline</v-icon>
                <v-icon v-if="item.status === 'Approved'" @click="printLeave(item)" color="success"
                  style="margin-right: 8px;" title="Print Leave Form">mdi-printer</v-icon>
                <v-icon @click="deleteLeave(item)" v-if="item.status == 'Pending'" color="error"
                  style="margin-right: 8px;" title="Delete Leave">mdi-delete
                </v-icon>
              </td>
            </tr>
          </template>
        </v-data-table>
      </v-card>
      <!-- view Leave Dialog -->
      <v-dialog v-model="viewLeaveModal" max-width="600px" persistent responsive>
        <v-card class="view-leave-card">
          <v-card-title class="headline mb-3"> <!-- Added margin-bottom -->
            <v-icon color="primary">mdi-information-outline</v-icon>
            Leave Information
          </v-card-title>
          <v-card-subtitle class="mb-3"> <!-- Added margin-bottom -->
            <v-icon color="primary">mdi-account-circle</v-icon>
            Employee Name: {{ selectedItem.user.firstname }} {{ selectedItem.user.lastname }}
          </v-card-subtitle>
          <v-card-subtitle class="mb-3"> <!-- Added margin-bottom -->
            <v-icon color="info">mdi-calendar-text</v-icon>
            Leave Type: {{ selectedItem.leave_type.name.replace('_', ' ') }}
          </v-card-subtitle>
          <v-card-text class="mb-4"> <!-- Added margin-bottom -->
            <!-- Display other leave information here -->
            <div class="mb-2"> <!-- Added margin-bottom -->
              <v-icon color="grey darken-2">mdi-calendar-clock</v-icon>
              Application Date: {{ formatDate(selectedItem.created_at) }}
            </div>
            <div class="mb-2"> <!-- Added margin-bottom -->
              <v-icon color="success">mdi-clock-time-eight</v-icon>
              From: {{ selectedItem.from }}
            </div>
            <div class="mb-2"> <!-- Added margin-bottom -->
              <v-icon color="error">mdi-clock-end</v-icon>
              To: {{ selectedItem.to }}
            </div>
            <div class="mb-2"> <!-- Added margin-bottom -->
              <v-icon color="indigo">mdi-calendar-star</v-icon>
              Leave Days: {{ selectedItem.days }}
            </div>
            <div class="mb-2"> <!-- Added margin-bottom -->
              <v-icon color="teal">mdi-comment-text-multiple</v-icon>
              Comment: {{ selectedItem.comment || 'N/A' }}
            </div>
            <div class="mb-2"> <!-- Added margin-bottom -->
              <v-icon color="purple">mdi-office-building</v-icon>
              Department: {{ selectedItem.user.department.name }}
            </div>
            <div class="mb-2">
              <v-icon color="blue">mdi-check-circle</v-icon>
              Leave Status: {{ selectedItem.status }}
            </div>
            <!-- Tasks Section -->
<div class="mt-4">
  <v-divider class="mb-3"></v-divider>

  <v-card-subtitle class="mb-2 d-flex align-center">
    <v-icon color="amber darken-2" class="mr-2">mdi-clipboard-list</v-icon>
    Delegated Tasks
  </v-card-subtitle>

  <v-list v-if="selectedItem.tasks && selectedItem.tasks.length > 0" dense class="rounded-lg">
    <v-list-item
      v-for="(task, index) in selectedItem.tasks"
      :key="index"
      class="mb-2 pa-2 grey lighten-4 rounded"
    >
      <v-list-item-icon>
        <v-icon color="amber darken-1">mdi-clipboard-text-outline</v-icon>
      </v-list-item-icon>

      <v-list-item-content>
        <v-list-item-title class="text-subtitle-2 font-weight-medium">
          {{ task.task_description || 'No description provided' }}
        </v-list-item-title>

        <v-list-item-subtitle class="text-body-2" v-if="task.assignee">
          <v-icon small color="primary" class="mr-1">mdi-account</v-icon>
          Assigned to: {{ task.assignee.firstname }} {{ task.assignee.lastname }}
        </v-list-item-subtitle>

        <v-list-item-subtitle class="text-body-2" v-else>
          <v-icon small color="grey" class="mr-1">mdi-account-off</v-icon>
          Assigned to: Unassigned
        </v-list-item-subtitle>
      </v-list-item-content>
    </v-list-item>
  </v-list>

  <v-alert v-else type="info" dense text class="mb-0">
    No tasks delegated for this leave request
  </v-alert>
</div>


          </v-card-text>
          <v-card-actions class="justify-end"> <!-- Align to the right -->
            <v-btn color="primary" @click="closeLeaveViewModal">
              <v-icon left>mdi-close-circle-outline</v-icon> Close
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <!-- Delete Leave Dialog -->
      <v-dialog v-model="deleteLeaveModal" max-width="600px" persistent>
        <v-card>
          <v-card-title class="headline mb-3">
            <v-icon color="error">mdi-delete-outline</v-icon>
            Delete Leave
          </v-card-title>
          <v-card-text>
            Are you sure you want to delete this leave request? This action cannot be undone.
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn @click="closeDeleteLeaveModal">No</v-btn>
            <v-btn color="error" @click="deleteLeaveAction">Yes, Delete</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <!-- view logs modal -->
      <v-dialog v-model="logsModal" max-width="600px" full-height top>
        <v-card>
          <v-card-title class="headline">
            <v-icon color="primary">mdi-history</v-icon>
            View Logs
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text>
            <v-list dense>
              <v-list-item-group>
                <v-list-item v-for="(log, index) in logs" :key="index">
                  <v-list-item-content>
                    <v-list-item-title class="mb-3">
                      <v-icon color="info" class="mr-1">mdi-account-circle</v-icon>
                      <strong>User:</strong> {{ log.user }}
                    </v-list-item-title>
                    <v-list-item-title class="mb-3">
                      <v-icon color="success" class="mr-1">mdi-check-circle-outline</v-icon>
                      <strong>Action:</strong> {{ log.action }}
                    </v-list-item-title>
                    <v-list-item-subtitle>
                      <v-icon color="red darken-2" class="mr-1">mdi-clock-time-eight</v-icon>
                      <strong>Time:</strong> {{ log.time }}
                    </v-list-item-subtitle>
                  </v-list-item-content>
                  <v-divider v-if="index < logs.length - 1"></v-divider>
                </v-list-item>
              </v-list-item-group>
            </v-list>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-btn color="primary" @click="closeLogsModal">
              <v-icon left>mdi-close-circle-outline</v-icon> Close
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <!-- Assign Leave component -->
      <v-dialog v-model="assignLeaveModal" max-width="600px" persistent>
        <v-card>
          <v-toolbar color="primary" dark>
            <v-toolbar-title>Assign Leave</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn icon @click="assignLeaveModal = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-toolbar>

          <v-card-text>
            <v-form ref="assignLeaveForm">
              <v-container>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select v-model="assignLeave.user_id" :items="users" label="Employee" item-value="id"
                      item-title="fullname" outlined clearable>
                    </v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select v-model="assignLeave.leave_type_id" :items="leaveTypes" label="Leave Type" item-value="id"
                      item-title="name" outlined clearable>
                    </v-select>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field v-model="assignLeave.from" label="From" type="date" outlined></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="assignLeave.to" label="To" type="date" outlined></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="assignLeave.days" label="Days" type="number" outlined></v-text-field>
                  </v-col>


                  <v-col cols="12" md="6">
                    <v-text-field v-model="assignLeave.phone" label="Phone" type="text" placeholder="+254" outlined>
                    </v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-file-input v-model="assignLeave.document" label="Attach Document (Optional)"
                      accept=".pdf, .doc, .docx, .png" outlined clearable></v-file-input>
                  </v-col>
                  <v-col cols="12">
                    <v-textarea v-model="assignLeave.comment" label="Comment (Optional)" outlined
                      clearable></v-textarea>
                  </v-col>
                </v-row>
              </v-container>
            </v-form>
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn @click="assignLeaveModal = false" outlined>close</v-btn>
            <v-btn class="bg-primary elevation-10" @click="submitLeave">Assign Leave</v-btn>
            <v-progress-circular v-if="isLoading" class="ml-2" color="primary" indeterminate>
            </v-progress-circular>
          </v-card-actions>

        </v-card>
      </v-dialog>
    </v-main>
  </v-layout>
</template>

<script>

export default {
  props: {
    userId: {
      type: [Number, Number],
      required: true
    }
  },
  data() {
    return {
      drawer: false,
      loading: false,
      filterLeaveModal: false,
      base_url: '/',
      search: '',
      selectAll: false,
      selectedItems: [],
      viewLeaveModal: false,
      approveLeaveModal: false,
      cancelLeaveModal: false,
      deleteLeaveModal: false,
      selectedItem: null,
      assignLeaveModal: false,
      approveNotes: '',
      cancelNotes: '',
      logsModal: false,
      logs: [],
      assignLeave: {
        user_id: null,
        from: null,
        to: null,
        leave_type_id: null,
        comment: null,
        days: null,
        document: null,

      },

      filterOptions: {
        user_ids: [],
        leave_type_ids: [],
        unit_ids: [],
        statuses: null,
        application_date: null,
        created_date: null,
      },
      applicationDateOptions: ['Today', 'Current Week', 'Last Week', 'Current Month', 'Current Year'],
      statusOptions: ['Approved', 'Hr Approved', 'Manager Approved', 'Pending', 'Cancelled'],
      users: [],
      leaveTypes: [],
      leaves: [],
      units: [],
      applicationDate: new Date().toISOString().substr(0, 10),
      tableHeaders: [
        { title: 'Employee', value: 'user.name' },
        { title: 'Leave Type', value: 'leave_type.name' },
        { title: 'Application Date', value: 'created_at' },
        { title: 'Start Date', value: 'from' },
        { title: 'End Date', value: 'to' },
        { title: 'Total Days', value: 'days' },
        { title: 'Status', value: 'status' },
        { title: 'Document', value: 'document' },
        { title: 'Action', value: 'actions' }
      ],
      stats: {
        hrApproved: 0,
        managerApproved: 0,
        cancelled: 0,
        pending: 0,
        approved: 0
      },

    };
  },
  mounted() {
    this.fetchUsers();
    this.fetchUnits();
    this.fetchLeaves();
    this.fetchLeaveTypes();
  },
  computed: {
    tableItems() {
      return this.leaves.map(item => ({
        ...item,
        user_full_name: this.getUserFullName(item.user),
        status_icon: this.getStatusIcon(item.status),
      }));
    },

  },
  methods: {

    formatDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(date).toLocaleDateString(undefined, options);
    },

    fetchLeaveTypes() {
      const apiUrl = this.base_url + 'api/v1/leave-types';

      axios.get(apiUrl)
        .then(response => {
          this.leaveTypes = response.data.leaveTypes;
        })
        .catch(error => {
          console.error('Error fetching Leave Types:', error);
        });
    },
    fetchUnits() {
      const apiUrl = this.base_url + 'api/v1/branches';

      axios.get(apiUrl)
        .then(response => {
          this.units = response.data.branches;
        })
        .catch(error => {
          console.error('Error fetching units:', error);
        });
    },
    filterLeaves() {
      const filterParams = new URLSearchParams();

      // Append user_ids if any
      if (this.filterOptions.user_ids.length) {
        filterParams.append('user_ids', this.filterOptions.user_ids.join(','));
      }

      // Append leave_type_ids if any
      if (this.filterOptions.leave_type_ids.length) {
        filterParams.append('leave_type_ids', this.filterOptions.leave_type_ids.join(','));
      }

      // Append unit_ids if any
      if (this.filterOptions.unit_ids.length) {
        filterParams.append('unit_ids', this.filterOptions.unit_ids.join(','));
      }

      // Append application_date if provided
      if (this.filterOptions.application_date) {
        filterParams.append('application_date', this.filterOptions.application_date);
      }

      // Append statuses if any
      // if (this.filterOptions.statuses.length) {
      //     filterParams.append('statuses', this.filterOptions.statuses.join(','));
      // }

      this.loading = true;

      const apiUrl = this.base_url + 'api/v1/leaves?' + filterParams.toString();

      axios.get(apiUrl)
        .then(response => {
          if (response.data.errors) {
            console.error('Errors returned from API:', response.data.errors);
          } else {
            this.leaves = response.data.leaves;
            //this.calculateDashboardSummary();
          }
        })
        .catch(error => {
          console.error('Error fetching leaves:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },


    filterManagerApproved() {
      this.loading = true;
      const apiUrl = this.base_url + 'api/v1/leaves?status=Manager Approved';

      axios.get(apiUrl)
        .then(response => {
          this.leaves = response.data.leaves;
        })
        .catch(error => {
          console.error('Error fetching manager approved leaves:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    filterHodApproved() {
      this.loading = true;
      const apiUrl = this.base_url + 'api/v1/leaves?status=Hr Approved';

      axios.get(apiUrl)
        .then(response => {
          this.leaves = response.data.leaves;
        })
        .catch(error => {
          console.error('Error fetching Hr Approved leaves:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    filterPending() {
      this.loading = true;
      const apiUrl = this.base_url + 'api/v1/leaves?status=Pending';

      axios.get(apiUrl)
        .then(response => {
          this.leaves = response.data.leaves;
        })
        .catch(error => {
          console.error('Error fetching Pending leaves:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    filterCancelled() {
      this.loading = true;
      const apiUrl = this.base_url + 'api/v1/leaves?status=Cancelled';

      axios.get(apiUrl)
        .then(response => {
          this.leaves = response.data.leaves;
        })
        .catch(error => {
          console.error('Error fetching cancelled leaves:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    filterApproved() {
      this.loading = true;
      const apiUrl = this.base_url + 'api/v1/leaves?status=Approved';

      axios.get(apiUrl)
        .then(response => {
          this.leaves = response.data.leaves;
        })
        .catch(error => {
          console.error('Error fetching approved leaves:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    isDateInRange(date, range) {
      const currentDate = new Date(date);
      const today = new Date();
      Comment
      switch (range) {
        case 'Today':
          return currentDate.toDateString() === today.toDateString();
        case 'Current Week':
          const currentWeekStart = new Date(today.setDate(today.getDate() - today.getDay()));
          const currentWeekEnd = new Date(today.setDate(today.getDate() + 6));
          return currentDate >= currentWeekStart && currentDate <= currentWeekEnd;
        case 'Last Week':
          const lastWeekStart = new Date(today.setDate(today.getDate() - today.getDay() - 7));
          const lastWeekEnd = new Date(today.setDate(today.getDate() + 6));
          return currentDate >= lastWeekStart && currentDate <= lastWeekEnd;
        case 'Current Month':
          const currentMonthStart = new Date(today.getFullYear(), today.getMonth(), 1);
          const nextMonthStart = new Date(today.getFullYear(), today.getMonth() + 1, 1);
          const currentMonthEnd = new Date(nextMonthStart - 1);
          return currentDate >= currentMonthStart && currentDate <= currentMonthEnd;
        case 'Current Year':
          const currentYearStart = new Date(today.getFullYear(), 0, 1);
          const nextYearStart = new Date(today.getFullYear() + 1, 0, 1);
          const currentYearEnd = new Date(nextYearStart - 1);
          return currentDate >= currentYearStart && currentDate <= currentYearEnd;
        default:
          return false;
      }
    },

    fetchUsers() {
      const apiUrl = this.base_url + 'api/v1/users';

      axios.get(apiUrl)
        .then(response => {
          this.users = response.data.users.map(user => ({
            id: user.id,
            fullname: user.firstname + " " + user.lastname,

          }));
        })
        .catch(error => {
          console.error('Error fetching users:', error);
        });
    },

    fetchLeaves() {
      this.loading = true;

      const apiUrl = this.base_url + 'api/v1/leaves';

      axios.get(apiUrl)
        .then(response => {
          this.leaves = response.data.leaves;
          this.calculateDashboardSummary();
        })
        .catch(error => {
          console.error('Error fetching leaves:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    refreshLeaves() {
      this.fetchLeaves();
      this.$toastr.success('success')
    },
    calculateDashboardSummary() {
      this.stats.approved = this.leaves.filter(item => item.status === 'Approved').length;
      this.stats.cancelled = this.leaves.filter(item => item.status === 'Cancelled').length;
      this.stats.pending = this.leaves.filter(item => item.status === 'Pending').length;
      this.stats.hrApproved = this.leaves.filter(item => item.status === 'Hr Approved').length;
      this.stats.managerApproved = this.leaves.filter(item => item.status === 'Manager Approved').length;

    },

    submitLeave() {
      const uri = '/api/v1/leave-assign'
      axios.post(url, this.assignLeaveForm)

    },

    getStatusClass(status) {
      return {
        color: status === 'Approved' ? 'green' :
          status === 'Cancelled' ? 'red' :
            status === 'Pending' ? 'orange' :
              status === 'Hr Approved' ? 'purple' :
                status === 'Manager Approved' ? 'blue' : 'blue'
      };
    },


    viewLeave(item) {
      this.selectedItem = item;
      this.viewLeaveModal = true;
    },
    closeLeaveViewModal() {
      this.viewLeaveModal = false;
    },

    openLogsModal(item) {
      const apiUrl = `${this.base_url}api/v1/leaves/${item.id}/logs`;

      axios.get(apiUrl)
        .then(response => {
          this.logs = response.data.logs;
        })
        .catch(error => {
          console.error('Error fetching logs:', error);
        })
        .finally(() => {
          this.logsModal = true;
        });
    },
    closeLogsModal() {
      this.logsModal = false;
    },

    deleteLeave(item) {
      this.selectedItem = item;
      this.deleteLeaveModal = true;
    },

    deleteLeaveAction() {
      const apiUrl = `${this.base_url}api/v1/leaves/${this.selectedItem.id}`;

      axios.delete(apiUrl)
        .then(response => {
          if (response.status === 200) {
            this.$toastr.success('Leave deleted successfully!');
            this.closeDeleteLeaveModal();
            this.fetchLeaves();
          } else {
            this.$toastr.error('Error deleting leave!');
            this.closeDeleteLeaveModal();
          }
        })
        .catch(error => {
          this.$toastr.error('Error deleting leave!');
          this.closeDeleteLeaveModal();
        });
    },

    closeDeleteLeaveModal() {
      this.deleteLeaveModal = false;
    },

    printLeave(item) {
      axios.post('/api/v1/leaves/print', { leave: item }, { responseType: 'blob' })
        .then(response => {
          const blob = new Blob([response.data], { type: 'application/pdf' });
          const url = window.URL.createObjectURL(blob);
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', 'leave_form.pdf');
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          console.log('Leave form downloaded successfully');
          this.$toastr.success('Leave form downloaded successfully');
        })
        .catch(error => {
          console.error('Error downloading leave form:', error);
          this.$toastr.error('Error downloading leave form');
        });
    },

    getDocumentUrl(documentName) {
    return `/leave/documents/${documentName}`;
    },

    generateExcel() {
      axios.post('/api/v1/leaves/generate-excel', { leaves: this.leaves })
        .then(response => {
          this.$toastr.success('Excel file generated successfully');
          console.log('Excel file generated successfully:', response.data);
        })
        .catch(error => {
          this.$toastr.error('Error generating Excel file');
          console.error('Error generating Excel file:', error);
        });
    },

    generatePDF() {
      axios.post('/api/v1/leaves/generate-pdf', { leaves: this.leaves })
        .then(response => {
          this.$toastr.success('PDF file generated successfully');
          console.log('PDF file generated successfully:', response.data);
        })
        .catch(error => {
          this.$toastr.error('Error generating PDF file');
          console.error('Error generating PDF file:', error);
        });
    },
    // Get assignee name from user ID
    // getAssigneeName(assigneeId) {
    // if (!assigneeId) return 'Unassigned';

    // const user = this.users.find(user => user.id === assigneeId);
    // if (user) {
    //     return `${user.firstname} ${user.lastname}`;
    // }
    // return 'Unknown User';
    // },
    // Format date for better readability
// formatDate(date) {
//   if (!date) return 'N/A';
//   return new Date(date).toLocaleDateString('en-US', {
//     year: 'numeric', 
//     month: 'long', 
//     day: 'numeric'
//   });
// },

  },
};
</script>
<style scoped>
button {
  letter-spacing: 1px !important;
  text-transform: uppercase !important;
}
</style>
