```vue
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
              <v-list-item>
                <v-col cols="12">
                  <v-label>Unit:</v-label>
                  <v-select v-model="filters.unit_id" item-value="id" item-title="name" :items="units" multiple clearable dense></v-select>
                </v-col>
                <v-col cols="12">
                  <v-label>Department:</v-label>
                  <v-select v-model="filters.department_id" item-value="id" item-title="name" :items="departments" multiple clearable dense></v-select>
                </v-col>
              </v-list-item>
              <v-list-item>
                <v-col cols="12">
                  <v-label>Evaluation Date:</v-label>
                  <v-row>
                    <v-col cols="6">
                      <v-text-field v-model="filters.evaluation_date_start" label="Start Date" type="date" dense></v-text-field>
                    </v-col>
                    <v-col cols="6">
                      <v-text-field v-model="filters.evaluation_date_end" label="End Date" type="date" dense></v-text-field>
                    </v-col>
                  </v-row>
                </v-col>
              </v-list-item>
              <v-list-item>
                <v-col cols="12">
                  <v-label>Employee:</v-label>
                  <v-combobox :items="users" item-title="fullName" item-value="id" v-model="filters.user_id" label="Select Employee" variant="outlined" clearable></v-combobox>
                </v-col>
              </v-list-item>
              <v-list-item>
                <v-col cols="12">
                  <v-label>Evaluator:</v-label>
                  <v-combobox :items="users" item-title="fullName" item-value="id" v-model="filters.evaluator_id" label="Select Evaluator" variant="outlined" clearable></v-combobox>
                </v-col>
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>
        <v-row align="center" justify="center" class="drawer-footer">
          <v-col cols="12">
            <v-btn @click.prevent="filterEvaluations" color="primary">
              <v-icon>mdi-filter</v-icon> Apply Filters
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main>
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
        </v-row>
        <v-row>
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
        </v-row>
      </v-col>

      <v-divider></v-divider>

      <v-row align="center" justify="space-between" class="mb-4">
        <v-col cols="auto" class="d-flex align-center">
          <v-icon size="20" color="primary" class="mx-2" @click.stop="drawer = !drawer">
            mdi-filter
          </v-icon>
          <v-btn @click="addEvaluationDialog = true" icon>
            <v-tooltip activator="parent" location="top">Add Evaluation</v-tooltip>
            <v-icon color="primary">mdi-plus</v-icon>
          </v-btn>
        </v-col>
        <v-col cols="auto">
          <div class="d-flex">
            <v-btn color="primary" class="mr-2" @click="downloadRankingReport">
              <v-icon left>mdi-podium</v-icon>
              Download Employee Rankings
            </v-btn>
            <v-btn color="primary" @click="downloadFullReport">
              <v-icon left>mdi-download</v-icon>
              Download Reports
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <v-row no-gutters>
        <v-col>
          <v-responsive>
            <v-progress-linear v-if="loading" color="green" indeterminate></v-progress-linear>
            <v-data-table v-model="selected" :headers="headers" :items="evaluations" item-key="id" class="elevation-10" dense show-select :search="search">
              <template v-slot:top>
                <v-row>
                  <v-col cols="12">
                    <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" label="Search Evaluations" clearable dense />
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
                <v-icon @click="viewEvaluation(item)" class="mx-1" title="View Evaluation" color="black">
                  mdi-information
                </v-icon>
                <v-icon color="primary" @click="openEditDialog(item)" title="Edit Evaluation">
                  mdi-pencil
                </v-icon>
                <v-icon @click="confirmDelete(item)" class="mx-1" title="Delete Evaluation" color="red">
                  mdi-delete
                </v-icon>
              </template>
            </v-data-table>
          </v-responsive>
        </v-col>
      </v-row>

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

      <v-dialog v-model="editDialog" max-width="800">
        <v-card>
          <v-card-title>Edit Performance Evaluation</v-card-title>
          <v-divider></v-divider>
          <v-card-text>
            <v-form ref="editEvaluationForm">
              <v-row>
                <v-col cols="12">
                  <v-autocomplete
                    v-model="editEvaluation.unit_id"
                    :items="units"
                    label="Unit"
                    item-title="name"
                    item-value="id"
                    variant="outlined"
                    clearable
                    :disabled="!isEditing"
                    :rules="[v => !!v || 'Unit is required']"
                    @update:modelValue="fetchDepartmentsAndEmployees"
                  />
                </v-col>
                <v-col cols="12">
                  <v-autocomplete
                    v-model="editEvaluation.department_id"
                    :items="departments"
                    label="Department"
                    item-title="name"
                    item-value="id"
                    variant="outlined"
                    clearable
                    :disabled="!isEditing"
                    :rules="[v => !!v || 'Department is required']"
                    @update:modelValue="fetchEmployees"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-autocomplete
                    v-model="editEvaluation.user_id"
                    :items="team"
                    label="Employee"
                    item-title="fullname"
                    item-value="id"
                    variant="outlined"
                    clearable
                    dense
                    :disabled="!isEditing"
                    :rules="[v => !!v || 'Employee is required']"
                    @update:modelValue="updateEmployee"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editEvaluation.attendance"
                    label="Attendance"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    :readonly="!isEditing"
                    @input="calculateEditScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editEvaluation.problems_solved"
                    label="Problems Solved"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    :readonly="!isEditing"
                    @input="calculateEditScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6" v-if="shouldShowReportsSubmitted">
                  <v-text-field
                    v-model.number="editEvaluation.reports_submitted"
                    label="Reports Submitted"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    :readonly="!isEditing"
                    @input="calculateEditScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editEvaluation.knowledge_of_work"
                    label="Knowledge of Work"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    :readonly="!isEditing"
                    @input="calculateEditScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editEvaluation.team_work"
                    label="Team Work"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    :readonly="!isEditing"
                    @input="calculateEditScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editEvaluation.reliability_visibility"
                    label="Reliability & Visibility"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    :readonly="!isEditing"
                    @input="calculateEditScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editEvaluation.productivity"
                    label="Productivity"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    :readonly="!isEditing"
                    @input="calculateEditScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editEvaluation.discipline"
                    label="Discipline"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    :readonly="!isEditing"
                    @input="calculateEditScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editEvaluation.quality_of_work"
                    label="Quality of Work"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    :readonly="!isEditing"
                    @input="calculateEditScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editEvaluation.communication"
                    label="Communication"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    :readonly="!isEditing"
                    @input="calculateEditScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6" v-if="shouldShowLeadership">
                  <v-text-field
                    v-model.number="editEvaluation.leadership"
                    label="Leadership"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    :readonly="!isEditing"
                    @input="calculateEditScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editEvaluation.total_score"
                    label="Total Score"
                    type="number"
                    dense
                    disabled
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editEvaluation.percentage"
                    label="Percentage"
                    type="number"
                    dense
                    disabled
                  />
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn v-if="!isEditing" color="primary" @click="isEditing = true">Edit</v-btn>
            <v-btn v-if="isEditing" color="success" @click="saveEditedEvaluation">Save</v-btn>
            <v-btn @click="cancelEdit">Cancel</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <v-dialog v-model="addEvaluationDialog" width="800">
        <v-card>
          <v-card-title>Add Performance Evaluation</v-card-title>
          <v-divider></v-divider>
          <v-card-text>
            <v-form ref="evaluationForm" @submit.prevent="addEvaluation">
              <v-row>
                <v-col cols="12">
                  <v-autocomplete
                    v-model="newEvaluation.unit_id"
                    :items="units"
                    label="Unit"
                    variant="outlined"
                    item-title="name"
                    item-value="id"
                    clearable
                    :rules="[v => !!v || 'Unit is required']"
                    @update:modelValue="fetchDepartmentsAndEmployees"
                  />
                </v-col>
                <v-col cols="12">
                  <v-autocomplete
                    v-model="newEvaluation.department_id"
                    :items="departments"
                    label="Department"
                    variant="outlined"
                    item-title="name"
                    item-value="id"
                    clearable
                    :rules="[v => !!v || 'Department is required']"
                    @update:modelValue="fetchEmployees"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-autocomplete
                    v-model="newEvaluation.user_id"
                    :items="team"
                    item-title="fullname"
                    item-value="id"
                    label="Employee"
                    clearable
                    dense
                    :rules="[v => !!v || 'Employee is required']"
                    @update:modelValue="updateNewEmployee"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="newEvaluation.attendance"
                    label="Attendance"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    @input="calculateScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="newEvaluation.problems_solved"
                    label="Problems Solved"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    @input="calculateScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6" v-if="shouldShowReportsSubmitted">
                  <v-text-field
                    v-model.number="newEvaluation.reports_submitted"
                    label="Reports Submitted"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    @input="calculateScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="newEvaluation.knowledge_of_work"
                    label="Knowledge of Work"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    @input="calculateScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="newEvaluation.team_work"
                    label="Team Work"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    @input="calculateScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="newEvaluation.reliability_visibility"
                    label="Reliability & Visibility"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    @input="calculateScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="newEvaluation.productivity"
                    label="Productivity"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    @input="calculateScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="newEvaluation.discipline"
                    label="Discipline"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    @input="calculateScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="newEvaluation.quality_of_work"
                    label="Quality of Work"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    @input="calculateScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="newEvaluation.communication"
                    label="Communication"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    @input="calculateScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6" v-if="shouldShowLeadership">
                  <v-text-field
                    v-model.number="newEvaluation.leadership"
                    label="Leadership"
                    type="number"
                    dense
                    :rules="[v => (v !== null && v >= 0 && v <= 10) || 'Must be between 0 and 10']"
                    @input="calculateScores"
                    required
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="newEvaluation.total_score"
                    label="Total Score"
                    type="number"
                    dense
                    disabled
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="newEvaluation.percentage"
                    label="Percentage"
                    type="number"
                    dense
                    disabled
                  />
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions class="justify-content-end">
            <v-btn @click="addEvaluationDialog = false" color="error">
              <v-icon>mdi-cancel</v-icon> Cancel
            </v-btn>
            <v-btn type="submit" color="primary" @click="addEvaluation">Submit</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

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
              <v-timeline-item dot-color="green" size="x-small">
                <div class="mb-4">
                  <div class="font-weight-normal">
                    <strong>Employee:</strong> {{ selectedEvaluation.employeeName }}
                  </div>
                </div>
              </v-timeline-item>
              <v-timeline-item dot-color="blue" size="x-small">
                <div class="mb-4">
                  <div class="font-weight-normal">
                    <strong>Evaluator:</strong> {{ selectedEvaluation.evaluatorName }}
                  </div>
                </div>
              </v-timeline-item>
              <v-timeline-item dot-color="purple" size="x-small">
                <div class="mb-4">
                  <div class="font-weight-normal">
                    <strong>Total Score:</strong> {{ selectedEvaluation.total_score }}
                  </div>
                </div>
              </v-timeline-item>
              <v-timeline-item dot-color="orange" size="x-small">
                <div class="mb-4">
                  <div class="font-weight-normal">
                    <strong>Percentage:</strong> {{ selectedEvaluation.percentage }}%
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
        { title: 'Employee', value: 'user.fullName' },
        { title: 'Attendance', value: 'attendance' },
        { title: 'Problems Solved', value: 'problems_solved' },
        { title: 'Knowledge of Work', value: 'knowledge_of_work' },
        { title: 'Team Work', value: 'team_work' },
        { title: 'Reliability & Visibility', value: 'reliability_visibility' },
        { title: 'Productivity', value: 'productivity' },
        { title: 'Discipline', value: 'discipline' },
        { title: 'Quality of Work', value: 'quality_of_work' },
        { title: 'Communication', value: 'communication' },
        { title: 'Leadership', value: 'leadership' },
        { title: 'Reports Submitted', value: 'reports_submitted' },
        { title: 'Total Score', value: 'total_score' },
        { title: 'Percentage', value: 'percentage' },
        { title: 'Evaluation Date', value: 'created_at' },
        { title: 'Actions', value: 'actions', sortable: false },
      ],
      rankedEmployees: [],
      loadingRankings: false,
      rankingSearch: '',
      rankingHeaders: [
        { text: 'Rank', value: 'rank', align: 'center', width: '70px' },
        { text: 'Employee', value: 'full_name' },
        { text: 'Department', value: 'department' },
        { text: 'Average Score', value: 'avg_score', align: 'center' },
        { text: 'Average Percentage', value: 'avg_percentage', align: 'center' },
        { text: 'Attendance', value: 'avg_attendance', align: 'center' },
        { text: 'Problems Solved', value: 'avg_problems_solved', align: 'center' },
        { text: 'Team Work', value: 'avg_team_work', align: 'center' },
        { text: 'Productivity', value: 'avg_productivity', align: 'center' },
      ],
      drawer: false,
      selected: [],
      search: '',
      loading: false,
      evaluations: [],
      employees: [],
      evaluators: [],
      departments: [],
      units: [],
      team: [],
      users: [],
      averageTotalScore: 0,
      averagePercentage: 0,
      averageAttendance: 0,
      averageProductivity: 0,
      averageProblemsSolved: 0,
      averageReportsSubmitted: 0,
      averageKnowledgeOfWork: 0,
      averageTeamWork: 0,
      averageReliabilityVisibility: 0,
      averageDiscipline: 0,
      averageQualityOfWork: 0,
      averageCommunication: 0,
      filters: {
        unit_id: null,
        department_id: null,
        evaluation_date_end: null,
        evaluation_date_start: null,
        user_id: null,
        evaluator_id: null,
      },
      newEvaluation: {
        id: null,
        user_id: null,
        evaluator_id: null,
        department_id: null,
        unit_id: null,
        evaluation_date: new Date().toISOString().substr(0, 10),
        attendance: null,
        problems_solved: null,
        reports_submitted: null,
        knowledge_of_work: null,
        team_work: null,
        reliability_visibility: null,
        productivity: null,
        discipline: null,
        quality_of_work: null,
        communication: null,
        total_score: null,
        percentage: null,
        leadership: null
      },
      editDialog: false,
      isEditing: false,
      editEvaluation: {
        id: null,
        user_id: null,
        evaluator_id: null,
        department_id: null,
        unit_id: null,
        evaluation_date: null,
        attendance: null,
        problems_solved: null,
        reports_submitted: null,
        knowledge_of_work: null,
        team_work: null,
        reliability_visibility: null,
        productivity: null,
        discipline: null,
        quality_of_work: null,
        communication: null,
        total_score: null,
        percentage: null,
        leadership: null
      },
      addEvaluationDialog: false,
      deleteDialog: false,
      viewEvaluationModal: false,
      selectedEvaluation: {
        evaluation_date: '',
        employeeName: '',
        evaluatorName: '',
        attendance: null,
        problems_solved: null,
        reports_submitted: null,
        knowledge_of_work: null,
        team_work: null,
        reliability_visibility: null,
        productivity: null,
        discipline: null,
        quality_of_work: null,
        communication: null,
        total_score: null,
        percentage: null,
        leadership: null
      },
      selectedEvaluationId: null,
    };
  },
  computed: {
    shouldShowReportsSubmitted() {
      if (!this.newEvaluation.user_id && !this.editEvaluation.user_id) return false;
      const newUser = this.team.find(user => user.id === (this.newEvaluation.user_id?.id || this.newEvaluation.user_id));
      const editUser = this.team.find(user => user.id === (this.editEvaluation.user_id?.id || this.editEvaluation.user_id));
      return (newUser && newUser.designation_id !== 1) || (editUser && editUser.designation_id !== 1);
    },
    shouldShowLeadership() {
      if (!this.newEvaluation.user_id && !this.editEvaluation.user_id) return false;
      const newUser = this.team.find(user => user.id === (this.newEvaluation.user_id?.id || this.newEvaluation.user_id));
      const editUser = this.team.find(user => user.id === (this.editEvaluation.user_id?.id || this.editEvaluation.user_id));
      return (newUser && newUser.designation_id === 1) || (editUser && editUser.designation_id === 1);
    },
  },
  watch: {
    'newEvaluation.user_id'(newVal) {
      if (!this.shouldShowReportsSubmitted) {
        this.newEvaluation.reports_submitted = null;
      }
      if (!this.shouldShowLeadership) {
        this.newEvaluation.leadership = null;
      }
      this.calculateScores();
    },
    'newEvaluation.unit_id'(newUnit) {
      this.newEvaluation.department_id = null;
      this.newEvaluation.user_id = null;
      this.fetchDepartmentsAndEmployees();
    },
    'newEvaluation.department_id'(newDept) {
      this.newEvaluation.user_id = null;
      this.fetchEmployees();
    },
    'editEvaluation.user_id'(newVal) {
      if (!this.shouldShowReportsSubmitted) {
        this.editEvaluation.reports_submitted = null;
      }
      if (!this.shouldShowLeadership) {
        this.editEvaluation.leadership = null;
      }
      this.calculateEditScores();
    },
    'editEvaluation.unit_id'(newUnit) {
      this.editEvaluation.department_id = null;
      this.editEvaluation.user_id = null;
      this.fetchDepartmentsAndEmployees();
    },
    'editEvaluation.department_id'(newDept) {
      this.editEvaluation.user_id = null;
      this.fetchEmployees();
    },
  },
  created() {
    this.fetchEvaluations();
    this.fetchDepartments();
    this.fetchUnits();
    this.fetchUsers();
  },
  methods: {
    fetchUsers() {
      axios.get('/api/v1/users')
        .then(response => {
          if (response.data && response.data.users) {
            this.users = response.data.users.map(user => ({
              id: user.id,
              fullName: `${user.firstname} ${user.lastname}`,
              designation_id: user.designation_id || null,
            }));
          } else {
            this.users = [];
          }
        })
        .catch(error => {
          console.error('Error fetching users:', error);
          this.users = [];
        });
    },
    downloadRankingReport() {
      this.loadingRankings = true;
      axios.post('/api/v1/performance-reports/ranked-employees', {
        evaluations: this.evaluations
      }, {
        responseType: 'blob'
      })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', 'ranked_employees.xlsx');
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        })
        .catch(error => {
          console.error('Error downloading ranked report:', error);
          this.$toastr.error('Failed to download ranked report.');
        })
        .finally(() => {
          this.loadingRankings = false;
        });
    },
    downloadFullReport() {
      this.loading = true;
      axios.post('/api/v1/performance-reports/export', {
        evaluations: this.evaluations
      }, {
        responseType: 'blob'
      })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', 'full_performance_report.xlsx');
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        })
        .catch(error => {
          console.error('Error downloading full report:', error);
          this.$toastr.error('Failed to download full report.');
        })
        .finally(() => {
          this.loading = false;
        });
    },
    getRankColor(rank) {
      if (rank === 1) return 'gold';
      if (rank === 2) return 'silver';
      if (rank === 3) return '#CD7F32';
      return 'grey';
    },
    calculateScores() {
      const baseFields = [
        'attendance', 'problems_solved', 'knowledge_of_work',
        'team_work', 'reliability_visibility', 'productivity',
        'discipline', 'quality_of_work', 'communication'
      ];
      const dynamicFields = [];
      if (this.shouldShowReportsSubmitted && this.newEvaluation.reports_submitted !== null) {
        dynamicFields.push('reports_submitted');
      }
      if (this.shouldShowLeadership && this.newEvaluation.leadership !== null) {
        dynamicFields.push('leadership');
      }
      const allFields = [...baseFields, ...dynamicFields];
      let total = 0;
      let validFields = 0;

      allFields.forEach(field => {
        const value = parseFloat(this.newEvaluation[field]) || 0;
        if (value >= 0 && value <= 10) {
          total += value;
          validFields++;
        }
      });

      this.newEvaluation.total_score = total;
      const maxPossibleScore = validFields * 10;
      this.newEvaluation.percentage = maxPossibleScore > 0 ? Math.round((total / maxPossibleScore) * 100) : 0;
    },
    calculateEditScores() {
      const baseFields = [
        'attendance', 'problems_solved', 'knowledge_of_work',
        'team_work', 'reliability_visibility', 'productivity',
        'discipline', 'quality_of_work', 'communication'
      ];
      const dynamicFields = [];
      if (this.shouldShowReportsSubmitted && this.editEvaluation.reports_submitted !== null) {
        dynamicFields.push('reports_submitted');
      }
      if (this.shouldShowLeadership && this.editEvaluation.leadership !== null) {
        dynamicFields.push('leadership');
      }
      const allFields = [...baseFields, ...dynamicFields];
      let total = 0;
      let validFields = 0;

      allFields.forEach(field => {
        const value = parseFloat(this.editEvaluation[field]) || 0;
        if (value >= 0 && value <= 10) {
          total += value;
          validFields++;
        }
      });

      this.editEvaluation.total_score = total;
      const maxPossibleScore = validFields * 10;
      this.editEvaluation.percentage = maxPossibleScore > 0 ? Math.round((total / maxPossibleScore) * 100) : 0;
    },
    openEditDialog(evaluation) {
      this.editEvaluation = { ...evaluation };
      if (evaluation.user && evaluation.user.id) {
        this.editEvaluation.user_id = {
          id: evaluation.user.id,
          fullname: evaluation.user.fullName,
          designation_id: evaluation.user.designation_id
        };
      }
      this.editEvaluation.unit_id = evaluation.unit_id;
      this.editEvaluation.department_id = evaluation.department_id;
      if (evaluation.user.designation_id === 1) {
        this.editEvaluation.reports_submitted = null;
      }
      this.fetchDepartmentsAndEmployees();
      this.editDialog = true;
      this.isEditing = false;
    },
    saveEditedEvaluation() {
      if (!this.$refs.editEvaluationForm.validate()) {
        this.$toastr.error('Please fill in all required fields correctly.');
        return;
      }
      if (!this.validateEvaluation(this.editEvaluation)) {
        return;
      }
      this.calculateEditScores();
      const updatedEvaluation = { ...this.editEvaluation };
      if (updatedEvaluation.user_id && typeof updatedEvaluation.user_id === 'object') {
        updatedEvaluation.user_id = updatedEvaluation.user_id.id;
      }
      const isManager = this.team.find(user => user.id === updatedEvaluation.user_id)?.designation_id === 1;
      const payload = {
        user_id: updatedEvaluation.user_id,
        unit_id: updatedEvaluation.unit_id,
        department_id: updatedEvaluation.department_id,
        attendance: updatedEvaluation.attendance,
        problems_solved: updatedEvaluation.problems_solved,
        knowledge_of_work: updatedEvaluation.knowledge_of_work,
        team_work: updatedEvaluation.team_work,
        reliability_visibility: updatedEvaluation.reliability_visibility,
        productivity: updatedEvaluation.productivity,
        discipline: updatedEvaluation.discipline,
        quality_of_work: updatedEvaluation.quality_of_work,
        communication: updatedEvaluation.communication,
        total_score: updatedEvaluation.total_score,
        percentage: updatedEvaluation.percentage,
        reports_submitted: isManager ? null : (this.shouldShowReportsSubmitted ? updatedEvaluation.reports_submitted : null),
        leadership: this.shouldShowLeadership ? updatedEvaluation.leadership : null,
        evaluation_date: updatedEvaluation.evaluation_date || new Date().toISOString().substr(0, 10),
        evaluator_id: this.user?.id || null
      };

      axios.put(`/api/v1/performance-evaluations/${updatedEvaluation.id}`, payload)
        .then(() => {
          this.$toastr.success('Evaluation updated successfully!');
          this.fetchEvaluations();
          this.editDialog = false;
          this.isEditing = false;
          this.resetEditForm();
        })
        .catch(error => {
          console.error('Error updating evaluation:', error.response ? error.response.data : error);
          this.$toastr.error(error.response?.data?.message || 'Failed to update evaluation.');
        });
    },
    cancelEdit() {
      this.editDialog = false;
      this.isEditing = false;
      this.resetEditForm();
    },
    resetEditForm() {
      this.editEvaluation = {
        id: null,
        user_id: null,
        evaluator_id: null,
        department_id: null,
        unit_id: null,
        evaluation_date: null,
        attendance: null,
        problems_solved: null,
        reports_submitted: null,
        knowledge_of_work: null,
        team_work: null,
        reliability_visibility: null,
        productivity: null,
        discipline: null,
        quality_of_work: null,
        communication: null,
        total_score: null,
        percentage: null,
        leadership: null
      };
    },
    addEvaluation() {
      if (!this.$refs.evaluationForm.validate()) {
        this.$toastr.error('Please fill in all required fields correctly.');
        return;
      }
      if (!this.validateEvaluation(this.newEvaluation)) {
        return;
      }
      this.calculateScores();
      const newEval = { ...this.newEvaluation };
      if (newEval.user_id && typeof newEval.user_id === 'object' && newEval.user_id.id) {
        newEval.user_id = newEval.user_id.id;
      } else if (!newEval.user_id) {
        this.$toastr.error('Please select an employee.');
        return;
      }
      const isManager = this.team.find(user => user.id === newEval.user_id)?.designation_id === 1;
      const payload = {
        user_id: newEval.user_id,
        unit_id: newEval.unit_id,
        department_id: newEval.department_id,
        attendance: newEval.attendance,
        problems_solved: newEval.problems_solved,
        knowledge_of_work: newEval.knowledge_of_work,
        team_work: newEval.team_work,
        reliability_visibility: newEval.reliability_visibility,
        productivity: newEval.productivity,
        discipline: newEval.discipline,
        quality_of_work: newEval.quality_of_work,
        communication: newEval.communication,
        total_score: newEval.total_score,
        percentage: newEval.percentage,
        reports_submitted: isManager ? null : (this.shouldShowReportsSubmitted ? newEval.reports_submitted : null),
        leadership: this.shouldShowLeadership ? newEval.leadership : null,
        evaluation_date: newEval.evaluation_date || new Date().toISOString().substr(0, 10),
        evaluator_id: this.user?.id || null
      };
      axios.post('/api/v1/performance-evaluations', payload)
        .then(response => {
          this.fetchEvaluations();
          this.$toastr.success('Evaluation added successfully!');
          this.addEvaluationDialog = false;
          this.newEvaluation = {
            id: null,
            user_id: null,
            evaluator_id: null,
            department_id: null,
            unit_id: null,
            evaluation_date: new Date().toISOString().substr(0, 10),
            attendance: null,
            problems_solved: null,
            reports_submitted: null,
            knowledge_of_work: null,
            team_work: null,
            reliability_visibility: null,
            productivity: null,
            discipline: null,
            quality_of_work: null,
            communication: null,
            total_score: null,
            percentage: null,
            leadership: null
          };
          this.$refs.evaluationForm.reset();
        })
        .catch(error => {
          console.error('Error adding evaluation:', error.response ? error.response.data : error);
          this.$toastr.error(error.response?.data?.message || 'Failed to add evaluation.');
        });
    },
    async fetchEvaluations() {
      this.loading = true;
      try {
        const response = await axios.get('/api/v1/performance-evaluations');
        const data = response.data;
        if (data.evaluations) {
          this.evaluations = data.evaluations.map(evaluation => ({
            ...evaluation,
            user: evaluation.user ? {
              ...evaluation.user,
              fullName: `${evaluation.user.firstname} ${evaluation.user.lastname}`
            } : { fullName: 'N/A' },
            evaluator: evaluation.evaluator ? {
              ...evaluation.evaluator,
              fullName: `${evaluation.evaluator.firstname} ${evaluation.evaluator.lastname}`
            } : { fullName: 'N/A' },
          }));
          this.calculateAverages();
        } else {
          this.evaluations = [];
          this.resetAverages();
        }
      } catch (error) {
        console.error('Error fetching evaluations:', error);
        this.evaluations = [];
        this.resetAverages();
      } finally {
        this.loading = false;
      }
    },
    async fetchEmployees() {
      this.team = [];
      const { unit_id, department_id } = this.newEvaluation;
      const editUnitId = this.editEvaluation.unit_id;
      const editDeptId = this.editEvaluation.department_id;
      const activeUnitId = editUnitId || unit_id;
      const activeDeptId = editDeptId || department_id;
      if (!activeUnitId || !activeDeptId) {
        this.$toastr.warning('Please select both a unit and a department to load employees.');
        return;
      }
      try {
        const { data } = await axios.get('/api/v1/team', {
          params: { unit_id: activeUnitId, department_id: activeDeptId }
        });
        if (data.team && Array.isArray(data.team)) {
          this.team = data.team.map(u => ({
            id: u.id,
            fullname: `${u.firstname} ${u.lastname}`,
            designation_id: u.designation_id
          }));
          if (this.editEvaluation.user_id && typeof this.editEvaluation.user_id === 'object') {
            const matchingUser = this.team.find(t => t.id === this.editEvaluation.user_id.id);
            if (matchingUser) {
              this.editEvaluation.user_id = matchingUser;
            } else {
              this.editEvaluation.user_id = null;
            }
          }
        } else {
          this.team = [];
          this.$toastr.warning('No employees found for the selected unit and department.');
        }
      } catch (err) {
        console.error('Error fetching team:', err);
        this.team = [];
        this.$toastr.error('Failed to fetch employees.');
      }
    },
    fetchDepartmentsAndEmployees() {
      this.departments = [];
      if (this.editEvaluation.unit_id || this.newEvaluation.unit_id) {
        this.fetchDepartments();
        this.fetchEmployees();
      }
    },
    async fetchDepartments() {
      if (!this.newEvaluation.unit_id && !this.editEvaluation.unit_id) {
        this.departments = [];
        return;
      }
      const unitId = this.newEvaluation.unit_id || this.editEvaluation.unit_id;
      try {
        const { data } = await axios.get('/api/v1/departments', {
          params: { unit_id: unitId }
        });
        this.departments = data.departments || [];
      } catch (error) {
        console.error('Error fetching departments:', error);
        this.departments = [];
      }
    },
    fetchUnits() {
      return axios.get('/api/v1/branches')
        .then(response => {
          if (response.data && response.data.branches && Array.isArray(response.data.branches)) {
            this.units = response.data.branches;
          } else {
            this.units = [];
          }
          return this.units;
        })
        .catch(error => {
          console.error('Failed to fetch units', error);
          this.units = [];
          return this.units;
        });
    },
    filterEvaluations() {
      this.loading = true;
      const params = {
        unit_id: this.filters.unit_id,
        department_id: this.filters.department_id,
        start_date: this.filters.evaluation_date_start,
        end_date: this.filters.evaluation_date_end,
        user_id: this.filters.user_id,
        evaluator_id: this.filters.evaluator_id,
      };
      axios.get('/api/v1/performance-evaluations/filter', { params })
        .then(response => {
          this.drawer = false;
          if (response.data.evaluations) {
            this.evaluations = response.data.evaluations.map(evaluation => ({
              ...evaluation,
              user: evaluation.user ? {
                ...evaluation.user,
                fullName: `${evaluation.user.firstname} ${evaluation.user.lastname}`
              } : { fullName: 'N/A' },
              evaluator: evaluation.evaluator ? {
                ...evaluation.evaluator,
                fullName: `${evaluation.evaluator.firstname} ${evaluation.evaluator.lastname}`
              } : { fullName: 'N/A' },
            }));
            this.calculateAverages();
          } else {
            this.evaluations = [];
            this.resetAverages();
          }
          this.loading = false;
        })
        .catch(error => {
          console.error('Error filtering evaluations:', error);
          this.$toastr.error('Error filtering evaluations.');
          this.loading = false;
        });
    },
    calculateAverages() {
      const evals = this.evaluations;
      if (!evals.length) {
        this.resetAverages();
        return;
      }
      const totals = {
        total_score: 0,
        percentage: 0,
        attendance: 0,
        productivity: 0,
        problems_solved: 0,
        reports_submitted: 0,
        knowledge_of_work: 0,
        team_work: 0,
        reliability_visibility: 0,
        discipline: 0,
        quality_of_work: 0,
        communication: 0
      };
      let reportsSubmittedCount = 0;
      evals.forEach(evaluation => {
        totals.total_score += parseFloat(evaluation.total_score) || 0;
        totals.percentage += parseFloat(evaluation.percentage) || 0;
        totals.attendance += parseFloat(evaluation.attendance) || 0;
        totals.productivity += parseFloat(evaluation.productivity) || 0;
        totals.problems_solved += parseFloat(evaluation.problems_solved) || 0;
        if (evaluation.reports_submitted !== null && evaluation.reports_submitted !== undefined) {
          totals.reports_submitted += parseFloat(evaluation.reports_submitted) || 0;
          reportsSubmittedCount++;
        }
        totals.knowledge_of_work += parseFloat(evaluation.knowledge_of_work) || 0;
        totals.team_work += parseFloat(evaluation.team_work) || 0;
        totals.reliability_visibility += parseFloat(evaluation.reliability_visibility) || 0;
        totals.discipline += parseFloat(evaluation.discipline) || 0;
        totals.quality_of_work += parseFloat(evaluation.quality_of_work) || 0;
        totals.communication += parseFloat(evaluation.communication) || 0;
      });
      const count = evals.length;
      this.averageTotalScore = count > 0 ? Math.round(totals.total_score / count * 10) / 10 : 0;
      this.averagePercentage = count > 0 ? Math.round(totals.percentage / count * 10) / 10 : 0;
      this.averageAttendance = count > 0 ? Math.round(totals.attendance / count * 10) / 10 : 0;
      this.averageProductivity = count > 0 ? Math.round(totals.productivity / count * 10) / 10 : 0;
      this.averageProblemsSolved = count > 0 ? Math.round(totals.problems_solved / count * 10) / 10 : 0;
      this.averageReportsSubmitted = reportsSubmittedCount > 0 ? Math.round(totals.reports_submitted / reportsSubmittedCount * 10) / 10 : 0;
      this.averageKnowledgeOfWork = count > 0 ? Math.round(totals.knowledge_of_work / count * 10) / 10 : 0;
      this.averageTeamWork = count > 0 ? Math.round(totals.team_work / count * 10) / 10 : 0;
      this.averageReliabilityVisibility = count > 0 ? Math.round(totals.reliability_visibility / count * 10) / 10 : 0;
      this.averageDiscipline = count > 0 ? Math.round(totals.discipline / count * 10) / 10 : 0;
      this.averageQualityOfWork = count > 0 ? Math.round(totals.quality_of_work / count * 10) / 10 : 0;
      this.averageCommunication = count > 0 ? Math.round(totals.communication / count * 10) / 10 : 0;
    },
    resetAverages() {
      this.averageTotalScore = 0;
      this.averagePercentage = 0;
      this.averageAttendance = 0;
      this.averageProductivity = 0;
      this.averageProblemsSolved = 0;
      this.averageReportsSubmitted = 0;
      this.averageKnowledgeOfWork = 0;
      this.averageTeamWork = 0;
      this.averageReliabilityVisibility = 0;
      this.averageDiscipline = 0;
      this.averageQualityOfWork = 0;
      this.averageCommunication = 0;
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
        leadership: evaluation.leadership,
        total_score: evaluation.total_score,
        percentage: evaluation.percentage,
      };
      this.viewEvaluationModal = true;
    },
    confirmDelete(evaluation) {
      this.selectedEvaluationId = evaluation.id;
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
          console.error("Error deleting evaluation:", error.response ? error.response.data : error);
          this.$toastr.error("Failed to delete evaluation.");
        });
    },
    validateEvaluation(evaluation) {
      const requiredFields = [
        'user_id', 'unit_id', 'department_id',
        'attendance', 'problems_solved', 'knowledge_of_work',
        'team_work', 'reliability_visibility', 'productivity',
        'discipline', 'quality_of_work', 'communication'
      ];
      for (const field of requiredFields) {
        const value = field === 'user_id' || field === 'unit_id' || field === 'department_id'
          ? evaluation[field]
          : parseFloat(evaluation[field]);
        if (value === null || value === '' || value === undefined) {
          this.$toastr.error(`Please fill in ${field.replace('_', ' ')}`);
          return false;
        }
        if (['attendance', 'problems_solved', 'knowledge_of_work', 'team_work',
             'reliability_visibility', 'productivity', 'discipline',
             'quality_of_work', 'communication'].includes(field)) {
          if (isNaN(value) || value < 0 || value > 10) {
            this.$toastr.error(`${field.replace('_', ' ')} must be between 0 and 10`);
            return false;
          }
        }
      }
      const isManager = this.team.find(user => user.id === (evaluation.user_id?.id || evaluation.user_id))?.designation_id === 1;
      if (isManager && evaluation.reports_submitted !== null) {
        this.$toastr.error('Reports submitted should not be provided for Managers');
        return false;
      }
      if (this.shouldShowLeadership && evaluation.leadership !== null) {
        const leadershipValue = parseFloat(evaluation.leadership);
        if (isNaN(leadershipValue) || leadershipValue < 0 || leadershipValue > 10) {
          this.$toastr.error('Leadership must be between 0 and 10');
          return false;
        }
      }
      return true;
    },
    updateEmployee(selected) {
      if (selected && typeof selected === 'object') {
        this.editEvaluation.user_id = selected;
        if (selected.designation_id === 1) {
          this.editEvaluation.reports_submitted = null;
        }
      }
    },
    updateNewEmployee(selected) {
      if (selected && typeof selected === 'object') {
        this.newEvaluation.user_id = selected;
        if (selected.designation_id === 1) {
          this.newEvaluation.reports_submitted = null;
        }
      }
    },
  }
};
</script>

<style scoped>
.green { color: green; }
.orange { color: orange; }
.red { color: red; }
</style>
```