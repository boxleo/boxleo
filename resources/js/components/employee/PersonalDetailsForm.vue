<template>
    <v-col cols="12">
      <v-card class="mb-5">
        <v-card-title class="headline">
          Personal Details
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            text
            @click="editing = !editing"
            v-if="!editing"
          >
            <v-icon left>mdi-pencil</v-icon>
            Edit Details
          </v-btn>
          <v-btn
            color="error"
            text
            @click="cancelEdit"
            v-else
          >
            <v-icon left>mdi-close</v-icon>
            Cancel
          </v-btn>
        </v-card-title>
        
        <v-divider></v-divider>
        
        <v-card-text>
          <v-form ref="detailsForm" v-model="valid" @submit.prevent="saveDetails">
            <v-row>
              <!-- Personal Information Section -->
              <v-col cols="12">
                <h3 class="subtitle-1 font-weight-bold mb-3">Personal Information</h3>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.national_id"
                  label="National ID"
                  outlined
                  dense
                  :disabled="!editing"
                  :rules="[(v) => !!v || 'National ID is required']"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-select
                  v-model="formData.gender"
                  label="Gender"
                  outlined
                  dense
                  :items="['Male', 'Female', 'Other']"
                  :disabled="!editing"
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-select
                  v-model="formData.marital_status"
                  label="Marital Status"
                  outlined
                  dense
                  :items="['Single', 'Married', 'Divorced', 'Widowed']"
                  :disabled="!editing"
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.staffID"
                  label="Staff ID"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
              
              <!-- Location Information -->
              <v-col cols="12">
                <h3 class="subtitle-1 font-weight-bold mb-3">Location Information</h3>
              </v-col>
              
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="formData.country"
                  label="Country"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="formData.nationality"
                  label="Nationality"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="formData.region"
                  label="Region"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
              
              <!-- Next of Kin Information -->
              <v-col cols="12">
                <h3 class="subtitle-1 font-weight-bold mb-3">Next of Kin Information</h3>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.kin"
                  label="Next of Kin"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.kin_contact"
                  label="Next of Kin Contact"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.spouse"
                  label="Spouse Name"
                  outlined
                  dense
                  :disabled="!editing || formData.marital_status !== 'Married'"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="formData.spouse_no"
                  label="Spouse Contact"
                  outlined
                  dense
                  :disabled="!editing || formData.marital_status !== 'Married'"
                ></v-text-field>
              </v-col>
              
              <!-- Financial Information -->
              <v-col cols="12">
                <h3 class="subtitle-1 font-weight-bold mb-3">Financial Information</h3>
              </v-col>
              
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="formData.bank_name"
                  label="Bank Name"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="formData.bank_branch"
                  label="Bank Branch"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="formData.bank_account"
                  label="Bank Account"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="formData.mpesa_no"
                  label="MPesa Number"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
              
              <!-- Official Documentation -->
              <v-col cols="12">
                <h3 class="subtitle-1 font-weight-bold mb-3">Official Documentation</h3>
              </v-col>
              
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="formData.nhif_no"
                  label="NHIF Number"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="formData.nssf_no"
                  label="NSSF Number"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="formData.kra_pin"
                  label="KRA PIN"
                  outlined
                  dense
                  :disabled="!editing"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        
        <v-card-actions v-if="editing">
          <v-spacer></v-spacer>
          <v-btn
            color="error"
            outlined
            @click="cancelEdit"
            class="mr-3"
          >
            Cancel
          </v-btn>
          <v-btn
            color="success"
            @click="saveDetails"
            :loading="loading"
            :disabled="!valid"
          >
            <v-icon left>mdi-content-save</v-icon>
            Save Changes
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-col>
  </template>
  
  <script>
  export default {
    props: {
      userId: {
        type: [Number, String],
        required: true
      },
      userData: {
        type: Object,
        default: () => ({})
      }
    },
    data() {
      return {
        editing: false,
        valid: true,
        loading: false,
        formData: {
          kin: '',
          kin_contact: '',
          bank_name: '',
          bank_branch: '',
          bank_account: '',
          marital_status: '',
          country: '',
          nationality: '',
          region: '',
          spouse: '',
          spouse_no: '',
          mpesa_no: '',
          nhif_no: '',
          kra_pin: '',
          nssf_no: '',
          national_id: '',
          gender: '',
          staffID: ''
        },
        originalFormData: {}
      };
    },
    created() {
      this.loadUserDetails();
    },
    methods: {
      loadUserDetails() {
        // Initialize with data from props if available
        if (this.userData && Object.keys(this.userData).length) {
          this.formData = { ...this.formData, ...this.userData };
        } else {
          // Otherwise fetch from API
          this.loading = true;
          axios.get(`/api/user-details/${this.userId}`)
            .then(response => {
              this.formData = { ...this.formData, ...response.data };
              // Store original data for cancel operation
              this.originalFormData = { ...this.formData };
            })
            .catch(error => {
              console.error('Error loading user details:', error);
              this.$toast.error('Failed to load user details');
            })
            .finally(() => {
              this.loading = false;
            });
        }
        // Store original data for cancel operation
        this.originalFormData = { ...this.formData };
      },
      
      cancelEdit() {
        // Restore original data
        this.formData = { ...this.originalFormData };
        this.editing = false;
      },
      
      saveDetails() {
        if (!this.$refs.detailsForm.validate()) return;
        
        this.loading = true;
        
        // Add user_id to the payload
        const payload = {
          ...this.formData,
          user_id: this.userId
        };
        
        axios.post('/api/user-details/update', payload)
          .then(response => {
            this.$toast.success('Personal details updated successfully');
            this.editing = false;
            // Update original data
            this.originalFormData = { ...this.formData };
            
            // Emit event to notify parent component
            this.$emit('details-updated', this.formData);
          })
          .catch(error => {
            console.error('Error updating user details:', error);
            this.$toast.error('Failed to update personal details');
          })
          .finally(() => {
            this.loading = false;
          });
      }
    }
  };
  </script>