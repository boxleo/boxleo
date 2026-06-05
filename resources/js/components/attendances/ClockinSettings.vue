<template>
  <AuthenticatedLayout>
    <div class="p-6">

      <!-- ── Page Header (matches your existing pages) ─────────────────── -->
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold text-gray-800">Country Clock-in Settings</h1>
        <button @click="openAddCountry"
          class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">
          <span class="text-lg leading-none">+</span> Add Country
        </button>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success"
        class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-md">
        {{ $page.props.flash.success }}
      </div>

      <!-- ── Country Table ──────────────────────────────────────────────── -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
              <th class="px-4 py-3 text-left">Country</th>
              <th class="px-4 py-3 text-left">Timezone</th>
              <th class="px-4 py-3 text-left">Default Clock-in</th>
              <th class="px-4 py-3 text-left">Default Clock-out</th>
              <th class="px-4 py-3 text-left">Grace Period</th>
              <th class="px-4 py-3 text-left">Today's Effective Time</th>
              <th class="px-4 py-3 text-left">Overrides</th>
              <th class="px-4 py-3 text-left">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="settings.length === 0">
              <td colspan="8" class="text-center py-12 text-gray-400">
                No countries configured yet. Click "+ Add Country" to get started.
              </td>
            </tr>
            <tr v-for="s in settings" :key="s.id"
              class="border-b border-gray-100 hover:bg-gray-50 transition">
              <td class="px-4 py-3 font-medium text-gray-800">
                {{ flagEmoji(s.country_code) }} {{ s.country }}
              </td>
              <td class="px-4 py-3 text-gray-500 text-xs">{{ s.timezone }}</td>
              <td class="px-4 py-3 font-semibold text-blue-700">{{ fmt(s.default_clockin_time) }}</td>
              <td class="px-4 py-3 text-gray-600">{{ fmt(s.default_clockout_time) }}</td>
              <td class="px-4 py-3 text-gray-500">{{ s.grace_minutes }} min</td>
              <td class="px-4 py-3">
                <span class="font-semibold"
                  :class="s.has_today_override ? 'text-orange-600' : 'text-green-600'">
                  {{ fmt(s.today_clockin_time) }}
                </span>
                <span v-if="s.has_today_override"
                  class="ml-1.5 text-xs bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded">override</span>
              </td>
              <td class="px-4 py-3">
                <button @click="openOverrideList(s)"
                  class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-2.5 py-1 rounded transition">
                  {{ s.overrides.length }} override{{ s.overrides.length !== 1 ? 's' : '' }}
                </button>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <!-- Edit defaults -->
                  <button @click="openEditDefault(s)" title="Edit default times"
                    class="text-blue-500 hover:text-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <!-- Add override -->
                  <button @click="openAddOverride(s)" title="Add time override"
                    class="text-orange-500 hover:text-orange-700 transition font-bold text-base leading-none">
                    +
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ── Overrides Panel (slide-in style) ──────────────────────────── -->
      <div v-if="activeCountry && showOverrideList" class="mt-6 bg-white rounded-lg border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-gray-800">
            {{ flagEmoji(activeCountry.country_code) }} {{ activeCountry.country }} — Time Overrides
          </h2>
          <button @click="showOverrideList = false" class="text-gray-400 hover:text-gray-600 text-xl">×</button>
        </div>

        <div v-if="activeCountry.overrides.length === 0" class="text-sm text-gray-400 italic py-4 text-center">
          No overrides set — using default times every day.
        </div>

        <table v-else class="w-full text-sm mb-4">
          <thead>
            <tr class="text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
              <th class="pb-2 text-left">Date</th>
              <th class="pb-2 text-left">Clock-in</th>
              <th class="pb-2 text-left">Clock-out</th>
              <th class="pb-2 text-left">Type</th>
              <th class="pb-2 text-left">Reason</th>
              <th class="pb-2 text-left">Remove</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="o in activeCountry.overrides" :key="o.id"
              class="border-b border-gray-50 hover:bg-orange-50 transition">
              <td class="py-2 font-medium text-gray-800">{{ fmtDate(o.override_date) }}</td>
              <td class="py-2 font-bold text-orange-600">{{ fmt(o.clockin_time) }}</td>
              <td class="py-2 text-gray-500">{{ o.clockout_time ? fmt(o.clockout_time) : '—' }}</td>
              <td class="py-2">
                <span class="text-xs px-2 py-0.5 rounded-full"
                  :class="o.type === 'permanent' ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600'">
                  {{ o.type }}
                </span>
              </td>
              <td class="py-2 text-gray-500">{{ o.reason || '—' }}</td>
              <td class="py-2">
                <button @click="deleteOverride(o.id)"
                  class="text-red-400 hover:text-red-600 transition text-xs font-medium">Remove</button>
              </td>
            </tr>
          </tbody>
        </table>

        <button @click="openAddOverride(activeCountry)"
          class="flex items-center gap-1.5 text-sm text-orange-600 border border-orange-300 px-3 py-1.5 rounded hover:bg-orange-50 transition">
          <span class="font-bold">+</span> Add Override for {{ activeCountry.country }}
        </button>
      </div>

    </div>

    <!-- ── Modal: Add Country ─────────────────────────────────────────── -->
    <Modal :show="showAddCountry" @close="showAddCountry = false">
      <template #title>Add Country Clock-in Settings</template>
      <form @submit.prevent="submitAddCountry" class="space-y-4 p-1">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Country Name</label>
            <input v-model="countryForm.country" type="text" placeholder="e.g. Kenya"
              class="boxleo-input" required />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Country Code</label>
            <input v-model="countryForm.country_code" type="text" placeholder="e.g. KE" maxlength="5"
              class="boxleo-input" required />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Default Clock-in</label>
            <input v-model="countryForm.default_clockin_time" type="time" class="boxleo-input" required />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Default Clock-out</label>
            <input v-model="countryForm.default_clockout_time" type="time" class="boxleo-input" required />
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">
            Grace Period (minutes before marked Late)
          </label>
          <input v-model="countryForm.grace_minutes" type="number" min="0" max="60" placeholder="5"
            class="boxleo-input" required />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Timezone</label>
          <select v-model="countryForm.timezone" class="boxleo-input">
            <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="showAddCountry = false" class="boxleo-btn-ghost">Cancel</button>
          <button type="submit" :disabled="countryForm.processing" class="boxleo-btn-primary">Save</button>
        </div>
      </form>
    </Modal>

    <!-- ── Modal: Edit Default ────────────────────────────────────────── -->
    <Modal :show="showEditDefault" @close="showEditDefault = false">
      <template #title>Edit — {{ activeCountry?.country }}</template>
      <form @submit.prevent="submitEditDefault" class="space-y-4 p-1">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Default Clock-in</label>
            <input v-model="editForm.default_clockin_time" type="time" class="boxleo-input" required />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Default Clock-out</label>
            <input v-model="editForm.default_clockout_time" type="time" class="boxleo-input" required />
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Grace Period (minutes)</label>
          <input v-model="editForm.grace_minutes" type="number" min="0" max="60" class="boxleo-input" required />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Timezone</label>
          <select v-model="editForm.timezone" class="boxleo-input">
            <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="showEditDefault = false" class="boxleo-btn-ghost">Cancel</button>
          <button type="submit" :disabled="editForm.processing" class="boxleo-btn-primary">Save Changes</button>
        </div>
      </form>
    </Modal>

    <!-- ── Modal: Add Override ───────────────────────────────────────── -->
    <Modal :show="showAddOverride" @close="showAddOverride = false">
      <template #title>Add Override — {{ activeCountry?.country }}</template>
      <form @submit.prevent="submitOverride" class="space-y-4 p-1">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Date</label>
          <input v-model="overrideForm.override_date" type="date" class="boxleo-input" required />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Clock-in Time</label>
            <input v-model="overrideForm.clockin_time" type="time" class="boxleo-input" required />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Clock-out Time <span class="text-gray-400 font-normal">(optional)</span></label>
            <input v-model="overrideForm.clockout_time" type="time" class="boxleo-input" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Type</label>
          <div class="flex gap-6">
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" v-model="overrideForm.type" value="temporary" />
              Temporary <span class="text-gray-400 text-xs">(this date only)</span>
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="radio" v-model="overrideForm.type" value="permanent" />
              Permanent <span class="text-gray-400 text-xs">(ongoing)</span>
            </label>
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Reason <span class="text-gray-400 font-normal">(optional)</span></label>
          <input v-model="overrideForm.reason" type="text"
            placeholder="e.g. Public Holiday — Madaraka Day"
            class="boxleo-input" />
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="showAddOverride = false" class="boxleo-btn-ghost">Cancel</button>
          <button type="submit" :disabled="overrideForm.processing" class="boxleo-btn-orange">Save Override</button>
        </div>
      </form>
    </Modal>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({ settings: Array })

