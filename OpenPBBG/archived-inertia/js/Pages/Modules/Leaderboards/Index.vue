<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    leaderboards: Object,
});

const activeTab = ref('level');

const tabs = [
    { key: 'level', label: 'Level', icon: '🏆' },
    { key: 'respect', label: 'Respect', icon: '⭐' },
    { key: 'cash', label: 'Cash', icon: '💰' },
    { key: 'networth', label: 'Networth', icon: '💎' },
];

const formatNumber = (num) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
};

const getRankBadge = (rank) => {
    if (rank === 1) return '🥇';
    if (rank === 2) return '🥈';
    if (rank === 3) return '🥉';
    return `#${rank}`;
};

const getRankClass = (rank) => {
    if (rank === 1) return 'text-yellow-500 font-bold';
    if (rank === 2) return 'text-gray-400 font-bold';
    if (rank === 3) return 'text-amber-700 font-bold';
    return 'text-gray-600';
};
</script>

<template>
    <AppLayout title="Leaderboards">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Leaderboards
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Tabs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="flex border-b border-gray-200 dark:border-gray-700">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            :class="[
                                'flex-1 px-6 py-4 text-center font-medium transition-colors',
                                activeTab === tab.key
                                    ? 'bg-indigo-600 text-white'
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >
                            <span class="text-xl mr-2">{{ tab.icon }}</span>
                            {{ tab.label }}
                        </button>
                    </div>
                </div>

                <!-- Leaderboard Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Rank
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Player
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Level
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Rank Title
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            {{ tabs.find(t => t.key === activeTab)?.label }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="player in leaderboards[activeTab]"
                                        :key="player.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['text-lg font-bold', getRankClass(player.rank)]">
                                                {{ getRankBadge(player.rank) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link
                                                :href="route('player.profile', player.id)"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium"
                                            >
                                                {{ player.username }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ player.level }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                {{ player.rank_title }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <span v-if="activeTab === 'level'">
                                                {{ player.level }}
                                            </span>
                                            <span v-else-if="activeTab === 'respect'">
                                                {{ player.respect.toLocaleString() }}
                                            </span>
                                            <span v-else-if="activeTab === 'cash'">
                                                {{ formatNumber(player.cash) }}
                                            </span>
                                            <span v-else-if="activeTab === 'networth'">
                                                {{ formatNumber(player.networth) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!leaderboards[activeTab] || leaderboards[activeTab].length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No players found
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
