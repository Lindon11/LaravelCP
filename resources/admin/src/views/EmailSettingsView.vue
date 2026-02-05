<template>
  <div class="space-y-6">
    <!-- Tabs -->
    <div class="border-b border-gray-700">
      <nav class="-mb-px flex space-x-8">
        <button
          @click="activeTab = 'compose'"
          :class="[
            activeTab === 'compose'
              ? 'border-amber-500 text-amber-500'
              : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300',
            'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          Compose
        </button>
        <button
          @click="activeTab = 'settings'"
          :class="[
            activeTab === 'settings'
              ? 'border-amber-500 text-amber-500'
              : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300',
            'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          Settings
        </button>
        <button
          @click="activeTab = 'templates'"
          :class="[
            activeTab === 'templates'
              ? 'border-amber-500 text-amber-500'
              : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300',
            'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          Templates
        </button>
      </nav>
    </div>

    <!-- Compose Tab -->
    <div v-if="activeTab === 'compose'" class="bg-gray-800 rounded-lg p-6">
      <div class="mb-4">
        <h2 class="text-lg font-medium text-white mb-2">Compose Email</h2>
        <p class="text-sm text-gray-400">Send a one-off email to any address</p>
      </div>
      <form @submit.prevent="sendManualEmail" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-1">To</label>
          <input v-model="composeEmail.to" type="email" required placeholder="recipient@example.com" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-1">Subject</label>
          <input v-model="composeEmail.subject" type="text" required placeholder="Email subject" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
        </div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block text-sm font-medium text-gray-300">Body</label>
            <label class="inline-flex items-center text-sm">
              <input type="checkbox" v-model="composeEmail.is_html" class="form-checkbox text-amber-500 bg-gray-700 border-gray-600 rounded" />
              <span class="ml-2 text-gray-300">HTML</span>
            </label>
          </div>
          <textarea v-model="composeEmail.body" required rows="12" :placeholder="composeEmail.is_html ? '<html>...</html>' : 'Email body text'" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500 font-mono text-sm"></textarea>
        </div>
        <div class="flex justify-end">
          <button type="submit" :disabled="sendingManual" class="px-6 py-2 bg-amber-500 text-black font-medium rounded-md hover:bg-amber-600 disabled:opacity-50">
            {{ sendingManual ? 'Sending...' : 'Send Email' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Settings Tab -->
    <div v-if="activeTab === 'settings'" class="bg-gray-800 rounded-lg p-6">
      <form @submit.prevent="saveSettings" class="space-y-6">
        <!-- Mailer Type -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Email Provider</label>
          <div class="flex space-x-4">
            <label class="inline-flex items-center">
              <input type="radio" v-model="settings.mailer" value="mailgun" class="form-radio text-amber-500 bg-gray-700 border-gray-600" />
              <span class="ml-2 text-gray-300">Mailgun</span>
            </label>
            <label class="inline-flex items-center">
              <input type="radio" v-model="settings.mailer" value="smtp" class="form-radio text-amber-500 bg-gray-700 border-gray-600" />
              <span class="ml-2 text-gray-300">SMTP</span>
            </label>
          </div>
        </div>

        <!-- Mailgun Settings -->
        <div v-if="settings.mailer === 'mailgun'" class="space-y-4 p-4 bg-gray-900 rounded-lg">
          <h3 class="text-lg font-medium text-white">Mailgun Configuration</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Domain</label>
              <input v-model="settings.mailgun_domain" type="text" placeholder="mg.yourdomain.com" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">
                API Key
                <span v-if="settings.has_mailgun_secret" class="text-green-400 text-xs ml-2">(saved)</span>
              </label>
              <input v-model="settings.mailgun_secret" type="password" :placeholder="settings.has_mailgun_secret ? '••••••••' : 'Enter API key'" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Endpoint</label>
              <select v-model="settings.mailgun_endpoint" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white focus:ring-amber-500 focus:border-amber-500">
                <option value="api.mailgun.net">US Region (api.mailgun.net)</option>
                <option value="api.eu.mailgun.net">EU Region (api.eu.mailgun.net)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- SMTP Settings -->
        <div v-if="settings.mailer === 'smtp'" class="space-y-4 p-4 bg-gray-900 rounded-lg">
          <h3 class="text-lg font-medium text-white">SMTP Configuration</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Host</label>
              <input v-model="settings.host" type="text" placeholder="smtp.example.com" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Port</label>
              <input v-model.number="settings.port" type="number" placeholder="587" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Username</label>
              <input v-model="settings.username" type="text" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">
                Password
                <span v-if="settings.has_password" class="text-green-400 text-xs ml-2">(saved)</span>
              </label>
              <input v-model="settings.password" type="password" :placeholder="settings.has_password ? '••••••••' : 'Enter password'" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Encryption</label>
              <select v-model="settings.encryption" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white focus:ring-amber-500 focus:border-amber-500">
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
                <option value="null">None</option>
              </select>
            </div>
          </div>
        </div>

        <!-- From Address -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">From Address *</label>
            <input v-model="settings.from_address" type="email" required placeholder="noreply@yourdomain.com" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">From Name *</label>
            <input v-model="settings.from_name" type="text" required placeholder="OpenPBBG" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
          </div>
        </div>

        <!-- Active Toggle -->
        <div class="flex items-center justify-between p-4 bg-gray-900 rounded-lg">
          <div>
            <h4 class="text-white font-medium">Enable Email System</h4>
            <p class="text-sm text-gray-400">When enabled, emails will be sent for registration, password reset, etc.</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="settings.is_active" class="sr-only peer">
            <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-amber-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
          </label>
        </div>

        <!-- Status -->
        <div v-if="settings.last_tested_at" class="flex items-center space-x-2 text-sm">
          <span :class="settings.test_successful ? 'text-green-400' : 'text-red-400'">
            {{ settings.test_successful ? '✓ Last test successful' : '✗ Last test failed' }}
          </span>
          <span class="text-gray-500">({{ new Date(settings.last_tested_at).toLocaleString() }})</span>
        </div>

        <!-- Actions -->
        <div class="flex items-center space-x-4 pt-4 border-t border-gray-700">
          <button type="submit" :disabled="saving" class="px-4 py-2 bg-amber-500 text-black font-medium rounded-md hover:bg-amber-600 disabled:opacity-50">
            {{ saving ? 'Saving...' : 'Save Settings' }}
          </button>
          <button type="button" @click="showTestModal = true" :disabled="!settings.id" class="px-4 py-2 bg-gray-600 text-white font-medium rounded-md hover:bg-gray-500 disabled:opacity-50">
            Send Test Email
          </button>
        </div>
      </form>
    </div>

    <!-- Templates Tab -->
    <div v-if="activeTab === 'templates'" class="space-y-4">
      <div class="flex justify-between items-center">
        <button @click="seedDefaults" :disabled="seedingDefaults" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-500 disabled:opacity-50">
          {{ seedingDefaults ? 'Creating...' : 'Create Default Templates' }}
        </button>
        <button @click="openTemplateModal()" class="px-4 py-2 bg-amber-500 text-black text-sm font-medium rounded-md hover:bg-amber-600">
          + New Template
        </button>
      </div>

      <!-- Templates List -->
      <div class="bg-gray-800 rounded-lg overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-900">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Name</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Slug</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Subject</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <tr v-for="template in templates" :key="template.id" class="hover:bg-gray-700">
              <td class="px-4 py-3 text-white">{{ template.name }}</td>
              <td class="px-4 py-3 text-gray-400 font-mono text-sm">{{ template.slug }}</td>
              <td class="px-4 py-3 text-gray-300 text-sm">{{ template.subject }}</td>
              <td class="px-4 py-3">
                <span :class="template.is_active ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300'" class="px-2 py-1 text-xs rounded-full">
                  {{ template.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-3 text-right space-x-2">
                <button @click="previewTemplate(template)" class="text-blue-400 hover:text-blue-300 text-sm">Preview</button>
                <button @click="openTemplateModal(template)" class="text-amber-400 hover:text-amber-300 text-sm">Edit</button>
                <button @click="confirmDeleteTemplate(template)" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
              </td>
            </tr>
            <tr v-if="templates.length === 0">
              <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                No templates found. Click "Create Default Templates" to get started.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Test Email Modal -->
    <div v-if="showTestModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-white mb-4">Send Test Email</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
          <input v-model="testEmail" type="email" placeholder="test@example.com" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
        </div>
        <div class="flex justify-end space-x-3">
          <button @click="showTestModal = false" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-500">Cancel</button>
          <button @click="sendTestEmail" :disabled="sendingTest" class="px-4 py-2 bg-amber-500 text-black rounded-md hover:bg-amber-600 disabled:opacity-50">
            {{ sendingTest ? 'Sending...' : 'Send Test' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Template Edit Modal -->
    <div v-if="showTemplateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-gray-800 rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-medium text-white mb-4">{{ editingTemplate.id ? 'Edit Template' : 'New Template' }}</h3>
        <form @submit.prevent="saveTemplate" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Name *</label>
              <input v-model="editingTemplate.name" type="text" required placeholder="Welcome Email" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Slug *</label>
              <input v-model="editingTemplate.slug" type="text" required placeholder="welcome" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Subject *</label>
            <input v-model="editingTemplate.subject" type="text" required placeholder="Welcome to {{app_name}}, {{username}}!" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
            <input v-model="editingTemplate.description" type="text" placeholder="When is this email sent?" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Available Variables <span class="text-gray-500 font-normal">(comma separated)</span></label>
            <input v-model="variablesInput" type="text" placeholder="app_name, username, email" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
            <p class="text-xs text-gray-500 mt-1">Use variables in templates like: <code class="bg-gray-700 px-1 rounded">{{username}}</code></p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">HTML Body *</label>
            <textarea v-model="editingTemplate.body_html" required rows="12" placeholder="<!DOCTYPE html>..." class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500 font-mono text-sm"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Plain Text Body (optional)</label>
            <textarea v-model="editingTemplate.body_text" rows="4" placeholder="Plain text version..." class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500"></textarea>
          </div>
          <div class="flex items-center">
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="editingTemplate.is_active" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-amber-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
              <span class="ml-3 text-sm text-gray-300">Active</span>
            </label>
          </div>
          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
            <button type="button" @click="showTemplateModal = false" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-500">Cancel</button>
            <button type="submit" :disabled="savingTemplate" class="px-4 py-2 bg-amber-500 text-black rounded-md hover:bg-amber-600 disabled:opacity-50">
              {{ savingTemplate ? 'Saving...' : 'Save Template' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Preview Modal -->
    <div v-if="showPreviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-gray-800 rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-white">Template Preview</h3>
          <button @click="showPreviewModal = false" class="text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="mb-2 text-sm text-gray-400"><strong>Subject:</strong> {{ previewData?.subject }}</div>
        <div class="flex-1 overflow-auto bg-white rounded-lg">
          <iframe v-if="previewData?.body_html" :srcdoc="previewData.body_html" class="w-full h-full min-h-[500px]" sandbox="allow-same-origin"></iframe>
        </div>
        <div class="mt-4 flex justify-end space-x-3">
          <button @click="showSendTestTemplateModal = true" class="px-4 py-2 bg-amber-500 text-black rounded-md hover:bg-amber-600">Send Test Email</button>
        </div>
      </div>
    </div>

    <!-- Send Test Template Modal -->
    <div v-if="showSendTestTemplateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60]">
      <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-white mb-4">Send Test Email</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
          <input v-model="templateTestEmail" type="email" placeholder="test@example.com" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:ring-amber-500 focus:border-amber-500" />
        </div>
        <div class="flex justify-end space-x-3">
          <button @click="showSendTestTemplateModal = false" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-500">Cancel</button>
          <button @click="sendTemplateTest" :disabled="sendingTemplateTest" class="px-4 py-2 bg-amber-500 text-black rounded-md hover:bg-amber-600 disabled:opacity-50">
            {{ sendingTemplateTest ? 'Sending...' : 'Send' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'

const activeTab = ref('compose')
const saving = ref(false)
const sendingTest = ref(false)
const seedingDefaults = ref(false)
const savingTemplate = ref(false)
const sendingTemplateTest = ref(false)
const sendingManual = ref(false)

const showTestModal = ref(false)
const showTemplateModal = ref(false)
const showPreviewModal = ref(false)
const showSendTestTemplateModal = ref(false)

const testEmail = ref('')
const templateTestEmail = ref('')

const composeEmail = ref({
  to: '',
  subject: '',
  body: '',
  is_html: true,
})
const previewData = ref(null)
const previewingTemplate = ref(null)

const settings = ref({
  id: null,
  mailer: 'mailgun',
  host: '',
  port: 587,
  username: '',
  password: '',
  encryption: 'tls',
  from_address: '',
  from_name: '',
  mailgun_domain: '',
  mailgun_secret: '',
  mailgun_endpoint: 'api.mailgun.net',
  is_active: false,
  has_password: false,
  has_mailgun_secret: false,
  last_tested_at: null,
  test_successful: null,
})

const templates = ref([])

const editingTemplate = ref({
  id: null,
  slug: '',
  name: '',
  subject: '',
  body_html: '',
  body_text: '',
  description: '',
  is_active: true,
  available_variables: [],
})

const variablesInput = computed({
  get: () => (editingTemplate.value.available_variables || []).join(', '),
  set: (val) => {
    editingTemplate.value.available_variables = val.split(',').map(v => v.trim()).filter(v => v)
  }
})

onMounted(() => {
  loadSettings()
  loadTemplates()
})

async function loadSettings() {
  try {
    const response = await api.get('/admin/email/settings')
    if (response.data.success) {
      Object.assign(settings.value, response.data.data)
    }
  } catch (error) {
    console.error('Failed to load email settings:', error)
  }
}

async function loadTemplates() {
  try {
    const response = await api.get('/admin/email/templates')
    if (response.data.success) {
      templates.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to load templates:', error)
  }
}

async function saveSettings() {
  saving.value = true
  try {
    const response = await api.post('/admin/email/settings', settings.value)
    if (response.data.success) {
      alert('Email settings saved successfully')
      await loadSettings()
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save settings')
  } finally {
    saving.value = false
  }
}

async function sendManualEmail() {
  sendingManual.value = true
  try {
    const response = await api.post('/admin/email/send', composeEmail.value)
    if (response.data.success) {
      alert(response.data.message)
      composeEmail.value = { to: '', subject: '', body: '', is_html: true }
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to send email')
  } finally {
    sendingManual.value = false
  }
}

async function sendTestEmail() {
  if (!testEmail.value) return
  sendingTest.value = true
  try {
    const response = await api.post('/admin/email/settings/test', { test_email: testEmail.value })
    if (response.data.success) {
      alert(response.data.message)
      showTestModal.value = false
      testEmail.value = ''
      await loadSettings()
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to send test email')
  } finally {
    sendingTest.value = false
  }
}

async function seedDefaults() {
  seedingDefaults.value = true
  try {
    const response = await api.post('/admin/email/templates/seed-defaults')
    if (response.data.success) {
      alert(response.data.message)
      await loadTemplates()
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to create default templates')
  } finally {
    seedingDefaults.value = false
  }
}

function openTemplateModal(template = null) {
  if (template) {
    editingTemplate.value = { ...template }
  } else {
    editingTemplate.value = { id: null, slug: '', name: '', subject: '', body_html: '', body_text: '', description: '', is_active: true, available_variables: [] }
  }
  showTemplateModal.value = true
}

async function saveTemplate() {
  savingTemplate.value = true
  try {
    const data = { ...editingTemplate.value }
    let response
    if (data.id) {
      response = await api.patch(`/admin/email/templates/${data.id}`, data)
    } else {
      response = await api.post('/admin/email/templates', data)
    }
    if (response.data.success) {
      alert(response.data.message)
      showTemplateModal.value = false
      await loadTemplates()
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save template')
  } finally {
    savingTemplate.value = false
  }
}

async function previewTemplate(template) {
  previewingTemplate.value = template
  try {
    const response = await api.post(`/admin/email/templates/${template.id}/preview`)
    if (response.data.success) {
      previewData.value = response.data.data
      showPreviewModal.value = true
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to preview template')
  }
}

async function sendTemplateTest() {
  if (!templateTestEmail.value || !previewingTemplate.value) return
  sendingTemplateTest.value = true
  try {
    const response = await api.post(`/admin/email/templates/${previewingTemplate.value.id}/test`, { test_email: templateTestEmail.value })
    if (response.data.success) {
      alert(response.data.message)
      showSendTestTemplateModal.value = false
      templateTestEmail.value = ''
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to send test email')
  } finally {
    sendingTemplateTest.value = false
  }
}

function confirmDeleteTemplate(template) {
  if (confirm(`Are you sure you want to delete "${template.name}"?`)) {
    deleteTemplate(template.id)
  }
}

async function deleteTemplate(id) {
  try {
    const response = await api.delete(`/admin/email/templates/${id}`)
    if (response.data.success) {
      alert(response.data.message)
      await loadTemplates()
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to delete template')
  }
}
</script>
