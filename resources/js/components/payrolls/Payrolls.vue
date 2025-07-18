<template>
    <v-container fluid>
      <!-- Page Header -->
      <v-row class="mb-4">
        <v-col>
          <h2 class="text-h4 font-weight-bold">Payroll Management</h2>
          <v-breadcrumbs :items="breadcrumbs" divider="/"></v-breadcrumbs>
        </v-col>
        <v-col cols="auto" class="d-flex align-center">
          <v-btn-group>
            <v-btn color="primary" prepend-icon="mdi-plus-circle" v-if="isAdmin" @click="showCreatePayrollModal">
              Generate Payroll
            </v-btn>
            <v-btn color="success" prepend-icon="mdi-file-excel" @click="showImportModal">
              Import Excel
            </v-btn>
            <v-btn color="info" prepend-icon="mdi-download" @click="exportToExcel">
              Export Excel
            </v-btn>
            <v-btn color="error" prepend-icon="mdi-file-pdf" @click="exportToPDF">
              Export PDF
            </v-btn>
          </v-btn-group>
        </v-col>
      </v-row>
  
      <!-- Filter Section -->
      <v-card class="mb-4">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="6" md="2">
              <v-select
                v-model="filters.month"
                :items="monthItems"
                label="Month"
                variant="outlined"
                density="comfortable"
                hide-details
                clearable
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6" md="2">
              <v-select
                v-model="filters.year"
                :items="yearItems"
                label="Year"
                variant="outlined"
                density="comfortable"
                hide-details
                clearable
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6" md="2">
              <v-select
                v-model="filters.department"
                :items="departmentItems"
                label="Department"
                variant="outlined"
                density="comfortable"
                hide-details
                clearable
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6" md="2">
              <v-select
                v-model="filters.designation"
                :items="designationItems"
                label="Designation"
                variant="outlined"
                density="comfortable"
                hide-details
                clearable
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6" md="2">
              <v-select
                v-model="filters.country"
                :items="countryItems"
                label="Country"
                variant="outlined"
                density="comfortable"
                hide-details
                clearable
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6" md="2">
              <v-btn color="primary" block @click="applyFilters" prepend-icon="mdi-filter">
                Apply Filters
              </v-btn>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
  
      <!-- Toggle View -->
      <v-btn-group class="mb-4">
        <v-btn 
          :variant="currentView === 'table' ? 'flat' : 'outlined'" 
          :color="currentView === 'table' ? 'primary' : ''" 
          @click="currentView = 'table'"
          prepend-icon="mdi-table"
        >
          Table View
        </v-btn>
        <v-btn 
          :variant="currentView === 'card' ? 'flat' : 'outlined'" 
          :color="currentView === 'card' ? 'primary' : ''" 
          @click="currentView = 'card'"
          prepend-icon="mdi-view-grid"
        >
          Card View
        </v-btn>
      </v-btn-group>
  
      <!-- Loading Progress -->
      <div v-if="isLoading" class="text-center my-5">
        <v-progress-circular indeterminate color="primary" size="60"></v-progress-circular>
        <p class="mt-4 text-body-1">Loading payroll data...</p>
      </div>
  
      <!-- Table View -->
      <v-card v-else-if="currentView === 'table'">
        <v-data-table
          :headers="headers"
          :items="paginatedPayrolls"
          :items-per-page="itemsPerPage"
          :page="currentPage"
          @update:page="currentPage = $event"
          @update:sort-by="updateSort"
          :sort-by="[{ key: sortColumn, order: sortDirection }]"
          class="elevation-1"
        >
          <template v-slot:item.employee="{ item }">
            <div class="d-flex align-center">
              <v-avatar size="36" class="mr-2">

                <v-img :src="item?.raw?.user?.avatar || '/images/default-avatar.png'" alt="avatar"></v-img>
              </v-avatar>
              <div>

                <div class="font-weight-medium">
                  {{ item?.raw?.user?.firstname || '' }} {{ item?.raw?.user?.lastname || '' }}
                </div>

                <div class="text-caption text-medium-emphasis">{{ item?.raw?.user?.job_title || '' }}</div>
              </div>
            </div>
          </template>
          
          <template #item.basic_pay="{ item }">
            {{ formatCurrency(item?.raw?.basic_pay ?? 0) }}
          </template>
          
          <template v-slot:item.allowances="{ item }">
            {{ formatCurrency(calculateTotalAllowances(item?.raw ?? {})) }}
          </template>
          
          <template v-slot:item.gross_pay="{ item }">
            {{ formatCurrency(item?.raw?.gross_pay ?? 0) }}
          </template>
          
          <template v-slot:item.deductions="{ item }">
            {{ formatCurrency(item?.raw?.deductions ?? 0) }}
          </template>
          
          <template v-slot:item.net_pay="{ item }">
            <span class="font-weight-bold">{{ formatCurrency(item?.raw?.net_pay ?? 0) }}</span>
          </template>
          
          <template v-slot:item.actions="{ item }">
            <v-menu>
              <template v-slot:activator="{ props }">
                <v-btn
                  density="comfortable"
                  icon="mdi-dots-vertical"
                  variant="text"
                  v-bind="props"
                ></v-btn>
              </template>
              <v-list>
                <v-list-item v-if="item?.raw?.id" @click="viewPayslip(item.raw.id)">
                  <template v-slot:prepend>
                    <v-icon color="info">mdi-eye</v-icon>
                  </template>
                  <v-list-item-title>View</v-list-item-title>
                </v-list-item>
                
                <v-list-item v-if="item?.raw?.id" @click="printPayslip(item.raw.id)">
                  <template v-slot:prepend>
                    <v-icon color="primary">mdi-printer</v-icon>
                  </template>
                  <v-list-item-title>Print</v-list-item-title>
                </v-list-item>
                
                <v-list-item v-if="item?.raw?.id" @click="emailPayslip(item.raw.id)">
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-email</v-icon>
                  </template>
                  <v-list-item-title>Email</v-list-item-title>
                </v-list-item>
                
                <v-divider v-if="isAdmin"></v-divider>
                
                <v-list-item v-if="isAdmin && item?.raw?.id" @click="editPayroll(item.raw.id)">
                  <template v-slot:prepend>
                    <v-icon color="warning">mdi-pencil</v-icon>
                  </template>
                  <v-list-item-title>Edit</v-list-item-title>
                </v-list-item>
                
                <v-list-item v-if="isAdmin && item?.raw?.id" @click="confirmDeletePayroll(item.raw.id)">
                  <template v-slot:prepend>
                    <v-icon color="error">mdi-delete</v-icon>
                  </template>
                  <v-list-item-title>Delete</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </template>
          
          <template v-slot:no-data>
            <div class="d-flex flex-column align-center py-8">
              <v-icon icon="mdi-file-search-outline" size="large" color="grey" class="mb-4"></v-icon>
              <h4 class="text-h6 font-weight-regular mb-2">No payroll records found</h4>
              <p class="text-body-2 text-medium-emphasis">Try adjusting your search or filter criteria</p>
            </div>
          </template>
        </v-data-table>
      </v-card>
  
      <!-- Card View -->
      <v-row v-else-if="currentView === 'card'">
        <v-col v-for="payroll in paginatedPayrolls" :key="payroll.id" cols="12" sm="6" md="4">
          <v-card height="100%">
            <v-card-item>
              <template v-slot:prepend>
                <v-avatar size="40">
                  <v-img :src="payroll.user.avatar || '/images/default-avatar.png'" alt="User Avatar"></v-img>
                </v-avatar>
              </template>
              
              <v-card-title>
                {{ payroll.user.firstname }} {{ payroll.user.lastname }}
              </v-card-title>
              
              <v-card-subtitle>
                {{ payroll.user.job_title }}
              </v-card-subtitle>
              
              <template v-slot:append>
                <v-chip
                  :color="getPaymentStatusColor(payroll.payment_status)"
                  size="small"
                  text-color="white"
                >
                  {{ payroll.payment_status || 'Pending' }}
                </v-chip>
              </template>
            </v-card-item>
            
            <v-card-text>
              <v-list density="compact" lines="two">
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon icon="mdi-domain" size="small" color="primary"></v-icon>
                  </template>
                  <v-list-item-title>Department</v-list-item-title>
                  <v-list-item-subtitle>{{ payroll.user.department }}</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon icon="mdi-calendar" size="small" color="primary"></v-icon>
                  </template>
                  <v-list-item-title>Period</v-list-item-title>
                  <v-list-item-subtitle>{{ getMonthName(payroll.month) }} {{ payroll.year }}</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon icon="mdi-cash" size="small" color="success"></v-icon>
                  </template>
                  <v-list-item-title>Basic Pay</v-list-item-title>
                  <v-list-item-subtitle>{{ formatCurrency(payroll.basic_pay) }}</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon icon="mdi-cash-multiple" size="small" color="success"></v-icon>
                  </template>
                  <v-list-item-title>Gross Pay</v-list-item-title>
                  <v-list-item-subtitle>{{ formatCurrency(payroll.gross_pay) }}</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon icon="mdi-cash-minus" size="small" color="error"></v-icon>
                  </template>
                  <v-list-item-title>Deductions</v-list-item-title>
                  <v-list-item-subtitle>{{ formatCurrency(payroll.deductions) }}</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon icon="mdi-cash-check" size="small" color="primary"></v-icon>
                  </template>
                  <v-list-item-title class="font-weight-bold">Net Pay</v-list-item-title>
                  <v-list-item-subtitle class="font-weight-bold">{{ formatCurrency(payroll.net_pay) }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
            
            <v-divider></v-divider>
            
            <v-card-actions>
              <v-btn variant="text" color="info" @click="viewPayslip(payroll.id)">
                <v-icon start>mdi-eye</v-icon>
                View
              </v-btn>
              <v-btn variant="text" color="primary" @click="printPayslip(payroll.id)">
                <v-icon start>mdi-printer</v-icon>
                Print
              </v-btn>
              <v-btn variant="text" color="success" @click="emailPayslip(payroll.id)">
                <v-icon start>mdi-email</v-icon>
                Email
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
        
        <!-- Empty State for Card View -->
        <v-col v-if="paginatedPayrolls.length === 0" cols="12">
          <v-card>
            <v-card-text class="text-center py-8">
              <v-icon icon="mdi-file-search-outline" size="x-large" color="grey" class="mb-4"></v-icon>
              <h4 class="text-h6 font-weight-regular mb-2">No payroll records found</h4>
              <p class="text-body-2 text-medium-emphasis">Try adjusting your search or filter criteria</p>
            </v-card-text>
          </v-card>
        </v-col>
        
        <!-- Pagination for Card View -->
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center">
            <div class="text-body-2">
              Showing {{ startIndex + 1 }} to {{ Math.min(startIndex + itemsPerPage, filteredPayrolls.length) }} of {{ filteredPayrolls.length }} entries
            </div>
            <v-pagination
              v-model="currentPage"
              :length="totalPages"
              :total-visible="5"
              rounded
            ></v-pagination>
          </div>
        </v-col>
      </v-row>
  
      <!-- Generate Payroll Dialog -->
      <v-dialog v-model="payrollDialog" max-width="900px">
        <v-card>
          <v-card-title class="text-h5 bg-primary text-white">
            {{ isEditing ? 'Update Payroll' : 'Generate Payroll' }}
            <v-spacer></v-spacer>
            <v-btn icon="mdi-close" variant="text" color="white" @click="payrollDialog = false"></v-btn>
          </v-card-title>
          
          <v-card-text class="pt-4">
            <v-form @submit.prevent="submitPayroll" ref="payrollForm">
              <v-row>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="payrollForm.user_id"
                    :items="employeeItems"
                      item-value="id" 
                    item-title="fullName"
                    label="Employee"
                    variant="outlined"
                    :rules="[v => !!v || 'Employee is required']"
                    required
                    @update:model-value="onEmployeeSelect"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="payrollForm.month"
                    :items="monthItems"
                    label="Month"
                    variant="outlined"
                    :rules="[v => !!v || 'Month is required']"
                    required
                  ></v-select>
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="payrollForm.year"
                    :items="yearItems"
                    label="Year"
                    variant="outlined"
                    :rules="[v => !!v || 'Year is required']"
                    required
                  ></v-select>
                </v-col>
              </v-row>
  
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model.number="payrollForm.basic_pay"
                    label="Basic Pay"
                    type="number"
                    variant="outlined"
                    prefix="Ksh"
                    :rules="[v => !!v || 'Basic pay is required']"
                    @input="calculatePayrollTotals"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="payrollForm.payment_mode"
                    :items="['Bank Transfer', 'Cash', 'Cheque', 'Mobile Money']"
                    label="Payment Mode"
                    variant="outlined"
                    :rules="[v => !!v || 'Payment mode is required']"
                    required
                  ></v-select>
                </v-col>
              </v-row>
  
              <v-row v-if="payrollForm.payment_mode === 'Bank Transfer'">
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="payrollForm.bank"
                    label="Bank"
                    variant="outlined"
                    :rules="[v => !!v || 'Bank is required']"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="payrollForm.bank_branch"
                    label="Branch"
                    variant="outlined"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="payrollForm.bank_account"
                    label="Account Number"
                    variant="outlined"
                    :rules="[v => !!v || 'Account number is required']"
                  ></v-text-field>
                </v-col>
              </v-row>
  
              <v-divider class="my-4"></v-divider>
              
              <!-- Earnings Section -->
              <div class="d-flex justify-space-between align-center mb-2">
                <h3 class="text-h6">Earnings</h3>
                <v-btn
                  size="small"
                  color="primary"
                  variant="text"
                  prepend-icon="mdi-plus"
                  @click="addEarning"
                >
                  Add Earning
                </v-btn>
              </div>
              
              <v-row v-for="(earning, index) in payrollForm.earnings" :key="`earning-${index}`" class="mb-1">
                <v-col cols="12" md="5">
                  <v-text-field
                    v-model="earning.earning_type.label"
                    label="Earning Type"
                    variant="outlined"
                    density="comfortable"
                    hide-details
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="5">
                  <v-text-field
                    v-model.number="earning.amount"
                    label="Amount"
                    type="number"
                    variant="outlined"
                    density="comfortable"
                    prefix="ksh"
                    hide-details
                    @input="calculatePayrollTotals"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="2">
                  <v-btn
                    icon="mdi-delete"
                    variant="text"
                    color="error"
                    @click="removeEarning(index)"
                    class="mt-2"
                  ></v-btn>
                </v-col>
              </v-row>
  
              <v-divider class="my-4"></v-divider>
              
              <!-- Deductions Section -->
              <div class="d-flex justify-space-between align-center mb-2">
                <h3 class="text-h6">Deductions</h3>
                <v-btn
                  size="small"
                  color="primary"
                  variant="text"
                  prepend-icon="mdi-plus"
                  @click="addDeduction"
                >
                  Add Deduction
                </v-btn>
              </div>
              
              <v-row v-for="(deduction, index) in payrollForm.deductions" :key="`deduction-${index}`" class="mb-1">
                <v-col cols="12" md="5">
                  <v-text-field
                    v-model="deduction.deduction_type.label"
                    label="Deduction Type"
                    variant="outlined"
                    density="comfortable"
                    hide-details
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="5">
                  <v-text-field
                    v-model.number="deduction.amount"
                    label="Amount"
                    type="number"
                    variant="outlined"
                    density="comfortable"
                    prefix="ksh"
                    hide-details
                    @input="calculatePayrollTotals"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="2">
                  <v-btn
                    icon="mdi-delete"
                    variant="text"
                    color="error"
                    @click="removeDeduction(index)"
                    class="mt-2"
                  ></v-btn>
                </v-col>
              </v-row>
  
              <v-divider class="my-4"></v-divider>
              
              <!-- Summary Section -->
              <v-card variant="outlined" class="mt-2 pa-4">
                <div class="d-flex justify-space-between mb-2">
                  <div class="text-subtitle-1">Gross Pay:</div>
                  <div class="text-subtitle-1">{{ formatCurrency(calculateGrossPay) }}</div>
                </div>
                <div class="d-flex justify-space-between mb-2">
                  <div class="text-subtitle-1">Total Deductions:</div>
                  <div class="text-subtitle-1">{{ formatCurrency(calculateTotalDeductions) }}</div>
                </div>
                <v-divider class="my-3"></v-divider>
                <div class="d-flex justify-space-between">
                  <div class="text-h6 font-weight-bold">Net Pay:</div>
                  <div class="text-h6 font-weight-bold text-primary">{{ formatCurrency(calculateNetPay) }}</div>
                </div>
              </v-card>
            </v-form>
          </v-card-text>
          
          <v-card-actions class="pa-4">
            <v-spacer></v-spacer>
            <v-btn variant="outlined" color="grey" @click="payrollDialog = false">Cancel</v-btn>
            <v-btn 
              variant="elevated" 
              color="primary" 
              :loading="isSubmitting"
              @click="submitPayroll"
            >
              {{ isEditing ? 'Update Payroll' : 'Generate Payroll' }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
  
      <!-- Import Excel Dialog -->
      <v-dialog v-model="importDialog" max-width="500px">
        <v-card>
          <v-card-title class="text-h5 bg-success text-white">
            Import Payroll Data
            <v-spacer></v-spacer>
            <v-btn icon="mdi-close" variant="text" color="white" @click="importDialog = false"></v-btn>
          </v-card-title>
          
          <v-card-text class="pt-4">
            <v-form @submit.prevent="importExcel">
              <v-file-input
                v-model="selectedFile"
                accept=".xlsx, .xls"
                label="Select Excel File"
                variant="outlined"
                prepend-icon="mdi-file-excel"
                :rules="[v => !!v || 'Please select a file']"
                @change="handleFileUpload"
                show-size
              ></v-file-input>
              
              <v-alert type="info" variant="tonal" class="mt-4">
                Please upload Excel file with the correct format. You can download a template first if needed.
              </v-alert>
              
              <div class="mt-4">
                <v-btn variant="text" color="primary" prepend-icon="mdi-download">
                  Download Template
                </v-btn>
              </div>
            </v-form>
          </v-card-text>
          
          <v-card-actions class="pa-4">
            <v-spacer></v-spacer>
            <v-btn variant="outlined" color="grey" @click="importDialog = false">Cancel</v-btn>
            <v-btn 
              variant="elevated" 
              color="success" 
              :loading="isSubmitting"
              :disabled="!selectedFile"
              @click="importExcel"
            >
              Import Data
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
  
      <!-- Payslip View Dialog -->
      <v-dialog v-model="payslipDialog" max-width="900px">
        <v-card id="payslipContent">
          <v-card-title class="d-flex justify-space-between bg-primary text-white">
            <span class="text-h5">Payslip</span>
            <div>
              <v-btn icon="mdi-printer" variant="text" color="white" @click="printCurrentPayslip"></v-btn>
              <v-btn icon="mdi-email" variant="text" color="white" @click="emailCurrentPayslip"></v-btn>
              <v-btn icon="mdi-close" variant="text" color="white" @click="payslipDialog = false"></v-btn>
            </div>
          </v-card-title>
          
          <v-card-text class="pa-4" v-if="currentPayslip">
            <div class="d-flex justify-space-between align-center mb-6">
              <div>
                <h4 class="text-h5 font-weight-bold">Your Company Name</h4>
                <p class="text-body-1 mb-0">123 Company Street, City, Country</p>
              </div>
              <div class="text-right">
                <h3 class="text-h5 text-uppercase text-primary font-weight-bold">Payslip</h3>
                <p class="text-body-1 mb-0">{{ getMonthName(currentPayslip.month) }} {{ currentPayslip.year }}</p>
              </div>
            </div>
  
            <v-divider class="mb-4"></v-divider>
  
            <v-row>
              <v-col cols="12" md="6">
                <h5 class="text-h6 mb-2">Employee Details</h5>
                <v-list density="compact" lines="two">
                  <v-list-item>
                    <v-list-item-title>Name</v-list-item-title>
                    <v-list-item-subtitle class="font-weight-bold">
                      {{ currentPayslip.user.firstname }} {{ currentPayslip.user.lastname }}
                    </v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item>
                    <v-list-item-title>Employee ID</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ currentPayslip.user.emp_id || 'EMP-' + currentPayslip.user.id }}
                    </v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item>
                    <v-list-item-title>Department</v-list-item-title>
                    <v-list-item-subtitle>{{ currentPayslip.user.department }}</v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item>
                    <v-list-item-title>Designation</v-list-item-title>
                    <v-list-item-subtitle>{{ currentPayslip.user.job_title }}</v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-col>
              
              <v-col cols="12" md="6">
                <h5 class="text-h6 mb-2">Payment Details</h5>
                <v-list density="compact" lines="two">
                  <v-list-item>
                    <v-list-item-title>Payment Mode</v-list-item-title>
                    <v-list-item-subtitle>{{ currentPayslip.payment_mode }}</v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item v-if="currentPayslip.payment_mode === 'Bank Transfer'">
                    <v-list-item-title>Bank</v-list-item-title>
                    <v-list-item-subtitle>{{ currentPayslip.bank }}</v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item v-if="currentPayslip.payment_mode === 'Bank Transfer'">
                    <v-list-item-title>Account No</v-list-item-title>
                    <v-list-item-subtitle>{{ currentPayslip.bank_account }}</v-list-item-subtitle>
                  </v-list-item>
                  
                  <v-list-item>
                  <v-list-item-title>Payment Date</v-list-item-title>
                  <v-list-item-subtitle>{{ formatDate(currentPayslip.payment_date) }}</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item>
                  <v-list-item-title>Payment Status</v-list-item-title>
                  <v-list-item-subtitle>
                    <v-chip
                      :color="getPaymentStatusColor(currentPayslip.payment_status)"
                      size="small"
                      text-color="white"
                    >
                      {{ currentPayslip.payment_status || 'Pending' }}
                    </v-chip>
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-col>
          </v-row>

          <v-divider class="my-4"></v-divider>

          <!-- Earnings Details -->
          <v-row>
            <v-col cols="12" md="6">
              <h5 class="text-h6 mb-2">Earnings</h5>
              <v-table>
                <thead>
                  <tr>
                    <th class="text-left">Description</th>
                    <th class="text-right">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Basic Pay</td>
                    <td class="text-right">{{ formatCurrency(currentPayslip.basic_pay) }}</td>
                  </tr>
                  <tr v-for="(earning, index) in currentPayslip.earnings" :key="index">
                    <td>{{ earning.type }}</td>
                    <td class="text-right">{{ formatCurrency(earning.amount) }}</td>
                  </tr>
                  <tr class="bg-grey-lighten-4">
                    <td class="font-weight-bold">Total Earnings</td>
                    <td class="text-right font-weight-bold">{{ formatCurrency(currentPayslip.gross_pay) }}</td>
                  </tr>
                </tbody>
              </v-table>
            </v-col>
            
            <v-col cols="12" md="6">
              <h5 class="text-h6 mb-2">Deductions</h5>
              <v-table>
                <thead>
                  <tr>
                    <th class="text-left">Description</th>
                    <th class="text-right">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(deduction, index) in currentPayslip.deductions" :key="index">
                    <td>{{ deduction.type }}</td>
                    <td class="text-right">{{ formatCurrency(deduction.amount) }}</td>
                  </tr>
                  <tr class="bg-grey-lighten-4">
                    <td class="font-weight-bold">Total Deductions</td>
                    <td class="text-right font-weight-bold">{{ formatCurrency(currentPayslip.total_deductions) }}</td>
                  </tr>
                </tbody>
              </v-table>
            </v-col>
          </v-row>

          <v-divider class="my-4"></v-divider>

          <!-- Payment Summary -->
          <v-row>
            <v-col cols="12">
              <v-card color="grey-lighten-5" class="pa-4">
                <div class="d-flex justify-space-between">
                  <div>
                    <h3 class="text-h6">Total Earnings</h3>
                    <p class="text-subtitle-1">{{ formatCurrency(currentPayslip.gross_pay) }}</p>
                  </div>
                  <div>
                    <h3 class="text-h6">Total Deductions</h3>
                    <p class="text-subtitle-1">{{ formatCurrency(currentPayslip.total_deductions) }}</p>
                  </div>
                  <div>
                    <h3 class="text-h6 text-primary">Net Pay</h3>
                    <p class="text-h5 font-weight-bold text-primary">{{ formatCurrency(currentPayslip.net_pay) }}</p>
                  </div>
                </div>
              </v-card>
            </v-col>
          </v-row>

          <v-divider class="my-4"></v-divider>

          <!-- Additional Notes -->
          <v-row>
            <v-col cols="12">
              <h5 class="text-h6 mb-2">Notes</h5>
              <v-card variant="outlined" class="pa-3">
                <p class="text-body-2">{{ currentPayslip.notes || 'No additional notes for this payslip.' }}</p>
              </v-card>
            </v-col>
          </v-row>
          
          <div class="mt-6 text-center text-body-2 text-grey">
            This is a computer-generated document and does not require a signature.
          </div>
        </v-card-text>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" color="primary" @click="downloadPayslip" prepend-icon="mdi-download">
            Download
          </v-btn>
          <v-btn variant="text" color="error" @click="printCurrentPayslip" prepend-icon="mdi-printer">
            Print
          </v-btn>
          <v-btn variant="text" @click="payslipDialog = false">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="450">
      <v-card>
        <v-card-title class="text-h5 bg-error text-white">
          Confirm Delete
          <v-spacer></v-spacer>
          <v-btn icon="mdi-close" variant="text" color="white" @click="deleteDialog = false"></v-btn>
        </v-card-title>
        
        <v-card-text class="pt-4">
          <p>Are you sure you want to delete this payroll record? This action cannot be undone.</p>
        </v-card-text>
        
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn variant="outlined" color="grey" @click="deleteDialog = false">Cancel</v-btn>
          <v-btn 
            variant="elevated" 
            color="error" 
            :loading="isDeleting"
            @click="deletePayroll"
          >
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Email Payslip Dialog -->
    <v-dialog v-model="emailDialog" max-width="500">
      <v-card>
        <v-card-title class="text-h5 bg-success text-white">
          Email Payslip
          <v-spacer></v-spacer>
          <v-btn icon="mdi-close" variant="text" color="white" @click="emailDialog = false"></v-btn>
        </v-card-title>
        
        <v-card-text class="pt-4">
          <v-form @submit.prevent="sendEmail" ref="emailForm">
            <v-text-field
              v-model="emailForm.to"
              label="Recipient Email"
              type="email"
              variant="outlined"
              :rules="[v => !!v || 'Email is required', v => /.+@.+\..+/.test(v) || 'Email must be valid']"
              required
            ></v-text-field>
            
            <v-text-field
              v-model="emailForm.subject"
              label="Subject"
              variant="outlined"
              :rules="[v => !!v || 'Subject is required']"
              required
            ></v-text-field>
            
            <v-textarea
              v-model="emailForm.message"
              label="Message"
              variant="outlined"
              auto-grow
              rows="3"
              :rules="[v => !!v || 'Message is required']"
              required
            ></v-textarea>
            
            <v-checkbox
              v-model="emailForm.attachPDF"
              label="Attach Payslip as PDF"
              hide-details
            ></v-checkbox>
          </v-form>
        </v-card-text>
        
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn variant="outlined" color="grey" @click="emailDialog = false">Cancel</v-btn>
          <v-btn 
            variant="elevated" 
            color="success" 
            :loading="isSending"
            @click="sendEmail"
          >
            Send Email
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Success/Error Snackbar -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="3000"
    >
      {{ snackbar.text }}
      <template v-slot:actions>
        <v-btn
          variant="text"
          icon="mdi-close"
          @click="snackbar.show = false"
        ></v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script>
