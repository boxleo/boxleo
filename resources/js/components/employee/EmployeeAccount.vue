<template>
  <v-container fluid>
    <v-row>
      <!-- Existing Profile Card -->
      <v-col cols="12">
        <v-card class="mb-5">
          <v-card-text>
            <v-row align="center">
              <v-col cols="12" md="3">
                <div class="d-flex justify-center">
                  <v-avatar size="150">
                    <img :src="user.avatar ? user.avatar : 'assets/img/user.jpg'" alt="Avatar">
                  </v-avatar>
                </div>
              </v-col>
              <v-col cols="12" md="8">
                <div>
                  <h2 class="mb-2">{{ user.firstname }} {{ user.lastname }}</h2>
                  <v-row>
                    <v-col cols="12">
                      <v-icon class="mr-2">mdi-email</v-icon>
                      <span>{{ user.email }}</span>
                    </v-col>
                    <v-col cols="12">
                      <v-icon class="mr-2">mdi-phone</v-icon>
                      <span>{{ user.phone }}</span>
                    </v-col>
                    <v-col cols="12">
                      <v-icon class="mr-2">mdi-domain</v-icon>
                      <span>{{ user.unit.name }}</span>
                    </v-col>
                    <v-col cols="12">
                      <v-icon class="mr-2">mdi-briefcase</v-icon>
                      <span>{{ user.designation.name }}</span>
                    </v-col>
                  </v-row>
                </div>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="d-flex justify-center">
            <v-dialog v-model="dialog" max-width="400px">
              <template v-slot:activator="{ on }">
                <v-tooltip bottom v-for="(action, index) in actions" :key="index" :title="action.title">
                  <template v-slot:activator="{ on }">
                    <v-btn icon color="primary" v-bind="on" @click="openModal(action)">
                      <v-icon>{{ action.icon }}</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
              </template>
              <v-card>
                <v-card-title>{{ currentAction.title }}</v-card-title>
                <v-card-text>
                  <v-form v-if="currentAction.type === 'edit'" ref="editForm">
                    <v-text-field v-model="formData[currentAction.field]" :label="currentAction.label" :rules="currentAction.rules" outlined></v-text-field>
                  </v-form>
                </v-card-text>
                <v-card-actions>
                  <v-btn color="primary" @click="executeAction">Save</v-btn>
                  <v-btn text @click="dialog = false">Cancel</v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-card-actions>
        </v-card>
      </v-col>

      <!-- {{user}} -->

      <!-- Personal Details Form Component -->
      <personal-details-form
        :user="user"
        :user-data="userDetails"
        @details-updated="onDetailsUpdated"
      />
    </v-row>
  </v-container>
</template>

<script>
import PersonalDetailsForm from '@/components/employee/PersonalDetailsForm.vue';

export default {


    props: {
    user: Object,
    roles: Array,
    permissions: Array
  },
  computed: {
    userId() {
      return this.user?.id   
    }
  },
  components: {
    PersonalDetailsForm
  },
  data() {
    return {
    //   user: {
    //     id: null,
    //     firstname: '',
    //     lastname: '',
    //     email: '',
    //     phone: '',
    //     avatar: null,
    //     unit: { name: '' },
    //     designation: { name: '' }
    //   },
      userDetails: {},
      actions: [
        { title: 'Edit Profile', icon: 'mdi-pencil', type: 'edit', field: 'fullname', label: 'Full Name', rules: [(v) => !!v || 'Full name is required'] },
        { title: 'Change Password', icon: 'mdi-lock-reset', type: 'edit', field: 'password', label: 'New Password', rules: [(v) => !!v || 'Password is required'] },
        { title: 'Edit Email', icon: 'mdi-email', type: 'edit', field: 'email', label: 'Email', rules: [(v) => !!v || 'Email is required'] },
        { title: 'Edit Phone', icon: 'mdi-phone', type: 'edit', field: 'phone', label: 'Phone', rules: [(v) => !!v || 'Phone number is required'] },
        { title: 'Delete Account', icon: 'mdi-delete', type: 'delete', field: 'delete', label: '', rules: [] },
        { title: 'Edit National ID', icon: 'mdi-card-account-details', type: 'edit', field: 'national_id', label: 'National ID', rules: [(v) => !!v || 'Required'] },
        { title: 'Edit KRA PIN', icon: 'mdi-barcode', type: 'edit', field: 'kra_pin', label: 'KRA PIN', rules: [(v) => !!v || 'Required'] },
        { title: 'Edit NSSF No.', icon: 'mdi-bank', type: 'edit', field: 'nssf_no', label: 'NSSF Number', rules: [(v) => !!v || 'Required'] },
  // Add more actions here
      ],
      dialog: false,
      currentAction: null,
      formData: {
        fullname: '',
        password: '',
        email: '',
        phone: ''
      }
    };
  },
  created() {
    this.fetchUserData();
    if (!this.user)       console.warn('EmployeeAccount: missing `user` prop');
  if (!this.roles.length)       console.warn('EmployeeAccount: missing `roles` prop');
  if (!this.permissions.length) console.warn('EmployeeAccount: missing `permissions` prop');
  },
  methods: {
    // fetchUserData() {
    //   // Fetch basic user info
    //   axios.get(`/api/v1/user-details/${this.user.id}`)
    //     .then(response => {
    //       this.user = response.data;
    //       // After getting user ID, fetch additional details
    //       return axios.get(`/api/v1/user-details/${this.user.id}`);
    //     })
    //     .then(response => {
    //       this.userDetails = response.data;
    //     })
    //     .catch(error => {
    //       console.error('Error fetching user data:', error);
    //     });
    // },
    fetchUserData() {
  const userId = this.user.id; // assume this is already available
  axios.get(`/api/v1/user-details/${userId}`)
    .then(response => {
      this.userDetails = response.data;
    })
    .catch(error => {
      console.error('Error fetching user data:', error);
    });
},

    openModal(action) {
      this.currentAction = action;
      this.dialog = true;
    },
    executeAction() {
      if (this.$refs.editForm && !this.$refs.editForm.validate()) return;

      // Update user information based on the current action
      switch (this.currentAction.field) {
        case 'fullname':
          this.user.fullname = this.formData.fullname;
          break;
        case 'password':
          // Perform action to change password
          break;
        case 'email':
          this.user.email = this.formData.email;
          break;
        case 'phone':
          this.user.phone = this.formData.phone;
          break;
        default:
          break;
      }

      this.dialog = false;
    },
    onDetailsUpdated(updatedDetails) {
      // Update local state with the new details
      this.userDetails = { ...updatedDetails };
      // You might want to refresh the entire user profile here
    }
  }
};
</script>
