<template>
  <v-container-fluid>
    <v-row justify="end" class="text-right">
      <v-col>
        <v-text-field prepend-icon="mdi-magnify" variant="underlined" v-model="search" label="Search" clearable
          @clear="clearSearch"></v-text-field>
      </v-col>
      <v-col cols="auto">
        <v-icon @click="addUserModal = true" color="warning" x-small>mdi-account-plus</v-icon>
      </v-col>
      <v-col cols="auto" v-if="users.length > 0">
        <v-icon v-if="users.length > 0" @click="filterDialog = true" color="primary" x-small>mdi-filter</v-icon>
      </v-col>
      <v-col cols="auto" v-if="users.length > 0">
        <v-icon @click.prevent="downloadExcel" v-if="users.length > 0" color="success">mdi-download</v-icon>
      </v-col>
      <v-col cols="auto">
        <v-icon @click.prevent="refreshUsers" color="danger" x-small>mdi-refresh</v-icon>
      </v-col>
    </v-row>
    <v-row>
      <v-responsive>
        <v-row v-if="selected.length > 0">
          <v-col>
            <v-icon class="toggle-account mx-1" title="Activate Account" @click="toggleAccount(item)"
              color="success">mdi-lock
            </v-icon>
            <v-icon class="mx-1" @click="openRoleSwitchModal(item)" icon title="Switch Role" color="orange">mdi-transfer
            </v-icon>
          </v-col>
        </v-row>
        <v-data-table v-model="selected" :headers="headers" :items="users" item-key="id" :search="search" responsive
          show-select>
          <template v-slot:item.has_biometrics="{ item }">
            <v-icon :color="getStatusColor(item.has_biometrics)" @click="biometricsModal(item)">
              {{ getStatusIcon(item.has_biometrics) }}
            </v-icon>
          </template>

          <template v-slot:item.is_enabled="{ item }" class="account-status">
            <v-icon :color="getStatusColor(item.is_enabled)" @click="toggleAccount(item)">
              {{ getStatusIcon(item.is_enabled) }}
            </v-icon>
          </template>

          <template v-slot:item.actions="{ item }" class="d-flex justify-content-around align-items-center">
            <v-icon @click="editUser(item)" title="Edit User" color="primary">mdi-pencil</v-icon>
            <v-icon class="edit-permissions" title="Edit permissions" @click="openPermissionsModal(item.id)"
              color="info">mdi-shield
            </v-icon>
            <v-icon class="toggle-account" title="Activate Account" @click="toggleAccount(item)"
              color="success">mdi-lock
            </v-icon>
            <v-icon title="Register Biometrics" @click="biometricsModal(item)" color="blue">mdi-fingerprint
            </v-icon>
            <v-icon @click="openRoleSwitchModal(item)" icon title="Switch Role" color="orange">mdi-account-switch
            </v-icon>
            <v-icon @click="deleteUser(item)" title="Delete" class="mx-2 text-danger">mdi-delete
            </v-icon>

            <v-icon @click="impersonateUser(item)" title="Impersonate User" color="purple">
              mdi-account
            </v-icon>

            <!-- add salaryinfo button and appropriate function to salarydialog -->

            <v-icon @click="openSalaryDialog(item)" title="Salary Info" color="primary">
              mdi-cash
            </v-icon>
          </template>
        </v-data-table>
      </v-responsive>
    </v-row>
    <!-- Add User Modal -->
    <v-dialog v-model="addUserModal" max-width="650px" persistent>
      <v-card>
        <v-card-title>Add Employee</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="submitAddUserForm">
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field variant="outlined" v-model="formData.first_name" label="First Name"
                  prepend-icon="mdi-account" :rules="[v => !!v || 'First name is required']">
                </v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field variant="outlined" v-model="formData.last_name" label="Last Name"
                  prepend-icon="mdi-account" :rules="[v => !!v || 'Last name is required']">
                </v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field variant="outlined" v-model="formData.phone" label="Phone" prepend-icon="mdi-phone"
                  :rules="[v => !!v || 'Phone number is required']">
                </v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field variant="outlined" v-model="formData.email" label="Email" prepend-icon="mdi-email"
                  :rules="[v => !!v || 'Email is required']">
                </v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" md="6">
                <v-select variant="outlined" v-model="formData.unit_id" :items="branches" label="Branch" item-value="id"
                  item-title="name" prepend-icon="mdi-domain" :rules="[v => !!v || 'Branch is required']">
                </v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select variant="outlined" v-model="formData.office_id" :items="offices" label="Office"
                  item-value="id" item-title="name" prepend-icon="mdi-briefcase"
                  :rules="[v => !!v || 'Office is required']">
                </v-select>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" md="6">
                <v-select variant="outlined" v-model="formData.department_id" :items="departments" label="Department"
                  item-value="id" item-title="name" prepend-icon="mdi-office-building"
                  :rules="[v => !!v || 'Department is required']">
                </v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select variant="outlined" v-model="formData.designation_id" :items="designations" label="Designation"
                  item-value="id" item-title="name" prepend-icon="mdi-account-tie"
                  :rules="[v => !!v || 'Designation is required']">
                </v-select>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field variant="outlined" v-model="formData.zk_user_id" label="ZK User ID"
                  prepend-icon="mdi-numeric" :rules="[v => !!v || 'ZK User ID is required']">
                </v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field variant="outlined" v-model="formData.zk_username" label="ZK Username"
                  prepend-icon="mdi-account" :rules="[v => !!v || 'ZK Username is required']">
                </v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="6">
                <v-switch v-model="formData.enable_login" label="Enable Login" color="success"
                  :rules="[v => v !== null || 'Please select Enable Login']">
                </v-switch>
              </v-col>
              <v-col cols="6">
                <v-switch v-model="formData.send_logins" label="Send Logins" color="success"
                  :rules="[v => v !== null || 'Please select Send Logins']">
                </v-switch>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-subheader class="font-weight-bold">Gender</v-subheader>
              </v-col>
              <v-col cols="12">
                <v-radio-group v-model="formData.gender" :rules="[v => !!v || 'Please select a gender']">
                  <v-row>
                    <v-col>
                      <v-radio label="Male" value="Male"></v-radio>
                    </v-col>
                    <v-col>
                      <v-radio label="Female" value="Female"></v-radio>
                    </v-col>
                  </v-row>
                </v-radio-group>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-subheader class="font-weight-bold">Role</v-subheader>
              </v-col>
              <v-col cols="12">
                <v-radio-group v-model="formData.role" :rules="[v => !!v || 'Please select a role']">
                  <v-row>
                    <v-col>
                      <v-radio label="Admin" value="admin"></v-radio>
                    </v-col>
                    <v-col>
                      <v-radio label="Employee" value="employee"></v-radio>
                    </v-col>
                  </v-row>
                </v-radio-group>
              </v-col>
            </v-row>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="danger" @click="addUserModal = false">Close</v-btn>
              <v-btn @click="submitAddUserForm" color="primary">
                + Add
              </v-btn>
            </v-card-actions>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Edit User Dialog -->
    <v-dialog v-model="editUserDialog" max-width="650px" persistent>
      <v-card>
        <v-card-title>Edit User</v-card-title>
        <v-card-text>
          <v-form ref="editForm" @submit.prevent="submitEditUserForm">
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field variant="outlined" v-model="editedUser.first_name" label="First Name"
                  prepend-icon="mdi-account" :rules="[v => !!v || 'First name is required']">
                </v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field variant="outlined" v-model="editedUser.last_name" label="Last Name"
                  prepend-icon="mdi-account" :rules="[v => !!v || 'Last name is required']">
                </v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field variant="outlined" v-model="editedUser.phone" label="Phone" prepend-icon="mdi-phone"
                  :rules="[v => !!v || 'Phone number is required']">
                </v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field variant="outlined" v-model="editedUser.email" label="Email" prepend-icon="mdi-email"
                  :rules="[v => !!v || 'Email is required', v => /.+@.+\..+/.test(v) || 'E-mail must be valid']">
                </v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" md="6">
                <v-select variant="outlined" v-model="editedUser.unit_id" :items="branches" label="Branch"
                  item-value="id" item-title="name" prepend-icon="mdi-domain"
                  :rules="[v => !!v || 'Branch is required']">
                </v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select variant="outlined" v-model="editedUser.office_id" :items="offices" label="Office"
                  item-value="id" item-title="name" prepend-icon="mdi-briefcase"
                  :rules="[v => !!v || 'Office is required']">
                </v-select>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" md="6">
                <v-combobox variant="outlined" v-model="editedUser.department_id" :items="departments"
                  label="Department" item-value="id" item-title="name" prepend-icon="mdi-office-building"
                  :rules="[v => !!v || 'Department is required']">
                </v-combobox>
              </v-col>
              <v-col cols="12" md="6">
                <v-select variant="outlined" v-model="editedUser.designation_id" :items="designations"
                  label="Designation" item-value="id" item-title="name" prepend-icon="mdi-account-tie"
                  :rules="[v => !!v || 'Designation is required']">
                </v-select>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field variant="outlined" v-model="editedUser.zk_user_id" label="ZK User ID"
                  prepend-icon="mdi-numeric" :rules="[v => !!v || 'ZK User ID is required']">
                </v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field variant="outlined" v-model="editedUser.zk_username" label="ZK Username"
                  prepend-icon="mdi-account" :rules="[v => !!v || 'ZK Username is required']">
                </v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-subheader class="font-weight-bold">Gender</v-subheader>
              </v-col>
              <v-col cols="12">
                <v-radio-group v-model="editedUser.gender" :rules="[v => !!v || 'Gender is required']">
                  <v-row>
                    <v-col>
                      <v-radio label="Male" value="Male"></v-radio>
                    </v-col>
                    <v-col>
                      <v-radio label="Female" value="Female"></v-radio>
                    </v-col>
                  </v-row>
                </v-radio-group>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-subheader class="font-weight-bold">Role</v-subheader>
              </v-col>
              <v-col cols="12">
                <v-radio-group v-model="editedUser.role" :rules="[v => !!v || 'Role is required']">
                  <v-row>
                    <v-col>
                      <v-radio label="Admin" value="admin"></v-radio>
                    </v-col>
                    <v-col>
                      <v-radio label="Employee" value="employee"></v-radio>
                    </v-col>
                  </v-row>
                </v-radio-group>
              </v-col>
            </v-row>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="red" @click="editUserDialog = false">Close</v-btn>
              <v-btn color="primary" type="submit">Update</v-btn>
            </v-card-actions>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>
    <!-- Permissions Modal -->
    <v-dialog v-model="permissionsDialog" max-width="600px" persistent>
      <v-card>
        <v-card-title>Edit Permissions</v-card-title>
        <v-card-text>
          <v-row justify="end">
            <v-col v-for="permission in userPermissions" :key="permission.id" cols="12" md="4">
              <v-checkbox v-model="selectedPermissions" :label="formatPermissionName(permission.name)"
                :value="permission.id">
              </v-checkbox>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-row class="d-flex align-center justify-space-between">
            <v-btn color="error" @click="closePermissionsDialog">Close</v-btn>
            <v-btn color="success" @click="submitPermissions">Submit</v-btn>
          </v-row>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Roles Modal -->
    <v-dialog v-model="switchRoleDialog" max-width="600px" persistent>
      <v-card class="elevation-12 rounded-xl">
        <v-card-title class="text-center">
          <v-icon left class="mr-2">mdi-account-switch</v-icon>
          Switch Roles
        </v-card-title>
        <hr>
        <v-card-text>
          <v-radio-group v-model="selectedRole" class="mt-4">
            <v-row>
              <v-col v-for="role in roles" :key="role.id" cols="12" md="6" lg="4">
                <v-radio :label="role.name" :value="role.name" class="mx-2 my-1"
                  style="font-weight: 600; font-size: 1.1rem; color: #333;" />
              </v-col>
            </v-row>
          </v-radio-group>
        </v-card-text>
        <v-card-actions class="d-flex justify-between pt-4">
          <v-btn color="red" @click="closeSwitchRoleDialog" class="rounded-lg text-uppercase font-weight-bold"
            style="min-width: 120px; box-shadow: none; transition: all 0.3s ease-in-out;">
            <v-icon left>mdi-close-circle-outline</v-icon> Cancel
          </v-btn>

          <v-btn color="green" @click="submitRole" class="rounded-lg text-uppercase font-weight-bold"
            style="min-width: 120px; box-shadow: none; transition: all 0.3s ease-in-out;">
            <v-icon left>mdi-check-circle-outline</v-icon> Confirm
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="filterDialog" max-width="500">
      <v-card>
        <v-card-title class="headline font-weight-bold">Filter Options</v-card-title>
        <v-card-text>
          <v-select v-model="filters.unit_id" :items="branches" item-value="id" item-title="name" label="Branch" dense>
          </v-select>
          <v-select v-model="filters.office_id" :items="offices" item-value="id" item-title="name" label="Office" dense>
          </v-select>

          <v-autocomplete v-model="filters.department_id" :items="departments" item-value="id" item-title="name" mutiple
            label="Department" dense>
          </v-autocomplete>
          <v-select v-model="filters.designation_id" :items="designations" item-value="id" item-title="name"
            label="Designation" dense>
          </v-select>
        </v-card-text>
        <v-card-actions>
          <v-btn @click="filterDialog = false" color="primary">close</v-btn>
          <v-btn @click="filterUsers" color="primary">filter</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>


    <!-- Salary Information Dialog -->
    <!-- Salary Information Dialog -->
