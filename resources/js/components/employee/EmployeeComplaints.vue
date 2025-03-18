<template>
    <v-container fluid>
        <v-row>
            <v-col>
                <v-responsive>

                    <v-data-table :headers="headers" :items="voices" item-key="id" class="elevation-1" show-select>
                        <template v-slot:top>
                            <!-- <v-toolbar flat color="primary"> -->
                            <v-col class="d-flex justify-end">
                                <v-btn class="mr-4" color="primary" @click="openDialog">Make Suggestion</v-btn>
                            </v-col>

                            <!-- </v-toolbar> -->
                        </template>


                        <template v-slot:item.attachments="{ item }">
                            <div v-if="item.attachments">
                                <template v-for="(attachment, index) in parseAttachments(item.attachments)"
                                    :key="index">
                                    <a :href="getAttachmentUrl(attachment)" target="_blank">
                                        {{ attachment.original_name }}
                                    </a>
                                    <span v-if="index < parseAttachments(item.attachments).length - 1">, </span>
                                </template>
                            </div>
                            <span v-else>-</span>
                        </template>

                        <template v-slot:item.links="{ item }">
                            <v-chip v-for="(link, index) in parseLinks(item.links)" :key="index" class="ma-2">
                                <a :href="link" target="_blank">{{ getDisplayLink(link) }}</a>
                            </v-chip>
                            <span v-if="!item.links || parseLinks(item.links).length === 0">-</span>
                        </template>

                        <template v-slot:item.status="{ item }">
                            <v-chip :color="getStatusColor(item.status)" dark
                                @click="openStatusDialog(item, 'status')">{{ item.status }}</v-chip>
                        </template>

                        <template v-slot:item.comments="{ item }">
                            <v-chip :color="getStatusColor(item.status)" dark
                                @click="openStatusDialog(item, 'comments')">{{ item.comments }}</v-chip>
                        </template>

                        <template v-slot:item.resolution="{ item }">
                            <v-chip :color="getStatusColor(item.status)" dark
                                @click="openStatusDialog(item, 'resolution')">{{ item.resolution }}</v-chip>
                        </template>

                        <template v-slot:item.priority="{ item }">
                            <v-chip :color="getPriorityColor(item.priority)" dark
                                @click="openStatusDialog(item, 'priority')">{{ item.priority }}</v-chip>
                        </template>
                        <template v-slot:item.addressed_to="{ item }">
                            <span v-if="item.addressed_to && item.addressed_to.length">
                                {{ item.addressed_to.join(', ') }}
                            </span>
                            <span v-else>-</span>
                        </template>
                        <template v-slot:item.followers="{ item }">
                            <span v-if="item.followers && item.followers.length">
                                {{ item.followers.join(', ') }}
                            </span>
                            <span v-else>-</span>
                        </template>


                        <template v-slot:item.actions="{ item }">
                            <v-icon small color="primary" @click="viewVoice(item.id)"
                                title="View Voice">mdi-account-search</v-icon>
                            <v-icon small color="success" @click="editVoice(item)"
                                title="Edit Voice">mdi-pencil</v-icon>
                            <v-icon small color="red" @click="openconfirmDeleteDialog(item)"
                                title="Delete Voice">mdi-delete</v-icon>
                        </template>
                    </v-data-table>
                </v-responsive>
            </v-col>
        </v-row>


        <!-- update status dialog  -->
        <v-dialog v-model="statusDialog" max-width="500px">
            <v-card>
                <v-card-title class="headline">Update {{ dialogField.charAt(0).toUpperCase() + dialogField.slice(1)
                    }}</v-card-title>
                <v-card-text>
                    <v-select v-if="dialogField === 'status'" v-model="editedVoice.status" :items="statuses"
                        label="Status" :rules="[v => !!v || 'Status is required']"></v-select>
                    <v-select v-if="dialogField === 'priority'" v-model="editedVoice.priority"
                        :items="['High', 'Medium', 'Low']" label="Priority"
                        :rules="[v => !!v || 'Priority is required']"></v-select>
                    <v-textarea v-if="dialogField === 'comments'" v-model="editedVoice.comments" label="Comments"
                        :rules="[v => !!v || 'Comments is required']"></v-textarea>
                    <v-textarea v-if="dialogField === 'resolution'" v-model="editedVoice.resolution" label="Resolution"
                        :rules="[v => !!v || 'Resolution is required']"></v-textarea>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" text @click="statusDialog = false">Cancel</v-btn>
                    <v-btn color="blue darken-1" text @click="updateField">Update</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- dialog to view voice shows the history of the voice -->
        <v-dialog v-model="viewVoiceDialog" max-width="800px">
            <v-card>
                <v-card-title class="d-flex justify-space-between align-center">
                    <span class="headline font-weight-bold">Complaint Logs</span>
                    <v-btn icon @click="viewVoiceDialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>

                <v-divider></v-divider>

                <v-card-text>
                    <v-container v-if="selectedVoice.length">
                        <v-row v-for="(log, index) in selectedVoice" :key="log.id">
                            <v-col cols="12">
                                <v-card outlined class="pa-4 elevation-2">
                                    <v-card-title class="font-weight-medium">
                                        Log #{{ index + 1 }}
                                    </v-card-title>

                                    <v-card-subtitle class="text-caption text-grey-darken-1">
                                        <v-chip small color="primary" class="mr-2">
                                            {{ log.action }}
                                        </v-chip>
                                        <strong>Logged By:</strong> {{ log.user ? log.user.firstname : 'Unknown' }} |
                                        <strong>Complaint ID:</strong> {{ log.complaint_id }}
                                    </v-card-subtitle>

                                    <v-divider></v-divider>

                                    <v-card-text class="mt-3">
                                        <v-list dense>
                                            <v-list-item v-if="parseNewData(log.new_data).description">
                                                <v-list-item-icon>
                                                    <v-icon color="blue">mdi-note-text</v-icon>
                                                </v-list-item-icon>
                                                <v-list-item-content>
                                                    <strong>Description:</strong> {{
                                                        parseNewData(log.new_data).description }}
                                                </v-list-item-content>
                                            </v-list-item>


                                            <v-list-item v-if="parseNewData(log.new_data).comments">
                                                <v-list-item-icon>
                                                    <v-icon color="purple">mdi-comment-text</v-icon>
                                                </v-list-item-icon>
                                                <v-list-item-content>
                                                    <strong>Comments:</strong> {{ parseNewData(log.new_data).comments }}
                                                </v-list-item-content>
                                            </v-list-item>


                                            <v-list-item v-if="parseNewData(log.new_data).category">
                                                <v-list-item-icon>
                                                    <v-icon color="purple">mdi-tag</v-icon>
                                                </v-list-item-icon>
                                                <v-list-item-content>
                                                    <strong>Category:</strong> {{ parseNewData(log.new_data).category }}
                                                </v-list-item-content>
                                            </v-list-item>
                                            <v-list-item v-if="parseNewData(log.new_data).addressed_to">
                                                <v-list-item-icon>
                                                    <v-icon color="teal">mdi-account-multiple</v-icon>
                                                </v-list-item-icon>
                                                <v-list-item-content>
                                                    <strong>Addressed To:</strong> {{
                                                        parseNewData(log.new_data).addressed_to.join(', ') }}
                                                </v-list-item-content>
                                            </v-list-item>
                                            <!-- <v-list-item v-if="parseNewData(log.new_data).followers">
                                                <v-list-item-icon>
                                                    <v-icon color="amber">mdi-account-group</v-icon>
                                                </v-list-item-icon>
                                                <v-list-item-content>
                                                    <strong>Followers:</strong> {{
                                                    parseNewData(log.new_data).followers.join(',
                                                    ') }}
                                                </v-list-item-content>
                                            </v-list-item> -->

                                            <v-list-item v-if="parseNewData(log.new_data).status">
                                                <v-list-item-icon>
                                                    <v-icon color="green">mdi-check-circle</v-icon>
                                                </v-list-item-icon>
                                                <v-list-item-content>
                                                    <strong>Status:</strong> {{ parseNewData(log.new_data).status }}
                                                </v-list-item-content>
                                            </v-list-item>

                                            <v-list-item v-if="parseNewData(log.new_data).priority">
                                                <v-list-item-icon>
                                                    <v-icon color="red">mdi-alert</v-icon>
                                                </v-list-item-icon>
                                                <v-list-item-content>
                                                    <strong>Priority:</strong> {{ parseNewData(log.new_data).priority }}
                                                </v-list-item-content>
                                            </v-list-item>

                                            <v-list-item v-if="parseNewData(log.new_data).is_anonymous">
                                                <v-list-item-icon>
                                                    <v-icon color="grey">mdi-incognito</v-icon>
                                                </v-list-item-icon>
                                                <v-list-item-content>
                                                    <strong>Anonymous:</strong> {{
                                                        parseNewData(log.new_data).is_anonymous ?
                                                            'Yes' : 'No' }}
                                                </v-list-item-content>
                                            </v-list-item>
                                        </v-list>

                                        <p class="text-caption text-grey mt-3">
                                            <strong>Updated At:</strong> {{ formatDate(log.updated_at) }}
                                        </p>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                    </v-container>
                    <v-container v-else class="text-center">
                        <v-alert type="info" outlined>
                            No complaint logs found.
                        </v-alert>
                    </v-container>
                </v-card-text>

                <v-card-actions class="justify-end">
                    <v-btn color="primary" @click="viewVoiceDialog = false">Close</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>


        <!-- confirm delete modal -->



        <v-dialog v-model="confirmDeleteDialog" max-width="500px">
            <v-card>
                <v-card-title class="headline">Confirm Delete</v-card-title>
                <v-card-text>Are you sure you want to delete this voice?</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" text @click="confirmDeleteDialog = false">Cancel</v-btn>
                    <v-btn color="red darken-1" text @click="confirmDelete(editedVoice)">Delete</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>


        <!-- edit voice -->
        <v-dialog v-model="showDialog" max-width="800px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ formTitle }}</span>
                </v-card-title>
                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="12">
                                <v-text-field variant="outlined" v-model="editedVoice.subject" label="Subject"
                                    :rules="[v => !!v || 'Subject is required']"></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-select variant="outlined" v-model="editedVoice.category" :items="categories"
                                    label="Category" :rules="[v => !!v || 'Category is required']"></v-select>
                            </v-col>
                            <v-col cols="12">
                                <v-select variant="outlined" v-model="editedVoice.addressed_to" :items="users"
                                    label="Address To" item-value="id" item-title="fullname" multiple
                                    :rules="[v => (v && v.length) || 'Addressee is required']"></v-select>
                            </v-col>
                            <v-col cols="12">
                                <v-select variant="outlined" v-model="editedVoice.followers" :items="users"
                                    label="Followers" item-value="id" item-title="fullname" multiple></v-select>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea variant="outlined" v-model="editedVoice.description" label="Description"
                                    :rules="[v => !!v || 'Description is required']"></v-textarea>
                            </v-col>


                            <v-col>
                                <v-file-input ref="fileInput" v-model="attachments" label="Attach Documents (Optional)"
                                    multiple accept=".pdf, .doc, .docx, .png" outlined clearable
                                    @change="handleFileUpload">
                                </v-file-input>
                                <div v-if="attachments && attachments.length > 0">
                                    <v-chip v-for="(file, index) in attachments" :key="index" class="ma-2" close
                                        @click:close="removeAttachment(index)">
                                        {{ file.name }}
                                    </v-chip>
                                </div>
                            </v-col>

                            <v-col cols="12">
                                <v-text-field v-model="newLink" label="Attach Link" append-icon="mdi-plus"
                                    @click:append="addLink"></v-text-field>
                                <v-chip v-for="(link, index) in editedVoice.links" :key="index" class="ma-2" close
                                    @click:close="removeLink(index)">
                                    {{ link }}
                                </v-chip>
                            </v-col>
                            <v-col cols="12">
                                <v-switch v-model="editedVoice.is_anonymous"
                                    :color="editedVoice.is_anonymous ? 'green' : 'blue'"
                                    label="Submit Anonymously"></v-switch>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="danger" text @click="closeDialog">Cancel</v-btn>
                    <v-btn color="green darken-1" text @click="saveVoice">Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
export default {
    props: {
        user: Object,
        roles: Array,
        permissions: Array
    },
    data() {
        return {

            statusDialog: false,
            selectedVoice: {},
            viewVoiceDialog: false,
            attachments: [],
            confirmDeleteDialog: false,
            followers: [],
            addressed_to: [],
            filteredUsers: [],
            loading: false,
            base_url: '/',
            showDialog: false,
            search: '',
            selectedFilter: null,
            filters: ['All', 'Closed', 'Request', 'Incident'],
            voices: [],
            headers: [
                { title: 'ID', value: 'id' },
                { title: 'Subject', value: 'subject' },
                { title: 'Description', value: 'description' },
                { title: 'Category', value: 'category' },
                // { title: 'Priority', value: 'priority' },
                // { title: 'Date Opened', value: 'created_at' },
                { title: 'Attachments', value: 'attachments' },
                { title: 'Links', value: 'links' },
                { title: 'Addressee', value: 'addressed_to' },
                { title: 'Followers', value: 'followers' },
                { title: 'Comments', value: 'comments' },
                { title: 'Status', value: 'status' },

                // { title: 'Resolution', value: 'resolution' },
                { title: 'Creation Date', value: 'created_at' },
                { title: 'Actions', value: 'actions', sortable: false },
            ],
            editedIndex: -1,
            editedVoice: this.getDefaultVoice(),
            defaultVoice: this.getDefaultVoice(),
            users: [],
            statuses: ['Open', 'In Progress', 'Resolved', 'Closed'],
            categories: ['Harassment', 'Workplace Safety', 'Compensation & Benefits', 'Employee Rights', 'Workload & Scheduling', 'Other'],
            newLink: '',
            links: [],

        };
    },
    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'Make Suggestion' : 'Edit Suggestion';
        }
    },
    created() {
        console.log("User:", this.user);
        console.log("Roles:", this.roles);
        console.log("Permissions:", this.permissions);
        this.fetchUsers();
        this.fetchVoices();
    },
    methods:
    {
        parseNewData(data) {
            try {
                return JSON.parse(data);
            } catch (e) {
                console.error("Error parsing JSON:", e);
                return {};
            }
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleString();
        },
        parseLinks(linksString) {
            try {
                return typeof linksString === 'string'
                    ? JSON.parse(linksString)
                    : (Array.isArray(linksString) ? linksString : []);
            } catch (e) {
                console.error('Error parsing links:', e);
                return [];
            }
        },
        getDisplayLink(link) {
            // Display a more readable version of the link
            // This will show the domain name instead of the full URL
            try {
                const url = new URL(link);
                return url.hostname;
            } catch (e) {
                return link;
            }
        },
        parseAttachments(attachmentsString) {
            try {
                return typeof attachmentsString === 'string'
                    ? JSON.parse(attachmentsString)
                    : (Array.isArray(attachmentsString) ? attachmentsString : []);
            } catch (e) {
                console.error('Error parsing attachments:', e);
                return [];
            }
        },

        getAttachmentUrl(attachment) {

            return `/storage/${attachment.path}`;
        },
        updateField() {
            const field = this.dialogField;
            if (!field) return;

            const updatedVoice = { ...this.editedVoice };

            axios.put(`${this.base_url}api/v1/voices/${updatedVoice.id}`, {
                [field]: updatedVoice[field]
            })
                .then(response => {
                    this.showSuccess(`${field.charAt(0).toUpperCase() + field.slice(1)} updated successfully`);
                    this.statusDialog = false;
                    this.fetchVoices();
                })
                .catch(error => {
                    console.error(`Error updating ${field}:`, error);
                    this.showError(`Failed to update ${field}. Please try again.`);
                });
        },
        openconfirmDeleteDialog(item) {
            this.editedVoice = item;
            this.confirmDeleteDialog = true;
        },

        handleFileUpload(file) {
            if (file instanceof File) {
                console.log('Valid file selected:', file);
                const file = this.attachments.document[0]; // Get the first file from the array

                // this.attachments = file;  // Store correctly
            } else {
                console.error('Invalid file selected:', file);
            }
        },
        removeAttachment(index) {
            this.attachments.splice(index, 1); // Remove the file at the specified index
        },
        getDefaultVoice() {
            return {
            subject: '',
            status: 'Open',
            addressed_to: [],
            description: '',
            category: '',
            priority: 'Medium',
            is_anonymous: true,
            attachments: [],
            links: [],
            followers: [],
            viewVoiceDialog: false,
            };
        },
        editVoice(item) {
            this.editedIndex = this.voices.indexOf(item);
            this.editedVoice = Object.assign({}, item);
            this.editedVoice.links = this.editedVoice.links || []; // Initialize links if undefined
            this.showDialog = true;
        },
        fetchUsers() {
            this.loading = true;
            axios.get(`${this.base_url}api/v1/users`)
                .then(response => {
                    this.users = response.data.users.map(user => ({
                        ...user,
                        fullname: `${user.firstname} ${user.lastname}`
                    }));
                    this.loading = false;
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Error fetching users:', error);
                    this.showError('Failed to load users. Please try again.');
                });
        },
        fetchVoices() {
            this.loading = true;
            axios.get(`${this.base_url}api/v1/voices`)
                .then(response => {
                    // Make sure the property name matches what the API returns
                    this.voices = response.data.complaints || response.data.voices || [];
                    const test = this.voices
                    console.log('voices:', test);
                    this.loading = false;
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Error fetching voices:', error);
                    this.showError('Failed to load voices. Please try again.');
                });
        },
        viewVoice(id) {
            this.viewVoiceDialog = true;
            this.loading = true;
            axios.get(`${this.base_url}api/v1/voices/${id}`)
                .then(response => {
                    this.selectedVoice = response.data.logs;
                    // this.viewVoiceDialog = true;
                    console.log('Selected voice:', this.selectedVoice);
                    this.loading = false;
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Error fetching voice details:', error);
                    this.showError('Failed to load voice details. Please try again.');
                });
        },
        openDialog() {
            this.editedIndex = -1;
            this.editedVoice = Object.assign({}, this.defaultVoice);
            this.showDialog = true;
        },
        editVoice(item) {
            this.editedIndex = this.voices.indexOf(item);
            this.editedVoice = Object.assign({}, item);
            this.showDialog = true;
        },
        confirmDelete(item) {
            axios.delete(`${this.base_url}api/v1/voices/${item.id}`)
                .then(() => {
                    this.voices.splice(this.voices.indexOf(item), 1);
                    this.showSuccess('Voice deleted successfully');
                    this.loading = false;
                    this.confirmDeleteDialog = false; // Close the dialog
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Error deleting voice:', error);
                    this.showError('Failed to delete voice. Please try again.');
                });
        }
        ,
        closeDialog() {
            this.showDialog = false;
            this.editedVoice = Object.assign({}, this.defaultVoice);
            this.editedIndex = -1;
        },
        saveVoice() {
            if (!this.validateForm()) {
                return;
            }

            const formData = new FormData();
            formData.append('subject', this.editedVoice.subject);
            formData.append('description', this.editedVoice.description);
            formData.append('category', this.editedVoice.category);
            formData.append('status', this.editedVoice.status);
            formData.append('priority', this.editedVoice.priority);
            formData.append('user_id', this.user.id,);

            // Handle array values correctly
            if (this.editedVoice.addressed_to && this.editedVoice.addressed_to.length) {
                this.editedVoice.addressed_to.forEach(assignee => {
                    formData.append('addressed_to[]', assignee);
                });
            }
            if (this.editedVoice.followers && this.editedVoice.followers.length) {
                this.editedVoice.followers.forEach(follower => {
                    formData.append('followers[]', follower);
                });
            }
            formData.append('is_anonymous', this.editedVoice.is_anonymous ? 1 : 0);

            if (this.attachments && this.attachments.length > 0) {
                formData.append('attachments', this.attachments[0]); // Only append the first file
                console.log('Appending first file:', this.attachments[0]);
            } else {
                console.log('No file selected');
            }
            // Handle links properly
            if (this.editedVoice.links && this.editedVoice.links.length) {
                this.editedVoice.links.forEach(link => {
                    formData.append('links[]', link);
                });
            }

            this.loading = true;
            const url = this.editedIndex === -1
                ? `${this.base_url}api/v1/voices`
                : `${this.base_url}api/v1/voices/${this.editedVoice.id}`;

            const method = this.editedIndex === -1 ? 'post' : 'put';

            axios[method](url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(response => {
                    this.loading = false;
                    this.showSuccess('Voice saved successfully');
                    this.closeDialog();
                    this.fetchVoices(); // Refresh the list
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Error saving voice:', error);
                    this.showError('Failed to save voice. Please try again.');
                });
        },

        openStatusDialog(item, field) {
            this.editedVoice = item;
            this.dialogField = field;
            this.statusDialog = true;
        },
        validateForm() {
            // Basic validation
            if (!this.editedVoice.subject) {
                this.showError('Subject is required');
                return false;
            }
            if (!this.editedVoice.description) {
                this.showError('Description is required');
                return false;
            }
            if (!this.editedVoice.category) {
                this.showError('Category is required');
                return false;
            }
            if (!this.editedVoice.addressed_to || !this.editedVoice.addressed_to.length) {
                this.showError('At least one addressee is required');
                return false;
            }
            return true;
        },
        addLink() {
            if (this.newLink) {
                if (!this.editedVoice.links) {
                    this.editedVoice.links = [];
                }
                this.editedVoice.links.push(this.newLink);
                this.newLink = '';
            }
        },
        removeLink(index) {
            this.editedVoice.links.splice(index, 1);
        },
        getStatusColor(status) {
            switch (status) {
                case 'Open':
                    return 'blue';
                case 'In Progress':
                    return 'orange';
                case 'Resolved':
                    return 'green';
                case 'Closed':
                    return 'red';
                default:
                    return 'grey';
            }
        },
        getPriorityColor(priority) {
            switch (priority) {
                case 'High':
                    return 'red';
                case 'Medium':
                    return 'orange';
                case 'Low':
                    return 'green';
                default:
                    return 'grey';
            }
        },
        showSuccess(message) {
            if (typeof this.$toastr !== 'undefined') {
                this.$toastr.success(message);
            } else {
                console.error(message);
            }
        },
        showError(message) {
            if (typeof this.$toastr !== 'undefined') {
                this.$toastr.error(message);
            } else {
                console.error(message);
            }
        }
    }
};
</script>
