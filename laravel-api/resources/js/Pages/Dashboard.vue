<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: Object,
    modules: Array,
    dailyReward: Object,
    activeTimers: Array
});

// Module access helper
const canAccessModule = (moduleName) => {
    if (!props.modules) return false;
    return props.modules.some(module => module.name === moduleName);
};

const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0
    }).format(amount);
};

// Energy regeneration logic
const energyRefillRate = 5; // matches server setting
const refillIntervalSeconds = 60; // 1 minute
const nextRefillIn = ref(refillIntervalSeconds);

// Jail timer logic
const jailTimeRemaining = ref(0);

const isInJail = computed(() => {
    return props.player.jail_until && new Date(props.player.jail_until) > new Date();
});

const isInSuperMax = computed(() => {
    return props.player.super_max_until && new Date(props.player.super_max_until) > new Date();
});

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

// Initialize jail timer
if (isInJail.value) {
    const jailUntil = new Date(props.player.jail_until);
    jailTimeRemaining.value = Math.max(0, Math.floor((jailUntil - new Date()) / 1000));
}

// Countdown timers
let interval = null;
let energyInterval = null;

onMounted(() => {
    // Jail timer
    if (isInJail.value) {
        interval = setInterval(() => {
            if (jailTimeRemaining.value > 0) {
                jailTimeRemaining.value--;
            } else {
                clearInterval(interval);
            }
        }, 1000);
    }
    
    // Energy regeneration countdown
    if (props.player.energy < props.player.max_energy) {
        energyInterval = setInterval(() => {
            if (nextRefillIn.value > 0) {
                nextRefillIn.value--;
            } else {
                nextRefillIn.value = refillIntervalSeconds;
            }
        }, 1000);
    }
});

