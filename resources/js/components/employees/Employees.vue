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

            <v-icon @click="impersonateUser(item)" title="Impersonate User"  color="purple">
              mdi-account
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
                <v-combobox variant="outlined" v-model="editedUser.department_id" :items="departments" label="Department"
                  item-value="id" item-title="name" prepend-icon="mdi-office-building"
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
  </v-container-fluid>
</template>

<script>

export default {
  data() {
    return {
      selected: [],
      base_url: '/',
      search: '',
      headers: [
        { title: 'Employee', key: 'fullName' },
        { title: 'Email', key: 'email' },
        { title: 'Phone', key: 'phone' },
        { title: 'Branch', key: 'unit.name' },
        { title: 'Biometrics', key: 'has_biometrics' },
        { title: 'Department', key: 'department.name' },
        { title: 'Status', key: 'is_enabled' },
        { title: 'Action', key: 'actions' },
      ],
      users: [],
      filters: {
        unit_id: null,
        office_id: null,
        department_id: null,
        designation_id: null,
      },
      switchRoleDialog: false,
      addUserModal: false,
      editUserDialog: false,
      filterDialog: false,
      deleteModal: false,
      permissionsDialog: false,
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
      },
      branches: [],
      offices: [],
      departments: [],
      designations: [],
      userPermissions: [],
      roles: [],
      selectedPermissions: [],
      selectedRole: null,

    };
  },

  created() {
    this.fetchUsers();
    this.fetchUnits();
    this.fetchOffices();
    this.fetchDepartments();
    this.fetchDesignations();
    this.fetchPermissions();
    this.fetchRoles();
  },
  methods: {


   
  //   impersonateUser(user) {

  //     user.id =filter(this.users where user_id == user.id)

  //   if (user.impersonate_url) {
  //     window.location.href = user.impersonate_url;
  //   } else {
  //     console.error('Impersonation URL not available for this user.');
  //     // Optionally, display a user-friendly message
  //     this.$toastr.error('Impersonation URL is missing. Please contact support.');
  //   }
  // },


   impersonateUser(user) {
  // Validate the user object and its ID
  if (!user || !user.id) {
    console.error('Invalid user object or missing user ID.');
    this.$toastr.error('User information is incomplete. Please contact support.');
    return;
  }

  // Attempt to retrieve or construct the impersonation URL
  let impersonateUrl = user.impersonate_url;
  if (!impersonateUrl) {
    // Construct the URL based on the user's ID, adjust the URL pattern as needed
    impersonateUrl = `/impersonate/${user.id}`;
  }

  // Redirect to the impersonation URL
  if (impersonateUrl) {
    window.location.href = impersonateUrl;
  } else {
    console.error('Impersonation URL could not be determined.');
    this.$toastr.error('Unable to determine impersonation URL. Please contact support.');
  }
}
,

    formatPermissionName(name) {
      return name.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
    },

    fetchUsers() {
      const apiUrl = this.base_url + 'api/v1/users';
      axios.get(apiUrl)
        .then(response => {
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
            role: user.role
          }));


        })
        .catch(error => {
          console.error('Error fetching users:', error);
        });
    },
    filterUsers() {
      const filterOptions = this.filterOptions;
      const uri = this.base_url + 'api/v1/users';
      axios.get(uri, filterOptions)
        .then(response => {
          this.users = response.data.users.map(user => ({
            id: user.id,
            fullName: user.firstname + user.lastname,
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
        })
        .catch(error => {
          console.log(error);
        })
    },

    getIconName(title) {
      switch (title) {
        case 'Total Employees':
          return 'account-group';
        case 'Total Departments':
          return 'office-building';
        case 'Total Offices':
          return 'gate';
        case 'Total Designations':
          return 'briefcase';
        default:
          return '';
      }
    },

    getIconColor(color) {
      switch (color) {
        case 'warning':
          return 'yellow darken-2';
        case 'primary':
          return 'blue darken-2';
        case 'info':
          return 'teal darken-2';
        case 'success':
          return 'green darken-2';
        default:
          return '';
      }
    },
    getAvatar(user) {
      if (user.gender == 'Male') {
        return '/assets/img/male-avatar.svg';
      }

      else if (user.gender == 'Female') {
        return '/assets/img/female-avatar.png';
      }
      else {
        return '/assets/img/user.jpg';
      }

    },
    clearSearch() {
      this.fetchUsers();

      this.search = '';

    },
    fetchUnits() {
      const apiUrl = this.base_url + 'api/v1/branches';

      axios.get(apiUrl)
        .then(response => {
          this.branches = response.data.branches;
        })
        .catch(error => {
          console.error('Error fetching branches:', error);
        });
    },

    fetchPermissions() {
      const apiUrl = this.base_url + 'api/v1/permissions';

      axios.get(apiUrl)
        .then(response => {
          this.permissions = response.data.permissions;
        })
        .catch(error => {
          console.error('Error fetching permissions:', error);
        });
    },

    fetchRoles() {
      const apiUrl = this.base_url + 'api/v1/roles';

      axios.get(apiUrl)
        .then(response => {
          this.roles = response.data.roles;
        })
        .catch(error => {
          console.error('Error fetching roles:', error);
        });
    },
    fetchOffices() {
      const apiUrl = this.base_url + 'api/v1/offices';

      axios.get(apiUrl)
        .then(response => {
          this.offices = response.data.offices;
        })
        .catch(error => {
          console.error('Error fetching offices:', error);
        });
    },
    fetchDepartments() {
      const apiUrl = this.base_url + 'api/v1/departments';

      axios.get(apiUrl)
        .then(response => {
          this.departments = response.data.departments;
        })
        .catch(error => {
          console.error('Error fetching departments:', error);
        });
    },

    fetchDesignations() {
      const apiUrl = this.base_url + 'api/v1/designations';

      axios.get(apiUrl)
        .then(response => {
          this.designations = response.data.designations;
        })
        .catch(error => {
          console.error('Error fetching designations:', error);
        });
    },
    submitAddUserForm() {
      console.log('Form submitted:', this.formData);
      const apiUrl = this.base_url + 'api/v1/users';

      axios.post(apiUrl, this.formData)
        .then(response => {
          console.log('Form submission successful:', response.data);

          this.$toastr.success('Employee created successfully');

          this.fetchUsers();
          this.addUserModal = false;
        })
        .catch(error => {
          console.error('Error submitting form:', error);
          this.$toastr.error('Error creating employee. Please try again.');
        });
    },
    refreshUsers() {
      this.fetchUsers();
      this.$toastr.success('Success')
    },
    downloadExcel() {

    },
    canEditUser(user) {
    },
    canDeleteResource() {
    },
    editUser(item) {
      if (item.super_admin == true) {
        this.$toastr.error('This User is read only');
        return;
      }


      console.log("Selected User:" + item)
      const apiUrl = `${this.base_url}api/v1/users/${item.id}`;

      axios.get(apiUrl)
        .then(response => {
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
          };

          this.editUserDialog = true;
        })
        .catch(error => {
          console.error('Error fetching user data:', error);
        });
    },



    submitEditUserForm() {
      const apiUrl = `${this.base_url}api/v1/users/update/${this.editedUser.id}`;

      const updatedUserData = {
        firstname: this.editedUser.first_name,
        lastname: this.editedUser.last_name,
        phone: this.editedUser.phone,
        email: this.editedUser.email,
        unit_id: this.editedUser.unit_id,
        office_id: this.editedUser.office_id,
        department_id: this.editedUser.department_id,
        designation_id: this.editedUser.designation_id,
        role: this.editedUser.role,
        gender: this.editedUser.gender,
      };

      axios.put(apiUrl, updatedUserData)
        .then(response => {
          console.log('User data updated successfully:', response.data);
          this.editUserDialog = false;
          this.$toastr.success(response.data.message);
          this.fetchUsers();
        })
        .catch(error => {
          console.error('Error updating user data:', error);

          this.$toastr.error('Error updating user data. Please try again.');
        });
    },
    openRoleSwitchModal(user) {

      console.log('User object:', user);
      this.$nextTick(() => {
        this.user = user;
        this.switchRoleDialog = true;
      });
    },


    closeSwitchRoleDialog() {
      this.switchRoleDialog = false;
    },

    submitRole() {

      if (!this.selectedRole) {
        this.$toastr.error('Please select a role');
        return;
      }

      const apiUrl = `${this.base_url}api/v1/users/${this.user.id}/switch-role`;

      axios.put(apiUrl, { role: this.selectedRole })
        .then(response => {
          this.$toastr.success('Role switched successfully');
          this.switchRoleDialog = false;
        })
        .catch(error => {
          console.error('Error switching role:', error);
          this.$toastr.error('Failed to switch role. Please try again.');
        });
    },

    openPermissionsModal(userId) {
      this.currentUserIdForPermissions = userId;
      const apiUrl = `${this.base_url}api/v1/permissions/${userId}`;

      axios.get(apiUrl)
        .then(response => {
          this.userPermissions = response.data.userPermissions;
          this.selectedPermissions = this.userPermissions.filter(permission => permission.selected).map(permission => permission.id);

          this.permissionsDialog = true;
        })
        .catch(error => {
          console.error('Error fetching user permissions:', error);
        });
    },
    closePermissionsDialog() {
      this.permissionsDialog = false;
    },
    submitPermissions() {
      const apiUrl = `${this.base_url}api/v1/users/${this.currentUserIdForPermissions}/update-permissions`;

      const data = {
        permissions: this.selectedPermissions,
      };

      axios.put(apiUrl, data)
        .then(response => {
          this.$toastr.success('Permissions updated successfully!');
          this.permissionsDialog = false;
        })
        .catch(error => {
          this.$toastr.error('Error updating permissions!');
          this.permissionsDialog = false;
        });
    },
    toggleAccount(user) {

      user.is_enabled = !user.is_enabled;
      const apiUrl = this.base_url + 'api/v1/users/' + user.id + '/toggle-status';

      axios
        .put(apiUrl, { is_enabled: user.is_enabled })
        .then((response) => {
          // Handle success
          this.$toastr.success('Account status toggled successfully');
        })
        .catch((error) => {
          // Handle error, rollback the toggle if necessary
          console.error('Error toggling account status:', error);
          user.is_enabled = !user.is_enabled;

          // Display an error toastr
          this.$toastr.error('Error toggling account status');
        });
    },

    openDeleteModal(item) {
      this.deletingItem = item;
      this.deleteModal = true;
    },
    closeDeleteModal() {
      this.deletingItem = null;
      this.deleteModal = false;
    },
    deleteUser(user) {
      if (user.super_admin == true) {
        this.$toastr.error('This User is read only');
        return;
      }


      const apiUrl = this.base_url + `api/v1/users/${user.id}`;
      if (confirm('Are you sure you want to delete this user?')) {
        axios.delete(apiUrl)
          .then(response => {
            this.fetchUsers();
            this.$toastr.success(response.data.message);
          })
          .catch(error => {
            console.error('Error deleting user:', error);
            this.$toastr.error("Failed to delete User");
          });
      }
    },

    getStatusColor(value) {
      console.log('valuse is:' + value)
      return value ? 'green' : 'red';
    },

    getStatusIcon(value) {
      console.log('valuse is:' + value)
      return value ? 'mdi-check-circle' : 'mdi-alert-circle';
    },
    biometricsModal(user) {

    }
  },
};

</script>
