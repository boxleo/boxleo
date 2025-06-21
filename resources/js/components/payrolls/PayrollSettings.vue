<template>
  <v-app>
    <v-main>
      <v-container fluid>
        <v-row>
          <v-col cols="12">
            <v-card>
              <v-card-title class="text-h4 pa-6">
                <v-icon left class="mr-3">mdi-account-cash</v-icon>
                Payroll Settings Management
              </v-card-title>
              
              <v-card-text>
                <v-tabs v-model="activeTab" color="primary">
                  <v-tab value="earnings">
                    <v-icon left>mdi-plus-circle</v-icon>
                    Earnings
                  </v-tab>
                  <v-tab value="deductions">
                    <v-icon left>mdi-minus-circle</v-icon>
                    Deductions
                  </v-tab>
                </v-tabs>

                <v-window v-model="activeTab">
                  <!-- Earnings Tab -->
                  <v-window-item value="earnings">
                    <v-card flat>
                      <v-card-title class="d-flex justify-space-between align-center">
                        <span>Earnings Configuration</span>
                        <v-btn color="primary" @click="openDialog('earnings')">
                          <v-icon left>mdi-plus</v-icon>
                          Add Earning
                        </v-btn>
                      </v-card-title>
                      
                      <v-data-table
                        :headers="earningsHeaders"
                        :items="earnings"
                        :items-per-page="10"
                        class="elevation-1"
                      >
                        <template v-slot:item.amount="{ item }">
                          <span v-if="item.type === 'percentage'">{{ item.amount }}%</span>
                          <span v-else>KES {{ formatCurrency(item.amount) }}</span>
                        </template>
                        
                        <template v-slot:item.taxable="{ item }">
                          <v-chip :color="item.taxable ? 'success' : 'error'" small>
                            {{ item.taxable ? 'Taxable' : 'Non-taxable' }}
                          </v-chip>
                        </template>
                        
                        <template v-slot:item.actions="{ item }">
                          <v-btn icon size="small" @click="editItem('earnings', item)" class="mr-2">
                            <v-icon>mdi-pencil</v-icon>
                          </v-btn>
                          <v-btn icon size="small" @click="deleteItem('earnings', item.id)" color="error">
                            <v-icon>mdi-delete</v-icon>
                          </v-btn>
                        </template>
                      </v-data-table>
                    </v-card>
                  </v-window-item>

                  <!-- Deductions Tab -->
                  <v-window-item value="deductions">
                    <v-card flat>
                      <v-card-title class="d-flex justify-space-between align-center">
                        <span>Deductions Configuration</span>
                        <v-btn color="primary" @click="openDialog('deductions')">
                          <v-icon left>mdi-plus</v-icon>
                          Add Deduction
                        </v-btn>
                      </v-card-title>
                      
                      <v-data-table
                        :headers="deductionsHeaders"
                        :items="deductions"
                        :items-per-page="10"
                        class="elevation-1"
                      >
                        <template v-slot:item.amount="{ item }">
                          <span v-if="item.type === 'percentage'">{{ item.amount }}%</span>
                          <span v-else>KES {{ formatCurrency(item.amount) }}</span>
                        </template>
                        
                        <template v-slot:item.mandatory="{ item }">
                          <v-chip :color="item.mandatory ? 'warning' : 'info'" small>
                            {{ item.mandatory ? 'Mandatory' : 'Optional' }}
                          </v-chip>
                        </template>
                        
                        <template v-slot:item.actions="{ item }">
                          <v-btn icon size="small" @click="editItem('deductions', item)" class="mr-2">
                            <v-icon>mdi-pencil</v-icon>
                          </v-btn>
                          <v-btn icon size="small" @click="deleteItem('deductions', item.id)" color="error">
                            <v-icon>mdi-delete</v-icon>
                          </v-btn>
                        </template>
                      </v-data-table>
                    </v-card>
                  </v-window-item>
                </v-window>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <!-- Dialog for Add/Edit -->
        <v-dialog v-model="dialog" max-width="600px" persistent>
          <v-card>
            <v-card-title>
              <span class="text-h5">{{ dialogTitle }}</span>
            </v-card-title>
            
            <v-card-text>
              <v-form ref="form" v-model="valid">
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="editedItem.name"
                      label="Name"
                      :rules="nameRules"
                      required
                    ></v-text-field>
                  </v-col>
                  
                  <v-col cols="12">
                    <v-textarea
                      v-model="editedItem.description"
                      label="Description"
                      rows="2"
                    ></v-textarea>
                  </v-col>
                  
                 
                  <!-- Earnings specific fields -->
                  <template v-if="currentType === 'earnings'">
                    <v-col cols="6">
                      <v-switch
                        v-model="editedItem.taxable"
                        label="Taxable"
                        color="primary"
                      ></v-switch>
                    </v-col>
                    
                    <v-col cols="6">
                      <v-switch
                        v-model="editedItem.pensionable"
                        label="Pensionable"
                        color="primary"
                      ></v-switch>
                    </v-col>
                  </template>
                  
                  <!-- Deductions specific fields -->
                  <template v-if="currentType === 'deductions'">
                    <v-col cols="6">
                      <v-switch
                        v-model="editedItem.mandatory"
                        label="Mandatory"
                        color="primary"
                      ></v-switch>
                    </v-col>
                    
                    <v-col cols="6">
                      <v-switch
                        v-model="editedItem.taxDeductible"
                        label="Tax Deductible"
                        color="primary"
                      ></v-switch>
                    </v-col>
                  </template>
                  
                  <v-col cols="12">
                    <v-switch
                      v-model="editedItem.active"
                      label="Active"
                      color="success"
                    ></v-switch>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
            
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="grey" variant="text" @click="closeDialog">
                Cancel
              </v-btn>
              <v-btn color="primary" @click="saveItem" :disabled="!valid">
                Save
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="400px">
          <v-card>
            <v-card-title class="text-h5">Confirm Delete</v-card-title>
            <v-card-text>
              Are you sure you want to delete this item? This action cannot be undone.
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="grey" variant="text" @click="deleteDialog = false">
                Cancel
              </v-btn>
              <v-btn color="error" @click="confirmDelete">
                Delete
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Snackbar for notifications -->
        <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000">
          {{ snackbarText }}
          <template v-slot:actions>
            <v-btn variant="text" @click="snackbar = false">Close</v-btn>
          </template>
        </v-snackbar>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import { ref, computed, nextTick, onMounted } from 'vue'
