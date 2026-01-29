<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: {
        type: Object,
        default: null
    },
    taxRate: {
        type: Number,
        default: 15
    },
});

const depositAmount = ref('');
const withdrawAmount = ref('');
const transferRecipient = ref('');
const transferAmount = ref('');
const processing = ref(false);
const activeTab = ref('deposit');

// Format money
const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

// Calculate deposit after tax
const depositAfterTax = computed(() => {
    const amount = parseInt(depositAmount.value) || 0;
    const taxMultiplier = (100 - props.taxRate) / 100;
    return Math.floor(amount * taxMultiplier);
});

const depositTax = computed(() => {
    const amount = parseInt(depositAmount.value) || 0;
    return amount - depositAfterTax.value;
});

// Quick buttons
const setDepositAmount = (type) => {
    if (type === 'all') {
        depositAmount.value = props.player.cash.toString();
    } else if (type === 'half') {
        depositAmount.value = Math.floor(props.player.cash / 2).toString();
    }
};

const setWithdrawAmount = (type) => {
    if (type === 'all') {
        withdrawAmount.value = props.player.bank.toString();
    } else if (type === 'half') {
        withdrawAmount.value = Math.floor(props.player.bank / 2).toString();
    }
};

// Actions
const deposit = () => {
    if (processing.value) return;
    
    const amount = parseInt(depositAmount.value);
    if (!amount || amount <= 0) return;
    
    processing.value = true;
    
    router.post(route('bank.deposit'), { amount }, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            depositAmount.value = '';
        },
    });
};

const withdraw = () => {
    if (processing.value) return;
    
    const amount = parseInt(withdrawAmount.value);
    if (!amount || amount <= 0) return;
    
    processing.value = true;
    
    router.post(route('bank.withdraw'), { amount }, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            withdrawAmount.value = '';
        },
    });
};

const transfer = () => {
    if (processing.value) return;
    
    const amount = parseInt(transferAmount.value);
    if (!amount || amount <= 0 || !transferRecipient.value) return;
    
    processing.value = true;
    
    router.post(route('bank.transfer'), { 
        recipient: transferRecipient.value,
        amount 
    }, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            transferRecipient.value = '';
            transferAmount.value = '';
        },
    });
};
</script>

