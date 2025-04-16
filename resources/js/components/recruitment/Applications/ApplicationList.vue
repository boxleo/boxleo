<template>
  <v-card class="mx-auto my-4" elevation="3" max-width="1200">
    <v-card-title class="text-h5 bg-primary text-white d-flex align-center">
      <v-icon icon="mdi-briefcase-account" class="mr-2" size="large"></v-icon>
      Job Applications Dashboard
    </v-card-title>

    <!-- Filters and Search -->
    <v-card-text>
      <v-row>
        <v-col cols="12" sm="6" md="4">
          <v-text-field
            v-model="search"
            label="Search applicants"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="comfortable"
            hide-details
            clearable
          ></v-text-field>
        </v-col>
        <v-col cols="12" sm="6" md="4">
          <v-select
            v-model="statusFilter"
            :items="statusItems"
            item-title="text"
            item-value="value"
            label="Application Status"
            prepend-inner-icon="mdi-filter-variant"
            variant="outlined"
            density="comfortable"
            hide-details
            clearable
          ></v-select>
        </v-col>
        <v-col cols="12" sm="6" md="4">
          <v-select
            v-model="jobFilter"
            :items="jobItems"
            item-title="title"
            item-value="id"
            label="Position"
            prepend-inner-icon="mdi-briefcase"
            variant="outlined"
            density="comfortable"
            hide-details
            clearable
          ></v-select>
        </v-col>
      </v-row>
    </v-card-text>

    <v-divider></v-divider>

    <!-- Applications Data Table -->
    <v-data-table
      v-model:page="currentPage"
      :headers="headers"
      :items="filteredApplications"
      :items-per-page="itemsPerPage"
      :sort-by="[{ key: sortKey, order: sortDirection }]"
      hover
      class="elevation-0"
    >
      <template v-slot:item.status="{ item }">
        <v-chip
          :color="getStatusColor(item.status)"
          :text="getStatusText(item.status)"
          size="small"
          class="text-uppercase font-weight-medium"
        ></v-chip>
      </template>
      
      <template v-slot:item.ai_score="{ item }">
        <v-progress-linear
          :model-value="item.ai_score || 0"
          :color="getScoreColor(item.ai_score)"
          height="10"
          rounded
          class="mt-1"
        >
          <template v-slot:default>
            <strong>{{ item.ai_score || 'N/A' }}%</strong>
          </template>
        </v-progress-linear>
      </template>
      
      <template v-slot:item.submitted_at="{ item }">
        {{ formatDate(item.submitted_at) }}
      </template>
      
      <template v-slot:item.actions="{ item }">
        <div class="d-flex gap-2">
          <v-btn
            size="small"
            variant="text"
            color="primary"
            @click="openDetails(item)"
            :prepend-icon="'mdi-eye'"
          >
            View
          </v-btn>
          <v-btn
            size="small"
            variant="text"
            color="success"
            @click="openStatusDialog(item)"
            :prepend-icon="'mdi-pencil'"
          >
            Update
          </v-btn>
          <v-btn
            v-if="item.resume_path"
            size="small"
            variant="text"
            color="info"
            :href="item.resume_path"
            target="_blank"
            :prepend-icon="'mdi-file-document-outline'"
            download
          >
            Resume
          </v-btn>
        </div>
      </template>
      
      <template v-slot:no-data>
        <v-alert type="info" variant="tonal">
          No applications match your search criteria
        </v-alert>
      </template>
    </v-data-table>

    <!-- Application Details Dialog -->
    <v-dialog v-model="detailsDialog" max-width="800">
      <v-card v-if="selectedApplication">
        <v-toolbar color="primary" dark>
          <v-toolbar-title>{{ selectedApplication.applicant_name }}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-chip
            :color="getStatusColor(selectedApplication.status)"
            :text="getStatusText(selectedApplication.status)"
            class="text-uppercase"
          ></v-chip>
        </v-toolbar>
        
        <v-card-text class="pt-4">
          <v-row>
            <v-col cols="12" md="6">
              <v-list>
                <v-list-subheader class="text-uppercase font-weight-bold">
                  Personal Information
                </v-list-subheader>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="primary" class="mr-2">mdi-email</v-icon>
                  </template>
                  <v-list-item-title>{{ selectedApplication.applicant_email }}</v-list-item-title>
                </v-list-item>
                <v-list-item v-if="selectedApplication.applicant_phone">
                  <template v-slot:prepend>
                    <v-icon color="primary" class="mr-2">mdi-phone</v-icon>
                  </template>
                  <v-list-item-title>{{ selectedApplication.applicant_phone }}</v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="primary" class="mr-2">mdi-briefcase</v-icon>
                  </template>
                  <v-list-item-title>{{ getJobTitle(selectedApplication.job_id) }}</v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="primary" class="mr-2">mdi-calendar</v-icon>
                  </template>
                  <v-list-item-title>
                    Applied: {{ formatDate(selectedApplication.submitted_at) }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="primary" class="mr-2">mdi-source-branch</v-icon>
                  </template>
                  <v-list-item-title>Source: {{ selectedApplication.source || 'N/A' }}</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-col>
            
            <v-col cols="12" md="6">
              <v-list>
                <v-list-subheader class="text-uppercase font-weight-bold">
                  Application Details
                </v-list-subheader>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="primary" class="mr-2">mdi-account-check</v-icon>
                  </template>
                  <v-list-item-title>
                    Assigned to: {{ getUserName(selectedApplication.user_id) || 'Unassigned' }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="primary" class="mr-2">mdi-chart-arc</v-icon>
                  </template>
                  <div>
                    <div class="d-flex align-center">
                      <span>AI Match Score:</span>
                      <v-rating
                        :model-value="Math.round((selectedApplication.ai_score || 0) / 20)"
                        color="amber"
                        density="compact"
                        half-increments
                        readonly
                        size="small"
                        class="ms-2"
                      ></v-rating>
                      <span class="ms-2">{{ selectedApplication.ai_score || 'N/A' }}%</span>
                    </div>
                  </div>
                </v-list-item>
              </v-list>
              
              <v-divider class="my-2"></v-divider>
              
              <v-list>
                <v-list-subheader class="text-uppercase font-weight-bold">
                  Documents & Links
                </v-list-subheader>
                <v-list-item v-if="selectedApplication.resume_path">
                  <template v-slot:prepend>
                    <v-icon color="primary" class="mr-2">mdi-file-document</v-icon>
                  </template>
                  <v-list-item-title>
                    <v-btn
                      variant="text"
                      color="primary"
                      :href="selectedApplication.resume_path"
                      download
                    >
                      Download Resume
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item v-if="selectedApplication.cover_letter_path">
                  <template v-slot:prepend>
                    <v-icon color="primary" class="mr-2">mdi-text-box</v-icon>
                  </template>
                  <v-list-item-title>
                    <v-btn
                      variant="text"
                      color="primary"
                      :href="selectedApplication.cover_letter_path"
                      download
                    >
                      Download Cover Letter
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item v-if="selectedApplication.portfolio_url">
                  <template v-slot:prepend>
                    <v-icon color="primary" class="mr-2">mdi-web</v-icon>
                  </template>
                  <v-list-item-title>
                    <v-btn
                      variant="text"
                      color="primary"
                      :href="selectedApplication.portfolio_url"
                      target="_blank"
                    >
                      View Portfolio
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-col>
          </v-row>
          
          <v-divider class="my-3"></v-divider>
          
          <v-row>
            <v-col cols="12">
              <div class="text-subtitle-1 font-weight-bold">Notes:</div>
              <v-textarea
                v-model="selectedApplication.notes"
                rows="3"
                variant="outlined"
                placeholder="No notes available for this candidate"
                readonly
                hide-details
              ></v-textarea>
            </v-col>
          </v-row>
        </v-card-text>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            @click="openStatusDialog(selectedApplication)"
            prepend-icon="mdi-pencil"
          >
            Update Status
          </v-btn>
          <v-btn
            color="grey-darken-1"
            variant="text"
            @click="detailsDialog = false"
          >
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Status Update Dialog -->
    <v-dialog v-model="statusDialog" max-width="500">
      <v-card v-if="selectedApplication">
        <v-card-title class="bg-primary text-white">
          Update Application Status
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="mb-2">Updating status for <strong>{{ selectedApplication.applicant_name }}</strong></p>
          <v-select
            v-model="newStatus"
            :items="statusItems"
            item-title="text"
            item-value="value"
            label="Select new status"
            variant="outlined"
          ></v-select>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey-darken-1"
            variant="text"
            @click="statusDialog = false"
          >
            Cancel
          </v-btn>
          <v-btn
            color="success"
            @click="updateStatus"
          >
            Update
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>

<script setup>
import { ref, computed } from 'vue';

// Fake Data
const fakeJobs = [
  { id: 1, title: 'Front-End Developer' },
  { id: 2, title: 'Digital Marketer' },
  { id: 3, title: 'HR Manager' },
  { id: 4, title: 'Backend Engineer' },
];

const fakeUsers = [
  { id: 1, name: 'Alice Smith' },
  { id: 2, name: 'Bob Johnson' },
];

const fakeApplications = [
  {
    id: 1,
    job_id: 1,
    applicant_name: 'James Otieno',
    applicant_email: 'james.otieno@example.com',
    applicant_phone: '+254-712-345-678',
    resume_path: '/files/resumes/james_resume.pdf',
    cover_letter_path: '/files/cover_letters/james_cover.pdf',
    portfolio_url: 'https://jamesotieno.dev',
    status: 'under_review',
    submitted_at: '2025-04-10T08:00:00Z',
    source: 'Company Website',
    notes: 'Strong Vue.js skills, needs to clarify experience with TypeScript.',
    user_id: 1,
    ai_score: 85,
  },
  {
    id: 2,
    job_id: 2,
    applicant_name: 'Mercy Wanjiku',
    applicant_email: 'mercy.wanjiku@example.com',
    applicant_phone: '+254-723-456-789',
    resume_path: '/files/resumes/mercy_resume.pdf',
    cover_letter_path: null,
    portfolio_url: 'https://linkedin.com/in/mercywanjiku',
    status: 'interview_scheduled',
    submitted_at: '2025-04-09T10:30:00Z',
    source: 'Indeed',
    notes: 'Excellent campaign management experience.',
    user_id: 2,
    ai_score: 91,
  },
  {
    id: 3,
    job_id: 3,
    applicant_name: 'Peter Kimani',
    applicant_email: 'peter.kimani@example.com',
    applicant_phone: null,
    resume_path: '/files/resumes/peter_resume.pdf',
    cover_letter_path: '/files/cover_letters/peter_cover.pdf',
    portfolio_url: null,
    status: 'received',
    submitted_at: '2025-04-08T14:15:00Z',
    source: 'Referral',
    notes: null,
    user_id: null,
    ai_score: 77,
  },
  {
    id: 4,
    job_id: 4,
    applicant_name: 'Sarah Mumbi',
    applicant_email: 'sarah.mumbi@example.com',
    applicant_phone: '+254-734-567-890',
    resume_path: null,
    cover_letter_path: null,
    portfolio_url: 'https://github.com/sarahmumbi',
    status: 'offered',
    submitted_at: '2025-04-07T09:00:00Z',
    source: 'LinkedIn',
    notes: 'Top candidate for Laravel expertise.',
    user_id: 1,
    ai_score: 93,
  },
  {
    id: 5,
    job_id: 1,
    applicant_name: 'David Ochieng',
    applicant_email: 'david.ochieng@example.com',
    applicant_phone: '+254-745-678-901',
    resume_path: '/files/resumes/david_resume.pdf',
    cover_letter_path: '/files/cover_letters/david_cover.pdf',
    portfolio_url: null,
    status: 'rejected',
    submitted_at: '2025-04-06T16:20:00Z',
    source: 'Company Website',
    notes: 'Lacks required experience in Vue 3.',
    user_id: 2,
    ai_score: 65,
  },
  {
    id: 6,
    job_id: 2,
    applicant_name: 'Esther Njeri',
    applicant_email: 'esther.njeri@example.com',
    applicant_phone: null,
    resume_path: '/files/resumes/esther_resume.pdf',
    cover_letter_path: null,
    portfolio_url: 'https://esthernjeri.com',
    status: 'hired',
    submitted_at: '2025-04-05T11:45:00Z',
    source: 'Job Fair',
    notes: 'Hired after exceptional interview performance.',
    user_id: 1,
    ai_score: 95,
  },
];

// Table headers
const headers = [
  { title: 'Applicant', key: 'applicant_name', sortable: true },
  { title: 'Email', key: 'applicant_email' },
  { title: 'Position', key: 'job_id', sortable: true },
  { title: 'Date Applied', key: 'submitted_at', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Source', key: 'source' },
  { title: 'AI Score', key: 'ai_score', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
];

// Status options with display text
const statusItems = [
  { text: 'All Statuses', value: '' },
  { text: 'Received', value: 'received' },
  { text: 'Under Review', value: 'under_review' },
  { text: 'Interview Scheduled', value: 'interview_scheduled' },
  { text: 'Offered', value: 'offered' },
  { text: 'Rejected', value: 'rejected' },
  { text: 'Hired', value: 'hired' },
];

// State
const applications = ref(fakeApplications);
const search = ref('');
const statusFilter = ref('');
const jobFilter = ref('');
const sortKey = ref('submitted_at');
const sortDirection = ref('desc');
const currentPage = ref(1);
const itemsPerPage = 5;
const selectedApplication = ref(null);
const detailsDialog = ref(false);
const statusDialog = ref(false);
const newStatus = ref('');
const jobItems = ref([{ title: 'All Jobs', id: '' }, ...fakeJobs]);

// Computed Properties
const filteredApplications = computed(() => {
  return applications.value
    .filter((app) => {
      const matchesSearch =
        app.applicant_name.toLowerCase().includes(search.value.toLowerCase()) ||
        app.applicant_email.toLowerCase().includes(search.value.toLowerCase());
      const matchesStatus = statusFilter.value ? app.status === statusFilter.value : true;
      const matchesJob = jobFilter.value ? app.job_id === parseInt(jobFilter.value) : true;
      return matchesSearch && matchesStatus && matchesJob;
    });
});

// Methods
const getJobTitle = (jobId) => {
  const job = fakeJobs.find((j) => j.id === jobId);
  return job ? job.title : 'N/A';
};

const getUserName = (userId) => {
  const user = fakeUsers.find((u) => u.id === userId);
  return user ? user.name : null;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getStatusColor = (status) => {
  const colors = {
    received: 'grey',
    under_review: 'blue',
    interview_scheduled: 'amber',
    offered: 'light-green',
    rejected: 'red',
    hired: 'green',
  };
  return colors[status] || 'grey';
};

const getStatusText = (status) => {
  const text = {
    received: 'Received',
    under_review: 'Under Review',
    interview_scheduled: 'Interview',
    offered: 'Offered',
    rejected: 'Rejected',
    hired: 'Hired',
  };
  return text[status] || status;
};

const getScoreColor = (score) => {
  if (!score) return 'grey';
  if (score >= 90) return 'green';
  if (score >= 75) return 'light-green';
  if (score >= 60) return 'amber';
  return 'red';
};

const openDetails = (application) => {
  selectedApplication.value = application;
  detailsDialog.value = true;
};

const openStatusDialog = (application) => {
  selectedApplication.value = application;
  newStatus.value = application.status;
  statusDialog.value = true;
};

const updateStatus = () => {
  if (selectedApplication.value && newStatus.value) {
    selectedApplication.value.status = newStatus.value;
    statusDialog.value = false;
    // In a real application, you would send an API request to update the status
  }
};
</script>