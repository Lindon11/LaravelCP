<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    drugPrices: Array,
    playerDrugs: Array,
    totalValue: Number,
    player: Object,
});

const buyForm = useForm({
    drug_id: null,
    quantity: 1,
});

const sellForm = useForm({
    drug_id: null,
    quantity: 1,
});

const buy = (drug) => {
    buyForm.drug_id = drug.id;
    buyForm.post(route('drugs.buy'), {
        preserveScroll: true,
        onSuccess: () => buyForm.reset('quantity'),
    });
};

const sell = (playerDrug) => {
    sellForm.drug_id = playerDrug.drug.id;
    sellForm.post(route('drugs.sell'), {
        preserveScroll: true,
        onSuccess: () => sellForm.reset('quantity'),
    });
};

const getRiskColor = (chance) => {
    if (chance < 10) return 'text-green-600';
    if (chance < 20) return 'text-yellow-600';
    if (chance < 30) return 'text-orange-600';
    return 'text-red-600';
};

const getRiskLabel = (chance) => {
    if (chance < 10) return 'Low Risk';
    if (chance < 20) return 'Medium Risk';
    if (chance < 30) return 'High Risk';
    return 'Very High Risk';
};
</script>

<template>
    <AppLayout>
        <Head title="Drug Dealing" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">💊 Drug Dealing</h1>
                    <a :href="route('dashboard')" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                        ← Dashboard
                    </a>
                </div>

                <!-- Warning Banner -->
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        <div>
                            <p class="text-red-800 font-bold">⚠️ Warning: Risk of Police Bust!</p>
                            <p class="text-red-700 text-sm">Higher risk drugs have higher bust chances. If caught, you'll lose your drugs/money and go to jail!</p>
                        </div>
                    </div>
                </div>

                <!-- Player Stats -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Cash</p>
                            <p class="text-2xl font-bold text-green-600">${{ player.cash?.toLocaleString() }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Drug Value</p>
                            <p class="text-2xl font-bold text-purple-600">${{ totalValue.toLocaleString() }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Current Location</p>
                            <p class="text-xl font-bold">{{ player.location?.name || 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Drug Types Owned</p>
                            <p class="text-2xl font-bold">{{ playerDrugs.length }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Buy Drugs -->
                    <div>
                        <h2 class="text-2xl font-bold mb-4">💵 Buy Drugs</h2>
                        <div class="space-y-4">
                            <div v-for="drug in drugPrices" :key="drug.id" 
                                 class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">{{ drug.name }}</h3>
                                        <p class="text-sm text-gray-600">{{ drug.description }}</p>
                                    </div>
                                    <span :class="getRiskColor(drug.bust_chance)" 
                                          class="px-2 py-1 text-xs font-semibold rounded">
                                        {{ getRiskLabel(drug.bust_chance) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <p class="text-2xl font-bold text-green-600">
                                            ${{ drug.price.toLocaleString() }}
                                            <span class="text-sm text-gray-500">/ unit</span>
                                        </p>
                                        <p class="text-xs text-gray-500">Bust chance: {{ drug.bust_chance }}%</p>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <input v-model.number="buyForm.quantity" 
                                           type="number" 
                                           min="1" 
                                           max="1000" 
                                           placeholder="Quantity"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    <button @click="buy(drug)" 
                                            :disabled="player.cash < (drug.price * buyForm.quantity) || buyForm.processing"
                                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap">
                                        Buy ${{ (drug.price * buyForm.quantity).toLocaleString() }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sell Drugs -->
                    <div>
                        <h2 class="text-2xl font-bold mb-4">💰 Your Stash</h2>
                        <div v-if="playerDrugs.length > 0" class="space-y-4">
                            <div v-for="playerDrug in playerDrugs" :key="playerDrug.id" 
                                 class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">{{ playerDrug.drug.name }}</h3>
                                        <p class="text-sm text-gray-600">Quantity: {{ playerDrug.quantity }} units</p>
                                    </div>
                                    <span :class="getRiskColor(playerDrug.drug.bust_chance * 0.5)" 
                                          class="px-2 py-1 text-xs font-semibold rounded">
                                        {{ getRiskLabel(playerDrug.drug.bust_chance * 0.5) }}
                                    </span>
                                </div>

                                <div class="mb-4">
                                    <p class="text-sm text-gray-600">Current Price</p>
                                    <p class="text-2xl font-bold text-blue-600">
                                        ${{ drugPrices.find(d => d.id === playerDrug.drug.id)?.price.toLocaleString() }}
                                        <span class="text-sm text-gray-500">/ unit</span>
                                    </p>
                                    <p class="text-sm text-green-600 font-semibold">
                                        Total Value: ${{ (drugPrices.find(d => d.id === playerDrug.drug.id)?.price * playerDrug.quantity).toLocaleString() }}
                                    </p>
                                </div>

                                <div class="flex gap-2">
                                    <input v-model.number="sellForm.quantity" 
                                           type="number" 
                                           :min="1" 
                                           :max="playerDrug.quantity" 
                                           placeholder="Quantity"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <button @click="sell(playerDrug)" 
                                            :disabled="sellForm.quantity > playerDrug.quantity || sellForm.processing"
                                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap">
                                        Sell ${{ ((drugPrices.find(d => d.id === playerDrug.drug.id)?.price || 0) * sellForm.quantity).toLocaleString() }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="bg-white rounded-lg shadow p-12 text-center">
                            <p class="text-gray-500 text-lg mb-4">You have no drugs</p>
                            <p class="text-sm text-gray-400">Buy some drugs on the left to start dealing!</p>
                        </div>

                        <!-- Travel Tip -->
                        <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                                <div>
                                    <p class="text-blue-800 font-semibold text-sm">💡 Pro Tip</p>
                                    <p class="text-blue-700 text-xs">
                                        Prices vary by location! Travel to different cities to find the best deals.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
