<template>
  <div class="space-y-6">
    <!-- Search Header -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center gap-4">
      <div class="relative flex-1 w-full lg:max-w-lg">
        <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by username, email, or ID..."
          @input="debouncedSearch"
          @keyup.enter="searchUser"
          class="w-full pl-12 pr-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
        />
        <!-- Search Results Dropdown -->
        <div v-if="searchResults.length > 0" class="absolute top-full left-0 right-0 mt-2 bg-slate-800 border border-slate-700/50 rounded-xl shadow-2xl z-50 overflow-hidden">
          <div
            v-for="result in searchResults"
            :key="result.id"
            class="flex items-center gap-3 p-3 hover:bg-slate-700/50 cursor-pointer transition-colors border-b border-slate-700/50 last:border-0"
            @click="selectUser(result)"
          >
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white font-bold">
              {{ result.username?.charAt(0).toUpperCase() || '?' }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-white font-medium truncate">{{ result.username }}</p>
              <p class="text-xs text-slate-400">ID: {{ result.id }} · Level {{ result.level || 1 }}</p>
            </div>
            <ChevronRightIcon class="w-5 h-5 text-slate-500" />
          </div>
        </div>
      </div>
      <button
        v-if="selectedUser"
        @click="clearUser"
        class="inline-flex items-center gap-2 px-4 py-3 text-slate-400 hover:text-white transition-colors"
      >
        <XMarkIcon class="w-4 h-4" />
        Clear Selection
      </button>
    </div>

    <!-- Empty State -->
    <div v-if="!selectedUser" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-12">
      <div class="flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-700/30 flex items-center justify-center mb-4">
          <UserIcon class="w-8 h-8 text-slate-500" />
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">No User Selected</h3>
        <p class="text-slate-400 text-center max-w-sm">Search for a user above to view their details, inventory, timers, and activity</p>
      </div>
    </div>

    <!-- User Selected View -->
    <template v-else>
      <!-- User Profile Card -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <div class="flex items-start gap-6">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
            {{ selectedUser.username.charAt(0).toUpperCase() }}
          </div>
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-1">
              <h2 class="text-xl font-bold text-white">{{ selectedUser.username }}</h2>
              <span class="text-xs font-mono text-slate-500">#{{ selectedUser.id }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm text-slate-400">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-700/50">
                <SparklesIcon class="w-4 h-4 text-amber-400" />
                Level {{ selectedUser.level }}
              </span>
              <span v-if="selectedUser.currentRank?.name || selectedUser.current_rank?.name" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-700/50">
                <TrophyIcon class="w-4 h-4 text-purple-400" />
                {{ selectedUser.currentRank?.name || selectedUser.current_rank?.name }}
              </span>
              <span v-if="selectedUser.location?.name" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-700/50">
                <MapPinIcon class="w-4 h-4 text-blue-400" />
                {{ selectedUser.location.name }}
              </span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="refreshData"
              class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700/50 hover:bg-slate-700 text-slate-300 rounded-xl transition-colors"
            >
              <ArrowPathIcon class="w-4 h-4" />
              Refresh
            </button>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex items-center gap-1 p-1 bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl overflow-x-auto">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          :class="[
            'flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium text-sm whitespace-nowrap transition-all',
            activeTab === tab.key
              ? 'bg-amber-500 text-white shadow-lg'
              : 'text-slate-400 hover:text-white hover:bg-slate-700/50'
          ]"
        >
          <component :is="tab.icon" class="w-4 h-4" />
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab Content -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 overflow-hidden">
        <!-- Loading State -->
        <div v-if="isLoading" class="p-12">
          <div class="flex flex-col items-center justify-center">
            <div class="w-12 h-12 border-4 border-amber-500/30 border-t-amber-500 rounded-full animate-spin mb-4"></div>
            <p class="text-slate-400">Loading data...</p>
          </div>
        </div>

        <!-- Items Tab -->
        <div v-else-if="activeTab === 'items'" class="divide-y divide-slate-700/50">
          <div class="p-4 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-white">Inventory Items</h3>
              <p class="text-sm text-slate-400">{{ inventory.total_items || 0 }} items · ${{ (inventory.total_value || 0).toLocaleString() }} value</p>
            </div>
          </div>
          <div v-if="inventory.inventory?.length" class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="text-left text-xs font-medium text-slate-400 uppercase tracking-wider">
                  <th class="p-4">Item</th>
                  <th class="p-4">Type</th>
                  <th class="p-4">Codename</th>
                  <th class="p-4">Qty</th>
                  <th class="p-4">Value</th>
                  <th class="p-4">Equipped</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-700/30">
                <tr v-for="item in inventory.inventory" :key="item.id" class="hover:bg-slate-700/20 transition-colors">
                  <td class="p-4 text-white font-medium">{{ item.item_name }}</td>
                  <td class="p-4">
                    <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-700/50 text-slate-300">
                      {{ item.item_type }}
                    </span>
                  </td>
                  <td class="p-4">
                    <code class="text-xs text-slate-500 bg-slate-900/50 px-2 py-1 rounded">{{ item.item_codename || '-' }}</code>
                  </td>
                  <td class="p-4 text-slate-300">{{ item.quantity }}</td>
                  <td class="p-4 text-emerald-400">${{ (item.item_value * item.quantity).toLocaleString() }}</td>
                  <td class="p-4">
                    <span v-if="item.equipped" class="inline-flex items-center gap-1 text-emerald-400">
                      <CheckCircleIcon class="w-4 h-4" />
                    </span>
                    <span v-else class="text-slate-600">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="p-12 text-center">
            <CubeIcon class="w-12 h-12 text-slate-600 mx-auto mb-3" />
            <p class="text-slate-400">No items in inventory</p>
          </div>
        </div>

        <!-- Jobs Tab -->
        <div v-else-if="activeTab === 'jobs'" class="divide-y divide-slate-700/50">
          <div class="p-4">
            <h3 class="text-lg font-semibold text-white">Job Statistics</h3>
            <p class="text-sm text-slate-400">Completed jobs and activities</p>
          </div>
          <div v-if="jobs.length" class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="text-left text-xs font-medium text-slate-400 uppercase tracking-wider">
                  <th class="p-4">Job Name</th>
                  <th class="p-4">Codename</th>
                  <th class="p-4">Type</th>
                  <th class="p-4">Completed</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-700/30">
                <tr v-for="job in jobs" :key="job.codename" class="hover:bg-slate-700/20 transition-colors">
                  <td class="p-4 text-white font-medium">{{ job.name }}</td>
                  <td class="p-4">
                    <code class="text-xs text-slate-500 bg-slate-900/50 px-2 py-1 rounded">{{ job.codename || '-' }}</code>
                  </td>
                  <td class="p-4">
                    <span :class="getTypeBadgeClass(job.type)">{{ job.type }}</span>
                  </td>
                  <td class="p-4 text-white font-medium">{{ job.total_completed }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="p-12 text-center">
            <BriefcaseIcon class="w-12 h-12 text-slate-600 mx-auto mb-3" />
            <p class="text-slate-400">No job statistics found</p>
          </div>
        </div>

        <!-- Job History Tab -->
        <div v-else-if="activeTab === 'history'" class="divide-y divide-slate-700/50">
          <div class="p-4 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-white">Job History</h3>
              <p class="text-sm text-slate-400">Recent job runs and results</p>
            </div>
            <select
              v-model="historyFilter"
              @change="loadJobHistory"
              class="px-4 py-2 bg-slate-700/50 border border-slate-600/50 rounded-xl text-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
            >
              <option value="all">All Types</option>
              <option value="crime_attempt">Crimes</option>
              <option value="theft_attempt">Theft</option>
              <option value="gym_train">Gym</option>
              <option value="organized_crime">Organized Crime</option>
            </select>
          </div>
          <div v-if="jobHistory.length" class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="text-left text-xs font-medium text-slate-400 uppercase tracking-wider">
                  <th class="p-4">Job</th>
                  <th class="p-4">Codename</th>
                  <th class="p-4">Type</th>
                  <th class="p-4">Result</th>
                  <th class="p-4">Time</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-700/30">
                <tr v-for="entry in jobHistory" :key="entry.id" class="hover:bg-slate-700/20 transition-colors">
                  <td class="p-4 text-white font-medium">{{ entry.name }}</td>
                  <td class="p-4">
                    <code class="text-xs text-slate-500 bg-slate-900/50 px-2 py-1 rounded">{{ entry.codename || '-' }}</code>
                  </td>
                  <td class="p-4">
                    <span :class="getTypeBadgeClass(entry.type)">{{ entry.type }}</span>
                  </td>
                  <td class="p-4">
                    <span v-if="entry.success === true" class="inline-flex items-center gap-1.5 text-emerald-400">
                      <CheckCircleIcon class="w-4 h-4" /> Success
                    </span>
                    <span v-else-if="entry.success === false" class="inline-flex items-center gap-1.5 text-red-400">
                      <XCircleIcon class="w-4 h-4" /> Failed
                    </span>
                    <span v-else class="text-slate-500">—</span>
                  </td>
                  <td class="p-4 text-slate-400 text-sm">{{ formatDateTime(entry.time) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="p-12 text-center">
            <ClockIcon class="w-12 h-12 text-slate-600 mx-auto mb-3" />
            <p class="text-slate-400">No job history found</p>
          </div>
        </div>

        <!-- Timers Tab -->
        <div v-else-if="activeTab === 'timers'" class="divide-y divide-slate-700/50">
          <div class="p-4 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-white">Active Timers & Cooldowns</h3>
              <p class="text-sm text-slate-400">Current cooldown periods</p>
            </div>
            <button
              @click="loadTimers"
              class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700/50 hover:bg-slate-700 text-slate-300 rounded-xl transition-colors"
            >
              <ArrowPathIcon class="w-4 h-4" />
              Refresh
            </button>
          </div>
          <div v-if="allTimers.length" class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="timer in allTimers"
              :key="timer.type"
              class="relative rounded-xl border overflow-hidden"
              :class="getTimerCardClass(timer.type)"
            >
              <div class="p-4">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-white">{{ formatTimerType(timer.type) }}</span>
                  <button
                    @click="clearTimer(timer.type)"
                    class="p-1.5 rounded-lg hover:bg-red-500/20 text-red-400 transition-colors"
                    title="Clear timer"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
                <p class="text-2xl font-bold text-white mb-1">{{ formatRemaining(timer.expires_at) }}</p>
                <p class="text-xs text-slate-400">Expires: {{ formatDateTime(timer.expires_at) }}</p>
              </div>
              <div class="h-1 bg-slate-700/50">
                <div class="h-full bg-current opacity-50" :style="{ width: getTimerProgress(timer) + '%' }"></div>
              </div>
            </div>
          </div>
          <div v-else class="p-12 text-center">
            <ClockIcon class="w-12 h-12 text-slate-600 mx-auto mb-3" />
            <p class="text-slate-400">No active timers</p>
          </div>
        </div>

        <!-- Roles Tab -->
        <div v-else-if="activeTab === 'roles'" class="divide-y divide-slate-700/50">
          <div class="p-4">
            <h3 class="text-lg font-semibold text-white">User Roles</h3>
            <p class="text-sm text-slate-400">Manage roles and permissions for this user</p>
          </div>

          <!-- Current Roles -->
          <div class="p-4">
            <h4 class="text-sm font-medium text-slate-400 uppercase tracking-wider mb-3">Assigned Roles</h4>
            <div v-if="userRoles.length" class="flex flex-wrap gap-2">
              <div
                v-for="role in userRoles"
                :key="role"
                class="inline-flex items-center gap-2 px-3 py-2 bg-amber-500/20 text-amber-400 rounded-xl"
              >
                <ShieldCheckIcon class="w-4 h-4" />
                <span class="font-medium">{{ role }}</span>
                <button
                  @click="removeRole(role)"
                  :disabled="savingRole"
                  class="p-0.5 rounded hover:bg-white/10 text-amber-300 hover:text-red-400 transition-colors"
                  title="Remove role"
                >
                  <XMarkIcon class="w-4 h-4" />
                </button>
              </div>
            </div>
            <p v-else class="text-slate-500">No roles assigned</p>
          </div>

          <!-- Available Roles -->
          <div class="p-4">
            <h4 class="text-sm font-medium text-slate-400 uppercase tracking-wider mb-3">Available Roles</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
              <div
                v-for="role in availableRoles"
                :key="role.id"
                class="p-4 bg-slate-700/30 rounded-xl border border-slate-600/50"
              >
                <div class="flex items-center justify-between gap-3">
                  <div class="flex-1 min-w-0">
                    <h5 class="font-medium text-white truncate">{{ role.name }}</h5>
                    <p class="text-xs text-slate-400">{{ role.permissions?.length || 0 }} permissions</p>
                  </div>
                  <button
                    v-if="!userRoles.includes(role.name)"
                    @click="assignRole(role.name)"
                    :disabled="savingRole"
                    class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors"
                  >
                    Assign
                  </button>
                  <span v-else class="inline-flex items-center gap-1 text-emerald-400 text-sm">
                    <CheckCircleIcon class="w-4 h-4" />
                    Assigned
                  </span>
                </div>
              </div>
            </div>
            <div v-if="!availableRoles.length" class="text-center py-8">
              <ShieldCheckIcon class="w-12 h-12 text-slate-600 mx-auto mb-3" />
              <p class="text-slate-400">No roles available</p>
            </div>
          </div>
        </div>

        <!-- Flags Tab -->
        <div v-else-if="activeTab === 'flags'" class="divide-y divide-slate-700/50">
          <div class="p-4 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-white">User Flags & Tags</h3>
              <p class="text-sm text-slate-400">Status indicators and warnings</p>
            </div>
            <button
              @click="showAddFlag = true"
              class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition-colors"
            >
              <PlusIcon class="w-4 h-4" />
              Add Flag
            </button>
          </div>
          <div v-if="flags.length" class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="flag in flags"
              :key="flag.type"
              class="rounded-xl p-4"
              :class="getFlagCardClass(flag.severity)"
            >
              <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                  <h4 class="font-medium text-white truncate">{{ flag.label }}</h4>
                  <p v-if="flag.value && flag.value !== true" class="text-sm text-slate-300 mt-1">
                    {{ typeof flag.value === 'string' && flag.value.includes('T') ? formatDateTime(flag.value) : flag.value }}
                  </p>
                  <p v-if="flag.reason" class="text-xs text-slate-400 mt-1">{{ flag.reason }}</p>
                </div>
                <button
                  v-if="flag.type !== 'role'"
                  @click="removeFlag(flag.type)"
                  class="p-1.5 rounded-lg hover:bg-white/10 text-slate-400 hover:text-red-400 transition-colors"
                >
                  <XMarkIcon class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
          <div v-else class="p-12 text-center">
            <FlagIcon class="w-12 h-12 text-slate-600 mx-auto mb-3" />
            <p class="text-slate-400">No flags assigned</p>
          </div>
        </div>

        <!-- Activity Tab -->
        <div v-else-if="activeTab === 'activity'" class="divide-y divide-slate-700/50">
          <div class="p-4">
            <h3 class="text-lg font-semibold text-white">Recent Activity</h3>
            <p class="text-sm text-slate-400">User actions and events</p>
          </div>
          <div v-if="activity.length" class="divide-y divide-slate-700/30">
            <div
              v-for="log in activity"
              :key="log.id"
              class="flex items-start gap-4 p-4 hover:bg-slate-700/20 transition-colors"
            >
              <div :class="getActivityIconClass(log.type)" class="p-2 rounded-xl">
                <component :is="getActivityIcon(log.type)" class="w-5 h-5" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-white">{{ log.description }}</p>
                <p class="text-sm text-slate-400 mt-1">{{ formatDateTime(log.created_at) }}</p>
              </div>
              <span :class="getTypeBadgeClass(log.type)">{{ log.type }}</span>
            </div>
          </div>
          <div v-else class="p-12 text-center">
            <ClipboardDocumentListIcon class="w-12 h-12 text-slate-600 mx-auto mb-3" />
            <p class="text-slate-400">No activity found</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Add Flag Modal -->
    <Teleport to="body">
      <div v-if="showAddFlag" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showAddFlag = false"></div>
        <div class="relative bg-slate-800 rounded-2xl border border-slate-700/50 shadow-2xl w-full max-w-md">
          <div class="p-6 border-b border-slate-700/50">
            <h3 class="text-lg font-semibold text-white">Add Flag</h3>
            <p class="text-sm text-slate-400">Add a new flag or tag to this user</p>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Flag Type</label>
              <input
                v-model="newFlag.type"
                type="text"
                placeholder="e.g., suspicious, vip, warning"
                class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Display Label</label>
              <input
                v-model="newFlag.label"
                type="text"
                placeholder="What to show on the flag"
                class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Reason (optional)</label>
              <textarea
                v-model="newFlag.reason"
                placeholder="Why is this flag being added?"
                rows="3"
                class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 resize-none"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Severity</label>
              <div class="grid grid-cols-4 gap-2">
                <button
                  v-for="sev in severities"
                  :key="sev.value"
                  @click="newFlag.severity = sev.value"
                  :class="[
                    'px-3 py-2 rounded-xl text-sm font-medium transition-all',
                    newFlag.severity === sev.value ? sev.activeClass : 'bg-slate-700/50 text-slate-400 hover:bg-slate-700'
                  ]"
                >
                  {{ sev.label }}
                </button>
              </div>
            </div>
          </div>
          <div class="p-6 border-t border-slate-700/50 flex justify-end gap-3">
            <button
              @click="showAddFlag = false"
              class="px-4 py-2 bg-slate-700/50 hover:bg-slate-700 text-slate-300 rounded-xl transition-colors"
            >
              Cancel
            </button>
            <button
              @click="addFlag"
              class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition-colors"
            >
              Add Flag
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import api from '@/services/api'
import {
  MagnifyingGlassIcon,
  UserIcon,
  XMarkIcon,
  ChevronRightIcon,
  SparklesIcon,
  TrophyIcon,
  MapPinIcon,
  ArrowPathIcon,
  CubeIcon,
  BriefcaseIcon,
  ClockIcon,
  FlagIcon,
  ClipboardDocumentListIcon,
  CheckCircleIcon,
  XCircleIcon,
  PlusIcon,
  TrashIcon,
  ShieldCheckIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  FireIcon,
  BoltIcon,
} from '@heroicons/vue/24/outline'

const searchQuery = ref('')
const searchResults = ref([])
const selectedUser = ref(null)
const activeTab = ref('items')

const tabs = [
  { key: 'items', label: 'Items', icon: CubeIcon },
  { key: 'jobs', label: 'Jobs', icon: BriefcaseIcon },
  { key: 'history', label: 'Job History', icon: ClockIcon },
  { key: 'timers', label: 'Timers', icon: ClockIcon },
  { key: 'roles', label: 'Roles', icon: ShieldCheckIcon },
  { key: 'flags', label: 'Flags', icon: FlagIcon },
  { key: 'activity', label: 'Activity', icon: ClipboardDocumentListIcon },
]

const severities = [
  { value: 'info', label: 'Info', activeClass: 'bg-blue-500/20 text-blue-400 ring-2 ring-blue-500/50' },
  { value: 'success', label: 'Good', activeClass: 'bg-emerald-500/20 text-emerald-400 ring-2 ring-emerald-500/50' },
  { value: 'warning', label: 'Warn', activeClass: 'bg-amber-500/20 text-amber-400 ring-2 ring-amber-500/50' },
  { value: 'danger', label: 'Danger', activeClass: 'bg-red-500/20 text-red-400 ring-2 ring-red-500/50' },
]

// Data states
const inventory = ref({})
const jobs = ref([])
const jobHistory = ref([])
const timers = ref({ timers: [], user_timers: [] })
const flags = ref([])
const activity = ref([])
const historyFilter = ref('all')

// Loading states
const loadingInventory = ref(false)
const loadingJobs = ref(false)
const loadingHistory = ref(false)
const loadingTimers = ref(false)
const loadingRoles = ref(false)
const loadingFlags = ref(false)
const loadingActivity = ref(false)

// Roles data
const userRoles = ref([])
const availableRoles = ref([])
const savingRole = ref(false)

// Add flag modal
const showAddFlag = ref(false)
const newFlag = ref({
  type: '',
  label: '',
  reason: '',
  severity: 'info',
})

// Computed
const isLoading = computed(() => {
  switch (activeTab.value) {
    case 'items': return loadingInventory.value
    case 'jobs': return loadingJobs.value
    case 'history': return loadingHistory.value
    case 'timers': return loadingTimers.value
    case 'roles': return loadingRoles.value
    case 'flags': return loadingFlags.value
    case 'activity': return loadingActivity.value
    default: return false
  }
})

const allTimers = computed(() => {
  const t = [...(timers.value.user_timers || [])]
  for (const timer of (timers.value.timers || [])) {
    t.push({
      type: timer.type,
      expires_at: timer.expires_at,
    })
  }
  return t
})

// Debounce search
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    if (searchQuery.value.length >= 2) {
      searchUser()
    } else {
      searchResults.value = []
    }
  }, 300)
}

