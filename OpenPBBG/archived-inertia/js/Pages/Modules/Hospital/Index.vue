<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: {
        type: Object,
        default: null
    },
    costPerHp: {
        type: Number,
        default: 100
    },
    fullHealCost: {
        type: Number,
        default: 0
    },
});

const processing = ref(false);
const customAmount = ref(10);

const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
    }).format(amount);
};

const healthPercent = computed(() => {
    if (!props.player) return 0;
    return Math.round((props.player.health / props.player.max_health) * 100);
});

const healthMissing = computed(() => {
    if (!props.player) return 0;
    return props.player.max_health - props.player.health;
});

const customCost = computed(() => {
    return customAmount.value * props.costPerHp;
});

const canAffordCustom = computed(() => {
    return props.player && props.player.cash >= customCost.value;
});

const canAffordFull = computed(() => {
    return props.player && props.player.cash >= props.fullHealCost;
});

const healCustom = () => {
    if (processing.value || !canAffordCustom.value) return;

    processing.value = true;
    router.post(route('hospital.heal'), { amount: customAmount.value }, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};

const healFull = () => {
    if (processing.value || !canAffordFull.value) return;

    processing.value = true;
    router.post(route('hospital.healFull'), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};

const getHealthBarColor = () => {
    if (healthPercent.value >= 70) return 'bg-green-500';
    if (healthPercent.value >= 40) return 'bg-yellow-500';
    return 'bg-red-500';
};

const setQuickAmount = (percent) => {
    customAmount.value = Math.ceil(healthMissing.value * (percent / 100));
};
</script>

<template>
    <AppLayout title="Hospital">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🏥 Hospital
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

                    <!-- Current Health Status -->
                    <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Your Health Status</h3>
                        
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-lg font-semibold text-gray-700">Health:</span>
                            <span class="text-2xl font-bold" :class="healthPercent >= 70 ? 'text-green-600' : healthPercent >= 40 ? 'text-yellow-600' : 'text-red-600'">
                                {{ player.health }} / {{ player.max_health }} HP
                            </span>
                        </div>

                        <!-- Health Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-8 overflow-hidden mb-6">
                            <div 
                                :class="getHealthBarColor()"
                                :style="{ width: healthPercent + '%' }"
                                class="h-full flex items-center justify-center text-white font-bold text-sm transition-all duration-300"
                            >
                                {{ healthPercent }}%
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-gray-600">
                            <span>Cash Available:</span>
                            <span class="text-xl font-bold text-green-600">{{ formatMoney(player.cash) }}</span>
                        </div>
                    </div>

                    <!-- Heal Full Health -->
                    <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-lg p-8 mb-6 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h3 class="text-2xl font-bold mb-2">💊 Full Treatment</h3>
                                <p class="text-red-100 mb-2">Restore to {{ player.max_health }} HP</p>
                                <p class="text-3xl font-bold">{{ formatMoney(fullHealCost) }}</p>
                                <p class="text-red-200 text-sm">({{ formatMoney(costPerHp) }} per HP)</p>
                            </div>
                            <button
                                @click="healFull"
                                :disabled="processing || !canAffordFull || healthMissing === 0"
                                class="px-8 py-4 bg-white text-red-600 rounded-lg hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed font-bold text-lg transition transform hover:scale-105"
                            >
                                {{ healthMissing === 0 ? 'Full Health' : processing ? 'Healing...' : 'Heal Full' }}
                            </button>
                        </div>
                    </div>

                    <!-- Custom Heal Amount -->
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">🩹 Partial Treatment</h3>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">
                                Heal Amount (HP)
                            </label>
                            <input
                                v-model.number="customAmount"
                                type="number"
                                min="1"
                                :max="healthMissing"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-red-500 focus:ring focus:ring-red-200 text-lg"
                            >
                        </div>

                        <!-- Quick Select Buttons -->
                        <div class="grid grid-cols-4 gap-3 mb-6">
                            <button 
                                @click="setQuickAmount(25)"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-semibold text-gray-700 transition"
                            >
                                25%
                            </button>
                            <button 
                                @click="setQuickAmount(50)"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-semibold text-gray-700 transition"
                            >
                                50%
                            </button>
                            <button 
                                @click="setQuickAmount(75)"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-semibold text-gray-700 transition"
                            >
                                75%
                            </button>
                            <button 
                                @click="customAmount = healthMissing"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-semibold text-gray-700 transition"
                            >
                                All
                            </button>
                        </div>

                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                            <p class="text-blue-800 font-semibold">
                                Cost: {{ formatMoney(customCost) }}
                            </p>
                            <p class="text-blue-700 text-sm">
                                You will have {{ formatMoney(player.cash - customCost) }} left
                            </p>
                        </div>

                        <button
                            @click="healCustom"
                            :disabled="processing || !canAffordCustom || customAmount <= 0 || customAmount > healthMissing"
                            class="w-full py-4 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed font-bold text-lg transition"
                        >
                            {{ processing ? 'Healing...' : `Heal ${customAmount} HP for ${formatMoney(customCost)}` }}
                        </button>
                    </div>

                    <!-- Info Box -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">💡 Hospital Information</h3>
                        <ul class="text-gray-700 text-sm space-y-2 list-disc list-inside">
                            <li>Hospital charges {{ formatMoney(costPerHp) }} per health point</li>
                            <li>You can heal partially or restore to full health</li>
                            <li>Health is lost when you're killed by other players</li>
                            <li>Maximum health increases with your level</li>
                            <li>Keep your health high to survive attacks!</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
