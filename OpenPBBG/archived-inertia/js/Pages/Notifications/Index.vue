<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
    notifications: Array,
    unread_count: Number
});

const markAsRead = async (notificationId) => {
    try {
        await axios.post(route('notifications.mark-read', notificationId), {}, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        router.reload({ only: ['notifications', 'unread_count'], preserveScroll: true });
    } catch (error) {
        console.error('Failed to mark notification as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post(route('notifications.mark-all-read'), {}, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        router.reload({ only: ['notifications', 'unread_count'], preserveScroll: true });
    } catch (error) {
        console.error('Failed to mark all notifications as read:', error);
    }
};

const deleteNotification = async (notificationId) => {
    if (confirm('Are you sure you want to delete this notification?')) {
        try {
            await axios.delete(route('notifications.delete', notificationId), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            router.reload({ only: ['notifications', 'unread_count'], preserveScroll: true });
        } catch (error) {
            console.error('Failed to delete notification:', error);
        }
    }
};

const deleteAllRead = async () => {
    if (confirm('Are you sure you want to delete all read notifications?')) {
        try {
            await axios.delete(route('notifications.delete-read'), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            router.reload({ only: ['notifications', 'unread_count'], preserveScroll: true });
        } catch (error) {
            console.error('Failed to delete read notifications:', error);
        }
    }
};

const getTypeColor = (type) => {
    const colors = {
        'achievement': 'bg-yellow-50 border-yellow-200',
        'combat': 'bg-red-50 border-red-200',
        'gang_invite': 'bg-purple-50 border-purple-200',
        'message': 'bg-blue-50 border-blue-200',
        'announcement': 'bg-green-50 border-green-200',
        'system': 'bg-gray-50 border-gray-200'
    };
    return colors[type] || 'bg-gray-50 border-gray-200';
};

const getTypeTextColor = (type) => {
    const colors = {
        'achievement': 'text-yellow-800',
        'combat': 'text-red-800',
        'gang_invite': 'text-purple-800',
        'message': 'text-blue-800',
        'announcement': 'text-green-800',
        'system': 'text-gray-800'
    };
    return colors[type] || 'text-gray-800';
};
</script>

<template>
    <AppLayout title="Notifications">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📬 Notifications
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header Actions -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6 p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Your Notifications</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ unread_count }} unread notification{{ unread_count !== 1 ? 's' : '' }}
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <button 
                                v-if="unread_count > 0"
                                @click="markAllAsRead"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Mark All Read
                            </button>
                            <button 
                                @click="deleteAllRead"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Delete Read
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="space-y-4">
                    <div 
                        v-if="notifications.length === 0"
                        class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No notifications</h3>
                        <p class="mt-2 text-sm text-gray-500">You're all caught up! Check back later.</p>
                    </div>

                    <div 
                        v-for="notification in notifications" 
                        :key="notification.id"
                        :class="[
                            'bg-white overflow-hidden shadow sm:rounded-lg border-l-4 transition-all duration-200 hover:shadow-md',
                            getTypeColor(notification.type),
                            notification.is_read ? 'opacity-75' : ''
                        ]">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-4 flex-1">
                                    <!-- Icon -->
                                    <div class="text-3xl">
                                        {{ notification.icon || '📩' }}
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h4 :class="['text-lg font-bold', getTypeTextColor(notification.type)]">
                                                {{ notification.title }}
                                            </h4>
                                            <span 
                                                v-if="!notification.is_read"
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                New
                                            </span>
                                        </div>
                                        <p class="text-gray-700 mb-2">
                                            {{ notification.message }}
                                        </p>
                                        <div class="flex items-center gap-4 text-sm text-gray-500">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ notification.time_ago }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 uppercase">
                                                {{ notification.type.replace('_', ' ') }}
                                            </span>
                                        </div>

                                        <!-- Link if available -->
                                        <div v-if="notification.link" class="mt-3">
                                            <a 
                                                :href="notification.link"
                                                class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                                                View Details
                                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-2 ml-4">
                                    <button 
                                        v-if="!notification.is_read"
                                        @click="markAsRead(notification.id)"
                                        class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors"
                                        title="Mark as read">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                    <button 
                                        @click="deleteNotification(notification.id)"
                                        class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                                        title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
