<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: Object,
    companies: Array,
    stats: Object,
    currentEmployment: Object,
    cooldown: Number,
});

const processing = ref(false);
const selectedAction = ref(null);

const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0
    }).format(amount);
};

const canWork = computed(() => {
    return !processing.value && 
           props.cooldown <= 0 && 
           props.player?.energy >= 5 &&
           props.currentEmployment;
});

const applyForJob = (position) => {
    processing.value = true;
    selectedAction.value = `apply-${position.id}`;
    
    router.post(route('employment.apply', position.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            selectedAction.value = null;
        }
    });
};

const workShift = () => {
    processing.value = true;
    selectedAction.value = 'work';
    
    router.post(route('employment.work'), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            selectedAction.value = null;
        }
    });
};

const quitJob = () => {
    if (!confirm('Are you sure you want to quit your job?')) return;
    
    processing.value = true;
    selectedAction.value = 'quit';
    
    router.post(route('employment.quit'), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            selectedAction.value = null;
        }
    });
};

const getCompanyTypeIcon = (type) => {
    const icons = {
        'hospital': '🏥',
        'law_firm': '⚖️',
        'tech_company': '💻',
        'retail': '🏪',
        'restaurant': '🍽️',
        'security': '🛡️',
        'other': '🏢'
    };
    return icons[type] || '🏢';
};

const canApplyForPosition = (position) => {
    return props.player.level >= position.level_requirement &&
           (!position.strength_requirement || props.player.strength >= position.strength_requirement) &&
           (!position.intelligence_requirement || props.player.intelligence >= position.intelligence_requirement);
};
</script>

