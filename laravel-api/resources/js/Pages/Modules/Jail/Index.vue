<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    jailedPlayers: {
        type: Array,
        default: () => []
    },
    player: {
        type: Object,
        default: null
    },
    playerStatus: {
        type: Object,
        default: null
    },
});

const processing = ref(false);
const selectedPlayer = ref(null);
const timeRemaining = ref(props.playerStatus?.time_remaining || 0);

// Debug logging
console.log('Jail Index - Props:', {
    player: props.player,
    playerStatus: props.playerStatus,
    jailedPlayers: props.jailedPlayers
});

// Format money
const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

// Format time remaining
const formatTime = (seconds) => {
    if (seconds <= 0) return '0s';
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    if (hours > 0) {
        return `${hours}h ${minutes}m ${secs}s`;
    } else if (minutes > 0) {
        return `${minutes}m ${secs}s`;
    } else {
        return `${secs}s`;
    }
};

// Calculate bail cost
const bailCost = computed(() => {
    return timeRemaining.value * 100;
});

// Countdown timer
let interval = null;
onMounted(() => {
    if (timeRemaining.value > 0) {
        interval = setInterval(() => {
            if (timeRemaining.value > 0) {
                timeRemaining.value--;
            } else {
                clearInterval(interval);
                // Reload page when timer expires
                router.reload();
            }
        }, 1000);
    }
});

onUnmounted(() => {
    if (interval) {
        clearInterval(interval);
    }
});

// Bust out action
const bustOut = (playerId) => {
    if (processing.value) return;
    
    processing.value = true;
    selectedPlayer.value = playerId;
    
    router.post(route('jail.bustout', playerId), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            selectedPlayer.value = null;
        },
    });
};

// Bail out action
const bailOutAction = () => {
    if (processing.value) return;
    
    processing.value = true;
    
    router.post(route('jail.bailout'), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <AppLayout title="Jail">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Jail
                </h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Debug Info (remove after testing) -->
                <div v-if="!player" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <p class="font-bold">Error: Player data not loaded</p>
                    <pre>{{ $page.props }}</pre>
                </div>

                <div v-if="player">
                    <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ $page.props.flash.error }}
                </div>

                <!-- Player Jail Status -->
                <div v-if="playerStatus && playerStatus.in_jail" class="bg-red-50 border-l-4 border-red-500 p-6 mb-6 rounded-lg shadow">
                    <div class="flex items-center mb-4">
                        <svg class="w-8 h-8 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <div>
                            <h3 class="text-xl font-bold text-red-800">
                                🚔 You are in {{ playerStatus.in_super_max ? 'Super Max' : 'Jail' }}!
                            </h3>
                            <p class="text-red-700 text-lg">
                                You must wait <span class="font-bold text-red-900">{{ timeRemaining }}</span> seconds before you are released.
                            </p>
                        </div>
                    </div>

                    <!-- Bail Out Option (not available in super max) -->
                    <div v-if="!playerStatus.in_super_max" class="mt-4 p-4 bg-white rounded border border-red-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-700 font-semibold">Bail Out</p>
                                <p class="text-gray-600 text-sm">Cost: {{ formatMoney(bailCost) }} ({{ formatMoney(100) }}/second)</p>
                                <p class="text-gray-500 text-xs mt-1">Your Cash: {{ formatMoney(player.cash) }}</p>
                            </div>
                            <button
                                @click="bailOutAction"
                                :disabled="processing || player.cash < bailCost"
                                class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-semibold"
                            >
                                {{ processing ? 'Processing...' : 'Pay Bail' }}
                            </button>
                        </div>
                    </div>

                    <div v-else class="mt-4 p-4 bg-red-100 rounded border border-red-300">
                        <p class="text-red-800 font-semibold">
                            ⚠️ You are in Super Max. You cannot bail out and must serve your full sentence.
                        </p>
                    </div>
                </div>

                <!-- Not in Jail Message -->
                <div v-else class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
                    <p class="text-green-800 font-semibold">✓ You are not currently in jail.</p>
                </div>

                <!-- Jailed Players List -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Players in Jail ({{ jailedPlayers.length }})
                        </h3>

                        <div v-if="jailedPlayers.length === 0" class="text-center py-8 text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-lg">No players are currently in jail.</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="inmate in jailedPlayers"
                                :key="inmate.id"
                                class="border rounded-lg p-4 hover:border-gray-400 transition"
                                :class="{
                                    'bg-yellow-50 border-yellow-300': inmate.is_current_user,
                                    'bg-red-50 border-red-300': inmate.is_super_max,
                                }"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <h4 class="text-lg font-bold text-gray-900">
                                                {{ inmate.username }}
                                                <span v-if="inmate.is_current_user" class="text-sm text-yellow-600 font-normal">(You)</span>
                                                <span v-if="inmate.is_super_max" class="ml-2 px-2 py-1 bg-red-600 text-white text-xs rounded-full font-bold">
                                                    SUPER MAX
                                                </span>
                                            </h4>
                                        </div>
                                        <div class="mt-2 space-y-1 text-sm text-gray-600">
                                            <p><span class="font-semibold">Level:</span> {{ inmate.level }} - {{ inmate.rank }}</p>
                                            <p><span class="font-semibold">Time Remaining:</span> {{ formatTime(inmate.time_remaining) }}</p>
                                            <p v-if="!inmate.is_super_max && inmate.bust_chance > 0" class="text-green-600">
                                                <span class="font-semibold">Bust Chance:</span> {{ inmate.bust_chance.toFixed(1) }}%
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Bust Out Button -->
                                    <div v-if="!inmate.is_super_max" class="ml-4">
                                        <button
                                            @click="bustOut(inmate.id)"
                                            :disabled="processing || inmate.bust_chance === 0 || playerStatus?.in_super_max"
                                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-semibold whitespace-nowrap"
                                            :class="{ 'opacity-50': processing && selectedPlayer === inmate.id }"
                                        >
                                            {{ processing && selectedPlayer === inmate.id ? 'Busting...' : 'Bust Out' }}
                                        </button>
                                        <p v-if="inmate.bust_chance === 0" class="text-xs text-red-500 mt-1 text-center">
                                            Can't bust
                                        </p>
                                    </div>
                                    <div v-else class="ml-4">
                                        <span class="text-red-600 font-semibold text-sm">Cannot bust out</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jail Info -->
                <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                    <h4 class="font-bold text-blue-900 mb-2">How Jail Works:</h4>
                    <ul class="text-blue-800 text-sm space-y-1 list-disc list-inside">
                        <li>You go to jail when caught committing crimes</li>
                        <li>You can pay bail to get out early ({{ formatMoney(100) }} per second remaining)</li>
                        <li>Other players can attempt to bust you out</li>
                        <li>Failed bust attempts send the rescuer to jail for 90 seconds</li>
                        <li>Failed bust attempts while already in jail extend your time and send you to Super Max</li>
                        <li>Super Max prisoners cannot be busted out and cannot pay bail</li>
                        <li>Bust success chance decreases with higher level targets</li>
                    </ul>
                </div>
                </div> <!-- end v-if="player" -->
            </div>
        </div>
    </AppLayout>
</template>
