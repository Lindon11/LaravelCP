<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    availableRaces: Array,
    raceHistory: Array,
    vehicles: Array,
    player: Object,
});

const showCreateRace = ref(false);

const createRaceForm = useForm({
    name: 'Street Race',
    entry_fee: 1000,
    min_participants: 2,
    max_participants: 8,
});

const joinRaceForm = useForm({
    vehicle_id: null,
    bet_amount: 0,
});

const createRace = () => {
    createRaceForm.post(route('racing.create'), {
        preserveScroll: true,
        onSuccess: () => {
            createRaceForm.reset();
            showCreateRace.value = false;
        },
    });
};

const joinRace = (raceId) => {
    joinRaceForm.post(route('racing.join', raceId), {
        preserveScroll: true,
        onSuccess: () => joinRaceForm.reset(),
    });
};

const leaveRace = (raceId) => {
    if (confirm('Leave race? (10% penalty)')) {
        router.post(route('racing.leave', raceId), {}, {
            preserveScroll: true,
        });
    }
};

const startRace = (raceId) => {
    if (confirm('Start this race?')) {
        router.post(route('racing.start', raceId), {}, {
            preserveScroll: true,
        });
    }
};

const isInRace = (race) => {
    return race.participants.some(p => p.player_id === props.player.id);
};

const getStatusColor = (status) => {
    return {
        'waiting': 'bg-yellow-500',
        'racing': 'bg-blue-500',
        'finished': 'bg-gray-500'
    }[status] || 'bg-gray-500';
};

const getPositionMedal = (position) => {
    return {
        1: '🥇',
        2: '🥈',
        3: '🥉'
    }[position] || position;
};
</script>

<template>
    <AppLayout>
        <Head title="Racing" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">🏁 Street Racing</h1>
                    <div class="flex gap-4">
                        <button @click="showCreateRace = !showCreateRace" 
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            + Create Race
                        </button>
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
                            <p class="text-sm text-gray-600">Speed Stat</p>
                            <p class="text-2xl font-bold text-blue-600">{{ player.speed || 10 }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Your Vehicles</p>
                            <p class="text-2xl font-bold">{{ vehicles.length }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Races Today</p>
                            <p class="text-2xl font-bold">{{ raceHistory.length }}</p>
                        </div>
                    </div>
                </div>

                <!-- Create Race Form -->
                <div v-if="showCreateRace" class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-4">Create New Race</h2>
                    <form @submit.prevent="createRace" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Race Name</label>
                            <input v-model="createRaceForm.name" type="text" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Entry Fee</label>
                                <input v-model.number="createRaceForm.entry_fee" type="number" min="100" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Min Players</label>
                                <input v-model.number="createRaceForm.min_participants" type="number" min="2" max="8" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Players</label>
                                <input v-model.number="createRaceForm.max_participants" type="number" min="2" max="8" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <button type="submit" :disabled="createRaceForm.processing"
                                    class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50">
                                Create Race
                            </button>
                            <button type="button" @click="showCreateRace = false"
                                    class="px-6 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Available Races -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">Open Races</h2>
                    <div v-if="availableRaces.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-for="race in availableRaces" :key="race.id" 
                             class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">{{ race.name }}</h3>
                                    <p class="text-sm text-gray-600">{{ race.location.name }}</p>
                                </div>
                                <span :class="getStatusColor(race.status)" 
                                      class="px-3 py-1 text-xs font-semibold text-white rounded uppercase">
                                    {{ race.status }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="bg-gray-50 p-3 rounded">
                                    <p class="text-xs text-gray-600">Entry Fee</p>
                                    <p class="text-lg font-bold text-green-600">${{ race.entry_fee.toLocaleString() }}</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded">
                                    <p class="text-xs text-gray-600">Prize Pool</p>
                                    <p class="text-lg font-bold text-purple-600">${{ race.prize_pool.toLocaleString() }}</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2">
                                    Racers: {{ race.participants.length }}/{{ race.max_participants }}
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="participant in race.participants" :key="participant.id"
                                          class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">
                                        {{ participant.player.username }}
                                        <span v-if="participant.vehicle">🚗</span>
                                    </span>
                                </div>
                            </div>

                            <div v-if="!isInRace(race)" class="space-y-3">
                                <div v-if="vehicles.length > 0">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Vehicle (Optional)</label>
                                    <select v-model="joinRaceForm.vehicle_id" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                        <option :value="null">No Vehicle</option>
                                        <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
                                            {{ vehicle.item.name }} (+{{ vehicle.item.stats.speed }} speed)
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Bet</label>
                                    <input v-model.number="joinRaceForm.bet_amount" type="number" min="0" step="100"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                </div>
                                <button @click="joinRace(race.id)" :disabled="player.cash < race.entry_fee"
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Join Race (${{ race.entry_fee.toLocaleString() }})
                                </button>
                            </div>

                            <div v-else class="flex gap-2">
                                <button @click="leaveRace(race.id)"
                                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    Leave (90% refund)
                                </button>
                                <button v-if="race.participants.length >= race.min_participants" 
                                        @click="startRace(race.id)"
                                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    Start Race
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="bg-white rounded-lg shadow p-12 text-center">
                        <p class="text-gray-500 text-lg mb-4">No races available</p>
                        <button @click="showCreateRace = true"
                                class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Create a Race
                        </button>
                    </div>
                </div>

                <!-- Race History -->
                <div v-if="raceHistory.length > 0">
                    <h2 class="text-2xl font-bold mb-4">Recent Races</h2>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Race</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Winnings</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="history in raceHistory" :key="history.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ history.race.location.name }}</div>
                                        <div class="text-sm text-gray-500">{{ new Date(history.created_at).toLocaleDateString() }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-2xl">{{ getPositionMedal(history.position) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ (history.finish_time / 1000).toFixed(2) }}s
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-lg font-bold" 
                                              :class="history.winnings > 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ history.winnings > 0 ? '+' : '' }}${{ history.winnings.toLocaleString() }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