// Methods
const searchUser = async () => {
  if (!searchQuery.value.trim()) return
  try {
    const response = await api.get('/admin/user-tools/search', {
      params: { q: searchQuery.value }
    })
    searchResults.value = response.data.users || []
  } catch (err) {
    console.error('Search failed:', err)
  }
}

const selectUser = async (user) => {
  searchResults.value = []
  searchQuery.value = ''
  try {
    const response = await api.get(`/admin/user-tools/${user.id}`)
    selectedUser.value = response.data.user
    loadTabData()
  } catch (err) {
    console.error('Failed to load user:', err)
  }
}

const clearUser = () => {
  selectedUser.value = null
  inventory.value = {}
  jobs.value = []
  jobHistory.value = []
  timers.value = { timers: [], user_timers: [] }
  userRoles.value = []
  flags.value = []
  activity.value = []
}

const refreshData = () => {
  loadTabData()
}

const loadTabData = () => {
  if (!selectedUser.value) return

  switch (activeTab.value) {
    case 'items': loadInventory(); break
    case 'jobs': loadJobs(); break
    case 'history': loadJobHistory(); break
    case 'timers': loadTimers(); break
    case 'roles': loadRoles(); break
    case 'flags': loadFlags(); break
    case 'activity': loadActivity(); break
  }
}

