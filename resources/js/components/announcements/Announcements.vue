<template>
    <v-container fluid>
        <v-row>
            <v-responsive>
                <v-data-table :headers="headers" :items="announcements" item-key="id" class="elevation-1" show-select>
                    <template v-slot:top>
                        <v-col class="d-flex justify-end">
                                <v-btn class="mr-4" color="primary" @click="createAnnouncement">Create Announcement</v-btn>
                        </v-col>
                        <!-- <v-toolbar flat color="primary">
                            <v-btn color="success" class="mr-4" @click="createAnnouncement">Create Announcement
                                </v-btn>
                            <v-select v-model="selectedFilter" :items="filters" label="-- select --"
                                class="mr-4"></v-select>
                            <v-spacer></v-spacer>
                            <v-btn color="secondary" class="mr-4">Discard current criteria</v-btn>
                            <v-text-field v-model="search" append-icon="mdi-magnify" label="Introduce your search"
                                single-line hide-details></v-text-field>
                        </v-toolbar> -->
                    </template>

                    <template v-slot:item.actions="{ item }">
                        <v-icon small @click="editTicket(item)">mdi-star</v-icon>
                        <v-icon small @click="viewAnnouncement(item.id)" title="View Announcement">mdi-eye</v-icon>
                        <v-icon small @click="editAnnouncement(item.id)" title="Edit Announcement">mdi-pencil</v-icon>
                        <v-icon small @click="deleteAnnouncement(item.id)"
                            title="Delete Announcement">mdi-delete</v-icon>
                    </template>
                </v-data-table>
            </v-responsive>
        </v-row>

        <v-dialog v-model="showDialog" max-width="600px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ formTitle }}</span>
                </v-card-title>
                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="12">
                                <v-text-field v-model="editedAnnouncement.subject" label="Subject"></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea v-model="editedAnnouncement.description" label="Description"></v-textarea>
                            </v-col>
                            <!-- <v-col cols="12" sm="6">
                                <v-select v-model="editedAnnouncement.author_id" :items="authors"
                                    label="Author"></v-select>
                            </v-col> -->
                            <v-col cols="12" sm="6">
                                <v-text-field v-model="editedAnnouncement.publish_date" label="Publish Date"
                                    type="date"></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field v-model="editedAnnouncement.expiration_date" label="Expiration Date"
                                    type="date"></v-text-field>
                            </v-col>
                            <!-- <v-col cols="12" sm="6">
                                <v-select v-model="editedAnnouncement.priority" :items="priorities"
                                    label="Priority"></v-select>
                            </v-col> -->
                            <v-col cols="12" sm="6">
                                <v-select v-model="editedAnnouncement.status" :items="statuses"
                                    label="Status"></v-select>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-switch v-model="editedAnnouncement.is_active" label="Active"></v-switch>
                            </v-col>
                            <v-col cols="12">
                                <v-file-input v-model="editedAnnouncement.attachment" label="Attachment"></v-file-input>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" @click="closeDialog">Cancel</v-btn>
                    <v-btn color="blue darken-1" @click="saveAnnouncement">Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
export default {
    data() {
        return {
            showDialog: false,
            search: '',
            selectedFilter: null,
            bulkAction: null,
            filters: ['All', 'Active', 'Expired'],
            announcements: [],
            headers: [
                { title: 'Subject', value: 'subject' },
                { title: 'Description', value: 'description' },
                { title: 'Author', value: 'author' },
                { title: 'Publish Date', value: 'publish_date' },
                { title: 'Expiration Date', value: 'expiration_date' },
                { title: 'Active', value: 'is_active' },
                // { title: 'Priority', value: 'priority' },
                { title: 'Status', value: 'status' },
                { title: 'Actions', value: 'actions', sortable: false },
            ],
            editedIndex: -1,
            editedAnnouncement: {
                subject: '',
                description: '',
                author: '',
                publish_date: '',
                expiration_date: '',
                is_active: false,
                attachment: '',
                // priority: '',
                status: ''
            },
            defaultAnnouncement: {
                subject: '',
                description: '',
                author: '',
                publish_date: '',
                expiration_date: '',
                is_active: false,
                attachment: '',
                // priority: '',
                status: ''
            },
            authors: [],
            // priorities: ['High', 'Medium', 'Low'],
            statuses: ['Published', 'Draft', 'Expired']
        }
    },
    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'Create Announcement' : 'Edit Announcement';
        }
    },
    methods: {
        createAnnouncement() {
            this.editedIndex = -1;
            this.editedAnnouncement = Object.assign({}, this.defaultAnnouncement);
            this.showDialog = true;
        },
        editAnnouncement(item) {
            this.editedIndex = this.announcements.indexOf(item);
            this.editedAnnouncement = Object.assign({}, item);
            this.showDialog = true;
        },
        deleteAnnouncement(item) {
            const index = this.announcements.indexOf(item);
            confirm('Are you sure you want to delete this announcement?') && this.announcements.splice(index, 1);
        },
        closeDialog() {
            this.showDialog = false;
            this.editedAnnouncement = Object.assign({}, this.defaultAnnouncement);
            this.editedIndex = -1;
        },
        saveAnnouncement() {
            axios.post('/api/v1/announcements', this.editedAnnouncement)
                .then(response => {
                    if (this.editedIndex > -1) {
                        Object.assign(this.announcements[this.editedIndex], response.data);
                    } else {
                        axios.put(`/api/v1/announcements/${response.data.id}`, response.data)
                            .then(() => {
                                Object.assign(this.announcements[this.editedIndex], response.data);
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    }
                    this.closeDialog();
                })
                .catch(error => {
                    console.log(error);
                });
        }
    }
}
</script>

<style scoped>
.headline {
    font-weight: bold;
}
</style>
