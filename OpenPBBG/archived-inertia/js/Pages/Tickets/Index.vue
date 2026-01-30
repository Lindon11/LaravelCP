<script setup>
import { ref } from 'vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    tickets: Array,
    categories: Array,
});

const showCreateForm = ref(false);

const createForm = useForm({
    ticket_category_id: '',
    subject: '',
    description: '',
    priority: 'medium',
});

const submit = () => {
    createForm.post(route('tickets.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            showCreateForm.value = false;
        },
    });
};

const getStatusColor = (status) => {
    const colors = {
        open: 'bg-green-100 text-green-800',
        waiting_response: 'bg-yellow-100 text-yellow-800',
        answered: 'bg-blue-100 text-blue-800',
        closed: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getPriorityColor = (priority) => {
    const colors = {
        low: 'text-gray-600',
        medium: 'text-yellow-600',
        high: 'text-orange-600',
        urgent: 'text-red-600',
    };
    return colors[priority] || 'text-gray-600';
};

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};
</script>

<template>
    <AppLayout>
        <Head title="Support Tickets" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">🎫 Support Tickets</h1>
                    <div class="flex gap-3">
                        <button @click="showCreateForm = !showCreateForm" 
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            {{ showCreateForm ? '✕ Cancel' : '+ New Ticket' }}
                        </button>
                        <a :href="route('dashboard')" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                            ← Dashboard
                        </a>
                    </div>
                </div>

                <!-- Create Ticket Form -->
                <div v-if="showCreateForm" class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-4">Create New Ticket</h2>
                    <form @submit.prevent="submit">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select v-model="createForm.ticket_category_id" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="">Select a category...</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.icon }} {{ category.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                <select v-model="createForm.priority" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="low">Low - General question</option>
                                    <option value="medium">Medium - Need assistance</option>
                                    <option value="high">High - Important issue</option>
                                    <option value="urgent">Urgent - Critical problem</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                                <input v-model="createForm.subject" 
                                       type="text" 
                                       placeholder="Brief description of your issue..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea v-model="createForm.description" 
                                          rows="5" 
                                          placeholder="Please provide detailed information about your issue..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                          required></textarea>
                                <p class="text-sm text-gray-500 mt-1">Minimum 10 characters</p>
                            </div>

                            <button type="submit" 
                                    :disabled="createForm.processing"
                                    class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                Submit Ticket
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tickets List -->
                <div v-if="tickets.length > 0" class="space-y-4">
                    <Link v-for="ticket in tickets" 
                          :key="ticket.id" 
                          :href="route('tickets.show', ticket.id)"
                          class="block bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-2xl">{{ ticket.category.icon }}</span>
                                    <h3 class="text-xl font-bold text-gray-900">{{ ticket.subject }}</h3>
                                </div>
                                <p class="text-sm text-gray-600">{{ ticket.category.name }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span :class="getStatusColor(ticket.status)" 
                                      class="px-3 py-1 text-xs font-semibold rounded-full">
                                    {{ formatStatus(ticket.status) }}
                                </span>
                                <span :class="getPriorityColor(ticket.priority)" 
                                      class="text-xs font-semibold">
                                    {{ ticket.priority.toUpperCase() }}
                                </span>
                            </div>
                        </div>

                        <p class="text-gray-600 mb-3 line-clamp-2">{{ ticket.description }}</p>

                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>💬 {{ ticket.messages_count }} {{ ticket.messages_count === 1 ? 'reply' : 'replies' }}</span>
                            <span>{{ new Date(ticket.created_at).toLocaleDateString() }}</span>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-500 text-lg mb-4">You have no support tickets</p>
                    <p class="text-sm text-gray-400 mb-6">Need help? Create a ticket and our support team will assist you!</p>
                    <button @click="showCreateForm = true" 
                            class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700">
                        Create Your First Ticket
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
