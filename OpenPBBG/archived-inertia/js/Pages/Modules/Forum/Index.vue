<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    categories: { type: Array, default: () => [] },
});
</script>

<template>
    <AppLayout title="Forum">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">💬 Forum</h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-2xl font-bold mb-6">Forum Categories</h3>
                    <div class="space-y-3">
                        <Link v-for="cat in categories" :key="cat.id" 
                              :href="route('forum.category', cat.id)"
                              class="block border border-gray-200 rounded-lg p-6 hover:border-blue-500 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold mb-1">{{ cat.name }}</h4>
                                    <p class="text-gray-600 text-sm">{{ cat.description }}</p>
                                </div>
                                <div class="text-right ml-6">
                                    <p class="text-3xl font-bold text-blue-600">{{ cat.topic_count }}</p>
                                    <p class="text-gray-500 text-sm">{{ cat.post_count }} posts</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
