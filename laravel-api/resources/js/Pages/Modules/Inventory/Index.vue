<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    inventory: Array,
    player: Object,
});

const selectedFilter = ref('all');

const filteredInventory = computed(() => {
    if (selectedFilter.value === 'all') {
        return props.inventory;
    }
    return props.inventory.filter(item => item.item.type === selectedFilter.value);
});

const equippedItems = computed(() => {
    return props.inventory.filter(item => item.equipped);
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

const equip = (inventoryId) => {
    router.post(route('inventory.equip', inventoryId), {}, {
        preserveScroll: true,
    });
};

const unequip = (inventoryId) => {
    router.post(route('inventory.unequip', inventoryId), {}, {
        preserveScroll: true,
    });
};

const useItem = (inventoryId) => {
    if (confirm('Are you sure you want to use this item?')) {
        router.post(route('inventory.use', inventoryId), {}, {
            preserveScroll: true,
        });
    }
};

const sellForm = useForm({
    quantity: 1,
});

const sell = (inventoryId, maxQuantity) => {
    if (confirm(`Are you sure you want to sell ${sellForm.quantity} of this item?`)) {
        router.post(route('inventory.sell', inventoryId), {
            quantity: sellForm.quantity,
        }, {
            preserveScroll: true,
            onSuccess: () => sellForm.reset(),
        });
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Inventory" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">Inventory</h1>
                    <div class="flex gap-4">
                        <a :href="route('shop.index')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            🛒 Shop
                        </a>
                        <a :href="route('dashboard')" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                            ← Dashboard
                        </a>
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
                            <p class="text-sm text-gray-600">Total Items</p>
                            <p class="text-2xl font-bold">{{ inventory.length }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Equipped</p>
                            <p class="text-2xl font-bold">{{ equippedItems.length }}</p>
                        </div>
                    </div>
                </div>

                <!-- Equipped Items -->
                <div v-if="equippedItems.length > 0" class="bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg shadow p-6 mb-6">
                    <h2 class="text-2xl font-bold text-white mb-4">⚔️ Equipped Items</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div v-for="item in equippedItems" :key="item.id" class="bg-white bg-opacity-20 backdrop-blur rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-white opacity-75 uppercase">{{ item.slot }}</p>
                                    <p class="text-lg font-bold text-white">{{ item.item.name }}</p>
                                    <div class="flex gap-2 mt-2">
                                        <span v-for="(value, stat) in item.item.stats" :key="stat" 
                                              class="text-xs bg-white bg-opacity-30 text-white px-2 py-1 rounded">
                                            {{ stat }}: +{{ value }}
                                        </span>
                                    </div>
                                </div>
                                <button @click="unequip(item.id)" 
                                        class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">
                                    Unequip
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="flex border-b">
                        <button @click="selectedFilter = 'all'" 
                                :class="selectedFilter === 'all' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'"
                                class="px-6 py-3 font-semibold">
                            All Items
                        </button>
                        <button @click="selectedFilter = 'weapon'" 
                                :class="selectedFilter === 'weapon' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'"
                                class="px-6 py-3 font-semibold">
                            ⚔️ Weapons
                        </button>
                        <button @click="selectedFilter = 'armor'" 
                                :class="selectedFilter === 'armor' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'"
                                class="px-6 py-3 font-semibold">
                            🛡️ Armor
                        </button>
                        <button @click="selectedFilter = 'vehicle'" 
                                :class="selectedFilter === 'vehicle' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'"
                                class="px-6 py-3 font-semibold">
                            🚗 Vehicles
                        </button>
                        <button @click="selectedFilter = 'consumable'" 
                                :class="selectedFilter === 'consumable' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'"
                                class="px-6 py-3 font-semibold">
                            💊 Consumables
                        </button>
                    </div>
                </div>

                <!-- Inventory Grid -->
                <div v-if="filteredInventory.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="item in filteredInventory" :key="item.id" 
                         class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                        <div :class="getRarityColor(item.item.rarity)" class="h-2"></div>
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">{{ item.item.name }}</h3>
                                    <p class="text-sm text-gray-600">{{ item.item.type }}</p>
                                </div>
                                <span :class="getRarityColor(item.item.rarity)" 
                                      class="px-2 py-1 text-xs font-semibold text-white rounded uppercase">
                                    {{ item.item.rarity }}
                                </span>
                            </div>
                            
                            <p class="text-gray-700 mb-4 text-sm">{{ item.item.description }}</p>
                            
                            <!-- Stats -->
                            <div v-if="item.item.stats && Object.keys(item.item.stats).length > 0" class="mb-4">
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="(value, stat) in item.item.stats" :key="stat" 
                                          class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">
                                        {{ stat }}: +{{ value }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Quantity -->
                            <div class="flex items-center justify-between mb-4">
                                <p class="text-sm text-gray-600">
                                    Quantity: <span class="font-bold text-gray-900">{{ item.quantity }}</span>
                                </p>
                                <p class="text-sm text-green-600 font-semibold">
                                    Sell: ${{ (item.item.sell_price * item.quantity).toLocaleString() }}
                                </p>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button v-if="!item.equipped && item.item.type !== 'consumable' && item.item.type !== 'misc'" 
                                        @click="equip(item.id)"
                                        class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                    Equip
                                </button>
                                <button v-if="item.item.type === 'consumable'" 
                                        @click="useItem(item.id)"
                                        class="flex-1 px-3 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                    Use
                                </button>
                                <button v-if="item.item.tradeable && !item.equipped" 
                                        @click="sell(item.id, item.quantity)"
                                        class="flex-1 px-3 py-2 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700">
                                    Sell
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-500 text-lg mb-4">No items in your inventory</p>
                    <a :href="route('shop.index')" 
                       class="inline-block px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Visit Shop
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
