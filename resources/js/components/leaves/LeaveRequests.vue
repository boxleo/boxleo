<template>
  <v-container-fluid>
    <v-layout>
      <v-navigation-drawer v-model="drawer" temporary location="right" width="500">
        <v-card>
          <v-toolbar>
            <v-toolbar-title>Filter Requests</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn icon @click="drawer = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-toolbar>


          <!-- start filter options -->
          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-select v-model="filterOptions.unit_ids" :items="countries" label="Country" item-value="id"
                    item-title="name" variant="outlined" clearable placeholder="Select Country" multiple>
                  </v-select>
                </v-col>
                <v-col cols="12">
                  <v-select v-model="filterOptions.leave_type_ids" :items="leaveTypes" placeholder="Select Leave Type"
                    label="Leave Type" item-value="id" item-title="name" variant="outlined" clearable multiple>
                  </v-select>
                </v-col>
                <v-col cols="12">
                  <v-select v-model="filterOptions.statuses" :items="statusOptions" placeholder="Select Status"
                    label="Status" variant="outlined" clearable item-value="id" item-title="name" multiple>
                  </v-select>
                </v-col>
                <v-col cols="12">
                  <v-combobox v-model="filterOptions.user_ids" :items="users" label="Employee" item-value="id"
                    item-title="fullname" variant="outlined" clearable placeholder="Select Employee" multiple>
                  </v-combobox>
                </v-col>
                <v-col cols="12">
                  <v-text-field v-model="filterOptions.application_date" placeholder="Select Application Date"
                    label="Application Date" variant="outlined" type="date" clearable>
                  </v-text-field>
                </v-col>

                <v-col cols="12">
                  <v-text-field v-model="filterOptions.from" placeholder="Select Start Date" label="Start Date"
                    variant="outlined" clearable type="date">
                  </v-text-field>
                </v-col>

              </v-row>
            </v-container>
          </v-card-text>
          <!-- end of filter options -->
          <v-card-actions class="justify-end">
            <v-btn class="bg-primary" @click="filterLeaves"><v-icon>mdi-magnify</v-icon></v-btn>
            <v-progress-circular v-if="isLoading" class="ml-2" color="primary" indeterminate>
            </v-progress-circular>
          </v-card-actions>
        </v-card>
      </v-navigation-drawer>
      <v-main>
        <!-- <v-row class="my-3">
          <v-select v-model="filterOptions.application_date" :items="application_date" label="Application Date"
            variant="outlined" dense class="mx-1">
          </v-select>
          <v-select v-model="filterOptions.statuses" :items="statusOptions" label="Status" variant="outlined" dense
            class="mx-1">
          </v-select>
          <v-select v-model="filterOptions.leave_type_ids" :items="leaveTypes" item-value="id" item-title="name"
            label="Leave Type" variant="outlined" dense class="mx-1">
          </v-select>
          <v-select v-model="filterOptions.user_ids" :items="users" label="Employee(s)" item-value="id"
            item-title="fullname" variant="outlined" dense class="mx-1">
          </v-select>
          <v-select v-filterOptions="filterOptions.unit" :items="countries" label="Branch" item-value="id"
            item-title="name" variant="outlined" dense class="mx-1">
          </v-select>
          <v-btn icon>
            <v-icon>mdi mdi-refresh</v-icon>
          </v-btn>
        </v-row> -->
        <v-row v-if="stats.awaitingApproval > 0">
          <v-col cols="12" md="8">
            <v-text-field v-model="search" label="Search" variant="underlined" clearable @clear="clearSearch"
              @input="performSearch" prepend-inner-icon="mdi-magnify">
            </v-text-field>
          </v-col>
          <v-col cols="auto">
            <v-icon @click.stop="drawer = !drawer" title="Filter Leaves" color="primary" x-small>mdi-filter</v-icon>
          </v-col>
        </v-row>
        <v-card class="mt-2">
          <v-progress-linear v-if="loading" color="green" indeterminate></v-progress-linear>
          <v-data-table v-model="selected" :headers="headers" item-key="id" :items="pendingLeaves" :search="search"
            responsive show-select>
            <template v-slot:item.comment="{ item }">

              <span v-if="item.comment">
                {{ truncateText(item.comment) }}
                <v-tooltip activator="parent" location="top"> {{ item.comment }}</v-tooltip>
              </span>
              <span v-else>
                {{ truncateText(item.comment) }}
              </span>

            </template>
            <template v-slot:item.document="{ item }">
              <a v-if="item.document" :href="getDocumentUrl(item.document)" target="_blank" :title="item.document">
                <v-icon>mdi-cloud-download</v-icon>
              </a>
              {{ !item.document ? 'Null' : '' }}
            </template>
            <template v-slot:item.status="{ item }" :style="getStatusClass(item.status)">
              <i class="mdi mdi-information"></i> {{ item.status.toUpperCase() }}
            </template>
            <template v-slot:item.actions="{ item }">
              <div style="display: flex;">
                <v-icon @click="openLogsModal(item)" color="info" style="margin-right: 8px;"
                  title="View Logs">mdi-history</v-icon>
                <v-icon @click="viewLeave(item)" color="info" style="margin-right: 8px;"
                  title="View Leave">mdi-eye-check-outline</v-icon>
                <v-icon @click="cancelLeave(item)"
                  v-if="item.status !== 'Cancelled' && item.status !== 'Approved' && item.status !== 'Expired'"
                  color="warning" style="margin-right: 8px;" title="Cancel Leave">mdi-close-circle</v-icon>
                <v-icon @click="approveLeave(item)"
                  v-if="(user.is_hr || user.is_hod || user.designation_id === 1 || item.status === 'Cancelled' || item.status === 'Pending') && item.status !== 'Approved' && item.status !== 'Expired'"
                  color="success" style="margin-right: 8px;" title="Approve Leave">mdi-thumb-up-outline
                </v-icon>
                <!-- <v-icon @click="deleteLeave(item)" v-if="item.status == 'Pending'" color="error"
                  style="margin-right: 8px;" title="Delete Leave">mdi-delete
                </v-icon> -->
              </div>
            </template>

          </v-data-table>
        </v-card>

        <!-- view Leave Dialog -->
        <v-dialog v-model="viewLeaveModal" max-width="600px" persistent responsive>
          <v-card class="view-leave-card">
            <v-card-title class="headline mb-3">
              <v-icon color="primary">mdi-information-outline</v-icon>
              Leave Information
            </v-card-title>
            <v-card-subtitle class="mb-3">
              <v-icon color="primary">mdi-account-circle</v-icon>
              Employee Name: {{ selectedItem.user.firstname }} {{ selectedItem.user.lastname }}
            </v-card-subtitle>
            <v-card-subtitle class="mb-3">
              <v-icon color="info">mdi-calendar-text</v-icon>
              Leave Type: {{ selectedItem.leave_type.name.replace('_', ' ') }}
            </v-card-subtitle>
            <v-card-text class="mb-4">
              <div class="mb-2">
                <v-icon color="grey darken-2">mdi-calendar-clock</v-icon>
                Application Date: {{ formatDate(selectedItem.created_at) }}
              </div>
              <div class="mb-2">
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
                Department: {{ selectedItem.user.department }}
              </div>
              <div class="mb-2">
                <v-icon color="blue">mdi-check-circle</v-icon>
                Leave Status: {{ selectedItem.status }}
              </div>
              <div class="mb-2"> <!-- Added margin-bottom -->
                <v-icon color="orange">mdi-progress-clock</v-icon>
                Progress: 20%
              </div>
            </v-card-text>
            <v-card-actions class="justify-end"> <!-- Align to the right -->
              <v-btn color="primary" @click="closeLeaveViewModal">
                <v-icon left>mdi-close-circle-outline</v-icon> Close
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Cancel Leave Dialog -->
        <v-dialog v-model="cancelLeaveModal" max-width="600px" persistent>
          <v-card>
            <v-card-title class="headline mb-3">
              <v-icon color="warning">mdi-alert-circle-outline</v-icon>
              Cancel Leave
            </v-card-title>
            <v-card-text>
              Are you sure you want to cancel this leave request?
              <v-textarea v-model="cancelComment" label="Notes"
                hint="Add any additional notes or comments"></v-textarea>
            </v-card-text>
            <v-card-actions class="justify-end">
              <v-btn @click="closeCancelLeaveModal">No</v-btn>
              <v-btn color="warning" @click="cancelLeaveAction">Yes, Cancel</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Approve Leave Dialog -->
        <v-dialog v-model="approveLeaveModal" max-width="600px" persistent>
          <v-card>
            <v-card-title class="headline mb-3">
              <v-icon color="success">mdi-check-circle-outline</v-icon>
              Approve Leave
            </v-card-title>
            <v-card-text>
              Are you sure you want to approve this leave request?

              <!-- Text area for adding notes -->
              <v-textarea v-model="approveNotes" label="Notes" hint="Add any additional notes or comments"></v-textarea>
            </v-card-text>
            <v-card-actions class="justify-end">
              <v-btn @click="closeApproveLeaveModal">No</v-btn>
              <v-btn color="success" @click="approveLeaveAction">Yes, Approve</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- View Logs Modal -->
        <v-dialog v-model="logsModal" max-width="700px" full-height top>
          <v-card class="elevation-10">
            <v-card-title class="headline">
              <v-icon color="primary">mdi-history</v-icon>
              Leave Logs
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text>
              <v-list dense>
                <v-list-item-group>
                  <v-list-item v-for="(log, index) in logs" :key="index">
                    <v-list-item-content>
                      <v-list-item-title class="mb-3">
                        <v-icon color="secondary" class="mr-1">mdi-account-circle</v-icon>
                        <strong>User:</strong> {{ log.user }}
                      </v-list-item-title>
                      <v-list-item-title class="mb-3">
                        <v-icon color="secondary" class="mr-1">mdi-check-circle-outline</v-icon>
                        <strong>Action:</strong> {{ capitalizeEachWord(log.action) }}
                      </v-list-item-title>

                      <v-list-item-subtitle>
                        <v-icon color="secondary" class="mr-1">mdi-clock-time-eight</v-icon>
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
              <v-btn color="primary" @click="logsModal = false" outlined>
                <v-icon left>mdi-close-circle-outline</v-icon> Close
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-main>
    </v-layout>
  </v-container-fluid>
