<template>
  <div class="settings-view">
    <div class="page-header">
      <h1>⚙️ Game Settings</h1>
      <p class="subtitle">Configure game mechanics, rates, and features</p>
    </div>

    <div class="settings-tabs">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        :class="['tab-btn', { active: activeTab === tab.id }]"
        @click="activeTab = tab.id"
      >
        {{ tab.icon }} {{ tab.label }}
      </button>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading settings...</p>
    </div>

    <form v-else @submit.prevent="saveSettings" class="settings-form">
      <!-- General Settings -->
      <div v-show="activeTab === 'general'" class="settings-section">
        <h2>🎮 General Settings</h2>
        
        <div class="setting-group">
          <label>Game Name</label>
          <input v-model="settings.game_name" type="text" placeholder="Gangster Legends">
          <span class="help-text">The name displayed throughout the game</span>
        </div>

        <div class="setting-group">
          <label>Registration Status</label>
          <div class="toggle-wrapper">
            <input v-model="settings.registration_enabled" type="checkbox" id="registration">
            <label for="registration" class="toggle-label">Allow new registrations</label>
          </div>
        </div>

        <div class="setting-group">
          <label>Maintenance Mode</label>
          <div class="toggle-wrapper">
            <input v-model="settings.maintenance_mode" type="checkbox" id="maintenance">
            <label for="maintenance" class="toggle-label">Enable maintenance mode</label>
          </div>
          <span class="help-text">Only admins can access the game when enabled</span>
        </div>

        <div class="setting-group">
          <label>Starting Cash</label>
          <input v-model.number="settings.starting_cash" type="number" min="0">
          <span class="help-text">Amount of cash new players start with</span>
        </div>

        <div class="setting-group">
          <label>Starting Health</label>
          <input v-model.number="settings.starting_health" type="number" min="1" max="100">
          <span class="help-text">Health percentage new players start with</span>
        </div>
      </div>

      <!-- Combat Settings -->
      <div v-show="activeTab === 'combat'" class="settings-section">
        <h2>⚔️ Combat Settings</h2>
        
        <div class="setting-group">
          <label>Attack Cooldown (seconds)</label>
          <input v-model.number="settings.attack_cooldown" type="number" min="0">
        </div>

        <div class="setting-group">
          <label>Minimum Attack Level</label>
          <input v-model.number="settings.min_attack_rank" type="number" min="1">
          <span class="help-text">Minimum rank required to attack other players</span>
        </div>

        <div class="setting-group">
          <label>Hospital Time Multiplier</label>
          <input v-model.number="settings.hospital_time_multiplier" type="number" min="0.1" step="0.1">
          <span class="help-text">Multiply hospital time by this factor</span>
        </div>

        <div class="setting-group">
          <label>Protection Period (hours)</label>
          <input v-model.number="settings.newbie_protection_hours" type="number" min="0">
          <span class="help-text">Hours of attack protection for new players</span>
        </div>
      </div>

      <!-- Economy Settings -->
      <div v-show="activeTab === 'economy'" class="settings-section">
        <h2>💰 Economy Settings</h2>
        
        <div class="setting-group">
          <label>Crime Payout Multiplier</label>
          <input v-model.number="settings.crime_payout_multiplier" type="number" min="0.1" step="0.1">
        </div>

        <div class="setting-group">
          <label>Bank Interest Rate (%)</label>
          <input v-model.number="settings.bank_interest_rate" type="number" min="0" max="100" step="0.1">
        </div>

        <div class="setting-group">
          <label>Property Income Multiplier</label>
          <input v-model.number="settings.property_income_multiplier" type="number" min="0.1" step="0.1">
        </div>

        <div class="setting-group">
          <label>Max Bank Balance</label>
          <input v-model.number="settings.max_bank_balance" type="number" min="0">
          <span class="help-text">Maximum amount players can store in bank (0 = unlimited)</span>
        </div>
      </div>

      <!-- Experience Settings -->
      <div v-show="activeTab === 'experience'" class="settings-section">
        <h2>📈 Experience & Progression</h2>
        
        <div class="setting-group">
          <label>XP Multiplier</label>
          <input v-model.number="settings.xp_multiplier" type="number" min="0.1" step="0.1">
          <span class="help-text">Global experience points multiplier</span>
        </div>

        <div class="setting-group">
          <label>Gym Gains Multiplier</label>
          <input v-model.number="settings.gym_gains_multiplier" type="number" min="0.1" step="0.1">
        </div>

        <div class="setting-group">
          <label>Energy Regeneration Rate</label>
          <input v-model.number="settings.energy_regen_rate" type="number" min="1">
          <span class="help-text">Energy points regenerated per minute</span>
        </div>

        <div class="setting-group">
          <label>Max Energy</label>
          <input v-model.number="settings.max_energy" type="number" min="1">
        </div>
      </div>

      <!-- Timer Settings -->
      <div v-show="activeTab === 'timers'" class="settings-section">
        <h2>⏱️ Timer Settings</h2>
        
        <div class="setting-group">
          <label>Crime Cooldown (seconds)</label>
          <input v-model.number="settings.crime_cooldown" type="number" min="0">
        </div>

        <div class="setting-group">
          <label>Gym Cooldown (seconds)</label>
          <input v-model.number="settings.gym_cooldown" type="number" min="0">
        </div>

        <div class="setting-group">
          <label>Travel Base Time (seconds)</label>
          <input v-model.number="settings.travel_base_time" type="number" min="0">
        </div>

        <div class="setting-group">
          <label>Organized Crime Cooldown (minutes)</label>
          <input v-model.number="settings.oc_cooldown_minutes" type="number" min="0">
        </div>
      </div>

      <!-- Feature Toggles -->
      <div v-show="activeTab === 'features'" class="settings-section">
        <h2>🔧 Feature Toggles</h2>
        
        <div class="feature-grid">
          <div class="feature-toggle">
            <div class="toggle-wrapper">
              <input v-model="settings.feature_crimes" type="checkbox" id="feature_crimes">
              <label for="feature_crimes" class="toggle-label">Crimes</label>
            </div>
          </div>

          <div class="feature-toggle">
            <div class="toggle-wrapper">
              <input v-model="settings.feature_gym" type="checkbox" id="feature_gym">
              <label for="feature_gym" class="toggle-label">Gym Training</label>
            </div>
          </div>

          <div class="feature-toggle">
            <div class="toggle-wrapper">
              <input v-model="settings.feature_combat" type="checkbox" id="feature_combat">
              <label for="feature_combat" class="toggle-label">Player Combat</label>
            </div>
          </div>

          <div class="feature-toggle">
            <div class="toggle-wrapper">
              <input v-model="settings.feature_gangs" type="checkbox" id="feature_gangs">
              <label for="feature_gangs" class="toggle-label">Gangs</label>
            </div>
          </div>

          <div class="feature-toggle">
            <div class="toggle-wrapper">
              <input v-model="settings.feature_properties" type="checkbox" id="feature_properties">
              <label for="feature_properties" class="toggle-label">Properties</label>
            </div>
          </div>

          <div class="feature-toggle">
            <div class="toggle-wrapper">
              <input v-model="settings.feature_racing" type="checkbox" id="feature_racing">
              <label for="feature_racing" class="toggle-label">Racing</label>
            </div>
          </div>

          <div class="feature-toggle">
            <div class="toggle-wrapper">
              <input v-model="settings.feature_missions" type="checkbox" id="feature_missions">
              <label for="feature_missions" class="toggle-label">Missions</label>
            </div>
          </div>

          <div class="feature-toggle">
            <div class="toggle-wrapper">
              <input v-model="settings.feature_achievements" type="checkbox" id="feature_achievements">
              <label for="feature_achievements" class="toggle-label">Achievements</label>
            </div>
          </div>

          <div class="feature-toggle">
            <div class="toggle-wrapper">
              <input v-model="settings.feature_forum" type="checkbox" id="feature_forum">
              <label for="feature_forum" class="toggle-label">Forum</label>
            </div>
          </div>

          <div class="feature-toggle">
            <div class="toggle-wrapper">
              <input v-model="settings.feature_travel" type="checkbox" id="feature_travel">
              <label for="feature_travel" class="toggle-label">Travel</label>
            </div>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="button" class="btn-reset" @click="resetSettings">
          🔄 Reset to Defaults
        </button>
        <button type="submit" class="btn-save" :disabled="saving">
          {{ saving ? '💾 Saving...' : '💾 Save Settings' }}
        </button>
      </div>
    </form>

    <div v-if="message" :class="['message', message.type]">
      {{ message.text }}
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'

const loading = ref(true)
const saving = ref(false)
const activeTab = ref('general')
const message = ref(null)

const tabs = [
  { id: 'general', label: 'General', icon: '🎮' },
  { id: 'combat', label: 'Combat', icon: '⚔️' },
  { id: 'economy', label: 'Economy', icon: '💰' },
  { id: 'experience', label: 'Experience', icon: '📈' },
  { id: 'timers', label: 'Timers', icon: '⏱️' },
  { id: 'features', label: 'Features', icon: '🔧' }
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