const loadInventory = async () => {
  loadingInventory.value = true
  try {
    const response = await api.get(`/admin/user-tools/${selectedUser.value.id}/inventory`)
    inventory.value = response.data
  } catch (err) {
    console.error('Failed to load inventory:', err)
  } finally {
    loadingInventory.value = false
  }
}

const loadJobs = async () => {
  loadingJobs.value = true
  try {
    const response = await api.get(`/admin/user-tools/${selectedUser.value.id}/jobs`)
    jobs.value = response.data.jobs || []
  } catch (err) {
    console.error('Failed to load jobs:', err)
  } finally {
    loadingJobs.value = false
  }
}

const loadJobHistory = async () => {
  loadingHistory.value = true
  try {
    const response = await api.get(`/admin/user-tools/${selectedUser.value.id}/job-history`, {
      params: { type: historyFilter.value }
    })
    jobHistory.value = response.data.history || []
  } catch (err) {
    console.error('Failed to load job history:', err)
  } finally {
    loadingHistory.value = false
  }
}

const loadTimers = async () => {
  loadingTimers.value = true
  try {
    const response = await api.get(`/admin/user-tools/${selectedUser.value.id}/timers`)
    timers.value = response.data
  } catch (err) {
    console.error('Failed to load timers:', err)
  } finally {
    loadingTimers.value = false
  }
}