<template>
    <AppLayout title="Employment">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-2">💼 Employment</h1>
                            <p class="text-blue-100">Get a job and earn a steady income</p>
                        </div>
                        <div v-if="currentEmployment" class="text-right">
                            <div class="text-white text-sm">Current Position</div>
                            <div class="text-2xl font-bold text-white">{{ stats.current_position }}</div>
                            <div class="text-blue-100 text-sm">{{ stats.current_company }}</div>
                        </div>
                    </div>
                </div>

                <!-- Player Status -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-gray-600 text-sm">Energy</div>
                        <div class="text-2xl font-bold" :class="player.energy >= 5 ? 'text-green-600' : 'text-red-600'">
                            {{ player.energy }}/100
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-gray-600 text-sm">Employment Status</div>
                        <div class="text-2xl font-bold" :class="stats.currently_employed ? 'text-green-600' : 'text-gray-600'">
                            {{ stats.currently_employed ? 'Employed' : 'Unemployed' }}
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-gray-600 text-sm">Current Salary</div>
                        <div class="text-2xl font-bold text-green-600">{{ formatMoney(stats.current_salary) }}</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-gray-600 text-sm">Shifts Worked</div>
                        <div class="text-2xl font-bold text-blue-600">{{ stats.total_shifts_worked }}</div>
                    </div>
                </div>

                <!-- Current Job Card -->
                <div v-if="currentEmployment" class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-1">Your Current Job</h2>
                            <p class="text-gray-600">{{ currentEmployment.position.name }} at {{ currentEmployment.company.name }}</p>
                        </div>
                        <button 
                            @click="quitJob"
                            :disabled="processing"
                            class="px-4 py-2 rounded-lg font-semibold text-white bg-red-600 hover:bg-red-700 transition disabled:opacity-50">
                            Quit Job
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <span class="text-gray-500 text-sm">Salary per shift:</span>
                            <div class="font-semibold text-green-600 text-lg">{{ formatMoney(currentEmployment.salary) }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500 text-sm">Total Shifts:</span>
                            <div class="font-semibold text-blue-600 text-lg">{{ currentEmployment.total_shifts }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500 text-sm">Energy Cost:</span>
                            <div class="font-semibold text-orange-600 text-lg">5 Energy</div>
                        </div>
                    </div>

                    <!-- Cooldown Warning -->
                    <div v-if="cooldown > 0" class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4 rounded-lg">
                        <p class="text-yellow-800">
                            ⏱️ You must wait <strong>{{ Math.floor(cooldown / 60) }}m {{ cooldown % 60 }}s</strong> before working again
                        </p>
                    </div>

                    <!-- Energy Warning -->
                    <div v-else-if="player.energy < 5" class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-lg">
                        <p class="text-red-800">
                            ⚡ Not enough energy! You need at least 5 energy to work
                        </p>
                    </div>

                    <button 
                        @click="workShift"
                        :disabled="!canWork"
                        class="w-full px-6 py-4 rounded-lg font-bold text-white text-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="selectedAction === 'work' ? 'bg-gray-400' : 'bg-blue-600 hover:bg-blue-700'">
                        <span v-if="selectedAction === 'work'">
                            <svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span v-else>
                            🔨 Work Shift
                        </span>
                    </button>
                </div>

                <!-- Available Companies -->
                <div class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-900">{{ currentEmployment ? 'Other Companies' : 'Available Companies' }}</h2>
                    
                    <div v-for="company in companies" :key="company.id" class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 border-b">
                            <div class="flex items-center gap-3">
                                <span class="text-4xl">{{ getCompanyTypeIcon(company.type) }}</span>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">{{ company.name }}</h3>
                                    <p class="text-gray-600">{{ company.description }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <h4 class="font-semibold text-gray-700 mb-4">Available Positions:</h4>
                            <div class="space-y-3">
                                <div v-for="position in company.positions" :key="position.id" 
                                     class="border rounded-lg p-4 hover:shadow-md transition"
                                     :class="{ 'opacity-50': currentEmployment || !canApplyForPosition(position) }">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <h5 class="font-bold text-gray-900">{{ position.name }}</h5>
                                                <span v-if="position.level_requirement > 1" 
                                                      class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700">
                                                    Level {{ position.level_requirement }}+
                                                </span>
                                            </div>
                                            <p class="text-gray-600 text-sm mb-3">{{ position.description }}</p>
                                            
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                                <div>
                                                    <span class="text-gray-500">Salary:</span>
                                                    <div class="font-semibold text-green-600">{{ formatMoney(position.base_salary) }}</div>
                                                </div>
                                                <div v-if="position.strength_requirement">
                                                    <span class="text-gray-500">Strength:</span>
                                                    <div class="font-semibold" :class="player.strength >= position.strength_requirement ? 'text-green-600' : 'text-red-600'">
                                                        {{ position.strength_requirement }}+
                                                    </div>
                                                </div>
                                                <div v-if="position.intelligence_requirement">
                                                    <span class="text-gray-500">Intelligence:</span>
                                                    <div class="font-semibold" :class="player.intelligence >= position.intelligence_requirement ? 'text-green-600' : 'text-red-600'">
                                                        {{ position.intelligence_requirement }}+
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Cooldown:</span>
                                                    <div class="font-semibold text-blue-600">15 minutes</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="ml-4">
                                            <button 
                                                @click="applyForJob(position)"
                                                :disabled="currentEmployment || !canApplyForPosition(position) || selectedAction === `apply-${position.id}`"
                                                class="px-4 py-2 rounded-lg font-semibold text-white transition disabled:opacity-50 disabled:cursor-not-allowed"
                                                :class="selectedAction === `apply-${position.id}` ? 'bg-gray-400' : 'bg-blue-600 hover:bg-blue-700'">
                                                <span v-if="selectedAction === `apply-${position.id}`">
                                                    <svg class="animate-spin h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </span>
                                                <span v-else-if="currentEmployment">
                                                    Employed
                                                </span>
                                                <span v-else-if="!canApplyForPosition(position)">
                                                    Locked
                                                </span>
                                                <span v-else>
                                                    Apply
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Pulse animation for work button */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .8;
    }
}

button:not(:disabled).bg-blue-600:hover {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
