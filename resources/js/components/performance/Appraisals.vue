<template>
    <v-app>
      <v-app-bar color="primary" dark app>
        <v-app-bar-title>Employee Appraisal </v-app-bar-title>
        <v-spacer></v-spacer>
        <v-btn icon @click="toggleTheme">
          <v-icon>{{ isDarkTheme ? 'mdi-weather-sunny' : 'mdi-weather-night' }}</v-icon>
        </v-btn>
      </v-app-bar>
  
      <v-main>
        <v-container fluid>
          <!-- HEADER SECTION -->
          <v-card class="mb-6">
            <!-- <v-card-title class="text-center text-h4 font-weight-bold">
              BOXLEO COURIER & FULFILLMENT SERVICES LIMITED
            </v-card-title>
            <v-card-subtitle class="text-center text-h5">
              EMPLOYEE APPRAISAL REPORT
            </v-card-subtitle> -->
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field 
                    v-model="employeeData.name" 
                    label="Employee Name" 
                    variant="outlined"
                    :rules="[v => !!v || 'Required']"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    v-model="employeeData.id" 
                    label="Employee ID" 
                    variant="outlined"
                    :rules="[v => !!v || 'Required']"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    v-model="employeeData.position" 
                    label="Position Title" 
                    variant="outlined"
                    :rules="[v => !!v || 'Required']"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    v-model="employeeData.department" 
                    label="Department" 
                    variant="outlined"
                    :rules="[v => !!v || 'Required']"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    v-model="employeeData.reviewDate" 
                    label="Review Date" 
                    type="date"
                    variant="outlined"
                    :rules="[v => !!v || 'Required']"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field 
                    v-model="employeeData.supervisorName" 
                    label="Supervisor's Name" 
                    variant="outlined"
                    :rules="[v => !!v || 'Required']"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
  
          <v-tabs v-model="activeTab" grow color="primary">
            <v-tab value="performance">Performance Objectives</v-tab>
            <v-tab value="competency">Competency Evaluation</v-tab>
            <v-tab value="evaluation">Evaluation Summary</v-tab>
            <v-tab value="development">Development Plan</v-tab>
            <v-tab value="feedback">Employee Feedback</v-tab>
          </v-tabs>
  
          <v-window v-model="activeTab">
            <!-- SECTION I: PERFORMANCE OBJECTIVES -->
            <v-window-item value="performance">
              <v-card class="mt-4">
                <v-card-title class="text-h6 font-weight-bold">
                  SECTION I: PERFORMANCE OBJECTIVES
                </v-card-title>
                <v-card-text>
                  <p class="mb-4">List all performance objectives and their criteria of measurement as formulated at the beginning of the year in collaboration with your line manager or HOD.</p>
                  
                  <v-row v-for="(objective, index) in objectives" :key="index" class="mb-2">
                    <v-col cols="12" md="5">
                      <v-text-field
                        v-model="objective.description"
                        :label="`Performance Objective ${index + 1}`"
                        variant="outlined"
                        :rules="[v => !!v || 'Required']"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="5">
                      <v-textarea
                        v-model="objective.criteria"
                        :label="`Criteria for Measurement ${index + 1}`"
                        variant="outlined"
                        rows="2"
                        :rules="[v => !!v || 'Required']"
                      ></v-textarea>
                    </v-col>
                    <v-col cols="12" md="2" class="d-flex align-center">
                      <v-btn icon color="error" @click="removeObjective(index)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </v-col>
                  </v-row>
                  
                  <v-btn 
                    color="success" 
                    class="mt-4" 
                    prepend-icon="mdi-plus" 
                    @click="addObjective"
                  >
                    Add Objective
                  </v-btn>
                </v-card-text>
              </v-card>
            </v-window-item>
  
            <!-- SECTION II: COMPETENCY EVALUATION -->
            <v-window-item value="competency">
              <v-card class="mt-4">
                <v-card-title class="text-h6 font-weight-bold">
                  SECTION II: COMPETENCY EVALUATION
                </v-card-title>
                <v-card-text>
                  <p class="mb-4">Expertise in all or most of the following competencies is essential for success at Boxleo. Rate the employee in each competency that applies to their current job.</p>
                  
                  <v-alert type="info" variant="tonal" class="mb-4">
                    <strong>Rating Scale:</strong><br>
                    1 = Unsatisfactory: Employee has not reached an acceptable level of competency<br>
                    2 = Needs Improvement: Employee needs some improvement to reach an acceptable level<br>
                    3 = Successful: Employee successfully demonstrates a satisfactory level of competency<br>
                    4 = Excellent: Employee excels in this competency and exceeds normal expectations
                  </v-alert>
  
                  <v-expansion-panels variant="accordion">
                    <v-expansion-panel
                      v-for="(category, index) in competencyCategories"
                      :key="index"
                    >
                      <v-expansion-panel-title>
                        <div class="d-flex align-center">
                          <span class="mr-4">{{ category.title }}</span>
                          <v-chip
                            v-if="getCategoryRating(category.id)"
                            color="primary"
                            size="small"
                          >
                            Rating: {{ getCategoryRating(category.id) }}
                          </v-chip>
                        </div>
                      </v-expansion-panel-title>
                      <v-expansion-panel-text>
                        <v-card flat>
                          <v-card-text>
                            <div v-for="(desc, i) in category.descriptions" :key="i" class="mb-2">
                              <v-icon size="small" color="primary" class="mr-2">mdi-check-circle</v-icon>
                              {{ desc }}
                            </div>
                            
                            <div class="mt-4">
                              <v-radio-group 
                                v-model="competencyRatings[category.id]" 
                                inline
                                class="mt-2"
                              >
                                <v-radio 
                                  v-for="n in 4" 
                                  :key="n" 
                                  :label="n.toString()" 
                                  :value="n"
                                ></v-radio>
                              </v-radio-group>
                            </div>
                            
                            <v-textarea
                              v-model="competencyComments[category.id]"
                              label="Comments"
                              variant="outlined"
                              rows="2"
                              class="mt-2"
                            ></v-textarea>
                          </v-card-text>
                        </v-card>
                      </v-expansion-panel-text>
                    </v-expansion-panel>
                  </v-expansion-panels>
                </v-card-text>
              </v-card>
            </v-window-item>
  
            <!-- SECTION III: EVALUATION SUMMARY -->
            <v-window-item value="evaluation">
              <v-card class="mt-4">
                <v-card-title class="text-h6 font-weight-bold">
                  SECTION III: PERFORMANCE OBJECTIVES EVALUATION
                </v-card-title>
                <v-card-text>
                  <v-textarea
                    v-model="evaluationSummary.objectivesMet"
                    label="Please explain how the employee met the performance objectives established for this year"
                    variant="outlined"
                    rows="4"
                  ></v-textarea>
                  
                  <v-textarea
                    v-model="evaluationSummary.improvementAreas"
                    label="Please indicate what areas of improvement are still needed to successfully meet performance objectives"
                    variant="outlined"
                    rows="4"
                    class="mt-4"
                  ></v-textarea>
                  
                  <v-card class="mt-6 pa-4">
                    <v-card-title class="text-h6">Overall Performance Rating</v-card-title>
                    <v-card-text>
                      <v-radio-group v-model="evaluationSummary.overallRating">
                        <v-radio
                          v-for="(desc, rating) in ratingDescriptions"
                          :key="rating"
                          :label="`${rating} - ${desc.title}`"
                          :value="parseInt(rating)"
                        >
                          <template v-slot:label>
                            <div>
                              <strong>{{ rating }} - {{ desc.title }}</strong>
                              <div class="text-caption">{{ desc.description }}</div>
                            </div>
                          </template>
                        </v-radio>
                      </v-radio-group>
                    </v-card-text>
                  </v-card>
                </v-card-text>
              </v-card>
            </v-window-item>
  
            <!-- SECTION IV: DEVELOPMENT PLAN -->
            <v-window-item value="development">
              <v-card class="mt-4">
                <v-card-title class="text-h6 font-weight-bold">
                  SECTION IV: PROFESSIONAL DEVELOPMENT PLAN
                </v-card-title>
                <v-card-text>
                  <v-textarea
                    v-model="developmentPlan.improvementAreas"
                    label="What areas do you feel you would like to improve?"
                    variant="outlined"
                    rows="3"
                  ></v-textarea>
                  
                  <v-textarea
                    v-model="developmentPlan.supervisorAssistance"
                    label="How can your supervisor assist you in your job?"
                    variant="outlined"
                    rows="3"
                    class="mt-4"
                  ></v-textarea>
                  
                  <v-textarea
                    v-model="developmentPlan.activities"
                    label="What internal and external professional development activities will you participate in during the upcoming year?"
                    variant="outlined"
                    rows="3"
                    class="mt-4"
                  ></v-textarea>
                </v-card-text>
              </v-card>
            </v-window-item>
  
            <!-- SECTION V: EMPLOYEE FEEDBACK -->
            <v-window-item value="feedback">
              <v-card class="mt-4">
                <v-card-title class="text-h6 font-weight-bold">
                  SECTION V: EMPLOYEE FEEDBACK
                </v-card-title>
                <v-card-text>
                  <v-textarea
                    v-model="employeeFeedback"
                    label="Please provide us with your comments on the above evaluation"
                    variant="outlined"
                    rows="6"
                  ></v-textarea>
                  
                  <v-divider class="my-6"></v-divider>
                  
                  <p class="mb-4">This review and its subsequent rating have been discussed with me. The employee's signature does not necessarily imply agreement with the rating or the contents of the review.</p>
                  
                  <v-row>
                    <v-col cols="12" md="6">
                      <v-text-field
                        v-model="signatures.supervisor"
                        label="Supervisor's Signature"
                        variant="outlined"
                      ></v-text-field>
                      <v-text-field
                        v-model="signatures.supervisorDate"
                        label="Date"
                        type="date"
                        variant="outlined"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-text-field
                        v-model="signatures.employee"
                        label="Employee's Signature"
                        variant="outlined"
                      ></v-text-field>
                      <v-text-field
                        v-model="signatures.employeeDate"
                        label="Date"
                        type="date"
                        variant="outlined"
                      ></v-text-field>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>
            </v-window-item>
          </v-window>
  
          <v-card class="mt-6">
            <v-card-actions>
              <v-btn 
                color="primary" 
                @click="previousTab" 
                :disabled="activeTab === 'performance'"
              >
                Previous
              </v-btn>
              <v-spacer></v-spacer>
              <v-btn 
                color="error" 
                @click="resetForm"
              >
                Reset Form
              </v-btn>
              <v-btn 
                color="success" 
                @click="saveAppraisal"
                class="ml-2"
              >
                Save
              </v-btn>
              <v-spacer></v-spacer>
              <v-btn 
                color="primary" 
                @click="nextTab" 
                :disabled="activeTab === 'feedback'"
              >
                Next
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-container>
      </v-main>
  
      <v-dialog v-model="dialogVisible" max-width="500px">
        <v-card>
          <v-card-title>{{ dialogTitle }}</v-card-title>
          <v-card-text>{{ dialogMessage }}</v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" @click="dialogVisible = false">OK</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
  
      <v-dialog v-model="confirmReset" max-width="500px">
        <v-card>
          <v-card-title>Confirm Reset</v-card-title>
          <v-card-text>Are you sure you want to reset the form? All data will be lost.</v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" @click="confirmReset = false">Cancel</v-btn>
            <v-btn color="error" @click="confirmResetForm">Reset</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-app>
  </template>
  
  <script>
  export default {
    data() {
      return {
        isDarkTheme: false,
        activeTab: 'performance',
        dialogVisible: false,
        dialogTitle: '',
        dialogMessage: '',
        confirmReset: false,
        
        employeeData: {
          name: '',
          id: '',
          position: '',
          department: '',
          reviewDate: new Date().toISOString().substr(0, 10),
          supervisorName: ''
        },
        
        objectives: [
          { description: '', criteria: '' },
          { description: '', criteria: '' },
          { description: '', criteria: '' }
        ],
        
        competencyCategories: [
          {
            id: 'communication',
            title: 'Communication',
            descriptions: [
              'Effectively expresses ideas & thoughts verbally and/or in written form',
              'Exhibits good listening & comprehension skills',
              'Uses necessary & appropriate communication methods to keep others informed'
            ]
          },
          {
            id: 'business',
            title: 'Business Principles',
            descriptions: [
              'Adheres to Boxleo financial & accounting procedures & solves financial problems within approved guidelines',
              'Consistently provides client-oriented service',
              'Demonstrates organizational commitment by participating in organizational activities'
            ]
          },
          {
            id: 'diversity',
            title: 'Diversity',
            descriptions: [
              'Demonstrates behaviors that contribute to Boxleo diversity initiatives',
              'Demonstrates a commitment to continuous improvement of diversity competencies'
            ]
          },
          {
            id: 'wellness',
            title: 'Corporate Wellness',
            descriptions: [
              'Consistently applies Boxleo values, policies, & procedures',
              'Contributes to business development and organization growth',
              'Effectively manages resources to achieve program goals'
            ]
          },
          {
            id: 'knowledge',
            title: 'Job Knowledge',
            descriptions: [
              'Demonstrates the required core administrative, technical & program competencies',
              'Keeps abreast of trends in their field of expertise',
              'Knowledgeable about the goals & objectives of the project, department, or committee'
            ]
          },
          {
            id: 'innovation',
            title: 'Innovation',
            descriptions: [
              'Effectively suggests, develops, and/or implements creative solutions to problems & issues',
              'Proactively enhances Boxleo products & services'
            ]
          },
          {
            id: 'productivity',
            title: 'Productivity',
            descriptions: [
              'Effectively plans, organizes & delegates work to achieve desired results',
              'Manages competing demands with appropriate flexibility & judgment',
              'Effectively applies knowledge & skills to work situations'
            ]
          },
          {
            id: 'leadership',
            title: 'Leadership',
            descriptions: [
              'Demonstrates good judgment, critically evaluates information, weighs alternative courses of action',
              'Enables & empowers staff to succeed',
              'Successfully builds & leverages positive relationships within & outside the organization'
            ]
          },
          {
            id: 'professionalism',
            title: 'Professionalism',
            descriptions: [
              'Honest, reliable, dependable & conscientious',
              'Successfully builds positive relationships with clients & colleagues',
              'Upholds a high standard of ethical and personal conduct'
            ]
          },
          {
            id: 'development',
            title: 'Staff Development',
            descriptions: [
              'Effectively engages staff & colleagues in problem solving',
              'Effectively mentors, coaches, counsels, & provides development of staff',
              'Pursues opportunities to engage in professional development'
            ]
          },
          {
            id: 'teamwork',
            title: 'Team Work',
            descriptions: [
              'Works cooperatively & effectively with supervisors, colleagues, & other staff at all levels',
              'Accepts (& offers) constructive criticism & feedback',
              'Exercises responsibility, courtesy, dependability & respect in work teams'
            ]
          }
        ],
        
        competencyRatings: {},
        competencyComments: {},
        
        evaluationSummary: {
          objectivesMet: '',
          improvementAreas: '',
          overallRating: null
        },
        
        ratingDescriptions: {
          1: {
            title: 'Unsatisfactory',
            description: 'Performance was often seriously deficient and required significant correction. Performance objectives were not attained.'
          },
          2: {
            title: 'Needs Improvement',
            description: 'Performance was deficient and needs improvement. Only some of the performance objectives were attained.'
          },
          3: {
            title: 'Successful',
            description: 'Performance and work quality meet all expectations. The majority of the performance objectives were attained.'
          },
          4: {
            title: 'Excellent',
            description: 'Performance was commendable and often viewed as a model for other employees. All of the performance objectives were attained.'
          }
        },
        
        developmentPlan: {
          improvementAreas: '',
          supervisorAssistance: '',
          activities: ''
        },
        
        employeeFeedback: '',
        
        signatures: {
          supervisor: '',
          supervisorDate: new Date().toISOString().substr(0, 10),
          employee: '',
          employeeDate: new Date().toISOString().substr(0, 10)
        }
      }
    },
    
    methods: {
      toggleTheme() {
        this.isDarkTheme = !this.isDarkTheme;
        this.$vuetify.theme.dark = this.isDarkTheme;
      },
      
      addObjective() {
        this.objectives.push({ description: '', criteria: '' });
      },
      
      removeObjective(index) {
        if (this.objectives.length > 1) {
          this.objectives.splice(index, 1);
        } else {
          this.showDialog('Cannot Remove', 'You must have at least one performance objective.');
        }
      },
      
      getCategoryRating(categoryId) {
        return this.competencyRatings[categoryId] || null;
      },
      
      nextTab() {
        const tabs = ['performance', 'competency', 'evaluation', 'development', 'feedback'];
        const currentIndex = tabs.indexOf(this.activeTab);
        if (currentIndex < tabs.length - 1) {
          this.activeTab = tabs[currentIndex + 1];
        }
      },
      
      previousTab() {
        const tabs = ['performance', 'competency', 'evaluation', 'development', 'feedback'];
        const currentIndex = tabs.indexOf(this.activeTab);
        if (currentIndex > 0) {
          this.activeTab = tabs[currentIndex - 1];
        }
      },
      
      showDialog(title, message) {
        this.dialogTitle = title;
        this.dialogMessage = message;
        this.dialogVisible = true;
      },
      
      resetForm() {
        this.confirmReset = true;
      },
      
      confirmResetForm() {
        // Reset all form data
        this.employeeData = {
          name: '',
          id: '',
          position: '',
          department: '',
          reviewDate: new Date().toISOString().substr(0, 10),
          supervisorName: ''
        };
        
        this.objectives = [
          { description: '', criteria: '' },
          { description: '', criteria: '' },
          { description: '', criteria: '' }
        ];
        
        this.competencyRatings = {};
        this.competencyComments = {};
        
        this.evaluationSummary = {
          objectivesMet: '',
          improvementAreas: '',
          overallRating: null
        };
        
        this.developmentPlan = {
          improvementAreas: '',
          supervisorAssistance: '',
          activities: ''
        };
        
        this.employeeFeedback = '';
        
        this.signatures = {
          supervisor: '',
          supervisorDate: new Date().toISOString().substr(0, 10),
          employee: '',
          employeeDate: new Date().toISOString().substr(0, 10)
        };
        
        this.activeTab = 'performance';
        this.confirmReset = false;
        
        this.showDialog('Form Reset', 'The appraisal form has been reset successfully.');
      },
      
      validateForm() {
        // Basic validation
        if (!this.employeeData.name || !this.employeeData.id || !this.employeeData.position || !this.employeeData.department) {
          this.showDialog('Validation Error', 'Please complete all employee information fields.');
          this.activeTab = 'performance';
          return false;
        }
        
        // Check if at least one objective is filled
        let hasValidObjective = false;
        for (const obj of this.objectives) {
          if (obj.description && obj.criteria) {
            hasValidObjective = true;
            break;
          }
        }
        
        if (!hasValidObjective) {
          this.showDialog('Validation Error', 'Please add at least one performance objective with criteria.');
          this.activeTab = 'performance';
          return false;
        }
        
        // Check if overall rating is selected
        if (!this.evaluationSummary.overallRating) {
          this.showDialog('Validation Error', 'Please select an overall performance rating.');
          this.activeTab = 'evaluation';
          return false;
        }
        
        return true;
      },
      
      saveAppraisal() {
        if (!this.validateForm()) return;
        
        // In a real application, you would send this data to your backend
        const appraisalData = {
          employeeInfo: this.employeeData,
          objectives: this.objectives,
          competencyRatings: this.competencyRatings,
          competencyComments: this.competencyComments,
          evaluationSummary: this.evaluationSummary,
          developmentPlan: this.developmentPlan,
          employeeFeedback: this.employeeFeedback,
          signatures: this.signatures,
          submittedDate: new Date().toISOString()
        };
        
        // For demo purposes, just log the data
        console.log('Appraisal data saved:', appraisalData);
        
        // You would typically use axios here to save the data
        // axios.post('/api/v1/employee-appraisals', appraisalData)
        //   .then(response => {
        //     this.showDialog('Success', 'Employee appraisal has been saved successfully!');
        //   })
        //   .catch(error => {
        //     this.showDialog('Error', 'Failed to save the appraisal. Please try again later.');
        //     console.error("Error saving appraisal:", error);
        //   });
        
        this.showDialog('Success', 'Employee appraisal has been saved successfully!');
      }
    }
  }
  </script>
  
  <style>
  .v-expansion-panel-title {
    font-weight: 600;
  }
  
  .v-expansion-panels {
    margin-bottom: 24px;
  }
  </style>