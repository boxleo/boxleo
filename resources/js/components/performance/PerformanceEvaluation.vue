<template>
  <v-layout>
    <!-- Filter Drawer -->
    <v-navigation-drawer location="right" width="500" v-model="drawer" temporary>
      <v-container>
        <v-row justify="space-between" class="drawer-header">
          <v-col>
            <v-list-item-title>Filter</v-list-item-title>
          </v-col>
          <v-col class="text-right">
            <v-icon @click="drawer = false">mdi-close</v-icon>
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row align="center" justify="center">
          <v-col cols="12">
            <v-list dense nav>
              <!-- Filter by Department -->
              <v-list-item>
                <v-col cols="12">
                  <v-label>Department:</v-label>
                  <v-select v-model="filters.department_id" item-value="id" item-title="name" :items="departments"
                  multiple
                    clearable dense>
                  </v-select>
                </v-col>
              </v-list-item>
              <!-- Filter by Evaluation Date -->
              <v-list-item>
                <v-col cols="12">
                  <v-label>Evaluation Date:</v-label>
                  <v-text-field v-model="filters.evaluation_date" type="date">
                  </v-text-field>
                </v-col>
              </v-list-item>
              <!-- Filter by Employee -->
              <v-list-item>
                <v-col cols="12">
                  <v-label>Employee:</v-label>
                  <v-autocomplete v-model="filters.user_id" :items="employees" item-title="fullName" item-value="id"
                    clearable dense>
                  </v-autocomplete>
                </v-col>
              </v-list-item>
              <!-- Filter by Evaluator -->
              <v-list-item>
                <v-col cols="12">
                  <v-label>Evaluator:</v-label>
                  <v-autocomplete v-model="filters.evaluator_id" :items="evaluators" item-title="fullName"
                    item-value="id" clearable dense>
                  </v-autocomplete>
                </v-col>
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>
        <v-row align="center" justify="center" class="drawer-footer">
          <v-col cols="12">
            <v-btn @click.prevent="filterEvaluations">
              <v-icon>mdi-filter</v-icon>
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main>
      <!-- Summary Cards -->
      <v-col>
        <v-row>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="purple lighten-1" size="48">mdi-star-circle</v-icon>
                <v-col>
                  <div class="text-h6">{{ averageTotalScore }}</div>
                  <div class="subtitle-2">Average Total Score</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="blue lighten-1" size="48">mdi-percent</v-icon>
                <v-col>
                  <div class="text-h6">{{ averagePercentage }}%</div>
                  <div class="subtitle-2">Average Percentage</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="green lighten-1" size="48">mdi-account</v-icon>
                <v-col>
                  <div class="text-h6">{{ averageAttendance }}</div>
                  <div class="subtitle-2">Attendance Score</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="orange lighten-1" size="48">mdi-briefcase-check</v-icon>
                <v-col>
                  <div class="text-h6">{{ averageProductivity }}</div>
                  <div class="subtitle-2">Avg. Productivity</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="red lighten-1" size="48">mdi-lightbulb</v-icon>
                <v-col>
                  <div class="text-h6">{{ averageProblemsSolved }}</div>
                  <div class="subtitle-2">Problems Solved</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="cyan lighten-1" size="48">mdi-file-document</v-icon>
                <v-col>
                  <div class="text-h6">{{ averageReportsSubmitted }}</div>
                  <div class="subtitle-2">Reports Submitted</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="teal lighten-1" size="48">mdi-book-open</v-icon>
                <v-col>
                  <div class="text-h6">{{ averageKnowledgeOfWork }}</div>
                  <div class="subtitle-2">Knowledge of Work</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="amber lighten-1" size="48">mdi-account-group</v-icon>
                <v-col>
                  <div class="text-h6">{{ averageTeamWork }}</div>
                  <div class="subtitle-2">Team Work</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="brown lighten-1" size="48">mdi-eye</v-icon>
                <v-col>
                  <div class="text-h6">{{ averageReliabilityVisibility }}</div>
                  <div class="subtitle-2">Reliability & Visibility</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="pink lighten-1" size="48">mdi-gavel</v-icon>
                <v-col>
                  <div class="text-h6">{{ averageDiscipline }}</div>
                  <div class="subtitle-2">Discipline</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="purple lighten-1" size="48">mdi-quality-high</v-icon>
                <v-col>
                  <div class="text-h6">{{ averageQualityOfWork }}</div>
                  <div class="subtitle-2">Quality of Work</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="blue lighten-1" size="48">mdi-message</v-icon>
                <v-col>
                  <div class="text-h6">{{ averageCommunication }}</div>
                  <div class="subtitle-2">Communication</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="green lighten-1" size="48">mdi-star</v-icon>
                <v-col>
                  <div class="text-h6">{{ averageTotalScore }}</div>
                  <div class="subtitle-2">Total Score</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
          <v-col cols="12" sm="3">
            <v-card class="pa-4" variant="flat">
              <v-row align="center">
                <v-icon color="orange lighten-1" size="48">mdi-percent</v-icon>
                <v-col>
                  <div class="text-h6">{{ averagePercentage }}%</div>
                  <div class="subtitle-2">Percentage</div>
                </v-col>
              </v-row>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
      <v-divider></v-divider>
      <v-row justify="end" class="text-right">
        <v-col cols="auto">
          <v-icon size="16" color="primary mx-1" @click.stop="drawer = !drawer" small>mdi-filter</v-icon>
          
            <!-- <v-btn v-if="permissions.includes('evaluate')" @click="addEvaluationDialog = true" icon> -->
              <v-btn  @click="addEvaluationDialog = true" icon>

            <v-tooltip activator="parent" location="top">Add Evaluation</v-tooltip>
            <v-icon color="primary">mdi-plus</v-icon>
            </v-btn>
        </v-col>
      </v-row>

      <!-- Data Table for Performance Evaluations -->
      <v-row no-gutters>
        <v-col>
          <v-responsive>
            <v-progress-linear v-if="loading" color="green" indeterminate></v-progress-linear>
            <v-data-table v-model="selected" :headers="headers" :items="evaluations" item-key="id" class="elevation-10"
              dense show-select :search="search">
              <template v-slot:top>
                <v-row>
                  <v-col cols="12">
                    <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" label="Search Evaluations" clearable
                      dense />
                  </v-col>
                </v-row>
              </template>
              <template v-slot:item.evaluation_date="{ item }">
                <span>{{ item.evaluation_date }}</span>
              </template>
              <template v-slot:item.user="{ item }">
                <span>{{ item.user.fullName }}</span>
              </template>
              <template v-slot:item.evaluator="{ item }">
                <span>{{ item.evaluator ? item.evaluator.fullName : 'N/A' }}</span>
              </template>
              <template v-slot:item.total_score="{ item }">
                <span>{{ item.total_score }}</span>
              </template>
              <template v-slot:item.percentage="{ item }">
                <span>{{ item.percentage }}%</span>
              </template>
              <template v-slot:item.actions="{ item }">
                <v-tooltip bottom>
                  <template v-slot:activator="{ on }">
                    <v-icon @click="viewEvaluation(item)" class="mx-1" title="View Evaluation" color="black" v-on="on">
                      mdi-information
                    </v-icon>
                    <v-icon @click="confirmDelete(item)" class="mx-1" title="Delete Evaluation" color="red" v-on="on">
                      mdi-delete
                    </v-icon>
                  </template>
                  <span>Actions</span>
                </v-tooltip>
              </template>
            </v-data-table>
          </v-responsive>
        </v-col>
      </v-row>

      <!-- Delete Confirmation Modal -->
      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title class="headline">Confirm Deletion</v-card-title>
          <v-card-text>Are you sure you want to delete this evaluation record?</v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn @click="deleteDialog = false" color="grey">Cancel</v-btn>
            <v-btn @click="deleteEvaluation" color="red">Delete</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Add Evaluation Modal -->
      <v-dialog v-model="addEvaluationDialog" width="800">
        <v-card>
          <v-card-title>Add Performance Evaluation</v-card-title>
          <v-divider></v-divider>
          <v-card-text>
            <v-form ref="evaluationForm">
              <v-row>




                <v-col cols="12" sm="6">
  <v-autocomplete
    v-model="newEvaluation.user_id"
    :items="team"
    item-title="fullname"
    item-value="id"
    label="Employee"
    clearable
    dense
  />
