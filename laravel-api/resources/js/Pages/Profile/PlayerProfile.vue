<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: Object,
    stats: Object,
    recent_crimes: Array,
    is_own_profile: Boolean,
});

const formatNumber = (num) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getHealthPercent = () => {
    return (props.player.health / props.player.max_health) * 100;
};

const getEnergyPercent = () => {
    return (props.player.energy / props.player.max_energy) * 100;
};

const getXpPercent = () => {
    const nextLevelXp = Math.floor(100 * Math.pow(props.player.level, 1.5));
    return (props.player.experience / nextLevelXp) * 100;
};
</script>

<template>
    <AppLayout :title="`${player.username}'s Profile`">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ player.username }}'s Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Player Info Card -->
                    <div class="md:col-span-1">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <div class="text-center mb-6">
                                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-3xl font-bold mb-4">
                                    {{ player.username.charAt(0).toUpperCase() }}
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ player.username }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ player.location }}
                                </p>
                            </div>

                            <!-- Level and Rank -->
                            <div class="border-t border-b border-gray-200 dark:border-gray-700 py-4 mb-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Level</span>
                                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ player.level }}</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-2">
                                    <div
                                        class="bg-indigo-600 h-2.5 rounded-full"
                                        :style="{ width: `${getXpPercent()}%` }"
                                    ></div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                    {{ player.experience.toLocaleString() }} XP
                                </div>
                            </div>

                            <div class="mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    {{ player.rank_title }}
                                </span>
                            </div>

                            <!-- Member Since -->
                            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <p><strong>Joined:</strong> {{ formatDate(player.created_at) }}</p>
                                <p><strong>Last Active:</strong> {{ formatDate(player.last_active) }}</p>
                            </div>

                            <!-- Action Buttons (if not own profile) -->
                            <div v-if="!is_own_profile" class="mt-6 space-y-2">
                                <Link
                                    :href="route('combat.index')"
                                    class="block w-full text-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                >
                                    ⚔️ Attack
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Stats and Details -->
                    <div class="md:col-span-2 space-y-6">
                        <!-- Stats Grid -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Statistics</h4>
                            
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Respect</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        ⭐ {{ player.respect.toLocaleString() }}
                                    </div>
                                </div>
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Cash</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        💰 {{ formatNumber(player.cash) }}
                                    </div>
                                </div>
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Bank</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        🏦 {{ formatNumber(player.bank) }}
                                    </div>
                                </div>
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Networth</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        💎 {{ formatNumber(player.networth) }}
                                    </div>
                                </div>
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Bullets</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        🔫 {{ player.bullets.toLocaleString() }}
                                    </div>
                                </div>
                            </div>

                            <!-- Health and Energy Bars -->
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600 dark:text-gray-400">Health</span>
                                        <span class="text-gray-900 dark:text-gray-100 font-medium">
                                            {{ player.health }} / {{ player.max_health }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                        <div
                                            class="bg-red-600 h-3 rounded-full transition-all"
                                            :style="{ width: `${getHealthPercent()}%` }"
                                        ></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600 dark:text-gray-400">Energy</span>
                                        <span class="text-gray-900 dark:text-gray-100 font-medium">
                                            {{ player.energy }} / {{ player.max_energy }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                        <div
                                            class="bg-green-600 h-3 rounded-full transition-all"
                                            :style="{ width: `${getEnergyPercent()}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Crime Statistics -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Crime Statistics</h4>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ stats.total_attempts }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Crimes</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-green-600">
                                        {{ stats.successful }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Successful</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-red-600">
                                        {{ stats.failed }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Failed</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-blue-600">
                                        {{ stats.success_rate }}%
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Success Rate</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-green-600">
                                        {{ formatNumber(stats.total_earnings) }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Earned</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-purple-600">
                                        {{ stats.total_respect_earned }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Respect Earned</div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Crimes -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Recent Crimes</h4>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Crime
                                            </th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Result
                                            </th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Cash
                                            </th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                When
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr
                                            v-for="crime in recent_crimes"
                                            :key="crime.id"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                {{ crime.crime_name }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span
                                                    v-if="crime.success"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                                                >
                                                    ✓ Success
                                                </span>
                                                <span
                                                    v-else
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                                                >
                                                    ✗ Failed
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">
                                                <span v-if="crime.cash_earned > 0" class="text-green-600 font-medium">
                                                    +{{ formatNumber(crime.cash_earned) }}
                                                </span>
                                                <span v-else class="text-gray-400">—</span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">
                                                {{ crime.time_ago }}
                                            </td>
                                        </tr>
                                        <tr v-if="recent_crimes.length === 0">
                                            <td colspan="4" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                                No recent crimes
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