// State
const activeCountry     = ref(null)
const showAddCountry    = ref(false)
const showEditDefault   = ref(false)
const showAddOverride   = ref(false)
const showOverrideList  = ref(false)

// Forms
const countryForm = useForm({
  country: '', country_code: '',
  default_clockin_time: '08:00', default_clockout_time: '17:00',
  grace_minutes: 5, timezone: 'Africa/Nairobi',
})

const editForm = useForm({
  default_clockin_time: '', default_clockout_time: '',
  grace_minutes: 5, timezone: '',
})

const overrideForm = useForm({
  override_date: '', clockin_time: '', clockout_time: '',
  reason: '', type: 'temporary',
})

// Open helpers
function openAddCountry()       { showAddCountry.value = true }
function openOverrideList(s)    { activeCountry.value = s; showOverrideList.value = true }
function openEditDefault(s) {
  activeCountry.value = s
  editForm.default_clockin_time  = s.default_clockin_time
  editForm.default_clockout_time = s.default_clockout_time
  editForm.grace_minutes         = s.grace_minutes
  editForm.timezone              = s.timezone
  showEditDefault.value = true
}
function openAddOverride(s) {
  activeCountry.value = s
  overrideForm.reset()
  overrideForm.type = 'temporary'
  showAddOverride.value = true
}