const clearTimer = async (timerType) => {
  if (!confirm(`Clear ${formatTimerType(timerType)} timer?`)) return
  try {
    await api.delete(`/admin/user-tools/${selectedUser.value.id}/timers/${timerType}`)
    loadTimers()
  } catch (err) {
    console.error('Failed to clear timer:', err)
  }
}

const loadRoles = async () => {
  loadingRoles.value = true
  try {
    // Load user's current roles and all available roles
    const [userResponse, rolesResponse] = await Promise.all([
      api.get(`/admin/users/${selectedUser.value.id}`),
      api.get('/admin/roles')
    ])
    userRoles.value = userResponse.data.roles?.map(r => r.name) || []
    availableRoles.value = rolesResponse.data || []
  } catch (err) {
    console.error('Failed to load roles:', err)
  } finally {
    loadingRoles.value = false
  }
}

const assignRole = async (roleName) => {
  savingRole.value = true
  try {
    await api.post(`/admin/users/${selectedUser.value.id}/roles`, { role: roleName })
    userRoles.value.push(roleName)
  } catch (err) {
    console.error('Failed to assign role:', err)
    alert('Failed to assign role')
  } finally {
    savingRole.value = false
  }
}

const removeRole = async (roleName) => {
  if (!confirm(`Remove role "${roleName}" from this user?`)) return
  savingRole.value = true
  try {
    await api.delete(`/admin/users/${selectedUser.value.id}/roles`, { data: { role: roleName } })
    userRoles.value = userRoles.value.filter(r => r !== roleName)
  } catch (err) {
    console.error('Failed to remove role:', err)
    alert('Failed to remove role')
  } finally {
    savingRole.value = false
  }
}

