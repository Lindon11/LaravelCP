<template>
  <div class="space-y-6">
    <!-- License Status Card -->
    <div :class="[
      'rounded-2xl backdrop-blur border p-6',
      license.licensed
        ? 'bg-emerald-900/20 border-emerald-600/30'
        : 'bg-red-900/20 border-red-600/30'
    ]">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div :class="[
            'w-14 h-14 rounded-xl flex items-center justify-center',
            license.licensed
              ? 'bg-emerald-500/20 text-emerald-400'
              : 'bg-red-500/20 text-red-400'
          ]">
            <ShieldCheckIcon v-if="license.licensed" class="w-8 h-8" />
            <ExclamationTriangleIcon v-else class="w-8 h-8" />
          </div>
          <div>
            <h2 class="text-xl font-bold text-white">
              {{ license.licensed ? 'License Active' : 'No Valid License' }}
            </h2>
            <p class="text-sm text-slate-400">
              {{ license.licensed
                ? `Licensed to ${license.customer}`
                : 'Enter your LaravelCP license key to activate.' }}
            </p>
          </div>
        </div>
        <span :class="[
          'px-4 py-1.5 rounded-full text-sm font-semibold uppercase tracking-wide',
          license.licensed
            ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
            : 'bg-red-500/20 text-red-400 border border-red-500/30'
        ]">
          {{ license.licensed ? license.tier : 'Unlicensed' }}
        </span>
      </div>
    </div>

    <!-- License Details (when licensed) -->
    <div v-if="license.licensed" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
      <div class="flex items-center gap-3 mb-6">
        <KeyIcon class="w-6 h-6 text-amber-400" />
        <h3 class="text-lg font-semibold text-white">License Details</h3>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div class="flex justify-between items-center py-3 border-b border-slate-700/50">
            <span class="text-sm text-slate-400">License Key</span>
            <code class="text-sm text-amber-400 bg-slate-900/50 px-3 py-1 rounded-lg font-mono">{{ license.key }}</code>
          </div>
          <div class="flex justify-between items-center py-3 border-b border-slate-700/50">
            <span class="text-sm text-slate-400">Tier</span>
            <span class="text-sm text-white font-medium capitalize">{{ license.tier }}</span>
          </div>
          <div class="flex justify-between items-center py-3 border-b border-slate-700/50">
            <span class="text-sm text-slate-400">Customer</span>
            <span class="text-sm text-white font-medium">{{ license.customer }}</span>
          </div>
          <div class="flex justify-between items-center py-3 border-b border-slate-700/50">
            <span class="text-sm text-slate-400">Email</span>
            <span class="text-sm text-white font-medium">{{ license.email }}</span>
          </div>
        </div>
        <div class="space-y-4">
          <div class="flex justify-between items-center py-3 border-b border-slate-700/50">
            <span class="text-sm text-slate-400">Domain</span>
            <span class="text-sm text-white font-medium">{{ license.domain }}</span>
          </div>
          <div class="flex justify-between items-center py-3 border-b border-slate-700/50">
            <span class="text-sm text-slate-400">Issued</span>
            <span class="text-sm text-white font-medium">{{ formatDate(license.issued) }}</span>
          </div>
          <div class="flex justify-between items-center py-3 border-b border-slate-700/50">
            <span class="text-sm text-slate-400">Expires</span>
            <span :class="[
              'text-sm font-medium',
              isExpiringSoon ? 'text-amber-400' : 'text-white'
            ]">{{ license.expires === 'never' ? 'Never' : formatDate(license.expires) }}</span>
          </div>
          <div class="flex justify-between items-center py-3 border-b border-slate-700/50">
            <span class="text-sm text-slate-400">Max Users</span>
            <span class="text-sm text-white font-medium">{{ license.max_users === 'unlimited' ? 'Unlimited' : license.max_users }}</span>
          </div>
        </div>
      </div>

      <!-- Deactivate Button -->
      <div class="mt-6 flex justify-end">
        <button
          @click="showDeactivateConfirm = true"
          class="px-4 py-2 rounded-xl text-sm font-medium text-red-400 border border-red-500/30 hover:bg-red-500/10 transition-all"
        >
          Deactivate License
        </button>
      </div>
    </div>

    <!-- Activate License Form -->
    <div v-if="!license.licensed" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
      <div class="flex items-center gap-3 mb-6">
        <KeyIcon class="w-6 h-6 text-amber-400" />
        <h3 class="text-lg font-semibold text-white">Activate License</h3>
      </div>

      <form @submit.prevent="activateLicense" class="space-y-4">
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-300">License Key</label>
          <input
            v-model="licenseKey"
            type="text"
            placeholder="LCP-STANDARD-xxxxxxx-xxxxxxxx"
            class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-500 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
          />
          <p class="text-xs text-slate-400">Enter the license key you received when purchasing LaravelCP.</p>
        </div>

        <div v-if="activateError" class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
          {{ activateError }}
        </div>

        <button
          type="submit"
          :disabled="!licenseKey || activating"
          :class="[
            'px-6 py-3 rounded-xl text-sm font-semibold text-white transition-all',
            !licenseKey || activating
              ? 'bg-slate-600 cursor-not-allowed opacity-50'
              : 'bg-gradient-to-r from-amber-500 to-orange-600 hover:shadow-lg hover:shadow-amber-500/25 hover:-translate-y-0.5'
          ]"
        >
          <span v-if="activating" class="flex items-center gap-2">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Validating...
          </span>
          <span v-else>Activate License</span>
        </button>
      </form>
    </div>

    <!-- License Tiers Info -->
    <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
      <div class="flex items-center gap-3 mb-6">
        <SparklesIcon class="w-6 h-6 text-amber-400" />
        <h3 class="text-lg font-semibold text-white">License Tiers</h3>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-xl bg-slate-900/50 border border-slate-700/30 p-5">
          <div class="flex items-center gap-2 mb-3">
            <span class="w-3 h-3 rounded-full bg-blue-400"></span>
            <h4 class="text-white font-semibold">Standard</h4>
          </div>
          <ul class="space-y-2 text-sm text-slate-400">
            <li class="flex items-center gap-2">
              <CheckIcon class="w-4 h-4 text-emerald-400 shrink-0" />
              Single domain
            </li>
            <li class="flex items-center gap-2">
              <CheckIcon class="w-4 h-4 text-emerald-400 shrink-0" />
              Core plugins
            </li>
            <li class="flex items-center gap-2">
              <CheckIcon class="w-4 h-4 text-emerald-400 shrink-0" />
              Community support
            </li>
          </ul>
        </div>

        <div class="rounded-xl bg-slate-900/50 border border-amber-500/20 p-5 ring-1 ring-amber-500/20">
          <div class="flex items-center gap-2 mb-3">
            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
            <h4 class="text-white font-semibold">Extended</h4>
            <span class="text-xs bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full">Popular</span>
          </div>
          <ul class="space-y-2 text-sm text-slate-400">
            <li class="flex items-center gap-2">
              <CheckIcon class="w-4 h-4 text-emerald-400 shrink-0" />
              Up to 5 domains
            </li>
            <li class="flex items-center gap-2">
              <CheckIcon class="w-4 h-4 text-emerald-400 shrink-0" />
              All plugins included
            </li>
            <li class="flex items-center gap-2">
              <CheckIcon class="w-4 h-4 text-emerald-400 shrink-0" />
              Priority support
            </li>
          </ul>
        </div>

        <div class="rounded-xl bg-slate-900/50 border border-purple-500/20 p-5">
          <div class="flex items-center gap-2 mb-3">
            <span class="w-3 h-3 rounded-full bg-purple-400"></span>
            <h4 class="text-white font-semibold">Unlimited</h4>
          </div>
          <ul class="space-y-2 text-sm text-slate-400">
            <li class="flex items-center gap-2">
              <CheckIcon class="w-4 h-4 text-emerald-400 shrink-0" />
              Unlimited domains
            </li>
            <li class="flex items-center gap-2">
              <CheckIcon class="w-4 h-4 text-emerald-400 shrink-0" />
              All plugins + future
            </li>
            <li class="flex items-center gap-2">
              <CheckIcon class="w-4 h-4 text-emerald-400 shrink-0" />
              Dedicated support
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Deactivate Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showDeactivateConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDeactivateConfirm = false"></div>
        <div class="relative bg-slate-800 border border-slate-700 rounded-2xl p-6 max-w-md w-full shadow-2xl">
          <h3 class="text-lg font-bold text-white mb-2">Deactivate License?</h3>
          <p class="text-sm text-slate-400 mb-6">
            This will remove the license key from this installation. You can re-activate it at any time with a valid key.
          </p>
          <div class="flex gap-3 justify-end">
            <button
              @click="showDeactivateConfirm = false"
              class="px-4 py-2 rounded-xl text-sm font-medium text-slate-300 border border-slate-600 hover:bg-slate-700/50 transition-all"
            >
              Cancel
            </button>
            <button
              @click="deactivateLicense"
              :disabled="deactivating"
              class="px-4 py-2 rounded-xl text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-all"
            >
              {{ deactivating ? 'Removing...' : 'Deactivate' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import {
  ShieldCheckIcon,
  ExclamationTriangleIcon,
  KeyIcon,
  SparklesIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

const { showToast } = useToast()

const loading = ref(true)
const licenseKey = ref('')
const activating = ref(false)
const activateError = ref('')
const deactivating = ref(false)
const showDeactivateConfirm = ref(false)

const license = ref({
  licensed: false,
  key: '',
  tier: '',
  customer: '',
  email: '',
  domain: '',
  issued: null,
  expires: 'never',
  max_users: 'unlimited',
  plugins: '*'
})

const isExpiringSoon = computed(() => {
  if (!license.value.expires || license.value.expires === 'never') return false
  const expiry = new Date(license.value.expires)
  const now = new Date()
  const daysLeft = (expiry - now) / (1000 * 60 * 60 * 24)
  return daysLeft > 0 && daysLeft <= 30
})

const formatDate = (dateStr) => {
  if (!dateStr || dateStr === 'never') return 'Never'
  try {
    return new Date(dateStr).toLocaleDateString('en-GB', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    })
  } catch {
    return dateStr
  }
}

const fetchLicenseStatus = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/license/status')
    license.value = response.data
  } catch (err) {
    console.error('Failed to fetch license status:', err)
  } finally {
    loading.value = false
  }
}

const activateLicense = async () => {
  activating.value = true
  activateError.value = ''
  try {
    const response = await api.post('/admin/license/activate', {
      license_key: licenseKey.value.trim()
    })
    showToast(response.data.message || 'License activated!', 'success')
    licenseKey.value = ''
    await fetchLicenseStatus()
  } catch (err) {
    activateError.value = err.response?.data?.error || 'Failed to activate license. Please check your key.'
  } finally {
    activating.value = false
  }
}

const deactivateLicense = async () => {
  deactivating.value = true
  try {
    await api.delete('/admin/license/deactivate')
    showToast('License deactivated.', 'info')
    showDeactivateConfirm.value = false
    await fetchLicenseStatus()
  } catch (err) {
    showToast('Failed to deactivate license.', 'error')
  } finally {
    deactivating.value = false
  }
}

onMounted(() => {
  fetchLicenseStatus()
})
</script>