// Submit handlers
function submitAddCountry() {
  countryForm.post(route('clockin-settings.store'), {
    onSuccess: () => { showAddCountry.value = false; countryForm.reset() }
  })
}
function submitEditDefault() {
  editForm.put(route('clockin-settings.update', activeCountry.value.id), {
    onSuccess: () => { showEditDefault.value = false }
  })
}
function submitOverride() {
  overrideForm.post(route('clockin-settings.overrides.store', activeCountry.value.id), {
    onSuccess: () => { showAddOverride.value = false; overrideForm.reset() }
  })
}
function deleteOverride(id) {
  if (!confirm('Remove this override?')) return
  router.delete(route('clockin-settings.overrides.destroy', id))
}

// Formatters
function fmt(t) {
  if (!t) return '—'
  const [h, m] = t.split(':')
  const hour = parseInt(h)
  return `${hour % 12 || 12}:${m} ${hour >= 12 ? 'PM' : 'AM'}`
}
function fmtDate(d) {
  return new Date(d + 'T00:00:00').toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' })
}
function flagEmoji(code) {
  if (!code) return ''
  return code.toUpperCase().replace(/./g, c => String.fromCodePoint(c.charCodeAt(0) + 127397))
}

const timezones = [
  'Africa/Nairobi','Africa/Kampala','Africa/Dar_es_Salaam','Africa/Lusaka',
  'Africa/Harare','Africa/Johannesburg','Africa/Lagos','Africa/Accra',
  'Europe/London','Europe/Paris','America/New_York','Asia/Dubai',
]
</script>

<style scoped>
.boxleo-input {
  @apply w-full border border-gray-300 rounded-md px-3 py-2 text-sm
         focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none;
}
.boxleo-btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md transition disabled:opacity-50;
}
.boxleo-btn-orange {
  @apply bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-md transition disabled:opacity-50;
}
.boxleo-btn-ghost {
  @apply border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-md transition;
}
</style>