const loadFlags = async () => {
  loadingFlags.value = true
  try {
    const response = await api.get(`/admin/user-tools/${selectedUser.value.id}/flags`)
    flags.value = response.data.flags || []
  } catch (err) {
    console.error('Failed to load flags:', err)
  } finally {
    loadingFlags.value = false
  }
}

const addFlag = async () => {
  try {
    await api.post(`/admin/user-tools/${selectedUser.value.id}/flags`, newFlag.value)
    showAddFlag.value = false
    newFlag.value = { type: '', label: '', reason: '', severity: 'info' }
    loadFlags()
  } catch (err) {
    console.error('Failed to add flag:', err)
  }
}

const removeFlag = async (flagType) => {
  if (!confirm(`Remove this flag?`)) return
  try {
    await api.delete(`/admin/user-tools/${selectedUser.value.id}/flags/${flagType}`)
    loadFlags()
  } catch (err) {
    console.error('Failed to remove flag:', err)
  }
}

const loadActivity = async () => {
  loadingActivity.value = true
  try {
    const response = await api.get(`/admin/user-tools/${selectedUser.value.id}/activity`)
    activity.value = response.data.activity || []
  } catch (err) {
    console.error('Failed to load activity:', err)
  } finally {
    loadingActivity.value = false
  }
}

