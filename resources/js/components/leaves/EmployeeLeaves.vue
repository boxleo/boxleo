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
                    <v-file-input v-model="newLeave.document" label="Attach Document (sickleave Must)"
                      accept=".pdf, .doc, .docx, .jpeg, .jpg, .png" variant="outlined" clearable
                      class="rounded-lg"></v-file-input>
                  </v-col>

                  <v-col cols="12">
                    <v-textarea v-model="newLeave.comment" label="Comment (Optional)" variant="outlined" clearable
                      class="rounded-lg"></v-textarea>
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
                    <!-- <v-file-input v-model="selectedLeave.document" label="Attach Document (Optional)"
                      accept=".pdf, .doc, .docx, .png" outlined clearable>
                    </v-file-input> -->


                    <v-file-input v-model="selectedLeave.document" label="Attach Document (Optional)"
                      accept=".pdf, .doc, .docx, .png" outlined clearable @change="handleFileUpload">
                    </v-file-input>

                  </v-col>
                  <v-col cols="12">
                    <v-textarea v-model="selectedLeave.comment" label="Comment (Optional)" outlined clearable>
                    </v-textarea>
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
        phone: null
      },
      logsModal: false,
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
      cancelComment: '',
      editLeaveModal: false,
      cancelLeaveModal: false,
      selectedLeave: {},
      createLeaveModal: false,
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

    handleFileUpload(file) {
      if (file) {
        console.log('File selected:', file); // Debugging
        this.newLeave.document = file;
      } else {
        console.log('No file selected');
      }
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

          console.log("HODs: ", this.hods);
          console.log("Managers: ", this.managers);
        })
        .catch(error => {
          console.error('Error fetching users:', error);
        });
    },

    openEditLeaveModal(leaveId) {
      this.selectedLeave = this.leaves.find(leave => leave.id === leaveId);
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

    submitNewLeave() {
      if (this.$refs.createLeaveForm.validate()) {
        this.isLoading = true;
        const formData = new FormData();
        formData.append('user_id', this.userId);
        formData.append('leave_type_id', this.newLeave.leave_type_id);
        formData.append('from', this.newLeave.from);
        formData.append('to', this.newLeave.to);
        formData.append('phone', this.newLeave.phone);
        formData.append('days', this.newLeave.days);
        formData.append('manager', this.newLeave.manager);
        formData.append('hod', this.newLeave.hod);
        formData.append('comment', this.newLeave.comment);      
        if (this.newLeave.document && this.newLeave.document.length > 0) {
          const file = this.newLeave.document[0]; // Get the first file from the array
          console.log('Appending file:', file);
          formData.append('document', file);
        } else {
          console.log('No file selected');
        }
        axios.post('/api/v1/leaves', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
          .then(response => {

            this.fetchLeaves();
            this.isLoading = false;
            this.$toastr.success(response.data.message);
            this.createLeaveModal = false;
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

    getDocumentUrl(documentName) {
      return `/storage/leave/documents/${documentName}`;
    },
    submitEdittedLeave() {
      if (this.$refs.editLeaveForm.validate()) {
        const formData = {
          leave_type_id: this.selectedLeave.leave_type_id,
          from: this.selectedLeave.from,
          to: this.selectedLeave.to,
          days: this.selectedLeave.days,
          manager: this.selectedLeave.manager,
          hod: this.selectedLeave.hod,
          document: this.selectedLeave.document,
          comment: this.selectedLeave.comment,
          phone: this.selectedLeave.phone,
        };

        const apiUrl = this.base_url + `api/v1/leaves/${this.selectedLeave.id}`;
        axios.put(apiUrl, formData)
          .then(response => {
            this.fetchLeaves();
            this.$toastr.success('Leave updated successfully!');
            this.closeModals();
          })
          .catch(error => {
            this.$toastr.error('Error updating leave!');
          });
      }
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
  },
};
</script>
