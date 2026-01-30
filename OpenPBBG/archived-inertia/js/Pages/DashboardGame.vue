<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GameLayout from '@/Layouts/GameLayout.vue';
import axios from 'axios';

// Import module components
import CrimesIndex from '@/Pages/Modules/Crimes/Index.vue';
import GymIndex from '@/Pages/Modules/Gym/Index.vue';
import HospitalIndex from '@/Pages/Modules/Hospital/Index.vue';
import JailIndex from '@/Pages/Modules/Jail/Index.vue';
import TheftIndex from '@/Pages/Modules/Theft/Index.vue';
import TheftGarage from '@/Pages/Modules/Theft/Garage.vue';
import TravelIndex from '@/Pages/Modules/Travel/Index.vue';

const props = defineProps({
    player: Object,
    modules: Array,
    dailyReward: Object,
    activeTimers: Array
});

// Active tab and module
const activeTab = ref('actions');
const activeModule = ref(null);
const moduleData = ref({});

// Create reactive timer objects with remaining seconds
const timers = ref((props.activeTimers || []).map(timer => ({
    ...timer,
    seconds: timer.remaining_seconds || 0
})));

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
const energyRefillRate = 5;
const refillIntervalSeconds = 60;
const nextRefillIn = ref(refillIntervalSeconds);

// Jail timer logic
const jailTimeRemaining = ref(0);

const isInJail = computed(() => {
    return props.player?.jail_until && new Date(props.player.jail_until) > new Date();
});

