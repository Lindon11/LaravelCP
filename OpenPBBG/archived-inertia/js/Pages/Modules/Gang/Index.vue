<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: { type: Object, default: null },
    myGang: { type: Object, default: null },
    gangs: { type: Array, default: () => [] },
    creationCost: { type: Number, default: 1000000 },
});

const showCreateForm = ref(false);
const gangName = ref('');
const gangTag = ref('');
const depositAmount = ref(0);
const withdrawAmount = ref(0);
const processing = ref(false);

const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(amount);
};

const isLeader = computed(() => props.myGang && props.myGang.leader_id === props.player?.id);

const createGang = () => {
    if (processing.value) return;
    processing.value = true;
    router.post(route('gangs.create'), { name: gangName.value, tag: gangTag.value }, {
        preserveScroll: true,
        onFinish: () => { processing.value = false; showCreateForm.value = false; }
    });
};

const leaveGang = () => {
    if (processing.value || !confirm('Are you sure you want to leave your gang?')) return;
    processing.value = true;
    router.post(route('gangs.leave'), {}, { preserveScroll: true, onFinish: () => processing.value = false });
};

const kickMember = (playerId) => {
    if (processing.value || !confirm('Kick this member?')) return;
    processing.value = true;
    router.post(route('gangs.kick', playerId), {}, { preserveScroll: true, onFinish: () => processing.value = false });
};

const deposit = () => {
    if (processing.value || depositAmount.value <= 0) return;
    processing.value = true;
    router.post(route('gangs.deposit'), { amount: depositAmount.value }, {
        preserveScroll: true,
        onFinish: () => { processing.value = false; depositAmount.value = 0; }
    });
};

const withdraw = () => {
    if (processing.value || withdrawAmount.value <= 0) return;
    processing.value = true;
    router.post(route('gangs.withdraw'), { amount: withdrawAmount.value }, {
        preserveScroll: true,
        onFinish: () => { processing.value = false; withdrawAmount.value = 0; }
    });
};
</script>

<template>
    <AppLayout title="Gangs">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">👥 Gangs</h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="player">
                    <!-- Flash Messages -->
                    <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ $page.props.flash.success }}</div>
                    <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $page.props.flash.error }}</div>

                    <!-- My Gang -->
                    <div v-if="myGang" class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-lg shadow-lg p-8 mb-6 text-white">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-3xl font-bold">{{ myGang.name }} [{{ myGang.tag }}]</h3>
                                <p class="text-purple-200">{{ myGang.description || 'No description' }}</p>
                            </div>
                            <button @click="leaveGang" :disabled="processing" class="px-4 py-2 bg-red-500 hover:bg-red-600 rounded font-semibold">Leave Gang</button>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="bg-white/10 p-4 rounded"><p class="text-sm text-purple-200">Members</p><p class="text-2xl font-bold">{{ myGang.member_count || 0 }}/{{ myGang.max_members }}</p></div>
                            <div class="bg-white/10 p-4 rounded"><p class="text-sm text-purple-200">Respect</p><p class="text-2xl font-bold">{{ myGang.respect }}</p></div>
                            <div class="bg-white/10 p-4 rounded"><p class="text-sm text-purple-200">Gang Bank</p><p class="text-2xl font-bold">{{ formatMoney(myGang.bank) }}</p></div>
                        </div>

                        <!-- Gang Bank Actions -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white/10 p-4 rounded">
                                <label class="block text-sm font-semibold mb-2">Deposit to Gang</label>
                                <div class="flex gap-2">
                                    <input v-model.number="depositAmount" type="number" min="1" class="flex-1 px-3 py-2 rounded text-gray-900" placeholder="Amount">
                                    <button @click="deposit" :disabled="processing || depositAmount <= 0" class="px-4 py-2 bg-green-500 hover:bg-green-600 rounded font-semibold">Deposit</button>
                                </div>
                            </div>
                            <div v-if="isLeader" class="bg-white/10 p-4 rounded">
                                <label class="block text-sm font-semibold mb-2">Withdraw (Leader Only)</label>
                                <div class="flex gap-2">
                                    <input v-model.number="withdrawAmount" type="number" min="1" class="flex-1 px-3 py-2 rounded text-gray-900" placeholder="Amount">
                                    <button @click="withdraw" :disabled="processing || withdrawAmount <= 0" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 rounded font-semibold">Withdraw</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Create Gang -->
                    <div v-else class="bg-white rounded-lg shadow-lg p-8 mb-6">
                        <button v-if="!showCreateForm" @click="showCreateForm = true" class="w-full py-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-bold text-lg">Create Gang ({{ formatMoney(creationCost) }})</button>
                        <div v-else>
                            <h3 class="text-2xl font-bold mb-4">Create Your Gang</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-semibold mb-2">Gang Name</label>
                                    <input v-model="gangName" type="text" maxlength="50" class="w-full px-4 py-2 border rounded" placeholder="The Syndicate">
                                </div>
                                <div>
                                    <label class="block font-semibold mb-2">Gang Tag (Max 5 chars)</label>
                                    <input v-model="gangTag" type="text" maxlength="5" class="w-full px-4 py-2 border rounded uppercase" placeholder="SYN">
                                </div>
                                <div class="flex gap-3">
                                    <button @click="createGang" :disabled="processing || !gangName || !gangTag" class="flex-1 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-bold">Create</button>
                                    <button @click="showCreateForm = false" class="px-6 py-3 bg-gray-300 rounded-lg hover:bg-gray-400 font-bold">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- All Gangs -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-2xl font-bold mb-4">All Gangs ({{ gangs.length }})</h3>
                        <div class="space-y-3">
                            <div v-for="gang in gangs" :key="gang.id" class="border rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-xl font-bold">{{ gang.name }} <span class="text-purple-600">[{{ gang.tag }}]</span></h4>
                                        <p class="text-gray-600 text-sm">Leader: {{ gang.leader }} | Members: {{ gang.members }}/{{ gang.max_members }} | Respect: {{ gang.respect }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-green-600 font-bold">{{ formatMoney(gang.bank) }}</p>
                                        <p class="text-gray-500 text-sm">Gang Bank</p>
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
