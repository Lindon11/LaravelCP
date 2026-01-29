<template>
    <Layout current-step="admin">
        <div class="p-12">
            <h2 class="text-3xl font-bold text-white mb-6">Create Admin Account</h2>
            
            <p class="text-gray-300 mb-8">
                Create your administrator account to manage the game.
            </p>

            <div v-if="errors && Object.keys(errors).length > 0" class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-white font-semibold mb-2">Username</label>
                    <input 
                        v-model="form.username"
                        type="text"
                        placeholder="admin"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                        required
                    >
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">Email Address</label>
                    <input 
                        v-model="form.email"
                        type="email"
                        placeholder="admin@example.com"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                        required
                    >
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">Password</label>
                    <input 
                        v-model="form.password"
                        type="password"
                        placeholder="Minimum 8 characters"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                        required
                    >
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">Confirm Password</label>
                    <input 
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="Re-enter your password"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                        required
                    >
                </div>

                <div class="flex justify-end pt-6 border-t border-white/10">
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Creating Account...</span>
                        <span v-else>Create Account & Finish →</span>
                    </button>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import Layout from './Layout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    errors: Object
});

const form = useForm({
    username: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const submit = () => {
    form.post(route('installer.admin.store'));
};
</script>