const isInSuperMax = computed(() => {
    return props.player?.super_max_until && new Date(props.player.super_max_until) > new Date();
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
let timerInterval = null;

onMounted(() => {
    if (isInJail.value) {
        interval = setInterval(() => {
            if (jailTimeRemaining.value > 0) {
                jailTimeRemaining.value--;
            } else {
                clearInterval(interval);
            }
        }, 1000);
    }
    
    if (props.player?.energy < props.player?.max_energy) {
        energyInterval = setInterval(() => {
            if (nextRefillIn.value > 0) {
                nextRefillIn.value--;
            } else {
                nextRefillIn.value = refillIntervalSeconds;
            }
        }, 1000);
    }
    
    // Countdown all active timers
    if (timers.value.length > 0) {
        timerInterval = setInterval(() => {
            timers.value.forEach(timer => {
                if (timer.seconds > 0) {
                    timer.seconds--;
                }
            });
            // Remove expired timers
            timers.value = timers.value.filter(t => t.seconds > 0);
        }, 1000);
    }
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
    if (energyInterval) clearInterval(energyInterval);
    if (timerInterval) clearInterval(timerInterval);
});

// Game features grid
const gameFeatures = [
    // Row 1 - Core Actions
    { name: 'Crimes', icon: '💰', route: 'crimes.index', module: 'crimes', color: 'from-red-600 to-red-800', description: 'Commit crimes for cash', stars: 10, unlocked: true },
    { name: 'Gym', icon: '💪', route: 'gym.index', module: 'gym', color: 'from-orange-500 to-orange-700', description: 'Train your stats', stars: 8, unlocked: true },
    { name: 'Hospital', icon: '🏥', route: 'hospital.index', module: 'hospital', color: 'from-pink-500 to-pink-700', description: 'Heal your wounds', stars: 5, unlocked: true },
    { name: 'Jail', icon: '⛓️', route: 'jail.index', module: 'jail', color: 'from-gray-600 to-gray-800', description: 'Bust out players', stars: 6, unlocked: true, alert: isInJail.value },
    
    // Row 2 - Money
    { name: 'Bank', icon: '🏦', route: 'bank.index', module: null, color: 'from-green-600 to-green-800', description: 'Manage finances', stars: 7, unlocked: true },
    { name: 'Bullets', icon: '🔫', route: 'bullets.index', module: null, color: 'from-yellow-600 to-yellow-800', description: 'Buy ammunition', stars: 4, unlocked: true },
    { name: 'Properties', icon: '🏠', route: 'properties.index', module: null, color: 'from-emerald-600 to-emerald-800', description: 'Real estate income', stars: 9, unlocked: true },
    { name: 'Shop', icon: '🛒', route: 'shop.index', module: null, color: 'from-indigo-500 to-indigo-700', description: 'Buy equipment', stars: 8, unlocked: true },
    
    // Row 3 - Combat
    { name: 'Attack', icon: '⚔️', route: 'combat.index', module: null, color: 'from-red-700 to-red-900', description: 'Fight players', stars: 10, unlocked: true },
    { name: 'Bounties', icon: '🎯', route: 'bounties.index', module: null, color: 'from-rose-600 to-rose-800', description: 'Place/collect hits', stars: 7, unlocked: true },
    { name: 'Detective', icon: '🔍', route: 'detective.index', module: null, color: 'from-slate-600 to-slate-800', description: 'Find players', stars: 5, unlocked: true },
    { name: 'Gangs', icon: '👥', route: 'gangs.index', module: null, color: 'from-purple-600 to-purple-800', description: 'Join a gang', stars: 9, unlocked: true },
    
    // Row 4 - Activities
    { name: 'Steal Cars', icon: '🚗', route: 'theft.index', module: 'theft', color: 'from-blue-600 to-blue-800', description: 'Grand theft auto', stars: 8, unlocked: true },
    { name: 'Garage', icon: '🏎️', route: 'theft.garage', module: 'garage', color: 'from-cyan-600 to-cyan-800', description: 'Your vehicles', stars: 6, unlocked: true },
    { name: 'Racing', icon: '🏁', route: 'racing.index', module: null, color: 'from-pink-600 to-pink-800', description: 'Race for cash', stars: 7, unlocked: true },
    { name: 'Drugs', icon: '💊', route: 'drugs.index', module: null, color: 'from-lime-600 to-lime-800', description: 'Drug dealing', stars: 8, unlocked: true },
    
    // Row 5 - Progress
    { name: 'Missions', icon: '📋', route: 'missions.index', module: null, color: 'from-teal-600 to-teal-800', description: 'Complete quests', stars: 9, unlocked: true },
    { name: 'Achievements', icon: '🏆', route: 'achievements.index', module: null, color: 'from-amber-500 to-amber-700', description: 'Your progress', stars: 10, unlocked: true },
    { name: 'Travel', icon: '✈️', route: 'travel.index', module: 'travel', color: 'from-sky-600 to-sky-800', description: 'Visit cities', stars: 6, unlocked: true },
    { name: 'Organized Crime', icon: '🎭', route: 'organized-crimes.index', module: null, color: 'from-violet-600 to-violet-800', description: 'Gang heists', stars: 10, unlocked: props.player?.level >= 5, requiredLevel: 5 },
    
    // Row 6 - Social
    { name: 'Inventory', icon: '🎒', route: 'inventory.index', module: null, color: 'from-fuchsia-600 to-fuchsia-800', description: 'Your items', stars: 7, unlocked: true },
    { name: 'Leaderboards', icon: '📊', route: 'leaderboards.index', module: null, color: 'from-yellow-500 to-yellow-700', description: 'Top players', stars: 5, unlocked: true },
    { name: 'Forum', icon: '💬', route: 'forum.index', module: null, color: 'from-blue-500 to-blue-700', description: 'Community', stars: 4, unlocked: true },
    { name: 'Chat', icon: '🗨️', route: 'chat', module: null, color: 'from-cyan-500 to-cyan-700', description: 'Global chat', stars: 3, unlocked: true },
];

const claimDailyReward = () => {
    router.post(route('daily-rewards.claim'));
};

// Module navigation
const openModule = async (moduleName) => {
    activeModule.value = moduleName;
    activeTab.value = 'module';
    
    // Fetch module-specific data via AJAX
    const routeMap = {
        'crimes': 'crimes.index',
        'gym': 'gym.index',
        'hospital': 'hospital.index',
        'jail': 'jail.index',
        'theft': 'theft.index',
        'garage': 'theft.garage',
    };
    
    const routeName = routeMap[moduleName];
    if (routeName) {
        try {
            const response = await axios.get(route(routeName), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-Inertia': 'true',
                    'X-Inertia-Partial-Component': 'DashboardGame',
                    'X-Inertia-Partial-Data': 'crimes,stats,cooldown,hospitals,jailTime,canBustOut,cars,playerCars'
                }
            });
            
            if (response.data && response.data.props) {
                moduleData.value = response.data.props;
            }
        } catch (error) {
            console.error('Failed to load module data:', error);
        }
    }
};

const closeModule = () => {
    activeModule.value = null;
    activeTab.value = 'actions';
    moduleData.value = {};
};

// Component mapping
const moduleComponents = {
    'crimes': CrimesIndex,
    'gym': GymIndex,
    'hospital': HospitalIndex,
    'jail': JailIndex,
    'theft': TheftIndex,
    'garage': TheftGarage,
    'travel': TravelIndex,
};
</script>

