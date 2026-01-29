<template>
    <Layout current-step="install">
        <div class="p-12">
            <h2 class="text-3xl font-bold text-white mb-6">Installing Your Game</h2>
            
            <p class="text-gray-300 mb-8">
                Please wait while we set everything up. This may take a minute...
            </p>

            <div v-if="!complete && !error" id="progress-container" class="space-y-4 mb-8">
                <div 
                    v-for="(step, index) in steps" 
                    :key="index"
                    class="flex items-center space-x-3 transition-opacity duration-300"
                    :class="{ 'opacity-50': index > currentStep }"
                >
                    <div class="spinner" v-if="index <= currentStep"></div>
                    <span class="text-gray-300">{{ step }}</span>
                </div>
            </div>

            <div v-if="error" class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6">
                <strong>Error:</strong> {{ error }}
            </div>

            <div v-if="complete" class="text-center">
                <div class="text-6xl mb-4">✓</div>
                <h3 class="text-2xl font-bold text-white mb-4">Installation Successful!</h3>
                <p class="text-gray-300 mb-6">Your game engine has been installed successfully.</p>
                <Link 
                    :href="route('installer.admin')"
                    class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200"
                >
                    Create Admin Account →
                </Link>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import Layout from './Layout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const steps = [
    'Running database migrations...',
    'Creating storage links...',
    'Finalizing installation...'
];

const currentStep = ref(0);
const complete = ref(false);
const error = ref(null);

onMounted(() => {
    // Animate steps
    const stepInterval = setInterval(() => {
        if (currentStep.value < steps.length - 1) {
            currentStep.value++;
        } else {
            clearInterval(stepInterval);
        }
    }, 1000);

    // Run installation
    axios.post(route('installer.install.process'))
        .then(response => {
            if (response.data.success) {
                setTimeout(() => {
                    complete.value = true;
                }, 2000);
            } else {
                error.value = response.data.message;
            }
        })
        .catch(err => {
            error.value = err.response?.data?.message || err.message;
        });
});
</script>

<style scoped>
.spinner {
    border: 3px solid rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    border-top: 3px solid #9333ea;
    width: 24px;
    height: 24px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
