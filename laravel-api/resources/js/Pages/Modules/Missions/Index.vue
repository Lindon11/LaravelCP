<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    missions: Array,
    stats: Object,
    player: Object,
});

const selectedType = ref('all');

const filteredMissions = computed(() => {
    if (selectedType.value === 'all') {
        return props.missions;
    }
    return props.missions.filter(m => m.type === selectedType.value);
});

const missionsByType = computed(() => {
    return {
        one_time: props.missions.filter(m => m.type === 'one_time'),
        daily: props.missions.filter(m => m.type === 'daily'),
        repeatable: props.missions.filter(m => m.type === 'repeatable'),
        story: props.missions.filter(m => m.type === 'story'),
    };
});

const startForm = useForm({
    mission_id: null,
});

const startMission = (mission) => {
    if (!confirm(`Start mission: ${mission.name}?`)) return;

    startForm.mission_id = mission.id;
    startForm.post(route('missions.start'), {
        preserveScroll: true,
    });
};

const getMissionTypeColor = (type) => {
    const colors = {
        one_time: 'bg-blue-100 text-blue-800',
        daily: 'bg-green-100 text-green-800',
        repeatable: 'bg-purple-100 text-purple-800',
        story: 'bg-yellow-100 text-yellow-800',
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
};

const getMissionTypeLabel = (type) => {
    const labels = {
        one_time: 'One Time',
        daily: 'Daily',
        repeatable: 'Repeatable',
        story: 'Story',
    };
    return labels[type] || type;
};

const getProgressPercentage = (progress, required) => {
    return Math.min(100, (progress / required) * 100);
};

const getStatusColor = (status) => {
    if (status === 'completed') return 'text-green-600';
    if (status === 'in_progress') return 'text-yellow-600';
    return 'text-gray-600';
};

const getStatusLabel = (status) => {
    const labels = {
        available: 'Available',
        in_progress: 'In Progress',
        completed: 'Completed',
    };
    return labels[status] || status;
};
</script>

<template>
    <AppLayout>
        <Head title="Missions" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">📋 Missions</h1>
                    <a :href="route('dashboard')" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                        ← Dashboard
                    </a>
                </div>

                <!-- Stats Overview -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4">Your Progress</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Total Missions</p>
                            <p class="text-3xl font-bold text-purple-600">{{ stats.total_missions }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Completed</p>
                            <p class="text-3xl font-bold text-green-600">{{ stats.completed }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">In Progress</p>
                            <p class="text-3xl font-bold text-yellow-600">{{ stats.in_progress }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Completion Rate</p>
                            <p class="text-3xl font-bold text-blue-600">{{ stats.completion_rate }}%</p>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="flex border-b">
                        <button @click="selectedType = 'all'"
                                :class="selectedType === 'all' ? 'border-b-2 border-purple-500 text-purple-600' : 'text-gray-600 hover:text-gray-900'"
                                class="px-6 py-3 font-semibold">
                            All ({{ missions.length }})
                        </button>
                        <button @click="selectedType = 'one_time'"
                                :class="selectedType === 'one_time' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-900'"
                                class="px-6 py-3 font-semibold">
                            One Time ({{ missionsByType.one_time.length }})
                        </button>
                        <button @click="selectedType = 'daily'"
                                :class="selectedType === 'daily' ? 'border-b-2 border-green-500 text-green-600' : 'text-gray-600 hover:text-gray-900'"
                                class="px-6 py-3 font-semibold">
                            Daily ({{ missionsByType.daily.length }})
                        </button>
                        <button @click="selectedType = 'repeatable'"
                                :class="selectedType === 'repeatable' ? 'border-b-2 border-purple-500 text-purple-600' : 'text-gray-600 hover:text-gray-900'"
                                class="px-6 py-3 font-semibold">
                            Repeatable ({{ missionsByType.repeatable.length }})
                        </button>
                    </div>
                </div>

                <!-- Missions List -->
                <div v-if="filteredMissions.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-600">No missions available</p>
                    <p class="text-sm text-gray-500 mt-2">Level up or complete other missions to unlock more</p>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div v-for="mission in filteredMissions" :key="mission.id"
                         class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                        
                        <!-- Mission Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ mission.name }}</h3>
                                <div class="flex items-center gap-2 mb-2">
                                    <span :class="getMissionTypeColor(mission.type)" 
                                          class="px-2 py-1 text-xs font-semibold rounded">
                                        {{ getMissionTypeLabel(mission.type) }}
                                    </span>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">
                                        Level {{ mission.required_level }}+
                                    </span>
                                    <span v-if="mission.location" class="text-xs text-gray-600">
                                        📍 {{ mission.location }}
                                    </span>
                                </div>
                            </div>
                            <span :class="getStatusColor(mission.status)" class="text-sm font-semibold">
                                {{ getStatusLabel(mission.status) }}
                            </span>
                        </div>

                        <!-- Description -->
                        <p class="text-gray-700 mb-4">{{ mission.description }}</p>

                        <!-- Progress Bar (for in progress missions) -->
                        <div v-if="mission.status === 'in_progress'" class="mb-4">
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-gray-600">Progress</span>
                                <span class="font-semibold">{{ mission.progress }} / {{ mission.objective_count }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-purple-600 h-3 rounded-full transition-all"
                                     :style="`width: ${getProgressPercentage(mission.progress, mission.objective_count)}%`">
                                </div>
                            </div>
                        </div>

                        <!-- Objective -->
                        <div class="mb-4 p-3 bg-gray-50 rounded">
                            <p class="text-sm font-semibold text-gray-700">Objective:</p>
                            <p class="text-sm text-gray-600">
                                {{ mission.objective_type.replace('_', ' ').toUpperCase() }} 
                                × {{ mission.objective_count }}
                            </p>
                        </div>

                        <!-- Rewards -->
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Rewards:</p>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div v-if="mission.rewards.cash > 0" class="flex items-center gap-1">
                                    <span class="text-green-600">💵</span>
                                    <span>${{ mission.rewards.cash.toLocaleString() }}</span>
                                </div>
                                <div v-if="mission.rewards.respect > 0" class="flex items-center gap-1">
                                    <span class="text-purple-600">⭐</span>
                                    <span>{{ mission.rewards.respect }} Respect</span>
                                </div>
                                <div v-if="mission.rewards.experience > 0" class="flex items-center gap-1">
                                    <span class="text-blue-600">📊</span>
                                    <span>{{ mission.rewards.experience }} XP</span>
                                </div>
                                <div v-if="mission.rewards.item" class="flex items-center gap-1">
                                    <span class="text-yellow-600">🎁</span>
                                    <span>{{ mission.rewards.item }} ({{ mission.rewards.item_quantity }})</span>
                                </div>
                            </div>
                        </div>

                        <!-- Cooldown Info -->
                        <div v-if="mission.cooldown_hours > 0 && mission.available_again_at" class="mb-4 text-xs text-gray-600">
                            Next available: {{ new Date(mission.available_again_at).toLocaleString() }}
                        </div>

                        <!-- Action Button -->
                        <button v-if="mission.status === 'available' && mission.can_start"
                                @click="startMission(mission)"
                                :disabled="startForm.processing"
                                class="w-full px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Start Mission
                        </button>
                        <div v-else-if="mission.status === 'in_progress'" 
                             class="w-full px-4 py-2 bg-yellow-100 text-yellow-800 rounded text-center font-semibold">
                            In Progress - Keep Going!
                        </div>
                        <div v-else-if="mission.status === 'completed'" 
                             class="w-full px-4 py-2 bg-green-100 text-green-800 rounded text-center font-semibold">
                            ✅ Completed
                        </div>
                        <div v-else-if="!mission.can_start" 
                             class="w-full px-4 py-2 bg-gray-100 text-gray-600 rounded text-center">
                            On Cooldown
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <h3 class="text-blue-800 font-semibold mb-2">💡 Mission Tips</h3>
                    <ul class="text-blue-700 text-sm space-y-1">
                        <li>• One-time missions can only be completed once</li>
                        <li>• Daily missions reset at midnight and can be done again</li>
                        <li>• Repeatable missions have cooldown periods after completion</li>
                        <li>• Your progress is tracked automatically as you play</li>
                        <li>• Some missions require specific locations or levels</li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