<template>
    <GameLayout title="Dashboard">
        <div class="max-w-7xl mx-auto">
            <!-- Daily Reward Banner -->
            <div v-if="dailyReward && dailyReward.can_claim" 
                 class="bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border border-yellow-500/50 rounded-lg p-4 mb-6 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="text-4xl animate-bounce">🎁</div>
                        <div>
                            <h3 class="text-xl font-bold text-yellow-400">Daily Reward Available!</h3>
                            <p class="text-gray-300">Day {{ dailyReward.streak }} Streak • 
                                <span class="text-green-400">${{ dailyReward.next_reward.cash.toLocaleString() }}</span> + 
                                <span class="text-yellow-400">{{ dailyReward.next_reward.xp }} XP</span>
                            </p>
                        </div>
                    </div>
                    <button 
                        @click="claimDailyReward"
                        class="bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-400 hover:to-orange-400 text-black font-bold px-6 py-3 rounded-lg transition transform hover:scale-105"
                    >
                        Claim Now!
                    </button>
                </div>
            </div>

            <!-- Jail Alert -->
            <div v-if="isInJail" 
                 class="bg-red-900/30 border border-red-500/50 rounded-lg p-4 mb-6 animate-pulse">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="text-4xl">⛓️</div>
                        <div>
                            <h3 class="text-xl font-bold text-red-400">🚔 You are in {{ isInSuperMax ? 'Super Max' : 'Jail' }}!</h3>
                            <p class="text-gray-300">You must wait <span class="font-bold text-red-200">{{ jailTimeRemaining }}</span> seconds before you are released.</p>
                        </div>
                    </div>
                    <Link 
                        :href="route('jail.index')"
                        class="bg-red-600 hover:bg-red-500 text-white font-bold px-6 py-3 rounded-lg transition"
                    >
                        Go to Jail
                    </Link>
                </div>
            </div>

            <!-- Active Cooldowns -->
            <div v-if="timers && timers.length > 0" 
                 class="bg-orange-900/20 border border-orange-500/30 rounded-lg p-4 mb-6">
                <div class="flex items-center space-x-3 mb-2">
                    <span class="text-2xl">⏱️</span>
                    <h3 class="font-bold text-orange-400">Active Cooldowns</h3>
                </div>
                <div class="flex flex-wrap gap-3">
                    <div v-for="timer in timers" :key="timer.name" 
                         class="bg-[#21262d] px-3 py-1.5 rounded-lg flex items-center space-x-2">
                        <span class="text-orange-400 capitalize">{{ timer.name.replace('_', ' ') }}:</span>
                        <span class="font-mono text-white">{{ formatTime(timer.seconds) }}</span>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex space-x-1 mb-6 bg-[#0d1117] p-1 rounded-lg w-fit">
                <button 
                    @click="activeTab = 'actions'; activeModule = null;"
                    :class="[
                        'px-6 py-2 rounded-lg font-semibold transition',
                        activeTab === 'actions' 
                            ? 'bg-cyan-600 text-white' 
                            : 'text-gray-400 hover:text-white hover:bg-[#21262d]'
                    ]"
                >
                    ACTIONS
                </button>
                <button 
                    @click="activeTab = 'stats'; activeModule = null;"
                    :class="[
                        'px-6 py-2 rounded-lg font-semibold transition',
                        activeTab === 'stats' 
                            ? 'bg-cyan-600 text-white' 
                            : 'text-gray-400 hover:text-white hover:bg-[#21262d]'
                    ]"
                >
                    STATS
                </button>
            </div>

            <!-- Actions Grid (ZedCity Style) -->
            <div v-if="activeTab === 'actions'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <component 
                    :is="feature.module ? 'div' : Link"
                    v-for="feature in gameFeatures" 
                    :key="feature.name"
                    :href="!feature.module && feature.unlocked ? route(feature.route) : undefined"
                    @click="feature.module && feature.unlocked ? openModule(feature.module) : null"
                    :class="[
                        'relative group bg-[#1c2128] border border-[#30363d] rounded-lg p-4 transition-all duration-200',
                        feature.unlocked 
                            ? 'hover:border-cyan-500/50 hover:bg-[#21262d] cursor-pointer' 
                            : 'opacity-50 cursor-not-allowed',
                        feature.alert ? 'ring-2 ring-red-500 animate-pulse' : ''
                    ]"
                >
                    <!-- Lock Icon for Locked Features -->
                    <div v-if="!feature.unlocked" class="absolute top-2 right-2 flex items-center text-xs text-gray-500">
                        <span>🔒</span>
                        <span class="ml-1">Unlock at level {{ feature.requiredLevel }}</span>
                    </div>

                    <div class="flex items-start space-x-3">
                        <!-- Icon Box -->
                        <div :class="[
                            'w-14 h-14 rounded-lg flex items-center justify-center text-2xl',
                            `bg-gradient-to-br ${feature.color}`
                        ]">
                            {{ feature.icon }}
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-white truncate">{{ feature.name }}</h3>
                            <!-- Star Rating -->
                            <div class="flex items-center space-x-0.5 my-1">
                                <span 
                                    v-for="i in 10" 
                                    :key="i" 
                                    :class="[
                                        'text-xs',
                                        i <= feature.stars ? 'text-yellow-400' : 'text-gray-600'
                                    ]"
                                >★</span>
                            </div>
                            <p class="text-xs text-gray-400 truncate">{{ feature.description }}</p>
                        </div>
                    </div>

                    <!-- Hover Effect Overlay -->
                    <div v-if="feature.unlocked" class="absolute inset-0 bg-cyan-500/5 opacity-0 group-hover:opacity-100 rounded-lg transition-opacity"></div>
                </component>
            </div>

            <!-- Module View -->
            <div v-if="activeTab === 'module' && activeModule">
                <!-- Back Button -->
                <div class="mb-4">
                    <button 
                        @click="closeModule"
                        class="flex items-center space-x-2 text-cyan-400 hover:text-cyan-300 transition"
                    >
                        <span>←</span>
                        <span>Back to Dashboard</span>
                    </button>
                </div>
                
                <!-- Module Content -->
                <div class="bg-[#1c2128] border border-[#30363d] rounded-lg p-6">
                    <component 
                        :is="moduleComponents[activeModule]" 
                        :player="player"
                        :embedded="true"
                        v-bind="moduleData"
                    />
                </div>
            </div>

            <!-- Stats Tab -->
            <div v-if="activeTab === 'stats'" class="space-y-6">
                <!-- Player Info Card -->
                <div class="bg-[#1c2128] border border-[#30363d] rounded-lg p-6">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-cyan-500 to-purple-600 flex items-center justify-center text-3xl font-bold">
                            {{ player?.username?.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">{{ player?.username }}</h2>
                            <p class="text-gray-400">{{ player?.rank }} • Level {{ player?.level }}</p>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-[#0d1117] rounded-lg p-4">
                            <div class="text-gray-400 text-sm mb-1">Cash</div>
                            <div class="text-xl font-bold text-green-400">{{ formatMoney(player?.cash || 0) }}</div>
                        </div>
                        <div class="bg-[#0d1117] rounded-lg p-4">
                            <div class="text-gray-400 text-sm mb-1">Bank</div>
                            <div class="text-xl font-bold text-blue-400">{{ formatMoney(player?.bank || 0) }}</div>
                        </div>
                        <div class="bg-[#0d1117] rounded-lg p-4">
                            <div class="text-gray-400 text-sm mb-1">Respect</div>
                            <div class="text-xl font-bold text-yellow-400">{{ player?.respect?.toLocaleString() || 0 }}</div>
                        </div>
                        <div class="bg-[#0d1117] rounded-lg p-4">
                            <div class="text-gray-400 text-sm mb-1">Level</div>
                            <div class="text-xl font-bold text-purple-400">{{ player?.level || 1 }}</div>
                        </div>
                    </div>

                    <!-- Battle Stats -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Battle Stats</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-[#0d1117] rounded-lg p-4">
                                <div class="text-gray-400 text-sm mb-1">Strength</div>
                                <div class="text-xl font-bold text-red-400">{{ player?.strength || 0 }}</div>
                            </div>
                            <div class="bg-[#0d1117] rounded-lg p-4">
                                <div class="text-gray-400 text-sm mb-1">Defense</div>
                                <div class="text-xl font-bold text-blue-400">{{ player?.defense || 0 }}</div>
                            </div>
                            <div class="bg-[#0d1117] rounded-lg p-4">
                                <div class="text-gray-400 text-sm mb-1">Speed</div>
                                <div class="text-xl font-bold text-green-400">{{ player?.speed || 0 }}</div>
                            </div>
                            <div class="bg-[#0d1117] rounded-lg p-4">
                                <div class="text-gray-400 text-sm mb-1">Dexterity</div>
                                <div class="text-xl font-bold text-yellow-400">{{ player?.dexterity || 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- XP Progress -->
                    <div class="mt-6">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-400">Experience to next level</span>
                            <span class="text-cyan-400">{{ player?.experience || 0 }} / {{ player?.next_level_xp || 1000 }}</span>
                        </div>
                        <div class="w-full bg-[#0d1117] rounded-full h-3 overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-cyan-500 to-purple-500 transition-all"
                                :style="{ width: `${((player?.experience || 0) / (player?.next_level_xp || 1000)) * 100}%` }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GameLayout>
</template>
