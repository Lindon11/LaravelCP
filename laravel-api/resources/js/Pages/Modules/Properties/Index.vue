<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: { type: Object, default: null },
    available: { type: Array, default: () => [] },
    myProperties: { type: Array, default: () => [] },
});

const processing = ref(false);
const activeTab = ref('available');

const formatMoney = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(val);

const buyProperty = (propertyId) => {
    if (processing.value) return;
    processing.value = true;
    router.post(route('properties.buy', propertyId), {}, { preserveScroll: true, onFinish: () => processing.value = false });
};

const sellProperty = (propertyId) => {
    if (processing.value || !confirm('Sell this property for 70% of purchase price?')) return;
    processing.value = true;
    router.post(route('properties.sell', propertyId), {}, { preserveScroll: true, onFinish: () => processing.value = false });
};

const collectIncome = () => {
    if (processing.value) return;
    processing.value = true;
    router.post(route('properties.collect'), {}, { preserveScroll: true, onFinish: () => processing.value = false });
};

const groupedAvailable = (type) => props.available.filter(p => p.type === type);
const totalIncome = props.myProperties.reduce((sum, p) => sum + parseFloat(p.income_per_day), 0);
</script>

<template>
    <AppLayout title="Properties">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">🏠 Properties</h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="player">
                    <!-- Flash -->
                    <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ $page.props.flash.success }}</div>
                    <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $page.props.flash.error }}</div>

                    <!-- Tabs -->
                    <div class="bg-white rounded-lg shadow-lg mb-6">
                        <div class="flex border-b">
                            <button @click="activeTab = 'available'" :class="activeTab === 'available' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="flex-1 py-4 font-semibold">Available Properties</button>
                            <button @click="activeTab = 'mine'" :class="activeTab === 'mine' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="flex-1 py-4 font-semibold">My Properties ({{ myProperties.length }})</button>
                        </div>

                        <!-- Available Tab -->
                        <div v-if="activeTab === 'available'" class="p-6">
                            <div v-for="type in ['house', 'business', 'warehouse']" :key="type" class="mb-8">
                                <h3 class="text-2xl font-bold mb-4 capitalize">{{ type }}s</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="prop in groupedAvailable(type)" :key="prop.id" class="border rounded-lg p-4 hover:bg-gray-50">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="flex-1">
                                                <h4 class="text-xl font-bold">{{ prop.name }}</h4>
                                                <p class="text-gray-600 text-sm">{{ prop.description }}</p>
                                            </div>
                                            <div class="text-right ml-4">
                                                <p class="text-2xl font-bold text-green-600">{{ formatMoney(prop.price) }}</p>
                                                <p class="text-sm text-gray-500">Lvl {{ prop.required_level }}+</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm">
                                                <span class="text-blue-600 font-semibold">{{ formatMoney(prop.income_per_day) }}/day</span>
                                            </div>
                                            <button @click="buyProperty(prop.id)" :disabled="processing || player.level < prop.required_level || player.cash < prop.price" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-bold disabled:bg-gray-400">Buy</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- My Properties Tab -->
                        <div v-else class="p-6">
                            <div v-if="myProperties.length === 0" class="text-center text-gray-500 py-12">
                                <p class="text-xl mb-2">You don't own any properties</p>
                                <p class="text-sm">Start investing to earn passive income!</p>
                            </div>
                            <div v-else>
                                <div class="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg p-6 mb-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-green-200">Total Daily Income</p>
                                            <p class="text-4xl font-bold">{{ formatMoney(totalIncome) }}</p>
                                        </div>
                                        <button @click="collectIncome" :disabled="processing" class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 rounded-lg font-bold text-xl">Collect Income</button>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div v-for="prop in myProperties" :key="prop.id" class="border border-green-200 rounded-lg p-4 hover:bg-green-50">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <h4 class="text-xl font-bold">{{ prop.name }}</h4>
                                                <p class="text-gray-600 text-sm">{{ prop.description }}</p>
                                                <p class="text-blue-600 font-semibold mt-1">{{ formatMoney(prop.income_per_day) }}/day</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-gray-600 text-sm mb-2">Purchased: {{ formatMoney(prop.price) }}</p>
                                                <button @click="sellProperty(prop.id)" :disabled="processing" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-semibold">Sell ({{ formatMoney(prop.price * 0.7) }})</button>
                                            </div>
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
