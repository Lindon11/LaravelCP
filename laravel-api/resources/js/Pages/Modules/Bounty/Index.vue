<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: { type: Object, default: null },
    bounties: { type: Array, default: () => [] },
    myBounties: { type: Object, default: null },
    minAmount: { type: Number, default: 10000 },
    feePercentage: { type: Number, default: 10 },
});

const showPlaceForm = ref(false);
const targetId = ref('');
const amount = ref(props.minAmount);
const reason = ref('');
const processing = ref(false);

const formatMoney = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(val);

const totalCost = computed(() => {
    const bounty = parseFloat(amount.value) || 0;
    const fee = bounty * (props.feePercentage / 100);
    return bounty + fee;
});

const placeBounty = () => {
    if (processing.value) return;
    processing.value = true;
    router.post(route('bounties.place'), { target_id: targetId.value, amount: amount.value, reason: reason.value }, {
        preserveScroll: true,
        onFinish: () => { processing.value = false; showPlaceForm.value = false; }
    });
};
</script>

<template>
    <AppLayout title="Bounties">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">💰 Bounties</h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="player">
                    <!-- Flash -->
                    <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ $page.props.flash.success }}</div>
                    <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $page.props.flash.error }}</div>

                    <!-- My Bounty Status -->
                    <div v-if="myBounties?.on_me > 0" class="bg-red-600 text-white rounded-lg shadow-lg p-6 mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold">⚠️ Warning: Bounty on Your Head</h3>
                                <p class="text-red-200">There's a bounty placed on you. Watch your back!</p>
                            </div>
                            <div class="text-right">
                                <p class="text-4xl font-bold">{{ formatMoney(myBounties.on_me) }}</p>
                                <p class="text-red-200">Total Bounty</p>
                            </div>
                        </div>
                    </div>

                    <!-- Place Bounty -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <button v-if="!showPlaceForm" @click="showPlaceForm = true" class="w-full py-4 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold text-lg">Place a Bounty</button>
                        <div v-else>
                            <h3 class="text-2xl font-bold mb-4">Place Bounty on Player</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-semibold mb-2">Target Player ID</label>
                                    <input v-model="targetId" type="number" class="w-full px-4 py-2 border rounded" placeholder="Enter player ID">
                                </div>
                                <div>
                                    <label class="block font-semibold mb-2">Bounty Amount (Min: {{ formatMoney(minAmount) }})</label>
                                    <input v-model.number="amount" type="number" :min="minAmount" class="w-full px-4 py-2 border rounded">
                                    <p class="text-sm text-gray-600 mt-1">Fee: {{ formatMoney(amount * (feePercentage / 100)) }} ({{ feePercentage }}%) | Total Cost: {{ formatMoney(totalCost) }}</p>
                                </div>
                                <div>
                                    <label class="block font-semibold mb-2">Reason (Optional)</label>
                                    <input v-model="reason" type="text" maxlength="255" class="w-full px-4 py-2 border rounded" placeholder="They disrespected me...">
                                </div>
                                <div class="flex gap-3">
                                    <button @click="placeBounty" :disabled="processing || !targetId || amount < minAmount" class="flex-1 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold">Place Bounty</button>
                                    <button @click="showPlaceForm = false" class="px-6 py-3 bg-gray-300 rounded-lg hover:bg-gray-400 font-bold">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Bounties -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h3 class="text-2xl font-bold mb-4">Active Bounties ({{ bounties.length }})</h3>
                        <div v-if="bounties.length === 0" class="text-center text-gray-500 py-8">No active bounties</div>
                        <div v-else class="space-y-3">
                            <div v-for="bounty in bounties" :key="bounty.id" class="border border-red-200 rounded-lg p-4 hover:bg-red-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-xl font-bold text-red-600">{{ bounty.target }} <span class="text-gray-500 text-sm">#{{ bounty.target_id }}</span></h4>
                                        <p v-if="bounty.reason" class="text-gray-600 text-sm italic">"{{ bounty.reason }}"</p>
                                        <p class="text-gray-500 text-xs mt-1">Placed {{ bounty.placed_at }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-3xl font-bold text-green-600">{{ formatMoney(bounty.amount) }}</p>
                                        <p class="text-gray-500 text-sm">Reward</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Placed Bounties -->
                    <div v-if="myBounties?.placed.length > 0" class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-2xl font-bold mb-4">My Placed Bounties</h3>
                        <div class="space-y-3">
                            <div v-for="(b, i) in myBounties.placed" :key="i" class="border rounded-lg p-4">
                                <div class="flex justify-between">
                                    <div>
                                        <p class="font-bold">{{ b.target }}</p>
                                        <p class="text-sm text-gray-500">{{ b.placed_at }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-green-600">{{ formatMoney(b.amount) }}</p>
                                        <p class="text-sm" :class="b.status === 'active' ? 'text-yellow-600' : b.status === 'claimed' ? 'text-red-600' : 'text-gray-500'">{{ b.status.toUpperCase() }}</p>
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
