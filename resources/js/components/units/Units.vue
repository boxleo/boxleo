<template>
  <v-container fluid>
    <v-row>
      <v-col class="text-right">
        <v-btn @click="addUnitDialog = true" color="primary" dark>
          <v-icon left>mdi-plus</v-icon>
          Add Branch
        </v-btn>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <v-data-table 
          :headers="tableHeaders" 
          :items="branches" 
          item-value="id" 
          class="elevation-1" 
          dense
          :search="search"
        >
          <template v-slot:[`item.index`]="{ index }">
            {{ index + 1 }}
          </template>
          <template v-slot:[`item.actions`]="{ item }">
            <v-icon color="success" class="mx-1" @click="openEditUnitDialog(item)" style="cursor: pointer;">
              mdi-pencil
            </v-icon>
            <v-icon color="error" class="mx-1" @click="confirmDeleteUnit(item.id)" style="cursor: pointer;">
              mdi-delete
            </v-icon>
          </template>
          <template v-slot:top>
            <v-text-field
              v-model="search"
              label="Search branches"
              prepend-icon="mdi-magnify"
              clearable
              single-line
              hide-details
              class="mx-4 mt-2"
            ></v-text-field>
          </template>
        </v-data-table>
      </v-col>
    </v-row>

    <!-- Add Branch Dialog -->
    <v-dialog v-model="addUnitDialog" max-width="800px" persistent>
      <v-card>
        <v-card-title class="headline primary--text">
          <v-icon left color="primary">mdi-plus</v-icon>
          Add Branch
        </v-card-title>
        <v-card-text>
          <v-form ref="addUnitForm" @submit.prevent="submitAddUnitForm" v-model="formValid">
            <v-row>
              <v-col cols="12" md="6">
                <v-autocomplete 
                  v-model="newUnit.name" 
                  :items="countries" 
                  item-title="label" 
                  item-value="value"
                  label="Select Country"
                  :rules="[(v) => !!v || 'Country is required']"
                  required
                ></v-autocomplete>
              </v-col>
              <v-col cols="12" md="6">
                <v-autocomplete 
                  v-model="newUnit.timezone" 
                  :items="timezones" 
                  item-title="label" 
                  item-value="value"
                  label="Select Timezone"
                  :rules="[(v) => !!v || 'Timezone is required']"
                  required
                ></v-autocomplete>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="newUnit.work_start_time" 
                  label="Work Start Time" 
                  type="time"
                  :rules="[(v) => !!v || 'Work start time is required']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="newUnit.late_threshold" 
                  label="Late Threshold " 
                  type="time"
                  :rules="[(v) => !!v || 'Late threshold is required']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="newUnit.weekend_day"
                  :items="weekdays"
                  label="Weekend Day"
                  :rules="[(v) => !!v || 'Weekend day is required']"
                  required
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="newUnit.weekend_clock_in_time" 
                  label="Weekend Clock In Time"
                  type="time"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="newUnit.weekend_clock_out_time" 
                  label="Weekend Clock Out Time"
                  type="time"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="newUnit.weekday_threshold" 
                  label="Weekday Threshold"
                  type="time"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="newUnit.weekend_threshold" 
                  label="Weekend Threshold"
                  type="time"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="newUnit.clock_in_time" 
                  label="Clock In Time" 
                  type="time"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="newUnit.clock_out_time" 
                  label="Clock Out Time" 
                  type="time"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="newUnit.phone" 
                  label="Phone" 
                  :rules="[(v) => !!v || 'Phone is required']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea 
                  v-model="newUnit.address" 
                  label="Address"
                  :rules="[(v) => !!v || 'Address is required']" 
                  required
                  rows="2"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="closeAddUnitDialog" color="error" text>
            <v-icon left>mdi-close</v-icon>
            Cancel
          </v-btn>
          <v-btn @click="addUnit" color="success" :disabled="!formValid" text>
            <v-icon left>mdi-check-circle</v-icon>
            Add
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Edit Branch Dialog -->
    <v-dialog v-model="editUnitDialog" max-width="800px" persistent>
      <v-card>
        <v-card-title class="headline primary--text">
          <v-icon left color="primary">mdi-pencil</v-icon>
          Edit Branch
        </v-card-title>
        <v-card-text>
          <v-form ref="editUnitForm" @submit.prevent="submitEditUnit" v-model="editFormValid">
            <v-row>
              <v-col cols="12" md="6">
                <v-autocomplete 
                  v-model="editUnit.name" 
                  :items="countries" 
                  item-title="label" 
                  item-value="value"
                  label="Select Country"
                  
                  required
                ></v-autocomplete>
              </v-col>
              <v-col cols="12" md="6">
                <v-autocomplete 
                  v-model="editUnit.timezone" 
                  :items="timezones" 
                  item-title="label" 
                  item-value="value"
                  label="Select Timezone"
                  
                  required
                ></v-autocomplete>
              </v-col>
              <!-- <v-col cols="12" md="6">
                <v-text-field 
                  v-model="editUnit.work_start_time" 
                  label="Work Start Time" 
                  type="time"
                  
                  required
                ></v-text-field>
              </v-col> -->
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="editUnit.clock_in_time" 
                  label="Clock In Time" 
                  type="time"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="editUnit.clock_out_time" 
                  label="Clock Out Time" 
                  type="time"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="editUnit.late_threshold" 
                  label="Late Threshold " 
                  type="Time"
                  
                  required
                ></v-text-field>
              </v-col>
              <!-- <v-col cols="12" md="6">
                <v-select
                  v-model="editUnit.weekend_day"
                  :items="weekdays"
                  label="Weekend Day"
                  
                  required
                ></v-select>
              </v-col> -->
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="editUnit.weekend_clock_in_time" 
                  label="Weekend Clock In Time"
                  type="time"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="editUnit.weekend_clock_out_time" 
                  label="Weekend Clock Out Time"
                  
                  type="time"
                ></v-text-field>
              </v-col>
              <!-- <v-col cols="12" md="6">
                <v-text-field 
                  v-model="editUnit.weekday_threshold" 
                  label="Weekday Threshold"
                  type="time"
                ></v-text-field>
              </v-col> -->
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="editUnit.weekend_threshold" 
                  label="Weekend Threshold"
                  type="time"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="editUnit.phone" 
                  label="Phone"
                  :rules="[(v) => !!v || 'Phone is required']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-textarea 
                  v-model="editUnit.address" 
                  label="Address"
                  :rules="[(v) => !!v || 'Address is required']"
                  required
                  rows="2"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="closeEditUnitDialog" color="error" text>
            <v-icon left>mdi-close</v-icon>
            Cancel
          </v-btn>
          <v-btn @click="updateUnit" color="success" :disabled="!editFormValid" text>
            <v-icon left>mdi-check-circle</v-icon>
            Update
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="headline error--text">
          <v-icon left color="error">mdi-alert</v-icon>
          Confirm Delete
        </v-card-title>
        <v-card-text>
          Are you sure you want to delete this branch? This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="deleteDialog = false" text>Cancel</v-btn>
          <v-btn @click="deleteUnit" color="error" text>Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  setup() {
    const timezones = ref([]);

    onMounted(async () => {
    try {
      // Fetch timezones
      const timezoneRes = await axios.get('/api/v1/timezones');
      timezones.value = timezoneRes.data.map(tz => ({
      label: `${tz.value} (${tz.utc_time}) - ${tz.country}`,
      value: tz.value,
      }));

      // Fetch countries
      const countryRes = await axios.get('/api/v1/countries');
      countries.value = countryRes.data.map(country => ({
        label: `${country.emoji} ${country.value} (${country.key})`,
        value: country.value,
        calling_code: country.calling_code,
        subdivisions: country.subdivision,
        currency: country.currency,



      }));
    } catch (error) {
      console.error('Error fetching data:', error);
    }
    });

    const countries = ref([]);
    return { timezones, countries };
    },
  data() {
    return {
      base_url: '/',
      formValid: false,
      editFormValid: false,
      deleteDialog: false,
      deleteUnitId: null,
      weekdays: [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday'
      ],
      newUnit: {
        name: '',
        timezone: '',
        work_start_time: '',
        late_threshold: '',
        weekend_day: 'Sunday',
        weekend_clock_in_time: '',
        weekend_clock_out_time: '',
        weekday_threshold: '',
        weekend_threshold: '',
        clock_in_time: '',
        clock_out_time: '',
        address: '',
        phone: '',
      },
      editUnit: {
        id: 0,
        name: '',
        timezone: '',
        work_start_time: '',
        late_threshold: '',
        weekend_day: '',
        weekend_clock_in_time: '',
        weekend_clock_out_time: '',
        weekday_threshold: '',
        weekend_threshold: '',
        clock_in_time: '',
        clock_out_time: '',
        address: '',
        phone: '',
      },
      branches: [],
      search: '',
      addUnitDialog: false,
      editUnitDialog: false,
      tableHeaders: [
        { title: '#', value: 'index', width: '5%' },
        { title: 'Branch Name', value: 'name', width: '20%' },
        { title: 'Address', value: 'address', width: '30%' },
        { title: 'Contact', value: 'phone', width: '15%' },
        { title: 'Timezone', value: 'timezone', width: '15%' },
        { title: 'Employees', value: 'users.length', sortable: false, width: '10%' },
        { title: 'Actions', value: 'actions', sortable: false, width: '10%' },
      ],
    };
  },
  created() {
    this.fetchUnits();
  },
  methods: {
    fetchUnits() {
      const apiUrl = this.base_url + 'api/v1/branches';

      axios.get(apiUrl)
        .then(response => {
          this.branches = response.data.branches;
        })
        .catch(error => {
          console.error('Error fetching branches:', error);
          this.$toastr.error('Error loading branches. Please refresh the page.');
        });
    },
    openEditUnitDialog(branch) {
      this.editUnit = { ...branch };
      this.editUnitDialog = true;
    },
    updateUnit() {
      if (!this.editFormValid) {
        this.$refs.editUnitForm.validate();
        return;
      }

      const apiUrl = this.base_url + 'api/v1/branches/' + this.editUnit.id;

      axios.put(apiUrl, this.editUnit)
        .then(() => {
          this.closeEditUnitDialog();
          this.fetchUnits();
          this.$toastr.success('Branch updated successfully!');
        })
        .catch(error => {
          this.$toastr.error('Error updating branch. Please try again.');
          console.error('Error updating branch:', error);
        });
    },
    submitEditUnit() {
      this.updateUnit();
    },
    closeEditUnitDialog() {
      this.editUnitDialog = false;
      this.$refs.editUnitForm.reset();
      this.editUnit = {
        id: 0,
        name: '',
        timezone: '',
        work_start_time: '',
        late_threshold: '',
        weekend_day: '',
        weekend_clock_in_time: '',
        weekend_clock_out_time: '',
        weekday_threshold: '',
        weekend_threshold: '',
        clock_in_time: '',
        clock_out_time: '',
        address: '',
        phone: '',
      };
    },
    confirmDeleteUnit(unitId) {
      this.deleteUnitId = unitId;
      this.deleteDialog = true;
    },
    deleteUnit() {
      if (!this.deleteUnitId) return;
      
      const apiUrl = this.base_url + 'api/v1/branches/' + this.deleteUnitId;

      axios.delete(apiUrl)
        .then(() => {
          this.fetchUnits();
          this.$toastr.success('Branch deleted successfully!');
          this.deleteDialog = false;
          this.deleteUnitId = null;
        })
        .catch(error => {
          this.$toastr.error('Error deleting branch. Please try again.');
          console.error('Error deleting branch:', error);
        });
    },
    addUnit() {
      if (!this.formValid) {
        this.$refs.addUnitForm.validate();
        return;
      }

      const apiUrl = this.base_url + 'api/v1/branches';

      axios.post(apiUrl, this.newUnit)
        .then(() => {
          this.closeAddUnitDialog();
          this.fetchUnits();
          this.$toastr.success('Branch added successfully!');
        })
        .catch(error => {
          this.$toastr.error('Error adding branch. Please try again.');
          console.error('Error adding branch:', error);
        });
    },
    closeAddUnitDialog() {
      this.addUnitDialog = false;
      this.$refs.addUnitForm.reset();
      this.newUnit = {
        name: '',
        timezone: '',
        work_start_time: '',
        late_threshold: '',
        weekend_day: 'Sunday',
        weekend_clock_in_time: '',
        weekend_clock_out_time: '',
        weekday_threshold: '',
        weekend_threshold: '',
        clock_in_time: '',
        clock_out_time: '',
        address: '',
        phone: '',
      };
    },
    submitAddUnitForm() {
      this.addUnit();
    },
  },
};
</script>

<style scoped>
.v-data-table :deep(.v-data-table__wrapper) {
  overflow-x: auto;
}
</style>