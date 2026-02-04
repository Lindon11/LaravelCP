<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white flex items-center gap-3">
          <div class="p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600">
            <Cog6ToothIcon class="w-6 h-6 text-white" />
          </div>
          Game Settings
        </h1>
        <p class="text-sm text-slate-400 mt-1">Configure game mechanics, rates, and features</p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-2">
      <div class="flex flex-wrap gap-2">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="[
            'px-4 py-2 rounded-xl text-sm font-medium transition-all flex items-center gap-2',
            activeTab === tab.id
              ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/25'
              : 'text-slate-400 hover:text-white hover:bg-slate-700/50'
          ]"
          @click="activeTab = tab.id"
        >
          <component :is="tab.icon" class="w-5 h-5" />
          {{ tab.label }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-8">
      <div class="flex items-center justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500"></div>
        <span class="ml-3 text-slate-400">Loading settings...</span>
      </div>
    </div>

    <form v-else @submit.prevent="saveSettings" class="space-y-6">
      <!-- General Settings -->
      <div v-show="activeTab === 'general'" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <div class="flex items-center gap-3 mb-6">
          <Cog6ToothIcon class="w-6 h-6 text-amber-400" />
          <h2 class="text-xl font-semibold text-white">General Settings</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Game Name</label>
            <input
              v-model="settings.game_name"
              type="text"
              placeholder="Gangster Legends"
              class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
            >
            <p class="text-xs text-slate-400">The name displayed throughout the game</p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Starting Cash</label>
            <div class="relative">
              <span class="absolute left-4 top-3.5 text-slate-400">$</span>
              <input
                v-model.number="settings.starting_cash"
                type="number"
                min="0"
                class="w-full pl-8 pr-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
              >
            </div>
            <p class="text-xs text-slate-400">Amount of cash new players start with</p>
          </div>

          <div class="space-y-2">
            <label class="flex items-center justify-between">
              <span class="text-sm font-medium text-slate-300">Registration Status</span>
              <div class="relative">
                <input
                  v-model="settings.registration_enabled"
                  type="checkbox"
                  class="sr-only peer"
                  id="registration"
                >
                <label
                  for="registration"
                  class="relative flex h-6 w-11 cursor-pointer items-center rounded-full bg-slate-600 px-0.5 outline-none transition-colors duration-200 ease-in-out focus-visible:ring focus-visible:ring-amber-500 focus-visible:ring-opacity-75 peer-checked:bg-gradient-to-r peer-checked:from-amber-500 peer-checked:to-orange-600"
                >
                  <span class="sr-only">Enable registration</span>
                  <span class="h-5 w-5 rounded-full bg-white shadow-lg transition-transform duration-200 ease-in-out peer-checked:translate-x-5"></span>
                </label>
              </div>
            </label>
            <p class="text-xs text-slate-400">Allow new user registrations</p>
          </div>

          <div class="space-y-2">
            <label class="flex items-center justify-between">
              <span class="text-sm font-medium text-slate-300">Maintenance Mode</span>
              <div class="relative">
                <input
                  v-model="settings.maintenance_mode"
                  type="checkbox"
                  class="sr-only peer"
                  id="maintenance"
                >
                <label
                  for="maintenance"
                  class="relative flex h-6 w-11 cursor-pointer items-center rounded-full bg-slate-600 px-0.5 outline-none transition-colors duration-200 ease-in-out focus-visible:ring focus-visible:ring-amber-500 focus-visible:ring-opacity-75 peer-checked:bg-gradient-to-r peer-checked:from-amber-500 peer-checked:to-orange-600"
                >
                  <span class="sr-only">Enable maintenance</span>
                  <span class="h-5 w-5 rounded-full bg-white shadow-lg transition-transform duration-200 ease-in-out peer-checked:translate-x-5"></span>
                </label>
              </div>
            </label>
            <p class="text-xs text-slate-400">Only admins can access when enabled</p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Starting Health</label>
            <div class="relative">
              <input
                v-model.number="settings.starting_health"
                type="number"
                min="1"
                max="100"
                class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
              >
              <span class="absolute right-4 top-3.5 text-slate-400">%</span>
            </div>
            <p class="text-xs text-slate-400">Health percentage new players start with</p>
          </div>
        </div>
      </div>

      <!-- Combat Settings -->
      <div v-show="activeTab === 'combat'" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <div class="flex items-center gap-3 mb-6">
          <FireIcon class="w-6 h-6 text-amber-400" />
          <h2 class="text-xl font-semibold text-white">Combat Settings</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Attack Cooldown (seconds)</label>
            <input v-model.number="settings.attack_cooldown" type="number" min="0" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Minimum Attack Level</label>
            <input v-model.number="settings.min_attack_rank" type="number" min="1" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
            <p class="text-xs text-slate-400">Minimum rank required to attack other players</p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Hospital Time Multiplier</label>
            <input v-model.number="settings.hospital_time_multiplier" type="number" min="0.1" step="0.1" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
            <p class="text-xs text-slate-400">Multiply hospital time by this factor</p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Protection Period (hours)</label>
            <input v-model.number="settings.newbie_protection_hours" type="number" min="0" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
            <p class="text-xs text-slate-400">Hours of attack protection for new players</p>
          </div>
        </div>
      </div>

      <!-- Economy Settings -->
      <div v-show="activeTab === 'economy'" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <div class="flex items-center gap-3 mb-6">
          <BanknotesIcon class="w-6 h-6 text-amber-400" />
          <h2 class="text-xl font-semibold text-white">Economy Settings</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Crime Payout Multiplier</label>
            <input v-model.number="settings.crime_payout_multiplier" type="number" min="0.1" step="0.1" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Bank Interest Rate (%)</label>
            <input v-model.number="settings.bank_interest_rate" type="number" min="0" max="100" step="0.1" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Property Income Multiplier</label>
            <input v-model.number="settings.property_income_multiplier" type="number" min="0.1" step="0.1" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Max Bank Balance</label>
            <input v-model.number="settings.max_bank_balance" type="number" min="0" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
            <p class="text-xs text-slate-400">Maximum amount players can store in bank (0 = unlimited)</p>
          </div>
        </div>
      </div>

      <!-- Experience Settings -->
      <div v-show="activeTab === 'experience'" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <div class="flex items-center gap-3 mb-6">
          <ChartBarIcon class="w-6 h-6 text-amber-400" />
          <h2 class="text-xl font-semibold text-white">Experience & Progression</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">XP Multiplier</label>
            <input v-model.number="settings.xp_multiplier" type="number" min="0.1" step="0.1" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
            <p class="text-xs text-slate-400">Global experience points multiplier</p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Gym Gains Multiplier</label>
            <input v-model.number="settings.gym_gains_multiplier" type="number" min="0.1" step="0.1" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Energy Regeneration Rate</label>
            <input v-model.number="settings.energy_regen_rate" type="number" min="1" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
            <p class="text-xs text-slate-400">Energy points regenerated per minute</p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Max Energy</label>
            <input v-model.number="settings.max_energy" type="number" min="1" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
          </div>
        </div>
      </div>

      <!-- Timer Settings -->
      <div v-show="activeTab === 'timers'" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <div class="flex items-center gap-3 mb-6">
          <ClockIcon class="w-6 h-6 text-amber-400" />
          <h2 class="text-xl font-semibold text-white">Timer Settings</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Crime Cooldown (seconds)</label>
            <input v-model.number="settings.crime_cooldown" type="number" min="0" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Gym Cooldown (seconds)</label>
            <input v-model.number="settings.gym_cooldown" type="number" min="0" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Travel Base Time (seconds)</label>
            <input v-model.number="settings.travel_base_time" type="number" min="0" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Organized Crime Cooldown (minutes)</label>
            <input v-model.number="settings.oc_cooldown_minutes" type="number" min="0" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all">
          </div>
        </div>
      </div>

      <!-- Feature Toggles -->
      <div v-show="activeTab === 'features'" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <div class="flex items-center gap-3 mb-6">
          <WrenchScrewdriverIcon class="w-6 h-6 text-amber-400" />
          <h2 class="text-xl font-semibold text-white">Feature Toggles</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
          <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700/50">
            <div class="flex items-center gap-3">
              <input v-model="settings.feature_crimes" type="checkbox" id="feature_crimes" class="w-5 h-5 rounded accent-amber-500">
              <label for="feature_crimes" class="text-sm font-medium text-slate-300 cursor-pointer">Crimes</label>
            </div>
          </div>

          <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700/50">
            <div class="flex items-center gap-3">
              <input v-model="settings.feature_gym" type="checkbox" id="feature_gym" class="w-5 h-5 rounded accent-amber-500">
              <label for="feature_gym" class="text-sm font-medium text-slate-300 cursor-pointer">Gym Training</label>
            </div>
          </div>

          <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700/50">
            <div class="flex items-center gap-3">
              <input v-model="settings.feature_combat" type="checkbox" id="feature_combat" class="w-5 h-5 rounded accent-amber-500">
              <label for="feature_combat" class="text-sm font-medium text-slate-300 cursor-pointer">Player Combat</label>
            </div>
          </div>

          <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700/50">
            <div class="flex items-center gap-3">
              <input v-model="settings.feature_gangs" type="checkbox" id="feature_gangs" class="w-5 h-5 rounded accent-amber-500">
              <label for="feature_gangs" class="text-sm font-medium text-slate-300 cursor-pointer">Gangs</label>
            </div>
          </div>

          <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700/50">
            <div class="flex items-center gap-3">
              <input v-model="settings.feature_properties" type="checkbox" id="feature_properties" class="w-5 h-5 rounded accent-amber-500">
              <label for="feature_properties" class="text-sm font-medium text-slate-300 cursor-pointer">Properties</label>
            </div>
          </div>

          <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700/50">
            <div class="flex items-center gap-3">
              <input v-model="settings.feature_racing" type="checkbox" id="feature_racing" class="w-5 h-5 rounded accent-amber-500">
              <label for="feature_racing" class="text-sm font-medium text-slate-300 cursor-pointer">Racing</label>
            </div>
          </div>

          <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700/50">
            <div class="flex items-center gap-3">
              <input v-model="settings.feature_missions" type="checkbox" id="feature_missions" class="w-5 h-5 rounded accent-amber-500">
              <label for="feature_missions" class="text-sm font-medium text-slate-300 cursor-pointer">Missions</label>
            </div>
          </div>

          <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700/50">
            <div class="flex items-center gap-3">
              <input v-model="settings.feature_achievements" type="checkbox" id="feature_achievements" class="w-5 h-5 rounded accent-amber-500">
              <label for="feature_achievements" class="text-sm font-medium text-slate-300 cursor-pointer">Achievements</label>
            </div>
          </div>

          <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700/50">
            <div class="flex items-center gap-3">
              <input v-model="settings.feature_forum" type="checkbox" id="feature_forum" class="w-5 h-5 rounded accent-amber-500">
              <label for="feature_forum" class="text-sm font-medium text-slate-300 cursor-pointer">Forum</label>
            </div>
          </div>

          <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700/50">
            <div class="flex items-center gap-3">
              <input v-model="settings.feature_travel" type="checkbox" id="feature_travel" class="w-5 h-5 rounded accent-amber-500">
              <label for="feature_travel" class="text-sm font-medium text-slate-300 cursor-pointer">Travel</label>
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between pt-6 border-t border-slate-700/50">
        <button type="button" @click="resetSettings" class="flex items-center gap-2 px-4 py-2.5 bg-slate-700/50 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl font-medium transition-all">
          <ArrowPathIcon class="w-5 h-5" />
          Reset to Defaults
        </button>
        <button type="submit" :disabled="saving" class="flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all disabled:opacity-50">
          <CheckIcon class="w-5 h-5" />
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </div>
    </form>

    <div v-if="message" :class="[
      'mt-4 p-4 rounded-xl border',
      message.type === 'success' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-red-500/10 border-red-500/30 text-red-400'
    ]">
      {{ message.text }}
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import {
  Cog6ToothIcon,
  FireIcon,
  BanknotesIcon,
  ChartBarIcon,
  ClockIcon,
  WrenchScrewdriverIcon,
  ArrowPathIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

const loading = ref(true)
const saving = ref(false)
const activeTab = ref('general')
const message = ref(null)

const tabs = [
  { id: 'general', label: 'General', icon: Cog6ToothIcon },
  { id: 'combat', label: 'Combat', icon: FireIcon },
  { id: 'economy', label: 'Economy', icon: BanknotesIcon },
  { id: 'experience', label: 'Experience', icon: ChartBarIcon },
  { id: 'timers', label: 'Timers', icon: ClockIcon },
  { id: 'features', label: 'Features', icon: WrenchScrewdriverIcon }
]

const settings = reactive({
  // General
  game_name: 'Gangster Legends',
  registration_enabled: true,
  maintenance_mode: false,
  starting_cash: 1000,
  starting_health: 100,

  // Combat
  attack_cooldown: 300,
  min_attack_rank: 2,
  hospital_time_multiplier: 1.0,
  newbie_protection_hours: 24,

  // Economy
  crime_payout_multiplier: 1.0,
  bank_interest_rate: 1.0,
  property_income_multiplier: 1.0,
  max_bank_balance: 0,

  // Experience
  xp_multiplier: 1.0,
  gym_gains_multiplier: 1.0,
  energy_regen_rate: 5,
  max_energy: 100,

  // Timers
  crime_cooldown: 120,
  gym_cooldown: 300,
  travel_base_time: 600,
  oc_cooldown_minutes: 60,

  // Features
  feature_crimes: true,
  feature_gym: true,
  feature_combat: true,
  feature_gangs: true,
  feature_properties: true,
  feature_racing: true,
  feature_missions: true,
  feature_achievements: true,
  feature_forum: true,
  feature_travel: true
})

const loadSettings = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/settings')
    Object.assign(settings, response.data)
  } catch (error) {
    console.error('Error loading settings:', error)
  } finally {
    loading.value = false
  }
}

