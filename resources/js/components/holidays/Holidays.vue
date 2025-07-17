<script>
export default {
    data() {
        return {
            search: '',
            loading: false,
            addHolidayDialog: false,
            editHolidayDialog: false,
            editingHoliday: null,
            newHoliday: {
                name: '',
                date: null,
                unit_id: null
            },
            holidays: [],
            branches: [],
            pagination: {
                page: 1,
                rowsPerPage: 10
            },
            headers: [
                { title: '#', value: 'index', sortable: false },
                { title: 'Holiday Name', align: 'start', value: 'name' },
                { title: 'Date', value: 'date' },
                { title: 'Branch', value: 'branch_name', sortable: false },
                { title: 'Action', value: 'action', sortable: false }
            ],
            dataTableOptions: {
                sortBy: ['date'],
                sortDesc: [false]
            }
        };
    },
    computed: {
        formattedAttributes() {
            return this.holidays.map(holiday => ({
                bar: {
                    style: {
                        backgroundColor: 'brown',
                    },
                },
                key: holiday.id,
                highlight: true,
                dates: {
                    start: new Date(holiday.date),
                    end: new Date(holiday.date)
                },
                popover: {
                    label: holiday.name
                }
            }));
        },
        upcomingHolidays() {
            const today = new Date();
            return this.holidays
                .filter(holiday => new Date(holiday.date) >= today)
                .sort((a, b) => new Date(a.date) - new Date(b.date))
                .slice(0, 5); // Show only next 5 upcoming holidays
        }
    },
    created() {
        this.fetchHolidays();
        this.fetchBranches();
    },
    methods: {
        fetchHolidays() {
            this.loading = true;
            axios.get('/api/v1/holidays')
                .then(response => {
                    this.holidays = response.data.holidays || response.data || [];
                })
                .catch(error => {
                    console.error('Error fetching holidays:', error);
                    this.$toastr.error("Failed to fetch holidays");
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        fetchBranches() {
            axios.get('/api/v1/branches')
                .then(response => {
                    this.branches = response.data.branches || response.data || [];
                })
                .catch(error => {
                    console.error('Error fetching branches:', error);
                    this.$toastr.error("Failed to fetch branches");
                });
        },
        addHoliday() {
            if (!this.newHoliday.name || !this.newHoliday.date) {
                this.$toastr.error("Please fill in all required fields");
                return;
            }

            axios.post('/api/v1/holidays', this.newHoliday)
                .then(response => {
                    const newHoliday = response.data.holiday || response.data;
                    this.holidays.push(newHoliday);
                    this.$toastr.success("Holiday created successfully!");
                    this.resetNewHoliday();
                    this.addHolidayDialog = false;
                })
                .catch(error => {
                    console.error('Error adding holiday:', error);
                    this.$toastr.error("Failed to create holiday");
                });
        },
        editHoliday(holiday) {
            this.editingHoliday = { ...holiday };
            this.editHolidayDialog = true;
        },
        updateHoliday() {
            if (!this.editingHoliday.name || !this.editingHoliday.date) {
                this.$toastr.error("Please fill in all required fields");
                return;
            }

            axios.put(`/api/v1/holidays/${this.editingHoliday.id}`, this.editingHoliday)
                .then(response => {
                    const updatedHoliday = response.data.holiday || response.data;
                    const index = this.holidays.findIndex(h => h.id === this.editingHoliday.id);
                    if (index !== -1) {
                        this.$set(this.holidays, index, updatedHoliday);
                    }
                    this.$toastr.success("Holiday updated successfully!");
                    this.editHolidayDialog = false;
                    this.editingHoliday = null;
                })
                .catch(error => {
                    console.error('Error updating holiday:', error);
                    this.$toastr.error("Failed to update holiday");
                });
        },
        deleteHoliday(holiday) {
            if (!holiday.id) {
                this.$toastr.error("Invalid holiday data");
                return;
            }

            if (!confirm(`Are you sure you want to delete "${holiday.name}"?`)) {
                return;
            }

            axios.delete(`/api/v1/holidays/${holiday.id}`)
                .then(() => {
                    this.holidays = this.holidays.filter(h => h.id !== holiday.id);
                    this.$toastr.success("Holiday deleted successfully!");
                })
                .catch(error => {
                    console.error('Error deleting holiday:', error);
                    this.$toastr.error("Failed to delete holiday");
                });
        },
        unmarkHoliday(holiday) {
            // Assuming this sets a holiday as inactive rather than deleting it
            if (!holiday.id) {
                this.$toastr.error("Invalid holiday data");
                return;
            }

            const updatedHoliday = { ...holiday, is_active: false };
            
            axios.put(`/api/v1/holidays/${holiday.id}`, updatedHoliday)
                .then(() => {
                    const index = this.holidays.findIndex(h => h.id === holiday.id);
                    if (index !== -1) {
                        this.$set(this.holidays, index, updatedHoliday);
                    }
                    this.$toastr.success("Holiday unmarked successfully!");
                })
                .catch(error => {
                    console.error('Error unmarking holiday:', error);
                    this.$toastr.error("Failed to unmark holiday");
                });
        },
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },
        clearSearch() {
            this.search = '';
        },
        resetNewHoliday() {
            this.newHoliday = {
                name: '',
                date: null,
                unit_id: null
            };
        },
        cancelEdit() {
            this.editHolidayDialog = false;
            this.editingHoliday = null;
        },
        getBranchName(unitId) {
            const branch = this.branches.find(b => b.id === unitId);
            return branch ? branch.name : 'N/A';
        }
    }
};
</script>

<template>
    <v-container fluid>
        <v-row>
            <v-col cols="12" md="8">
                <v-text-field 
                    v-model="search" 
                    label="Search Holidays" 
                    clearable 
                    outlined 
                    dense 
                    @click:clear="clearSearch"
                    prepend-inner-icon="mdi-magnify">
                </v-text-field>
                
                <v-card class="mt-4 elevation-12">

                      <v-btn @click="addHolidayDialog = true" outlined color="white" style="border-color: #fff; color: #fff;">
                            <v-icon size="22" left style="color: #fff;">mdi-plus</v-icon>
                            Add Holiday
                        </v-btn>
                    <v-toolbar flat color="primary" dark>
                        <v-toolbar-title>Holidays</v-toolbar-title>
                      
                    </v-toolbar>
                    
                    <v-data-table 
                        :headers="headers" 
                        item-key="id" 
                        :items="holidays" 
                        :search="search"
                        :options="dataTableOptions" 
                        :loading="loading"
                        :items-per-page="pagination.rowsPerPage"
                        :footer-props="{
                            'items-per-page-options': [10, 25, 50, 100]
                        }"
                        class="elevation-1">
                        
                        <template v-slot:item="{ item, index }">
                            <tr>
                                <td>{{ index + 1 }}</td>
                                <td>{{ item.name }}</td>
                                <td>{{ formatDate(item.date) }}</td>
                                <td>{{ getBranchName(item.unit_id) }}</td>
                                <td>
                                    <v-btn icon small color="info" @click="editHoliday(item)" class="mr-1">
                                        <v-icon>mdi-pencil</v-icon>
                                    </v-btn>
                                    <v-btn icon small color="warning" @click="unmarkHoliday(item)" class="mr-1">
                                        <v-icon>mdi-close-circle</v-icon>
                                    </v-btn>
                                    <v-btn icon small color="error" @click="deleteHoliday(item)">
                                        <v-icon>mdi-delete</v-icon>
                                    </v-btn>
                                </td>
                            </tr>
                        </template>
                        
                        <template v-slot:no-data>
                            <div class="text-center pa-4">
                                <v-icon size="48" color="grey lighten-1">mdi-calendar-remove</v-icon>
                                <p class="mt-2 grey--text">No holidays found</p>
                            </div>
                        </template>
                    </v-data-table>
                </v-card>
            </v-col>

            <v-col cols="12" md="4">
                <v-row>
                    <v-col cols="12">
                        <v-card class="elevation-12">
                            <v-card-title class="text-h6">Upcoming Holidays</v-card-title>
                            <v-divider></v-divider>
                            <v-list dense>
                                <template v-if="upcomingHolidays.length > 0">
                                    <v-list-item v-for="holiday in upcomingHolidays" :key="holiday.id">
                                        <v-list-item-content>
                                            <v-row align="center">
                                                <v-col cols="6">
                                                    <v-list-item-title class="font-weight-bold">{{ holiday.name }}</v-list-item-title>
                                                </v-col>
                                                <v-col cols="6" class="text-right">
                                                    <v-list-item-subtitle>{{ formatDate(holiday.date) }}</v-list-item-subtitle>
                                                </v-col>
                                            </v-row>
                                        </v-list-item-content>
                                    </v-list-item>
                                </template>
                                <template v-else>
                                    <v-list-item>
                                        <v-list-item-content class="text-center">
                                            <v-list-item-title class="grey--text">No upcoming holidays</v-list-item-title>
                                        </v-list-item-content>
                                    </v-list-item>
                                </template>
                            </v-list>
                        </v-card>
                    </v-col>
                    
                    <v-col cols="12">
                        <v-card class="calendar-card elevation-12">
                            <v-card-title class="text-h6">Calendar</v-card-title>
                            <v-divider></v-divider>
                            <div class="d-flex align-center justify-center">
                                <VCalendar 
                                    title-position="left" 
                                    :attributes="formattedAttributes" 
                                    class="mt-4">
                                </VCalendar>
                            </div>
                        </v-card>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>

        <!-- Add Holiday Dialog -->
        <v-dialog v-model="addHolidayDialog" max-width="500px" persistent>
            <v-card>
                <v-card-title class="text-h6">Add New Holiday</v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <v-form ref="addForm">
                        <v-text-field 
                            v-model="newHoliday.name" 
                            label="Holiday Name*" 
                            outlined 
                            dense
                            :rules="[v => !!v || 'Holiday name is required']">
                        </v-text-field>
                        <v-text-field 
                            v-model="newHoliday.date" 
                            label="Date*" 
                            type="date" 
                            outlined 
                            dense
                            :rules="[v => !!v || 'Date is required']">
                        </v-text-field>
                        <v-select 
                            v-model="newHoliday.unit_id" 
                            item-title="name" 
                            item-value="id"
                            :items="branches" 
                            label="Branch"
                            clearable 
                            outlined 
                            dense>
                        </v-select>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="addHolidayDialog = false; resetNewHoliday()" color="error" text>Cancel</v-btn>
                    <v-btn @click="addHoliday" color="success" text>Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Edit Holiday Dialog -->
        <v-dialog v-model="editHolidayDialog" max-width="500px" persistent>
            <v-card>
                <v-card-title class="text-h6">Edit Holiday</v-card-title>
                <v-divider></v-divider>
                <v-card-text v-if="editingHoliday">
                    <v-form ref="editForm">
                        <v-text-field 
                            v-model="editingHoliday.name" 
                            label="Holiday Name*" 
                            outlined 
                            dense
                            :rules="[v => !!v || 'Holiday name is required']">
                        </v-text-field>
                        <v-text-field 
                            v-model="editingHoliday.date" 
                            label="Date*" 
                            type="date" 
                            outlined 
                            dense
                            :rules="[v => !!v || 'Date is required']">
                        </v-text-field>
                        <v-select 
                            v-model="editingHoliday.unit_id" 
                            item-title="name" 
                            item-value="id"
                            :items="branches" 
                            label="Branch"
                            clearable 
                            outlined 
                            dense>
                        </v-select>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="cancelEdit" color="error" text>Cancel</v-btn>
                    <v-btn @click="updateHoliday" color="success" text>Update</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<style scoped>
.calendar-card {
    padding: 20px;
}
</style>