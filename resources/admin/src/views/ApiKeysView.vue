<template>
  <div class="space-y-6">
    <!-- Search & Actions -->
    <div class="flex items-center gap-4">
      <div class="flex-1">
        <input
          v-model="search"
          @input="debouncedSearch"
          type="text"
          placeholder="Search API keys..."
          class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50"
        />
      </div>
      <select
        v-model="statusFilter"
        @change="loadApiKeys"
        class="px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50"
      >
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
      <button
        @click="showAnalytics = true"
        class="px-4 py-2 bg-slate-700 hover:bg-slate-600 border border-slate-600 rounded-xl text-white flex items-center gap-2 transition-colors"
      >
        <ChartBarIcon class="w-5 h-5" />
        Analytics
      </button>
      <button
        @click="openCreateModal"
        class="px-4 py-2 bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700 rounded-xl text-white flex items-center gap-2 transition-colors"
      >
        <PlusIcon class="w-5 h-5" />
        Create API Key
      </button>
    </div>

    <!-- API Keys Table -->
    <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-700/50">
              <th class="text-left text-sm font-medium text-slate-400 px-6 py-4">Name</th>
              <th class="text-left text-sm font-medium text-slate-400 px-6 py-4">Key</th>
              <th class="text-left text-sm font-medium text-slate-400 px-6 py-4">Status</th>
              <th class="text-left text-sm font-medium text-slate-400 px-6 py-4">Rate Limit</th>
              <th class="text-left text-sm font-medium text-slate-400 px-6 py-4">Requests</th>
              <th class="text-left text-sm font-medium text-slate-400 px-6 py-4">Last Used</th>
              <th class="text-right text-sm font-medium text-slate-400 px-6 py-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="apiKey in apiKeys" :key="apiKey.id" class="border-b border-slate-700/30 hover:bg-slate-700/20">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="p-2 rounded-lg bg-violet-500/20">
                    <KeyIcon class="w-4 h-4 text-violet-400" />
                  </div>
                  <div>
                    <p class="text-white font-medium">{{ apiKey.name }}</p>
                    <p class="text-xs text-slate-500">{{ apiKey.description || 'No description' }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <code class="text-sm text-slate-400 bg-slate-700/50 px-2 py-1 rounded">
                    {{ maskKey(apiKey.key) }}
                  </code>
                  <button @click="copyKey(apiKey.key)" class="text-slate-500 hover:text-white">
                    <ClipboardIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
              <td class="px-6 py-4">
                <span v-if="apiKey.is_expired" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-red-500/20 text-red-400">
                  Expired
                </span>
                <span v-else-if="apiKey.is_active" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-500/20 text-emerald-400">
                  Active
                </span>
                <span v-else class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-500/20 text-slate-400">
                  Inactive
                </span>
              </td>
              <td class="px-6 py-4 text-slate-300">
                {{ apiKey.rate_limit }}/min
                <span v-if="apiKey.daily_limit" class="text-slate-500 text-xs">
                  ({{ apiKey.daily_limit }}/day)
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="text-white">{{ formatNumber(apiKey.total_requests) }}</div>
                <div class="text-xs text-slate-500">{{ apiKey.requests_today }} today</div>
              </td>
              <td class="px-6 py-4 text-slate-400 text-sm">
                {{ apiKey.last_used_at ? formatDate(apiKey.last_used_at) : 'Never' }}
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="viewKey(apiKey)"
                    class="p-2 rounded-lg bg-slate-700/50 hover:bg-slate-600/50 text-slate-400 hover:text-white transition-colors"
                    title="View Details"
                  >
                    <EyeIcon class="w-4 h-4" />
                  </button>
                  <button
                    @click="editKey(apiKey)"
                    class="p-2 rounded-lg bg-slate-700/50 hover:bg-slate-600/50 text-slate-400 hover:text-white transition-colors"
                    title="Edit"
                  >
                    <PencilIcon class="w-4 h-4" />
                  </button>
                  <button
                    @click="toggleKey(apiKey)"
                    class="p-2 rounded-lg bg-slate-700/50 hover:bg-slate-600/50 transition-colors"
                    :class="apiKey.is_active ? 'text-emerald-400 hover:text-emerald-300' : 'text-slate-400 hover:text-white'"
                    :title="apiKey.is_active ? 'Disable' : 'Enable'"
                  >
                    <BoltIcon class="w-4 h-4" />
                  </button>
                  <button
                    @click="confirmDelete(apiKey)"
                    class="p-2 rounded-lg bg-slate-700/50 hover:bg-red-500/20 text-slate-400 hover:text-red-400 transition-colors"
                    title="Delete"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="apiKeys.length === 0 && !loading">
              <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                No API keys found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex justify-center gap-2">
      <button
        v-for="page in pagination.last_page"
        :key="page"
        @click="loadApiKeys(page)"
        :class="[
          'px-3 py-1.5 rounded-lg text-sm',
          page === pagination.current_page
            ? 'bg-violet-500 text-white'
            : 'bg-slate-700 text-slate-300 hover:bg-slate-600'
        ]"
      >
        {{ page }}
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>
      <div class="relative bg-slate-800 rounded-2xl border border-slate-700 shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto m-4">
        <div class="sticky top-0 bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
          <h2 class="text-xl font-semibold text-white">
            {{ isEditing ? 'Edit API Key' : 'Create API Key' }}
          </h2>
          <button @click="closeModal" class="text-slate-400 hover:text-white">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <form @submit.prevent="saveApiKey" class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Name *</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full px-4 py-2.5 bg-slate-700/50 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50"
              placeholder="My Integration"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Description</label>
            <textarea
              v-model="form.description"
              rows="2"
              class="w-full px-4 py-2.5 bg-slate-700/50 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50"
              placeholder="What is this API key used for?"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Rate Limit (per minute)</label>
              <input
                v-model.number="form.rate_limit"
                type="number"
                min="1"
                max="10000"
                class="w-full px-4 py-2.5 bg-slate-700/50 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Daily Limit (optional)</label>
              <input
                v-model.number="form.daily_limit"
                type="number"
                min="1"
                class="w-full px-4 py-2.5 bg-slate-700/50 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50"
                placeholder="Unlimited"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Expires At (optional)</label>
            <input
              v-model="form.expires_at"
              type="datetime-local"
              class="w-full px-4 py-2.5 bg-slate-700/50 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Permissions</label>
            <div class="grid grid-cols-2 gap-2">
              <label
                v-for="perm in availablePermissions"
                :key="perm.key"
                class="flex items-center gap-2 p-3 rounded-xl bg-slate-700/30 cursor-pointer hover:bg-slate-700/50"
              >
                <input
                  type="checkbox"
                  :value="perm.key"
                  v-model="form.permissions"
                  class="rounded border-slate-600 bg-slate-700 text-violet-500 focus:ring-violet-500/50"
                />
                <div>
                  <span class="text-sm text-white">{{ perm.label }}</span>
                  <p class="text-xs text-slate-500">{{ perm.description }}</p>
                </div>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Allowed IPs (optional)</label>
            <div class="flex gap-2 mb-2">
              <input
                v-model="newIp"
                type="text"
                class="flex-1 px-4 py-2.5 bg-slate-700/50 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50"
                placeholder="192.168.1.1"
                @keyup.enter.prevent="addIp"
              />
              <button
                type="button"
                @click="addIp"
                class="px-4 py-2.5 bg-slate-600 hover:bg-slate-500 rounded-xl text-white"
              >
                Add
              </button>
            </div>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="(ip, index) in form.allowed_ips"
                :key="index"
                class="px-3 py-1 bg-slate-700 rounded-lg text-sm text-slate-300 flex items-center gap-2"
              >
                {{ ip }}
                <button type="button" @click="removeIp(index)" class="text-slate-500 hover:text-red-400">
                  <XMarkIcon class="w-4 h-4" />
                </button>
              </span>
              <span v-if="form.allowed_ips.length === 0" class="text-slate-500 text-sm">
                All IPs allowed
              </span>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-slate-700">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-white"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-6 py-2 bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700 rounded-xl text-white flex items-center gap-2"
            >
              <span v-if="saving">Saving...</span>
              <span v-else>{{ isEditing ? 'Update' : 'Create' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Created Key Modal (shows key and secret) -->
    <div v-if="showCreatedModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
      <div class="relative bg-slate-800 rounded-2xl border border-slate-700 shadow-xl w-full max-w-lg m-4 p-6">
        <div class="text-center mb-6">
          <div class="w-16 h-16 rounded-full bg-emerald-500/20 flex items-center justify-center mx-auto mb-4">
            <CheckCircleIcon class="w-8 h-8 text-emerald-400" />
          </div>
          <h2 class="text-xl font-semibold text-white">API Key Created</h2>
          <p class="text-slate-400 text-sm mt-1">Save these credentials - the secret won't be shown again!</p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-400 mb-2">API Key</label>
            <div class="flex items-center gap-2">
              <code class="flex-1 px-4 py-3 bg-slate-700 rounded-xl text-white text-sm break-all">
                {{ createdCredentials.key }}
              </code>
              <button @click="copyToClipboard(createdCredentials.key)" class="p-2 bg-slate-600 hover:bg-slate-500 rounded-lg">
                <ClipboardIcon class="w-5 h-5 text-white" />
              </button>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-400 mb-2">API Secret</label>
            <div class="flex items-center gap-2">
              <code class="flex-1 px-4 py-3 bg-slate-700 rounded-xl text-white text-sm break-all">
                {{ createdCredentials.secret }}
              </code>
              <button @click="copyToClipboard(createdCredentials.secret)" class="p-2 bg-slate-600 hover:bg-slate-500 rounded-lg">
                <ClipboardIcon class="w-5 h-5 text-white" />
              </button>
            </div>
          </div>
        </div>

        <div class="mt-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30">
          <p class="text-amber-400 text-sm flex items-start gap-2">
            <ExclamationTriangleIcon class="w-5 h-5 flex-shrink-0" />
            <span>Make sure to copy these credentials now. The secret will not be displayed again.</span>
          </p>
        </div>

        <button
          @click="showCreatedModal = false"
          class="w-full mt-6 px-4 py-3 bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700 rounded-xl text-white font-medium"
        >
          I've Saved My Credentials
        </button>
      </div>
    </div>

    <!-- View Details Modal -->
    <div v-if="showViewModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showViewModal = false"></div>
      <div class="relative bg-slate-800 rounded-2xl border border-slate-700 shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto m-4">
        <div class="sticky top-0 bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
          <h2 class="text-xl font-semibold text-white">{{ selectedKey?.name }}</h2>
          <button @click="showViewModal = false" class="text-slate-400 hover:text-white">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <div class="p-6 space-y-6" v-if="selectedKey">
          <!-- Stats Grid -->
          <div class="grid grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-slate-700/30">
              <p class="text-slate-400 text-sm">Today</p>
              <p class="text-2xl font-bold text-white">{{ selectedKey.stats?.requests_today || 0 }}</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-700/30">
              <p class="text-slate-400 text-sm">This Week</p>
              <p class="text-2xl font-bold text-white">{{ selectedKey.stats?.requests_week || 0 }}</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-700/30">
              <p class="text-slate-400 text-sm">Avg Response</p>
              <p class="text-2xl font-bold text-white">{{ selectedKey.stats?.avg_response_time || 0 }}ms</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-700/30">
              <p class="text-slate-400 text-sm">Error Rate</p>
              <p class="text-2xl font-bold text-white">{{ selectedKey.stats?.error_rate || 0 }}%</p>
            </div>
          </div>

          <!-- Recent Requests -->
          <div>
            <h3 class="text-lg font-medium text-white mb-3">Recent Requests</h3>
            <div class="space-y-2">
              <div
                v-for="req in selectedKey.stats?.last_endpoints || []"
                :key="req.created_at"
                class="flex items-center justify-between p-3 rounded-xl bg-slate-700/30"
              >
                <div class="flex items-center gap-3">
                  <span :class="[
                    'px-2 py-0.5 rounded text-xs font-medium',
                    req.method === 'GET' ? 'bg-emerald-500/20 text-emerald-400' :
                    req.method === 'POST' ? 'bg-blue-500/20 text-blue-400' :
                    req.method === 'PUT' ? 'bg-amber-500/20 text-amber-400' :
                    'bg-red-500/20 text-red-400'
                  ]">{{ req.method }}</span>
                  <span class="text-slate-300">{{ req.endpoint }}</span>
                </div>
                <div class="flex items-center gap-4">
                  <span :class="[
                    'text-sm',
                    req.status_code < 300 ? 'text-emerald-400' :
                    req.status_code < 400 ? 'text-amber-400' : 'text-red-400'
                  ]">{{ req.status_code }}</span>
                  <span class="text-slate-500 text-sm">{{ formatDate(req.created_at) }}</span>
                </div>
              </div>
              <div v-if="!selectedKey.stats?.last_endpoints?.length" class="text-center py-8 text-slate-500">
                No recent requests
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4 border-t border-slate-700">
            <button
              @click="regenerateSecret(selectedKey.id)"
              class="px-4 py-2 bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/50 rounded-xl text-amber-400 flex items-center gap-2"
            >
              <ArrowPathIcon class="w-4 h-4" />
              Regenerate Secret
            </button>
            <button
              @click="viewLogs(selectedKey.id)"
              class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-white flex items-center gap-2"
            >
              <DocumentTextIcon class="w-4 h-4" />
              View Full Logs
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Analytics Modal -->
    <div v-if="showAnalytics" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showAnalytics = false"></div>
      <div class="relative bg-slate-800 rounded-2xl border border-slate-700 shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto m-4">
        <div class="sticky top-0 bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
          <h2 class="text-xl font-semibold text-white">API Analytics</h2>
          <button @click="showAnalytics = false" class="text-slate-400 hover:text-white">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <div class="p-6 space-y-6">
          <!-- Overview Cards -->
          <div class="grid grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-gradient-to-br from-violet-500/20 to-purple-600/20 border border-violet-500/30">
              <p class="text-violet-400 text-sm">Total Keys</p>
              <p class="text-2xl font-bold text-white">{{ analytics.overview?.total_keys || 0 }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-600/20 border border-emerald-500/30">
              <p class="text-emerald-400 text-sm">Active Keys</p>
              <p class="text-2xl font-bold text-white">{{ analytics.overview?.active_keys || 0 }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gradient-to-br from-blue-500/20 to-cyan-600/20 border border-blue-500/30">
              <p class="text-blue-400 text-sm">Total Requests</p>
              <p class="text-2xl font-bold text-white">{{ formatNumber(analytics.overview?.total_requests || 0) }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gradient-to-br from-red-500/20 to-orange-600/20 border border-red-500/30">
              <p class="text-red-400 text-sm">Error Rate</p>
              <p class="text-2xl font-bold text-white">{{ analytics.overview?.error_rate || 0 }}%</p>
            </div>
          </div>

          <!-- Top Endpoints -->
          <div>
            <h3 class="text-lg font-medium text-white mb-3">Top Endpoints</h3>
            <div class="space-y-2">
              <div
                v-for="endpoint in analytics.top_endpoints || []"
                :key="endpoint.endpoint"
                class="flex items-center justify-between p-3 rounded-xl bg-slate-700/30"
              >
                <span class="text-slate-300">{{ endpoint.endpoint }}</span>
                <div class="flex items-center gap-4">
                  <span class="text-slate-400 text-sm">{{ endpoint.avg_response_time?.toFixed(0) }}ms avg</span>
                  <span class="text-white font-medium">{{ formatNumber(endpoint.total) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Top API Keys -->
          <div>
            <h3 class="text-lg font-medium text-white mb-3">Most Active Keys</h3>
            <div class="space-y-2">
              <div
                v-for="key in analytics.requests_by_key || []"
                :key="key.id"
                class="flex items-center justify-between p-3 rounded-xl bg-slate-700/30"
              >
                <div>
                  <span class="text-white">{{ key.name }}</span>
                  <span class="text-slate-500 text-sm ml-2">{{ maskKey(key.key) }}</span>
                </div>
                <span class="text-white font-medium">{{ formatNumber(key.requests_count) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>
      <div class="relative bg-slate-800 rounded-2xl border border-slate-700 shadow-xl w-full max-w-md m-4 p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Delete API Key</h2>
        <p class="text-slate-400 mb-6">
          Are you sure you want to delete <strong class="text-white">{{ keyToDelete?.name }}</strong>?
          This action cannot be undone and will immediately revoke access.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-white"
          >
            Cancel
          </button>
          <button
            @click="deleteKey"
            class="px-4 py-2 bg-red-500 hover:bg-red-600 rounded-xl text-white"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import {
  KeyIcon,
  PlusIcon,
  ChartBarIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  BoltIcon,
  ClipboardIcon,
  XMarkIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  ArrowPathIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'

const toast = useToast()

const loading = ref(false)
const saving = ref(false)
const search = ref('')
const statusFilter = ref('')
const apiKeys = ref([])
const pagination = ref({ current_page: 1, last_page: 1 })

const showModal = ref(false)
const showCreatedModal = ref(false)
const showViewModal = ref(false)
const showAnalytics = ref(false)
const showDeleteModal = ref(false)

const isEditing = ref(false)
const editingId = ref(null)
const selectedKey = ref(null)
const keyToDelete = ref(null)
const createdCredentials = ref({ key: '', secret: '' })
const analytics = ref({})
const availablePermissions = ref([])
const newIp = ref('')

const form = ref({
  name: '',
  description: '',
  rate_limit: 60,
  daily_limit: null,
  expires_at: '',
  permissions: [],
  allowed_ips: [],
  allowed_domains: []
})

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadApiKeys(), 300)
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0)
}

const formatDate = (date) => {
  if (!date) return 'Never'
  return new Date(date).toLocaleString()
}

const maskKey = (key) => {
  if (!key) return ''
  return key.substring(0, 8) + '...' + key.substring(key.length - 4)
}

const copyKey = async (key) => {
  await navigator.clipboard.writeText(key)
  toast.success('API key copied to clipboard')
}

const copyToClipboard = async (text) => {
  await navigator.clipboard.writeText(text)
  toast.success('Copied to clipboard')
}

const loadApiKeys = async (page = 1) => {
  loading.value = true
  try {
    const response = await api.get('/admin/api-keys', {
      params: {
        page,
        search: search.value,
        status: statusFilter.value
      }
    })
    apiKeys.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page
    }
  } catch (error) {
    toast.error('Failed to load API keys')
  } finally {
    loading.value = false
  }
}

const loadPermissions = async () => {
  try {
    const response = await api.get('/admin/api-keys/permissions')
    availablePermissions.value = response.data
  } catch (error) {
    console.error('Failed to load permissions')
  }
}

const loadAnalytics = async () => {
  try {
    const response = await api.get('/admin/api-keys/analytics')
    analytics.value = response.data
  } catch (error) {
    toast.error('Failed to load analytics')
  }
}

const openCreateModal = () => {
  isEditing.value = false
  editingId.value = null
  form.value = {
    name: '',
    description: '',
    rate_limit: 60,
    daily_limit: null,
    expires_at: '',
    permissions: [],
    allowed_ips: [],
    allowed_domains: []
  }
  showModal.value = true
}

const editKey = (apiKey) => {
  isEditing.value = true
  editingId.value = apiKey.id
  form.value = {
    name: apiKey.name,
    description: apiKey.description || '',
    rate_limit: apiKey.rate_limit,
    daily_limit: apiKey.daily_limit,
    expires_at: apiKey.expires_at ? apiKey.expires_at.substring(0, 16) : '',
    permissions: apiKey.permissions || [],
    allowed_ips: apiKey.allowed_ips || [],
    allowed_domains: apiKey.allowed_domains || []
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const saveApiKey = async () => {
  saving.value = true
  try {
    const data = { ...form.value }
    if (!data.daily_limit) delete data.daily_limit
    if (!data.expires_at) delete data.expires_at
    if (data.permissions.length === 0) delete data.permissions
    if (data.allowed_ips.length === 0) delete data.allowed_ips
    if (data.allowed_domains.length === 0) delete data.allowed_domains

    if (isEditing.value) {
      await api.patch(`/admin/api-keys/${editingId.value}`, data)
      toast.success('API key updated successfully')
    } else {
      const response = await api.post('/admin/api-keys', data)
      createdCredentials.value = {
        key: response.data.key,
        secret: response.data.secret
      }
      showCreatedModal.value = true
      toast.success('API key created successfully')
    }
    closeModal()
    loadApiKeys()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save API key')
  } finally {
    saving.value = false
  }
}

const viewKey = async (apiKey) => {
  try {
    const response = await api.get(`/admin/api-keys/${apiKey.id}`)
    selectedKey.value = response.data
    showViewModal.value = true
  } catch (error) {
    toast.error('Failed to load API key details')
  }
}

const toggleKey = async (apiKey) => {
  try {
    await api.post(`/admin/api-keys/${apiKey.id}/toggle`)
    apiKey.is_active = !apiKey.is_active
    toast.success(apiKey.is_active ? 'API key enabled' : 'API key disabled')
  } catch (error) {
    toast.error('Failed to toggle API key')
  }
}

const confirmDelete = (apiKey) => {
  keyToDelete.value = apiKey
  showDeleteModal.value = true
}

const deleteKey = async () => {
  try {
    await api.delete(`/admin/api-keys/${keyToDelete.value.id}`)
    toast.success('API key deleted successfully')
    showDeleteModal.value = false
    loadApiKeys()
  } catch (error) {
    toast.error('Failed to delete API key')
  }
}

const regenerateSecret = async (id) => {
  if (!confirm('Are you sure? The old secret will stop working immediately.')) return
  try {
    const response = await api.post(`/admin/api-keys/${id}/regenerate-secret`)
    createdCredentials.value = {
      key: selectedKey.value.key,
      secret: response.data.secret
    }
    showViewModal.value = false
    showCreatedModal.value = true
    toast.success('Secret regenerated successfully')
  } catch (error) {
    toast.error('Failed to regenerate secret')
  }
}

const viewLogs = (id) => {
  // Could navigate to a logs page or open another modal
  toast.info('Logs viewer coming soon')
}

const addIp = () => {
  const ip = newIp.value.trim()
  if (ip && !form.value.allowed_ips.includes(ip)) {
    // Basic IP validation
    const ipRegex = /^(\d{1,3}\.){3}\d{1,3}$/
    if (ipRegex.test(ip)) {
      form.value.allowed_ips.push(ip)
      newIp.value = ''
    } else {
      toast.error('Invalid IP address')
    }
  }
}

const removeIp = (index) => {
  form.value.allowed_ips.splice(index, 1)
}

onMounted(() => {
  loadApiKeys()
  loadPermissions()
  loadAnalytics()
})
</script>
