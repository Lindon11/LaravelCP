<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: { type: Object, default: null },
    crimes: { type: Array, default: () => [] },
    gang: { type: Object, default: null },
    history: { type: Array, default: () => [] },
});

const processing = ref(false);
const selectedCrime = ref(null);
const selectedMembers = ref([]);

const formatMoney = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(val);

const attemptCrime = () => {
    if (processing.value || !selectedCrime.value || selectedMembers.value.length < selectedCrime.value.required_members) return;
    processing.value = true;
    router.post(route('organized-crimes.attempt', selectedCrime.value.id), { participants: selectedMembers.value }, {
        preserveScroll: true,
        onFinish: () => { processing.value = false; selectedCrime.value = null; selectedMembers.value = []; }
    });
};

const toggleMember = (memberId) => {
    const index = selectedMembers.value.indexOf(memberId);
    if (index > -1) {
        selectedMembers.value.splice(index, 1);
    } else {
        selectedMembers.value.push(memberId);
    }
};
</script>

<template>
    <AppLayout title="Organized Crimes">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">🎭 Organized Crime</h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="player">
                    <!-- Flash -->
                    <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ $page.props.flash.success }}</div>
                    <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $page.props.flash.error }}</div>

                    <!-- No Gang Warning -->
                    <div v-if="!gang" class="bg-yellow-50 border border-yellow-400 text-yellow-800 p-6 rounded-lg mb-6">
                        <div class="flex items-center gap-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <p class="font-bold text-lg">Gang Required</p>
                                <p>You must be in a gang to attempt organized crimes. Only gang leaders can initiate them.</p>
                            </div>
                        </div>
                    </div>

                    <div v-else>
                        <!-- Available Crimes -->
                        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                            <h3 class="text-2xl font-bold mb-6">Available Organized Crimes</h3>
                            <div class="space-y-4">
                                <div v-for="crime in crimes" :key="crime.id" 
                                     class="border rounded-lg p-6 hover:shadow-md transition cursor-pointer"
                                     :class="selectedCrime?.id === crime.id ? 'border-purple-600 bg-purple-50' : 'border-gray-200'"
                                     @click="selectedCrime = crime">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <h4 class="text-2xl font-bold mb-2">{{ crime.name }}</h4>
                                            <p class="text-gray-600 mb-3">{{ crime.description }}</p>
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                                <div><span class="text-gray-500">Success Rate:</span> <span class="font-semibold text-yellow-600">{{ crime.success_rate }}%</span></div>
                                                <div><span class="text-gray-500">Reward:</span> <span class="font-semibold text-green-600">{{ formatMoney(crime.min_reward) }} - {{ formatMoney(crime.max_reward) }}</span></div>
                                                <div><span class="text-gray-500">Members:</span> <span class="font-semibold">{{ crime.required_members }}+</span></div>
                                                <div><span class="text-gray-500">Level:</span> <span class="font-semibold">{{ crime.required_level }}+</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Member Selection -->
                        <div v-if="selectedCrime && gang.leader_id === player.id" class="bg-white rounded-lg shadow-lg p-6 mb-6">
                            <h3 class="text-2xl font-bold mb-4">Select {{ selectedCrime.required_members }}+ Gang Members</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                                <div v-for="member in gang.members" :key="member.id" 
                                     @click="toggleMember(member.id)"
                                     class="border-2 rounded-lg p-4 cursor-pointer transition"
                                     :class="selectedMembers.includes(member.id) ? 'border-purple-600 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                                    <p class="font-bold">{{ member.username }}</p>
                                    <p class="text-sm text-gray-600">Level {{ member.level }}</p>
                                </div>
                            </div>
                            <button @click="attemptCrime" 
                                    :disabled="processing || selectedMembers.length < selectedCrime.required_members" 
                                    class="w-full py-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-bold text-xl disabled:bg-gray-400">
                                Attempt Crime ({{ selectedMembers.length }}/{{ selectedCrime.required_members }} selected)
                            </button>
                        </div>

                        <!-- Gang History -->
                        <div v-if="history.length > 0" class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-2xl font-bold mb-4">Gang Crime History</h3>
                            <div class="space-y-3">
                                <div v-for="(record, i) in history" :key="i" 
                                     class="border rounded-lg p-4"
                                     :class="record.success ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50'">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h4 class="font-bold">{{ record.name }}</h4>
                                            <p :class="record.success ? 'text-green-700' : 'text-red-700'">{{ record.result_message }}</p>
                                            <p class="text-sm text-gray-500 mt-1">{{ new Date(record.attempted_at).toLocaleString() }}</p>
                                        </div>
                                        <span v-if="record.success" class="px-3 py-1 bg-green-600 text-white font-bold rounded text-sm">SUCCESS</span>
                                        <span v-else class="px-3 py-1 bg-red-600 text-white font-bold rounded text-sm">FAILED</span>
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
