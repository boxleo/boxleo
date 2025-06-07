<template>
  <v-row>
    <v-col>
      <v-row class="mb-0">

        <v-col class="d-flex justify-end">

          <v-btn color="primary" @click="applyLeave">
            Apply Leave
          </v-btn>
        </v-col>
      </v-row>
      <v-divider></v-divider>
      <v-responsive>
        <v-data-table :headers="headers" :items="leaves" item-key="id">
          <template v-slot:item.index="{ index }">
            {{ index + 1 }}
          </template>
          <template v-slot:item.status="{ item }">
            <td>
              <span :style="{ color: getStatusBackgroundColor(item.status) }">
                <v-icon>mdi-history</v-icon>
                {{ item.status.charAt(0).toUpperCase() + item.status.slice(1).toLowerCase() }}
              </span>
            </td>
          </template>
          <template v-slot:item.document="{ item }">
            <td>
              <a v-if="item.document" :href="getDocumentUrl(item.document)" target="_blank" :title="item.document">
                <v-icon>mdi-cloud-download</v-icon>
              </a>
              {{ !item.document ? '- - -' : '' }}
            </td>
          </template>
          <template v-slot:item.actions="{ item }">
            <td>
              <div class="d-flex align-items-center">
                <v-icon @click="viewLeave(item)" color="info" style="margin-right: 8px;"
                  title="View Leave">mdi-eye-check-outline</v-icon>

                <v-icon @click.prevent="openLogsModal(item)" color="primary" class="mr-2" title="View Logs">mdi-history
                </v-icon>

                <v-icon v-if="item.status == 'Pending'" @click="openEditLeaveModal(item.id)" title="Edit Leave"
                  class="mr-2" color="info">mdi-pencil
                </v-icon>
                <v-icon v-if="item.status == 'Pending'" class="cancel-icon" @click="openCancelLeaveModal(item.id)"
                  title="Cancel Leave" color="danger">mdi-close-circle
                </v-icon>
              </div>
            </td>
          </template>
        </v-data-table>
      </v-responsive>
      <!-- appy Leave Modal -->
      <v-dialog v-model="createLeaveModal" max-width="600px" persistent>
        <v-card class="elevation-12" style="border-radius: 16px;">
          <v-toolbar color="primary" dark>
            <v-toolbar-title>Apply Leave</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn icon @click="createLeaveModal = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-toolbar>

          <v-card-text>
            <v-form ref="createLeaveForm">
              <v-container>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select v-model="newLeave.leave_type_id" :items="leaveTypes" label="Leave Type" item-value="id"
                      item-title="name" variant="outlined" clearable class="rounded-lg"
                      :rules="[v => !!v || 'Leave Type is required']"></v-select>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field v-model="newLeave.days" label="Days" type="number" variant="outlined"
                      class="rounded-lg"
                      :rules="[v => !!v || 'Days are required', v => v > 0 || 'Days must be greater than 0']"></v-text-field>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field v-model="newLeave.from" label="From" type="date" variant="outlined" class="rounded-lg"
                      :rules="[v => !!v || 'From date is required']"></v-text-field>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field v-model="newLeave.to" label="To" type="date" variant="outlined" class="rounded-lg"
                      :rules="[v => !!v || 'To date is required']"></v-text-field>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-select v-model="newLeave.manager" :items="managers" label="Line Manager" item-value="id"
                      item-title="fullname" variant="outlined" clearable class="rounded-lg"
                      :rules="[v => !!v || 'Line Manager is required']"></v-select>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-select v-model="newLeave.hod" :items="hods" label="HOD" item-value="id" item-title="fullname"
                      variant="outlined" clearable class="rounded-lg"
                      :rules="[v => !!v || 'HOD is required']"></v-select>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field v-model="newLeave.phone" label="Phone" type="text" placeholder="254"
                      variant="outlined" class="rounded-lg"
                      :rules="[v => !!v || 'Phone number is required', v => /^[+254]\d{9,12}$/.test(v) || 'Invalid phone number']"></v-text-field>
                  </v-col>


                  <v-col cols="12" md="6">
                    <v-file-input v-model="selectedLeave.document" label="Attach Document (Optional)"
                      accept=".pdf,.doc,.docx,.jpeg,.jpg,.png" variant="outlined" clearable class="rounded-lg"
                      @change="handleFileUpload" ref="editDocumentInput" show-size prepend-icon="mdi-paperclip">
                    </v-file-input>
                  </v-col>


                  <v-col cols="12" md="6">
                    <v-textarea v-model="newLeave.comment" label="Comment (Optional)" variant="outlined" clearable
                      class="rounded-lg"></v-textarea>
                  </v-col>

                  <!-- <v-col cols="12">
                    <v-autocomplete v-model="newLeave.follower" :items="followers" label="Delegate your tasks to:" item-value="id"
                      item-title="fullname" variant="outlined" clearable class="rounded-lg" multiple></v-autocomplete>
                  </v-col>

                  <v-col cols="12">
                    <v-textarea v-model="newLeave.task" label="Tasks (Optional)" variant="outlined" clearable
                      class="rounded-lg"></v-textarea>
                  </v-col> -->

                  <v-col cols="12">
                    <div class="mb-2">
                      <span class="text-subtitle-1 font-weight-medium">Assign Tasks (Optional)</span>
                    </div>

                    <div v-for="(task, index) in newLeave.delegatedTasks" :key="index" class="mb-4">
                      <v-row>
                        <v-col cols="12" md="5">
                          <v-select v-model="task.assignee_id" :items="followers" item-value="id" item-title="fullname"
                            label="Assign To" :rules="[v => !!v || 'Assignee is required']" variant="outlined"
                            dense></v-select>
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-text-field v-model="task.task_description" label="Task Description"
                            :rules="[v => !!v || 'Task description is required']" variant="outlined"
                            dense></v-text-field>
                        </v-col>

                        <v-col cols="12" md="1" class="d-flex align-center justify-center">
                          <v-btn icon @click="removeNewTask(index)" variant="text" color="red">
                            <v-icon>mdi-delete</v-icon>
                          </v-btn>
                        </v-col>
                      </v-row>
                    </div>

                    <v-btn color="primary" @click="addNewTask" text class="mb-4">
                      <v-icon left>mdi-plus</v-icon> Add Task
                    </v-btn>
                  </v-col>


                </v-row>
              </v-container>
            </v-form>
          </v-card-text>

          <v-card-actions class="justify-end">
            <v-btn @click="createLeaveModal = false" outlined color="secondary" class="text-body-1 rounded-lg">
              Cancel
            </v-btn>
            <v-btn class="bg-primary elevation-10 text-white rounded-lg" @click="submitNewLeave">
              Apply Leave
            </v-btn>
            <v-progress-circular v-if="isLoading" class="ml-2" color="primary" indeterminate
              size="24"></v-progress-circular>
          </v-card-actions>
        </v-card>
      </v-dialog>


      <!-- Edit Leave Modal -->
      <v-dialog v-model="editLeaveModal" max-width="600px" persistent>
        <v-card>
          <v-toolbar color="primary" dark>
            <v-toolbar-title>Edit Leave</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn icon @click="closeModals">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-toolbar>

          <v-card-text>
            <v-form ref="editLeaveForm">
              <v-container>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-select v-model="selectedLeave.leave_type_id" :items="leaveTypes" label="Leave Type"
                      item-value="id" item-title="name" outlined clearable>
                    </v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="selectedLeave.from" label="From" type="date" outlined>
                    </v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="selectedLeave.to" label="To" type="date" outlined>
                    </v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="selectedLeave.days" label="Days" type="number" outlined>
                    </v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select v-model="selectedLeave.manager" :items="users" label="Line Manager" item-value="id"
                      item-title="fullname" outlined clearable>
                    </v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select v-model="selectedLeave.hod" :items="users" label="HOD" item-value="id"
                      item-title="fullname" outlined clearable>
                    </v-select>
                  </v-col>
                  <v-col cols="12">
                    <v-col cols="12">
                      <v-file-input v-model="selectedLeave.document" label="Attach Document (Optional)"
                        accept=".pdf,.doc,.docx,.jpeg,.jpg,.png" variant="outlined" clearable class="rounded-lg"
                        @change="handleFileUpload" ref="editDocumentInput" show-size prepend-icon="mdi-paperclip">
                      </v-file-input>
                    </v-col>

                  </v-col>
                  <v-col cols="12">
                    <v-textarea v-model="selectedLeave.comment" label="Comment (Optional)" outlined clearable>
                    </v-textarea>
                  </v-col>
                  <v-col cols="12">
                    <v-divider class="my-4"></v-divider>
                    <h3 class="text-h6 mb-2">Assign Tasks (Optional)</h3>

                    <v-row v-for="(task, index) in selectedLeave.tasks" :key="index" align="center">
                      <v-col cols="12" md="5">
                        <v-select v-model="task.assignee_id" :items="users" label="Assignee" item-value="id"
                          item-title="fullname" outlined dense clearable></v-select>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field v-model="task.task_description" label="Task Description" outlined
                          dense></v-text-field>
                      </v-col>
                      <v-col cols="12" md="1" class="d-flex align-center justify-center">
                        <v-btn icon color="red" variant="text" @click="removeEditTask(index)">
                          <v-icon>mdi-delete</v-icon>
                        </v-btn>
                      </v-col>
                    </v-row>

                    <v-btn color="primary" small @click="addEditTask">
                      <v-icon left>mdi-plus</v-icon> Add Task
                    </v-btn>
                  </v-col>

                </v-row>
              </v-container>
            </v-form>
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn color="red" @click="closeModals" outlined>Cancel</v-btn>
            <v-btn color="green" @click="submitEdittedLeave">Save Changes</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

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
              Department: {{ selectedItem.user.department }}
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
                <v-list-item v-for="(task, index) in selectedItem.tasks" :key="index"
                  class="mb-2 pa-2 grey lighten-4 rounded">
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


      <!-- Cancel Leave Modal -->
      <v-dialog v-model="cancelLeaveModal" max-width="600px">
        <v-card>
          <v-card-title>Cancel Leave</v-card-title>
          <v-card-text>
            <div>
              <p>Are you sure you want to cancel the leave?</p>
              <v-textarea v-model="cancelComment" label="Comment" outlined></v-textarea>
            </div>
          </v-card-text>
          <v-card-actions>
            <v-btn @click="closeCancelModals">Close</v-btn>
            <v-btn color="error" @click="submitCancelLeave">Cancel Leave</v-btn>
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

    </v-col>
  </v-row>
