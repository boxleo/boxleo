<template>
  <v-container fluid>
    <v-layout>

      <v-navigation-drawer v-model="drawer" temporary location="right" width="500">
        <v-card>
          <v-card-title>Filter Requisitions</v-card-title>
          <v-card-text>

            <v-col class="mt-lg-5">
              <v-autocomplete v-model="filterOptions.item_names" :items="availableItems" label="Item Name"
                item-title="name" variant="outlined" multiple clearable />
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
                <v-autocomplete v-model="filterOptions.approver_types" :items="approverTypeOptions"
                  label="Type of Approval" item-value="name" item-title="name" variant="outlined" multiple clearable>
                </v-autocomplete>
              </v-col>
              <v-col>
                <!-- <v-date-picker v-model="filterOptions.date_created" label="Date Created" prepend-icon="mdi-calendar"
                  clearable range></v-date-picker> -->

                <v-text-field v-model="filterOptions.date_created" type="date" label="Date Created" clearable
                  variant="outlined">
                </v-text-field>
              </v-col>

              <!-- input type date  -->
              <v-col>
                <v-text-field v-model="filterOptions.date_paid" type="date" label="Date Paid" clearable
                  variant="outlined">
                </v-text-field>
              </v-col>

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
            <!-- <v-btn v-if="stats.totalRequisitions >= 0" @click="filterAllRequisitions" color="secondary" outlined>
              <v-icon class="mr-1">mdi-refresh</v-icon>
              All Requisitions: {{ stats.totalRequisitions }}
            </v-btn> -->
            <!-- <v-btn v-if="stats.pending > 0" @click="filterPending" color="orange" outlined class="mx-1">
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
            </v-btn> -->
            <v-btn @click="openRequestModal" color="primary" outlined class="mx-1">
              <v-icon>mdi-plus</v-icon>
              New Request
            </v-btn>

            <v-btn v-if="permissions.includes('add account')" @click="openAccountModal" color="primary" outlined
              class="mx-1">
              <v-icon>mdi-account-plus</v-icon>
              Add Account
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

        <!-- inlude a tab for Current and ALL -->
        <v-card class="mt-2">
          <v-progress-linear v-if="loading" color="green" indeterminate></v-progress-linear>


          <v-tabs v-model="tab" align-tabs="start" color="primary">
            <v-tab :value="1">Current</v-tab>
            <v-tab :value="2">All</v-tab>
          </v-tabs>

          <v-data-table :headers="headers" :items="filteredRequisitions" :search="search" item-key="id" responsive
            show-select v-model:expanded="expandedItems" show-expand>


            <!-- Special Instructions Column -->
            <template v-slot:item.special_instructions="{ item }">
              <span v-if="item.special_instructions.length < 50">
                {{ item.special_instructions }}
              </span>
              <span v-else>
                {{ item.special_instructions.substring(0, 50) }}...
                <v-icon @click="toggleExpand(item)">mdi-chevron-down</v-icon>
              </span>

            </template>

            <!-- Items Column with Expand Button -->
            <template v-slot:item.items="{ item }">
              <v-btn color="primary" variant="tonal" size="small" @click="toggleExpand(item)" density="comfortable">
                <v-icon :icon="expandedItems.includes(item.id) ? 'mdi-chevron-up' : 'mdi-chevron-down'" size="small"
                  class="mr-1"></v-icon>
                {{ item.items.length }} Items
              </v-btn>
            </template>

            <!-- ✅ Combined Expanded Row Slot -->
            <template v-slot:expanded-row="{ item }">
              <tr>
                <td :colspan="headers.length">
                  <div class="pa-3">
                    <strong>Special Instructions:</strong> {{ item.special_instructions }}
                  </div>

                  <v-divider></v-divider>

                  <v-list dense>
                    <v-list-item v-for="(detail, index) in item.items" :key="index">
                      <v-list-item-content>
                        <v-list-item-title>{{ detail.name }}</v-list-item-title>
                        <v-list-item-subtitle>
                          Quantity: {{ detail.quantity }}, Unit Cost: {{ detail.unit_cost }}, Total: {{
                            detail.total_cost }}
                        </v-list-item-subtitle>

                        <v-list-item-subtitle>

                          <v-list-item-title>Description: {{ detail.description }}</v-list-item-title>
                        </v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                  </v-list>
                </td>
              </tr>
            </template>



            <template v-slot:item.status="{ item }">
              <v-chip :color="getStatusColor(item.status)" dark @click="openStatusDialog(item)">{{ item.status
                }}</v-chip>
            </template>

            <template v-slot:item.pop="{ item }">
              <v-chip color="primary" @click="openPopDialog(item)">{{ item.pop
                }}

              </v-chip>
            </template>

            <template v-slot:item.comment="{ item }">
              <v-chip color="primary" @click="openCommentsDialog(item)">{{ item.comment }}</v-chip>
            </template>

            <template v-slot:item.actions="{ item }">
              <div class="action-icons">

                <!-- make a comment  -->

                <v-icon @click="openCommentsDialog(item)" color="primary" style="margin-right: 8px;"
                  title="Add Comments">
                  mdi-message-text >
                </v-icon>

                <!-- edit requisition -->
                <v-icon
                  v-if="item.status == 'Pending' || item.status == 'Manager Approved' || item.status == 'HR Approved'"
                  @click="openEditRequisitionModal(item)" color="primary" style="margin-right: 8px;"
                  title="Edit Requisition">
                  mdi-pencil
                </v-icon>


                <v-icon @click="openLogsModal(item)" color="primary" style="margin-right: 8px;"
                  title="View Logs">mdi-history</v-icon>
                <v-icon v-if="permissions.includes('view requisition')" @click="viewRequisition(item)" color="info"
                  title="View Requisition">
                  mdi-eye-check-outline
                </v-icon>
                <v-icon v-if="permissions.includes('approve requisition')" @click="OpenapproveRequisitionModal(item)"
                  color="success" title="Approve Requisition">
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

                <v-icon v-if="permissions.includes('mark as paid')" @click="markAsPaid(item)" color="blue"
                  title="Mark as Paid">
                  mdi-cash-check
                </v-icon>

              </div>
            </template>
          </v-data-table>
        </v-card>



        <!-- Status Dialog -->
        <v-dialog v-model="statusDialog" max-width="500px">
          <v-card>
            <v-card-title class="headline">Update Status</v-card-title>
            <v-card-text>
              <v-autocomplete v-model="selectedItem.status" :items="statusOptions" label="Status" item-value="name"
                item-title="name" variant="outlined" clearable>
              </v-autocomplete> </v-card-text>
            <v-card-actions>
              <v-btn text @click="statusDialog = false">Cancel</v-btn>
              <v-btn color="primary" @click="confirmStatusUpdate(selectedItem)">Save</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Comments Dialog -->
        <v-dialog v-model="commentsDialog" max-width="500px">
          <v-card>
            <v-card-title class="headline">Update Comments</v-card-title>
            <v-card-text>
              <v-textarea v-model="selectedItem.comment" label="Comments"></v-textarea>
            </v-card-text>
            <v-card-actions>
              <v-btn text @click="commentsDialog = false">Cancel</v-btn>
              <v-btn color="primary" @click="confirmCommentsUpdate(selectedItem)">Save</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Pop Item Dialog -->
        <v-dialog v-model="popDialog" max-width="500px">
          <v-card>
            <v-card-title class="headline">POP </v-card-title>
            <v-card-text>
              <v-textarea v-model="selectedItem.pop" label="MPESA CODE"></v-textarea>
            </v-card-text>
            <v-card-actions>
              <v-btn text @click="popDialog = false">Cancel</v-btn>
              <v-btn color="primary" @click="confirmPopUpdate(selectedItem)">Save</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>



        <!-- openAccountModal -->
        <v-dialog v-model="accountModal" max-width="600px" persistent>
          <v-card>
            <v-card-title class="headline mb-3">
              <v-icon color="primary">mdi-account-plus</v-icon>
              Add Account
            </v-card-title>
            <v-card-text>
              <v-text-field v-model="availableItem" label="Account Name" />
            </v-card-text>
            <v-card-actions class="justify-end">
              <v-btn @click="closeAccountModal">Cancel</v-btn>
              <v-btn color="primary" @click="saveAccount">Save</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>




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
              <v-btn color="success" @click="approveRequisition(selectedRequisition)">Yes, Approve
                <v-progress-circular v-if="isLoading" class="ml-2" color="primary" indeterminate
              size="24"></v-progress-circular>
              </v-btn>
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
                        <v-autocomplete v-model="item.name" :items="availableItems" item-title="name" density="compact"
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

                <v-textarea v-model="specialInstructions" label="Mode of Payment" rows="3" outlined />

                <v-select v-model="approverType" :items="['Finance Manager', 'CFO', 'HR', 'Welfare']"
                  label="Select Approver Type" outlined dense></v-select>

              </template>

              <!-- Step 3: Finalize -->

              <template v-slot:item.3>
                <p>Review your requisition and click 'Submit' to finalize.</p>
                <v-btn @click="saveRequisition" color="success">Save</v-btn>

                <!-- <v-btn @click="saveRequisition" color="success">
                     {{ selectedItem ? "Update" : "Submit" }}
                          </v-btn> -->

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

