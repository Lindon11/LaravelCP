<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: Object,
    crimes: Array,
    stats: Object,
    cooldown: Number,
});

const processing = ref(false);
const selectedCrime = ref(null);

const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0
    }).format(amount);
};

const canAttemptCrime = computed(() => {
    return !processing.value && 
           props.cooldown <= 0 && 
           props.player?.energy >= 10;
});

const attemptCrime = (crime) => {
    if (!canAttemptCrime.value) return;
    
    processing.value = true;
    selectedCrime.value = crime.id;
    
    router.post(route('crimes.attempt', crime.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            selectedCrime.value = null;
        }
    });
};

const getDifficultyColor = (difficulty) => {
    const colors = {
        'Very Easy': 'text-green-600',
        'Easy': 'text-lime-600',
        'Medium': 'text-yellow-600',
        'Hard': 'text-orange-600',
        'Very Hard': 'text-red-600'
    };
    return colors[difficulty] || 'text-gray-600';
};

const getSuccessRateColor = (rate) => {
    if (rate >= 70) return 'text-green-600';
    if (rate >= 50) return 'text-yellow-600';
    return 'text-red-600';
};
</script>

<template>
    <AppLayout title="Crimes">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-lg shadow-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-2">🔫 Crimes</h1>
                            <p class="text-red-100">Commit crimes to earn cash and experience</p>
                        </div>
                        <div class="text-right">
                            <div class="text-white text-sm">Your Stats</div>
                            <div class="text-2xl font-bold text-white">{{ stats.successful_crimes }}/{{ stats.total_crimes }}</div>
                            <div class="text-red-100 text-sm">Success Rate</div>
                        </div>
                    </div>
                </div>

                <!-- Player Status -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-gray-600 text-sm">Energy</div>
                        <div class="text-2xl font-bold" :class="player.energy >= 10 ? 'text-green-600' : 'text-red-600'">
                            {{ player.energy }}/100
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-gray-600 text-sm">Total Earned</div>
                        <div class="text-2xl font-bold text-green-600">{{ formatMoney(stats.total_earned) }}</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-gray-600 text-sm">Times Jailed</div>
                        <div class="text-2xl font-bold text-orange-600">{{ stats.times_jailed }}</div>
                    </div>
                </div>

                <!-- Cooldown Warning -->
                <div v-if="cooldown > 0" class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded-lg">
                    <p class="text-yellow-800">
                        ⏱️ You must wait <strong>{{ cooldown }} seconds</strong> before committing another crime
                    </p>
                </div>

                <!-- Energy Warning -->
                <div v-else-if="player.energy < 10" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                    <p class="text-red-800">
                        ⚡ Not enough energy! You need at least 10 energy to commit a crime
                    </p>
                </div>

                <!-- Available Crimes -->
                <div class="space-y-4">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Available Crimes</h2>
                    
                    <div v-for="crime in crimes" :key="crime.id" 
                         class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition"
                         :class="{ 'opacity-50': !canAttemptCrime || crime.required_level > player.level }">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-xl font-bold text-gray-900">{{ crime.name }}</h3>
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100" 
                                              :class="getDifficultyColor(crime.difficulty)">
                                            {{ crime.difficulty }}
                                        </span>
                                        <span v-if="crime.required_level > 1" 
                                              class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700">
                                            Level {{ crime.required_level }}+
                                        </span>
                                    </div>
                                    <p class="text-gray-600 mb-4">{{ crime.description }}</p>
                                    
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div>
                                            <span class="text-gray-500 text-sm">Reward:</span>
                                            <div class="font-semibold text-green-600">
                                                {{ formatMoney(crime.min_cash) }} - {{ formatMoney(crime.max_cash) }}
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 text-sm">Experience:</span>
                                            <div class="font-semibold text-blue-600">+{{ crime.experience }} XP</div>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 text-sm">Success Rate:</span>
                                            <div class="font-semibold" :class="getSuccessRateColor(crime.success_rate)">
                                                {{ crime.success_rate }}%
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 text-sm">Energy Cost:</span>
                                            <div class="font-semibold text-orange-600">-10 Energy</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="ml-6">
                                    <button 
                                        @click="attemptCrime(crime)"
                                        :disabled="!canAttemptCrime || crime.required_level > player.level || selectedCrime === crime.id"
                                        class="px-6 py-3 rounded-lg font-bold text-white transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="selectedCrime === crime.id ? 'bg-gray-400' : 'bg-red-600 hover:bg-red-700'">
                                        <span v-if="selectedCrime === crime.id">
                                            <svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                        <span v-else-if="crime.required_level > player.level">
                                            Locked
                                        </span>
                                        <span v-else>
                                            Attempt
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="crimes.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
                        <p class="text-gray-500">No crimes available at your level</p>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                    <h4 class="font-bold text-blue-900 mb-2">How Crimes Work:</h4>
                    <ul class="text-blue-800 text-sm space-y-1 list-disc list-inside">
                        <li>Each crime costs 10 energy and has a 60 second cooldown</li>
                        <li>Success rate is based on your level and stats</li>
                        <li>Failed crimes may result in jail time</li>
                        <li>Higher difficulty crimes offer better rewards</li>
                        <li>Gain experience with every successful crime</li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
