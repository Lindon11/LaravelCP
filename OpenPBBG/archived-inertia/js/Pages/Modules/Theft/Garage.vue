<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: {
        type: Object,
        default: null
    },
    garage: {
        type: Array,
        default: () => []
    },
});

const processing = ref(false);

const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
    }).format(amount);
};

const sellCar = (garageId) => {
    if (processing.value) return;

    if (!confirm('Are you sure you want to sell this car?')) {
        return;
    }

    processing.value = true;
    router.post(route('theft.sell', garageId), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};

const getDamageColor = (damage) => {
    if (damage < 20) return 'text-green-600';
    if (damage < 40) return 'text-yellow-600';
    if (damage < 60) return 'text-orange-600';
    return 'text-red-600';
};

const getDamageBar = (damage) => {
    if (damage < 20) return 'bg-green-500';
    if (damage < 40) return 'bg-yellow-500';
    if (damage < 60) return 'bg-orange-500';
    return 'bg-red-500';
};
</script>

<template>
    <AppLayout title="Garage">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Your Garage
                </h2>
                <a :href="route('theft.index')" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 font-semibold">
                    Steal More Cars
                </a>
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

                    <!-- Empty State -->
                    <div v-if="garage.length === 0" class="bg-white rounded-lg shadow-lg p-12 text-center">
                        <svg class="w-24 h-24 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Your Garage is Empty</h3>
                        <p class="text-gray-600 mb-6">You haven't stolen any cars yet. Head over to Car Theft to start building your collection!</p>
                        <a :href="route('theft.index')" class="inline-block px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-bold">
                            Start Stealing Cars
                        </a>
                    </div>

                    <!-- Garage Grid -->
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div 
                            v-for="car in garage" 
                            :key="car.id"
                            class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition"
                        >
                            <!-- Car Header -->
                            <div class="bg-gradient-to-r from-gray-700 to-gray-800 p-4">
                                <h3 class="text-xl font-bold text-white">{{ car.car_name }}</h3>
                                <p class="text-gray-300 text-sm">{{ car.location }}</p>
                            </div>

                            <!-- Car Details -->
                            <div class="p-6">
                                <!-- Value -->
                                <div class="mb-4">
                                    <div class="flex justify-between items-baseline mb-1">
                                        <span class="text-gray-600 text-sm">Base Value:</span>
                                        <span class="text-gray-500 line-through">{{ formatMoney(car.base_value) }}</span>
                                    </div>
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-gray-900 font-semibold">Current Value:</span>
                                        <span class="text-green-600 font-bold text-xl">{{ formatMoney(car.current_value) }}</span>
                                    </div>
                                </div>

                                <!-- Damage -->
                                <div class="mb-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-600 text-sm">Damage:</span>
                                        <span :class="getDamageColor(car.damage)" class="font-bold">{{ car.damage }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                        <div 
                                            :class="getDamageBar(car.damage)"
                                            :style="{ width: car.damage + '%' }"
                                            class="h-full transition-all duration-300"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Stolen Time -->
                                <p class="text-gray-500 text-xs mb-4">Stolen {{ car.stolen_at }}</p>

                                <!-- Sell Button -->
                                <button
                                    @click="sellCar(car.id)"
                                    :disabled="processing"
                                    class="w-full py-3 px-4 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-bold transition"
                                >
                                    {{ processing ? 'Selling...' : 'Sell for ' + formatMoney(car.current_value) }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Garage Stats -->
                    <div v-if="garage.length > 0" class="mt-8 bg-blue-50 rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Garage Statistics</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center">
                                <p class="text-3xl font-bold text-blue-600">{{ garage.length }}</p>
                                <p class="text-gray-600 text-sm">Total Cars</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-bold text-green-600">
                                    {{ formatMoney(garage.reduce((sum, car) => sum + car.current_value, 0)) }}
                                </p>
                                <p class="text-gray-600 text-sm">Total Value</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-bold text-orange-600">
                                    {{ Math.round(garage.reduce((sum, car) => sum + car.damage, 0) / garage.length) }}%
                                </p>
                                <p class="text-gray-600 text-sm">Average Damage</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
