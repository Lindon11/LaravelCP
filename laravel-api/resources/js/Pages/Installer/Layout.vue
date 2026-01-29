<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 flex items-center justify-center p-4">
        <div class="max-w-4xl w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-block bg-white/10 backdrop-blur-lg rounded-2xl p-8 shadow-2xl">
                    <h1 class="text-5xl font-bold text-white mb-2">
                        🎮 OpenPBBG
                    </h1>
                    <p class="text-gray-300 text-lg">Game Engine Installer</p>
                </div>
            </div>

            <!-- Progress Steps -->
            <div v-if="!hideProgress" class="mb-8">
                <div class="flex items-center justify-between">
                    <div v-for="(step, index) in steps" :key="step.key" class="flex-1 flex items-center">
                        <div class="flex items-center justify-center">
                            <div class="relative">
                                <div 
                                    class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300"
                                    :class="getStepClass(index)"
                                >
                                    <span v-if="index < currentStepIndex">✓</span>
                                    <span v-else>{{ index + 1 }}</span>
                                </div>
                                <div class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 whitespace-nowrap">
                                    <span 
                                        class="text-xs transition-colors duration-300"
                                        :class="index === currentStepIndex ? 'text-purple-400 font-semibold' : 'text-gray-500'"
                                    >
                                        {{ step.label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div 
                            v-if="index < steps.length - 1" 
                            class="flex-1 h-1 mx-2 transition-colors duration-300"
                            :class="index < currentStepIndex ? 'bg-green-500' : 'bg-gray-700'"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Content Card -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl overflow-hidden mt-12">
                <slot />
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-400 text-sm space-y-1">
                <p>Powered by <span class="text-purple-400 font-semibold">Lindon</span></p>
                <p class="text-gray-500 text-xs">Inspired by Gangster Legends</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    currentStep: {
        type: String,
        required: true
    },
    hideProgress: {
        type: Boolean,
        default: false
    }
});

const page = usePage();

const steps = [
    { key: 'welcome', label: 'Welcome' },
    { key: 'requirements', label: 'Requirements' },
    { key: 'database', label: 'Database' },
    { key: 'settings', label: 'Settings' },
    { key: 'install', label: 'Install' },
    { key: 'admin', label: 'Admin' },
    { key: 'complete', label: 'Complete' }
];

const currentStepIndex = computed(() => {
    return steps.findIndex(s => s.key === props.currentStep);
});

const currentYear = computed(() => new Date().getFullYear());

const getStepClass = (index) => {
    if (index < currentStepIndex.value) {
        return 'bg-green-500 text-white';
    } else if (index === currentStepIndex.value) {
        return 'bg-purple-600 text-white';
    } else {
        return 'bg-gray-700 text-gray-400';
    }
};
</script>
