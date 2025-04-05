<template>
  <v-container>
    <!-- Add Office Button -->
    <v-row>
      <v-col class="text-right">
        <v-btn @click="openOfficeDialog()" color="primary" outlined>
          <v-icon left>mdi-plus</v-icon>
          Add Office
        </v-btn>
      </v-col>
    </v-row>

    <!-- Offices Table -->
    <v-row>
      <v-col cols="12">
        <v-data-table :headers="headers" :items="offices" item-value="id" class="elevation-1" dense>
          <template #item.index="{ index }">
            {{ index + 1 }}
          </template>

          <template #item.actions="{ item }">
            <v-icon class="mx-1" color="info" @click="viewOffice(item)">mdi-eye</v-icon>
            <v-icon class="mx-1" color="success" @click="openOfficeDialog(item)">mdi-pencil</v-icon>
            <v-icon class="mx-1" color="error" @click="confirmDeleteOffice(item.id)">mdi-delete</v-icon>
          </template>
        </v-data-table>
      </v-col>
    </v-row>

    <!-- Office Dialog (Combined for Add/Edit) -->
    <v-dialog v-model="officeDialog.show" max-width="600px" persistent>
      <v-card>
        <v-card-title>
          <v-icon class="mr-2">{{ officeDialog.isEdit ? 'mdi-pencil' : 'mdi-plus' }}</v-icon>
          {{ officeDialog.isEdit ? 'Edit Office' : 'Add Office' }}
        </v-card-title>
        <v-card-text>
          <v-form ref="officeForm" @submit.prevent="saveOffice">
            <v-text-field 
              v-model="officeForm.name" 
              label="Office Name" 
              :rules="[v => !!v || 'Name is required']"
              required
            ></v-text-field>
            
            <v-select 
              v-model="officeForm.unit_id" 
              :items="branches" 
              label="Branch" 
              item-value="id"
              item-title="name"
              :rules="[v => !!v || 'Branch is required']"
              required
            ></v-select>
            
            <v-text-field 
              v-model="officeForm.phone" 
              label="Phone" 
              :rules="[v => !!v || 'Phone is required']"
              required
            ></v-text-field>
            
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="officeForm.latitude" 
                  label="Latitude" 
                  type="number" 
                  step="0.000001"
                  :rules="[v => !v || (v >= -90 && v <= 90) || 'Latitude must be between -90 and 90']"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="officeForm.longitude" 
                  label="Longitude" 
                  type="number" 
                  step="0.000001"
                  :rules="[v => !v || (v >= -180 && v <= 180) || 'Longitude must be between -180 and 180']"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="closeOfficeDialog" color="error" text>Cancel</v-btn>
          <v-btn 
            @click="saveOffice" 
            color="success" 
            :loading="loading"
            :disabled="loading"
          >
            <v-icon left>mdi-check-circle</v-icon>
            {{ officeDialog.isEdit ? 'Update' : 'Add' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Confirmation Dialog -->
    <v-dialog v-model="confirmDialog.show" max-width="400">
      <v-card>
        <v-card-title class="text-h6">
          Confirm Delete
        </v-card-title>
        <v-card-text>
          Are you sure you want to delete this office? This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey darken-1" text @click="confirmDialog.show = false">
            Cancel
          </v-btn>
          <v-btn 
            color="error" 
            text 
            @click="deleteOffice(confirmDialog.id)"
            :loading="loading"
            :disabled="loading"
          >
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      base_url: '/',
      loading: false,
      headers: [
        { title: '#', value: 'index' },
        { title: 'Name', value: 'name' },
        { title: 'Branch', value: 'unit.name' },
        { title: 'Phone', value: 'phone' },
        { title: 'Latitude', value: 'latitude' },
        { title: 'Longitude', value: 'longitude' },
        { title: 'Employees', value: 'users.length' },
        { title: 'Actions', value: 'actions', sortable: false },
      ],
      officeForm: this.getEmptyOfficeForm(),
      officeDialog: {
        show: false,
        isEdit: false,
      },
      confirmDialog: {
        show: false,
        id: null,
      },
      offices: [],
      branches: [],
    };
  },
  created() {
    this.fetchOffices();
    this.fetchUnits();
  },
  methods: {
    getEmptyOfficeForm() {
      return {
        id: null,
        name: '',
        unit_id: null,
        phone: '',
        latitude: '',
        longitude: '',
      };
    },
    fetchOffices() {
      this.loading = true;
      const apiUrl = this.base_url + 'api/v1/offices';

      axios
        .get(apiUrl)
        .then((response) => {
          this.offices = response.data.offices;
        })
        .catch((error) => {
          this.$toastr.error('Error fetching offices');
          console.error('Error fetching offices:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    fetchUnits() {
      this.loading = true;
      const apiUrl = this.base_url + 'api/v1/branches';

      axios
        .get(apiUrl)
        .then((response) => {
          this.branches = response.data.branches;
        })
        .catch((error) => {
          this.$toastr.error('Error fetching branches');
          console.error('Error fetching branches:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    viewOffice(office) {
      // Implementation for viewing an office
      console.log('View Office:', office);
    },
    openOfficeDialog(office = null) {
      // Reset form validation
      if (this.$refs.officeForm) {
        this.$refs.officeForm.resetValidation();
      }
      
      if (office) {
        // Edit mode - clone the office data to avoid direct mutation
        this.officeDialog.isEdit = true;
        this.officeForm = { ...office };
        
        // Ensure numeric values are treated as numbers for form validation
        if (this.officeForm.latitude) this.officeForm.latitude = parseFloat(this.officeForm.latitude);
        if (this.officeForm.longitude) this.officeForm.longitude = parseFloat(this.officeForm.longitude);
      } else {
        // Add mode
        this.officeDialog.isEdit = false;
        this.officeForm = this.getEmptyOfficeForm();
      }
      
      this.officeDialog.show = true;
    },
    closeOfficeDialog() {
      this.officeDialog.show = false;
      // Clear form data after dialog is closed
      setTimeout(() => {
        this.officeForm = this.getEmptyOfficeForm();
      }, 300);
    },
    saveOffice() {
      // Form validation
      if (this.$refs.officeForm && !this.$refs.officeForm.validate()) {
        return;
      }
      
      this.loading = true;
      
      if (this.officeDialog.isEdit) {
        this.updateOffice();
      } else {
        this.addOffice();
      }
    },
    addOffice() {
      const apiUrl = this.base_url + 'api/v1/offices';

      axios
        .post(apiUrl, this.officeForm)
        .then((response) => {
          this.closeOfficeDialog();
          this.fetchOffices();
          this.$toastr.success('Office added successfully!');
        })
        .catch((error) => {
          this.$toastr.error('Error adding office. Please try again.');
          console.error('Error adding office:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    updateOffice() {
      const apiUrl = this.base_url + 'api/v1/offices/' + this.officeForm.id;

      axios
        .put(apiUrl, this.officeForm)
        .then((response) => {
          this.closeOfficeDialog();
          this.fetchOffices();
          this.$toastr.success('Office updated successfully!');
        })
        .catch((error) => {
          this.$toastr.error('Error updating office. Please try again.');
          console.error('Error updating office:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    confirmDeleteOffice(officeId) {
      this.confirmDialog = {
        show: true,
        id: officeId
      };
    },
    deleteOffice(officeId) {
      this.loading = true;
      const apiUrl = this.base_url + 'api/v1/offices/' + officeId;

      axios
        .delete(apiUrl)
        .then((response) => {
          this.fetchOffices();
          this.$toastr.success('Office deleted successfully!');
          this.confirmDialog.show = false;
        })
        .catch((error) => {
          this.$toastr.error('Error deleting office. Please try again.');
          console.error('Error deleting office:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
};
</script>