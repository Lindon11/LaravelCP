<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: {
        type: Object,
        default: null
    },
    theftTypes: {
        type: Array,
        default: () => []
    },
    canAttempt: {
        type: Boolean,
        default: true
    },
    cooldownRemaining: {
        type: Number,
        default: 0
    },
});

const processing = ref(false);
const remainingCooldown = ref(Math.floor(props.cooldownRemaining || 0));

// Countdown timer
let cooldownInterval = null;

// Watch for cooldown prop changes
watch(() => props.cooldownRemaining, (newCooldown) => {
    remainingCooldown.value = Math.floor(newCooldown || 0);
    
    if (cooldownInterval) {
        clearInterval(cooldownInterval);
        cooldownInterval = null;
    }
    
    if (remainingCooldown.value > 0) {
        cooldownInterval = setInterval(() => {
            if (remainingCooldown.value > 0) {
                remainingCooldown.value--;
            } else {
                clearInterval(cooldownInterval);
            }
        }, 1000);
    }
});

onMounted(() => {
    if (remainingCooldown.value > 0) {
        cooldownInterval = setInterval(() => {
            if (remainingCooldown.value > 0) {
                remainingCooldown.value--;
            } else {
                clearInterval(cooldownInterval);
            }
        }, 1000);
    }
});

onUnmounted(() => {
    if (cooldownInterval) clearInterval(cooldownInterval);
});

const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatTime = (seconds) => {
    const roundedSeconds = Math.floor(seconds);
    if (roundedSeconds <= 0) return '0s';
    const mins = Math.floor(roundedSeconds / 60);
    const secs = roundedSeconds % 60;
    return mins > 0 ? `${mins}m ${secs}s` : `${secs}s`;
};

const attemptTheft = (typeId) => {
    if (processing.value || !props.canAttempt) return;

    processing.value = true;
    router.post(route('theft.attempt', typeId), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};

const getDifficultyColor = (successRate) => {
    if (successRate >= 70) return 'text-green-600';
    if (successRate >= 50) return 'text-yellow-600';
    if (successRate >= 30) return 'text-orange-600';
    return 'text-red-600';
};

const getDifficultyBadge = (successRate) => {
    if (successRate >= 70) return { text: 'Easy', class: 'bg-green-100 text-green-800' };
    if (successRate >= 50) return { text: 'Medium', class: 'bg-yellow-100 text-yellow-800' };
    if (successRate >= 30) return { text: 'Hard', class: 'bg-orange-100 text-orange-800' };
    return { text: 'Very Hard', class: 'bg-red-100 text-red-800' };
};
</script>

<template>
    <AppLayout title="Car Theft (GTA)">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Car Theft (GTA)
                </h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="player">
                    <!-- Flash Messages -->
                    <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ $page.props.flash.success }}
                    </div>
                    <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ $page.props.flash.error }}
                    </div>

                    <!-- Cooldown Warning -->
                    <div v-if="remainingCooldown > 0" class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                        <p class="text-yellow-800 font-semibold">⏱️ Cooldown Active</p>
                        <p class="text-yellow-700">You must wait <span class="font-bold text-yellow-900">{{ remainingCooldown }}</span> seconds before attempting another theft.</p>
                    </div>

                    <!-- Theft Types Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div 
                            v-for="type in theftTypes" 
                            :key="type.id"
                            class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300"
                        >
                            <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xl font-bold text-white">{{ type.name }}</h3>
                                    <span :class="getDifficultyBadge(type.success_rate).class" class="px-3 py-1 rounded-full text-xs font-bold">
                                        {{ getDifficultyBadge(type.success_rate).text }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-6">
                                <p class="text-gray-600 mb-4">{{ type.description }}</p>

                                <div class="space-y-2 mb-4 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Success Rate:</span>
                                        <span :class="getDifficultyColor(type.success_rate)" class="font-bold">
                                            {{ type.success_rate }}%
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Car Value Range:</span>
                                        <span class="font-semibold text-gray-900">
                                            {{ formatMoney(type.min_car_value) }} - {{ formatMoney(type.max_car_value) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Cooldown:</span>
                                        <span class="font-semibold text-gray-900">{{ type.cooldown }}s</span>
                                    </div>
                                </div>

                                <button
                                    @click="attemptTheft(type.id)"
                                    :disabled="processing || !canAttempt"
                                    class="w-full py-3 px-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed font-bold transition transform hover:scale-105"
                                >
                                    {{ processing ? 'Attempting...' : 'Steal a Car' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Link to Garage -->
                    <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-800 font-semibold">💼 Your Garage</p>
                                <p class="text-blue-700 text-sm">View and sell your stolen cars</p>
                            </div>
                            <a :href="route('theft.garage')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">
                                View Garage
                            </a>
                        </div>
                    </div>

                    <!-- How It Works -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">🚗 How Car Theft Works</h3>
                        <ul class="text-gray-700 text-sm space-y-2 list-disc list-inside">
                            <li>Choose a theft difficulty based on your risk tolerance</li>
                            <li>Higher difficulty = Lower success rate, but more valuable cars</li>
                            <li>If you fail, you might get caught and sent to jail (1 in 3 chance)</li>
                            <li>Successfully stolen cars go to your garage with random damage</li>
                            <li>Sell cars from your garage to earn cash</li>
                            <li>Damage reduces the car's sale value</li>
                            <li>3-minute cooldown between theft attempts</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
