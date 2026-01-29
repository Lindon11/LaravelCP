<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    availableTargets: Array,
    combatHistory: Array,
    player: Object,
});

const selectedTarget = ref(null);

const attackForm = useForm({
    defender_id: null,
});

const attack = (target) => {
    if (!confirm(`Attack ${target.username}?`)) return;

    attackForm.defender_id = target.id;
    attackForm.post(route('combat.attack'), {
        preserveScroll: true,
        onSuccess: () => {
            selectedTarget.value = null;
        },
    });
};

const getHealthColor = (health, maxHealth) => {
    const percentage = (health / maxHealth) * 100;
    if (percentage > 60) return 'bg-green-500';
    if (percentage > 30) return 'bg-yellow-500';
    return 'bg-red-500';
};

const getHealthPercentage = (health, maxHealth) => {
    return Math.round((health / maxHealth) * 100);
};

const getOutcomeColor = (outcome) => {
    if (outcome === 'killed') return 'text-red-600 font-bold';
    if (outcome === 'success') return 'text-green-600';
    return 'text-yellow-600';
};

const getOutcomeText = (outcome) => {
    if (outcome === 'killed') return 'KILLED';
    if (outcome === 'success') return 'HIT';
    return 'MISS';
};
</script>

<template>
    <AppLayout>
        <Head title="Combat" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">⚔️ Combat</h1>
                    <a :href="route('dashboard')" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                        ← Dashboard
                    </a>
                </div>

                <!-- Player Stats -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4">Your Combat Stats</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Health</p>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-4">
                                    <div :class="getHealthColor(player.health, player.max_health)" 
                                         class="h-4 rounded-full transition-all"
                                         :style="`width: ${getHealthPercentage(player.health, player.max_health)}%`">
                                    </div>
                                </div>
                                <span class="text-sm font-semibold">{{ player.health }}/{{ player.max_health }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Weapon</p>
                            <p class="text-lg font-bold">
                                {{ player.equipped_items?.find(i => i.slot === 'weapon')?.item?.name || 'Fists' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Armor</p>
                            <p class="text-lg font-bold">
                                {{ player.equipped_items?.find(i => i.slot === 'armor')?.item?.name || 'None' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Level</p>
                            <p class="text-2xl font-bold text-purple-600">{{ player.level }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Available Targets -->
                    <div>
                        <h2 class="text-2xl font-bold mb-4">🎯 Available Targets</h2>
                        
                        <div v-if="player.health <= 0" class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded">
                            <p class="text-red-800 font-bold">☠️ You are dead!</p>
                            <p class="text-red-700 text-sm">Visit the hospital to recover.</p>
                        </div>

                        <div v-if="availableTargets.length === 0" class="bg-gray-50 rounded-lg p-8 text-center">
                            <p class="text-gray-600">No targets available in your location</p>
                            <p class="text-sm text-gray-500 mt-2">Travel to a different city or wait for players to come online</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="target in availableTargets" :key="target.id"
                                 class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition-shadow">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-lg font-bold">{{ target.username }}</h3>
                                            <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">
                                                Lvl {{ target.level }}
                                            </span>
                                            <span class="text-sm text-gray-600">{{ target.rank }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-600">HP:</span>
                                        <div class="flex-1 bg-gray-200 rounded-full h-3">
                                            <div :class="getHealthColor(target.health, target.max_health)" 
                                                 class="h-3 rounded-full"
                                                 :style="`width: ${getHealthPercentage(target.health, target.max_health)}%`">
                                            </div>
                                        </div>
                                        <span class="text-xs font-semibold">{{ target.health }}/{{ target.max_health }}</span>
                                    </div>
                                </div>

                                <button @click="attack(target)"
                                        :disabled="player.health <= 0 || attackForm.processing"
                                        class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    ⚔️ Attack
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Combat History -->
                    <div>
                        <h2 class="text-2xl font-bold mb-4">📜 Combat History</h2>
                        
                        <div v-if="combatHistory.length === 0" class="bg-gray-50 rounded-lg p-8 text-center">
                            <p class="text-gray-600">No combat history yet</p>
                            <p class="text-sm text-gray-500 mt-2">Attack someone to see your combat logs here</p>
                        </div>

                        <div v-else class="space-y-3 max-h-[600px] overflow-y-auto">
                            <div v-for="log in combatHistory" :key="log.id"
                                 class="bg-white rounded-lg shadow p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span :class="getOutcomeColor(log.outcome)" class="font-bold">
                                                {{ getOutcomeText(log.outcome) }}
                                            </span>
                                            <span class="text-gray-400">•</span>
                                            <span class="text-sm text-gray-600">
                                                {{ new Date(log.created_at).toLocaleString() }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-sm mt-1" v-if="log.attacker_id === player.id">
                                            You attacked <strong>{{ log.defender.username }}</strong>
                                        </p>
                                        <p class="text-sm mt-1" v-else>
                                            <strong>{{ log.attacker.username }}</strong> attacked you
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3 text-xs mt-3">
                                    <div>
                                        <p class="text-gray-600">Damage</p>
                                        <p class="font-bold text-red-600">{{ log.damage_dealt }}</p>
                                    </div>
                                    <div v-if="log.cash_stolen > 0">
                                        <p class="text-gray-600">Cash Stolen</p>
                                        <p class="font-bold text-green-600">${{ log.cash_stolen.toLocaleString() }}</p>
                                    </div>
                                    <div v-if="log.respect_gained > 0">
                                        <p class="text-gray-600">Respect</p>
                                        <p class="font-bold text-purple-600">+{{ log.respect_gained }}</p>
                                    </div>
                                    <div v-if="log.weapon">
                                        <p class="text-gray-600">Weapon</p>
                                        <p class="font-bold">{{ log.weapon.name }}</p>
                                    </div>
                                </div>

                                <div v-if="log.outcome === 'killed'" class="mt-3 pt-3 border-t border-red-200">
                                    <p class="text-red-600 font-bold text-sm">☠️ {{ log.defender.username }} was killed!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Combat Tips -->
                <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <h3 class="text-blue-800 font-semibold mb-2">💡 Combat Tips</h3>
                    <ul class="text-blue-700 text-sm space-y-1">
                        <li>• Equip better weapons and armor from the Shop to increase your damage and defense</li>
                        <li>• Higher level players have better stats but also give more respect when defeated</li>
                        <li>• If you miss your attack, the defender will counter-attack you</li>
                        <li>• Killing a player steals 10% of their cash and 5% of their respect</li>
                        <li>• Keep your health high by visiting the Hospital after battles</li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
