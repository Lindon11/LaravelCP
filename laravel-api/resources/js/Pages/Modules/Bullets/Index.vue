<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: {
        type: Object,
        default: null
    },
    costPerBullet: {
        type: Number,
        default: 50
    },
});

const processing = ref(false);
const quantity = ref(100);

const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
    }).format(amount);
};

const totalCost = computed(() => {
    return quantity.value * props.costPerBullet;
});

const canAfford = computed(() => {
    return props.player && props.player.cash >= totalCost.value;
});

const buyBullets = () => {
    if (processing.value || !canAfford.value) return;

    processing.value = true;
    router.post(route('bullets.buy'), { quantity: quantity.value }, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};

const setQuickQuantity = (amount) => {
    quantity.value = amount;
};
</script>

<template>
    <AppLayout title="Bullet Shop">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🔫 Bullet Shop
                </h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div v-if="player">
                    <!-- Flash Messages -->
                    <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ $page.props.flash.success }}
                    </div>
                    <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ $page.props.flash.error }}
                    </div>

                    <!-- Current Status -->
                    <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Your Arsenal</h3>
                        
                        <div class="grid grid-cols-2 gap-6">
                            <div class="text-center p-6 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg">
                                <p class="text-yellow-600 font-semibold mb-2">Current Bullets</p>
                                <p class="text-5xl font-bold text-yellow-700">{{ player.bullets }}</p>
                            </div>
                            <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-lg">
                                <p class="text-green-600 font-semibold mb-2">Cash Available</p>
                                <p class="text-5xl font-bold text-green-700">{{ formatMoney(player.cash) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Buy Bullets -->
                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-8 text-white mb-6">
                        <h3 class="text-3xl font-bold mb-2">💥 Buy Ammunition</h3>
                        <p class="text-orange-100 text-lg mb-6">{{ formatMoney(costPerBullet) }} per bullet</p>

                        <div class="bg-white/10 backdrop-blur rounded-lg p-6">
                            <label class="block text-white font-semibold mb-3 text-lg">
                                Quantity
                            </label>
                            <input
                                v-model.number="quantity"
                                type="number"
                                min="1"
                                step="1"
                                class="w-full px-6 py-4 border-0 rounded-lg text-gray-900 text-2xl font-bold focus:ring-4 focus:ring-orange-300"
                            >

                            <!-- Quick Select -->
                            <div class="grid grid-cols-5 gap-3 mt-4">
                                <button 
                                    @click="setQuickQuantity(50)"
                                    class="px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg font-bold transition"
                                >
                                    50
                                </button>
                                <button 
                                    @click="setQuickQuantity(100)"
                                    class="px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg font-bold transition"
                                >
                                    100
                                </button>
                                <button 
                                    @click="setQuickQuantity(250)"
                                    class="px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg font-bold transition"
                                >
                                    250
                                </button>
                                <button 
                                    @click="setQuickQuantity(500)"
                                    class="px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg font-bold transition"
                                >
                                    500
                                </button>
                                <button 
                                    @click="setQuickQuantity(1000)"
                                    class="px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg font-bold transition"
                                >
                                    1000
                                </button>
                            </div>

                            <!-- Cost Display -->
                            <div class="mt-6 p-4 bg-white/20 backdrop-blur rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg">Total Cost:</span>
                                    <span class="text-3xl font-bold">{{ formatMoney(totalCost) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-orange-100">
                                    <span class="text-sm">Cash After Purchase:</span>
                                    <span class="font-semibold">{{ formatMoney(player.cash - totalCost) }}</span>
                                </div>
                            </div>

                            <!-- Buy Button -->
                            <button
                                @click="buyBullets"
                                :disabled="processing || !canAfford || quantity <= 0"
                                class="w-full mt-6 py-5 bg-white text-orange-600 rounded-lg hover:bg-orange-50 disabled:opacity-50 disabled:cursor-not-allowed font-bold text-xl transition transform hover:scale-105"
                            >
                                {{ processing ? 'Processing...' : `Buy ${quantity} Bullets for ${formatMoney(totalCost)}` }}
                            </button>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">🎯 Bullet Information</h3>
                        <ul class="text-gray-700 text-sm space-y-2 list-disc list-inside">
                            <li>Bullets are required to attack other players</li>
                            <li>Each attack attempt uses 1 bullet</li>
                            <li>Price: {{ formatMoney(costPerBullet) }} per bullet</li>
                            <li>Stock up before going hunting!</li>
                            <li>You can't attack without bullets</li>
                            <li>Bullets don't expire - buy in bulk to save time</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
