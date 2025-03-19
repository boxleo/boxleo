<template>
  <v-container fluid>
    <v-layout>

      <v-navigation-drawer v-model="drawer" temporary location="right" width="500">
        <v-card>
          <v-card-title>Filter Requisitions</v-card-title>
          <v-card-text>
            <v-col class="mt-lg-5">
              <v-autocomplete v-model="filterOptions.item_names" :items="availableItems" label="Item Name"
                variant="outlined" multiple clearable />
            </v-col>
            <v-col>
              <v-autocomplete v-model="filterOptions.department_ids" :items="departments" label="Department"
                variant="outlined" multiple item-title="name" item-value="id" clearable />
            </v-col>
            <v-col>
              <v-autocomplete v-model="filterOptions.statuses" :items="statusOptions" multiple label="Status"
                item-value="name" item-title="name" variant="outlined" clearable>
              </v-autocomplete>
            </v-col>
            <v-col>
              <v-col>
                <v-date-picker v-model="filterOptions.date_created" label="Date Created" prepend-icon="mdi-calendar" clearable range></v-date-picker>
              </v-col>

            </v-col>
            <v-col>
              <v-autocomplete v-model="filterOptions.approver_types" :items="approverTypeOptions"
                label="Type of Approval" item-value="name" item-title="name" variant="outlined" multiple clearable>
              </v-autocomplete>
            </v-col>
            <v-col class="d-flex justify-end">
              <v-btn color="dark" @click.prevent="filterRequisitions">
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
        <!-- Filters and Actions -->
        <v-row class="my-3">

          <div class="d-flex justify-center">
            <v-btn v-if="stats.totalRequisitions >= 0" @click="filterAllRequisitions" color="secondary" outlined>
              <v-icon class="mr-1">mdi-refresh</v-icon>
              All Requisitions: {{ stats.totalRequisitions }}
            </v-btn>
            <v-btn v-if="stats.pending > 0" @click="filterPending" color="orange" outlined class="mx-1">
              <v-icon>mdi-clock</v-icon>
              Pending: {{ stats.pending }}
            </v-btn>
            <v-btn v-if="stats.approved > 0" @click="filterApproved" color="green" outlined class="mx-1">
              <v-icon>mdi-check</v-icon>
              Approved: {{ stats.approved }}
            </v-btn>
            <v-btn v-if="stats.rejected > 0" @click="filterRejected" color="red" outlined class="mx-1">
              <v-icon>mdi-cancel</v-icon>
              Rejected: {{ stats.rejected }}
            </v-btn>
            <v-btn @click="openRequestModal" color="primary" outlined class="mx-1">
              <v-icon>mdi-plus</v-icon>
              New Request
            </v-btn>


            <!-- include filter icon  and report download  -->

            <v-btn @click="drawer = !drawer" color="primary" outlined class="mx-1">
              <v-icon>mdi-filter</v-icon>
              Filter
            </v-btn>
            <v-btn @click="downloadReport" color="primary" outlined class="mx-1">
              <v-icon>mdi-download</v-icon>
              Download Report
            </v-btn>



          </div>
        </v-row>


        <v-row>
          <v-col>
            <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" single-line
              hide-details></v-text-field>
          </v-col>
        </v-row>

        <!-- Data Table -->
        <v-card class="mt-2">
          <v-progress-linear v-if="loading" color="green" indeterminate></v-progress-linear>


          <v-data-table v-model="selected" :headers="headers" :items="requisitions" :search="search" item-key="id"
            responsive show-select>


            <!-- Slot for Items Column -->
            <template v-slot:item.items="{ item }">
              <v-list dense>
                <v-list-item v-for="(itemDetail, index) in item.items" :key="index">
                  <v-list-item-content>
                    <v-list-item-title>{{ itemDetail.name }}</v-list-item-title>
                    <v-list-item-subtitle>
                      Quantity: {{ itemDetail.quantity }}, Unit Cost: {{ itemDetail.unit_cost }}, Total: {{
                        itemDetail.total_cost }}
                    </v-list-item-subtitle>
                  </v-list-item-content>
                </v-list-item>
              </v-list>
            </template>

            <template v-slot:item.actions="{ item }">
              <div class="action-icons">

                <v-icon @click="openLogsModal(item)" color="primary" style="margin-right: 8px;"
                  title="View Logs">mdi-history</v-icon>
                <v-icon @click="viewRequisition(item)" color="info" title="View Requisition">
                  mdi-eye-check-outline
                </v-icon>
                <v-icon v-if="roles.includes('admin')" @click="OpenapproveRequisitionModal(item)" color="success"
                  title="Approve Requisition">
                  mdi-thumb-up-outline
                </v-icon>
                <v-icon v-if="roles.includes('admin')" @click="OpencancelRequisitionModal(item)" color="error"
                  title="Reject Requisition">
                  mdi-close-circle
                </v-icon>
                <v-icon v-if="roles.includes('admin')" @click="deleteRequisition(item)" color="error"
                  title="Delete Requisition">
                  mdi-delete
                </v-icon>

                <v-icon @click="markAsPaid(item)" color="blue" title="Mark as Paid">
                  mdi-cash-check
                </v-icon>

              </div>
            </template>
          </v-data-table>
        </v-card>


        <!-- requisition history -->

        <v-dialog v-model="logsModal" max-width="700px" full-height top>
          <v-card class="elevation-10">
            <v-card-title class="headline">
              <v-icon color="primary">mdi-history</v-icon>
              Requisition Logs
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text>
              <!-- Loading Indicator -->
              <v-progress-circular v-if="loadingLogs" indeterminate color="primary" />

              <!-- Logs List -->
              <v-list dense v-else>
                <v-list-item-group>
                  <v-list-item v-for="(log, index) in logs" :key="index">
                    <v-list-item-content>
                      <v-list-item-title class="mb-3">
                        <v-icon color="secondary" class="mr-1">mdi-account-circle</v-icon>
                        <strong>User:</strong> {{ log.user }}
                      </v-list-item-title>
                      <v-list-item-title class="mb-3">
                        <v-icon color="secondary" class="mr-1">mdi-check-circle-outline</v-icon>
                        <strong>Action:</strong> {{ log.action }}
                      </v-list-item-title>
                      <v-list-item-subtitle>
                        <v-icon color="secondary" class="mr-1">mdi-clock-time-eight</v-icon>
                        <strong>Time:</strong> {{ log.time }}
                      </v-list-item-subtitle>
                    </v-list-item-content>
                  </v-list-item>
                </v-list-item-group>
              </v-list>
            </v-card-text>
            <v-divider></v-divider>
            <v-card-actions>
              <v-btn color="primary" @click="closelogsModal" outlined>
                <v-icon left>mdi-close-circle-outline</v-icon> Close
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>



        <!-- Cancel Requisition Dialog -->
        <v-dialog v-model="cancelRequisitionModal" max-width="600px" persistent>
          <v-card>
            <v-card-title class="headline mb-3">
              <v-icon color="warning">mdi-alert-circle-outline</v-icon>
              Cancel Requisition
            </v-card-title>
            <v-card-text>
              Are you sure you want to cancel this requisition?
              <v-textarea v-model="comment" label="Notes" hint="Add any additional notes or comments"></v-textarea>
            </v-card-text>
            <v-card-actions class="justify-end">
              <v-btn @click="ClosecancelRequisitionModal">No</v-btn>
              <v-btn color="warning" @click="cancelRequisitionAction">Yes, Cancel</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>


        <!-- Approve Leave Dialog -->
        <v-dialog v-model="approveRequisitionModal" max-width="600px" persistent>
          <v-card>
            <v-card-title class="headline mb-3">
              <v-icon color="success">mdi-check-circle-outline</v-icon>
              Approve Requisition
            </v-card-title>
            <v-card-text>
              Are you sure you want to approve this requisition?

              <!-- Text area for adding notes -->
              <v-textarea v-model="comment" label="Notes" hint="Add any additional notes or comments"></v-textarea>
            </v-card-text>
            <v-card-actions class="justify-end">
              <v-btn @click="CloseapproveRequisitionModal">No</v-btn>
              <v-btn color="success" @click="approveRequisition(selectedRequisition)">Yes, Approve</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>


        <!-- Request Modal with Steppers -->
        <v-dialog v-model="requestModal" :max-width="(step === 1) ? 1500 : 800" persistent>
          <v-card>
            <v-stepper v-model="step" :items="items" show-actions>
              <!-- Stepper Items -->
              <template v-slot:item.1>

                <!-- Step 1: Requisition Items -->
                <v-table>
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Description</th>
                      <th>Quantity</th>
                      <th>Unit Cost</th>
                      <th>Total</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in requisitionItems" :key="item.id">
                      <td>
                        <!-- <v-text-field v-model="item.name" item-title="name" density="compact" hide-details /> -->
                        <v-select v-model="item.name" :items="availableItems" item-title="name" density="compact"
                          hide-details />

                      </td>

                      <td>
                        <v-text-field v-model="item.description" item-title="description" density="compact"
                          hide-details />
                      </td>
                      <td>
                        <v-text-field v-model.number="item.quantity" type="number" density="compact" hide-details
                          :rules="[v => v > 0 || 'Quantity must be greater than 0']" />
                      </td>
                      <td>
                        <v-text-field v-model.number="item.unit_cost" type="number" density="compact" hide-details
                          :rules="[v => v >= 0 || 'Unit price must be non-negative']" />
                      </td>
                      <!-- <td>{{ item.quantity * item.unit_cost }}</td> -->
                      <td>{{ item.total_cost }}</td>

                      <td>
                        <v-icon color="error" size="small" @click="removeItem(item)">mdi-delete</v-icon>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="5">
                        <v-btn color="primary" @click="addItem">Add Item</v-btn>
                      </td>
                    </tr>
                  </tfoot>
                </v-table>
              </template>

              <!-- Step 2: Special Instructions -->
              <template v-slot:item.2>

                <v-textarea v-model="specialInstructions" label="Special Instructions" rows="3" outlined />

                <v-select v-model="approverType" :items="['Finance Manager', 'CFO', 'HR']" label="Select Approver Type"
                  outlined dense></v-select>

              </template>

              <!-- Step 3: Finalize -->

              <template v-slot:item.3>
                <p>Review your requisition and click 'Submit' to finalize.</p>
                <v-btn @click="saveRequisition" color="success">Submit</v-btn>

              </template>

              <!-- </v-card-text> -->
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue-darken-1" variant="text" @click="closeRequestModal">Close</v-btn>
              </v-card-actions>
            </v-stepper>
          </v-card>
        </v-dialog>
      </v-main>
    </v-layout>
  </v-container>

