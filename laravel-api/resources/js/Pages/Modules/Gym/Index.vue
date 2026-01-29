<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: { type: Object, default: null },
    costs: { type: Object, default: () => ({}) },
    maxPerSession: { type: Number, default: 50 },
});

const processing = ref(false);
const selectedAttribute = ref('strength');
const times = ref(1);

const attributes = [
    { key: 'strength', name: 'Strength', icon: '💪', description: 'Increase attack power' },
    { key: 'defense', name: 'Defense', icon: '🛡️', description: 'Reduce damage taken' },
    { key: 'speed', name: 'Speed', icon: '⚡', description: 'Move faster, dodge attacks' },
    { key: 'stamina', name: 'Stamina', icon: '❤️', description: 'Increase max health' },
];

const formatMoney = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(val);

const totalCost = () => (props.costs[selectedAttribute.value] || 0) * times.value;

const train = () => {
    if (processing.value) return;
    processing.value = true;
    router.post(route('gym.train'), { attribute: selectedAttribute.value, times: times.value }, {
        preserveScroll: true,
        onFinish: () => processing.value = false
    });
};
</script>

<template>
    <AppLayout title="Gym">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">💪 Gym</h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="player">
                    <!-- Flash -->
                    <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ $page.props.flash.success }}</div>
                    <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $page.props.flash.error }}</div>

                    <!-- Gym Header -->
                    <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-lg shadow-lg p-8 mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-4xl font-bold mb-2">Iron Paradise Gym</h3>
                                <p class="text-orange-200">Train your stats to become stronger</p>
                            </div>
                            <div class="text-7xl">🏋️</div>
                        </div>
                    </div>

                    <!-- Training Selection -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h3 class="text-2xl font-bold mb-6">Select Training</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div v-for="attr in attributes" :key="attr.key" 
                                 @click="selectedAttribute = attr.key"
                                 class="border-2 rounded-lg p-6 cursor-pointer transition"
                                 :class="selectedAttribute === attr.key ? 'border-orange-600 bg-orange-50' : 'border-gray-200 hover:border-orange-300'">
                                <div class="flex items-center gap-4 mb-3">
                                    <div class="text-5xl">{{ attr.icon }}</div>
                                    <div class="flex-1">
                                        <h4 class="text-2xl font-bold">{{ attr.name }}</h4>
                                        <p class="text-gray-600 text-sm">{{ attr.description }}</p>
                                    </div>
                                </div>
                                <p class="text-orange-600 font-bold text-lg">{{ formatMoney(costs[attr.key]) }} per session</p>
                            </div>
                        </div>

                        <!-- Training Controls -->
                        <div class="border-t pt-6">
                            <div class="max-w-2xl mx-auto">
                                <label class="block text-lg font-semibold mb-3">How many times?</label>
                                <div class="flex items-center gap-4 mb-4">
                                    <input v-model.number="times" type="number" min="1" :max="maxPerSession" class="flex-1 px-4 py-3 border rounded-lg text-lg">
                                    <div class="flex gap-2">
                                        <button @click="times = 1" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">1</button>
                                        <button @click="times = 10" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">10</button>
                                        <button @click="times = 25" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">25</button>
                                        <button @click="times = 50" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">50</button>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                    <div class="flex justify-between items-center text-lg">
                                        <span class="font-semibold">Total Cost:</span>
                                        <span class="text-2xl font-bold text-orange-600">{{ formatMoney(totalCost()) }}</span>
                                    </div>
                                </div>
                                <button @click="train" :disabled="processing || times < 1 || player.cash < totalCost()" 
                                        class="w-full py-4 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-bold text-xl disabled:bg-gray-400">
                                    Start Training
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h4 class="font-bold text-blue-900 mb-2">💡 Training Tips</h4>
                        <ul class="text-blue-800 text-sm space-y-1">
                            <li>• Train regularly to increase your stats and dominate in combat</li>
                            <li>• Different training types help in different situations</li>
                            <li>• Max {{ maxPerSession }} sessions per training</li>
                            <li>• Higher stats = better success in crimes, fights, and missions</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