export default {
  name: 'PayrollManagement',
  
  data() {
    return {
      // Page data
      breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'HR Management', href: '/hr' },
        { title: 'Payroll', href: '/payroll' },
      ],
      isAdmin: true, // Will be set based on user role
      isLoading: false,
      isSubmitting: false,
      isDeleting: false,
      isSending: false,
      
      // View state
      currentView: 'table',
      currentPage: 1,
      itemsPerPage: 10,
      sortColumn: 'created_at',
      sortDirection: 'desc',
      
      // Dialogs
      payrollDialog: false,
      importDialog: false,
      payslipDialog: false,
      deleteDialog: false,
      emailDialog: false,
      
      // Selected data
      selectedPayrollId: null,
      selectedFile: null,
      
      // Filter options
      filters: {
        month: null,
        year: null,
        department: null,
        designation: null,
        country: null,
      },
      
      // Form models
      payrollForm: {
        user_id: null,
        month: new Date().getMonth() + 1,
        year: new Date().getFullYear(),
        basic_pay: 0,
        payment_mode: 'Bank Transfer',
        bank: '',
        bank_branch: '',
        bank_account: '',
        earnings: [],
        deductions: [],
      },
      
      emailForm: {
        to: '',
        subject: 'Payslip',
        message: 'Please find attached your monthly payslip.',
        attachPDF: true,
      },
      
      // Dropdown options
      monthItems: [
        { title: 'January', value: 1 },
        { title: 'February', value: 2 },
        { title: 'March', value: 3 },
        { title: 'April', value: 4 },
        { title: 'May', value: 5 },
        { title: 'June', value: 6 },
        { title: 'July', value: 7 },
        { title: 'August', value: 8 },
        { title: 'September', value: 9 },
        { title: 'October', value: 10 },
        { title: 'November', value: 11 },
        { title: 'December', value: 12 },
      ],
      yearItems: [
        { title: '2023', value: 2023 },
        { title: '2024', value: 2024 },
        { title: '2025', value: 2025 },
      ],
      departmentItems: [
        { title: 'IT', value: 'IT' },
        { title: 'HR', value: 'HR' },
        { title: 'Finance', value: 'Finance' },
        { title: 'Marketing', value: 'Marketing' },
        { title: 'Operations', value: 'Operations' },
      ],
      designationItems: [
        { title: 'Manager', value: 'Manager' },
        { title: 'Developer', value: 'Developer' },
        { title: 'Analyst', value: 'Analyst' },
        { title: 'Designer', value: 'Designer' },
        { title: 'Accountant', value: 'Accountant' },
      ],
      countryItems: [
        { title: 'United States', value: 'US' },
        { title: 'Canada', value: 'CA' },
        { title: 'United Kingdom', value: 'UK' },
        { title: 'Australia', value: 'AU' },
        { title: 'Germany', value: 'DE' },
      ],
      employeeItems: [
        // { title: 'John Doe (IT)', value: 1 },
        // { title: 'Jane Smith (HR)', value: 2 },
        // { title: 'Robert Johnson (Finance)', value: 3 },
        // { title: 'Emily Davis (Marketing)', value: 4 },
        // { title: 'Michael Wilson (Operations)', value: 5 },
      ],
      
      // Table headers
      headers: [
        { title: 'Employee', key: 'employee', sortable: true },
        { title: 'Department', key: 'department', sortable: true },
        { title: 'Period', key: 'period', sortable: true },
        { title: 'Basic Pay', key: 'basic_pay', sortable: true, align: 'end' },
        { title: 'Allowances', key: 'allowances', sortable: true, align: 'end' },
        { title: 'Gross Pay', key: 'gross_pay', sortable: true, align: 'end' },
        { title: 'Deductions', key: 'deductions', sortable: true, align: 'end' },
        { title: 'Net Pay', key: 'net_pay', sortable: true, align: 'end' },
        { title: 'Actions', key: 'actions', sortable: false, align: 'center' },
      ],
      
      // Payroll data
      payrollData: [
        {
          id: 1,
          user_id: 1,
          user: {
            id: 1,
            firstname: 'John',
            lastname: 'Doe',
            job_title: 'Senior Developer',
            department: 'IT',
            emp_id: 'EMP-001',
            avatar: 'https://randomuser.me/api/portraits/men/1.jpg',
          },
          month: 4,
          year: 2025,
          basic_pay: 5000,
          gross_pay: 6500,
          deductions: 1500,
          total_deductions: 1500,
          net_pay: 5000,
          earnings: [
            { type: 'Housing Allowance', amount: 1000 },
            { type: 'Transportation', amount: 500 },
          ],
          deductions: [
            { type: 'Tax', amount: 1000 },
            { type: 'Insurance', amount: 500 },
          ],
          payment_mode: 'Bank Transfer',
          bank: 'City Bank',
          bank_branch: 'Main Branch',
          bank_account: '1234567890',
          payment_status: 'Paid',
          payment_date: '2025-04-28',
          created_at: '2025-04-05T12:00:00Z',
        },
        {
          id: 2,
          user_id: 2,
          user: {
            id: 2,
            firstname: 'Jane',
            lastname: 'Smith',
            job_title: 'HR Manager',
            department: 'HR',
            emp_id: 'EMP-002',
            avatar: 'https://randomuser.me/api/portraits/women/1.jpg',
          },
          month: 4,
          year: 2025,
          basic_pay: 4500,
          gross_pay: 5500,
          deductions: 1200,
          total_deductions: 1200,
          net_pay: 4300,
          earnings: [
            { type: 'Housing Allowance', amount: 800 },
            { type: 'Transportation', amount: 200 },
          ],
          deductions: [
            { type: 'Tax', amount: 800 },
            { type: 'Insurance', amount: 400 },
          ],
          payment_mode: 'Bank Transfer',
          bank: 'National Bank',
          bank_branch: 'Downtown Branch',
          bank_account: '0987654321',
          payment_status: 'Paid',
          payment_date: '2025-04-28',
          created_at: '2025-04-05T13:00:00Z',
        },
        {
          id: 3,
          user_id: 3,
          user: {
            id: 3,
            firstname: 'Robert',
            lastname: 'Johnson',
            job_title: 'Financial Analyst',
            department: 'Finance',
            emp_id: 'EMP-003',
            avatar: 'https://randomuser.me/api/portraits/men/2.jpg',
          },
          month: 4,
          year: 2025,
          basic_pay: 4000,
          gross_pay: 4800,
          deductions: 1000,
          total_deductions: 1000,
          net_pay: 3800,
          earnings: [
            { type: 'Housing Allowance', amount: 600 },
            { type: 'Transportation', amount: 200 },
          ],
          deductions: [
            { type: 'Tax', amount: 700 },
            { type: 'Insurance', amount: 300 },
          ],
          payment_mode: 'Bank Transfer',
          bank: 'International Bank',
          bank_branch: 'Central Branch',
          bank_account: '2468135790',
          payment_status: 'Pending',
          created_at: '2025-04-05T14:00:00Z',
        },
      ],
      
      // Notification
      snackbar: {
        show: false,
        text: '',
        color: 'success',
      },
    };
  },
  
  computed: {
    isEditing() {
      return !!this.selectedPayrollId;
    },
    
    filteredPayrolls() {
      let filtered = [...this.payrollData];
      
      if (this.filters.month) {
        filtered = filtered.filter(p => p.month === this.filters.month);
      }
      
      if (this.filters.year) {http://127.0.0.1:8000/api/v1/payslips/1/with-user
        filtered = filtered.filter(p => p.year === this.filters.year);
      }
      
      if (this.filters.department) {
        filtered = filtered.filter(p => p.user.department === this.filters.department);
      }
      
      if (this.filters.designation) {
        filtered = filtered.filter(p => p.user.job_title === this.filters.designation);
      }
      
      if (this.filters.country) {
        filtered = filtered.filter(p => p.user.country === this.filters.country);
      }
      
      return filtered;
    },
    
    paginatedPayrolls() {
      const startIndex = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredPayrolls.slice(startIndex, startIndex + this.itemsPerPage);
    },
    
    startIndex() {
      return (this.currentPage - 1) * this.itemsPerPage;
    },
    
    totalPages() {
      return Math.ceil(this.filteredPayrolls.length / this.itemsPerPage);
    },
    
    currentPayslip() {
      if (!this.selectedPayrollId) return null;
      return this.payrollData.find(p => p.id === this.selectedPayrollId);
    },
    
    calculateGrossPay() {
      let basic = parseFloat(this.payrollForm.basic_pay) || 0;
      let earnings = this.payrollForm.earnings.reduce((total, earning) => total + (parseFloat(earning.amount) || 0), 0);
      
      return basic + earnings;
    },
    
    calculateTotalDeductions() {
      return this.payrollForm.deductions.reduce((total, deduction) => total + (parseFloat(deduction.amount) || 0), 0);
    },
    
    calculateNetPay() {
      return this.calculateGrossPay - this.calculateTotalDeductions;
    },
  },
  
  methods: {


  async onEmployeeSelect() {
  const userId = this.payrollForm.user_id;
  if (!userId) return;

  try {
    const response = await axios.get(`/api/v1/payslips/${userId}/with-user`);
    const data = response.data;

    const user = data; // root level
    const userDetails = user.userdetails?.[0] || {};

    this.payrollForm.full_name = `${user.firstname} ${user.lastname}`;
    this.payrollForm.employment_date = user.employment_date;

    this.payrollForm.bank = userDetails.bank_name;
    this.payrollForm.bank_branch = userDetails.bank_branch;
    this.payrollForm.bank_account = userDetails.bank_account;
    this.payrollForm.basic_pay = userDetails.basic_pay || 0;

    this.payrollForm.earnings = data.earnings || [];
    this.payrollForm.deductions = data.deductions || [];
  } catch (error) {
    console.error('Error fetching payslip with user:', error);
    this.showNotification('Failed to fetch employee details', 'error');
  }
}
,


    // Filter methods
    applyFilters() {
      this.currentPage = 1;
    },
    
    // Sort methods
    updateSort(event) {
      if (event.length > 0) {
        this.sortColumn = event[0].key;
        this.sortDirection = event[0].order;
      }
    },
    
    // Payroll CRUD methods
    showCreatePayrollModal() {
      this.resetPayrollForm();
      this.selectedPayrollId = null;
      this.payrollDialog = true;
    },
    
    editPayroll(id) {
      this.selectedPayrollId = id;
      const payroll = this.payrollData.find(p => p.id === id);
      
      this.payrollForm = {
        user_id: payroll.user_id,
        month: payroll.month,
        year: payroll.year,
        basic_pay: payroll.basic_pay,
        payment_mode: payroll.payment_mode,
        bank: payroll.bank || '',
        bank_branch: payroll.bank_branch || '',
        bank_account: payroll.bank_account || '',
        earnings: [...payroll.earnings],
        deductions: [...payroll.deductions],
      };
      
      this.payrollDialog = true;
    },
    
    submitPayroll() {
      this.isSubmitting = true;
      
      // Calculate totals
      const grossPay = this.calculateGrossPay;
      const totalDeductions = this.calculateTotalDeductions;
      const netPay = this.calculateNetPay;
      
      // Create or update payroll object
      const payrollData = {
        ...this.payrollForm,
        gross_pay: grossPay,
        total_deductions: totalDeductions,
        net_pay: netPay,
      };
      
      // Simulate API call
      setTimeout(() => {
        if (this.isEditing) {
          // Update existing payroll
          const index = this.payrollData.findIndex(p => p.id === this.selectedPayrollId);
          this.payrollData[index] = {
            ...this.payrollData[index],
            ...payrollData,
          };
          
          this.showNotification('Payroll updated successfully', 'success');
        } else {
          // Create new payroll
          const newPayroll = {
            id: this.payrollData.length + 1,
            ...payrollData,
            user: this.employeeItems.find(e => e.value === payrollData.user_id),
            payment_status: 'Pending',
            created_at: new Date().toISOString(),
          };
          
          this.payrollData.push(newPayroll);
          this.showNotification('Payroll generated successfully', 'success');
        }
        
        this.isSubmitting = false;
        this.payrollDialog = false;
      }, 1000);
    },
    
    confirmDeletePayroll(id) {
      this.selectedPayrollId = id;
      this.deleteDialog = true;
    },
    
    deletePayroll() {
      this.isDeleting = true;
      
      // Simulate API call
      setTimeout(() => {
        const index = this.payrollData.findIndex(p => p.id === this.selectedPayrollId);
        this.payrollData.splice(index, 1);
        
        this.isDeleting = false;
        this.deleteDialog = false;
        this.showNotification('Payroll deleted successfully', 'success');
      }, 1000);
    },
    
    // Form manipulation methods
    resetPayrollForm() {
      this.payrollForm = {
        user_id: null,
        month: new Date().getMonth() + 1,
        year: new Date().getFullYear(),
        basic_pay: 0,
        payment_mode: 'Bank Transfer',
        bank: '',
        bank_branch: '',
        bank_account: '',
        earnings: [],
        deductions: [],
      };
    },
    
    addEarning() {
      this.payrollForm.earnings.push({ type: '', amount: 0 });
    },
    
    removeEarning(index) {
      this.payrollForm.earnings.splice(index, 1);
      this.calculatePayrollTotals();
    },
    
    addDeduction() {
      this.payrollForm.deductions.push({ type: '', amount: 0 });
    },
    
    removeDeduction(index) {
      this.payrollForm.deductions.splice(index, 1);
      this.calculatePayrollTotals();
    },
    
    calculatePayrollTotals() {
      // This function will be called when inputs change
      // The computed properties will handle the actual calculations
    },
    
    // Payslip methods
    viewPayslip(id) {
      this.selectedPayrollId = id;
      this.payslipDialog = true;
    },
    
    printPayslip(id) {
      this.selectedPayrollId = id;
      setTimeout(() => {
        this.printCurrentPayslip();
      }, 500);
    },
    
    // printCurrentPayslip() {
    //   const printContent = document.getElementById('payslipContent');
    //   const windowPrint = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
      
    //   windowPrint.document.write(`
    //     <html>
    //       <head>
    //         <title>Payslip - ${this.currentPayslip.user.firstname} ${this.currentPayslip.user.lastname}</title>
    //         <style>
    //           body { font-family: Arial, sans-serif; padding: 20px; }
    //           table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    //           th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    //           th { background-color: #f2f2f2; }
    //           .text-right { text-align: right; }
    //           .text-center { text-align: center; }
    //           .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
    //           .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
    //         </style>
    //       </head>
    //       <body>
    //         ${printContent.innerHTML}
    //       </body>
    //     </html>
    //   `);
      
    //   windowPrint.document.close();
    //   windowPrint.focus();
    //   windowPrint.print();
    //   windowPrint.close();
    // },
    
    emailPayslip(id) {
      this.selectedPayrollId = id;
      const payroll = this.payrollData.find(p => p.id === id);
      this.emailForm.to = payroll.user.email || '';
      this.emailForm.subject = `Payslip for ${this.getMonthName(payroll.month)} ${payroll.year}`;
      this.emailForm.message = `Dear ${payroll.user.firstname},\n\nPlease find attached your payslip for the month of ${this.getMonthName(payroll.month)} ${payroll.year}.\n\nRegards,\nHR Department`;
      this.emailDialog = true;
    },
    
    emailCurrentPayslip() {
      this.emailPayslip(this.selectedPayrollId);
    },
    
    sendEmail() {
      this.isSending = true;
      
      // Simulate API call
      setTimeout(() => {
        this.isSending = false;
        this.emailDialog = false;
        this.showNotification('Payslip sent successfully', 'success');
      }, 1000);
    },
    
    downloadPayslip() {
      // Simulate downloading PDF
      this.showNotification('Payslip downloaded successfully', 'success');
    },
    
    // Import/Export methods
    showImportModal() {
      this.selectedFile = null;
      this.importDialog = true;
    },
    
    handleFileUpload() {
      // Handle file upload
    },
    
    importExcel() {
      this.isSubmitting = true;
      
      // Simulate API call
      setTimeout(() => {
        this.isSubmitting = false;
        this.importDialog = false;
        this.showNotification('Payroll data imported successfully', 'success');
      }, 1500);
    },
    
    exportToExcel() {
      // Simulate Excel export
      this.showNotification('Payroll data exported to Excel', 'success');
    },
    
    exportToPDF() {
      // Simulate PDF export
      this.showNotification('Payroll data exported to PDF', 'success');
    },
    
    // Utility methods
    formatCurrency(value) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
      }).format(value);
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      }).format(date);
    },
    
    getMonthName(monthNumber) {
      const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ];
      return monthNames[monthNumber - 1];
    },
    
    calculateTotalAllowances(payroll) {
      return payroll.earnings ? payroll.earnings.reduce((total, earning) => total + earning.amount, 0) : 0;
    },
    
    getPaymentStatusColor(status) {
      const statusColors = {
        'Paid': 'success',
        'Pending': 'warning',
        'Failed': 'error',
        'Processing': 'info'
      };
      return statusColors[status] || 'grey';
    },
    
    getEmployeeName(payroll) {
      if (!payroll || !payroll.user) return 'N/A';
      return `${payroll.user.firstname} ${payroll.user.lastname}`;
    },
    
    getPeriodLabel(month, year) {
      return `${this.getMonthName(month)} ${year}`;
    },
    
    showNotification(text, color = 'success') {
      this.nackbar = {
        show: true,
        text,
        color
      };
    },


    async fetchEmployees() {
      const apiUrl = 'api/v1/users';

      try {
        const response = await axios.get(apiUrl);
        this.employeeItems = response.data.users.filter(employee => !employee.super_admin)
          .map(employee => ({
            id: employee.id,
            fullName: `${employee.firstname} ${employee.lastname}`,
          }));
      } catch (error) {
        console.error('Error fetching employees:', error);
      }
    },

    
    resetFilters() {
      this.filters = {
        month: null,
        year: null,
        department: null,
        designation: null,
        country: null,
      };
      this.currentPage = 1;
    }
  },
  
  mounted() {
    // Fetch initial data
    this.isLoading = true;
    this.fetchEmployees();
    
    // Simulate API call
    setTimeout(() => {
      this.isLoading = false;
    }, 800);
  },
  
  watch: {
    'payrollForm.basic_pay'() {
      this.calculatePayrollTotals();
    },
    'payrollForm.earnings'() {
      this.calculatePayrollTotals();
    },
    'payrollForm.deductions'() {
      this.calculatePayrollTotals();
    }
  }
  
}
</script>

<style scoped>
.payroll-card {
  transition: all 0.2s ease;
}

.payroll-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.action-button {
  margin: 0 4px;
}

.status-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
  margin-right: 5px;
}

.status-paid {
  background-color: #4CAF50;
}

.status-pending {
  background-color: #FFC107;
}

.status-failed {
  background-color: #F44336;
}

@media print {
  .no-print {
    display: none;
  }
}
</style>