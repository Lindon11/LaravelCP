<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
    stats: Object,
});

const activityData = ref(null);
const serverHealth = ref(null);
const loading = ref(true);

onMounted(async () => {
    try {
        const [activityRes, healthRes] = await Promise.all([
            axios.get('/admin/system/activity'),
            axios.get('/admin/system/health'),
        ]);
        
        activityData.value = activityRes.data;
        serverHealth.value = healthRes.data;
    } catch (error) {
        console.error('Failed to load system data:', error);
    } finally {
        loading.value = false;
    }
});

function getHealthColor(status) {
    return status === 'healthy' ? 'text-green-600' : status === 'warning' ? 'text-yellow-600' : 'text-red-600';
}

function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
}
</script>

<template>
    <Head title="Admin - System Dashboard" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">📊 System Dashboard</h1>
                <p class="mt-2 text-gray-600">Monitor server health, player activity, and game statistics</p>
            </div>

            <!-- Player Stats -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">👥 Player Statistics</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-full">
                                <span class="text-2xl">👥</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Total Players</p>
                                <p class="text-2xl font-bold">{{ stats.players.total }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-full">
                                <span class="text-2xl">🟢</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Online Now</p>
                                <p class="text-2xl font-bold text-green-600">{{ stats.players.online }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 rounded-full">
                                <span class="text-2xl">📈</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Active Today</p>
                                <p class="text-2xl font-bold text-purple-600">{{ stats.players.active_today }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-orange-100 rounded-full">
                                <span class="text-2xl">✨</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">New Today</p>
                                <p class="text-2xl font-bold text-orange-600">{{ stats.players.new_today }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Stats -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">⚡ Activity Statistics</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Crimes Today</p>
                            <p class="text-3xl font-bold text-red-600">{{ stats.activity.crimes_today }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Combat Today</p>
                            <p class="text-3xl font-bold text-orange-600">{{ stats.activity.combat_today }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Total Gangs</p>
                            <p class="text-3xl font-bold text-purple-600">{{ stats.activity.gangs }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Total Users</p>
                            <p class="text-3xl font-bold text-blue-600">{{ stats.activity.users }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Economy Stats -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">💰 Economy Statistics</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Total Cash</p>
                            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.economy.total_cash) }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Total Bank</p>
                            <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(stats.economy.total_bank) }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Avg Level</p>
                            <p class="text-3xl font-bold text-purple-600">{{ stats.economy.average_level }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Total Respect</p>
                            <p class="text-3xl font-bold text-orange-600">{{ stats.economy.total_respect.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Server Health -->
            <div v-if="serverHealth" class="mb-8">
                <h2 class="text-xl font-semibold mb-4">🏥 Server Health</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Database</p>
                                <p :class="['text-lg font-semibold', getHealthColor(serverHealth.database.status)]">
                                    {{ serverHealth.database.status.toUpperCase() }}
                                </p>
                                <p v-if="serverHealth.database.latency_ms" class="text-xs text-gray-500">
                                    Latency: {{ serverHealth.database.latency_ms }}ms
                                </p>
                            </div>
                            <div :class="[
                                'w-3 h-3 rounded-full',
                                serverHealth.database.status === 'healthy' ? 'bg-green-500' : 'bg-red-500'
                            ]"></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Cache</p>
                                <p :class="['text-lg font-semibold', getHealthColor(serverHealth.cache.status)]">
                                    {{ serverHealth.cache.status.toUpperCase() }}
                                </p>
                            </div>
                            <div :class="[
                                'w-3 h-3 rounded-full',
                                serverHealth.cache.status === 'healthy' ? 'bg-green-500' : 'bg-red-500'
                            ]"></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Storage</p>
                                <p :class="['text-lg font-semibold', getHealthColor(serverHealth.storage.status)]">
                                    {{ serverHealth.storage.status.toUpperCase() }}
                                </p>
                                <p v-if="serverHealth.storage.used_percentage" class="text-xs text-gray-500">
                                    Used: {{ serverHealth.storage.used_percentage }}%
                                </p>
                            </div>
                            <div :class="[
                                'w-3 h-3 rounded-full',
                                serverHealth.storage.status === 'healthy' ? 'bg-green-500' : 
                                serverHealth.storage.status === 'warning' ? 'bg-yellow-500' : 'bg-red-500'
                            ]"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Players -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">🏆 Top Players</h2>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Respect</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(player, index) in stats.top_players" :key="player.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-2xl">{{ index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : index + 1 }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium">{{ player.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">Level {{ player.level }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-orange-600 font-semibold">
                                    {{ player.respect.toLocaleString() }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Signups -->
            <div>
                <h2 class="text-xl font-semibold mb-4">✨ Recent Signups</h2>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Player</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="player in stats.recent_signups" :key="player.id">
                                <td class="px-6 py-4 whitespace-nowrap font-medium">{{ player.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ player.user.email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">Level {{ player.level }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(player.created_at).toLocaleDateString() }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
