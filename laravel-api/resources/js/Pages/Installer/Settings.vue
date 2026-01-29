<template>
    <Layout current-step="settings">
        <div class="p-12">
            <h2 class="text-3xl font-bold text-white mb-6">Application Settings</h2>
            
            <p class="text-gray-300 mb-8">
                Configure your game's basic settings.
            </p>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-white font-semibold mb-2">Application Name</label>
                    <input 
                        v-model="form.app_name"
                        type="text"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                        required
                    >
                    <p class="text-gray-400 text-sm mt-1">This will be displayed across your game</p>
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">Application URL</label>
                    <input 
                        v-model="form.app_url"
                        type="url"
                        placeholder="http://your-domain.com"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                        required
                    >
                    <p class="text-gray-400 text-sm mt-1">The URL where your game will be accessible</p>
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">Environment</label>
                    <select 
                        v-model="form.app_env"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500"
                        required
                    >
                        <option value="production">Production (Live Game)</option>
                        <option value="local">Development (Testing)</option>
                    </select>
                    <p class="text-gray-400 text-sm mt-1">Choose Production for live games</p>
                </div>

                <div class="flex justify-between pt-6 border-t border-white/10">
                    <Link 
                        :href="route('installer.database')"
                        class="text-gray-400 hover:text-white transition duration-200"
                    >
                        ← Back
                    </Link>
                    
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200 disabled:opacity-50"
                    >
                        Continue →
                    </button>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import Layout from './Layout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    app_name: 'Gangster Legends',
    app_url: window.location.origin,
    app_env: 'production'
});

const submit = () => {
    form.post(route('installer.settings.store'));
};
</script>