</template>

<script>
export default {
  props: {
    userId: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      selected: [],
      drawer: false,
      base_url: '/',
      search: '',
      viewLeaveModal: false,
      approveLeaveModal: false,
      cancelLeaveModal: false,
      selectedItem: null,
      approveNotes: '',
      cancelComment: '',
      logsModal: false,
      loading: false,
      logs: [],
      pagination: {
        page: 1,
        rowsPerPage: 10
      },

      stats: {
        awaitingApproval: 0,
        cancelled: 0,
        pending: 0,
        hrApproved: 0,
        approved: 0,
        managerApproved: 0
      },
      filterOptions: {
        unit_ids: [],
        leave_type_ids: [],
        statuses: [],
        application_date: null,
        start_date: null,
        user_ids: [],
      },
      statusOptions: ['Pending', 'Cancelled', 'Manager Approved', 'Hr Approved', 'Approved'],
      users: [],
      user: {},
      leaveTypes: [],
      leaves: [],
      countries: [],
      pendingLeaves: [],
      headers: [
        { title: 'Employee', value: 'user.name' },
        { title: 'Leave Type', value: 'leave_type.name' },
        { title: 'Application Date', value: 'created_at' },
        { title: 'Duration (Days)', value: 'days' },
        { title: 'From', value: 'from' },
        { title: 'To', value: 'to' },
        { title: 'Notes', value: 'comment' },
        { title: 'Document', value: 'document' },
        { title: 'Status', value: 'status' },
        { title: 'Action', value: 'actions' }
      ],

    };
  },
  created() {
    this.fetchUsers();
    this.fetchLeaves();
    this.fetchLeaveTypes();
    this.fetchCountries();
  },


  methods: {
    truncateText(text) {
      if (text) {
        const maxLength = 20;
        if (text.length > maxLength) {
          return text.substring(0, maxLength) + '...';
        } else {
          return text;
        }
      } else {
        return '';
      }
    },

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
    fetchCountries() {
      const apiUrl = this.base_url + 'api/v1/branches';
      axios.get(apiUrl)
        .then(response => {
          this.countries = response.data.branches;
        })
        .catch(error => {
          console.error('Error fetching units:', error);
        });

    },
    fetchUsers() {
      const apiUrl = this.base_url + 'api/v1/users';

      axios.get(apiUrl)
        .then(response => {
          this.user = response.data.users.find(user => user.id === parseInt(this.userId));
          console.log("The User Object is: ", this.user);
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
          const allLeaves = response.data.leaves.filter(leave =>
            leave.status == 'Pending' ||
            leave.status == 'Manager Approved' ||
            leave.status == 'Hr Approved'
          ).map(leave => {
            if (leave.user && leave.user.firstname && leave.user.lastname) {
              leave.user.fullName = `${leave.user.firstname} ${leave.user.lastname}`;
            } else {
              leave.user.fullName = '---';
            }
            return leave;
          });

          if (this.user) {
            console.log("User  is logged in:", this.user);

            if (this.user.is_hr) {
              console.log("User  is HR.");
              // HR: See 'Pending' and 'Manager Approved' leaves
              this.pendingLeaves = allLeaves.filter(leave => leave.status === 'Pending' || leave.status === 'Manager Approved');
              console.log("Pending leaves for HR:", this.pendingLeaves); // Debugging: Log the pending leaves for HR
            } else if (this.user.is_hod === 1) {
              console.log("User  is HOD.");
              // this.pendingLeaves = allLeaves.filter(leave => leave.status === 'Approved' || leave.status === 'Hr Approved');
              this.pendingLeaves = allLeaves.filter(leave => leave.status === 'Hr Approved');
              console.log("Pending leaves for HOD:", this.pendingLeaves); // Debugging: Log the pending leaves for HOD
            } else {
              console.log("User  is neither HR nor HOD."); // Debugging: Log that the user is neither HR nor HOD
              // Other roles: See all filtered leaves
              this.pendingLeaves = allLeaves;
              console.log("All leaves for other roles:", this.pendingLeaves); // Debugging: Log all leaves for other roles
            }
          } else {
            console.log("No user logged in."); // Debugging: Log that no user is logged in
            // No user logged in
            this.pendingLeaves = [];
          }

          // Update statistics
          this.stats.awaitingApproval = allLeaves.filter(leave => leave.status !== 'Approved' && leave.status !== 'Cancelled').length;
          this.stats.cancelled = allLeaves.filter(leave => leave.status === 'Cancelled').length;
          this.stats.approved = allLeaves.filter(leave => leave.status === 'Approved').length;
          this.stats.managerApproved = allLeaves.filter(leave => leave.status === 'Manager Approved').length;
          this.stats.hrApproved = allLeaves.filter(leave => leave.status === 'Hr Approved').length;
          this.stats.pending = allLeaves.filter(leave => leave.status === 'Pending').length;
        })
        .catch(error => {
          console.error('Error fetching leaves:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    }
    ,


    performSearch() {
      if (this.search) {
        this.pendingLeaves = this.pendingLeaves.filter(leave => {
          // Search across multiple fields
          const searchTermLower = this.search.toLowerCase();
          return (
            leave.user.fullName.toLowerCase().includes(searchTermLower) ||
            leave.leave_type.name.toLowerCase().includes(searchTermLower) ||
            leave.status.toLowerCase().includes(searchTermLower) ||
            leave.from.toLowerCase().includes(searchTermLower) ||
            leave.to.toLowerCase().includes(searchTermLower)
          );
        });
      } else {
        this.fetchLeaves(); // Reload original data
      }
    },

    filterLeaves() {
      this.loading = true;
      const apiUrl = this.base_url + 'api/v1/leaves';
      const params = {
        unit_ids: this.filterOptions.unit_ids,
        leave_type_ids: this.filterOptions.leave_type_ids,
        application_date: this.filterOptions.application_date,
        from: this.filterOptions.start_date,
        user_ids: this.filterOptions.user_ids,
        statuses: this.filterOptions.statuses.map(status =>
          status.toLowerCase().replace(/\s+/g, '_')
        )
      };

      axios.get(apiUrl, { params })
        .then(response => {
          this.drawer = false;
          console.log("Filter leave requests:", response.data.leaves);
          this.pendingLeaves = response.data.leaves.map(leave => ({
            ...leave,
            user: {
              ...leave.user,
              fullName: `${leave.user.firstname} ${leave.user.lastname}`
            }
          }));

          this.loading = false;
        })
        .catch(error => {
          this.loading = false;
          console.error('Error filtering Leaves:', error);
        });
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
    capitalizeEachWord(string) {
      return string.replace(/\b\w/g, c => c.toUpperCase());
    },

    cancelLeave(item) {
      this.selectedItem = item;
      this.cancelLeaveModal = true;
    },

    approveLeave(item) {
      this.selectedItem = item;
      this.approveLeaveModal = true;
    },

    cancelLeaveAction() {
      const cancelComment = this.cancelComment.trim();

      if (!cancelComment || !cancelComment.split(/\s+/).some(word => word.length > 0)) {
        this.$toastr.error('Cancellation reason should contain at least one word');
        return;
      }

      const apiUrl = `${this.base_url}api/v1/leaves/${this.selectedItem.id}/cancel`;

      const requestData = {
        userId: this.userId,
        comment: this.cancelComment
      };

      axios.put(apiUrl, requestData)
        .then(response => {
          this.$toastr.success('Leave canceled successfully!');
          this.fetchLeaves();
        })
        .catch(error => {
          // Handle error
          this.$toastr.error('Error canceling leave!');
        })
        .finally(() => {
          this.closeCancelLeaveModal();
        });
    },
    approveLeaveAction() {
      const apiUrl = `${this.base_url}api/v1/leaves/${this.selectedItem.id}/approve`;
      const requestData = { userId: this.userId };

      axios.put(apiUrl, requestData)
        .then(response => {
          this.$toastr.success(response.data.message);
          this.fetchLeaves();
        })
        .catch(error => {
          this.$toastr.error(error.response.data.error);
        })
        .finally(() => {
          this.closeApproveLeaveModal();
        });
    },
    closeApproveLeaveModal() {
      this.approveLeaveModal = false;
    },

    closeCancelLeaveModal() {
      this.cancelLeaveModal = false;
    },

    filterLeaveRequests() {
      this.loading = true;
      const apiUrl = this.base_url + 'api/v1/leaves';

      axios.get(apiUrl)
        .then(response => {
          this.pendingLeaves = response.data.leaves.filter(leave => leave.status !== 'Approved' && leave.status !== 'Cancelled');

        })
        .catch(error => {
          console.error('Error fetching Cancelled leaves:', error);
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
          this.pendingLeaves = response.data.leaves;
        })
        .catch(error => {
          console.error('Error fetching HR approved leaves:', error);
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
          this.pendingLeaves = response.data.leaves;
        })
        .catch(error => {
          console.error('Error fetching manager approved leaves:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    filterHrApproved() {
      this.loading = true;
      const apiUrl = this.base_url + 'api/v1/leaves?status=Hr Approved';

      axios.get(apiUrl)
        .then(response => {
          this.pendingLeaves = response.data.leaves;
        })
        .catch(error => {
          console.error('Error fetching Hr approved leaves:', error);
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
          this.pendingLeaves = response.data.leaves;
        })
        .catch(error => {
          console.error('Error fetching Pending leaves:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    getDocumentUrl(documentName) {
      return `/storage/leave/documents/${documentName}`;
    },

  },
};
</script>
