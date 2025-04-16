<template>
  <v-app>
    <v-main>
      <v-container fluid>
        <!-- Header Section -->
        <v-row class="my-4">
          <v-col cols="12" md="8">
            <h1 class="text-h4 font-weight-bold">Job Management</h1>
            <p class="text-subtitle-1 text-medium-emphasis">Create and manage job listings for your organization</p>
          </v-col>
          <v-col cols="12" md="4" class="d-flex justify-end align-center">
            <v-btn
              color="primary"
              prepend-icon="mdi-plus"
              @click="showJobModal = true"
              size="large"
            >
              Create New Job
            </v-btn>
          </v-col>
        </v-row>

        <!-- Stats Cards -->
        <v-row>
          <v-col cols="12" sm="6" md="3">
            <v-card>
              <v-card-text>
                <div class="d-flex align-center">
                  <v-avatar color="primary" variant="tonal" size="40" class="mr-4">
                    <v-icon>mdi-briefcase</v-icon>
                  </v-avatar>
                  <div>
                    <div class="text-caption text-medium-emphasis">Active Jobs</div>
                    <div class="text-h5 font-weight-bold">{{ activeJobs }}</div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
          
          <v-col cols="12" sm="6" md="3">
            <v-card>
              <v-card-text>
                <div class="d-flex align-center">
                  <v-avatar color="success" variant="tonal" size="40" class="mr-4">
                    <v-icon>mdi-account-group</v-icon>
                  </v-avatar>
                  <div>
                    <div class="text-caption text-medium-emphasis">Applications</div>
                    <div class="text-h5 font-weight-bold">{{ totalApplications }}</div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
          
          <v-col cols="12" sm="6" md="3">
            <v-card>
              <v-card-text>
                <div class="d-flex align-center">
                  <v-avatar color="info" variant="tonal" size="40" class="mr-4">
                    <v-icon>mdi-calendar-month</v-icon>
                  </v-avatar>
                  <div>
                    <div class="text-caption text-medium-emphasis">This Month</div>
                    <div class="text-h5 font-weight-bold">{{ jobsThisMonth }}</div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
          
          <v-col cols="12" sm="6" md="3">
            <v-card>
              <v-card-text>
                <div class="d-flex align-center">
                  <v-avatar color="warning" variant="tonal" size="40" class="mr-4">
                    <v-icon>mdi-check-circle</v-icon>
                  </v-avatar>
                  <div>
                    <div class="text-caption text-medium-emphasis">Open Positions</div>
                    <div class="text-h5 font-weight-bold">{{ openPositions }}</div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <!-- Filter & Search -->
        <v-card class="my-4">
          <v-card-text>
            <v-row>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="searchQuery"
                  prepend-inner-icon="mdi-magnify"
                  label="Search jobs..."
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="4">
                <v-select
                  v-model="departmentFilter"
                  label="Department"
                  :items="departments"
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="4">
                <v-select
                  v-model="statusFilter"
                  label="Status"
                  :items="statuses"
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-select>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Jobs Table -->
        <v-card>
          <v-data-table
            v-if="filteredJobs.length"
            :headers="headers"
            :items="filteredJobs"
            :items-per-page="10"
            density="comfortable"
            hover
          >
            <template v-slot:item.title="{ item }">
              <div>
                <div class="font-weight-medium">{{ item.title }}</div>
                <div class="text-caption text-medium-emphasis d-flex align-center">
                  <v-icon size="small" class="mr-1">mdi-map-marker</v-icon>
                  {{ item.location }}
                </div>
              </div>
            </template>
            
            <template v-slot:item.postedDate="{ item }">
              <div>
                <div>{{ formatDate(item.postedDate) }}</div>
                <div class="text-caption text-medium-emphasis">{{ getTimeAgo(item.postedDate) }}</div>
              </div>
            </template>
            
            <template v-slot:item.status="{ item }">
              <v-chip
                :color="getStatusColor(item.status)"
                size="small"
                variant="tonal"
              >
                {{ item.status.charAt(0).toUpperCase() + item.status.slice(1) }}
              </v-chip>
            </template>
            
            <template v-slot:item.actions="{ item }">
              <v-icon
                size="small"
                class="mr-2"
                @click="editJob(item)"
                color="primary"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                size="small"
                class="mr-2"
                @click="viewApplications(item)"
                color="info"
              >
                mdi-eye
              </v-icon>
              <v-icon
                size="small"
                @click="confirmDelete(item)"
                color="error"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>
          
          <div v-else class="d-flex flex-column align-center justify-center pa-8">
            <v-icon
              icon="mdi-folder-plus-outline"
              size="64"
              color="grey-lighten-1"
              class="mb-4"
            ></v-icon>
            <h3 class="text-h6 font-weight-medium mb-1">No jobs found</h3>
            <p class="text-body-2 text-medium-emphasis mb-4">
              Get started by creating a new job posting.
            </p>
            <v-btn
              color="primary"
              prepend-icon="mdi-plus"
              @click="showJobModal = true"
              variant="flat"
            >
              Create New Job
            </v-btn>
          </div>
        </v-card>
      </v-container>
    </v-main>

    <!-- Job Form Dialog -->
    <v-dialog
      v-model="showJobModal"
      max-width="900px"
    >
      <v-card>
        <v-card-title class="bg-primary text-white">
          {{ editingJob ? 'Edit Job Listing' : 'Create New Job Listing' }}
        </v-card-title>
        
        <v-card-text class="pt-4">
          <v-form ref="jobForm" @submit.prevent="saveJob">
            <v-container>
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.title"
                    label="Job Title*"
                    variant="outlined"
                    placeholder="e.g. Senior Frontend Developer"
                    :rules="[v => !!v || 'Title is required']"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.location"
                    label="Location*"
                    variant="outlined"
                    placeholder="e.g. New York, NY (Remote)"
                    :rules="[v => !!v || 'Location is required']"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.department"
                    label="Department*"
                    :items="departments"
                    variant="outlined"
                    :rules="[v => !!v || 'Department is required']"
                  ></v-select>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.status"
                    label="Status"
                    :items="statuses"
                    variant="outlined"
                  ></v-select>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.salary_min"
                    label="Minimum Salary"
                    variant="outlined"
                    type="number"
                    prefix="$"
                    suffix="USD"
                    placeholder="0.00"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.salary_max"
                    label="Maximum Salary"
                    variant="outlined"
                    type="number"
                    prefix="$"
                    suffix="USD"
                    placeholder="0.00"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12">
                  <v-textarea
                    v-model="form.responsibilities"
                    label="Job Responsibilities*"
                    variant="outlined"
                    placeholder="Describe the main responsibilities for this role"
                    rows="3"
                    :rules="[v => !!v || 'Job responsibilities are required']"
                  ></v-textarea>
                </v-col>
                
                <v-col cols="12">
                  <v-textarea
                    v-model="form.qualifications"
                    label="Qualifications*"
                    variant="outlined"
                    placeholder="List required skills, education, and experience"
                    rows="3"
                    :rules="[v => !!v || 'Qualifications are required']"
                  ></v-textarea>
                </v-col>
                
                <v-col cols="12">
                  <v-textarea
                    v-model="form.benefits"
                    label="Benefits"
                    variant="outlined"
                    placeholder="Describe benefits, perks, and other compensation"
                    rows="2"
                  ></v-textarea>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-textarea
                    v-model="form.application_instructions"
                    label="Application Instructions"
                    variant="outlined"
                    placeholder="Instructions for applicants"
                    rows="2"
                  ></v-textarea>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-date-picker
                    v-model="form.application_deadline"
                    label="Application Deadline"
                    variant="outlined"
                  ></v-date-picker>
                </v-col>
              </v-row>
            </v-container>
          </v-form>
        </v-card-text>
        
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn
            variant="text"
            @click="showJobModal = false"
          >
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            @click="saveJob"
            variant="elevated"
          >
            {{ editingJob ? 'Save Changes' : 'Create Job' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog
      v-model="showDeleteModal"
      max-width="500px"
    >
      <v-card>
        <v-card-title class="bg-error text-white">
          Delete Job Listing
        </v-card-title>
        
        <v-card-text class="pt-4">
          <p>Are you sure you want to delete the job listing for "{{ jobToDelete?.title }}"?</p>
          <p class="text-body-2 text-medium-emphasis">This action cannot be undone.</p>
        </v-card-text>
        
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn
            variant="text"
            @click="showDeleteModal = false"
          >
            Cancel
          </v-btn>
          <v-btn
            color="error"
            @click="deleteJob"
            variant="elevated"
          >
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-app>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

// Form data
const form = ref({
  title: '',
  location: '',
  salary_min: null,
  salary_max: null,
  department: '',
  responsibilities: '',
  qualifications: '',
  benefits: '',
  application_instructions: '',
  application_deadline: '',
  status: 'published',
});

// Table data
const jobs = ref([
  {
    id: 1,
    title: "Frontend Developer",
    location: "New York, NY (Hybrid)",
    department: "Tech",
    postedDate: "2025-04-01",
    applications: 24,
    status: "published",
    salary_min: 90000,
    salary_max: 120000,
  },
  {
    id: 2,
    title: "HR Officer",
    location: "Boston, MA (On-site)",
    department: "Human Resources",
    postedDate: "2025-03-28",
    applications: 15,
    status: "published",
    salary_min: 65000,
    salary_max: 85000,
  },
  {
    id: 3,
    title: "Marketing Specialist",
    location: "Remote (US)",
    department: "Marketing",
    postedDate: "2025-04-10",
    applications: 8,
    status: "draft",
    salary_min: 70000,
    salary_max: 90000,
  }
]);

// Stats
const activeJobs = computed(() => jobs.value.filter(job => job.status === 'published').length);
const totalApplications = computed(() => jobs.value.reduce((acc, job) => acc + job.applications, 0));
const jobsThisMonth = computed(() => {
  const now = new Date();
  const currentMonth = now.getMonth();
  const currentYear = now.getFullYear();
  return jobs.value.filter(job => {
    const postedDate = new Date(job.postedDate);
    return postedDate.getMonth() === currentMonth && postedDate.getFullYear() === currentYear;
  }).length;
});
const openPositions = computed(() => jobs.value.filter(job => job.status === 'published').length);

// UI States
const showJobModal = ref(false);
const showDeleteModal = ref(false);
const jobToDelete = ref(null);
const editingJob = ref(false);
const currentJobId = ref(null);
const jobForm = ref(null);

// Filter states
const searchQuery = ref('');
const departmentFilter = ref('');
const statusFilter = ref('');

// Form options
const departments = ['Tech', 'Human Resources', 'Marketing', 'Finance', 'Operations', 'Sales'];
const statuses = ['published', 'draft', 'closed'];

// Table headers
const headers = [
  { title: 'Title & Location', key: 'title', sortable: true },
  { title: 'Department', key: 'department', sortable: true },
  { title: 'Posted Date', key: 'postedDate', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Applications', key: 'applications', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false }
];

// Filtered jobs based on search and filters
const filteredJobs = computed(() => {
  return jobs.value.filter(job => {
    const matchesSearch = !searchQuery.value || 
      job.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
      job.department.toLowerCase().includes(searchQuery.value.toLowerCase());
    
    const matchesDepartment = !departmentFilter.value || job.department === departmentFilter.value;
    const matchesStatus = !statusFilter.value || job.status === statusFilter.value;
    
    return matchesSearch && matchesDepartment && matchesStatus;
  });
});

// Format date
const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

// Get time ago from date
const getTimeAgo = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now - date);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays === 0) return 'Today';
  if (diffDays === 1) return 'Yesterday';
  if (diffDays < 7) return `${diffDays} days ago`;
  if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
  return `${Math.floor(diffDays / 30)} months ago`;
};