// Formatters
const formatDateTime = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatRemaining = (expiresAt) => {
  const now = new Date()
  const expires = new Date(expiresAt)
  const diff = expires - now

  if (diff <= 0) return 'Expired'

  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  const seconds = Math.floor((diff % (1000 * 60)) / 1000)

  if (hours > 0) return `${hours}h ${minutes}m`
  if (minutes > 0) return `${minutes}m ${seconds}s`
  return `${seconds}s`
}

const formatTimerType = (type) => {
  return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getTimerProgress = (timer) => {
  const now = new Date()
  const expires = new Date(timer.expires_at)
  const remaining = expires - now
  const total = 60 * 60 * 1000
  return Math.max(0, Math.min(100, (remaining / total) * 100))
}

const getTypeBadgeClass = (type) => {
  const classes = {
    crime: 'bg-purple-500/20 text-purple-400',
    crime_attempt: 'bg-purple-500/20 text-purple-400',
    theft: 'bg-amber-500/20 text-amber-400',
    theft_attempt: 'bg-amber-500/20 text-amber-400',
    gym: 'bg-emerald-500/20 text-emerald-400',
    gym_train: 'bg-emerald-500/20 text-emerald-400',
    travel: 'bg-blue-500/20 text-blue-400',
    jail: 'bg-red-500/20 text-red-400',
    hospital: 'bg-pink-500/20 text-pink-400',
    login: 'bg-emerald-500/20 text-emerald-400',
    logout: 'bg-slate-500/20 text-slate-400',
    combat: 'bg-red-500/20 text-red-400',
    mission: 'bg-purple-500/20 text-purple-400',
    organized_crime: 'bg-amber-500/20 text-amber-400',
  }
  return `inline-flex px-2.5 py-1 rounded-lg text-xs font-medium ${classes[type] || 'bg-slate-700/50 text-slate-300'}`
}

const getTimerCardClass = (type) => {
  const classes = {
    crime: 'border-purple-500/30 text-purple-400',
    theft: 'border-amber-500/30 text-amber-400',
    gym: 'border-emerald-500/30 text-emerald-400',
    travel: 'border-blue-500/30 text-blue-400',
    jail: 'border-red-500/30 text-red-400',
    hospital: 'border-pink-500/30 text-pink-400',
  }
  return classes[type] || 'border-slate-600/50 text-slate-400'
}

const getFlagCardClass = (severity) => {
  const classes = {
    info: 'bg-blue-500/10 border border-blue-500/30',
    success: 'bg-emerald-500/10 border border-emerald-500/30',
    warning: 'bg-amber-500/10 border border-amber-500/30',
    danger: 'bg-red-500/10 border border-red-500/30',
  }
  return classes[severity] || 'bg-slate-700/50 border border-slate-600/50'
}

const getActivityIcon = (type) => {
  const icons = {
    login: ShieldCheckIcon,
    logout: ShieldCheckIcon,
    combat: FireIcon,
    crime: BoltIcon,
    theft: ExclamationTriangleIcon,
  }
  return icons[type] || InformationCircleIcon
}

const getActivityIconClass = (type) => {
  const classes = {
    login: 'bg-emerald-500/20 text-emerald-400',
    logout: 'bg-slate-500/20 text-slate-400',
    combat: 'bg-red-500/20 text-red-400',
    crime: 'bg-purple-500/20 text-purple-400',
    theft: 'bg-amber-500/20 text-amber-400',
  }
  return classes[type] || 'bg-slate-700/50 text-slate-400'
}

// Watch tab changes
watch(activeTab, loadTabData)
</script>
