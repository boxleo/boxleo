<script>
export default {

    data() {
        return {
            search: '',
            addHolidayDialog: false,
            newHoliday: {
                name: '',
                date: null,
                branches: []
            },
            holidays: [],
            branches: [],
            pagination: {
                page: 1,
                rowsPerPage: 10
            },
            headers: [
                { title: '#', value: 'index' },
                { title: 'Holiday Name', align: 'start', value: 'name' },
                { title: 'Date', value: 'date' },
                { title: 'Action', value: 'action', sortable: false },
                // { title: 'Branches', value: 'name', sortable: false }
            ],
            dataTableOptions: {
                sortRowsBy: ['date']
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
                key: holiday.name,
                highlight: true,
                dates: {
                    start: new Date(holiday.date),
                    end: new Date(holiday.date)
                },
                popover: {
                    label: holiday.name
                }
            }));
        }
    },
    created() {
        this.fetchHolidays();
        this.fetchBranches();
    },
    methods: {
        fetchHolidays() {
            // Fetch holidays from your API endpoint
            this.loading = true;
            axios.get('/api/v1/holidays')
                .then(response => {
                    this.holidays = response.data.holidays;
                })
                .catch(error => {
                    console.error('Error fetching holidays:', error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        fetchBranches() {
            axios.get('/api/v1/branches')
                .then(response => {
                    this.branches = response.data.branches;
                })
                .catch(error => {
                    console.error('Error fetching branches:', error);
                })
        },
        addHoliday() {
            if (this.newHoliday.name && this.newHoliday.date) {
                axios.post('/api/v1/holidays', this.newHoliday)
                    .then(response => {
                        this.holidays.push(response.data.holiday);
                        this.$toastr.success("Holiday created!")
                        this.newHoliday.name = '';
                        this.newHoliday.date = null;
                        this.addHolidayDialog = false;
                    })
                    .catch(error => {
                        console.error('Error adding holiday:', error);
                    });
            }
        },
      
        deleteHoliday(holiday) {
    if (!holiday.id) return; // Ensure holiday has an ID

    axios.delete(`/api/v1/holidays/${holiday.id}`)
        .then(() => {
            this.holidays = this.holidays.filter(h => h.id !== holiday.id);
            this.$toastr.success("Holiday deleted successfully!");
        })
        .catch(error => {
            console.error('Error deleting holiday:', error);
            this.$toastr.error("Failed to delete holiday.");
        });
}
,
        formatDate(date) {
            // Format date as needed
            return date ? new Date(date).toLocaleDateString() : '';
        },
        clearSearch() {
            this.search = '';
        }
    }
};
</script>
<template>
    <v-container-fluid>
        <v-row>
            <v-col cols="12" md="8">
                <v-text-field 
                    v-model="search" 
                    label="Search Holidays" 
                    clearable 
                    outlined 
                    dense 
                    @clear="clearSearch"
                    prepend-inner-icon="mdi-magnify">
                </v-text-field>
                <v-card class="mt-4 elevation-12">
                    <v-btn color="white" @click="addHolidayDialog = true" outlined>
                            <v-icon size="22" left>mdi-plus</v-icon>Add Holiday
                        </v-btn>
                    <v-toolbar flat color="primary" dark>
                        <v-toolbar-title>Holidays</v-toolbar-title>
                        <v-spacer></v-spacer>
                       
                    </v-toolbar>
                    <v-data-table 
                        :headers="headers" 
                        item-key="id" 
                        :items="holidays" 
                        :search="search"
                        :options="dataTableOptions" 
                        :pagination.sync="pagination"
                        :rows-per-page-items="[10, 25, 50, 100]" 
                        class="elevation-1">
                        <template v-slot:top>
                            <v-divider></v-divider>
                        </template>
                        <template v-slot:item="{ item, index }">
                            <tr>
                                <td>{{ index + 1 }}</td>
                                <td>{{ item.name }}</td>
                                <td>{{ formatDate(item.date) }}</td>
                                <td>
                                    <v-btn icon small color="info" @click="editHoliday(item)">
                                        <v-icon>mdi-pen</v-icon>
                                    </v-btn>
                                    <v-btn icon small color="warning" @click="unmarkHoliday(item)">
                                        <v-icon>mdi-close-circle</v-icon>
                                    </v-btn>
                                    <v-btn icon small color="error" @click="deleteHoliday(item)">
                                        <v-icon>mdi-delete</v-icon>
                                    </v-btn>
                                </td>
                            </tr>
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
                                <v-list-item-group>
                                    <v-list-item v-for="holiday in holidays" :key="holiday.id">
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
                                </v-list-item-group>
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

        <v-dialog v-model="addHolidayDialog" max-width="500px">
            <v-card>
                <v-card-title class="text-h6">Add New Holiday</v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <v-form @submit.prevent="addHoliday">
                        <v-text-field 
                            v-model="newHoliday.name" 
                            label="Holiday Name" 
                            outlined 
                            dense>
                        </v-text-field>
                        <v-text-field 
                            v-model="newHoliday.date" 
                            label="Date" 
                            type="date" 
                            outlined 
                            dense>
                        </v-text-field>
                        <v-select 
                            v-model="newHoliday.branches" 
                            item-title="name" 
                            :items="branches" 
                            label="Branches"
                            clearable 
                            outlined 
                            dense>
                        </v-select>
                    </v-form>
                </v-card-text>
                <v-card-actions justify="space-between">
                    <v-btn @click="addHolidayDialog = false" color="error" outlined>Cancel</v-btn>
                    <v-btn @click="addHoliday" color="success" outlined>Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container-fluid>
</template>



<style scoped>
.calendar-card {
    padding: 20px;
}
</style>