const saveSettings = async () => {
  saving.value = true
  message.value = null

  try {
    await api.post('/admin/settings', settings)
    message.value = { type: 'success', text: '✅ Settings saved successfully!' }
  } catch (error) {
    message.value = { type: 'error', text: error.response?.data?.message || 'Failed to save settings' }
  } finally {
    saving.value = false
    setTimeout(() => message.value = null, 5000)
  }
}

const resetSettings = () => {
  if (confirm('Are you sure you want to reset all settings to defaults? This cannot be undone.')) {
    loadSettings()
  }
}

onMounted(() => {
  loadSettings()
})
</script>

<style scoped>
.settings-view {
  padding: 2rem;
  max-width: 900px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  color: #f1f5f9;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #94a3b8;
}

.settings-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.tab-btn {
  padding: 0.75rem 1.25rem;
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.5rem;
  color: #94a3b8;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn:hover {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.tab-btn.active {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  border-color: transparent;
}

.loading-state {
  padding: 4rem;
  text-align: center;
  color: #94a3b8;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(59, 130, 246, 0.3);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.settings-section {
  background: rgba(30, 41, 59, 0.5);
  border-radius: 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.1);
  padding: 2rem;
  margin-bottom: 1.5rem;
}

.settings-section h2 {
  font-size: 1.25rem;
  color: #f1f5f9;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.setting-group {
  margin-bottom: 1.5rem;
}

