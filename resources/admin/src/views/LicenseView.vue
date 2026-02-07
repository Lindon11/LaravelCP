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

    <!-- Generate License Key (only visible when private key exists — owner only) -->
    <div v-if="canGenerate" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-indigo-500/30 p-6">
      <div class="flex items-center gap-3 mb-2">
        <CommandLineIcon class="w-6 h-6 text-indigo-400" />
        <h3 class="text-lg font-semibold text-white">Generate License Key</h3>
        <span class="text-xs bg-indigo-500/20 text-indigo-400 px-2 py-0.5 rounded-full border border-indigo-500/30">Owner Only</span>
      </div>
      <p class="text-sm text-slate-400 mb-6">Create a new license key for a customer. This section is only visible because the private signing key is present on this installation.</p>

      <form @submit.prevent="generateLicense" class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Customer Name <span class="text-red-400">*</span></label>
            <input
              v-model="genForm.customer"
              type="text"
              required
              placeholder="John Smith"
              class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
            />
          </div>
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Customer Email <span class="text-red-400">*</span></label>
            <input
              v-model="genForm.email"
              type="email"
              required
              placeholder="customer@example.com"
              class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
            />
          </div>
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Domain</label>
            <input
              v-model="genForm.domain"
              type="text"
              placeholder="* (any domain)"
              class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
            />
            <p class="text-xs text-slate-500">Use * for any domain, or specify e.g. example.com, *.example.com</p>
          </div>
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">License Tier</label>
            <select
              v-model="genForm.tier"
              class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
            >
              <option value="standard">Standard</option>
              <option value="extended">Extended</option>
              <option value="unlimited">Unlimited</option>
            </select>
          </div>
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Expires</label>
            <div class="flex gap-3">
              <select
                v-model="expiryType"
                class="px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
              >
                <option value="lifetime">Lifetime</option>
                <option value="date">Custom Date</option>
              </select>
              <input
                v-if="expiryType === 'date'"
                v-model="genForm.expires"
                type="date"
                class="flex-1 px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
              />
            </div>
          </div>
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-300">Max Users</label>
            <input
              v-model.number="genForm.max_users"
              type="number"
              min="0"
              placeholder="0 = Unlimited"
              class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
            />
            <p class="text-xs text-slate-500">0 = Unlimited users</p>
          </div>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-300">Plugins</label>
          <input
            v-model="genForm.plugins"
            type="text"
            placeholder="all"
            class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
          />
          <p class="text-xs text-slate-500">Use "all" for all plugins, or comma-separated list of plugin slugs</p>
        </div>

        <div v-if="generateError" class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
          {{ generateError }}
        </div>

        <button
          type="submit"
          :disabled="!genForm.customer || !genForm.email || generating"
          :class="[
            'px-6 py-3 rounded-xl text-sm font-semibold text-white transition-all',
            !genForm.customer || !genForm.email || generating
              ? 'bg-slate-600 cursor-not-allowed opacity-50'
              : 'bg-gradient-to-r from-indigo-500 to-purple-600 hover:shadow-lg hover:shadow-indigo-500/25 hover:-translate-y-0.5'
          ]"
        >
          <span v-if="generating" class="flex items-center gap-2">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Generating...
          </span>
          <span v-else>Generate License Key</span>
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

    <!-- Generated Keys Table (owner only) -->
    <div v-if="canGenerate" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <TableCellsIcon class="w-6 h-6 text-indigo-400" />
          <h3 class="text-lg font-semibold text-white">Generated Keys</h3>
          <span v-if="keysMeta.total" class="text-xs bg-slate-700 text-slate-300 px-2 py-0.5 rounded-full">{{ keysMeta.total }}</span>
        </div>
        <div class="flex items-center gap-3">
          <!-- Status filter -->
          <select
            v-model="keysFilter"
            @change="fetchKeys(1)"
            class="px-3 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
          >
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="activated">Activated</option>
            <option value="revoked">Revoked</option>
          </select>
          <!-- Search -->
          <div class="relative">
            <input
              v-model="keysSearch"
              @input="debouncedFetchKeys"
              type="text"
              placeholder="Search..."
              class="pl-9 pr-4 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all w-48"
            />
            <MagnifyingGlassIcon class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" />
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="keysLoading" class="flex justify-center py-8">
        <svg class="animate-spin h-6 w-6 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
      </div>

      <!-- Empty state -->
      <div v-else-if="!generatedKeys.length" class="text-center py-12">
        <KeyIcon class="w-12 h-12 text-slate-600 mx-auto mb-3" />
        <p class="text-slate-400 text-sm">No license keys generated yet.</p>
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-700/50">
              <th class="text-left py-3 px-4 text-slate-400 font-medium">ID</th>
              <th class="text-left py-3 px-4 text-slate-400 font-medium">Customer</th>
              <th class="text-left py-3 px-4 text-slate-400 font-medium">Domain</th>
              <th class="text-left py-3 px-4 text-slate-400 font-medium">Tier</th>
              <th class="text-left py-3 px-4 text-slate-400 font-medium">Expires</th>
              <th class="text-left py-3 px-4 text-slate-400 font-medium">Status</th>
              <th class="text-left py-3 px-4 text-slate-400 font-medium">Created</th>
              <th class="text-right py-3 px-4 text-slate-400 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="k in generatedKeys" :key="k.id" class="border-b border-slate-700/30 hover:bg-slate-700/20 transition-colors">
              <td class="py-3 px-4">
                <code class="text-xs text-amber-400 bg-slate-900/50 px-2 py-0.5 rounded font-mono">{{ k.license_id }}</code>
              </td>
              <td class="py-3 px-4">
                <div class="text-white font-medium">{{ k.customer }}</div>
                <div class="text-xs text-slate-500">{{ k.email }}</div>
              </td>
              <td class="py-3 px-4 text-slate-300 font-mono text-xs">{{ k.domain }}</td>
              <td class="py-3 px-4">
                <span :class="tierBadgeClass(k.tier)" class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize">{{ k.tier }}</span>
              </td>
              <td class="py-3 px-4 text-slate-300 text-xs">{{ k.expires === 'lifetime' ? 'Lifetime' : formatDate(k.expires) }}</td>
              <td class="py-3 px-4">
                <span :class="statusBadgeClass(k.status)" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                  <span :class="statusDotClass(k.status)" class="w-1.5 h-1.5 rounded-full"></span>
                  {{ k.status }}
                </span>
              </td>
              <td class="py-3 px-4 text-slate-400 text-xs">{{ formatDate(k.created_at) }}</td>
              <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    v-if="!k.is_revoked"
                    @click="openKeyDetails(k)"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all"
                    title="Details"
                  >
                    <EyeIcon class="w-4 h-4" />
                  </button>
                  <button
                    v-if="!k.is_revoked"
                    @click="confirmRevokeKey(k)"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-all"
                    title="Revoke"
                  >
                    <XCircleIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="keysMeta.last_page > 1" class="flex items-center justify-between mt-4 pt-4 border-t border-slate-700/50">
          <span class="text-xs text-slate-400">
            Showing {{ keysMeta.from }}–{{ keysMeta.to }} of {{ keysMeta.total }}
          </span>
          <div class="flex gap-1">
            <button
              v-for="page in keysMeta.last_page"
              :key="page"
              @click="fetchKeys(page)"
              :class="[
                'w-8 h-8 rounded-lg text-xs font-medium transition-all',
                page === keysMeta.current_page
                  ? 'bg-indigo-500 text-white'
                  : 'text-slate-400 hover:bg-slate-700/50 hover:text-white'
              ]"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Key Details Modal -->
    <Teleport to="body">
      <div v-if="selectedKey" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="selectedKey = null"></div>
        <div class="relative bg-slate-800 border border-slate-700 rounded-2xl p-6 max-w-lg w-full shadow-2xl">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">Key Details</h3>
            <button @click="selectedKey = null" class="text-slate-400 hover:text-white transition-colors">
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>

          <div class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-slate-700/50">
              <span class="text-slate-400">License ID</span>
              <code class="text-amber-400 font-mono text-xs">{{ selectedKey.license_id }}</code>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-700/50">
              <span class="text-slate-400">Key</span>
              <code class="text-slate-300 font-mono text-xs">{{ selectedKey.masked_key }}</code>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-700/50">
              <span class="text-slate-400">Customer</span>
              <span class="text-white">{{ selectedKey.customer }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-700/50">
              <span class="text-slate-400">Email</span>
              <span class="text-white">{{ selectedKey.email }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-700/50">
              <span class="text-slate-400">Domain</span>
              <span class="text-white font-mono text-xs">{{ selectedKey.domain }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-700/50">
              <span class="text-slate-400">Tier</span>
              <span class="text-white capitalize">{{ selectedKey.tier }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-700/50">
              <span class="text-slate-400">Status</span>
              <span :class="statusBadgeClass(selectedKey.status)" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                <span :class="statusDotClass(selectedKey.status)" class="w-1.5 h-1.5 rounded-full"></span>
                {{ selectedKey.status }}
              </span>
            </div>
            <div v-if="selectedKey.activated_domain" class="flex justify-between py-2 border-b border-slate-700/50">
              <span class="text-slate-400">Activated On</span>
              <span class="text-white">{{ selectedKey.activated_domain }}</span>
            </div>
            <div v-if="selectedKey.activated_ip" class="flex justify-between py-2 border-b border-slate-700/50">
              <span class="text-slate-400">Activated IP</span>
              <span class="text-white font-mono text-xs">{{ selectedKey.activated_ip }}</span>
            </div>
            <div v-if="selectedKey.activated_at" class="flex justify-between py-2 border-b border-slate-700/50">
              <span class="text-slate-400">Activated At</span>
              <span class="text-white">{{ formatDate(selectedKey.activated_at) }}</span>
            </div>
            <div class="py-2">
              <label class="block text-slate-400 mb-2">Notes</label>
              <textarea
                v-model="keyNotes"
                rows="3"
                placeholder="Add a note about this key..."
                class="w-full px-3 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all resize-none"
              ></textarea>
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-4">
            <button
              @click="saveKeyNotes"
              :disabled="savingNotes"
              class="px-4 py-2 rounded-xl text-sm font-medium text-white bg-indigo-500 hover:bg-indigo-600 transition-all"
            >
              {{ savingNotes ? 'Saving...' : 'Save Notes' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Revoke Confirmation Modal -->
    <Teleport to="body">
      <div v-if="revokeTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="revokeTarget = null"></div>
        <div class="relative bg-slate-800 border border-slate-700 rounded-2xl p-6 max-w-md w-full shadow-2xl">
          <h3 class="text-lg font-bold text-white mb-2">Revoke License Key?</h3>
          <p class="text-sm text-slate-400 mb-1">
            This will revoke the key for <span class="text-white font-medium">{{ revokeTarget.customer }}</span>.
          </p>
          <p class="text-sm text-red-400 mb-6">
            Note: This only marks the key as revoked in your records. The key will still work on the customer's installation unless you update the key validation to check revocation status remotely.
          </p>
          <div class="flex gap-3 justify-end">
            <button
              @click="revokeTarget = null"
              class="px-4 py-2 rounded-xl text-sm font-medium text-slate-300 border border-slate-600 hover:bg-slate-700/50 transition-all"
            >
              Cancel
            </button>
            <button
              @click="revokeKey"
              :disabled="revoking"
              class="px-4 py-2 rounded-xl text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-all"
            >
              {{ revoking ? 'Revoking...' : 'Revoke Key' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

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

    <!-- Generate Key Result Modal -->
    <Teleport to="body">
      <div v-if="generatedKey" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="generatedKey = ''"></div>
        <div class="relative bg-slate-800 border border-slate-700 rounded-2xl p-6 max-w-2xl w-full shadow-2xl">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center">
              <CheckIcon class="w-6 h-6 text-emerald-400" />
            </div>
            <h3 class="text-lg font-bold text-white">License Key Generated</h3>
          </div>
          <p class="text-sm text-slate-400 mb-4">
            Copy this key and send it to the customer. It will not be shown again.
          </p>
          <div class="relative">
            <textarea
              readonly
              :value="generatedKey"
              rows="4"
              class="w-full px-4 py-3 bg-slate-900 border border-slate-600/50 rounded-xl text-amber-400 font-mono text-xs focus:outline-none resize-none"
            ></textarea>
            <button
              @click="copyToClipboard(generatedKey)"
              class="absolute top-2 right-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-700 text-slate-300 hover:bg-slate-600 transition-all"
            >
              {{ copied ? '✓ Copied' : 'Copy' }}
            </button>
          </div>
          <div class="flex justify-end mt-4">
            <button
              @click="generatedKey = ''"
              class="px-4 py-2 rounded-xl text-sm font-medium text-white bg-gradient-to-r from-amber-500 to-orange-600 hover:shadow-lg hover:shadow-amber-500/25 transition-all"
            >
              Done
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import {
  ShieldCheckIcon,
  ExclamationTriangleIcon,
  KeyIcon,
  SparklesIcon,
  CheckIcon,
  CommandLineIcon,
  TableCellsIcon,
  MagnifyingGlassIcon,
  EyeIcon,
  XCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const { showToast } = useToast()

const loading = ref(true)
const licenseKey = ref('')
const activating = ref(false)
const activateError = ref('')
const deactivating = ref(false)
const showDeactivateConfirm = ref(false)
const canGenerate = ref(false)

// Generate form
const generating = ref(false)
const generateError = ref('')
const generatedKey = ref('')
const copied = ref(false)
const genForm = ref({
  domain: '*',
  tier: 'standard',
  customer: '',
  email: '',
  expires: 'lifetime',
  max_users: 0,
  plugins: 'all'
})

const expiryType = ref('lifetime')

// Generated keys table
const generatedKeys = ref([])
const keysLoading = ref(false)
const keysFilter = ref('')
const keysSearch = ref('')
const keysMeta = ref({ total: 0, current_page: 1, last_page: 1, from: 0, to: 0 })
const selectedKey = ref(null)
const keyNotes = ref('')
const savingNotes = ref(false)
const revokeTarget = ref(null)
const revoking = ref(false)
let searchTimeout = null

watch(expiryType, (val) => {
  if (val === 'lifetime') {
    genForm.value.expires = 'lifetime'
  }
})

const license = ref({
  licensed: false,
  can_generate: false,
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
    canGenerate.value = response.data.can_generate || false
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
    console.error('Activate error:', err.response?.status, err.response?.data)
    activateError.value = err.response?.data?.error
      || err.response?.data?.message
      || 'Failed to activate license. Please check your key.'
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

const generateLicense = async () => {
  generating.value = true
  generateError.value = ''
  try {
    const response = await api.post('/admin/license/generate', genForm.value)
    generatedKey.value = response.data.license_key
    showToast('License key generated!', 'success')
    await fetchKeys(1) // Refresh the keys table
    // Reset form
    genForm.value = {
      domain: '*',
      tier: 'standard',
      customer: '',
      email: '',
      expires: 'lifetime',
      max_users: 0,
      plugins: 'all'
    }
  } catch (err) {
    generateError.value = err.response?.data?.error || 'Failed to generate license key.'
  } finally {
    generating.value = false
  }
}

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch {
    // Fallback
    const el = document.createElement('textarea')
    el.value = text
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  }
}

// --- Generated Keys ---
const fetchKeys = async (page = 1) => {
  keysLoading.value = true
  try {
    const params = { page }
    if (keysFilter.value) params.status = keysFilter.value
    if (keysSearch.value) params.search = keysSearch.value
    const response = await api.get('/admin/license/keys', { params })
    generatedKeys.value = response.data.data || []
    keysMeta.value = {
      total: response.data.total || 0,
      current_page: response.data.current_page || 1,
      last_page: response.data.last_page || 1,
      from: response.data.from || 0,
      to: response.data.to || 0,
    }
  } catch (err) {
    console.error('Failed to fetch keys:', err)
  } finally {
    keysLoading.value = false
  }
}

const debouncedFetchKeys = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchKeys(1), 300)
}

const tierBadgeClass = (tier) => {
  const classes = {
    standard: 'bg-blue-500/20 text-blue-400 border border-blue-500/30',
    extended: 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
    unlimited: 'bg-purple-500/20 text-purple-400 border border-purple-500/30',
  }
  return classes[tier] || 'bg-slate-500/20 text-slate-400 border border-slate-500/30'
}

const statusBadgeClass = (status) => {
  const classes = {
    activated: 'bg-emerald-500/10 text-emerald-400',
    pending: 'bg-amber-500/10 text-amber-400',
    revoked: 'bg-red-500/10 text-red-400',
  }
  return classes[status] || 'bg-slate-500/10 text-slate-400'
}

const statusDotClass = (status) => {
  const classes = {
    activated: 'bg-emerald-400',
    pending: 'bg-amber-400',
    revoked: 'bg-red-400',
  }
  return classes[status] || 'bg-slate-400'
}

const openKeyDetails = (key) => {
  selectedKey.value = key
  keyNotes.value = key.notes || ''
}

const saveKeyNotes = async () => {
  savingNotes.value = true
  try {
    await api.put(`/admin/license/keys/${selectedKey.value.id}`, { notes: keyNotes.value })
    selectedKey.value.notes = keyNotes.value
    showToast('Notes saved.', 'success')
  } catch (err) {
    showToast('Failed to save notes.', 'error')
  } finally {
    savingNotes.value = false
  }
}

const confirmRevokeKey = (key) => {
  revokeTarget.value = key
}

const revokeKey = async () => {
  revoking.value = true
  try {
    await api.post(`/admin/license/keys/${revokeTarget.value.id}/revoke`)
    showToast(`Key for ${revokeTarget.value.customer} revoked.`, 'info')
    revokeTarget.value = null
    await fetchKeys(keysMeta.value.current_page)
  } catch (err) {
    showToast('Failed to revoke key.', 'error')
  } finally {
    revoking.value = false
  }
}

onMounted(() => {
  fetchLicenseStatus()
  fetchKeys()
})
</script>