import axios from 'axios'

export default {
  name: 'PayrollSettings',
  setup() {
    // Reactive data
    const activeTab = ref('earnings')
    const dialog = ref(false)
    const deleteDialog = ref(false)
    const valid = ref(false)
    const currentType = ref('')
    const editedIndex = ref(-1)
    const itemToDelete = ref(null)
    const snackbar = ref(false)
    const snackbarText = ref('')
    const snackbarColor = ref('success')
    const form = ref(null)

    // Default item structure
    const defaultEarning = () => ({
      id: null,
      name: '',
      description: '',
      type: 'fixed',
      amount: 0,
      taxable: true,
      pensionable: true,
      active: true
    })

    const defaultDeduction = () => ({
      id: null,
      name: '',
      description: '',
      type: 'fixed',
      amount: 0,
      mandatory: false,
      taxDeductible: false,
      active: true
    })

    const editedItem = ref(defaultEarning())

    // Data from API
    const earnings = ref([])
    const deductions = ref([])

    // Table headers
    const earningsHeaders = [
      { title: 'Name', key: 'name', sortable: true },
      { title: 'Description', key: 'description', sortable: false },
      // { title: 'Amount', key: 'amount', sortable: true },
      { title: 'Taxable', key: 'taxable', sortable: true },
      { title: 'Pensionable', key: 'pensionable', sortable: true },
      { title: 'Actions', key: 'actions', sortable: false }
    ]

    const deductionsHeaders = [
      { title: 'Name', key: 'name', sortable: true },
      { title: 'Description', key: 'description', sortable: false },
      { title: 'Type', key: 'mandatory', sortable: true },
      { title: 'Tax Deductible', key: 'taxDeductible', sortable: true },
      { title: 'Actions', key: 'actions', sortable: false }
    ]

    // Form options
    const amountTypes = [
      { title: 'Fixed Amount', value: 'fixed' },
      { title: 'Percentage', value: 'percentage' }
    ]

    // Validation rules
    const nameRules = [
      v => !!v || 'Name is required',
      v => (v && v.length >= 2) || 'Name must be at least 2 characters'
    ]

    const typeRules = [
      v => !!v || 'Amount type is required'
    ]

    const amountRules = [
      v => v !== null && v !== undefined && v !== '' || 'Amount is required',
      v => v >= 0 || 'Amount must be positive'
    ]

    // Computed properties
    const dialogTitle = computed(() => {
      const action = editedIndex.value === -1 ? 'Add' : 'Edit'
      const type = currentType.value === 'earnings' ? 'Earning' : 'Deduction'
      return `${action} ${type}`
    })

    // Methods
    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-KE').format(amount)
    }

    // API endpoints
    const apiBase = '/api'
    const earningsApi = `${apiBase}/v1/earnings`
    const deductionsApi = `${apiBase}/v1/deductions`

    // Fetch data from API
    const fetchEarnings = async () => {
      try {
        const res = await axios.get(earningsApi)
        earnings.value = res.data.data || res.data // adjust if API returns differently
      } catch (e) {
        showSnackbar('Failed to load earnings', 'error')
      }
    }

    const fetchDeductions = async () => {
      try {
        const res = await axios.get(deductionsApi)
        deductions.value = res.data.data || res.data
      } catch (e) {
        showSnackbar('Failed to load deductions', 'error')
      }
    }

    onMounted(() => {
      fetchEarnings()
      fetchDeductions()
    })

    const openDialog = (type) => {
      currentType.value = type
      editedIndex.value = -1
      editedItem.value = type === 'earnings' ? defaultEarning() : defaultDeduction()
      dialog.value = true
    }

    const editItem = (type, item) => {
      currentType.value = type
      editedIndex.value = type === 'earnings' ? 
        earnings.value.findIndex(e => e.id === item.id) :
        deductions.value.findIndex(d => d.id === item.id)
      editedItem.value = { ...item }
      dialog.value = true
    }

    const closeDialog = () => {
      dialog.value = false
      nextTick(() => {
        editedItem.value = currentType.value === 'earnings' ? defaultEarning() : defaultDeduction()
        editedIndex.value = -1
        if (form.value) {
          form.value.resetValidation()
        }
      })
    }

    const saveItem = async () => {
      if (!valid.value) return

      const isEdit = editedIndex.value > -1
      const type = currentType.value
      const apiUrl = type === 'earnings' ? earningsApi : deductionsApi
      const item = { ...editedItem.value }
      let successMsg = ''
      let errorMsg = ''

      try {
        if (isEdit) {
          // Update
          await axios.put(`${apiUrl}/${item.id}`, item)
          successMsg = `${type === 'earnings' ? 'Earning' : 'Deduction'} updated successfully`
        } else {
          // Create
          const res = await axios.post(apiUrl, item)
          item.id = res.data.data?.id || res.data.id // adjust if API returns differently
          successMsg = `${type === 'earnings' ? 'Earning' : 'Deduction'} added successfully`
        }
        // Refresh data
        if (type === 'earnings') {
          await fetchEarnings()
        } else {
          await fetchDeductions()
        }
        showSnackbar(successMsg, 'success')
        closeDialog()
      } catch (e) {
        errorMsg = isEdit
          ? `Failed to update ${type === 'earnings' ? 'earning' : 'deduction'}`
          : `Failed to add ${type === 'earnings' ? 'earning' : 'deduction'}`
        showSnackbar(errorMsg, 'error')
      }
    }

    const deleteItem = (type, id) => {
      itemToDelete.value = { type, id }
      deleteDialog.value = true
    }

    const confirmDelete = async () => {
      if (itemToDelete.value) {
        const { type, id } = itemToDelete.value
        const apiUrl = type === 'earnings' ? earningsApi : deductionsApi
        try {
          await axios.delete(`${apiUrl}/${id}`)
          if (type === 'earnings') {
            await fetchEarnings()
          } else {
            await fetchDeductions()
          }
          showSnackbar(`${type === 'earnings' ? 'Earning' : 'Deduction'} deleted successfully`, 'success')
        } catch (e) {
          showSnackbar(`Failed to delete ${type === 'earnings' ? 'earning' : 'deduction'}`, 'error')
        }
      }
      deleteDialog.value = false
      itemToDelete.value = null
    }

    const showSnackbar = (text, color = 'success') => {
      snackbarText.value = text
      snackbarColor.value = color
      snackbar.value = true
    }

    return {
      // Reactive refs
      activeTab,
      dialog,
      deleteDialog,
      valid,
      currentType,
      editedIndex,
      editedItem,
      earnings,
      deductions,
      snackbar,
      snackbarText,
      snackbarColor,
      form,
      
      // Static data
      earningsHeaders,
      deductionsHeaders,
      amountTypes,
      nameRules,
      typeRules,
      amountRules,
      
      // Computed
      dialogTitle,
      
      // Methods
      formatCurrency,
      openDialog,
      editItem,
      closeDialog,
      saveItem,
      deleteItem,
      confirmDelete,
      showSnackbar
    }
  }
}
</script>

<style scoped>
.v-application {
  background-color: #f5f5f5;
}
</style>