<v-dialog v-model="salaryDialog" max-width="500px" persistent>
  <v-card>
    <v-card-title class="headline font-weight-bold">
      Salary Information
    </v-card-title>
    <v-card-text>
      <v-form ref="salaryForm" @submit.prevent="submitSalaryInfo">
        <!-- Earnings Section -->
        <div class="mb-4">
          <h3 class="subtitle-1 font-weight-medium mb-2">Earnings</h3>


           <v-btn
                  size="small"
                  color="primary"
                  variant="text"
                  prepend-icon="mdi-plus"
                  @click="addEarning"
                >
                  Add Earning
                </v-btn>
          
          <!-- Show user's existing earnings if assigned -->
          <div v-if="hasExistingEarnings">
            <v-row v-for="(earning, index) in userEarnings" :key="`user-earning-${index}`" class="mb-2">
              <v-col cols="4">
                <v-text-field 
                  :label="earning.label || 'Earning'" 
                  :value="earning.label || `Earning ${index + 1}`" 
                  readonly 
                  dense
                  hide-details>
                </v-text-field>
              </v-col>
              <v-col cols="4">
                <v-text-field 
                  v-model="earning.amount"
                  :label="earning.type === 'percentage' ? 'Percentage (%)' : 'Amount (KES)'"
                  :prefix="earning.type === 'percentage' ? '' : 'KES'"
                  :suffix="earning.type === 'percentage' ? '%' : ''" 
                  type="number"
                  :rules="[v => !!v || (earning.type === 'percentage' ? 'Percentage is required' : 'Amount is required')]"
                  dense 
                  required 
                  hide-details>
                </v-text-field>
                
              </v-col>

                 <v-col cols="4" >
                  <v-btn
                    icon="mdi-delete"
                    variant="text"
                    color="error"
                    @click="removeEarning(index)"
                    class="mt-2"
                  ></v-btn>
                </v-col>
            </v-row>
          </div>
          
          <!-- Show default earnings form if no existing earnings -->
            <div v-else>
            <v-row v-for="(earning, index) in allEarnings" :key="`earning-${index}`" class="mb-2">
              <v-col cols="7">
              <v-text-field 
                :label="earning.label || earning.name" 
                :value="earning.label || earning.name" 
                readonly 
                dense
                hide-details>
              </v-text-field>
              </v-col>
              <v-col cols="5">
              <v-text-field 
                v-model="userEarnings[index].amount"
                :label="earning.type === 'percentage' ? 'Percentage (%)' : 'Amount (KES)'"
                :prefix="earning.type === 'percentage' ? '' : 'KES'"
                :suffix="earning.type === 'percentage' ? '%' : ''" 
                type="number"
                :rules="[v => !!v || (earning.type === 'percentage' ? 'Percentage is required' : 'Amount is required')]"
                dense 
                required 
                hide-details>
              </v-text-field>

             
              <!-- add edit and delete  -->
              </v-col>
            </v-row>
            </div>
        </div>

        <!-- Deductions Section -->
        <div class="mb-4">
          <h3 class="subtitle-1 font-weight-medium mb-2">Deductions</h3>


              <v-btn
                  size="small"
                  color="primary"
                  variant="text"
                  prepend-icon="mdi-plus"
                  @click="addDeduction"
                >
                  Add Deduction
                </v-btn>
          
          <!-- Show user's existing deductions if assigned -->
          <div v-if="hasExistingDeductions">
            <v-row v-for="(deduction, index) in userDeductions" :key="`user-deduction-${index}`" class="mb-2">
              <v-col cols="4">
                <v-text-field 
                  :label="deduction.label || 'Deduction'" 
                  :value="deduction.label || `Deduction ${index + 1}`" 
                  readonly 
                  dense
                  hide-details>
                </v-text-field>
              </v-col>
              <v-col cols="4">
                <v-text-field 
                  v-model="deduction.amount"
                  :label="deduction.type === 'percentage' ? 'Percentage (%)' : 'Amount (KES)'"
                  :prefix="deduction.type === 'percentage' ? '' : 'KES'"
                  :suffix="deduction.type === 'percentage' ? '%' : ''" 
                  type="number"
                  :rules="[v => !!v || (deduction.type === 'percentage' ? 'Percentage is required' : 'Amount is required')]"
                  dense 
                  required 
                  hide-details>
                </v-text-field>
              </v-col>

                  <v-col cols="4" md="2">
                  <v-btn
                    icon="mdi-delete"
                    variant="text"
                    color="error"
                    @click="removeDeduction(index)"
                    class="mt-2"
                  ></v-btn>
                </v-col>

            </v-row>
          </div>
          
          <!-- Show default deductions form if no existing deductions -->
            <div v-else>
            <v-row v-for="(deduction, index) in allDeductions" :key="`deduction-${index}`" class="mb-2">
              <v-col cols="7">
              <v-text-field 
                :label="deduction.label || deduction.name" 
                :value="deduction.label || deduction.name" 
                readonly 
                dense
                hide-details>
              </v-text-field>
              </v-col>
              <v-col cols="5">
              <v-text-field 
                v-model="userDeductions[index].amount"
                :label="deduction.type === 'percentage' ? 'Percentage (%)' : 'Amount (KES)'"
                :prefix="deduction.type === 'percentage' ? '' : 'KES'"
                :suffix="deduction.type === 'percentage' ? '%' : ''" 
                type="number"
                :rules="[v => !!v || (deduction.type === 'percentage' ? 'Percentage is required' : 'Amount is required')]"
                dense 
                required 
                hide-details>
              </v-text-field>
              </v-col>
            </v-row>
            </div></div>
      </v-form>
    </v-card-text>
    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn color="red" text @click="salaryDialog = false">
        Close
      </v-btn>
      <v-btn color="primary" @click="submitSalaryInfo">
        {{ hasExistingEarnings || hasExistingDeductions ? 'Update' : 'Save' }}
      </v-btn>
    </v-card-actions>
  </v-card>
