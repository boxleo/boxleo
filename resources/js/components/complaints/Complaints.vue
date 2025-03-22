
<template>
    <v-container fluid>
        <v-row>
            <v-responsive>
                <v-data-table v-model="selected" :headers="headers" :items="complaints" item-key="id"
                    class="elevation-1" show-select>
                    <template v-slot:top>
                        <v-toolbar flat color="primary">
                            <v-select v-model="selectedFilter" :items="filters" label="-- select --"
                                class="mr-4"></v-select>
                            <v-spacer></v-spacer>
                            <v-text-field v-model="search" append-icon="mdi-magnify" label="Introduce your search"
                                single-line hide-details></v-text-field>
                        </v-toolbar>
                        <v-row v-if="selected.length > 0" class="bg-primary">
                            <v-col>
                                <v-icon class="mx-3" color="white" @click="bulkUpdateStatus" title="Update status"
                                    size="15">mdi-account-clock</v-icon>
                                <v-icon class="mx-3" color="white" @click="bulkAddComments" title="Add comments"
                                    size="15">mdi-comment</v-icon>
                                <v-icon class="mx-3" color="white" @click="bulkDeleteTickets" title="Delete tickets"
                                    size="15">mdi-delete</v-icon>
                            </v-col>
                        </v-row>
                    </template>
                    <template v-slot:item.actions="{ item }">
                        <v-icon small @click="viewComplaint(item.id)" title="View Complaint">mdi-account-search</v-icon>
                        <v-icon small @click="editComplaint(item)" title="Edit Complaint">mdi-pencil</v-icon>
                        <v-icon small @click="deleteComplaint(item)" title="Delete Complaint">mdi-delete</v-icon>
                    </template>
                </v-data-table>
            </v-responsive>
        </v-row>
    </v-container>
</template>

<script>
export default {
    data() {
        return {
            selected: [],
            base_url: '/',
            search: '',
            selectedFilter: null,
            bulkAction: null,
            filters: ['All', 'Closed', 'Request', 'Incident'],
            complaints: [],
            headers: [
                { title: 'Created At', value: 'created_at' },
                { title: 'Subject', value: 'subject' },
                { title: 'Description', value: 'description' },
                { title: 'Category', value: 'category' },
                { title: 'Status', value: 'status' },
                { title: 'Priority', value: 'priority' },
                { title: 'Date Opened', value: 'date_opened' },
                { title: 'Attachment', value: 'attachment' },
                { title: 'Addressee', value: 'addressed_to' },
                { title: 'Date Closed', value: 'closed_date' },
                {title:'Department',value:'department'},
                { title: 'Actions', value: 'actions', sortable: false },
            ],
            editedIndex: -1,
            admins: [],
            statuses: ['Open', 'In Progress', 'Resolved', 'Closed'],
        }
    },

    created() {
        this.fetchUsers();
        this.fetchComplaints();
        this.fetchDepartments();
        
    },
    methods: {



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
        fetchUsers() {
            const apiUrl = `${this.base_url}api/v1/users`;
            axios.get(apiUrl)
                .then((response) => {
                    this.users = response.data.users;
                    this.admins = this.users.filter(user => user.is_hod)
                        .map(user => ({ ...user, fullname: `${user.firstname} ${user.lastname}` }));
                })
                .catch((error) => {
                    console.error('Error fetching users:', error);
                });
        },
        fetchComplaints() {
            const apiUrl = `${this.base_url}api/v1/complaints`;
            axios.get(apiUrl)
                .then((response) => {
                    this.complaints = response.data.complaints;
                })
                .catch((error) => {
                    console.error('Error fetching complaints:', error);
                });
        },
        deleteComplaint(item) {
            const index = this.complaints.indexOf(item);
            if (confirm('Are you sure you want to delete this complaint?')) {
                this.complaints.splice(index, 1);
                // Call API to delete the complaint
                const apiUrl = `${this.base_url}api/v1/complaints/${item.id}`;
                axios.delete(apiUrl)
                    .then(() => {
                        console.log('Complaint deleted');
                    })
                    .catch((error) => {
                        console.error('Error deleting complaint:', error);
                    });
            }
        },
        saveComplaint() {
            const complaintData = {
                ...this.editedComplaint,
                is_anonymous: this.editedComplaint.is_anonymous ? true : false
            };
            if (this.editedIndex > -1) {
                Object.assign(this.complaints[this.editedIndex], complaintData);
                // Call API to update the complaint
                const apiUrl = `${this.base_url}api/v1/complaints/${complaintData.id}`;
                axios.put(apiUrl, complaintData)
                    .then(() => {
                        console.log('Complaint updated');
                    })
                    .catch((error) => {
                        console.error('Error updating complaint:', error);
                    });
            } else {
                complaintData.id = this.complaints.length + 1;
                this.complaints.push(complaintData);
                // Call API to create the complaint
                const apiUrl = `${this.base_url}api/v1/complaints`;
                axios.post(apiUrl, complaintData)
                    .then(() => {
                        console.log('Complaint created');
                    })
                    .catch((error) => {
                        console.error('Error creating complaint:', error);
                    });
            }
            this.closeDialog();
        },
        bulkUpdateStatus() {
            this.selected.forEach(complaint => {
                complaint.status = 'Updated Status'; // Set the desired status
                const apiUrl = `${this.base_url}api/v1/complaints/${complaint.id}`;
                axios.put(apiUrl, complaint)
                    .then(() => {
                        console.log(`Status updated for complaint ${complaint.id}`);
                    })
                    .catch((error) => {
                        console.error(`Error updating status for complaint ${complaint.id}:`, error.message);
                    });
            });
            this.fetchComplaints();
        },
        bulkAddComments() {
            const comment = prompt("Enter your comment:");
            if (comment) {
                this.selected.forEach(complaint => {
                    const apiUrl = `${this.base_url}api/v1/complaints/${complaint.id}/comments`;
                    axios.post(apiUrl, { comment })
                        .then(() => {
                            console.log(`Comment added to complaint ${complaint.id}`);
                        })
                        .catch((error) => {
                            console.error(`Error adding comment to complaint ${complaint.id}:`, error.message);
                        });
                });
                this.fetchComplaints();
            } else {
                console.warn('No comment entered');
            }
        },
        bulkDeleteTickets() {
            if (confirm('Are you sure you want to delete selected complaints?')) {
                this.selected.forEach(complaint => {
                    const apiUrl = `${this.base_url}api/v1/complaints/${complaint.id}`;
                    axios.delete(apiUrl)
                        .then(() => {
                            const index = this.complaints.indexOf(complaint);
                            this.complaints.splice(index, 1);
                            console.log(`Complaint ${complaint.id} deleted successfully`);
                        })
                        .catch((error) => {
                            console.error(`Error deleting complaint ${complaint.id}:`, error.message);
                        });
                });
                this.selected = [];
            }
        },
        viewComplaint(id) {
            // Implement view complaint logic
        },
        editComplaint(complaint) {
            // Implement edit complaint logic
        }
    }
}
</script>
