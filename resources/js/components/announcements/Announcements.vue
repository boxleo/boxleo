<template>
    <v-container fluid>
        <v-row>
            <v-responsive>
                <v-data-table :headers="headers" :items="announcements" item-key="id" class="elevation-1" show-select>
                    <template v-slot:top>
                        <v-col class="d-flex justify-end">
                                <v-btn class="mr-4" color="primary" @click="createAnnouncement">Create Announcement</v-btn>
                        </v-col>

                    </template>

                    <template v-slot:item.attachments="{ item }">
                        <div v-if="item.attachments && item.attachments.length > 0">
                            <div v-for="attachment in item.attachments" :key="attachment.id" class="mb-1">
                                <a
                                    :href="'/storage/' + attachment.file_path"
                                    target="_blank"
                                    :title="attachment.filename || 'Download attachment'"
                                    class="text-decoration-none"
                                >
                                    <v-icon>mdi-cloud-download</v-icon>
                                </a>
                            </div>
                        </div>
                        <span v-else>Null</span>
                    </template>

                    <template v-slot:item.actions="{ item }">
                        <v-icon small @click="viewAnnouncement(item)" title="View Announcement">mdi-eye</v-icon>
                        <v-icon small @click="editAnnouncement(item)" title="Edit Announcement">mdi-pencil</v-icon>
                        <v-icon small @click="deleteAnnouncement(item)"
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
                            <v-col cols="12" sm="6">
                                <v-text-field v-model="editedAnnouncement.expiration_date" label="Expiration Date"
                                    type="date"></v-text-field>
                            </v-col>

                            <v-col cols="12">
                                <v-file-input
                                    label="Attachment"
                                    multiple

                                    v-model="attachments"
                                    accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain"
                                ></v-file-input>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" @click="closeDialog">Cancel</v-btn>
                    <v-btn color="blue darken-1" @click="saveAnnouncement">Save as draft</v-btn>
                    <v-btn color="blue darken-1" @click="publishAnnouncement">Publish</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- View Announcement Dialog -->
        <v-dialog v-model="showViewDialog" max-width="600px">
        <v-card>
            <v-card-title class="text-center font-weight-bold text-h5 mb-4">ANNOUNCEMENT</v-card-title>
            <v-card-text v-if="viewedAnnouncement">
            <div class="text-center">
                <p class="font-weight-bold text-h5 mb-4">{{ viewedAnnouncement.subject }}</p>
                <p class="mb-4">{{ viewedAnnouncement.description }}</p>
                <p>Status: {{ viewedAnnouncement.is_active ? 'Active' : 'Inactive' }}</p>
            </div>
            </v-card-text>
            <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="blue darken-1" text @click="showViewDialog = false">Close</v-btn>
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
                { title: 'Author', value: 'author_name' },
                { title: 'Publish Date', value: 'publish_date' },
                { title: 'Expiration Date', value: 'expiration_date' },
                { title: 'Active Status', value: 'is_active' },
                { title: 'Attachments', value: 'attachments' },
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
            },
            defaultAnnouncement: {
                subject: '',
                description: '',
                author: '',
                publish_date: '',
                expiration_date: '',
                is_active: false,
            },
            authors: [],
            statuses: ['Published', 'Draft', 'Expired'],
            attachments: [],
            showViewDialog: false,
            viewedAnnouncement: null
        }
    },
    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'Create Announcement' : 'Edit Announcement';
        }
    },
    mounted() {
        this.fetchAnnouncements();
        // this.fetchAuthors();
    },
    methods: {
        fetchAnnouncements() {
            axios.get('/api/v1/announcements')
                .then(response => {
                    this.announcements = response.data;
                })
                .catch(error => console.error(error));
        },
        viewAnnouncement(item) {
            //

            this.viewedAnnouncement = Object.assign({}, item);

            this.showViewDialog = true;

        },


    handleFileUpload(files) {
    // Handle both single file or null input
    if (!files) {
        this.attachments = [];
        return;
    }

    // Convert FileList to Array if needed
    const fileArray = Array.isArray(files) ? files : [files];

    // Validate file types and sizes
    const MAX_SIZE = 5 * 1024 * 1024; // 5MB
    //const VALID_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    const VALID_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain'
    ];

    // Process each file
    const validFiles = fileArray.filter(file => {
        // Size validation
        if (file.size > MAX_SIZE) {
            //
            alert(`"${file.name}" exceeds the 5MB size limit`);

            return false;
        }

        // Type validation
        if (!VALID_TYPES.includes(file.type)) {

            const ext = name.split('.').pop();
            console.log(`File extension: ${ext}`);

            return false;
        }

        // Add preview URL for images
        if (file.type.startsWith('image/')) {
            file.preview = URL.createObjectURL(file);
        }

        return true;
    });

    // Update attachments with valid files
    this.attachments = validFiles;

    // Log the processed files
    console.log('Selected files:', this.attachments);

    if (this.attachments.length > 0) {
        alert(`${this.attachments.length} file(s) ready for upload`);
    }
},

        removeAttachment(index) {
            const newAttachments = [...this.attachments];
            newAttachments.splice(index, 1);
            this.attachments = newAttachments;
        },
        openAttachment(path) {
        const fullPath = `/storage/${path}`; // adjust if you're using a different path
        window.open(fullPath, '_blank');
        },

        watch: {
            attachments(newFiles) {
                this.handleFileUpload(newFiles);
            }
        },

    createAnnouncement() {
        this.editedIndex = -1;
        this.editedAnnouncement = Object.assign({}, this.defaultAnnouncement);
        this.attachments = [];
        this.showDialog = true;
    },
    editAnnouncement(item) {
        this.editedIndex = this.announcements.indexOf(item);
        this.editedAnnouncement = Object.assign({}, item);
        this.attachments = item.attachments || [];
        this.showDialog = true;
    },
    deleteAnnouncement(item) {
        const index = this.announcements.indexOf(item);
        if (index === -1) {
            console.error('Error: Announcement ID is missing');
            return;
        }
        if (!confirm('Are you sure you want to delete this announcement?')) return;

        axios.delete(`/api/v1/announcements/${item.id}`)
            .then(() => {
                this.announcements.splice(index, 1);
                console.log('Announcement deleted successfully');
            })
            .catch(error => {
                console.error('Error deleting announcement:', error);
            });
    },
    closeDialog() {
        this.showDialog = false;
        this.editedAnnouncement = Object.assign({}, this.defaultAnnouncement);
        this.editedIndex = -1;
    },

    saveAnnouncement() {
        const formData = new FormData();

        // Add all announcement fields
        for (const key in this.editedAnnouncement) {
            if (this.editedAnnouncement[key] !== null && this.editedAnnouncement[key] !== undefined) {
                formData.append(key, this.editedAnnouncement[key]);
            }
        }

        // Add action
        formData.append('action', 'save_draft');

        // Add attachments
        if (this.attachments && this.attachments.length) {
            for (let i = 0; i < this.attachments.length; i++) {
                formData.append('attachments[]', this.attachments[i]);
            }
        }

        // Send to Laravel endpoint
        const url = this.editedIndex > -1
            ? `/api/v1/announcements/${this.editedAnnouncement.id}?_method=PUT`
            : '/api/v1/announcements';

        axios.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        .then(response => {
            // Handle success
            if (this.editedIndex > -1) {
                Object.assign(this.announcements[this.editedIndex], response.data);
            } else {
                this.announcements.push(response.data);
            }
            this.fetchAnnouncements();
            this.closeDialog();
        })
        .catch(error => {
            console.error('Error saving announcement:', error.response?.data || error.message);
        });
    },

    publishAnnouncement() {
        // Create FormData object for file uploads
        const formData = new FormData();

        // Add all announcement fields
        for (const key in this.editedAnnouncement) {
            if (this.editedAnnouncement[key] !== null && this.editedAnnouncement[key] !== undefined) {
                formData.append(key, this.editedAnnouncement[key]);
            }
        }

        // Add action
        formData.append('action', 'publish');

        // Add attachments
        if (this.attachments && this.attachments.length) {
            for (let i = 0; i < this.attachments.length; i++) {
                formData.append('attachments[]', this.attachments[i]);
            }
        }

        if (this.editedIndex > -1) {
            axios.post(`/api/v1/announcements/${this.editedAnnouncement.id}?_method=PUT`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(response => {
                Object.assign(this.announcements[this.editedIndex], response.data);
                this.fetchAnnouncements();
                this.closeDialog();

            })
            .catch(error => console.error('Error publishing announcement:', error));
        } else {
            axios.post('/api/v1/announcements', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(response => {
                this.announcements.push(response.data);
                this.fetchAnnouncements();
                this.closeDialog();
            })
            .catch(error => console.error('Error publishing announcement:', error));
        }
    }
    }
}

</script>

<style scoped>
.headline {
    font-weight: bold;
}
</style>
