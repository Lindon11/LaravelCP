<template>
    <Layout current-step="database">
        <div class="p-12">
            <h2 class="text-3xl font-bold text-white mb-6">Database Configuration</h2>
            
            <p class="text-gray-300 mb-8">
                Enter your database details. Make sure the database already exists.
            </p>

            <div v-if="errors && Object.keys(errors).length > 0" class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-white font-semibold mb-2">Database Host</label>
                        <input 
                            v-model="form.db_host"
                            type="text"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-white font-semibold mb-2">Database Port</label>
                        <input 
                            v-model="form.db_port"
                            type="number"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                            required
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">Database Name</label>
                    <input 
                        v-model="form.db_name"
                        type="text"
                        placeholder="gangster_legends"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                        required
                    >
                    <p class="text-gray-400 text-sm mt-1">The database must already exist</p>
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">Database Username</label>
                    <input 
                        v-model="form.db_username"
                        type="text"
                        placeholder="root"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                        required
                    >
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">Database Password</label>
                    <input 
                        v-model="form.db_password"
                        type="password"
                        placeholder="Leave empty if no password"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                    >
                </div>

                <div class="flex justify-between pt-6 border-t border-white/10">
                    <Link 
                        :href="route('installer.requirements')"
                        class="text-gray-400 hover:text-white transition duration-200"
                    >
                        ← Back
                    </Link>
                    
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Testing Connection...</span>
                        <span v-else>Test Connection & Continue →</span>
                    </button>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import Layout from './Layout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    errors: Object
});

const form = useForm({
    db_host: 'localhost',
    db_port: 3306,
    db_name: '',
    db_username: '',
    db_password: ''
});

const submit = () => {
    form.post(route('installer.database.store'));
};
</script>
