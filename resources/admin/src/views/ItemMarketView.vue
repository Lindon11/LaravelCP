<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-white">Item Market</h1>
        <p class="text-slate-400 mt-1">Monitor and manage the in-game item marketplace</p>
      </div>
      <div class="flex gap-3">
        <button
          @click="activeTab = 'overview'"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-all',
            activeTab === 'overview'
              ? 'bg-amber-500 text-white'
              : 'bg-slate-700/50 text-slate-300 hover:bg-slate-700'
          ]"
        >
          <ChartBarIcon class="w-5 h-5 inline-block mr-1.5 -mt-0.5" />
          Overview
        </button>
        <button
          @click="activeTab = 'listings'"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-all',
            activeTab === 'listings'
              ? 'bg-amber-500 text-white'
              : 'bg-slate-700/50 text-slate-300 hover:bg-slate-700'
          ]"
        >
          <TagIcon class="w-5 h-5 inline-block mr-1.5 -mt-0.5" />
          Listings
        </button>
        <button
          @click="activeTab = 'transactions'"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-all',
            activeTab === 'transactions'
              ? 'bg-amber-500 text-white'
              : 'bg-slate-700/50 text-slate-300 hover:bg-slate-700'
          ]"
        >
          <ArrowsRightLeftIcon class="w-5 h-5 inline-block mr-1.5 -mt-0.5" />
          Transactions
        </button>
        <button
          @click="activeTab = 'points'"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-all',
            activeTab === 'points'
              ? 'bg-amber-500 text-white'
              : 'bg-slate-700/50 text-slate-300 hover:bg-slate-700'
          ]"
        >
          <CurrencyDollarIcon class="w-5 h-5 inline-block mr-1.5 -mt-0.5" />
          Points Market
        </button>
      </div>
    </div>

    <!-- Overview Tab -->
    <template v-if="activeTab === 'overview'">
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <div
          v-for="stat in marketStats"
          :key="stat.label"
          class="relative overflow-hidden rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6 group hover:border-slate-600/50 transition-all"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-medium text-slate-400">{{ stat.label }}</p>
              <p class="mt-2 text-3xl font-bold text-white">{{ stat.value }}</p>
              <p v-if="stat.subtext" class="mt-1 text-xs text-slate-500">{{ stat.subtext }}</p>
            </div>
            <div :class="['p-3 rounded-xl', stat.color]">
              <component :is="stat.icon" class="w-6 h-6 text-white" />
            </div>
          </div>
        </div>
      </div>

      <!-- Charts & Lists -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Traded Items -->
        <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
          <h2 class="text-lg font-semibold text-white mb-4">Top Traded Items</h2>
          <div class="space-y-3">
            <div
              v-for="(item, index) in topItems"
              :key="item.item_id"
              class="flex items-center gap-4 p-3 rounded-xl bg-slate-700/30"
            >
              <span class="text-lg font-bold text-amber-400 w-6">{{ index + 1 }}</span>
              <div class="w-10 h-10 rounded-lg bg-slate-600/50 flex items-center justify-center overflow-hidden">
                <img v-if="item.item_image" :src="item.item_image" :alt="item.item_name" class="w-full h-full object-cover">
                <CubeIcon v-else class="w-5 h-5 text-slate-400" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ item.item_name }}</p>
                <p class="text-xs text-slate-400">{{ item.trade_count }} trades</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-semibold text-emerald-400">${{ formatNumber(item.total_volume) }}</p>
                <p class="text-xs text-slate-500">volume</p>
              </div>
            </div>
            <p v-if="!topItems.length" class="text-center text-slate-400 py-4">No trading data yet</p>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
          <h2 class="text-lg font-semibold text-white mb-4">Recent Transactions</h2>
          <div class="space-y-3">
            <div
              v-for="tx in recentTransactions"
              :key="tx.id"
              class="flex items-center gap-3 p-3 rounded-xl bg-slate-700/30"
            >
              <div class="w-8 h-8 rounded-lg bg-slate-600/50 flex items-center justify-center overflow-hidden flex-shrink-0">
                <img v-if="tx.item_image" :src="tx.item_image" :alt="tx.item" class="w-full h-full object-cover">
                <CubeIcon v-else class="w-4 h-4 text-slate-400" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-white truncate">
                  <span class="font-medium">{{ tx.buyer }}</span>
                  <span class="text-slate-400"> bought </span>
                  <span class="text-amber-400">{{ tx.quantity }}x {{ tx.item }}</span>
                </p>
                <p class="text-xs text-slate-500">from {{ tx.seller }} • {{ formatTime(tx.completed_at) }}</p>
              </div>
              <div class="text-right flex-shrink-0">
                <p class="text-sm font-semibold text-emerald-400">${{ formatNumber(tx.total_price) }}</p>
              </div>
            </div>
            <p v-if="!recentTransactions.length" class="text-center text-slate-400 py-4">No transactions yet</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Listings Tab -->
    <template v-if="activeTab === 'listings'">
      <!-- Filters -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-4">
        <div class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-[200px]">
            <input
              v-model="listingsSearch"
              @input="debounceListingsSearch"
              type="text"
              placeholder="Search items..."
              class="w-full px-4 py-2 rounded-lg bg-slate-700/50 border border-slate-600/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
            >
          </div>
          <select
            v-model="listingsStatus"
            @change="fetchListings"
            class="px-4 py-2 rounded-lg bg-slate-700/50 border border-slate-600/50 text-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
          >
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="sold">Sold</option>
            <option value="cancelled">Cancelled</option>
            <option value="expired">Expired</option>
          </select>
        </div>
      </div>

      <!-- Listings Table -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-700/30">
              <tr>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Item</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Seller</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Qty</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Price/Unit</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Total</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Status</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Listed</th>
                <th class="text-right px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
              <tr v-for="listing in listings" :key="listing.id" class="hover:bg-slate-700/20">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-slate-600/50 flex items-center justify-center overflow-hidden">
                      <img v-if="listing.item_image" :src="listing.item_image" :alt="listing.item_name" class="w-full h-full object-cover">
                      <CubeIcon v-else class="w-5 h-5 text-slate-400" />
                    </div>
                    <div>
                      <p class="text-sm font-medium text-white">{{ listing.item_name }}</p>
                      <p v-if="listing.item_rarity" :class="getRarityClass(listing.item_rarity)" class="text-xs capitalize">{{ listing.item_rarity }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">{{ listing.seller }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">{{ listing.quantity }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">${{ formatNumber(listing.price_per_unit) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-emerald-400">${{ formatNumber(listing.total_price) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(listing.status)" class="px-2 py-1 rounded-lg text-xs font-medium capitalize">
                    {{ listing.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">{{ formatTime(listing.listed_at) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <button
                    v-if="listing.status === 'active'"
                    @click="cancelListing(listing.id)"
                    class="p-1.5 rounded-lg hover:bg-red-500/20 text-slate-400 hover:text-red-400 transition-colors"
                    title="Cancel Listing"
                  >
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                  <button
                    @click="deleteListing(listing.id)"
                    class="p-1.5 rounded-lg hover:bg-red-500/20 text-slate-400 hover:text-red-400 transition-colors ml-1"
                    title="Delete Listing"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </td>
              </tr>
              <tr v-if="!listings.length">
                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                  No listings found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="listingsPagination.last_page > 1" class="px-6 py-4 border-t border-slate-700/50 flex items-center justify-between">
          <p class="text-sm text-slate-400">
            Showing {{ (listingsPagination.current_page - 1) * listingsPagination.per_page + 1 }}
            to {{ Math.min(listingsPagination.current_page * listingsPagination.per_page, listingsPagination.total) }}
            of {{ listingsPagination.total }} listings
          </p>
          <div class="flex gap-2">
            <button
              @click="listingsPage--; fetchListings()"
              :disabled="listingsPagination.current_page <= 1"
              class="px-3 py-1.5 rounded-lg bg-slate-700/50 text-slate-300 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            <button
              @click="listingsPage++; fetchListings()"
              :disabled="listingsPagination.current_page >= listingsPagination.last_page"
              class="px-3 py-1.5 rounded-lg bg-slate-700/50 text-slate-300 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- Transactions Tab -->
    <template v-if="activeTab === 'transactions'">
      <!-- Filters -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-4">
        <div class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-[200px]">
            <input
              v-model="transactionsDateFrom"
              @change="fetchTransactions"
              type="date"
              class="w-full px-4 py-2 rounded-lg bg-slate-700/50 border border-slate-600/50 text-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
            >
          </div>
          <div class="flex-1 min-w-[200px]">
            <input
              v-model="transactionsDateTo"
              @change="fetchTransactions"
              type="date"
              class="w-full px-4 py-2 rounded-lg bg-slate-700/50 border border-slate-600/50 text-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
            >
          </div>
        </div>
      </div>

      <!-- Transactions Table -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-700/30">
              <tr>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Item</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Seller</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Buyer</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Qty</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Price</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Fee</th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-400 uppercase tracking-wider">Completed</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
              <tr v-for="tx in transactions" :key="tx.id" class="hover:bg-slate-700/20">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-600/50 flex items-center justify-center overflow-hidden">
                      <img v-if="tx.item_image" :src="tx.item_image" :alt="tx.item_name" class="w-full h-full object-cover">
                      <CubeIcon v-else class="w-4 h-4 text-slate-400" />
                    </div>
                    <span class="text-sm font-medium text-white">{{ tx.item_name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">{{ tx.seller }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">{{ tx.buyer }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">{{ tx.quantity }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-emerald-400">${{ formatNumber(tx.total_price) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-amber-400">${{ formatNumber(tx.market_fee) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">{{ formatTime(tx.completed_at) }}</td>
              </tr>
              <tr v-if="!transactions.length">
                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                  No transactions found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="transactionsPagination.last_page > 1" class="px-6 py-4 border-t border-slate-700/50 flex items-center justify-between">
          <p class="text-sm text-slate-400">
            Showing {{ (transactionsPagination.current_page - 1) * transactionsPagination.per_page + 1 }}
            to {{ Math.min(transactionsPagination.current_page * transactionsPagination.per_page, transactionsPagination.total) }}
            of {{ transactionsPagination.total }} transactions
          </p>
          <div class="flex gap-2">
            <button
              @click="transactionsPage--; fetchTransactions()"
              :disabled="transactionsPagination.current_page <= 1"
              class="px-3 py-1.5 rounded-lg bg-slate-700/50 text-slate-300 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            <button
              @click="transactionsPage++; fetchTransactions()"
              :disabled="transactionsPagination.current_page >= transactionsPagination.last_page"
              class="px-3 py-1.5 rounded-lg bg-slate-700/50 text-slate-300 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- Points Market Tab -->
    <template v-if="activeTab === 'points'">
      <!-- Points Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <div
          v-for="stat in pointsStats"
          :key="stat.label"
          class="relative overflow-hidden rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-medium text-slate-400">{{ stat.label }}</p>
              <p class="mt-2 text-3xl font-bold text-white">{{ stat.value }}</p>
            </div>
            <div :class="['p-3 rounded-xl', stat.color]">
              <component :is="stat.icon" class="w-6 h-6 text-white" />
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Active Listings -->
        <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
          <h2 class="text-lg font-semibold text-white mb-4">Active Points Listings</h2>
          <div class="space-y-3">
            <div
              v-for="listing in pointsListings"
              :key="listing.id"
              class="flex items-center justify-between p-4 rounded-xl bg-slate-700/30"
            >
              <div>
                <p class="text-white font-medium">{{ formatNumber(listing.points_amount) }} Points</p>
                <p class="text-xs text-slate-400">by {{ listing.seller }}</p>
              </div>
              <div class="text-right">
                <p class="text-emerald-400 font-semibold">${{ formatNumber(listing.cash_price) }}</p>
                <p class="text-xs text-slate-400">{{ listing.rate }} pts/$1</p>
              </div>
            </div>
            <p v-if="!pointsListings.length" class="text-center text-slate-400 py-4">No active listings</p>
          </div>
        </div>

        <!-- Recent Points Transactions -->
        <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
          <h2 class="text-lg font-semibold text-white mb-4">Recent Points Transactions</h2>
          <div class="space-y-3">
            <div
              v-for="tx in pointsTransactions"
              :key="tx.id"
              class="flex items-center gap-3 p-3 rounded-xl bg-slate-700/30"
            >
              <div class="flex-1">
                <p class="text-sm text-white">
                  <span class="font-medium">{{ tx.buyer }}</span>
                  <span class="text-slate-400"> bought </span>
                  <span class="text-amber-400">{{ formatNumber(tx.points_amount) }} pts</span>
                </p>
                <p class="text-xs text-slate-500">from {{ tx.seller }} • {{ formatTime(tx.completed_at) }}</p>
              </div>
              <div class="text-emerald-400 font-semibold">${{ formatNumber(tx.cash_paid) }}</div>
            </div>
            <p v-if="!pointsTransactions.length" class="text-center text-slate-400 py-4">No transactions yet</p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import {
  ChartBarIcon,
  TagIcon,
  ArrowsRightLeftIcon,
  CurrencyDollarIcon,
  CubeIcon,
  ShoppingCartIcon,
  BanknotesIcon,
  ReceiptPercentIcon,
  XMarkIcon,
  TrashIcon,
  ClockIcon,
  StarIcon
} from '@heroicons/vue/24/outline'

const toast = useToast()

// Tab state
const activeTab = ref('overview')

// Overview data
const stats = ref({})
const topItems = ref([])
const recentTransactions = ref([])

// Listings data
const listings = ref([])
const listingsSearch = ref('')
const listingsStatus = ref('all')
const listingsPage = ref(1)
const listingsPagination = ref({ total: 0, per_page: 20, current_page: 1, last_page: 1 })

// Transactions data
const transactions = ref([])
const transactionsDateFrom = ref('')
const transactionsDateTo = ref('')
const transactionsPage = ref(1)
const transactionsPagination = ref({ total: 0, per_page: 20, current_page: 1, last_page: 1 })

// Points market data
const pointsStats = ref([])
const pointsListings = ref([])
const pointsTransactions = ref([])

// Computed stats for display
const marketStats = computed(() => [
  {
    label: 'Total Listings',
    value: formatNumber(stats.value.total_listings || 0),
    subtext: `${stats.value.active_listings || 0} active`,
    icon: TagIcon,
    color: 'bg-blue-500'
  },
  {
    label: 'Total Transactions',
    value: formatNumber(stats.value.total_transactions || 0),
    subtext: `${stats.value.today_transactions || 0} today`,
    icon: ArrowsRightLeftIcon,
    color: 'bg-emerald-500'
  },
  {
    label: 'Total Volume',
    value: '$' + formatNumber(stats.value.total_volume || 0),
    subtext: `$${formatNumber(stats.value.today_volume || 0)} today`,
    icon: BanknotesIcon,
    color: 'bg-amber-500'
  },
  {
    label: 'Market Fees',
    value: '$' + formatNumber(stats.value.total_fees || 0),
    icon: ReceiptPercentIcon,
    color: 'bg-purple-500'
  }
])

// Debounce for search
let searchTimeout = null
const debounceListingsSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    listingsPage.value = 1
    fetchListings()
  }, 300)
}

// Fetch overview data
const fetchOverview = async () => {
  try {
    const response = await api.get('/admin/item-market')
    stats.value = response.data.stats || {}
    topItems.value = response.data.top_items || []
    recentTransactions.value = response.data.recent_transactions || []
  } catch (error) {
    console.error('Failed to fetch item market overview:', error)
    toast.error('Failed to load market overview')
  }
}

// Fetch listings
const fetchListings = async () => {
  try {
    const params = {
      page: listingsPage.value,
      per_page: 20
    }
    if (listingsSearch.value) params.search = listingsSearch.value
    if (listingsStatus.value !== 'all') params.status = listingsStatus.value

    const response = await api.get('/admin/item-market/listings', { params })
    listings.value = response.data.listings || []
    listingsPagination.value = response.data.pagination || listingsPagination.value
  } catch (error) {
    console.error('Failed to fetch listings:', error)
    toast.error('Failed to load listings')
  }
}

// Fetch transactions
const fetchTransactions = async () => {
  try {
    const params = {
      page: transactionsPage.value,
      per_page: 20
    }
    if (transactionsDateFrom.value) params.from_date = transactionsDateFrom.value
    if (transactionsDateTo.value) params.to_date = transactionsDateTo.value

    const response = await api.get('/admin/item-market/transactions', { params })
    transactions.value = response.data.transactions || []
    transactionsPagination.value = response.data.pagination || transactionsPagination.value
  } catch (error) {
    console.error('Failed to fetch transactions:', error)
    toast.error('Failed to load transactions')
  }
}

// Fetch points market data
const fetchPointsMarket = async () => {
  try {
    const response = await api.get('/admin/item-market/points-market')
    const data = response.data

    pointsStats.value = [
      {
        label: 'Active Listings',
        value: formatNumber(data.stats?.active_listings || 0),
        icon: TagIcon,
        color: 'bg-blue-500'
      },
      {
        label: 'Total Transactions',
        value: formatNumber(data.stats?.total_transactions || 0),
        icon: ArrowsRightLeftIcon,
        color: 'bg-emerald-500'
      },
      {
        label: 'Points Traded',
        value: formatNumber(data.stats?.total_points_traded || 0),
        icon: StarIcon,
        color: 'bg-amber-500'
      },
      {
        label: 'Cash Traded',
        value: '$' + formatNumber(data.stats?.total_cash_traded || 0),
        icon: BanknotesIcon,
        color: 'bg-purple-500'
      }
    ]

    pointsListings.value = data.active_listings || []
    pointsTransactions.value = data.recent_transactions || []
  } catch (error) {
    console.error('Failed to fetch points market:', error)
    toast.error('Failed to load points market')
  }
}

// Cancel listing
const cancelListing = async (id) => {
  if (!confirm('Are you sure you want to cancel this listing?')) return

  try {
    await api.post(`/admin/item-market/listings/${id}/cancel`)
    toast.success('Listing cancelled')
    fetchListings()
    fetchOverview()
  } catch (error) {
    console.error('Failed to cancel listing:', error)
    toast.error('Failed to cancel listing')
  }
}

// Delete listing
const deleteListing = async (id) => {
  if (!confirm('Are you sure you want to delete this listing? This cannot be undone.')) return

  try {
    await api.delete(`/admin/item-market/listings/${id}`)
    toast.success('Listing deleted')
    fetchListings()
    fetchOverview()
  } catch (error) {
    console.error('Failed to delete listing:', error)
    toast.error('Failed to delete listing')
  }
}

// Helper functions
const formatNumber = (num) => {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
  return num?.toLocaleString() || '0'
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date

  if (diff < 60000) return 'Just now'
  if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`
  if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`
  return date.toLocaleDateString([], { month: 'short', day: 'numeric' })
}

const getRarityClass = (rarity) => {
  const classes = {
    common: 'text-slate-400',
    uncommon: 'text-emerald-400',
    rare: 'text-blue-400',
    epic: 'text-purple-400',
    legendary: 'text-amber-400'
  }
  return classes[rarity] || 'text-slate-400'
}

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-emerald-500/20 text-emerald-400',
    sold: 'bg-blue-500/20 text-blue-400',
    cancelled: 'bg-red-500/20 text-red-400',
    expired: 'bg-slate-500/20 text-slate-400'
  }
  return classes[status] || 'bg-slate-500/20 text-slate-400'
}

// Watch tab changes to fetch data
watch(activeTab, (tab) => {
  if (tab === 'listings') fetchListings()
  else if (tab === 'transactions') fetchTransactions()
  else if (tab === 'points') fetchPointsMarket()
})

onMounted(() => {
  fetchOverview()
})
</script>
