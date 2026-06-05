<template>
  <AuthenticatedLayout>
    <div class="p-6">

      <!-- ── Page Header ─────────────────────────────────────────────── -->
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold text-gray-800">Bulk Leave Balance</h1>
        <a :href="route('leaves.bulk-balance.template')"
          class="flex items-center gap-1.5 text-sm border border-gray-300 text-gray-600 hover:bg-gray-50 px-3 py-2 rounded-md transition">
          ↓ Download Template
        </a>
      </div>

      <!-- Flash messages -->
      <div v-if="$page.props.flash?.success"
        class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-md">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.errors?.bulk"
        class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-md">
        {{ $page.props.errors.bulk }}
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

        <!-- ── Left: Bulk Assign Form (3 cols) ─────────────────────── -->
        <div class="xl:col-span-3 bg-white rounded-lg border border-gray-200 p-6">
          <h2 class="text-sm font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">
            Assign Leave Balance
          </h2>

          <form @submit.prevent="submitBulkAssign" class="space-y-5">

            <!-- Label -->
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">
                Label <span class="text-gray-400 font-normal">(optional)</span>
              </label>
              <input v-model="form.label" type="text"
                placeholder="e.g. June 2026 Monthly Accrual"
                class="boxleo-input" />
            </div>

            <!-- Leave Type + Year -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Leave Type *</label>
                <select v-model="form.leave_type_id" class="boxleo-input" required>
                  <option value="">Select type...</option>
                  <option v-for="lt in leaveTypes" :key="lt.id" :value="lt.id">{{ lt.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Year *</label>
                <select v-model="form.year" class="boxleo-input" required>
                  <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                </select>
              </div>
            </div>

            <!-- Days -->
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Number of Days *</label>
              <input v-model="form.days" type="number" step="0.5" min="0"
                placeholder="e.g. 1.5"
                class="boxleo-input" required />
              <p class="text-xs text-gray-400 mt-1">Supports half-days (e.g. 1.5)</p>
            </div>

            <!-- Action toggle -->
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-2">Action *</label>
              <div class="flex border border-gray-200 rounded-md overflow-hidden w-fit">
                <button type="button" @click="form.action = 'add'"
                  class="px-4 py-2 text-sm font-medium transition"
                  :class="form.action === 'add' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'">
                  + Add to balance
                </button>
                <button type="button" @click="form.action = 'set'"
                  class="px-4 py-2 text-sm font-medium transition border-l border-gray-200"
                  :class="form.action === 'set' ? 'bg-orange-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'">
                  = Set balance
                </button>
              </div>
              <p v-if="form.action === 'set'" class="text-xs text-orange-500 mt-1.5">
                ⚠️ This will overwrite existing balances for all affected employees.
              </p>
            </div>

            <!-- Scope -->
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-2">Apply To *</label>
              <div class="flex border border-gray-200 rounded-md overflow-hidden w-fit">
                <button type="button" @click="form.scope = 'all'; previewCount = null"
                  class="px-4 py-2 text-sm font-medium transition"
                  :class="form.scope === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'">
                  All Countries
                </button>
                <button type="button" @click="form.scope = 'country'; previewCount = null"
                  class="px-4 py-2 text-sm font-medium transition border-l border-gray-200"
                  :class="form.scope === 'country' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'">
                  By Country
                </button>
              </div>
            </div>

            <!-- Country dropdown (conditional) -->
            <div v-if="form.scope === 'country'">
              <label class="block text-xs font-semibold text-gray-600 mb-1">Country *</label>
              <select v-model="form.country" class="boxleo-input"
                @change="previewCount = null" required>
                <option value="">Select country...</option>
                <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
              </select>
            </div>

            <!-- Preview bar -->
            <div v-if="previewCount !== null"
              class="flex items-center gap-2 bg-blue-50 border border-blue-200 text-blue-700 text-sm px-4 py-2.5 rounded-md">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              This will affect <strong class="mx-1">{{ previewCount }}</strong> active employee(s).
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-1">
              <button type="button" @click="fetchPreview"
                :disabled="loadingPreview || (form.scope === 'country' && !form.country)"
                class="border border-blue-400 text-blue-600 hover:bg-blue-50 text-sm font-medium px-4 py-2 rounded-md transition disabled:opacity-40">
                {{ loadingPreview ? 'Checking...' : 'Preview' }}
              </button>
              <button type="submit"
                :disabled="form.processing || previewCount === null"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2 rounded-md transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
                <span v-if="form.processing">Processing...</span>
                <span v-else>Apply to {{ previewCount ?? '?' }} Employees</span>
              </button>
            </div>

          </form>
        </div>

        <!-- ── Right: Recent Actions (2 cols) ──────────────────────── -->
        <div class="xl:col-span-2 bg-white rounded-lg border border-gray-200 p-6">
          <h2 class="text-sm font-semibold text-gray-700 mb-4 pb-3 border-b border-gray-100">
            Recent Bulk Actions
          </h2>

          <div v-if="recentImports.length === 0" class="text-center py-10 text-gray-400 text-sm">
            No bulk actions yet.
          </div>

          <div v-else class="space-y-3">
            <div v-for="imp in recentImports" :key="imp.id"
              class="border border-gray-100 rounded-md p-3 text-sm hover:bg-gray-50 transition">

              <div class="flex items-start justify-between mb-1.5">
                <p class="font-medium text-gray-800 text-xs leading-snug">{{ imp.label }}</p>
                <span class="text-xs px-2 py-0.5 rounded-full shrink-0 ml-2"
                  :class="{
                    'bg-green-100 text-green-700': imp.status === 'completed',
                    'bg-yellow-100 text-yellow-700': imp.status === 'processing',
                    'bg-red-100 text-red-700': imp.status === 'failed',
                  }">
                  {{ imp.status }}
                </span>
              </div>

              <p class="text-xs text-gray-400 mb-2">
                {{ imp.country || 'All Countries' }} ·
                {{ imp.creator?.name }} ·
                {{ fmtDate(imp.created_at) }}
              </p>

              <div class="flex gap-3 text-xs">
                <span class="text-gray-500">Total: <strong class="text-gray-700">{{ imp.total_records }}</strong></span>
                <span class="text-green-600 font-medium">✓ {{ imp.success_count }}</span>
                <span v-if="imp.failed_count > 0" class="text-red-500 font-medium">✗ {{ imp.failed_count }}</span>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  leaveTypes:    Array,
  countries:     Array,
  recentImports: Array,
})