</v-dialog>
  </v-container-fluid>
</template>

<script>
export default {
  props: {
    user: Object,
    roles: Array,
  },
  
  computed: {
    userId() {
      return this.user?.id;
    },
    
    hasExistingEarnings() {
      return this.selectedUser && 
             this.selectedUser.earnings && 
             this.selectedUser.earnings.length > 0;
    },

    hasExistingDeductions() {
      return this.selectedUser && 
             this.selectedUser.deductions && 
             this.selectedUser.deductions.length > 0;
    },
    
    filteredUsers() {
      if (!this.search) return this.users;
      return this.users.filter(user => 
        user.fullName.toLowerCase().includes(this.search.toLowerCase()) ||
        user.email.toLowerCase().includes(this.search.toLowerCase())
      );
    }
  },

  data() {
    return {
      // earnings: [],
      // Dialog states
      salaryDialog: false,
      switchRoleDialog: false,
      addUserModal: false,
      editUserDialog: false,
      filterDialog: false,
      deleteModal: false,
      permissionsDialog: false,
      
      // User management
      selectedUser: null,
      users: [],
      search: '',
      base_url: '/',
      selected: [],
      
      // Salary management
      userEarnings: [],
      userDeductions: [],
      allEarnings: [],
      allDeductions: [],
      
      // Table headers
      headers: [
        { title: 'Employee', key: 'fullName' },
        { title: 'Email', key: 'email' },
        { title: 'Phone', key: 'phone' },
        { title: 'Branch', key: 'unit.name' },
        { title: 'ZK User ID', key: 'zk_user_id' },
        { title: 'ZK Username', key: 'zk_username' },
        { title: 'Biometrics', key: 'has_biometrics' },
        { title: 'Department', key: 'department.name' },
        { title: 'Status', key: 'is_enabled' },
        { title: 'Action', key: 'actions' },
      ],
      
      // Form data
      formData: {
        first_name: '',
        last_name: '',
        phone: '',
        email: '',
        gender: null,
        unit_id: null,
        office_id: null,
        department_id: null,
        designation_id: null,
        enable_login: false,
        send_logins: false,
        role: null,
        zk_user_id: '',
        zk_username: '',
      },
      
      editedUser: {
        id: null,
        first_name: '',
        last_name: '',
        phone: '',
        gender: null,
        email: '',
        unit_id: null,
        office_id: null,
        department_id: null,
        designation_id: null,
        role: null,
        zk_user_id: '',
        zk_username: '',
      },
      
      // Master data
      branches: [],
      offices: [],
      departments: [],
      designations: [],
      roles: [],
      permissions: [],
      
      // Filters
      filters: {
        unit_id: null,
        office_id: null,
        department_id: null,
        designation_id: null,
      },
      
      // Permissions
      userPermissions: [],
      selectedPermissions: [],
      currentUserIdForPermissions: null,
      
      // Role switching
      selectedRole: null,
      
      // Delete
      deletingItem: null,
      
      // Loading states
      loading: {
        users: false,
        salary: false,
        delete: false,
      }
    };
  },

  created() {
    this.initializeComponent();
  },

  methods: {
    // Initialization
    async initializeComponent() {
      this.loading.users = true;
      try {
        await Promise.all([
          this.fetchUsers(),
          this.fetchUnits(),
          this.fetchOffices(),
          this.fetchDepartments(),
          this.fetchDesignations(),
          this.fetchPermissions(),
          this.fetchRoles(),
          this.fetchAllEarnings(),
          this.fetchAllDeductions(),
        ]);
      } catch (error) {
        console.error('Error initializing component:', error);
        this.$toast?.error?.('Failed to load data. Please refresh the page.');
      } finally {
        this.loading.users = false;
      }
    },

    // Salary Management
    async fetchAllEarnings() {
      try {
        const response = await axios.get('api/v1/earnings');
        this.allEarnings = response.data.data;
      } catch (error) {
        console.error('Error fetching earnings:', error);
        this.$toast?.error?.('Failed to load earnings data.');
      }
    },

    async fetchAllDeductions() {
      try {
        const response = await axios.get('api/v1/deductions');
        this.allDeductions = response.data.data;
      } catch (error) {
        console.error('Error fetching deductions:', error);
        this.$toast?.error?.('Failed to load deductions data.');
      }
    },

    openSalaryDialog(user) {
      this.selectedUser = user;
      this.prepareSalaryData();
      this.salaryDialog = true;
    },

    prepareSalaryData() {
      // Initialize earnings data
      if (this.hasExistingEarnings) {
        this.userEarnings = this.selectedUser.earnings.map(earning => ({
          id: earning.id,
          earning_id: earning.earning_id,
          amount: earning.amount || '',
          label: this.getEarningLabel(earning.earning_id),
          type: this.getEarningType(earning.earning_id)
        }));
      } else {
        // Create empty entries for all available earnings
        this.userEarnings = this.allEarnings.map(earning => ({
          earning_id: earning.id,
          amount: '',
          label: earning.label || earning.name,
          type: earning.type || 'amount'
        }));
      }

      // Initialize deductions data
      if (this.hasExistingDeductions) {
        this.userDeductions = this.selectedUser.deductions.map(deduction => ({
          id: deduction.id,
          deduction_id: deduction.deduction_id,
          amount: deduction.amount || '',
          label: this.getDeductionLabel(deduction.deduction_id),
          type: this.getDeductionType(deduction.deduction_id)
        }));
      } else {
        // Create empty entries for all available deductions
        this.userDeductions = this.allDeductions.map(deduction => ({
          deduction_id: deduction.id,
          amount: '',
          label: deduction.label || deduction.name,
          type: deduction.type || 'amount'
        }));
      }
    },

    getEarningLabel(earningId) {
      const earning = this.allEarnings.find(e => e.id === earningId);
      return earning ? (earning.label || earning.name) : `Earning ${earningId}`;
    },

    getEarningType(earningId) {
      const earning = this.allEarnings.find(e => e.id === earningId);
      return earning ? (earning.type || 'amount') : 'amount';
    },

    getDeductionLabel(deductionId) {
      const deduction = this.allDeductions.find(d => d.id === deductionId);
      return deduction ? (deduction.label || deduction.name) : `Deduction ${deductionId}`;
    },

    getDeductionType(deductionId) {
      const deduction = this.allDeductions.find(d => d.id === deductionId);
      return deduction ? (deduction.type || 'amount') : 'amount';
    },

    async submitSalaryInfo() {
      if (!this.selectedUser?.id) {
        this.$toast?.error?.('No user selected.');
        return;
      }

      this.loading.salary = true;
      
      try {
        if (this.hasExistingEarnings || this.hasExistingDeductions) {
          await this.updateUserSalaryInfo();
        } else {
          await this.createUserSalaryInfo();
        }
        
        this.$emit('salary-saved');
        this.salaryDialog = false;
        this.$toast?.success?.('Salary details saved successfully.');
        
        // Refresh user data to show updated information
        await this.fetchUsers();
        
      } catch (error) {
        console.error('Error saving salary info:', error);
        this.$toast?.error?.('Failed to save salary information. Please try again.');
      } finally {
        this.loading.salary = false;
      }
    },
    async updateUserSalaryInfo() {
      const payload = {
      user_id: this.selectedUser.id,
      earnings: this.userEarnings.filter(e => e.amount && parseFloat(e.amount) > 0),
      deductions: this.userDeductions.filter(d => d.amount && parseFloat(d.amount) > 0)
      };

      const [earningsRes, deductionsRes] = await Promise.all([
      axios.put(`/api/v1/earnings/user-earnings/${this.selectedUser.id}`, {
        user_id: this.selectedUser.id,
        earnings: payload.earnings
      }),
      axios.put(`/api/v1/deductions/user-deductions/${this.selectedUser.id}`, {
        user_id: this.selectedUser.id,
        deductions: payload.deductions
      })
      ]);

      if (!earningsRes.data.success || !deductionsRes.data.success) {
      throw new Error('API returned error status');
      }
    },

    async createUserSalaryInfo() {
      const earningsPayload = {
        user_id: this.selectedUser.id,
        earnings: this.userEarnings
          .filter(e => e.amount && parseFloat(e.amount) > 0)
          .map(e => ({
            earning_id: e.earning_id,
            amount: parseFloat(e.amount),
            type: e.type
          }))
      };

      const deductionsPayload = {
        user_id: this.selectedUser.id,
        deductions: this.userDeductions
          .filter(d => d.amount && parseFloat(d.amount) > 0)
          .map(d => ({
            deduction_id: d.deduction_id,
            amount: parseFloat(d.amount),
            type: d.type
          }))
      };

      const [earningsRes, deductionsRes] = await Promise.all([
        axios.post('/api/v1/earnings/user-earnings', earningsPayload),
        axios.post('/api/v1/deductions/user-deductions', deductionsPayload)
      ]);

      if (!earningsRes.data.success || !deductionsRes.data.success) {
        throw new Error('API returned error status');
      }
    },

    // User Management
    async fetchUsers() {
      try {
        const response = await axios.get(`${this.base_url}api/v1/users`);
        this.users = response.data.users.map(user => ({
          id: user.id,
          fullName: `${user.firstname} ${user.lastname}`,
          super_admin: user.super_admin,
          email: user.email,
          phone: user.phone,
          unit: user.unit,
          department: user.department,
          designation: user.designation,
          is_enabled: user.is_enabled,
          has_biometrics: user.has_biometrics,
          office: user.office,
          gender: user.gender,
          role: user.role,
          zk_user_id: user.zk_user_id,
          zk_username: user.zk_username,
          earnings: user.earnings || [],
          deductions: user.deductions || [],
        }));
      } catch (error) {
        console.error('Error fetching users:', error);
        throw error;
      }
    },

    async filterUsers() {
      try {
        const response = await axios.get(`${this.base_url}api/v1/users`, {
          params: this.filters
        });
        this.users = response.data.users.map(user => ({
          id: user.id,
          fullName: `${user.firstname} ${user.lastname}`,
          email: user.email,
          phone: user.phone,
          unit: user.unit,
          department: user.department,
          designation: user.designation,
          is_enabled: user.is_enabled,
          office: user.office,
          gender: user.gender,
          role: user.role
        }));
      } catch (error) {
        console.error('Error filtering users:', error);
        this.$toast?.error?.('Failed to filter users.');
      }
    },

    // Master Data Fetching
    async fetchUnits() {
      try {
        const response = await axios.get(`${this.base_url}api/v1/branches`);
        this.branches = response.data.branches;
      } catch (error) {
        console.error('Error fetching branches:', error);
        throw error;
      }
    },

    async fetchOffices() {
      try {
        const response = await axios.get(`${this.base_url}api/v1/offices`);
        this.offices = response.data.offices;
      } catch (error) {
        console.error('Error fetching offices:', error);
        throw error;
      }
    },

    async fetchDepartments() {
      try {
        const response = await axios.get(`${this.base_url}api/v1/departments`);
        this.departments = response.data.departments;
      } catch (error) {
        console.error('Error fetching departments:', error);
        throw error;
      }
    },

    async fetchDesignations() {
      try {
        const response = await axios.get(`${this.base_url}api/v1/designations`);
        this.designations = response.data.designations;
      } catch (error) {
        console.error('Error fetching designations:', error);
        throw error;
      }
    },

    async fetchPermissions() {
      try {
        const response = await axios.get(`${this.base_url}api/v1/permissions`);
        this.permissions = response.data.permissions;
      } catch (error) {
        console.error('Error fetching permissions:', error);
        throw error;
      }
    },

    async fetchRoles() {
      try {
        const response = await axios.get(`${this.base_url}api/v1/roles`);
        this.roles = response.data.roles;
      } catch (error) {
        console.error('Error fetching roles:', error);
        throw error;
      }
    },

    // User Actions
    async submitAddUserForm() {
      try {
        const response = await axios.post(`${this.base_url}api/v1/users`, this.formData);
        this.$toast?.success?.('Employee created successfully');
        await this.fetchUsers();
        this.addUserModal = false;
        this.resetFormData();
      } catch (error) {
        console.error('Error submitting form:', error);
        this.$toast?.error?.('Error creating employee. Please try again.');
      }
    },

    async editUser(item) {
      if (item.super_admin) {
        this.$toast?.error?.('This User is read only');
        return;
      }

      try {
        const response = await axios.get(`${this.base_url}api/v1/users/${item.id}`);
        const userData = response.data.user;
        
        this.editedUser = {
          id: userData.id,
          first_name: userData.firstname,
          last_name: userData.lastname,
          phone: userData.phone,
          email: userData.email,
          unit_id: userData.unit_id,
          office_id: userData.office_id,
          department_id: userData.department_id,
          designation_id: userData.designation_id,
          role: userData.role[0],
          gender: userData.gender,
          zk_user_id: userData.zk_user_id,
          zk_username: userData.zk_username,
        };

        this.editUserDialog = true;
      } catch (error) {
        console.error('Error fetching user data:', error);
        this.$toast?.error?.('Failed to load user data.');
      }
    },

    async submitEditUserForm() {
      try {
        const updatedUserData = {
          firstname: this.editedUser.first_name,
          lastname: this.editedUser.last_name,
          phone: this.editedUser.phone,
          email: this.editedUser.email,
          unit_id: this.editedUser.unit_id,
          office_id: this.editedUser.office_id,
          department_id: typeof this.editedUser.department_id === 'object' 
            ? this.editedUser.department_id.id 
            : this.editedUser.department_id,
          designation_id: this.editedUser.designation_id,
          role: this.editedUser.role,
          gender: this.editedUser.gender,
          zk_user_id: this.editedUser.zk_user_id,
          zk_username: this.editedUser.zk_username,
        };

        const response = await axios.put(
          `${this.base_url}api/v1/users/update/${this.editedUser.id}`, 
          updatedUserData
        );
        
        this.editUserDialog = false;
        this.$toast?.success?.(response.data.message);
        await this.fetchUsers();
      } catch (error) {
        console.error('Error updating user data:', error);
        this.$toast?.error?.('Error updating user data. Please try again.');
      }
    },

    async deleteUser(user) {
      if (user.super_admin) {
        this.$toast?.error?.('This User is read only');
        return;
      }

      if (!confirm('Are you sure you want to delete this user?')) {
        return;
      }

      this.loading.delete = true;
      try {
        const response = await axios.delete(`${this.base_url}api/v1/users/${user.id}`);
        await this.fetchUsers();
        this.$toast?.success?.(response.data.message);
      } catch (error) {
        console.error('Error deleting user:', error);
        this.$toast?.error?.('Failed to delete User');
      } finally {
        this.loading.delete = false;
      }
    },

    async toggleAccount(user) {
      const originalStatus = user.is_enabled;
      user.is_enabled = !user.is_enabled;

      try {
        await axios.put(`${this.base_url}api/v1/users/${user.id}/toggle-status`, {
          is_enabled: user.is_enabled
        });
        this.$toast?.success?.('Account status toggled successfully');
      } catch (error) {
        console.error('Error toggling account status:', error);
        user.is_enabled = originalStatus; // Rollback
        this.$toast?.error?.('Error toggling account status');
      }
    },

    // Role Management
    openRoleSwitchModal(user) {
      this.user = user;
      this.selectedRole = null;
      this.switchRoleDialog = true;
    },

    async submitRole() {
      if (!this.selectedRole) {
        this.$toast?.error?.('Please select a role');
        return;
      }

      try {
        await axios.put(`${this.base_url}api/v1/users/${this.user.id}/switch-role`, {
          role: this.selectedRole
        });
        this.$toast?.success?.('Role switched successfully');
        this.switchRoleDialog = false;
        await this.fetchUsers();
      } catch (error) {
        console.error('Error switching role:', error);
        this.$toast?.error?.('Failed to switch role. Please try again.');
      }
    },

    // Permissions Management
    async openPermissionsModal(userId) {
      this.currentUserIdForPermissions = userId;
      
      try {
        const response = await axios.get(`${this.base_url}api/v1/permissions/${userId}`);
        this.userPermissions = response.data.userPermissions;
        this.selectedPermissions = this.userPermissions
          .filter(permission => permission.selected)
          .map(permission => permission.id);
        this.permissionsDialog = true;
      } catch (error) {
        console.error('Error fetching user permissions:', error);
        this.$toast?.error?.('Failed to load permissions.');
      }
    },

    async submitPermissions() {
      try {
        await axios.put(
          `${this.base_url}api/v1/users/${this.currentUserIdForPermissions}/update-permissions`,
          { permissions: this.selectedPermissions }
        );
        this.$toast?.success?.('Permissions updated successfully!');
        this.permissionsDialog = false;
      } catch (error) {
        console.error('Error updating permissions:', error);
        this.$toast?.error?.('Error updating permissions!');
      }
    },

    // Utility Methods
    impersonateUser(user) {
      if (!user || !user.id) {
        console.error('Invalid user object or missing user ID.');
        this.$toast?.error?.('User information is incomplete. Please contact support.');
        return;
      }

      const impersonateUrl = user.impersonate_url || `/impersonate/${user.id}`;
      
      if (impersonateUrl) {
        window.location.href = impersonateUrl;
      } else {
        console.error('Impersonation URL could not be determined.');
        this.$toast?.error?.('Unable to determine impersonation URL. Please contact support.');
      }
    },

    formatPermissionName(name) {
      return name.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
    },

    getAvatar(user) {
      const avatarMap = {
        'Male': '/assets/img/male-avatar.svg',
        'Female': '/assets/img/female-avatar.png'
      };
      return avatarMap[user.gender] || '/assets/img/user.jpg';
    },

    getStatusColor(value) {
      return value ? 'green' : 'red';
    },

    getStatusIcon(value) {
      return value ? 'mdi-check-circle' : 'mdi-alert-circle';
    },

    getIconName(title) {
      const iconMap = {
        'Total Employees': 'account-group',
        'Total Departments': 'office-building',
        'Total Offices': 'gate',
        'Total Designations': 'briefcase'
      };
      return iconMap[title] || '';
    },

    getIconColor(color) {
      const colorMap = {
        'warning': 'yellow darken-2',
        'primary': 'blue darken-2',
        'info': 'teal darken-2',
        'success': 'green darken-2'
      };
      return colorMap[color] || '';
    },

    // Form Reset
    resetFormData() {
      this.formData = {
        first_name: '',
        last_name: '',
        phone: '',
        email: '',
        gender: null,
        unit_id: null,
        office_id: null,
        department_id: null,
        designation_id: null,
        enable_login: false,
        send_logins: false,
        role: null,
        zk_user_id: '',
        zk_username: '',
      };
    },

    // Dialog Management
    clearSearch() {
      this.search = '';
      this.fetchUsers();
    },

    refreshUsers() {
      this.fetchUsers();
      this.$toast?.success?.('Data refreshed successfully');
    },

    closeSwitchRoleDialog() {
      this.switchRoleDialog = false;
      this.selectedRole = null;
    },

    closePermissionsDialog() {
      this.permissionsDialog = false;
      this.selectedPermissions = [];
    },

    openDeleteModal(item) {
      this.deletingItem = item;
      this.deleteModal = true;
    },

    closeDeleteModal() {
      this.deletingItem = null;
      this.deleteModal = false;
    },

    // Placeholder methods
    downloadExcel() {
      // Implementation for Excel download
      console.log('Excel download functionality to be implemented');
    },

    canEditUser(user) {
      // Implementation for user edit permissions
      return !user.super_admin;
    },

    canDeleteResource() {
      // Implementation for delete permissions
      return true;
    },

    biometricsModal(user) {
      // Implementation for biometrics management
      console.log('Biometrics modal functionality to be implemented');
    }
  }
};
</script>