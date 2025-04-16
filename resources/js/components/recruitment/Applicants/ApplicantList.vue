<template>
  <v-container fluid>
    <v-card class="elevation-3 rounded-lg">
      <v-card-title class="d-flex justify-space-between align-center">
        <h2>Applicant Tracking System</h2>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="showNewApplicantDialog = true"
        >
          Add Applicant
        </v-btn>
      </v-card-title>
      
      <!-- Dashboard Summary -->
      <v-card-text>
        <v-row>
          <v-col cols="12" sm="6" md="3">
            <v-card class="mb-4 rounded-lg" color="primary" dark>
              <v-card-text>
                <div class="text-h5 mb-2">{{ totalApplications }}</div>
                <div class="text-overline">Total Applications</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-card class="mb-4 rounded-lg" color="success" dark>
              <v-card-text>
                <div class="text-h5 mb-2">{{ totalHired }}</div>
                <div class="text-overline">Hired Candidates</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-card class="mb-4 rounded-lg" color="warning" dark>
              <v-card-text>
                <div class="text-h5 mb-2">{{ activeJobs }}</div>
                <div class="text-overline">Active Jobs</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-card class="mb-4 rounded-lg" color="info" dark>
              <v-card-text>
                <div class="text-h5 mb-2">{{ applicationsThisWeek }}</div>
                <div class="text-overline">New This Week</div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>

      <!-- Filters and Search -->
      <v-card-text>
        <v-row>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="search"
              prepend-inner-icon="mdi-magnify"
              label="Search applicants"
              density="comfortable"
              variant="outlined"
              hide-details
              clearable
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="2">
            <v-select
              v-model="stageFilter"
              :items="stageOptions"
              label="Application Stage"
              density="comfortable"
              variant="outlined"
              hide-details
              clearable
            ></v-select>
          </v-col>
          <v-col cols="12" md="2">
            <v-select
              v-model="jobFilter"
              :items="jobOptions"
              label="Job Position"
              density="comfortable"
              variant="outlined"
              hide-details
              clearable
            ></v-select>
          </v-col>
          <v-col cols="12" md="2">
            <v-select
              v-model="sourceFilter"
              :items="sourceOptions"
              label="Source"
              density="comfortable"
              variant="outlined"
              hide-details
              clearable
            ></v-select>
          </v-col>
          <v-col cols="12" md="2">
            <v-select
              v-model="dateFilter"
              :items="dateFilterOptions"
              label="Date Range"
              density="comfortable"
              variant="outlined"
              hide-details
              clearable
            ></v-select>
          </v-col>
        </v-row>
      </v-card-text>

      <!-- Applicants Table -->
      <v-data-table
        :headers="headers"
        :items="filteredApplications"
        :items-per-page="10"
        :items-per-page-options="[5, 10, 15, 20]"
        :search="search"
        class="elevation-1 rounded mx-4 mb-4"
        item-key="application_id"
      >
        <!-- Table Columns -->
        <template v-slot:item.applicant_name="{ item }">
          <div class="d-flex align-center">
            <v-avatar 
              :color="getInitialsColor(item.applicant_name)" 
              size="36" 
              class="mr-2"
            >
              {{ getInitials(item.applicant_name) }}
            </v-avatar>
            <div>
              <div class="font-weight-medium">{{ item.applicant_name }}</div>
              <div class="text-caption">{{ item.applicant_email }}</div>
            </div>
          </div>
        </template>

        <template v-slot:item.job_title="{ item }">
          <div>{{ item.job_title }}</div>
          <div class="text-caption">ID: {{ item.job_id }}</div>
        </template>

        <template v-slot:item.submitted_at="{ item }">
          {{ formatDate(item.submitted_at) }}
        </template>

        <template v-slot:item.status="{ item }">
          <v-chip
            :color="getStatusColor(item.status)"
            size="small"
            text-color="white"
          >
            {{ item.status }}
          </v-chip>
        </template>

        <template v-slot:item.ai_score="{ item }">
          <v-progress-linear
            :model-value="item.ai_score"
            height="6"
            rounded
            :color="getScoreColor(item.ai_score)"
          ></v-progress-linear>
          <div class="text-caption text-right">{{ item.ai_score }}%</div>
        </template>

        <template v-slot:item.actions="{ item }">
          <v-btn
            size="small"
            icon
            variant="text"
            color="primary"
            @click="viewApplication(item)"
          >
            <v-icon>mdi-eye</v-icon>
          </v-btn>
          <v-btn
            size="small"
            icon
            variant="text"
            color="secondary"
            @click="editApplication(item)"
          >
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
          <v-menu location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn
                size="small"
                icon
                variant="text"
                v-bind="props"
              >
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list>
              <v-list-item
                v-if="item.status === 'shortlisted'"
                @click="moveToStage(item, 'final')"
                prepend-icon="mdi-check-circle-outline"
                title="Move to Final"
              ></v-list-item>
              <v-list-item
                v-if="item.status === 'final'"
                @click="moveToStage(item, 'hired')"
                prepend-icon="mdi-check-circle"
                title="Mark as Hired"
              ></v-list-item>
              <v-list-item
                v-if="item.resume_path"
                :href="item.resume_path"
                download
                prepend-icon="mdi-file-pdf-box"
                title="Download Resume"
              ></v-list-item>
              <v-list-item
                @click="scheduleInterview(item)"
                prepend-icon="mdi-calendar-clock"
                title="Schedule Interview"
              ></v-list-item>
              <v-list-item
                @click="viewAllApplications(item.applicant_id)"
                prepend-icon="mdi-account-multiple"
                title="View All Applications"
              ></v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-data-table>
    </v-card>

    <!-- Application Details Dialog -->
    <v-dialog v-model="applicationDialog" max-width="700px">
      <v-card v-if="selectedApplication">
        <v-card-title class="text-h5 d-flex justify-space-between">
          <span>{{ selectedApplication.applicant_name }}</span>
          <v-chip
            :color="getStatusColor(selectedApplication.status)"
            size="small"
            text-color="white"
          >
            {{ selectedApplication.status }}
          </v-chip>
        </v-card-title>

        <v-card-text>
          <v-row>
            <v-col cols="12" md="6">
              <v-list>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="primary">mdi-email</v-icon>
                  </template>
                  <v-list-item-title>{{ selectedApplication.applicant_email }}</v-list-item-title>
                  <v-list-item-subtitle>Email</v-list-item-subtitle>
                </v-list-item>
                <v-list-item v-if="selectedApplication.applicant_phone">
                  <template v-slot:prepend>
                    <v-icon color="primary">mdi-phone</v-icon>
                  </template>
                  <v-list-item-title>{{ selectedApplication.applicant_phone }}</v-list-item-title>
                  <v-list-item-subtitle>Phone</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-col>
            <v-col cols="12" md="6">
              <v-list>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="primary">mdi-briefcase</v-icon>
                  </template>
                  <v-list-item-title>{{ selectedApplication.job_title }}</v-list-item-title>
                  <v-list-item-subtitle>Position</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="primary">mdi-calendar</v-icon>
                  </template>
                  <v-list-item-title>{{ formatDate(selectedApplication.submitted_at) }}</v-list-item-title>
                  <v-list-item-subtitle>Date Applied</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-col>
          </v-row>

          <v-divider class="my-4"></v-divider>

          <v-row>
            <v-col cols="12" md="6">
              <v-card variant="outlined" class="mb-4">
                <v-card-title class="text-subtitle-1">Application Details</v-card-title>
                <v-card-text>
                  <v-list>
                    <v-list-item>
                      <template v-slot:prepend>
                        <v-icon color="primary">mdi-source-branch</v-icon>
                      </template>
                      <v-list-item-title>{{ selectedApplication.source || 'N/A' }}</v-list-item-title>
                      <v-list-item-subtitle>Source</v-list-item-subtitle>
                    </v-list-item>
                    <v-list-item>
                      <template v-slot:prepend>
                        <v-icon color="primary">mdi-chart-line</v-icon>
                      </template>
                      <v-list-item-title>{{ selectedApplication.ai_score || 'N/A' }}%</v-list-item-title>
                      <v-list-item-subtitle>AI Score</v-list-item-subtitle>
                    </v-list-item>
                    <v-list-item v-if="selectedApplication.user_id">
                      <template v-slot:prepend>
                        <v-icon color="primary">mdi-account-tie</v-icon>
                      </template>
                      <v-list-item-title>{{ getUserName(selectedApplication.user_id) }}</v-list-item-title>
                      <v-list-item-subtitle>Assigned HR</v-list-item-subtitle>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" md="6">
              <v-card variant="outlined" class="mb-4">
                <v-card-title class="text-subtitle-1">Documents & Links</v-card-title>
                <v-card-text>
                  <v-list>
                    <v-list-item v-if="selectedApplication.resume_path">
                      <template v-slot:prepend>
                        <v-icon color="primary">mdi-file-pdf-box</v-icon>
                      </template>
                      <v-list-item-title>
                        <a :href="selectedApplication.resume_path" download class="text-primary">Resume</a>
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="selectedApplication.cover_letter_path">
                      <template v-slot:prepend>
                        <v-icon color="primary">mdi-file-document</v-icon>
                      </template>
                      <v-list-item-title>
                        <a :href="selectedApplication.cover_letter_path" download class="text-primary">Cover Letter</a>
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="selectedApplication.portfolio_url">
                      <template v-slot:prepend>
                        <v-icon color="primary">mdi-web</v-icon>
                      </template>
                      <v-list-item-title>
                        <a :href="selectedApplication.portfolio_url" target="_blank" class="text-primary">Portfolio</a>
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <v-card variant="outlined" class="mb-4">
            <v-card-title class="text-subtitle-1">Notes</v-card-title>
            <v-card-text>
              <v-textarea
                v-model="selectedApplication.notes"
                placeholder="Add notes about this applicant..."
                auto-grow
                rows="3"
                variant="outlined"
              ></v-textarea>
              <v-btn
                color="primary"
                variant="text"
                @click="saveNotes"
                size="small"
                class="mt-2"
              >
                Save Notes
              </v-btn>
            </v-card-text>
          </v-card>

          <v-card v-if="applicantHistory.length > 0" variant="outlined" class="mb-4">
            <v-card-title class="text-subtitle-1">
              Application History
              <v-chip class="ml-2" size="small">{{ applicantHistory.length }}</v-chip>
            </v-card-title>
            <v-card-text>
              <v-timeline density="compact" align="start">
                <v-timeline-item
                  v-for="app in applicantHistory"
                  :key="app.application_id"
                  :dot-color="getStatusColor(app.status)"
                  size="small"
                >
                  <div class="d-flex justify-space-between">
                    <div>
                      <strong>{{ app.job_title }}</strong>
                      <div class="text-caption">{{ formatDate(app.submitted_at) }}</div>
                    </div>
                    <v-chip
                      :color="getStatusColor(app.status)"
                      size="x-small"
                      text-color="white"
                    >
                      {{ app.status }}
                    </v-chip>
                  </div>
                </v-timeline-item>
              </v-timeline>
            </v-card-text>
          </v-card>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            variant="text"
            @click="scheduleInterview(selectedApplication)"
          >
            Schedule Interview
          </v-btn>
          <v-btn
            color="secondary"
            variant="text"
            @click="applicationDialog = false"
          >
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Schedule Interview Dialog -->
    <v-dialog v-model="interviewDialog" max-width="500px">
      <v-card>
        <v-card-title>Schedule Interview</v-card-title>
        <v-card-text>
          <v-form ref="interviewForm">
            <v-text-field
              v-model="interviewForm.title"
              label="Interview Title"
              required
              variant="outlined"
            ></v-text-field>
            <v-select
              v-model="interviewForm.type"
              :items="['Phone', 'Video', 'In-Person', 'Technical Test']"
              label="Interview Type"
              required
              variant="outlined"
            ></v-select>
            <v-row>
              <v-col cols="12" sm="6">
                <v-date-picker v-model="interviewForm.date" class="mb-4"></v-date-picker>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="interviewForm.time"
                  label="Time"
                  type="time"
                  variant="outlined"
                ></v-text-field>
                <v-select
                  v-model="interviewForm.duration"
                  :items="['30 minutes', '45 minutes', '1 hour', '1.5 hours', '2 hours']"
                  label="Duration"
                  variant="outlined"
                ></v-select>
                <v-select
                  v-model="interviewForm.interviewers"
                  :items="fakeUsers"
                  item-title="name"
                  item-value="id"
                  label="Interviewers"
                  multiple
                  chips
                  variant="outlined"
                ></v-select>
              </v-col>
            </v-row>
            <v-textarea
              v-model="interviewForm.notes"
              label="Notes"
              variant="outlined"
              rows="3"
            ></v-textarea>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            variant="text"
            @click="saveInterview"
          >
            Schedule
          </v-btn>
          <v-btn
            color="secondary"
            variant="text"
            @click="interviewDialog = false"
          >
            Cancel
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- New Applicant Dialog -->
    <v-dialog v-model="showNewApplicantDialog" max-width="700px">
      <v-card>
        <v-card-title>Add New Applicant</v-card-title>
        <v-card-text>
          <v-form ref="newApplicantForm">
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="newApplicant.name"
                  label="Full Name"
                  required
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="newApplicant.email"
                  label="Email"
                  type="email"
                  required
                  variant="outlined"
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="newApplicant.phone"
                  label="Phone"
                  variant="outlined"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="newApplicant.job_id"
                  :items="jobOptions"
                  label="Position"
                  required
                  variant="outlined"
                ></v-select>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" md="6">
                <v-select
                  v-model="newApplicant.source"
                  :items="sourceOptions"
                  label="Source"
                  variant="outlined"
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="newApplicant.status"
                  :items="stageOptions"
                  label="Stage"
                  required
                  variant="outlined"
                ></v-select>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-file-input
                  v-model="newApplicant.resume"
                  label="Resume"
                  accept=".pdf,.doc,.docx"
                  prepend-icon="mdi-file-document"
                  variant="outlined"
                ></v-file-input>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-file-input
                  v-model="newApplicant.cover_letter"
                  label="Cover Letter"
                  accept=".pdf,.doc,.docx"
                  prepend-icon="mdi-file-document"
                  variant="outlined"
                ></v-file-input>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="newApplicant.portfolio_url"
                  label="Portfolio URL"
                  variant="outlined"
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-textarea
                  v-model="newApplicant.notes"
                  label="Notes"
                  variant="outlined"
                  rows="3"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            variant="text"
            @click="saveNewApplicant"
          >
            Save
          </v-btn>
          <v-btn
            color="secondary"
            variant="text"
            @click="showNewApplicantDialog = false"
          >
            Cancel
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- All Applications Dialog -->
    <v-dialog v-model="allApplicationsDialog" max-width="800px">
      <v-card v-if="selectedApplicant">
        <v-card-title>
          <v-avatar 
            :color="getInitialsColor(selectedApplicant.name)" 
            size="36" 
            class="mr-2"
          >
            {{ getInitials(selectedApplicant.name) }}
          </v-avatar>
          {{ selectedApplicant.name }}'s Applications
        </v-card-title>
        <v-card-text>
          <v-data-table
            :headers="applicationsHeaders"
            :items="selectedApplicant.applications"
            class="elevation-1"
          >
            <template v-slot:item.job_title="{ item }">
              {{ item.job_title }}
            </template>
            <template v-slot:item.submitted_at="{ item }">
              {{ formatDate(item.submitted_at) }}
            </template>
            <template v-slot:item.status="{ item }">
              <v-chip
                :color="getStatusColor(item.status)"
                size="small"
                text-color="white"
              >
                {{ item.status }}
              </v-chip>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-btn
                size="small"
                icon
                variant="text"
                color="primary"
                @click="viewApplication(item)"
              >
                <v-icon>mdi-eye</v-icon>
              </v-btn>
            </template>
          </v-data-table>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="secondary"
            variant="text"
            @click="allApplicationsDialog = false"
          >
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Analytics Dialog -->
    <v-dialog v-model="analyticsDialog" max-width="1000px">
      <v-card>
        <v-card-title>Analytics Dashboard</v-card-title>
        <v-card-text>
          <v-row>
            <v-col cols="12" md="6">
              <v-card variant="outlined">
                <v-card-title>Applications by Status</v-card-title>
                <v-card-text style="height: 300px">
                  <!-- Chart would be here -->
                  <div class="d-flex justify-center align-center h-100">
                    <div>Chart Placeholder</div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" md="6">
              <v-card variant="outlined">
                <v-card-title>Applications by Source</v-card-title>
                <v-card-text style="height: 300px">
                  <!-- Chart would be here -->
                  <div class="d-flex justify-center align-center h-100">
                    <div>Chart Placeholder</div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="secondary"
            variant="text"
            @click="analyticsDialog = false"
          >
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