const previewCount  = ref(null)
const loadingPreview = ref(false)
const currentYear   = new Date().getFullYear()
const yearOptions   = [currentYear - 1, currentYear, currentYear + 1]

const form = useForm({
  label:         '',
  leave_type_id: '',
  days:          '',
  action:        'add',
  scope:         'all',
  country:       '',
  year:          currentYear,
})

async function fetchPreview() {
  if (form.scope === 'country' && !form.country) return
  loadingPreview.value = true
  try {
    const { data } = await axios.post(route('leaves.bulk-balance.preview'), {
      scope: form.scope, country: form.country,
    })
    previewCount.value = data.count
  } finally {
    loadingPreview.value = false
  }
}

function submitBulkAssign() {
  if (!confirm(`Apply leave balance changes to ${previewCount.value} employee(s)?\n\nThis cannot be undone.`)) return
  form.post(route('leaves.bulk-balance.assign'), {
    onSuccess: () => { form.reset(); previewCount.value = null; form.action = 'add'; form.scope = 'all'; form.year = currentYear }
  })
}

function fmtDate(d) {
  return new Date(d).toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' })
}
</script>

<style scoped>
.boxleo-input {
  @apply w-full border border-gray-300 rounded-md px-3 py-2 text-sm
         focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none;
}
</style>