<template>
    <AppLayout title="Bank">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Bank
                </h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Debug Info (remove after testing) -->
                <div v-if="!player" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <p class="font-bold">Error: Player data not loaded</p>
                    <pre>{{ $page.props }}</pre>
                </div>

                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ $page.props.flash.error }}
                </div>

                <!-- Balance Cards -->
                <div v-if="player" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg shadow-lg border-2 border-green-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-green-600 font-semibold mb-1">Cash on Hand</p>
                                <p class="text-3xl font-bold text-green-800">{{ formatMoney(player.cash) }}</p>
                            </div>
                            <svg class="w-16 h-16 text-green-600 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg shadow-lg border-2 border-blue-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-blue-600 font-semibold mb-1">Bank Balance</p>
                                <p class="text-3xl font-bold text-blue-800">{{ formatMoney(player.bank) }}</p>
                            </div>
                            <svg class="w-16 h-16 text-blue-600 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Main Bank Interface -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button
                                @click="activeTab = 'deposit'"
                                :class="[
                                    'py-4 px-6 text-center border-b-2 font-medium text-sm',
                                    activeTab === 'deposit' 
                                        ? 'border-green-500 text-green-600' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Deposit
                            </button>
                            <button
                                @click="activeTab = 'withdraw'"
                                :class="[
                                    'py-4 px-6 text-center border-b-2 font-medium text-sm',
                                    activeTab === 'withdraw' 
                                        ? 'border-blue-500 text-blue-600' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Withdraw
                            </button>
                            <button
                                @click="activeTab = 'transfer'"
                                :class="[
                                    'py-4 px-6 text-center border-b-2 font-medium text-sm',
                                    activeTab === 'transfer' 
                                        ? 'border-purple-500 text-purple-600' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Transfer
                            </button>
                        </nav>
                    </div>

                    <!-- Deposit Tab -->
                    <div v-if="activeTab === 'deposit'" class="p-6">
                        <div class="mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 rounded">
                            <p class="font-semibold">⚠️ Bank Tax: {{ taxRate }}%</p>
                            <p class="text-sm">Your money launderer takes a {{ taxRate }}% cut when you deposit cash.</p>
                        </div>

                        <div class="max-w-md">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Amount to Deposit
                            </label>
                            <input
                                v-model="depositAmount"
                                type="number"
                                min="1"
                                :max="player.cash"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="Enter amount"
                            />

                            <div class="mt-3 flex space-x-2">
                                <button @click="setDepositAmount('half')" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm">
                                    Half
                                </button>
                                <button @click="setDepositAmount('all')" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm">
                                    All
                                </button>
                            </div>

                            <div v-if="depositAmount && parseInt(depositAmount) > 0" class="mt-4 p-3 bg-gray-50 rounded border">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">You send:</span>
                                    <span class="font-semibold">{{ formatMoney(parseInt(depositAmount)) }}</span>
                                </div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Tax ({{ taxRate }}%):</span>
                                    <span class="font-semibold text-red-600">-{{ formatMoney(depositTax) }}</span>
                                </div>
                                <div class="flex justify-between text-sm font-bold text-green-600 pt-2 border-t">
                                    <span>Deposited:</span>
                                    <span>{{ formatMoney(depositAfterTax) }}</span>
                                </div>
                            </div>

                            <button
                                @click="deposit"
                                :disabled="processing || !depositAmount || parseInt(depositAmount) <= 0 || parseInt(depositAmount) > player.cash"
                                class="mt-4 w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-semibold"
                            >
                                {{ processing ? 'Processing...' : 'Deposit Cash' }}
                            </button>
                        </div>
                    </div>

                    <!-- Withdraw Tab -->
                    <div v-if="activeTab === 'withdraw'" class="p-6">
                        <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-400 text-blue-700 rounded">
                            <p class="font-semibold">💰 No Fees</p>
                            <p class="text-sm">Withdrawals are tax-free! You get the full amount.</p>
                        </div>

                        <div class="max-w-md">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Amount to Withdraw
                            </label>
                            <input
                                v-model="withdrawAmount"
                                type="number"
                                min="1"
                                :max="player.bank"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter amount"
                            />

                            <div class="mt-3 flex space-x-2">
                                <button @click="setWithdrawAmount('half')" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm">
                                    Half
                                </button>
                                <button @click="setWithdrawAmount('all')" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm">
                                    All
                                </button>
                            </div>

                            <button
                                @click="withdraw"
                                :disabled="processing || !withdrawAmount || parseInt(withdrawAmount) <= 0 || parseInt(withdrawAmount) > player.bank"
                                class="mt-4 w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-semibold"
                            >
                                {{ processing ? 'Processing...' : 'Withdraw Cash' }}
                            </button>
                        </div>
                    </div>

                    <!-- Transfer Tab -->
                    <div v-if="activeTab === 'transfer'" class="p-6">
                        <div class="mb-4 p-4 bg-purple-50 border-l-4 border-purple-400 text-purple-700 rounded">
                            <p class="font-semibold">🔄 Send Money</p>
                            <p class="text-sm">Transfer cash directly to another player's pocket.</p>
                        </div>

                        <div class="max-w-md">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Recipient Username
                            </label>
                            <input
                                v-model="transferRecipient"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 mb-4"
                                placeholder="Enter username"
                            />

                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Amount
                            </label>
                            <input
                                v-model="transferAmount"
                                type="number"
                                min="1"
                                :max="player.cash"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="Enter amount"
                            />

                            <button
                                @click="transfer"
                                :disabled="processing || !transferRecipient || !transferAmount || parseInt(transferAmount) <= 0 || parseInt(transferAmount) > player.cash"
                                class="mt-4 w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed font-semibold"
                            >
                                {{ processing ? 'Processing...' : 'Send Money' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
