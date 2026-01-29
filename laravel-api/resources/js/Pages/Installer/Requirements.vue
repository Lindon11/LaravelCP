<template>
    <Layout current-step="requirements">
        <div class="p-12">
            <h2 class="text-3xl font-bold text-white mb-6">System Requirements Check</h2>
            
            <p class="text-gray-300 mb-8">
                We're checking if your server meets all the requirements to run Gangster Legends.
            </p>

            <!-- PHP Version -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-white mb-4">PHP Version</h3>
                <div class="bg-white/5 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-300">
                            PHP {{ requirements.php.version }}
                            <span class="text-sm text-gray-500">(Required: {{ requirements.php.required }}+)</span>
                        </span>
                        <span class="text-2xl">
                            <span v-if="requirements.php.status" class="text-green-400">✓</span>
                            <span v-else class="text-red-400">✗</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- PHP Extensions -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-white mb-4">PHP Extensions</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div 
                        v-for="(enabled, extension) in requirements.extensions" 
                        :key="extension"
                        class="bg-white/5 rounded-lg p-4"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-gray-300">{{ extension }}</span>
                            <span class="text-2xl">
                                <span v-if="enabled" class="text-green-400">✓</span>
                                <span v-else class="text-red-400">✗</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Directory Permissions -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-white mb-4">Directory Permissions</h3>
                <div class="space-y-3">
                    <div 
                        v-for="(writable, directory) in permissions" 
                        :key="directory"
                        class="bg-white/5 rounded-lg p-4"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-gray-300">{{ directory }}</span>
                            <span class="text-2xl">
                                <span v-if="writable" class="text-green-400">✓</span>
                                <span v-else class="text-red-400">✗</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between pt-6 border-t border-white/10">
                <Link 
                    :href="route('installer.index')"
                    class="text-gray-400 hover:text-white transition duration-200"
                >
                    ← Back
                </Link>
                
                <Link 
                    v-if="allRequirementsMet"
                    :href="route('installer.database')"
                    class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200"
                >
                    Continue →
                </Link>
                <div v-else class="text-red-400">
                    Please fix the issues above before continuing
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import Layout from './Layout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    requirements: Object,
    permissions: Object,
    allRequirementsMet: Boolean
});
</script>
