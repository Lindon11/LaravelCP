<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white flex items-center gap-3">
          <div class="p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600">
            <ShieldCheckIcon class="w-6 h-6 text-white" />
          </div>
          Security Settings
        </h1>
        <p class="text-sm text-slate-400 mt-1">Manage two-factor authentication, OAuth providers, and security policies</p>
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
              ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/25'
              : 'text-slate-400 hover:text-white hover:bg-slate-700/50'
          ]"
          @click="activeTab = tab.id"
        >
          <component :is="tab.icon" class="w-5 h-5" />
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- Two-Factor Authentication Settings -->
    <div v-show="activeTab === '2fa'" class="space-y-6">
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <div class="flex items-center gap-3 mb-6">
          <DevicePhoneMobileIcon class="w-6 h-6 text-emerald-400" />
          <h2 class="text-xl font-semibold text-white">Two-Factor Authentication</h2>
        </div>

        <div class="space-y-6">
          <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl">
            <div>
              <h3 class="font-medium text-white">Require 2FA for Admins</h3>
              <p class="text-sm text-slate-400">Force all admin users to enable 2FA</p>
            </div>
            <button
              type="button"
              @click="settings.require_2fa_admin = !settings.require_2fa_admin"
              :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                settings.require_2fa_admin ? 'bg-emerald-500' : 'bg-slate-600'
              ]"
            >
              <span :class="[
                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                settings.require_2fa_admin ? 'translate-x-6' : 'translate-x-1'
              ]" />
            </button>
          </div>

          <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl">
            <div>
              <h3 class="font-medium text-white">Allow 2FA for All Users</h3>
              <p class="text-sm text-slate-400">Let regular players enable 2FA on their accounts</p>
            </div>
            <button
              type="button"
              @click="settings.allow_2fa_users = !settings.allow_2fa_users"
              :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                settings.allow_2fa_users ? 'bg-emerald-500' : 'bg-slate-600'
              ]"
            >
              <span :class="[
                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                settings.allow_2fa_users ? 'translate-x-6' : 'translate-x-1'
              ]" />
            </button>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-300">Recovery Codes Count</label>
              <input
                v-model.number="settings.recovery_codes_count"
                type="number"
                min="4"
                max="16"
                class="w-full px-4 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
              >
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-300">Code Validity Window (seconds)</label>
              <input
                v-model.number="settings.totp_window"
                type="number"
                min="30"
                max="120"
                class="w-full px-4 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
              >
            </div>
          </div>
        </div>

        <!-- 2FA Stats -->
        <div class="mt-6 pt-6 border-t border-slate-700/50">
          <h3 class="text-lg font-medium text-white mb-4">2FA Statistics</h3>
          <div class="grid grid-cols-3 gap-4">
            <div class="p-4 bg-slate-900/50 rounded-xl text-center">
              <p class="text-2xl font-bold text-emerald-400">{{ twoFactorStats.enabled }}</p>
              <p class="text-sm text-slate-400">Users with 2FA</p>
            </div>
            <div class="p-4 bg-slate-900/50 rounded-xl text-center">
              <p class="text-2xl font-bold text-amber-400">{{ twoFactorStats.admins_enabled }}</p>
              <p class="text-sm text-slate-400">Admins with 2FA</p>
            </div>
            <div class="p-4 bg-slate-900/50 rounded-xl text-center">
              <p class="text-2xl font-bold text-blue-400">{{ twoFactorStats.percentage }}%</p>
              <p class="text-sm text-slate-400">Adoption Rate</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- OAuth Settings -->
    <div v-show="activeTab === 'oauth'" class="space-y-6">
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <div class="flex items-center gap-3 mb-6">
          <KeyIcon class="w-6 h-6 text-emerald-400" />
          <h2 class="text-xl font-semibold text-white">OAuth Providers</h2>
        </div>

        <div class="space-y-4">
          <div
            v-for="provider in oauthProviders"
            :key="provider.name"
            class="p-4 bg-slate-900/50 rounded-xl"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div :class="[
                  'p-3 rounded-xl',
                  provider.color
                ]">
                  <component :is="provider.icon" class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="font-medium text-white">{{ provider.label }}</h3>
                  <p class="text-sm text-slate-400">
                    {{ provider.configured ? 'Configured' : 'Not configured' }}
                  </p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  provider.enabled
                    ? 'bg-emerald-500/20 text-emerald-400'
                    : 'bg-slate-600/20 text-slate-400'
                ]">
                  {{ provider.enabled ? 'Enabled' : 'Disabled' }}
                </span>
                <button
                  @click="configureProvider(provider)"
                  class="p-2 text-slate-400 hover:text-white hover:bg-slate-600/50 rounded-lg transition-colors"
                >
                  <Cog6ToothIcon class="w-5 h-5" />
                </button>
              </div>
            </div>

            <div v-if="expandedProvider === provider.name" class="mt-4 pt-4 border-t border-slate-700/50 space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-slate-300">Client ID</label>
                  <input
                    v-model="provider.client_id"
                    type="text"
                    placeholder="Enter client ID"
                    class="w-full px-4 py-2 bg-slate-800 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                  >
                </div>
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-slate-300">Client Secret</label>
                  <input
                    v-model="provider.client_secret"
                    type="password"
                    placeholder="Enter client secret"
                    class="w-full px-4 py-2 bg-slate-800 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                  >
                </div>
              </div>
              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Redirect URI</label>
                <div class="flex gap-2">
                  <input
                    :value="getRedirectUri(provider.name)"
                    type="text"
                    readonly
                    class="flex-1 px-4 py-2 bg-slate-800 border border-slate-600/50 rounded-xl text-slate-400"
                  >
                  <button
                    @click="copyRedirectUri(provider.name)"
                    class="px-3 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-white transition-colors"
                  >
                    <ClipboardIcon class="w-5 h-5" />
                  </button>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="provider.enabled"
                    class="rounded border-slate-600 bg-slate-800 text-emerald-500 focus:ring-emerald-500"
                  >
                  <span class="text-sm text-slate-300">Enable this provider</span>
                </label>
                <button
                  @click="saveProvider(provider)"
                  class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl transition-colors"
                >
                  Save
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- OAuth Stats -->
        <div class="mt-6 pt-6 border-t border-slate-700/50">
          <h3 class="text-lg font-medium text-white mb-4">OAuth Statistics</h3>
          <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div
              v-for="provider in oauthProviders"
              :key="provider.name"
              class="p-4 bg-slate-900/50 rounded-xl text-center"
            >
              <p class="text-2xl font-bold text-white">{{ provider.users_count || 0 }}</p>
              <p class="text-sm text-slate-400">{{ provider.label }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Security Policies -->
    <div v-show="activeTab === 'policies'" class="space-y-6">
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <div class="flex items-center gap-3 mb-6">
          <LockClosedIcon class="w-6 h-6 text-emerald-400" />
          <h2 class="text-xl font-semibold text-white">Security Policies</h2>
        </div>

        <div class="space-y-6">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Minimum Password Length</label>
            <input
              v-model.number="settings.min_password_length"
              type="number"
              min="6"
              max="32"
              class="w-full px-4 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
            >
          </div>

          <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl">
            <div>
              <h3 class="font-medium text-white">Require Special Characters</h3>
              <p class="text-sm text-slate-400">Passwords must include symbols</p>
            </div>
            <button
              type="button"
              @click="settings.require_special_chars = !settings.require_special_chars"
              :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                settings.require_special_chars ? 'bg-emerald-500' : 'bg-slate-600'
              ]"
            >
              <span :class="[
                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                settings.require_special_chars ? 'translate-x-6' : 'translate-x-1'
              ]" />
            </button>
          </div>

          <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl">
            <div>
              <h3 class="font-medium text-white">Require Uppercase & Lowercase</h3>
              <p class="text-sm text-slate-400">Mixed case required in passwords</p>
            </div>
            <button
              type="button"
              @click="settings.require_mixed_case = !settings.require_mixed_case"
              :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                settings.require_mixed_case ? 'bg-emerald-500' : 'bg-slate-600'
              ]"
            >
              <span :class="[
                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                settings.require_mixed_case ? 'translate-x-6' : 'translate-x-1'
              ]" />
            </button>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Session Timeout (minutes)</label>
            <input
              v-model.number="settings.session_timeout"
              type="number"
              min="5"
              class="w-full px-4 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
            >
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Max Login Attempts</label>
            <input
              v-model.number="settings.max_login_attempts"
              type="number"
              min="3"
              max="20"
              class="w-full px-4 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
            >
            <p class="text-xs text-slate-400">Number of failed attempts before lockout</p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Lockout Duration (minutes)</label>
            <input
              v-model.number="settings.lockout_duration"
              type="number"
              min="1"
              class="w-full px-4 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
            >
          </div>
        </div>
      </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end">
      <button
        @click="saveSettings"
        :disabled="saving"
        class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-teal-700 transition-all shadow-lg shadow-emerald-500/25 disabled:opacity-50 flex items-center gap-2"
      >
        <ArrowPathIcon v-if="saving" class="w-5 h-5 animate-spin" />
        {{ saving ? 'Saving...' : 'Save Settings' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
  ShieldCheckIcon,
  DevicePhoneMobileIcon,
  KeyIcon,
  LockClosedIcon,
  Cog6ToothIcon,
  ClipboardIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline'
import api from '../services/api'

// Simple icon components for OAuth providers
const DiscordIcon = { template: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>' }
const GoogleIcon = { template: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>' }
const GithubIcon = { template: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>' }
const TwitterIcon = { template: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>' }
const FacebookIcon = { template: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>' }

const tabs = [
  { id: '2fa', label: 'Two-Factor Auth', icon: DevicePhoneMobileIcon },
  { id: 'oauth', label: 'OAuth Providers', icon: KeyIcon },
  { id: 'policies', label: 'Security Policies', icon: LockClosedIcon }
]

const activeTab = ref('2fa')
const saving = ref(false)
const expandedProvider = ref(null)

const settings = ref({
  require_2fa_admin: false,
  allow_2fa_users: true,
  recovery_codes_count: 8,
  totp_window: 30,
  min_password_length: 8,
  require_special_chars: false,
  require_mixed_case: false,
  session_timeout: 120,
  max_login_attempts: 5,
  lockout_duration: 15
})

const twoFactorStats = ref({
  enabled: 0,
  admins_enabled: 0,
  percentage: 0
})

const oauthProviders = ref([
  { name: 'discord', label: 'Discord', icon: DiscordIcon, color: 'bg-indigo-600', configured: false, enabled: false, client_id: '', client_secret: '', users_count: 0 },
  { name: 'google', label: 'Google', icon: GoogleIcon, color: 'bg-red-500', configured: false, enabled: false, client_id: '', client_secret: '', users_count: 0 },
  { name: 'github', label: 'GitHub', icon: GithubIcon, color: 'bg-slate-700', configured: false, enabled: false, client_id: '', client_secret: '', users_count: 0 },
  { name: 'twitter', label: 'Twitter/X', icon: TwitterIcon, color: 'bg-slate-800', configured: false, enabled: false, client_id: '', client_secret: '', users_count: 0 },
  { name: 'facebook', label: 'Facebook', icon: FacebookIcon, color: 'bg-blue-600', configured: false, enabled: false, client_id: '', client_secret: '', users_count: 0 }
])

onMounted(async () => {
  await loadSettings()
})

async function loadSettings() {
  try {
    const response = await api.get('/admin/settings')
    const data = response.data

    // Map settings from API
    if (data.security) {
      Object.assign(settings.value, data.security)
    }

    // Load 2FA stats
    if (data.two_factor_stats) {
      twoFactorStats.value = data.two_factor_stats
    }

    // Load OAuth provider configs
    if (data.oauth_providers) {
      oauthProviders.value.forEach(provider => {
        const apiProvider = data.oauth_providers.find(p => p.name === provider.name)
        if (apiProvider) {
          Object.assign(provider, apiProvider)
        }
      })
    }
  } catch (error) {
    console.error('Failed to load settings:', error)
  }
}

async function saveSettings() {
  saving.value = true
  try {
    await api.post('/admin/settings', {
      security: settings.value
    })
    alert('Settings saved successfully!')
  } catch (error) {
    console.error('Failed to save settings:', error)
    alert('Failed to save settings')
  } finally {
    saving.value = false
  }
}

function configureProvider(provider) {
  expandedProvider.value = expandedProvider.value === provider.name ? null : provider.name
}

async function saveProvider(provider) {
  try {
    await api.post(`/admin/settings/oauth/${provider.name}`, {
      client_id: provider.client_id,
      client_secret: provider.client_secret,
      enabled: provider.enabled
    })
    provider.configured = !!(provider.client_id && provider.client_secret)
    alert(`${provider.label} settings saved!`)
  } catch (error) {
    console.error('Failed to save provider:', error)
    alert('Failed to save provider settings')
  }
}

function getRedirectUri(provider) {
  return `${window.location.origin}/api/oauth/${provider}/callback`
}

function copyRedirectUri(provider) {
  navigator.clipboard.writeText(getRedirectUri(provider))
  alert('Redirect URI copied to clipboard!')
}
</script>