</v-col>

             
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.attendance" label="Attendance" dense></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.problems_solved" label="Problems Solved" dense></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.reports_submitted" label="Reports Submitted"
                    dense></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.knowledge_of_work" label="Knowledge of Work"
                    dense></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.team_work" label="Team Work" dense></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.reliability_visibility" label="Reliability & Visibility"
                    dense></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.productivity" label="Productivity" dense></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.discipline" label="Discipline" dense></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.quality_of_work" label="Quality of Work" dense></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.communication" label="Communication" dense></v-text-field>
                </v-col>

                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.leadership" label="Leadership" dense></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.total_score" label="Total Score" dense disabled></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="newEvaluation.percentage" label="Percentage" dense disabled></v-text-field>
                </v-col>

              
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions class="justify-content-end">
            <v-btn @click="addEvaluationDialog = false" color="error">
              <v-icon>mdi-cancel</v-icon> Cancel
            </v-btn>
            <v-btn @click="addEvaluation" color="primary">Submit</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- View Evaluation Modal -->
      <v-dialog v-model="viewEvaluationModal" max-width="600">
        <v-card>
          <v-card-title>
            <v-row class="justify-space-between align-center">
              <v-col cols="auto" class="d-flex align-center">
                <v-icon>mdi-star-circle</v-icon>
                <span class="ml-2">Evaluation Details</span>
              </v-col>
              <v-col cols="auto" class="d-flex justify-end">
                <v-btn icon @click="viewEvaluationModal = false">
                  <v-icon color="red">mdi-close</v-icon>
                </v-btn>
              </v-col>
            </v-row>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text>
            <v-timeline align="start" density="compact">
              <v-timeline-item dot-color="indigo" size="x-small">
                <div class="mb-4">
                  <div class="font-weight-normal">
                    <strong>Evaluation Date:</strong> {{ selectedEvaluation.evaluation_date }}
                  </div>
                </div>
              </v-timeline-item>
            </v-timeline>
            <v-timeline align="start" density="compact">
              <v-timeline-item dot-color="green" size="x-small">
                <div class="mb-4">
                  <div class="font-weight-normal">
                    <strong>Employee:</strong> {{ selectedEvaluation.employeeName }}
                  </div>
                </div>
              </v-timeline-item>
            </v-timeline>
            <v-timeline align="start" density="compact">
              <v-timeline-item dot-color="blue" size="x-small">
                <div class="mb-4">
                  <div class="font-weight-normal">
                    <strong>Evaluator:</strong> {{ selectedEvaluation.evaluatorName }}
                  </div>
                </div>
              </v-timeline-item>
            </v-timeline>
          </v-card-text>
        </v-card>
      </v-dialog>
    </v-main>
  </v-layout>
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
      headers: [
        // { title: 'Evaluation Date', value: 'evaluation_date' },

        { title: 'Employee', value: 'user.fullName' },
        // { title: 'Evaluator', value: 'evaluator.fullName' },
        // { title: 'Department', value: 'department.name' },
        { title: 'Attendance', value: 'attendance' },
        { title: 'Problems Solved', value: 'problems_solved' },
        { title: 'Reports Submitted', value: 'reports_submitted' },
        { title: 'Knowledge of Work', value: 'knowledge_of_work' },
        { title: 'Team Work', value: 'team_work' },
        { title: 'Reliability & Visibility', value: 'reliability_visibility' },
        { title: 'Productivity', value: 'productivity' },
        { title: 'Discipline', value: 'discipline' },
        { title: 'Quality of Work', value: 'quality_of_work' },
        { title: 'Communication', value: 'communication' },
        { title: 'Leadership', value: 'leadership' },
        { title: 'Total Score', value: 'total_score' },
        { title: 'Percentage', value: 'percentage' },
        // leadership
        { title: 'Evaluation Date', value: 'created_at' },

        { title: 'Actions', value: 'actions', sortable: false },
      ],

      drawer: false,
      selected: [],
      search: '',
      loading: false,
      evaluations: [],
      employees: [],
      evaluators: [],
      departments: [],
      team: [],
      user:'',
      averageTotalScore: 0,
      averagePercentage: 0,
      averageAttendance: 0,
      averageProductivity: 0,
      filters: {
        department_id: null,
        evaluation_date: null,
        user_id: null,
        evaluator_id: null,
      },
      newEvaluation: {
        user_id: null,
        evaluator_id: null,
        department_id: null,
        evaluation_date: null,
        attendance: 0,
        problems_solved: 0,
        reports_submitted: 0,
        knowledge_of_work: 0,
        team_work: 0,
        reliability_visibility: 0,
        productivity: 0,
        discipline: 0,
        quality_of_work: 0,
        communication: 0,
        total_score: 0,
        percentage: 0,
      },
      addEvaluationDialog: false,
      deleteDialog: false,
      viewEvaluationModal: false,
      selectedEvaluation: {
        evaluation_date: '',
        employeeName: '',
        evaluatorName: '',
        attendance: 0,
        problems_solved: 0,
        reports_submitted: 0,
        knowledge_of_work: 0,
        team_work: 0,
        reliability_visibility: 0,
        productivity: 0,
        discipline: 0,
        quality_of_work: 0,
        communication: 0,
        total_score: 0,
        percentage: 0,
      },
      selectedEvaluationId: null,
    };
  },
  created() {
    this.fetchEvaluations();
    this.fetchDepartments();
    this.fetchTeam();
    console.log("User:", this.user);
    console.log("Roles:", this.roles);
    console.log("Permissions:", this.permissions);
  },

  methods: {
    calculateScores() {
      const fields = [
        'attendance',
        'problems_solved',
        'reports_submitted',
        'knowledge_of_work',
        'team_work',
        'reliability_visibility',
        'productivity',
        'discipline',
        'quality_of_work',
        'communication'
      ];

      let total = 0;
      fields.forEach(field => {
        total += parseFloat(this.newEvaluation[field]) || 0;
      });

      this.newEvaluation.total_score = total;
      this.newEvaluation.percentage = (total / (fields.length * 10)) * 100; // Assuming each field is out of 10
    },

    addEvaluation() {
      this.calculateScores();
      axios.post('/api/v1/performance-evaluations', this.newEvaluation)
      .then(response => {
        this.fetchEvaluations();
        this.$toastr.success('Evaluation added successfully!');
        this.addEvaluationDialog = false;
        // Reset form
        this.newEvaluation = {
        user_id: this.user.id,
        evaluator_id: null,
        department_id: null,
        evaluation_date: null,
        attendance: 0,
        problems_solved: 0,
        reports_submitted: 0,
        knowledge_of_work: 0,
        team_work: 0,
        reliability_visibility: 0,
        productivity: 0,
        discipline: 0,
        quality_of_work: 0,
        communication: 0,
        total_score: 0,
        percentage: 0,
        };
        this.$refs.evaluationForm.reset();
      })
      .catch(error => {
        console.error('Error adding evaluation:', error);
        this.$toastr.error('Failed to add evaluation.');
      });
    },


    async fetchEvaluations() {
  this.loading = true;
  try {
    const response = await axios.get('/api/v1/performance-evaluations');
    const data = response.data; // Directly access the response data
    if (data.evaluations) {
      this.evaluations = data.evaluations.map(evaluation => ({
        ...evaluation,
        user: {
          ...evaluation.user,
          fullName: `${evaluation.user.firstname} ${evaluation.user.lastname}`
        },
        evaluator: evaluation.evaluator ? {
          ...evaluation.evaluator,
          fullName: `${evaluation.evaluator.firstname} ${evaluation.evaluator.lastname}`
        } : null,
      }));
      console.log("Evaluations:", this.evaluations);
      this.averageTotalScore = data.average_total_score || 0;
      this.averagePercentage = data.average_percentage || 0;
      this.averageAttendance = data.average_attendance || 0;
      this.averageProductivity = data.average_productivity || 0;
    } else {
      console.error('Evaluations data is undefined');
    }
  } catch (error) {
    console.error('Error fetching evaluations:', error);
  } finally {
    this.loading = false;
  }
},



fetchTeam() {
  const apiUrl = `api/v1/team`;

  axios.get(apiUrl)
    .then(response => {
      this.user = response.data.user;

      this.users = response.data.users.map(user => ({
        ...user,
        fullname: `${user.firstname} ${user.lastname}`,
      }));

      this.team = response.data.team.map(user => ({
        ...user,
        fullname: `${user.firstname} ${user.lastname}`,
      }));

      console.log("Team members based on role:", this.team);
    })
    .catch(error => {
      console.error('Error fetching team:', error);
    });
}
,

    async fetchDepartments() {
      try {
        const response = await axios.get('/api/v1/departments');
        this.departments = response.data.departments;
      } catch (error) {
        console.error('Error fetching departments:', error);
      }
    },
    filterEvaluations() {
      this.loading = true;
      const params = {
        department_id: this.filters.department_id,
        evaluation_date: this.filters.evaluation_date,
        user_id: this.filters.user_id,
        // evaluator_id: this.filters.evaluator_id,
      };
      axios.get('/api/v1/users', { params })
        .then(response => {
          this.drawer = false;
          this.evaluations = response.data.evaluations.map(evaluation => ({
            ...evaluation,
            user: {
              ...evaluation.user,
              fullName: `${evaluation.user.firstname} ${evaluation.user.lastname}`
            },
            evaluator: evaluation.evaluator ? {
              ...evaluation.evaluator,
              fullName: `${evaluation.evaluator.firstname} ${evaluation.evaluator.lastname}`
            } : null,
          }));
          this.loading = false;
        })
        .catch(error => {
          console.error('Error filtering evaluations:', error);
          this.loading = false;
        });
    },
    
    viewEvaluation(evaluation) {
      this.selectedEvaluation = {
        evaluation_date: evaluation.evaluation_date,
        employeeName: evaluation.user.fullName,
        evaluatorName: evaluation.evaluator ? evaluation.evaluator.fullName : 'N/A',
        attendance: evaluation.attendance,
        problems_solved: evaluation.problems_solved,
        reports_submitted: evaluation.reports_submitted,
        knowledge_of_work: evaluation.knowledge_of_work,
        team_work: evaluation.team_work,
        reliability_visibility: evaluation.reliability_visibility,
        productivity: evaluation.productivity,
        discipline: evaluation.discipline,
        quality_of_work: evaluation.quality_of_work,
        communication: evaluation.communication,
        total_score: evaluation.total_score,
        percentage: evaluation.percentage,
      };
      this.viewEvaluationModal = true;
    },
    confirmDelete(evaluation) {
      this.selectedEvaluationId = evaluation.id;
      this.selectedEvaluation = evaluation;
      this.deleteDialog = true;
    },
    deleteEvaluation() {
      if (!this.selectedEvaluationId) return;
      axios.delete(`/api/v1/performance-evaluations/${this.selectedEvaluationId}`)
        .then(() => {
          this.$toastr.success("Evaluation deleted successfully!");
          this.fetchEvaluations();
          this.deleteDialog = false;
          this.selectedEvaluationId = null;
        })
        .catch(error => {
          console.error("Error deleting evaluation:", error);
          this.$toastr.error("Failed to delete evaluation.");
        });
    },
  },
};
</script>