.setting-group label {
  display: block;
  font-weight: 600;
  color: #f1f5f9;
  margin-bottom: 0.5rem;
}

.setting-group input[type="text"],
.setting-group input[type="number"] {
  width: 100%;
  max-width: 400px;
  padding: 0.875rem 1.125rem;
  background: rgba(15, 23, 42, 0.5);
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.625rem;
  color: #f1f5f9;
  font-size: 0.938rem;
  transition: all 0.2s ease;
}

.setting-group input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.help-text {
  display: block;
  font-size: 0.75rem;
  color: #64748b;
  margin-top: 0.5rem;
}

.toggle-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.toggle-wrapper input[type="checkbox"] {
  width: 20px;
  height: 20px;
  accent-color: #3b82f6;
}

.toggle-label {
  font-weight: normal !important;
  color: #cbd5e1;
  cursor: pointer;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.feature-toggle {
  padding: 1rem;
  background: rgba(15, 23, 42, 0.3);
  border-radius: 0.5rem;
  border: 1px solid rgba(148, 163, 184, 0.1);
}

.form-actions {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(148, 163, 184, 0.1);
}

.btn-save,
.btn-reset {
  padding: 1rem 2rem;
  border-radius: 0.625rem;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-save {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-save:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-reset {
  background: transparent;
  border: 1px solid #64748b;
  color: #94a3b8;
}

.btn-reset:hover {
  background: rgba(100, 116, 139, 0.1);
}

.message {
  margin-top: 1.5rem;
  padding: 1rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
}

.message.success {
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #10b981;
}

.message.error {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #ef4444;
}

@media (max-width: 768px) {
  .settings-tabs {
    justify-content: center;
  }

  .form-actions {
    flex-direction: column;
  }

  .btn-save,
  .btn-reset {
    width: 100%;
  }
}
</style>