onUnmounted(() => {
    if (interval) {
        clearInterval(interval);
    }
    if (energyInterval) {
        clearInterval(energyInterval);
    }
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Daily Reward Alert -->
                <div v-if="dailyReward && dailyReward.can_claim" 
                     class="bg-gradient-to-r from-yellow-400 to-orange-500 p-6 rounded-lg shadow-xl mb-6 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <svg class="w-8 h-8 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z" />
                                </svg>
                                <h3 class="text-2xl font-bold">🎁 Daily Reward Available!</h3>
                            </div>
                            <p class="text-yellow-100 text-lg mb-2">
                                Day {{ dailyReward.streak }} Streak - Claim your rewards now!
                            </p>
                            <div class="flex space-x-6 text-sm">
                                <div class="bg-white bg-opacity-20 px-3 py-1 rounded">
                                    💰 ${{ dailyReward.next_reward.cash.toLocaleString() }}
                                </div>
                                <div class="bg-white bg-opacity-20 px-3 py-1 rounded">
                                    ⭐ {{ dailyReward.next_reward.xp }} XP
                                </div>
                                <div v-if="dailyReward.next_reward.bullets > 0" class="bg-white bg-opacity-20 px-3 py-1 rounded">
                                    🔫 {{ dailyReward.next_reward.bullets }} Bullets
                                </div>
                            </div>
                        </div>
                        <form @submit.prevent="$inertia.post(route('daily-rewards.claim'))" class="ml-6">
                            <button type="submit" 
                                    class="bg-white text-orange-600 px-8 py-4 rounded-lg font-bold text-lg hover:bg-yellow-50 transition duration-150 shadow-lg transform hover:scale-105">
                                Claim Now!
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Daily Reward Info (Already Claimed) -->
                <div v-else-if="dailyReward && !dailyReward.can_claim" 
                     class="bg-gray-100 border-l-4 border-gray-500 p-4 mb-6 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-gray-800 font-bold">Daily Reward Claimed!</p>
                            <p class="text-gray-600 text-sm">
                                Current streak: {{ dailyReward.streak }} days - Come back tomorrow for your next reward
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Active Cooldowns -->
                <div v-if="activeTimers && activeTimers.length > 0" 
                     class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-6 rounded-lg shadow">
                    <div class="flex items-center mb-2">
                        <svg class="w-6 h-6 text-orange-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                        <h3 class="font-bold text-orange-800">⏱️ Active Cooldowns</h3>
                    </div>
                    <div class="ml-9 space-y-1">
                        <div v-for="timer in activeTimers" :key="timer.name" 
                             class="text-sm text-orange-700 flex justify-between items-center py-1">
                            <span class="font-semibold capitalize">{{ timer.name.replace('_', ' ') }}:</span>
                            <span class="font-mono bg-orange-100 px-2 py-0.5 rounded">
                                {{ timer.remaining_time }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Jail Alert -->
                <div v-if="isInJail" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <div class="flex-1">
                            <p class="text-red-800 font-bold">
                                You are in {{ isInSuperMax ? 'Super Max' : 'Jail' }}!
                            </p>
                            <p class="text-red-700 font-mono">
                                Time Remaining: {{ formatTime(jailTimeRemaining) }}
                            </p>
                        </div>
                        <Link :href="route('jail.index')" 
                              class="ml-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-semibold">
                            Go to Jail
                        </Link>
                    </div>
                </div>

                <!-- Player Stats Card -->
                <div v-if="player" class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">{{ player.username }}</h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 mb-1">Cash</div>
                                <div class="text-2xl font-bold text-green-600">{{ formatMoney(player.cash) }}</div>
                            </div>
                            
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 mb-1">Bank</div>
                                <div class="text-2xl font-bold text-blue-600">{{ formatMoney(player.bank) }}</div>
                            </div>
                            
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 mb-1">Respect</div>
                                <div class="text-2xl font-bold text-purple-600">{{ player.respect }}</div>
                            </div>
                            
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 mb-1">Level</div>
                                <div class="text-2xl font-bold text-yellow-600">{{ player.level }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex flex-col space-y-1">
                                <span class="text-sm text-gray-600">Rank</span>
                                <span class="font-semibold text-gray-900">{{ player.rank }}</span>
                            </div>
                            
                            <div class="flex flex-col space-y-1">
                                <span class="text-sm text-gray-600">Health</span>
                                <div class="flex items-center space-x-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-red-600 h-2.5 rounded-full" 
                                             :style="{ width: `${(player.health / player.max_health) * 100}%` }"></div>
                                    </div>
                                    <span class="font-semibold text-gray-900 text-sm">{{ player.health }}/{{ player.max_health }}</span>
                                </div>
                            </div>
                            
                            <div class="flex flex-col space-y-1">
                                <span class="text-sm text-gray-600">Energy</span>
                                <div class="flex items-center space-x-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-green-600 h-2.5 rounded-full" 
                                             :style="{ width: `${(player.energy / player.max_energy) * 100}%` }"></div>
                                    </div>
                                    <span class="font-semibold text-gray-900 text-sm">{{ player.energy }}/{{ player.max_energy }}</span>
                                </div>
                                <div v-if="player.energy < player.max_energy" class="text-xs text-gray-500">
                                    +{{ energyRefillRate }} in {{ nextRefillIn }}s
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <Link :href="route('crimes.index')" 
                          class="bg-red-600 hover:bg-red-700 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Commit Crimes</h3>
                        <p class="text-sm">Earn cash and respect</p>
                    </Link>
                    
                    <Link :href="route('achievements.index')" 
                          class="bg-purple-600 hover:bg-purple-700 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        <h3 class="text-xl font-bold mb-2">🏆 Achievements</h3>
                        <p class="text-sm">View your progress</p>
                    </Link>

                    <Link :href="route('theft.index')" 
                          class="bg-indigo-600 hover:bg-indigo-700 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Steal Cars</h3>
                        <p class="text-sm">Grand Theft Auto</p>
                    </Link>

                    <Link :href="route('bank.index')" 
                          class="bg-green-600 hover:bg-green-700 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Bank</h3>
                        <p class="text-sm">Manage your money</p>
                    </Link>
                </div>
                
                <!-- More Actions -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <Link :href="route('jail.index')" 
                          class="bg-orange-600 hover:bg-orange-700 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center"
                          :class="{ 'ring-4 ring-red-400 animate-pulse': isInJail }">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Jail</h3>
                        <p class="text-sm">{{ isInJail ? 'You are in jail!' : 'Bust out players' }}</p>
                    </Link>
                    
                    <Link :href="route('hospital.index')" 
                          class="bg-red-500 hover:bg-red-600 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Hospital</h3>
                        <p class="text-sm">Heal your wounds ($100/HP)</p>
                    </Link>

                    <Link :href="route('bullets.index')" 
                          class="bg-yellow-600 hover:bg-yellow-700 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Buy Bullets</h3>
                        <p class="text-sm">Ammunition shop ($50/bullet)</p>
                    </Link>

                    <Link :href="route('theft.garage')" 
                          class="bg-blue-600 hover:bg-blue-700 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Your Garage</h3>
                        <p class="text-sm">View and sell stolen cars</p>
                    </Link>

                    <Link :href="route('gangs.index')" 
                          class="bg-purple-700 hover:bg-purple-800 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Gangs</h3>
                        <p class="text-sm">Create or join a gang</p>
                    </Link>
                </div>

                <!-- New Features -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Link :href="route('bounties.index')" 
                          class="bg-red-700 hover:bg-red-800 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Bounties</h3>
                        <p class="text-sm">Place hits on players ($10k+)</p>
                    </Link>

                    <Link :href="route('properties.index')" 
                          class="bg-green-700 hover:bg-green-800 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Properties</h3>
                        <p class="text-sm">Buy real estate for income</p>
                    </Link>

                    <Link :href="route('detective.index')" 
                          class="bg-gray-700 hover:bg-gray-800 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Detective</h3>
                        <p class="text-sm">Hire detective to find players</p>
                    </Link>

                    <Link :href="route('travel.index')" 
                          class="bg-blue-700 hover:bg-blue-800 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Travel</h3>
                        <p class="text-sm">Visit different cities</p>
                    </Link>
                </div>

                <!-- Additional Features -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                    <Link :href="route('gym.index')" 
                          class="bg-orange-600 hover:bg-orange-700 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Gym</h3>
                        <p class="text-sm">Train your stats</p>
                    </Link>

                    <Link :href="route('organized-crimes.index')" 
                          class="bg-purple-700 hover:bg-purple-800 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Organized Crime</h3>
                        <p class="text-sm">Gang heists</p>
                    </Link>

                    <Link :href="route('forum.index')" 
                          class="bg-indigo-600 hover:bg-indigo-700 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Forum</h3>
                        <p class="text-sm">Community board</p>
                    </Link>

                    <Link :href="route('leaderboards.index')" 
                          class="bg-yellow-600 hover:bg-yellow-700 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Leaderboards</h3>
                        <p class="text-sm">Top players ranking</p>
                    </Link>
                </div>

                <!-- Row 5: Combat & More -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                    <Link :href="route('combat.index')" 
                          class="bg-red-800 hover:bg-red-900 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Attack</h3>
                        <p class="text-sm">Fight other players</p>
                    </Link>

                    <Link :href="route('missions.index')" 
                          class="bg-teal-600 hover:bg-teal-700 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Missions</h3>
                        <p class="text-sm">Complete quests</p>
                    </Link>

                    <Link :href="route('racing.index')" 
                          class="bg-pink-600 hover:bg-pink-700 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Racing</h3>
                        <p class="text-sm">Race stolen cars</p>
                    </Link>

                    <Link :href="route('drugs.index')" 
                          class="bg-green-800 hover:bg-green-900 text-white p-6 rounded-lg shadow-lg transition duration-150 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Drugs</h3>
                        <p class="text-sm">Drug dealing</p>
                    </Link>
                </div>

                <!-- Row 6: Inventory System -->
                <div v-if="canAccessModule('inventory') || canAccessModule('shop')" 
                     class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <Link v-if="canAccessModule('inventory')" :href="route('inventory.index')" 
                          class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white p-6 rounded-lg shadow-lg transition transform hover:scale-105">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold mb-2">🎒 Inventory</h3>
                                <p class="text-indigo-100">Manage your items and equipment</p>
                            </div>
                            <span class="text-4xl">🎒</span>
                        </div>
                    </Link>

                    <Link v-if="canAccessModule('shop')" :href="route('shop.index')" 
                          class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white p-6 rounded-lg shadow-lg transition transform hover:scale-105">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold mb-2">🛒 Shop</h3>
                                <p class="text-green-100">Buy weapons, armor, and items</p>
                            </div>
                            <span class="text-4xl">🛒</span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