// Table Headers
const headers = [
  { title: 'Applicant', key: 'applicant_name', sortable: true },
  { title: 'Position', key: 'job_title', sortable: true },
  { title: 'Date Applied', key: 'submitted_at', sortable: true },
  { title: 'Stage', key: 'status', sortable: true },
  { title: 'Source', key: 'source', sortable: true },
  { title: 'AI Score', key: 'ai_score', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
];

const applicationsHeaders = [
  { title: 'Job Position', key: 'job_title', sortable: true },
  { title: 'Date Applied', key: 'submitted_at', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
];

// Filters
const search = ref('');
const stageFilter = ref('');
const jobFilter = ref('');
const sourceFilter = ref('');
const dateFilter = ref('');

// Options for filters
const stageOptions = ['shortlisted', 'final', 'hired', 'rejected'];
const dateFilterOptions = ['Last 7 days', 'Last 30 days', 'This month', 'Last month', 'This year'];

// Dialog controls
const applicationDialog = ref(false);
const interviewDialog = ref(false);
const showNewApplicantDialog = ref(false);
const allApplicationsDialog = ref(false);
const analyticsDialog = ref(false);

// Selected items
const selectedApplication = ref(null);
const selectedApplicant = ref(null);
const applicantHistory = ref([]);

// Form data
const interviewForm = ref({
  title: '',
  type: 'Video',
  date: null,
  time: '',
  duration: '1 hour',
  interviewers: [],
  notes: '',
});

const newApplicant = ref({
  name: '',
  email: '',
  phone: '',
  job_id: '',
  source: '',
  status: 'shortlisted',
  resume: null,
  cover_letter: null,
  portfolio_url: '',
  notes: '',
});

// Fake data
const fakeUsers = [
  { id: 1, name: 'Alice Smith' },
  { id: 2, name: 'Bob Johnson' },
  { id: 3, name: 'Carol Williams' },
  { id: 4, name: 'David Brown' },
];

const fakeJobs = [
  { id: 1, title: 'Front-End Developer', department: 'Engineering', status: 'active' },
  { id: 2, title: 'Digital Marketer', department: 'Marketing', status: 'active' },
  { id: 3, title: 'HR Manager', department: 'Human Resources', status: 'active' },
  { id: 4, title: 'Backend Engineer', department: 'Engineering', status: 'active' },
  { id: 5, title: 'Product Manager', department: 'Product', status: 'active' },
  { id: 6, title: 'UX Designer', department: 'Design', status: 'closed' },
];

</script>