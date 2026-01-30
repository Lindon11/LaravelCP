<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    category: { type: Object, required: true },
    topics: { type: Array, default: () => [] },
});

const showCreateForm = ref(false);
const form = useForm({
    title: '',
    content: '',
});

const createTopic = () => {
    form.post(route('forum.create-topic', props.category.id), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateForm.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <AppLayout :title="category.name">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ category.name }}</h2>
                <div class="flex gap-4">
                    <Link :href="route('forum.index')" class="text-blue-600 hover:text-blue-800">← Forum</Link>
                    <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800 font-semibold">← Dashboard</Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash -->
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ $page.props.flash.success }}</div>
                <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $page.props.flash.error }}</div>

                <!-- Create Topic Button -->
                <div class="mb-6">
                    <button v-if="!showCreateForm" @click="showCreateForm = true" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold">New Topic</button>
                    <div v-else class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-bold mb-4">Create New Topic</h3>
                        <div class="space-y-4">
                            <div>
                                <input v-model="form.title" type="text" class="w-full px-4 py-2 border rounded" placeholder="Topic Title" :class="{ 'border-red-500': form.errors.title }">
                                <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
                            </div>
                            <div>
                                <textarea v-model="form.content" rows="6" class="w-full px-4 py-2 border rounded" placeholder="Your message (at least 10 characters)..." :class="{ 'border-red-500': form.errors.content }"></textarea>
                                <p v-if="form.errors.content" class="text-red-500 text-sm mt-1">{{ form.errors.content }}</p>
                            </div>
                            <div class="flex gap-3">
                                <button @click="createTopic" :disabled="form.processing || !form.title || !form.content" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-bold disabled:bg-gray-400">Post Topic</button>
                                <button @click="showCreateForm = false; form.reset(); form.clearErrors();" class="px-6 py-2 bg-gray-300 rounded hover:bg-gray-400 font-bold">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Topics List -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div v-if="topics.length === 0" class="text-center text-gray-500 py-12">
                        <p class="text-xl mb-2">No topics yet</p>
                        <p class="text-sm">Be the first to start a discussion!</p>
                    </div>
                    <div v-else class="space-y-3">
                        <Link v-for="topic in topics" :key="topic.id" 
                              :href="route('forum.topic', topic.id)"
                              class="block border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span v-if="topic.sticky" class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded">STICKY</span>
                                        <span v-if="topic.locked" class="px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded">LOCKED</span>
                                        <h4 class="text-lg font-bold">{{ topic.title }}</h4>
                                    </div>
                                    <p class="text-sm text-gray-600">by {{ topic.author }} • {{ topic.created_at }}</p>
                                </div>
                                <div class="text-right ml-6">
                                    <p class="text-2xl font-bold text-blue-600">{{ topic.replies }}</p>
                                    <p class="text-gray-500 text-sm">{{ topic.views }} views</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