// Get status color
const getStatusColor = (status) => {
  switch (status) {
    case 'published': return 'success';
    case 'draft': return 'warning';
    case 'closed': return 'error';
    default: return 'grey';
  }
};

// Edit job
const editJob = (job) => {
  currentJobId.value = job.id;
  editingJob.value = true;
  Object.keys(form.value).forEach(key => {
    if (key in job) {
      form.value[key] = job[key];
    }
  });
  showJobModal.value = true;
};

// Save job
const saveJob = () => {
  if (editingJob.value) {
    const index = jobs.value.findIndex(job => job.id === currentJobId.value);
    if (index !== -1) {
      jobs.value[index] = { ...jobs.value[index], ...form.value };
    }
  } else {
    const newId = Math.max(0, ...jobs.value.map(job => job.id)) + 1;
    const today = new Date().toISOString().split('T')[0];
    
    jobs.value.push({
      id: newId,
      ...form.value,
      postedDate: today,
      applications: 0
    });
  }
  
  resetForm();
  showJobModal.value = false;
};

// View applications
const viewApplications = (job) => {
  // Placeholder for viewing applications functionality
  console.log(`Viewing applications for job: ${job.title}`);
};

// Confirm delete
const confirmDelete = (job) => {
  jobToDelete.value = job;
  showDeleteModal.value = true;
};

// Delete job
const deleteJob = () => {
  if (jobToDelete.value) {
    const index = jobs.value.findIndex(job => job.id === jobToDelete.value.id);
    if (index !== -1) {
      jobs.value.splice(index, 1);
    }
    jobToDelete.value = null;
    showDeleteModal.value = false;
  }
};

// Reset form
const resetForm = () => {
  form.value = {
    title: '',
    location: '',
    salary_min: null,
    salary_max: null,
    department: '',
    responsibilities: '',
    qualifications: '',
    benefits: '',
    application_instructions: '',
    application_deadline: '',
    status: 'published',
  };
  editingJob.value = false;
  currentJobId.value = null;
};

// Initialize component
onMounted(() => {
  // Any initialization logic can go here
});
</script>