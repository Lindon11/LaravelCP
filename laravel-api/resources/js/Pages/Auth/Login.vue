<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in - Gangster Legends" />

    <div class="min-h-screen flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-red-900 via-gray-900 to-black relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIj48cGF0aCBkPSJNMzYgMzRjMC0yLjIxLTEuNzktNC00LTRzLTQgMS43OS00IDQgMS43OSA0IDQgNCA0LTEuNzkgNC00em0wLTEwYzAtMi4yMS0xLjc5LTQtNC00cy00IDEuNzktNCA0IDEuNzkgNCA0IDQgNC0xLjc5IDQtNHptMC0xMGMwLTIuMjEtMS43OS00LTQtNHMtNCAxLjc5LTQgNCAxLjc5IDQgNCA0IDQtMS43OSA0LTR6TTI2IDM0YzAtMi4yMS0xLjc5LTQtNC00cy00IDEuNzktNCA0IDEuNzkgNCA0IDQgNC0xLjc5IDQtNHptMC0xMGMwLTIuMjEtMS43OS00LTQtNHMtNCAxLjc5LTQgNCAxLjc5IDQgNCA0IDQtMS43OSA0LTR6bTAtMTBjMC0yLjIxLTEuNzktNC00LTRzLTQgMS43OS00IDQgMS43OSA0IDQgNCA0LTEuNzkgNC00ek00NiAzNGMwLTIuMjEtMS43OS00LTQtNHMtNCAxLjc5LTQgNCAxLjc5IDQgNCA0IDQtMS43OSA0LTR6bTAtMTBjMC0yLjIxLTEuNzktNC00LTRzLTQgMS43OS00IDQgMS43OSA0IDQgNCA0LTEuNzkgNC00em0wLTEwYzAtMi4yMS0xLjc5LTQtNC00cy00IDEuNzktNCA0IDEuNzkgNCA0IDQgNC0xLjc5IDQtNHoiLz48L2c+PC9nPjwvc3ZnPg==')] bg-repeat"></div>
            </div>
            <div class="relative z-10 flex flex-col justify-center px-12 text-white">
                <h1 class="text-5xl font-bold mb-4">🎲 Gangster Legends</h1>
                <p class="text-2xl mb-8 text-gray-300">Build Your Criminal Empire</p>
                <div class="space-y-4 text-lg">
                    <div class="flex items-start space-x-3">
                        <span class="text-red-500 text-2xl">💰</span>
                        <div>
                            <h3 class="font-semibold">Commit Crimes</h3>
                            <p class="text-gray-400">Earn cash, gain experience, and climb the ranks</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="text-red-500 text-2xl">⚔️</span>
                        <div>
                            <h3 class="font-semibold">Battle Players</h3>
                            <p class="text-gray-400">Attack rivals and defend your turf</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="text-red-500 text-2xl">👥</span>
                        <div>
                            <h3 class="font-semibold">Form Gangs</h3>
                            <p class="text-gray-400">Team up with others to dominate the streets</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="text-red-500 text-2xl">🏆</span>
                        <div>
                            <h3 class="font-semibold">Rule the City</h3>
                            <p class="text-gray-400">Rise from Thug to Godfather</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="flex-1 flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white">🎲 Gangster Legends</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Build Your Criminal Empire</p>
                </div>

                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                        Welcome Back
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Sign in to continue your criminal empire
                    </p>
                </div>

                <div v-if="status" class="rounded-md bg-green-50 dark:bg-green-900/20 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ status }}</p>
                        </div>
                    </div>
                </div>

                <form class="mt-8 space-y-6" @submit.prevent="submit">
                    <div class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Email Address
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                autocomplete="username"
                                class="mt-1 appearance-none relative block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent focus:z-10 sm:text-sm transition-colors"
                                placeholder="Enter your email"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Password
                            </label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                autocomplete="current-password"
                                class="mt-1 appearance-none relative block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white dark:bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent focus:z-10 sm:text-sm transition-colors"
                                placeholder="Enter your password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <Checkbox v-model:checked="form.remember" name="remember" id="remember" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded" />
                            <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Remember me
                            </label>
                        </div>

                        <div class="text-sm">
                            <Link v-if="canResetPassword" :href="route('password.request')" class="font-medium text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300">
                                Forgot password?
                            </Link>
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
                        >
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg v-if="!form.processing" class="h-5 w-5 text-red-300 group-hover:text-red-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                <svg v-else class="animate-spin h-5 w-5 text-red-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            {{ form.processing ? 'Signing in...' : 'Sign in' }}
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Don't have an account?
                            <Link :href="route('register')" class="font-medium text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300">
                                Create one now
                            </Link>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
