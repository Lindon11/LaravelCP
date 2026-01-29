<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    recentBans: Array,
    stats: Object,
});

const activeTab = ref('bans');
const selectedPlayer = ref(null);

const banForm = useForm({
    player_id: null,
    type: 'temporary',
    reason: '',
    duration_hours: 24,
});

const warnForm = useForm({
    player_id: null,
    severity: 'minor',
    reason: '',
});

const statsForm = useForm({
    player_id: null,
    cash: 0,
    bank: 0,
    respect: 0,
    experience: 0,
    health: null,
    level: null,
});

const grantItemForm = useForm({
    player_id: null,
    item_id: null,
    quantity: 1,
});

const announcementForm = useForm({
    title: '',
    message: '',
    type: 'info',
    target: 'all',
    min_level: null,
    max_level: null,
    location_id: null,
    expires_at: null,
    is_sticky: false,
});

const massEmailForm = useForm({
    subject: '',
    message: '',
    target: 'all',
    min_level: null,
    max_level: null,
    location_id: null,
});

function submitBan() {
    banForm.post(route('admin.moderation.ban'), {
        onSuccess: () => {
            banForm.reset();
            selectedPlayer.value = null;
        },
    });
}

function submitWarn() {
    warnForm.post(route('admin.moderation.warn'), {
        onSuccess: () => {
            warnForm.reset();
            selectedPlayer.value = null;
        },
    });
}

function submitStatsAdjustment() {
    statsForm.post(route('admin.moderation.adjust-stats'), {
        onSuccess: () => {
            statsForm.reset();
            selectedPlayer.value = null;
        },
    });
}

function submitGrantItem() {
    grantItemForm.post(route('admin.moderation.grant-item'), {
        onSuccess: () => {
            grantItemForm.reset();
        },
    });
}

function submitAnnouncement() {
    announcementForm.post(route('admin.moderation.announcement'), {
        onSuccess: () => {
            announcementForm.reset();
        },
    });
}

function submitMassEmail() {
    massEmailForm.post(route('admin.moderation.mass-email'), {
        onSuccess: () => {
            massEmailForm.reset();
        },
    });
}

function unbanPlayer(banId) {
    const reason = prompt('Reason for unban:');
    if (reason) {
        useForm({ reason }).post(route('admin.moderation.unban', banId));
    }
}

function getBanTypeColor(type) {
    return type === 'permanent' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800';
}

function getSeverityColor(severity) {
    const colors = {
        minor: 'bg-blue-100 text-blue-800',
        moderate: 'bg-yellow-100 text-yellow-800',
        severe: 'bg-red-100 text-red-800',
    };
    return colors[severity] || colors.minor;
}
</script>

