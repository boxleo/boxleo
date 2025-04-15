<template>
    <v-container-fluid>
        <div class="text-center">

            <v-row justify="center" class="my-4">
                <v-chip-group column>
                    <v-chip @click="filterLaptops" class="ma-2" color="deep-purple lighten-2" outlined elevation="4" prepend-icon="mdi-laptop">
                        <strong class="white--text">Laptops:</strong> <span class="white--text">{{ assets.laptops }}</span>
                    </v-chip>

                    <v-chip @click="filterDesktops" prepend-icon="mdi-monitor" class="ma-2" color="blue darken-3" outlined elevation="4">
                        <strong class="white--text">Desktops:</strong> <span class="white--text">{{ assets.desktops }}</span>
                    </v-chip>

                    <v-chip @click="filterHeadsets" prepend-icon="mdi-headset" class="ma-2" color="green accent-4" outlined elevation="4">
                        <strong class="white--text">Headsets:</strong> <span class="white--text">{{ assets.headsets }}</span>
                    </v-chip>

                    <v-chip @click="filterPhones" class="ma-2" color="cyan darken-2" outlined elevation="4" prepend-icon="mdi-cellphone">
                        <strong class="white--text">Phones:</strong> <span class="white--text">{{ assets.phones }}</span>
                    </v-chip>

                    <v-chip @click="filterCharger" class="ma-2" color="orange darken-3" outlined elevation="4" prepend-icon="mdi-battery-charging">
                        <strong class="white--text">Chargers:</strong> <span class="white--text">{{ assets.chargers }}</span>
                    </v-chip>

                    <v-chip @click="filterMouses" class="ma-2" color="pink darken-1" outlined elevation="4" prepend-icon="mdi-mouse">
                        <strong class="white--text">Mouses:</strong> <span class="white--text">{{ assets.mouses }}</span>
                    </v-chip>

                    <v-chip @click="filterPrinters" class="ma-2" color="purple darken-4" outlined elevation="4" prepend-icon="mdi-printer">
                        <strong class="white--text">Printers:</strong> <span class="white--text">{{ assets.printers }}</span>
                    </v-chip>

                    <v-chip @click="filterScanners" class="ma-2" color="teal darken-3" outlined elevation="4" prepend-icon="mdi-scanner">
                        <strong class="white--text">Scanners:</strong> <span class="white--text">{{ assets.scanners }}</span>
                    </v-chip>
                    
                    <v-chip @click="filterSimcards" class="ma-2" color="lime darken-3" outlined elevation="4" prepend-icon="mdi-sim">
                        <strong class="white--text">Sim Cards:</strong> <span class="white--text">{{ assets.simcards }}</span>
                    </v-chip>

                    <v-chip @click="filterCamera" class="ma-2" color="indigo darken-3" outlined elevation="4" prepend-icon="mdi-camera">
                        <strong class="white--text">Cameras:</strong> <span class="white--text">{{ assets.camera }}</span>
                    </v-chip>

                    <v-chip @click="filterOfficeChair" class="ma-2" color="grey darken-3" outlined elevation="4" prepend-icon="mdi-chair-office">
                        <strong class="white--text">Office Chairs:</strong> <span class="white--text">{{ assets.officechair }}</span>
                    </v-chip>

                    <v-chip @click="filterOfficeDesk" class="ma-2" color="grey darken-3" outlined elevation="4" prepend-icon="mdi-desk">
                        <strong class="white--text">Office Desks:</strong> <span class="white--text">{{ assets.officedesk }}</span>
                    </v-chip>

                    <v-chip @click="filterTable" class="ma-2" color="grey darken-3" outlined elevation="4" prepend-icon="mdi-table">
                        <strong class="white--text">Tables:</strong> <span class="white--text">{{ assets.table }}</span>
                    </v-chip>
                    
                    <v-chip @click="filterFileCabinet" class="ma-2" color="grey darken-3" outlined elevation="4" prepend-icon="mdi-file-cabinet">
                        <strong class="white--text">File Cabinets:</strong> <span class="white--text">{{ assets.filecabinet }}</span>
                    </v-chip>

                    <v-chip @click="filterDocumentHolder" class="ma-2" color="grey darken-3" outlined elevation="4" prepend-icon="mdi-file-document">
                        <strong class="white--text">Document Holders:</strong> <span class="white--text">{{ assets.documentholder }}</span>
                    </v-chip>

                    <v-chip @click="filterGuestSeat" class="ma-2" color="grey darken-3" outlined elevation="4" prepend-icon="mdi-seat">
                        <strong class="white--text">Guest Seats:</strong> <span class="white--text">{{ assets.guestseat }}</span>
                    </v-chip>

                    <v-chip @click="filterSideTable" class="ma-2" color="grey darken-3" outlined elevation="4" prepend-icon="mdi-table-chair">
                        <strong class="white--text">Side Tables:</strong> <span class="white--text">{{ assets.sidetable }}</span>
                    </v-chip>

                    <v-chip @click="filterOther" class="ma-2" color="grey darken-1" outlined elevation="4" prepend-icon="mdi-help-circle">
                        <strong class="white--text">Other:</strong> <span class="white--text">{{ assets.other }}</span>
                    </v-chip>
                </v-chip-group>
            </v-row>
        </div>
        <v-row justify="end" class="text-right">
            <v-col>
                <v-row justify="start" class="text-right">
                    <v-col cols="auto">
                        <v-btn v-if="roles.includes('admin')" @click="resourceClear" variant="tonal" icon>
                            <v-tooltip activator="parent" location="top">Asset clearance</v-tooltip>
                            <v-icon color="warning" x-small>mdi-format-rotate-90</v-icon>
                        </v-btn>
                    </v-col>
                </v-row>
            </v-col>
            <v-col>

                <v-row justify="end" class="text-right">

                    <v-col cols="auto">
                        <v-btn v-if="roles.includes('admin')" @click="addResource" icon>
                            <v-tooltip activator="parent" location="top">Add Resource</v-tooltip>
                            <v-icon color="primary">mdi-plus</v-icon>
                        </v-btn>
                    </v-col>
                    <v-col cols="auto" v-if="resources.length > 0">
                        <v-btn v-if="roles.includes('admin')" @click="openFilterDialog" icon>
                            <v-tooltip activator="parent" location="top">Filter Resource</v-tooltip>
                            <v-icon color="primary" x-small>mdi-filter</v-icon>
                        </v-btn>
                    </v-col>


                    <!-- Import Asset -->

                    <v-col cols="auto" v-if="resources.length > 0">
                        <v-btn v-if="roles.includes('admin')" @click="importAsset" icon>
                            <v-tooltip activator="parent" location="top">Import Asset</v-tooltip>
                            <v-icon color="primary" x-small>mdi-file</v-icon>
                        </v-btn>
                    </v-col>

                    <v-col cols="auto" v-if="resources.length > 0">
                        <v-btn v-if="roles.includes('admin')" @click.prevent="downloadReport" icon>
                            <v-tooltip activator="parent" location="top">Generate Report</v-tooltip>
                            <v-icon color="success" x-small>mdi-download</v-icon>
                        </v-btn>
                    </v-col>
                    <v-col cols="auto">
                        <v-btn v-if="roles.includes('admin')" @click.prevent="refreshResources" icon>
                            <v-tooltip activator="parent" location="top">Refresh Assets</v-tooltip>
                            <v-icon color="danger" x-small>mdi-refresh</v-icon>
                        </v-btn>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
        <v-col>
            <v-text-field   v-model="search" label="Search"  prepend-inner-icon="mdi-magnify" clearable @clear="clearSearch"></v-text-field>

        </v-col>
        <v-row>
            <v-responsive>
                <v-progress-linear v-if="loading" color="primary" indeterminate></v-progress-linear>

                <v-data-table  v-model="selected" :headers="headers" :items="resources" item-key="id" :search="search"
                    class="elevation-10" responsive show-select>

                    <template v-slot:item.issued_to="{ item }">
                        <td>
                            <v-span>
                                {{ item.issued_to ? `${item.issued_to.firstname ?? '..'} ${item.issued_to.lastname ??
                                    '..'}` : '..' }}
                            </v-span>
                        </td>
                    </template>

                    <template v-slot:item.issued_by="{ item }">
                        <td>
                            <v-span>
                                {{ item.issued_by ? `${item.issued_by.firstname ?? '..'} ${item.issued_by.lastname ??
                                    '..'}` : '..' }}
                            </v-span>
                        </td>
                    </template>

                    <template v-slot:item.condition="{ item }">
                        <td>
                            <v-span>{{ item.condition ?? '..' }}</v-span>
                        </td>
                    </template>

                    <template v-slot:item.actions="{ item }">
                        <td class="d-flex justify-content-around align-items-center">
                            <!-- Edit User -->
                            <v-icon v-if="roles.includes('admin')" @click="editResource(item)" title="Edit User"
                                color="primary">
                                mdi-pencil
                            </v-icon>

                            <!-- Issue to a User -->
                            <v-icon v-if="roles.includes('admin')" @click="openAssignUserModal(item)"
                                title="Issue to a user" color="orange">
                                mdi-account-arrow-right
                            </v-icon>

                            <!-- Schedule Maintenance -->
                            <v-icon v-if="roles.includes('admin')" @click="scheduleMaintence(item)"
                                title="Schedule Maintenance" color="blue">
                                mdi-calendar-clock
                            </v-icon>

                            <!-- View History -->
                            <v-icon @click="viewHistory(item)" title="View History" color="black">
                                mdi-history
                            </v-icon>

                            <!-- Delete Resource -->
                            <v-icon v-if="roles.includes('admin')" @click="deleteResource(item)" title="Delete"
                                color="red">
                                mdi-delete
                            </v-icon>
                        </td>
                    </template>
                </v-data-table>
            </v-responsive>
        </v-row>
        <v-dialog v-model="dialog" max-width="600px" transition="scale-transition">
            <v-card elevation="12">
                <v-toolbar>
                    <v-toolbar-title>{{ editMode ? 'Edit Resource' : 'Add Resource' }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="dialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-toolbar>
                <v-card-text>
                    <v-form>
                        <v-container>
                            <v-row>
<!--                                 
                                <v-col cols="12" sm="4">
                                    <v-select v-model="formData.unit"
                                        :items="branches" 
                                        item-title="name" 
                                        item-value="id"
                                        label="Branch" variant="outlined">                                        label="Operating unit" variant="outlined">
                                    </v-select>
                                </v-col> -->
                                
                                <v-col cols="12" sm="6">
                                    <v-select v-model="formData.category"
                                        :items="['Hardware', 'Software', 'Stationery', 'Furniture & fittings', 'Electronics',]"
                                        label="Category" variant="outlined">
                                    </v-select>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-select v-model="formData.name"
                                        :items="['Laptop', 'Headset', 'Desktop', 'Phone', 'Charger', 'Mouse','Sim Card','Printer','Camera','Office chair','Office desk','Tables','File cabinet','Document holder','Guest seat','Side table','Other']"
                                        label="Name" variant="outlined">
                                    </v-select>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-text-field v-model="formData.issuance_date" label="Date of issue" type="date"
                                        variant="outlined">
                                    </v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-text-field v-model="formData.serial_no" label="Serial no" placeholder="Serial no"
                                        variant="outlined">
                                    </v-text-field>
                                </v-col>
                               
                                <v-col cols="12">
                                    <v-textarea v-model="formData.description" label="Description/Specification"
                                        placeholder="HP HP Pavilion Laptop 15-eg1xxx" variant="outlined">
                                    </v-textarea>
                                </v-col>


                                <v-col cols="12" sm="6">
                                    <v-text-field v-model="formData.purchase_cost" label="Purchase Cost"
                                        placeholder="Ksh 100,000" variant="outlined">
                                    </v-text-field>
                                </v-col>
                            
                                <v-col cols="12" sm="6">
                                    <v-text-field v-model="formData.purchase_date" label="Purchase Date" type="date"
                                        variant="outlined">
                                    </v-text-field>
                                </v-col>


                                <v-col cols="12" sm="6">
                                    <v-select :items="users" item-title="fullName" item-value="id" search-input
                                        v-model="formData.issued_to" label="Assign to Employee">
                                    </v-select>

                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-select v-model="formData.condition"
                                        :items="['New', 'In Use', 'Under Repair', 'Disposed', 'Fault']"
                                        label="Condition" variant="outlined">
                                    </v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-textarea v-model="formData.comment"
                                        placeholder="8GB RAM,11th Gen Intel® Core™ i7-1195G7 @ 2.90GHz × 8"
                                        label="Comment (Optional)" variant="outlined">
                                    </v-textarea>
                                </v-col>


                                <v-col cols="12">
                                <v-file-input v-model="formData.attachments" label="Attach Image in pdf (Optional)"
                                    accept=".pdf, .doc, .docx, .png" outlined clearable></v-file-input>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-form>

                </v-card-text>
                <v-card-actions class="justify-space-between">
                    <v-btn color="danger" @click="cancelEdit">close</v-btn>
                    <v-btn color="primary" @click="saveResource">Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- asssig Assign Employee   -->
        <v-dialog v-model="assignUserModal" max-width="500px">
            <v-card>
                <v-card-title>Assign Employee</v-card-title>
                <v-card-text>
                    <v-select :items="users" :item-title="'fullName'" :item-value="'id'" v-model="formData.issued_to"
                        label="Assign to Employee" clearable></v-select>

                </v-card-text>
                <v-card-actions class="justify-space-between">
                    <v-btn color="danger" @click="assignUserModal = false">Cancel</v-btn>
                    <v-btn color="primary" variant="tonal" @click="assignUser">Assign</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>


        <!-- Asset logs -->

        <v-dialog v-model="logsModal" max-width="700px" full-height top>
            <v-card class="elevation-10">
                <v-card-title class="headline">
                    <v-icon color="primary">mdi-history</v-icon>
                    Asset Logs
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

                                    <v-list-item-title class="mb-3">
                                        <v-icon color="secondary" class="mr-1">mdi-account-arrow-right</v-icon>
                                        <strong> To:</strong> {{
                                            log.details.issued_to }}
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



        <!-- clear  -->
        <v-dialog v-model="resourceClearanceModal" max-width="500px" persistent>
            <v-card>
                <v-card-title class="text-center">
                    <v-icon>mdi-laptop</v-icon> Asset clearance
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <v-col cols="12">
                        <v-text-field v-model="clearanceForm.serial_no" label="Enter/scan asset serial no"
                            placeholder="Serial no..." @keyup.enter="checkSerialNumber">
                        </v-text-field>
                    </v-col>
                    <!-- <v-col cols="12">
                        <v-select :items="users" :item-title="'fullName'" :item-value="'id'"
                            v-model="clearanceForm.user_id" label="Clear Employee" clearable multiple></v-select>
                    </v-col> -->
                    <v-col cols="12">
                        <v-textarea v-model="clearanceForm.comment" label="Comment" placeholder="cleared.."
                            variant="outlined">
                        </v-textarea>
                    </v-col>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions class="justify-space-between">
                    <v-btn @click="resourceClearanceModal = false">Close</v-btn>
                    <v-btn variant="tonal" :loading="loading" @click="clearAsset" color="primary">
                        Submit
                        <template v-slot:loader>
                            <v-progress-linear indeterminate></v-progress-linear>
                        </template>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container-fluid>
</template>
<script>
import axios from 'axios';

export default {
    props: {
        user: Object,
        roles: Array,
        permissions: Array

    },
    data() {
        return {
            
            search: '',
            logsModal: false,
            logs: [],
            loadingLogs: false,
            loading: false,
            selectedItemId: null,
            selected: [],
            resources: [],
            users: [],
            loading: false,
            assignUserModal: false,
            selected: this.User,
            dialog: false,
            drawer: false,
            assets: {
                laptops: 0,
                desktops: 0,
                headsets: 0,
                phones: 0,
                chargers: 0,
                mouses: 0,
                printers: 0,
                scanners: 0,
                simcards: 0,
                camera: 0,
                officechair: 0,
                officedesk: 0,
                table: 0,
                filecabinet: 0,
                documentholder: 0,
                guestseat: 0,
                sidetable: 0,
                other: 0,
            },

            formData: {
                name: '',
                serial_no: '',
                category: '',
                issuance_date: '',
                issued_to: null,
                condition: '',
                description: '',
                comment: '',
                issued_by: null,
                purchase_cost: null,
                purchase_date: null,
                attachments: null,
            },
            editMode: false,
            editedResourceId: null,
            resourceClearanceModal: false,
            clearanceForm: {
                serial_no: null,
                user_id: null,
                comment: null,
                is_cleared: false,

            },
            headers: [
                { title: 'Name', value: 'name' },
                { title: 'Description', value: 'description' },
                { title: 'Serial No', value: 'serial_no' },
                { title: 'Category', value: 'category' },
                { title: 'Issued To', value: 'issued_to' },
                { title: 'Issued By', value: 'issued_by' },
                { title: 'Condition', value: 'condition' },
                { title: 'Comment', value: 'comment' },
                { title: 'Purchase Cost', value: 'purchase_cost' },
                { title: 'Purchase Date', value: 'purchase_date' },
                { title: 'Actions', value: 'actions', sortable: false },
            ],
        };
    },
    watch: {
        loading(val) {
            if (!val) return
            setTimeout(() => (this.loading = false), 8000)
        },
    },

    mounted() {
        console.log("User:", this.user);
        console.log("Roles:", this.roles);
        console.log("Permissions:", this.permissions);
        this.fetchResources();
        this.fetchUsers();

    },


    computed: {

filteredResources() {

  if (!this.search) {

    return this.resources;

  }

  const searchLower = this.search.toLowerCase();

  return this.resources.filter(item => {

    return (

      item.name.toLowerCase().includes(searchLower) ||

    //   item.email.toLowerCase().includes(searchLower) ||

      item.serial_no.toLowerCase().includes(searchLower) ||
        // item.category.toLowerCase().includes(searchLower) ||

        item.issued_to?.firstname.toLowerCase().includes(searchLower)

        // item.issued_by?.firstname.toLowerCase().includes(searchLower) ||

        // item.comment.toLowerCase().includes(searchLower)

      // Add more fields to search as needed

    );

  });

},

},
    methods: {

        clearSearch() {

this.search = '';

},


        importAsset() {
            // Logic to handle asset import
            // You can open a file dialog to select a file and then process it
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.csv, .xlsx';
            input.onchange = async (event) => {
                const file = event.target.files[0];
                if (file) {
                    const formData = new FormData();
                    formData.append('file', file);

                    try {
                        const response = await axios.post('/api/v1/resources/import', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                            },
                        });
                        this.$toastr.success(response.data.message);
                        this.fetchResources();
                    } catch (error) {
                        console.error('Error importing asset:', error);
                        this.$toastr.error('Failed to import asset.');
                    }
                }
            };
            input.click();
        },

        assignUser() {
            console.log('Assigning user to resource...');
            console.log('Selected user:', this.formData.issued_to);
            console.log('Selected resource:', this.selectedItemId);
            axios.put(`/api/v1/resources/${this.selectedItemId}/reassign`, {

                issued_to: this.formData.issued_to,
            })
                .then(response => {
                    console.log('API response:', response);
                    this.$toastr.success(response.data.message);
                    this.assignUserModal = false;
                    this.fetchResources();
                })
                .catch(error => {
                    console.error('Error assigning user to resource:', error);
                    if (error.response && error.response.data && error.response.data.error) {
                        this.$toastr.error(error.response.data.error);
                    } else {
                        this.$toastr.error('An error occurred while assigning user to resource.');
                    }
                });
        },


        closelogsModal() {
            this.logsModal = false;

        },

        viewHistory(item) {
            this.logsModal = true;
            this.fetchLogs(item.id);
        },
        async fetchLogs(resourceId) {
            this.loadingLogs = true;
            try {
                const response = await axios.get(`/api/v1/resource-logs/${resourceId}`);
                this.logs = response.data.logs;
            } catch (error) {
                console.error('Error fetching logs:', error);
                this.$toastr.error('Failed to fetch logs.');
            } finally {
                this.loadingLogs = false;
            }
        },
        async clearAsset() {
            try {
                const response = await axios.post(`/api/v1/resources/${this.clearanceForm.serial_no}/clear`, {
                    user_id: this.clearanceForm.user_id,
                    comment: this.clearanceForm.comment,

                });

                this.$toastr.success(response.data.message);
                this.resourceClearanceModal = false;
                this.fetchResources();
            } catch (error) {
                console.error('Error clearing asset:', error);
                if (error.response && error.response.data && error.response.data.error) {
                    this.$toastr.error(error.response.data.error);
                } else {
                    this.$toastr.error('An error occurred while clearing the asset.');
                }
            }
        },

        fetchResources(name) {
            this.loading = true;

            let url = '/api/v1/resources';
            if (name) {
                url += `?name=${name}`;
            }
            axios.get(url)
                .then(response => {
                    this.resources = response.data.resources;
                    this.updateAssetCounts();
                })
                .catch(error => {
                    console.error('Error fetching resources:', error);
                }).finally(() => {
                    this.loading = false;
                });
        },

        updateAssetCounts() {
            this.assets = {
                laptops: this.resources.filter(resource => resource.name === 'Laptop').length,
                desktops: this.resources.filter(resource => resource.name === 'Desktop').length,
                headsets: this.resources.filter(resource => resource.name === 'Headset').length,
                phones: this.resources.filter(resource => resource.name === 'Phone').length,
                chargers: this.resources.filter(resource => resource.name === 'Charger').length,
                mouses: this.resources.filter(resource => resource.name === 'Mouse').length,
                printers: this.resources.filter(resource => resource.name === 'Printer').length,
                scanners: this.resources.filter(resource => resource.name === 'Scanner').length,
                simcards: this.resources.filter(resource => resource.name === 'Sim_Card').length,
                camera: this.resources.filter(resource => resource.name === 'Camera').length,
                officechair: this.resources.filter(resource => resource.name === 'Office_Chair').length,
                officedesk: this.resources.filter(resource => resource.name === 'Office_Desk').length,
                table: this.resources.filter(resource => resource.name === 'Table').length,
                filecabinet: this.resources.filter(resource => resource.name === 'File_Cabinet').length,
                documentholder: this.resources.filter(resource => resource.name === 'Document_Holder').length,
                guestseat: this.resources.filter(resource => resource.name === 'Guest_Seat').length,
                sidetable: this.resources.filter(resource => resource.name === 'Side_Table').length,
                other: this.resources.filter(resource => resource.name === 'Other').length,
            };
        },

        filterResources(name) {
            this.fetchResources(name);
        },
        filterHeadsets() {
            this.filterResources('Headset');
        },

        filterDesktops() {
            this.filterResources('Desktop');
        },

        filterLaptops() {
            this.filterResources('Laptop');
        },

        filterPhones() {
            this.filterResources('Phone');
        },
        filterCharger() {
            this.filterResources('Charger');
        },
        filterMouses() {
            this.filterResources('Mouse');
        },
        filterPrinters() {
            this.filterResources('Printer');
        },
        filterScanners() {
            this.filterResources('Scanner');
        },
        filterSimcards() {
            this.filterResources('Sim Card');
        },
        filterCamera() {
            this.filterResources('Camera');
        },
        filterOfficeChair() {
            this.filterResources('Office Chair');
        },
        filterOfficeDesk() {
            this.filterResources('Office Desk');
        },
        filterTable() {
            this.filterResources('Table');
        },
        filterFileCabinet() {
            this.filterResources('File Cabinet');
        },
        filterDocumentHolder() {
            this.filterResources('Document Holder');
        },
        filterGuestSeat() {
            this.filterResources('Guest Seat');
        },
        filterSideTable() {
            this.filterResources('Side Table');
        },
        filterOther() {
            this.filterResources('Other');
        },
        fetchUsers() {
            console.log('Fetching users from the API...');
            axios.get('/api/v1/users')
                .then(response => {
                    console.log('API response received:', response);

                    if (response.data && response.data.users) {

                        console.log('Users data found:', response.data.users);

                        this.users = response.data.users.map(user => ({

                            id: user.id,

                            fullName: `${user.firstname} ${user.lastname}`,
                        }));
                        // console.log('Processed users:', this.users); 
                        console.log('Processed users:', JSON.parse(JSON.stringify(this.users)));

                    } else {

                        console.warn('No users found in the response.');

                    }

                })
                .catch(error => {

                    console.error('Error fetching users:', error); // Log the error
                });
        },
        getUserFullName(userId) {
            const user = this.users.find(user => user.id === userId);

            if (user) {
                return user.firstname;
            } else {
                return '';
            }
        },

        addResource() {
            this.editMode = false;
            this.editedResourceId = null;
            this.formData = {
                name: '',
                description: '',
                serial_no: '',
                category: '',
                issued_to: null,
                condition: '',
                issuance_date: '',
                comment: '',
                issued_by: null,
                purchase_date: null,
                purchase_cost: null,
            };
            this.dialog = true;
        },
        editResource(resource) {
            this.editMode = true;
            this.editedResourceId = resource.id;
            this.formData = {
                name: resource.name,
                serial_no: resource.serial_no,
                category: resource.category,
                issued_to: resource.issued_to,
                issued_by: resource.issued_by && resource.issued_by.id ? resource.issued_by.id : null,
                condition: resource.condition,
                description: resource.description,
                issuance_date: resource.issuance_date,
                comment: resource.comment,
                purchase_date: resource.purchase_date,
                purchase_cost: resource.purchase_cost,
            };
            this.dialog = true;
        },
        saveResource() {
            if (this.editMode) {
                axios.put(`/api/v1/resources/${this.editedResourceId}`, this.formData)
                    .then(response => {
                        this.$toastr.success(response.data.message);
                        this.fetchResources();
                        this.dialog = false;
                    })
                    .catch(error => {
                        console.error('Error updating resource:', error);
                    });
            } else {
                axios.post('/api/v1/resources', this.formData)
                    .then(response => {
                        this.$toastr.success('Success');
                        this.fetchResources();
                        this.dialog = false;
                    })
                    .catch(error => {
                        this.$toastr.error('failed');
                        console.error('Error adding new resource:', error);
                    });
            }
        },
        checkSerialNumber() {
            if (this.clearanceForm.serial_no) {
                const resource = this.resources.find(resource => resource.serial_no === this.clearanceForm.serial_no);
                if (resource) {
                    this.clearanceForm.user_id = resource.user_id;
                } else {
                    this.clearanceForm.user_id = null;
                    this.$toastr.error("The asset is not assigned to a user");
                }
            } else {
                this.clearanceForm.user_id = null;
                this.$toastr.warning("Please enter a serial number");
            }
        },

        resourceClear() {

            this.resourceClearanceModal = true;
        },


        deleteResource(resource) {
            axios.delete(`/api/v1/resources/${resource.id}`)
                .then(response => {
                    this.$toastr.success(response.data.message);
                    this.fetchResources();
                })
                .catch(error => {
                    if (error.response && error.response.data && error.response.data.error) {
                        this.$toastr.error(error.response.data.error);
                    } else {
                        this.$toastr.error('An error occurred while deleting the resource.');
                    }
                    console.error('Error deleting resource:', error);
                    this.fetchResources();
                });
        },

        openAssignUserModal(item) {
            this.assignUserModal = true;
            this.selectedItemId = item.id

        },
        cancelEdit() {
            this.dialog = false;
        },
        refreshResources() {
            this.fetchResources();
        },

        async downloadReport() {
            try {
                const response = await axios.get("/api/v1/asset-report", {
                    responseType: "blob",
                });

                // Create a blob from the response data
                const blob = new Blob([response.data], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });

                // Create a temporary download link
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "asset_report.xlsx";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                console.error("Error downloading Excel:", error);
            }
        },
        openFilterDialog() {
            this.drawer = true;
        },
        viewResource(resource) {
            // Method to view resource details
            // Implement based on your requirements
        },
    },

};
</script>