</template>

<script>
export default {
  props: {
    userId: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      base_url: '/',
      headers: [
        { title: '#', key: 'index' },
        { title: 'Application Date', key: 'created_at' },
        { title: 'Leave Type', key: 'leave_type.name' },
        { title: 'Leave Days', key: 'days' },
        { title: 'From', key: 'from' },
        { title: 'To', key: 'to' },
        { title: 'Document', key: 'document' },
        { title: 'Comment', key: 'comment' },
        { title: 'Status', key: 'status' },
        { title: 'Action', key: 'actions' },
      ],
      leaves: [],
      users: [],
      managers: [],
      hods: [],
      followers: [],
      delegatedTasks: [],
      user: {},
      user_unit_id: null,
      leaveTypes: [],
      search: '',
      applyLeaveModal: false,
      leaveForm: {
        leave_type_id: null,
        from: null,
        to: null,
        phone: null,
        days: null,
        manager: null,
        hod: null,
        document: null,
        comment: '',
        phone: null,
        follower: null,
      },
      logsModal: false,
      viewLeaveModal: false,
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
        follower: null,
        task: '',
        delegatedTasks: [
          { assignee_id: null, task_description: '' },
        ],
      },
      isLoading: false,
      cancelComment: '',
      editLeaveModal: false,
      cancelLeaveModal: false,
      selectedLeave: {},
      createLeaveModal: false,
      isNewLeaveMode: true,
    };
  },
  computed: {
    filteredLeaves() {
      return this.leaves.filter(leave =>
        Object.values(leave).some(val =>
          val && val.toString().toLowerCase().includes(this.search.toLowerCase())
        )
      );
    },
    getStatusBackgroundColor() {
      return function (status) {
        switch (status) {
          case 'Approved':
            return 'green';
          case 'Cancelled':
            return 'red';
          case 'Pending':
            return 'indigo';
          case 'Hr Approved':
            return 'purple';
          case 'Manager Approved':
            return 'black';
          default:
            return 'indigo';
        }
      };
    },

  },
  mounted() {
    this.fetchLeaves();
    this.fetchUsers();
    this.fetchLeaveTypes();
  },
  methods: {
    formatDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(date).toLocaleDateString(undefined, options);
    },


    capitalizeAction(action) {
      return action.replace(/\b\w/g, (char) => char.toUpperCase());
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

    fetchLeaves() {
      const apiUrl = this.base_url + `api/v1/leaves/${this.userId}`;
      axios.get(apiUrl)
        .then(response => {
          this.leaves = response.data.leaves;
          console.log("Leaves: ", this.leaves);
        })
        .catch(error => {
          console.error('Error fetching leaves:', error);
        });
    },
    fetchUsers() {
      const apiUrl = this.base_url + `api/v1/users`;
      axios.get(apiUrl)
        .then(response => {
          this.user = response.data.users.find(user => user.id === parseInt(this.userId));
          console.log("The User Object is: ", this.user);

          this.users = response.data.users.map(user => ({
            ...user,
            fullname: `${user.firstname} ${user.lastname}`,
          }));

          this.hods = this.users.filter(user => user.is_hod === 1);
          this.managers = this.users.filter(user => user.designation_id === 1);
          // Find users in the same department as the logged-in user
          //   this.followers = this.users.filter(user =>
          //         // user.department_id === this.user.department_id &&
          //         user.unit_id === this.user.unit_id &&
          //         user.id !== parseInt(this.userId)
          //   );
          this.followers = this.users.filter(u =>
            u.unit_id === this.user.unit_id &&   // same unit
            u.department_id === this.user.department_id &&   // same department
            u.id !== this.user.id              // exclude yourself
          );

          console.log("HODs: ", this.hods);
          console.log("Managers: ", this.managers);
        })
        .catch(error => {
          console.error('Error fetching users:', error);
        });
    },

    // openEditLeaveModal(leaveId) {
    // // Create a deep copy and ensure consistent property names
    // const leave = this.leaves.find(leave => leave.id === leaveId);

    // this.selectedLeave = {
    //     ...leave,
    //     // Keep the property name as 'tasks' to be consistent with your edit methods
    //     tasks: leave?.tasks || [{ assignee_id: null, task_description: '' }]
    // };

    // this.isNewLeaveMode = false;
    // this.editLeaveModal = true;
    // },
    openEditLeaveModal(leaveId) {
      // Find the leave in your data source
      const originalLeave = this.leaves.find(leave => leave.id === leaveId);

      // Create a deep copy to avoid direct mutation
      this.selectedLeave = JSON.parse(JSON.stringify(originalLeave));

      // Ensure tasks array exists and is properly initialized
      if (!this.selectedLeave.tasks || !Array.isArray(this.selectedLeave.tasks)) {
        this.selectedLeave.tasks = [{ assignee_id: null, task_description: '' }];
      }

      this.editLeaveModal = true;
    },

    openCancelLeaveModal(leaveId) {
      this.selectedLeave = this.leaves.find(leave => leave.id === leaveId);
      this.cancelLeaveModal = true;
    },

    closeModals() {
      this.applyLeaveModal = false;
      this.editLeaveModal = false;
      this.cancelLeaveModal = false;
      this.selectedLeave = {};
    },

    applyLeave() {
      this.createLeaveModal = true;
    },



    // Fixed handleFileUpload method
    handleFileUpload(event) {
      console.log('File upload event:', event);

      let files = null;

      // Handle different event types from v-file-input
      if (event && Array.isArray(event)) {
        // Vuetify v-file-input returns array directly
        files = event;
      } else if (event && event.target && event.target.files) {
        // Standard file input event
        files = Array.from(event.target.files);
      } else if (event instanceof FileList) {
        // FileList object
        files = Array.from(event);
      } else if (event instanceof File) {
        // Single file
        files = [event];
      }

      console.log('Processed files:', files);

      if (files && files.length > 0) {
        const file = files[0]; // Take the first file
        console.log('Selected file:', {
          name: file.name,
          type: file.type,
          size: file.size
        });

        // Validate file size (10MB = 10485760 bytes)
        if (file.size > 10485760) {
          this.$toastr.error('File size must be less than 10MB');
          return;
        }

        // Validate file type
        const allowedTypes = ['application/pdf', 'application/msword',
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
          'image/jpeg', 'image/jpg', 'image/png'];

        if (!allowedTypes.includes(file.type)) {
          this.$toastr.error('Invalid file type. Only PDF, DOC, DOCX, JPEG, JPG, and PNG files are allowed.');
          return;
        }

        // Store the file
        if (this.createLeaveModal) {
          this.newLeave.document = file;
        }

        if (this.editLeaveModal) {
          this.selectedLeave.document = file;
        }
      } else {
        console.log('No files selected');

        // Clear documents when no files
        if (this.createLeaveModal) {
          this.newLeave.document = null;
        }

        if (this.editLeaveModal) {
          this.selectedLeave.document = null;
        }
      }
    },

    // Fixed submitNewLeave method
    submitNewLeave() {
      if (!this.$refs.createLeaveForm.validate()) {
        this.$toastr.error('Please fill in all required fields correctly');
        return;
      }

      this.isLoading = true;
      const formData = new FormData();

      // Append basic fields
      formData.append('user_id', this.userId);
      formData.append('leave_type_id', this.newLeave.leave_type_id);
      formData.append('from', this.newLeave.from);
      formData.append('to', this.newLeave.to);
      formData.append('phone', this.newLeave.phone);
      formData.append('days', this.newLeave.days);
      formData.append('manager', this.newLeave.manager);
      formData.append('hod', this.newLeave.hod);
      formData.append('comment', this.newLeave.comment || '');

      // Handle file upload
      if (this.newLeave.document && this.newLeave.document instanceof File) {
        console.log('Appending file:', this.newLeave.document.name);
        formData.append('document', this.newLeave.document);
      }

      // Handle delegated tasks - filter out empty tasks
      const validTasks = this.newLeave.delegatedTasks.filter(task =>
        task.assignee_id && task.task_description && task.task_description.trim()
      );

      if (validTasks.length > 0) {
        formData.append('delegatedTasks', JSON.stringify(validTasks));
      }

      // Debug FormData contents
      console.log('FormData contents:');
      for (let pair of formData.entries()) {
        if (pair[1] instanceof File) {
          console.log(pair[0] + ': ', `File - ${pair[1].name} (${pair[1].size} bytes)`);
        } else {
          console.log(pair[0] + ': ', pair[1]);
        }
      }

      axios.post('/api/v1/leaves', formData, {
        // headers: {
        //   'Content-Type': 'multipart/form-data',
        // },
        // timeout: 30000, // 30 second timeout
      })
        .then(response => {
          this.fetchLeaves();
          this.isLoading = false;
          this.$toastr.success(response.data.message);
          this.createLeaveModal = false;
          this.resetNewLeaveForm();
        })
        .catch(error => {
          this.isLoading = false;
          console.error('Submit error:', error);

          if (error.response && error.response.data) {
            if (error.response.data.errors) {
              // Handle validation errors
              const errors = error.response.data.errors;
              Object.keys(errors).forEach(key => {
                errors[key].forEach(message => {
                  this.$toastr.error(message);
                });
              });
            } else if (error.response.data.error) {
              this.$toastr.error(error.response.data.error);
            } else if (error.response.data.message) {
              this.$toastr.error(error.response.data.message);
            }
          } else {
            this.$toastr.error('An error occurred while submitting the leave application');
          }
        });
    },

    // Fixed submitEdittedLeave method
    submitEdittedLeave() {
      if (!this.$refs.editLeaveForm.validate()) {
        this.$toastr.error('Please fill in all required fields correctly');
        return;
      }

      const formData = new FormData();

      // Append basic fields
      formData.append('leave_type_id', this.selectedLeave.leave_type_id);
      formData.append('from', this.selectedLeave.from);
      formData.append('to', this.selectedLeave.to);
      formData.append('days', this.selectedLeave.days);
      formData.append('manager', this.selectedLeave.manager);
      formData.append('hod', this.selectedLeave.hod);
      formData.append('phone', this.selectedLeave.phone);
      formData.append('comment', this.selectedLeave.comment || '');

      // Handle file upload
      if (this.selectedLeave.document && this.selectedLeave.document instanceof File) {
        console.log('Appending file for edit:', this.selectedLeave.document.name);
        formData.append('document', this.selectedLeave.document);
      }

      // Handle tasks - filter out empty tasks
      if (this.selectedLeave.tasks) {
        const validTasks = this.selectedLeave.tasks.filter(task =>
          task.assignee_id && task.task_description && task.task_description.trim()
        );

        if (validTasks.length > 0) {
          formData.append('delegatedTasks', JSON.stringify(validTasks));
        }
      }

      // Add method spoofing for Laravel PUT request with FormData
      formData.append('_method', 'PUT');

      console.log('Edit FormData contents:');
      for (let pair of formData.entries()) {
        if (pair[1] instanceof File) {
          console.log(pair[0] + ': ', `File - ${pair[1].name} (${pair[1].size} bytes)`);
        } else {
          console.log(pair[0] + ': ', pair[1]);
        }
      }

      // Use POST with _method=PUT for FormData compatibility
      axios.post(`/api/v1/leaves/${this.selectedLeave.id}`, formData, {
        // headers: {
        //   'Content-Type': 'multipart/form-data'
        // },
        // timeout: 30000, // 30 second timeout
      })
        .then(response => {
          this.fetchLeaves();
          this.$toastr.success('Leave updated successfully!');
          this.closeModals();
        })
        .catch(error => {
          console.error('Edit error:', error);

          if (error.response && error.response.data) {
            if (error.response.data.errors) {
              // Handle validation errors
              const errors = error.response.data.errors;
              Object.keys(errors).forEach(key => {
                errors[key].forEach(message => {
                  this.$toastr.error(message);
                });
              });
            } else if (error.response.data.error) {
              this.$toastr.error(error.response.data.error);
            } else if (error.response.data.message) {
              this.$toastr.error(error.response.data.message);
            }
          } else {
            this.$toastr.error('An error occurred while updating the leave');
          }
        });
    },

    // Add this method to reset the form
    resetNewLeaveForm() {
      this.newLeave = {
        leave_type_id: null,
        from: null,
        to: null,
        phone: null,
        days: null,
        manager: null,
        hod: null,
        document: null,
        comment: null,
        follower: null,
        task: '',
        delegatedTasks: [
          { assignee_id: null, task_description: '' },
        ],
      };

      // Reset file input
      if (this.$refs.documentInput) {
        this.$refs.documentInput.reset();
      }

      // Reset form validation
      if (this.$refs.createLeaveForm) {
        this.$refs.createLeaveForm.resetValidation();
      }
    },
    getDocumentUrl(documentName) {
      return `/storage/leave/documents/${documentName}`;
    },


    submitCancelLeave() {
      const leaveId = this.selectedLeave.id;
      const cancelComment = this.cancelComment.trim();

      if (!cancelComment || !cancelComment.split(/\s+/).some(word => word.length > 0)) {
        this.showErrorToast('Cancellation reason should contain at least one word');
        return;
      }
      const formData = new FormData();
      formData.append('user_id', this.userId);
      formData.append('comment', cancelComment);

      axios.put(`/api/v1/leaves/cancel/${leaveId}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
        .then(response => {
          this.showSuccessToast(response.data.message);
          this.fetchLeaves();
          this.closeCancelModals();
        })
        .catch(error => {
          this.showErrorToast('Error cancelling leave.');
          console.error('Cancel leave error:', error);
        });
    },
    viewLeave(item) {
      this.selectedItem = item;
      this.viewLeaveModal = true;
    },
    closeLeaveViewModal() {
      this.viewLeaveModal = false;
    },
    capitalizeEachWord(string) {
      return string.replace(/\b\w/g, c => c.toUpperCase());
    },

    showErrorToast(message) {
      this.$toastr.error(message);
    },

    showSuccessToast(message) {
      this.$toastr.success(message);
    },
    closeCancelModals() {
      this.cancelLeaveModal = false;
      this.cancelComment = '';
      this.selectedLeave = {};
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
    // addTask() {
    //   this.newLeave.delegatedTasks.push({ assignee_id: null, task_description: '' });
    // },
    // removeTask(index) {
    //   this.newLeave.delegatedTasks.splice(index, 1);
    // },

    // Method to add a task in edit leave
    addEditTask() {
      // Make sure tasks array exists
      if (!this.selectedLeave.tasks) {
        this.$set(this.selectedLeave, 'tasks', []);
      }

      // Add new task
      this.selectedLeave.tasks.push({
        assignee_id: null,
        task_description: ''
      });
    },

    // Method to remove a task in edit leave
    removeEditTask(index) {
      if (!this.selectedLeave.tasks) {
        return;
      }

      if (this.selectedLeave.tasks.length > 1) {
        this.selectedLeave.tasks.splice(index, 1);
      } else {
        // If it's the last task, just clear it
        this.selectedLeave.tasks[0] = {
          assignee_id: null,
          task_description: ''
        };
      }
    },
    // Method to add a task in apply leave
    addNewTask() {
      if (!this.newLeave.delegatedTasks) {
        this.newLeave.delegatedTasks = [];
      }

      this.newLeave.delegatedTasks.push({
        assignee_id: null,
        task_description: ''
      });
    },

    // Method to remove a task in apply leave
    removeNewTask(index) {
      if (!this.newLeave.delegatedTasks) {
        return;
      }

      this.newLeave.delegatedTasks.splice(index, 1);
    }
  }
};
</script>
