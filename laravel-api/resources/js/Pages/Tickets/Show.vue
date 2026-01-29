<script setup>
import { ref } from 'vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    ticket: Object,
});

const replyForm = useForm({
    message: '',
});

const submitReply = () => {
    replyForm.post(route('tickets.reply', props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => replyForm.reset(),
    });
};

const closeTicket = () => {
    if (confirm('Are you sure you want to close this ticket?')) {
        router.post(route('tickets.close', props.ticket.id));
    }
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
</script>

<template>
    <AppLayout>
        <Head :title="'Ticket #' + ticket.id" />

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">Ticket #{{ ticket.id }}</h1>
                    <Link :href="route('tickets.index')" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                        ← Back to Tickets
                    </Link>
                </div>

                <!-- Ticket Details -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">{{ ticket.category.icon }}</span>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">{{ ticket.subject }}</h2>
                                <p class="text-sm text-gray-600">{{ ticket.category.name }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span :class="getStatusColor(ticket.status)" 
                                  class="px-3 py-1 text-xs font-semibold rounded-full">
                                {{ ticket.status.replace('_', ' ').toUpperCase() }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ new Date(ticket.created_at).toLocaleString() }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-gray-800">{{ ticket.description }}</p>
                    </div>

                    <div v-if="ticket.status !== 'closed'" class="flex justify-end">
                        <button @click="closeTicket" 
                                class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                            Close Ticket
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <div class="space-y-4 mb-6">
                    <div v-for="message in ticket.messages" 
                         :key="message.id"
                         :class="message.is_staff_reply ? 'bg-blue-50' : 'bg-white'"
                         class="rounded-lg shadow p-6">
                        <div class="flex items-start gap-4">
                            <div :class="message.is_staff_reply ? 'bg-blue-600' : 'bg-gray-600'" 
                                 class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold">
                                {{ message.is_staff_reply ? '👨‍💼' : '👤' }}
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-bold text-gray-900">
                                            {{ message.is_staff_reply ? 'Support Team' : message.user.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ new Date(message.created_at).toLocaleString() }}
                                        </p>
                                    </div>
                                    <span v-if="message.is_staff_reply" 
                                          class="px-2 py-1 bg-blue-600 text-white text-xs font-semibold rounded">
                                        STAFF
                                    </span>
                                </div>
                                <p class="text-gray-800 whitespace-pre-wrap">{{ message.message }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reply Form -->
                <div v-if="ticket.status !== 'closed'" class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-bold mb-4">Add Reply</h3>
                    <form @submit.prevent="submitReply">
                        <textarea v-model="replyForm.message" 
                                  rows="4" 
                                  placeholder="Type your message here..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-4"
                                  required></textarea>
                        <button type="submit" 
                                :disabled="replyForm.processing"
                                class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            Send Reply
                        </button>
                    </form>
                </div>

                <!-- Closed Notice -->
                <div v-else class="bg-gray-50 rounded-lg shadow p-6 text-center">
                    <p class="text-gray-600">
                        🔒 This ticket is closed. Create a new ticket if you need further assistance.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
