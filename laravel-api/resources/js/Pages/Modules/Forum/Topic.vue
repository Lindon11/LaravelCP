<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    topic: { type: Object, required: true },
    posts: { type: Array, default: () => [] },
});

const content = ref('');
const processing = ref(false);

const reply = () => {
    if (processing.value) return;
    processing.value = true;
    router.post(route('forum.reply', props.topic.id), { content: content.value }, {
        preserveScroll: true,
        onFinish: () => { processing.value = false; content.value = ''; }
    });
};
</script>

<template>
    <AppLayout :title="topic.title">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ topic.title }}</h2>
                <div class="flex gap-4">
                    <button @click="$inertia.visit(route('forum.index'))" class="text-blue-600 hover:text-blue-800">← Forum</button>
                    <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash -->
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ $page.props.flash.success }}</div>
                <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $page.props.flash.error }}</div>

                <!-- Topic Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Started by <span class="font-semibold">{{ topic.author }}</span> on {{ topic.created_at }}</p>
                        </div>
                        <div class="text-sm text-gray-600">{{ topic.views }} views</div>
                    </div>
                </div>

                <!-- Posts -->
                <div class="space-y-4 mb-6">
                    <div v-for="post in posts" :key="post.id" class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex gap-6">
                            <div class="w-32 text-center">
                                <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-2">
                                    <span class="text-2xl font-bold text-blue-600">{{ post.author.charAt(0).toUpperCase() }}</span>
                                </div>
                                <p class="font-bold text-sm">{{ post.author }}</p>
                                <p class="text-xs text-gray-500">Level {{ post.author_level }}</p>
                            </div>
                            <div class="flex-1">
                                <div class="text-gray-700 mb-3 whitespace-pre-wrap">{{ post.content }}</div>
                                <div class="text-xs text-gray-500 border-t pt-2">Posted {{ post.created_at }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reply Form -->
                <div v-if="!topic.locked" class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-bold mb-4">Post Reply</h3>
                    <textarea v-model="content" rows="6" class="w-full px-4 py-2 border rounded mb-4" placeholder="Write your reply..."></textarea>
                    <button @click="reply" :disabled="processing || !content" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold disabled:bg-gray-400">Post Reply</button>
                </div>
                <div v-else class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                    <p class="text-red-800 font-bold">🔒 This topic is locked. No new replies can be posted.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
