<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    achievements: Object,
    stats: Object
});

const achievementTypes = {
    'crime_count': { name: 'Crime Master', icon: '🎭', color: 'red' },
    'level_reached': { name: 'Level Progress', icon: '⭐', color: 'yellow' },
    'cash_earned': { name: 'Wealth', icon: '💰', color: 'green' },
    'kills': { name: 'Combat', icon: '⚔️', color: 'purple' },
    'properties_owned': { name: 'Real Estate', icon: '🏢', color: 'blue' },
    'gang_joined': { name: 'Gang Life', icon: '🤝', color: 'orange' }
};

const getColorClasses = (color, earned) => {
    const colors = {
        red: earned ? 'bg-red-100 border-red-500 text-red-900' : 'bg-gray-100 border-gray-300 text-gray-600',
        yellow: earned ? 'bg-yellow-100 border-yellow-500 text-yellow-900' : 'bg-gray-100 border-gray-300 text-gray-600',
        green: earned ? 'bg-green-100 border-green-500 text-green-900' : 'bg-gray-100 border-gray-300 text-gray-600',
        purple: earned ? 'bg-purple-100 border-purple-500 text-purple-900' : 'bg-gray-100 border-gray-300 text-gray-600',
        blue: earned ? 'bg-blue-100 border-blue-500 text-blue-900' : 'bg-gray-100 border-gray-300 text-gray-600',
        orange: earned ? 'bg-orange-100 border-orange-500 text-orange-900' : 'bg-gray-100 border-gray-300 text-gray-600'
    };
    return colors[color];
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('en-US').format(num);
};
</script>

<template>
    <AppLayout title="Achievements">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Achievements
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Card -->
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-xl p-6 mb-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-3xl font-bold mb-2">🏆 Your Progress</h3>
                            <p class="text-purple-100">Keep unlocking achievements to earn rewards!</p>
                        </div>
                        <div class="text-center bg-white bg-opacity-20 rounded-lg p-6">
                            <div class="text-5xl font-bold">{{ stats.earned }}/{{ stats.total }}</div>
                            <div class="text-sm mt-2">Unlocked ({{ stats.percentage }}%)</div>
                        </div>
                    </div>
                </div>

                <!-- Achievement Categories -->
                <div v-for="(categoryAchievements, type) in achievements" :key="type" class="mb-8">
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-700 to-gray-900 p-4 text-white">
                            <h3 class="text-xl font-bold flex items-center">
                                <span class="text-3xl mr-3">{{ achievementTypes[type]?.icon || '🎯' }}</span>
                                {{ achievementTypes[type]?.name || type }}
                            </h3>
                        </div>
                        
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="achievement in categoryAchievements" 
                                 :key="achievement.id"
                                 :class="[
                                     'border-l-4 p-4 rounded-lg transition duration-150',
                                     getColorClasses(achievementTypes[type]?.color || 'gray', achievement.is_earned)
                                 ]">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <span class="text-3xl mr-3">{{ achievement.icon }}</span>
                                            <div>
                                                <h4 class="font-bold text-lg">{{ achievement.name }}</h4>
                                                <p class="text-sm opacity-75">{{ achievement.description }}</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Progress Bar -->
                                        <div v-if="!achievement.is_earned" class="mt-3">
                                            <div class="flex justify-between text-xs mb-1">
                                                <span>Progress</span>
                                                <span>{{ formatNumber(achievement.progress) }} / {{ formatNumber(achievement.requirement) }}</span>
                                            </div>
                                            <div class="bg-gray-300 rounded-full h-2 overflow-hidden">
                                                <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-full transition-all duration-300"
                                                     :style="{ width: achievement.percentage + '%' }"></div>
                                            </div>
                                            <div class="text-xs mt-1 text-center font-semibold">{{ achievement.percentage }}%</div>
                                        </div>
                                        
                                        <!-- Earned Badge -->
                                        <div v-else class="mt-3 inline-flex items-center bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                            ✓ UNLOCKED
                                        </div>
                                        
                                        <!-- Rewards -->
                                        <div class="mt-3 flex space-x-3 text-sm font-semibold">
                                            <div v-if="achievement.reward_cash > 0" class="flex items-center">
                                                <span class="mr-1">💰</span>
                                                ${{ formatNumber(achievement.reward_cash) }}
                                            </div>
                                            <div v-if="achievement.reward_xp > 0" class="flex items-center">
                                                <span class="mr-1">⭐</span>
                                                {{ formatNumber(achievement.reward_xp) }} XP
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-6">
                    <Link :href="route('dashboard')" 
                          class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-bold rounded-lg transition duration-150">
                        ← Back to Dashboard
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
