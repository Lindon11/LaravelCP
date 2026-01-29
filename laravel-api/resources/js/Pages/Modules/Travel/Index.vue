<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: { type: Object, default: null },
    locations: { type: Array, default: () => [] },
    currentLocation: { type: Object, default: null },
    playersHere: { type: Array, default: () => [] },
});

const processing = ref(false);

const formatMoney = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(val);

const travel = (locationId) => {
    if (processing.value) return;
    processing.value = true;
    router.post(route('travel.go', locationId), {}, { preserveScroll: true, onFinish: () => processing.value = false });
};
</script>

<template>
    <AppLayout title="Travel">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">✈️ Travel</h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="player">
                    <!-- Flash -->
                    <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ $page.props.flash.success }}</div>
                    <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $page.props.flash.error }}</div>

                    <!-- Current Location -->
                    <div v-if="currentLocation" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg shadow-lg p-8 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-blue-200 text-sm">Currently in</p>
                                <h3 class="text-4xl font-bold">{{ currentLocation.name }}</h3>
                                <p class="text-blue-200 mt-2">{{ currentLocation.description }}</p>
                            </div>
                            <div class="text-6xl">📍</div>
                        </div>
                        <div v-if="playersHere.length > 0" class="mt-4 pt-4 border-t border-blue-400">
                            <p class="text-blue-200 text-sm mb-2">Players Online Here ({{ playersHere.length }})</p>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="p in playersHere.slice(0, 10)" :key="p.id" class="px-3 py-1 bg-blue-800 rounded-full text-sm">
                                    {{ p.username }} ({{ p.level }})
                                </span>
                                <span v-if="playersHere.length > 10" class="px-3 py-1 bg-blue-800 rounded-full text-sm">
                                    +{{ playersHere.length - 10 }} more
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Available Locations -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-2xl font-bold mb-6">Available Destinations</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="loc in locations" :key="loc.id" 
                                 class="border rounded-lg p-6 hover:shadow-md transition"
                                 :class="currentLocation?.id === loc.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h4 class="text-2xl font-bold">{{ loc.name }}</h4>
                                            <span v-if="currentLocation?.id === loc.id" class="px-2 py-1 bg-blue-600 text-white text-xs font-bold rounded">CURRENT</span>
                                        </div>
                                        <p class="text-gray-600 mb-3">{{ loc.description }}</p>
                                        <div class="flex items-center gap-4 text-sm">
                                            <span class="text-green-600 font-semibold">{{ formatMoney(loc.travel_cost) }}</span>
                                            <span class="text-gray-500">Level {{ loc.required_level }}+</span>
                                        </div>
                                    </div>
                                    <div class="text-4xl ml-4">🌆</div>
                                </div>
                                <button 
                                    v-if="currentLocation?.id !== loc.id"
                                    @click="travel(loc.id)" 
                                    :disabled="processing || player.level < loc.required_level || player.cash < loc.travel_cost" 
                                    class="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold disabled:bg-gray-400">
                                    Travel Here
                                </button>
                                <div v-else class="w-full py-2 bg-blue-100 text-blue-800 rounded text-center font-semibold">
                                    You are here
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