<template>
    <Head title="Admin - Moderation Tools" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">🛡️ Moderation Tools</h1>
                <p class="mt-2 text-gray-600">Manage players, bans, warnings, and system announcements</p>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <span class="text-2xl">🚫</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Active Player Bans</p>
                            <p class="text-2xl font-bold">{{ stats.active_bans }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-orange-100 rounded-full">
                            <span class="text-2xl">🔒</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Active IP Bans</p>
                            <p class="text-2xl font-bold">{{ stats.active_ip_bans }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <span class="text-2xl">⚠️</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Recent Actions</p>
                            <p class="text-2xl font-bold">{{ recentBans.length }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'bans'"
                        :class="[
                            activeTab === 'bans'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium'
                        ]"
                    >
                        Bans & Warnings
                    </button>
                    <button
                        @click="activeTab = 'player-management'"
                        :class="[
                            activeTab === 'player-management'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium'
                        ]"
                    >
                        Player Management
                    </button>
                    <button
                        @click="activeTab = 'announcements'"
                        :class="[
                            activeTab === 'announcements'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium'
                        ]"
                    >
                        Announcements
                    </button>
                    <button
                        @click="activeTab = 'mass-communication'"
                        :class="[
                            activeTab === 'mass-communication'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium'
                        ]"
                    >
                        Mass Communication
                    </button>
                </nav>
            </div>

            <!-- Bans & Warnings Tab -->
            <div v-if="activeTab === 'bans'" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Ban Player Form -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold mb-4">🚫 Ban Player</h2>
                        <form @submit.prevent="submitBan" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Player ID</label>
                                <input
                                    v-model="banForm.player_id"
                                    type="number"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ban Type</label>
                                <select
                                    v-model="banForm.type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="temporary">Temporary</option>
                                    <option value="permanent">Permanent</option>
                                </select>
                            </div>

                            <div v-if="banForm.type === 'temporary'">
                                <label class="block text-sm font-medium text-gray-700">Duration (hours)</label>
                                <input
                                    v-model="banForm.duration_hours"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Reason</label>
                                <textarea
                                    v-model="banForm.reason"
                                    required
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                            </div>

                            <button
                                type="submit"
                                :disabled="banForm.processing"
                                class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 disabled:opacity-50"
                            >
                                Ban Player
                            </button>
                        </form>
                    </div>

                    <!-- Warn Player Form -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold mb-4">⚠️ Warn Player</h2>
                        <form @submit.prevent="submitWarn" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Player ID</label>
                                <input
                                    v-model="warnForm.player_id"
                                    type="number"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Severity</label>
                                <select
                                    v-model="warnForm.severity"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="minor">Minor</option>
                                    <option value="moderate">Moderate</option>
                                    <option value="severe">Severe</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Reason</label>
                                <textarea
                                    v-model="warnForm.reason"
                                    required
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                            </div>

                            <button
                                type="submit"
                                :disabled="warnForm.processing"
                                class="w-full bg-yellow-600 text-white py-2 px-4 rounded-md hover:bg-yellow-700 disabled:opacity-50"
                            >
                                Issue Warning
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Recent Bans -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold">Recent Bans</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Player</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Banned By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="ban in recentBans" :key="ban.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ ban.player.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['px-2 py-1 text-xs rounded-full', getBanTypeColor(ban.type)]">
                                            {{ ban.type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ ban.reason.substring(0, 50) }}...</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ban.banned_by.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ ban.expires_at ? new Date(ban.expires_at).toLocaleDateString() : 'Never' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button
                                            v-if="ban.is_active"
                                            @click="unbanPlayer(ban.id)"
                                            class="text-green-600 hover:text-green-900"
                                        >
                                            Unban
                                        </button>
                                        <span v-else class="text-gray-400">Inactive</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Player Management Tab -->
            <div v-if="activeTab === 'player-management'" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Adjust Stats Form -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold mb-4">📊 Adjust Player Stats</h2>
                        <form @submit.prevent="submitStatsAdjustment" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Player ID</label>
                                <input
                                    v-model="statsForm.player_id"
                                    type="number"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cash (±)</label>
                                    <input
                                        v-model.number="statsForm.cash"
                                        type="number"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Bank (±)</label>
                                    <input
                                        v-model.number="statsForm.bank"
                                        type="number"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Respect (±)</label>
                                    <input
                                        v-model.number="statsForm.respect"
                                        type="number"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Experience (±)</label>
                                    <input
                                        v-model.number="statsForm.experience"
                                        type="number"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Health (Set)</label>
                                    <input
                                        v-model.number="statsForm.health"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Level (Set)</label>
                                    <input
                                        v-model.number="statsForm.level"
                                        type="number"
                                        min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="statsForm.processing"
                                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                            >
                                Adjust Stats
                            </button>
                        </form>
                    </div>

                    <!-- Grant Item Form -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold mb-4">🎁 Grant Item</h2>
                        <form @submit.prevent="submitGrantItem" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Player ID</label>
                                <input
                                    v-model="grantItemForm.player_id"
                                    type="number"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Item ID</label>
                                <input
                                    v-model="grantItemForm.item_id"
                                    type="number"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input
                                    v-model="grantItemForm.quantity"
                                    type="number"
                                    min="1"
                                    max="1000"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <button
                                type="submit"
                                :disabled="grantItemForm.processing"
                                class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 disabled:opacity-50"
                            >
                                Grant Item
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">💡 Admin Tips</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Use negative values to subtract stats (e.g., -1000 cash)</li>
                        <li>• Health and Level are SET values, not adjustments</li>
                        <li>• Check player ID in the Users admin panel or database</li>
                        <li>• Item IDs can be found in the Items admin panel</li>
                    </ul>
                </div>
            </div>

            <!-- Announcements Tab -->
            <div v-if="activeTab === 'announcements'" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">📢 Create Announcement</h2>
                <form @submit.prevent="submitAnnouncement" class="space-y-4">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input
                                v-model="announcementForm.title"
                                type="text"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select
                                v-model="announcementForm.type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="info">Info</option>
                                <option value="warning">Warning</option>
                                <option value="success">Success</option>
                                <option value="danger">Danger</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea
                            v-model="announcementForm.message"
                            required
                            rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Target Audience</label>
                            <select
                                v-model="announcementForm.target"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="all">All Players</option>
                                <option value="online">Online Only</option>
                                <option value="level_range">Level Range</option>
                                <option value="location">Specific Location</option>
                            </select>
                        </div>

                        <div v-if="announcementForm.target === 'level_range'">
                            <label class="block text-sm font-medium text-gray-700">Min Level</label>
                            <input
                                v-model.number="announcementForm.min_level"
                                type="number"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <div v-if="announcementForm.target === 'level_range'">
                            <label class="block text-sm font-medium text-gray-700">Max Level</label>
                            <input
                                v-model.number="announcementForm.max_level"
                                type="number"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <div v-if="announcementForm.target === 'location'">
                            <label class="block text-sm font-medium text-gray-700">Location ID</label>
                            <input
                                v-model.number="announcementForm.location_id"
                                type="number"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="announcementForm.is_sticky"
                            type="checkbox"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <label class="ml-2 block text-sm text-gray-900">Pin to top (sticky)</label>
                    </div>

                    <button
                        type="submit"
                        :disabled="announcementForm.processing"
                        class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        Create Announcement
                    </button>
                </form>
            </div>

            <!-- Mass Communication Tab -->
            <div v-if="activeTab === 'mass-communication'" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">📧 Mass Email</h2>
                <form @submit.prevent="submitMassEmail" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject</label>
                        <input
                            v-model="massEmailForm.subject"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea
                            v-model="massEmailForm.message"
                            required
                            rows="6"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target Audience</label>
                        <select
                            v-model="massEmailForm.target"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="all">All Players</option>
                            <option value="level_range">Level Range</option>
                            <option value="location">Specific Location</option>
                            <option value="active">Active (Last 7 Days)</option>
                            <option value="inactive">Inactive (30+ Days)</option>
                        </select>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">
                            ⚠️ <strong>Warning:</strong> Mass emails will be sent immediately. Double-check your message and target audience.
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="massEmailForm.processing"
                        class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        Send Mass Email
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
