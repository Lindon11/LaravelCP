<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: { type: Object, default: null },
    reports: { type: Array, default: () => [] },
    cost: { type: Number, default: 5000 },
    investigationTime: { type: Number, default: 5 },
});

const targetId = ref('');
const processing = ref(false);
const currentTime = ref(Date.now());

const formatMoney = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(val);

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const hireDetective = () => {
    if (processing.value) return;
    processing.value = true;
    router.post(route('detective.hire'), { target_id: targetId.value }, {
        preserveScroll: true,
        onFinish: () => { processing.value = false; targetId.value = ''; }
    });
};

let interval;
onMounted(() => {
    interval = setInterval(() => {
        currentTime.value = Date.now();
    }, 1000);
});
onUnmounted(() => clearInterval(interval));
</script>

<template>
    <AppLayout title="Detective">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">🕵️ Detective</h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="player">
                    <!-- Flash -->
                    <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ $page.props.flash.success }}</div>
                    <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $page.props.flash.error }}</div>

                    <!-- Hire Detective -->
                    <div class="bg-gradient-to-r from-gray-700 to-gray-800 text-white rounded-lg shadow-lg p-8 mb-6">
                        <div class="flex items-center mb-6">
                            <div class="text-6xl mr-6">🕵️</div>
                            <div class="flex-1">
                                <h3 class="text-3xl font-bold mb-2">Hire a Private Detective</h3>
                                <p class="text-gray-300">Find any player's location for {{ formatMoney(cost) }}</p>
                                <p class="text-gray-400 text-sm">Investigation takes {{ investigationTime }} minutes</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <input v-model="targetId" type="number" class="flex-1 px-4 py-3 rounded text-gray-900 text-lg" placeholder="Enter Player ID">
                            <button @click="hireDetective" :disabled="processing || !targetId" class="px-8 py-3 bg-yellow-500 hover:bg-yellow-600 rounded font-bold text-lg disabled:bg-gray-600">Hire Detective</button>
                        </div>
                    </div>

                    <!-- My Reports -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-2xl font-bold mb-4">My Detective Reports ({{ reports.length }})</h3>
                        
                        <div v-if="reports.length === 0" class="text-center text-gray-500 py-12">
                            <p class="text-xl mb-2">No detective reports</p>
                            <p class="text-sm">Hire a detective to track down other players</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="report in reports" :key="report.id" class="border rounded-lg p-4" :class="report.status === 'complete' ? 'border-green-300 bg-green-50' : 'border-yellow-300 bg-yellow-50'">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h4 class="text-xl font-bold">{{ report.target }} <span class="text-gray-500 text-sm">#{{ report.target_id }}</span></h4>
                                            <span v-if="report.status === 'complete'" class="px-3 py-1 bg-green-600 text-white text-xs font-bold rounded">COMPLETE</span>
                                            <span v-else class="px-3 py-1 bg-yellow-600 text-white text-xs font-bold rounded">INVESTIGATING</span>
                                        </div>
                                        
                                        <p v-if="report.status === 'complete' && report.location_info" class="text-gray-700 mb-2">
                                            📍 {{ report.location_info }}
                                        </p>
                                        
                                        <p v-if="report.status === 'investigating'" class="text-yellow-700 font-semibold">
                                            ⏱️ Report ready in: {{ formatTime(report.time_remaining) }}
                                        </p>
                                        
                                        <p class="text-gray-500 text-sm">Hired {{ report.hired_at }}</p>
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