</template>

<script>
export default {


  props: {
    user: Object,
    roles: Array,
    permissions: Array
  },


  data() {
    return {
      drawer: false,
      menu: false,

      // Filter options
      filterOptions: {
        item_names: [],
        department_ids: [],
        statuses: [],
        date_created: null,
        approver_types: [],

      },
      modalWidth: 1500,
      step: 1,
      items: [
        'Requisition Items ',
        'Special instructions',
        'Submit',
      ],

      stats: {
        totalRequisitions: 0,
        pending: 0,
        approved: 0,
        rejected: 0,
      },
      selectedRequisition: "",
      requisition_id: "",
      specialInstructions: "",
      approverType: "",
      comment: "",
      requisitions: [],

      approveRequisitionModal: false,
      cancelRequisitionModal: false,
      logsModal: false,
      loadingLogs: false,

      // Dropdown options
      departments: [],
      statusOptions: [
        { name: 'Pending' },
        { name: 'Approved' },
        { name: 'Manager Approved' },
        { name: 'HR Approved' },
        { name: 'Finance Manager Approved' },
        { name: 'Paid' }

      ],
      approverTypeOptions: [
        { name: 'Finance Manager' },
        { name: 'CFO' },
        { name: 'HR' }
      ],

      availableItems: [
        "Airtime",
        "Fuel - Riders",
        "Welfare - Concerning welfare & kitchen expenses",
        "Subscription",
        "Rent",
        "Stationery",
        "Packaging materials",
        "Fare facilitation",
        "Asset purchase",
        "Fuel - motor vehicle"
      ],
      // availableItem:"",
      requisitionItems: [
        {
          name: "",
          description: "",
          quantity: 1,
          unit_cost: 0,
          total_cost: 0,
        },],
      requestModal: false,
      step: 1,
      search: "",
      selected: [],
      headers: [
        { title: "Requisition ID", value: "id" },
        { title: "Application Date", value: "created_at" },
        { title: "Items", value: "items" },
        { title: "Requisition Total value", value: "items_sum_total_cost" },
        { title: "Special instructions", value: "special_instructions" },
        { title: "Department", value: "user.department.name" },
        { title: "Requester", value: "user.firstname" },
        { title: "Status", value: "status" },
        { title: "Comments", value: "comment" },
        { title: "Type", value: "approver_type" },
        { title: "Actions", value: "actions", sortable: false },
      ],
      loading: false,
    };
  },
  mounted() {
    console.log("User:", this.user);
    console.log("Roles:", this.roles);
    console.log("Permissions:", this.permissions);
    this.fetchStats();
    this.fetchRequisitions();
    this.fetchDepartments();
  },
  methods: {



    fetchDepartments() {
      axios.get('/api/v1/departments')
        .then(response => {
          this.departments = response.data.departments;
        })
        .catch(error => {
          console.error("Error fetching departments:", error);
          this.$toastr.error("Failed to fetch departments");
        });
    },

    // Filter requisitions based on current filter options
    filterRequisitions() {
      this.loading = true;

      axios.post('/api/v1/filter-requisitions', this.filterOptions)
        .then(response => {
          this.requisitions = response.data.requisitions;
          this.drawer = false; // Close drawer after filtering
        })
        .catch(error => {
          console.error("Error filtering requisitions:", error);
          this.$toastr.error("Failed to filter requisitions");
        })
        .finally(() => {
          this.loading = false;
        });
    },
    viewRequisition(item) {
      const requisitionId = item.id;
      const pdfUrl = `/api/v1/requisitions/${requisitionId}/pdf`;
      window.open(pdfUrl, '_blank');
    },
    capitalizeEachWord(text) {
      if (!text) return '';
      return text
        .split(' ')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
    },
    // safeParseDetails(details) {
    //   try {
    //     // Attempt to parse the details if it's valid JSON
    //     return JSON.parse(details);
    //   } catch (e) {
    //     console.warn('Error parsing details:', e.message, 'Details:', details);
    //     // Return the details as a string if parsing fails
    //     return { rawDetails: details };
    //   }
    // },

    closelogsModal() {
      this.logsModal = false
    },
    openLogsModal(requisitionId) {
      this.logsModal = true; // Open the modal
      this.logs = []; // Clear existing logs
      this.loadingLogs = true; // Set loading state

      axios
        .get(`/api/v1/requisitions-logs/${requisitionId.id}`)
        .then((response) => {
          this.logs = response.data.logs; // Update logs
        })
        .catch((error) => {
          console.error("Error fetching logs:", error.response?.data || error.message);
          this.$toastr.error(
            error.response?.data?.error || "Failed to fetch logs. Please try again."
          );
        })
        .finally(() => {
          this.loadingLogs = false; // Remove loading state
        });
    },

    cancelRequisitionAction() {
      // Debug: Log the selected requisition and comment
      // console.log('Selected Requisition:', this.selectedRequisition);
      // console.log('Cancellation Comment:', this.comment);

      // Ensure a requisition is selected
      if (!this.selectedRequisition) {
        this.$toastr.error('No requisition selected for cancellation');
        return;
      }

      // Ensure a comment is provided
      if (!this.comment || this.comment.trim() === '') {
        this.$toastr.error('Please provide a reason or comment for cancellation');
        return;
      }

      // Debug: Log API request details before sending
      console.log(`Sending POST request to /api/v1/cancel-requisition/${this.selectedRequisition.id}`);

      // Send POST request to cancel the requisition
      axios
        .post(`/api/v1/cancel-requisition/${this.selectedRequisition.id}`, {
          comment: this.comment,
        })
        .then(response => {
          // Debug: Log the success response
          console.log('Cancel Requisition Response:', response.data);

          // Close the dialog
          this.cancelRequisitionModal = false;

          // Show success notification
          this.$toastr.success(response.data.message || 'Requisition canceled successfully');

          // Refresh the requisition list
          this.fetchRequisitions();
        })
        .catch(error => {
          // Debug: Log the error response
          console.error('Error canceling requisition:', error.response?.data || error.message);

          // Show error notification
          this.$toastr.error(
            error.response?.data?.error || 'Failed to cancel requisition. Please try again.'
          );
        });
    },

    deleteRequisition(item) {

      // Confirm deletion with the user
      axios
        .delete(`/api/v1/delete-requisition/${item.id}`)
        .then(response => {
          // Show success notification
          this.$toastr.success(response.data.message || 'Requisition deleted successfully');

          // Refresh the list of requisitions
          this.fetchRequisitions();
        })
        .catch(error => {
          // Handle errors
          console.error('Error deleting requisition:', error.response?.data || error.message);

          // Show error notification
          this.$toastr.error(
            error.response?.data?.error || 'Failed to delete requisition. Please try again.'
          );
        });

    },
    OpenapproveRequisitionModal(item) {

      this.selectedRequisition = item;

      this.approveRequisitionModal = true;
    },
    CloseapproveRequisitionModal() {
      this.approveRequisitionModal = false;
    },
    OpencancelRequisitionModal(item) {
      this.selectedRequisition = item;
      this.cancelRequisitionModal = true;
    },
    ClosecancelRequisitionModal() {
      this.cancelRequisitionModal = false;
    },
    approveRequisition(selectedRequisition) {
      // Prepare the approval data
      const approvalData = {
        requisition_id: selectedRequisition.id,
        user_id: this.user.id,
        comment: this.comment,
      };
      axios
        .post('/api/v1/approve-requisition', approvalData)
        .then((response) => {
          console.log('Requisition Approved:', response.data);
          this.$toastr.success('Requisition approved successfully!');
          this.fetchRequisitions();
          this.CloseapproveRequisitionModal();

        })
        .catch((error) => {
          console.error('Error approving requisition:', error.response?.data || error.message);
          this.$toastr.error('Failed to approve requisition. Please try again.');
        });
    },

    async fetchStats() {
      try {
        const response = await axios.get("/api/v1/stats");
        this.stats = response.data;
      } catch (error) {
        console.error("Error fetching stats:", error);
      }
    },
    async fetchRequisitions() {
      this.loading = true;
      try {
        const response = await axios.get("/api/v1/requisitions");
        this.requisitions = response.data.requisitions;
      } catch (error) {
        console.error("Error fetching requisitions:", error);
      } finally {
        this.loading = false;
      }
    },
    nextStep() {
      if (this.step < 3) this.step++;
    },
    prevStep() {
      if (this.step > 1) this.step--;
    },
    addItem() {
      this.requisitionItems.push({
        name: "",
        description: "",
        quantity: 1,
        unit_cost: 0,
        total_cost: 0,
      });
    },
    removeItem(item) {
      this.requisitionItems = this.requisitionItems.filter((i) => i !== item);
    },
    saveRequisition() {


      const requisitionData = {
        items: this.requisitionItems.map((item) => ({
          name: item.name,
          description: item.description,
          quantity: item.quantity,
          unit_cost: item.unit_cost,
          total_cost: item.total_cost, // Use the dynamically updated value
        })),
        user_id: this.user.id,
        special_instructions: this.specialInstructions,
        approver_type: this.approverType,

      };

      console.log('requisitionData', requisitionData);

      // Make the POST request using Axios
      axios
        .post('/api/v1/requisitions', requisitionData)
        .then((response) => {
          console.log('Requisition Saved:', response.data);
          this.$toastr.success('Requisition saved successfully!'); // Optional: Toast notification
          this.requestModal = false; // Close the modal
          this.step = 1; // Reset stepper
          this.requisitionItems = []; // Clear requisition items

          this.fetchRequisitions(); // Fetch requisitions after saving

        })
        // .catch((error) => {
        //   console.error('Error saving requisition:', error.response?.data || error.message);
        //   this.$toastr.error('Failed to save requisition. Please try again.'); // Optional: Error notification
        // });
        .catch((error) => {
          const errorMessage = error.response?.data?.message || "An unexpected error occurred.";
          console.error("Error saving requisition:", errorMessage, error.response?.data);
          this.$toastr.error(errorMessage);
        });
    },
    openRequestModal() {
      this.requestModal = true;
    },
    closeRequestModal() {
      this.requestModal = false;
      this.step = 1; // Reset stepper
    },
    filterAllRequisitions() {
      this.fetchRequisitions();
    },
    filterPending() {
      // Add specific filter logic
      this.fetchRequisitions();
    },
    filterApproved() {
      // Add specific filter logic
      this.fetchRequisitions();
    },
    filterRejected() {
      // Add specific filter logic
      this.fetchRequisitions();
    },
  },
  async created() {
    console.log("User:", this.user);
    console.log("Roles:", this.roles);
    console.log("Permissions:", this.permissions);
    await this.fetchStats();
    await this.fetchRequisitions();
  },
  watch: {
    requisitionItems: {
      handler(newItems) {
        newItems.forEach((item) => {
          item.total_cost = (item.quantity || 0) * (item.unit_cost || 0);
        });
      },
      deep: true, // Watches nested properties
    },
  },
};
</script>