import { ref } from "vue";

const tab = ref(null)

export default {
  props: {
    user: Object,
    roles: Array,
    permissions: Array
  },

  data() {
    return {
      tab: 1,

      expandedItems: [],

      // selectedItem: null,

      statusDialog: false,
      commentsDialog: false,
      EditRequisitionModa: false,
      isLoading: false,
      popDialog: false,
      selectedItem: {},
      drawer: false,
      menu: false,
      accountModal: false,
      availableItem: null,


      // Filter options
      filterOptions: {
        item_names: [],
        department_ids: [],
        statuses: [],
        date_created: null,
        approver_types: [],
        date_paid: null,

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
      //  v-model="selected"

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
        // {title: "Description", value: "description" },
        { title: "Special instructions", value: "special_instructions" },
        { title: "Department", value: "user.department.name" },
        { title: "Requester", value: "user.firstname" },
        { title: "Status", value: "status" },
        // { title: "Comments", value: "comment" },
        { title: "Type", value: "approver_type" },
        {
          title: "POP", value: "pop"

        },
        { title: "Actions", value: "actions", sortable: true },
      ],
      loading: false,
    };
  },
  mounted() {
    console.log("User:", this.user);
    console.log("Roles:", this.roles);
    console.log("Permissions:", this.permissions);
    // this.fetchStats();
    this.fetchRequisitions();
    this.fetchDepartments();
    this.fetchAccounts();
  },

  computed: {


  filteredRequisitions() {
    // First apply tab-based filtering (current vs all)
    let result = this.tab === 1
      ? this.requisitions.filter(req => {
          const today = new Date().toISOString().slice(0, 10); // Format: YYYY-MM-DD
          return req.created_at.slice(0, 10) === today;
        })
      : this.requisitions; // Show all when tab is 2

    // Then apply role-based filtering if needed
    // if (this.user) {
    //   if (this.user.is_cfo) {
    //     result = result.filter(req => req.status === 'COO Approved');
    //   } else if (this.user.is_coo) {
    //     result = result.filter(req => req.status === 'HR Approved');
    //   } else if (this.user.is_hr) {
    //     result = result.filter(req =>
    //       req.status === 'Manager Approved' ||
    //       req.status === 'Finance Manager Approved'
    //     );
    //   }
    //   // Finance Manager sees all requisitions, so no additional filtering needed
    //   // Regular users also see all requisitions that pass the tab filter
    // }

    return result;
  }
},

  methods: {


    toggleExpand(item) {
      const index = this.expandedItems.indexOf(item.id);
      if (index !== -1) {
        this.expandedItems.splice(index, 1); // Remove if already expanded
      } else {
        this.expandedItems.push(item.id); // Add if collapsed
      }
    },
    openEditRequisitionModal(item) {
      this.selectedItem = item;
      axios
        .get(`/api/v1/requisition/${item.id}`)
        .then((response) => {
          const requisition = response.data.data;
          this.requisitionItems = requisition.items || [];
          this.specialInstructions = requisition.special_instructions || "";
          this.approverType = requisition.approver_type || null;
          this.requestModal = true;
        })
        .catch((error) => {
          console.error("Error fetching requisition:", error);
        });
    }
    ,

    getStatusColor(status) {
      switch (status) {
        case 'Pending':
          return '#2196F3'; // Blue
        case 'Approved':
          return '#FF9800'; // Orange
        case 'Manager Approved':
          return '#8BC34A'; // Light Green
        case 'Closed':
          return '#F44336'; // Red
        case 'HR Approved':
          return '#9C27B0'; // Purple
        case 'Finance Manager Approved':
          return '#3F51B5'; // Indigo
        case 'Cancelled':
          return '#F44336'; // Red (same as Closed for consistency)
        case 'COO Approved':
          return '#9E9E9E'; // Grey
        case 'Paid':
          return '#4CAF50'; // Green
        default:
          return '#B0BEC5'; // Light Grey for unknown status
      }
    },
    fetchAccounts() {
      axios
        .get(`/api/v1/accounts`)
        .then(response => {
          this.availableItems = response.data.accounts;
        })
        .catch(error => {
          console.error("Error fetching accounts:", error);
        });
    },

    saveAccount() {

      axios
        .post(`/api/v1/accounts`, { name: this.availableItem }) // Ensure you're sending the correct field
        .then((response) => {
          console.log(response);
          this.$toastr.success("Account updated successfully!");
          this.accountModal = false;
        })
        .catch((error) => {
          console.error("Error updating account:", error);
          this.$toastr.error("Failed to update account. Please try again.");
        });
    },

    openStatusDialog(item) {
      this.selectedItem = item;
      this.statusDialog = true;
    },
    openCommentsDialog(item) {
      this.selectedItem = item;
      this.commentsDialog = true;
    },
    openPopDialog(item) {
      this.selectedItem = item;
      this.popDialog = true;
    },
    confirmStatusUpdate(item) {
      axios
        .put(`/api/v1/update/${item.id}`, {
          status: item.status,
        })
        .then((response) => {
          console.log(response);
          this.fetchRequisitions();
        })
        .catch((error) => {
          console.log(error);
        });
      this.statusDialog = false;
    },
    confirmCommentsUpdate(item) {
      axios
        .put(`/api/v1/update/${item.id}`, {
          comment: item.comment,
        })
        .then((response) => {
          console.log(response);
          this.fetchRequisitions();
        })
        .catch((error) => {
          console.log(error);
        });
      this.commentsDialog = false;
    },
    confirmPopUpdate(item) {
      axios
        .put(`/api/v1/update/${item.id}`, {
          pop: item.pop,
        })
        .then((response) => {
          console.log(response);
          this.$toastr.success("Requisition updated successfully");
          this.fetchRequisitions();
          this.popDialog = false;
        })
        .catch((error) => {
          if (error.response && error.response.data.error === "A requisition with the same POP already exists.") {
            this.$toastr.error("A requisition with the same POP already exists.");
          } else {
            this.$toastr.error("Failed to update requisition. Please try again.");
            console.log(error);
          }
        });
    },

    closeAccountModal() {
      this.accountModal = false;
    },

    openAccountModal() {
      this.accountModal = true;
    },
    markAsPaid(item) {
      axios.put(`/api/v1/update/${item.id}`,
        {
          paid: true // Ensure 'paid' is sent as true
        })
        .then(response => {
          this.$toastr.success(response.data.message || 'Requisition marked as paid successfully');
          this.fetchRequisitions();
        })
        .catch(error => {
          console.error('Error marking requisition as paid:', error.response?.data || error.message);
          this.$toastr.error(
            error.response?.data?.error || 'Failed to mark requisition as paid. Please try again.'
          );
        });
    },
    downloadReport() {
      axios.post('/api/v1/download-requisitions-report', { requisitions: this.requisitions }, { responseType: 'blob' })
        .then(response => {
          // Create a blob from the response data
          const blob = new Blob([response.data], { type: 'application/pdf' });

          // Create a link element to trigger the download
          const link = document.createElement('a');
          link.href = window.URL.createObjectURL(blob);
          link.download = 'requisition_invoice.pdf';

          // Append the link to the document and trigger the click event
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);

          this.$toastr.success('PDF file downloaded successfully');
        })
        .catch(error => {
          this.$toastr.error('Error generating PDF file');
          console.error('Error generating PDF file:', error);
        });
    }
    ,
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
        this.isLoading = true;
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
        })
        .finally(() => {
        this.isLoading = false; // Ensure loader stops
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



    // async fetchRequisitions() {
    //   this.loading = true;
    //   try {
    //     const response = await axios.get("/api/v1/requisitions");
    //     this.requisitions = response.data.requisitions;

    //     if (this.user) {
    //       console.log("User is logged in:", this.user);

    //       // Filter requisitions based on user role
    //       if (this.user.is_cfo) {
    //         console.log("User is CFO");
    //         // CFO should see requisitions with status "COO Approved"
    //         this.filteredRequisitions = this.requisitions.filter(req =>
    //           req.status === 'COO Approved'
    //         );
    //         console.log("Requisitions for CFO:", this.filteredRequisitions);
    //       }
    //       else if (this.user.is_coo) {
    //         console.log("User is COO");
    //         // COO should see requisitions with status "HR Approved"
    //         this.filteredRequisitions = this.requisitions.filter(req =>
    //           req.status === 'HR Approved'
    //         );
    //         console.log("Requisitions for COO:", this.filteredRequisitions);
    //       }
    //       else if (this.user.is_hr) {
    //         console.log("User is HR");
    //         // HR should see Manager Approved and Finance Manager Approved
    //         this.filteredRequisitions = this.requisitions.filter(req =>
    //           req.status === 'Manager Approved' ||
    //           req.status === 'Finance Manager Approved'
    //         );
    //         console.log("Requisitions for HR:", this.filteredRequisitions);
    //       }
    //       else if (this.user.is_finance_manager) {
    //         console.log("User is Finance Manager");
    //         // Finance Manager should see all requisitions
    //         this.filteredRequisitions = this.requisitions;
    //         console.log("All requisitions for Finance Manager:", this.filteredRequisitions);
    //       }
    //       else {
    //         console.log("User has no special role");
    //         // For users with no special roles, show pending requisitions
    //         this.filteredRequisitions = this.requisitions;
    //         console.log("Pending requisitions for regular user:", this.filteredRequisitions);
    //       }
    //     } else {
    //       console.log("No user logged in");
    //       this.filteredRequisitions = [];
    //     }

    //     ;

    //     return this.filteredRequisitions;
    //   } catch (error) {
    //     console.error("Error fetching requisitions:", error);
    //     this.filteredRequisitions = [];
    //     return [];
    //   } finally {
    //     this.loading = false;
    //   }
    // },


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
      console.log("Selected Item:", JSON.stringify(this.selectedItem, null, 2));

      const payload = {
        items: this.requisitionItems,
        special_instructions: this.specialInstructions,
        approver_type: this.approverType,
        user_id: this.user.id,

      };

      if (this.selectedItem && this.selectedItem.id) {
        // Updating requisition
        axios
          .put(`/api/v1/update/${this.selectedItem.id}`, payload)
          .then((response) => {
            this.$toastr.success("Requisition updated successfully");
            this.requestModal = false;
            this.fetchRequisitions(); // Refresh list
          })
          .catch((error) => {
            console.error("Error updating requisition:", error);
          });
      } else {
        // Creating requisition
        axios
          .post("/api/v1/requisitions", payload)
          .then((response) => {
            this.$toastr.success("Requisition created successfully");
            this.requestModal = false;
            this.fetchRequisitions(); // Refresh list
          })
          .catch((error) => {
            console.error("Error creating requisition:", error);
          });
      }
    }
    ,
    openRequestModal() {
      this.requestModal = true;
    },
    // closeRequestModal() {
    //   this.requestModal = false;
    //   this.step = 1; // Reset stepper
    // },

    closeRequestModal() {
      this.requestModal = false;
      this.selectedItem = null;
      this.requisitionItems = [];
      this.specialInstructions = "";
      this.approverType = null;
    }
    ,
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
