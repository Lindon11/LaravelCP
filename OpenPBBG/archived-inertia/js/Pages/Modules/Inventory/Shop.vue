<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    items: Array,
    player: Object,
});

const selectedFilter = ref('all');
const buyForm = useForm({
    quantity: 1,
});

const filteredItems = computed(() => {
    if (selectedFilter.value === 'all') {
        return props.items;
    }
    return props.items.filter(item => item.type === selectedFilter.value);
});

const getRarityColor = (rarity) => {
    const colors = {
        common: 'bg-gray-500',
        uncommon: 'bg-green-500',
        rare: 'bg-blue-500',
        epic: 'bg-purple-500',
        legendary: 'bg-yellow-500'
    };
    return colors[rarity] || 'bg-gray-500';
};

const canAfford = (item) => {
    return props.player.cash >= (item.price * buyForm.quantity);
};

const buy = (item) => {
    if (!canAfford(item)) {
        alert('You cannot afford this item!');
        return;
    }
    
    router.post(route('shop.buy', item.id), {
        quantity: buyForm.quantity,
    }, {
        preserveScroll: true,
        onSuccess: () => buyForm.reset(),
    });
};

const meetsRequirements = (item) => {
    if (!item.requirements) return true;
    
    if (item.requirements.level && props.player.level < item.requirements.level) {
        return false;
    }
    
    return true;
};
</script>

<template>
    <AppLayout>
        <Head title="Shop" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">🛒 Shop</h1>
                    <div class="flex gap-4">
                        <a :href="route('inventory.index')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            🎒 Inventory
                        </a>
                        <a :href="route('dashboard')" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                            ← Dashboard
                        </a>
                    </div>
                </div>

                <!-- Player Cash -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg shadow-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-sm opacity-90">Your Cash</p>
                            <p class="text-3xl font-bold text-white">${{ player.cash?.toLocaleString() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-white text-sm opacity-90">Level</p>
                            <p class="text-3xl font-bold text-white">{{ player.level }}</p>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="flex border-b overflow-x-auto">
                        <button @click="selectedFilter = 'all'" 
                                :class="selectedFilter === 'all' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'"
                                class="px-6 py-3 font-semibold whitespace-nowrap">
                            All Items
                        </button>
                        <button @click="selectedFilter = 'weapon'" 
                                :class="selectedFilter === 'weapon' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'"
                                class="px-6 py-3 font-semibold whitespace-nowrap">
                            ⚔️ Weapons
                        </button>
                        <button @click="selectedFilter = 'armor'" 
                                :class="selectedFilter === 'armor' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'"
                                class="px-6 py-3 font-semibold whitespace-nowrap">
                            🛡️ Armor
                        </button>
                        <button @click="selectedFilter = 'vehicle'" 
                                :class="selectedFilter === 'vehicle' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'"
                                class="px-6 py-3 font-semibold whitespace-nowrap">
                            🚗 Vehicles
                        </button>
                        <button @click="selectedFilter = 'consumable'" 
                                :class="selectedFilter === 'consumable' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'"
                                class="px-6 py-3 font-semibold whitespace-nowrap">
                            💊 Consumables
                        </button>
                    </div>
                </div>

                <!-- Items Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="item in filteredItems" :key="item.id" 
                         class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition relative">
                        
                        <!-- Rarity Bar -->
                        <div :class="getRarityColor(item.rarity)" class="h-2"></div>
                        
                        <!-- Locked Overlay -->
                        <div v-if="!meetsRequirements(item)" 
                             class="absolute inset-0 bg-black bg-opacity-70 flex items-center justify-center z-10 rounded-lg">
                            <div class="text-center">
                                <p class="text-white text-xl font-bold mb-2">🔒 Locked</p>
                                <p class="text-white text-sm">Requires Level {{ item.requirements?.level }}</p>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">{{ item.name }}</h3>
                                    <p class="text-sm text-gray-600 capitalize">{{ item.type }}</p>
                                </div>
                                <span :class="getRarityColor(item.rarity)" 
                                      class="px-2 py-1 text-xs font-semibold text-white rounded uppercase">
                                    {{ item.rarity }}
                                </span>
                            </div>
                            
                            <p class="text-gray-700 mb-4 text-sm">{{ item.description }}</p>
                            
                            <!-- Stats -->
                            <div v-if="item.stats && Object.keys(item.stats).length > 0" class="mb-4">
                                <p class="text-xs text-gray-600 mb-2 font-semibold uppercase">Stats</p>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="(value, stat) in item.stats" :key="stat" 
                                          class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">
                                        {{ stat }}: +{{ value }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Requirements -->
                            <div v-if="item.requirements && Object.keys(item.requirements).length > 0" class="mb-4">
                                <p class="text-xs text-gray-600 mb-2 font-semibold uppercase">Requirements</p>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="(value, req) in item.requirements" :key="req" 
                                          :class="meetsRequirements(item) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                          class="px-2 py-1 text-xs font-semibold rounded capitalize">
                                        {{ req }}: {{ value }} {{ meetsRequirements(item) ? '✓' : '✗' }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Price -->
                            <div class="mb-4 bg-gray-50 rounded-lg p-3">
                                <p class="text-2xl font-bold text-green-600">
                                    ${{ (item.price * buyForm.quantity).toLocaleString() }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    ${{ item.price.toLocaleString() }} each
                                </p>
                            </div>
                            
                            <!-- Quantity Selector (for stackable items) -->
                            <div v-if="item.stackable" class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                <input v-model.number="buyForm.quantity" 
                                       type="number" 
                                       min="1" 
                                       :max="item.max_stack || 100"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <!-- Buy Button -->
                            <button @click="buy(item)" 
                                    :disabled="!canAfford(item) || !meetsRequirements(item)"
                                    :class="canAfford(item) && meetsRequirements(item) 
                                        ? 'bg-green-600 hover:bg-green-700' 
                                        : 'bg-gray-400 cursor-not-allowed'"
                                    class="w-full px-4 py-3 text-white font-semibold rounded-lg transition">
                                <span v-if="!meetsRequirements(item)">🔒 Locked</span>
                                <span v-else-if="!canAfford(item)">💰 Can't Afford</span>
                                <span v-else>Buy Now</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredItems.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-500 text-lg">No items available in this category</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
