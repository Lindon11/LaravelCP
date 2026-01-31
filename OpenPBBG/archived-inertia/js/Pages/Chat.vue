<template>
    <AppLayout title="Chat">
        <!-- Discord-style full-height chat layout -->
        <div class="flex h-[calc(100vh-64px)] bg-gray-100 dark:bg-[#313338]">
            <!-- Server/Channel Sidebar -->
            <div class="w-60 bg-gray-200 dark:bg-[#2b2d31] flex flex-col border-r border-gray-300 dark:border-[#1e1f22]">
                <!-- Server Header -->
                <div class="h-12 px-4 flex items-center border-b border-gray-300 dark:border-[#1e1f22] shadow-sm">
                    <h2 class="font-bold text-gray-800 dark:text-white truncate">OpenPBBG</h2>
                </div>

                <!-- Channels List -->
                <div class="flex-1 overflow-y-auto py-2 scrollbar-thin">
                    <!-- Text Channels Section -->
                    <div class="px-2 mb-2">
                        <button class="w-full flex items-center px-1 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hover:text-gray-700 dark:hover:text-gray-300">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Text Channels
                        </button>
                        
                        <div v-if="channels.length === 0" class="px-2 py-2 text-sm text-gray-500 dark:text-gray-400">
                            No channels available
                        </div>
                        
                        <div v-for="channel in channels" :key="channel.id">
                            <button
                                @click="selectChannel(channel)"
                                :class="[
                                    'w-full flex items-center px-2 py-1.5 rounded text-sm group transition-colors',
                                    selectedChannel?.id === channel.id
                                        ? 'bg-gray-400/50 dark:bg-[#404249] text-gray-900 dark:text-white'
                                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-300/50 dark:hover:bg-[#35373c] hover:text-gray-800 dark:hover:text-gray-200'
                                ]"
                            >
                                <span class="text-gray-500 dark:text-gray-400 mr-1.5">#</span>
                                <span class="truncate">{{ channel.name }}</span>
                                <span v-if="channel.type === 'announcement'" class="ml-auto">
                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Direct Messages Section -->
                    <div class="px-2 mt-4">
                        <button class="w-full flex items-center px-1 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hover:text-gray-700 dark:hover:text-gray-300">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Direct Messages
                        </button>
                        
                        <div v-if="directMessages.length === 0" class="px-2 py-2 text-sm text-gray-500 dark:text-gray-400">
                            No messages yet
                        </div>
                        
                        <div v-for="dm in directMessages" :key="dm.id">
                            <button
                                @click="selectDM(dm)"
                                :class="[
                                    'w-full flex items-center px-2 py-1.5 rounded text-sm group transition-colors relative',
                                    selectedDM?.id === dm.id
                                        ? 'bg-gray-400/50 dark:bg-[#404249] text-gray-900 dark:text-white'
                                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-300/50 dark:hover:bg-[#35373c] hover:text-gray-800 dark:hover:text-gray-200'
                                ]"
                            >
                                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center mr-2 text-white text-xs font-medium">
                                    {{ (dm.from_user_id === auth.user.id ? dm.recipient?.name : dm.sender?.name)?.charAt(0).toUpperCase() || '?' }}
                                </div>
                                <span class="truncate">{{ dm.from_user_id === auth.user.id ? dm.recipient?.name : dm.sender?.name }}</span>
                                <span v-if="dm.unread_count > 0" class="ml-auto bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full min-w-[20px] text-center">
                                    {{ dm.unread_count }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- User Panel -->
                <div class="h-14 px-2 flex items-center bg-gray-300/50 dark:bg-[#232428] border-t border-gray-300 dark:border-[#1e1f22]">
                    <div class="flex items-center flex-1 min-w-0">
                        <div class="relative">
                            <img :src="auth.user.profile_photo_url" class="w-8 h-8 rounded-full" :alt="auth.user.name">
                            <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-gray-300 dark:border-[#232428]"></div>
                        </div>
                        <div class="ml-2 min-w-0">
                            <div class="text-sm font-medium text-gray-800 dark:text-white truncate">{{ auth.user.name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Online</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="flex-1 flex flex-col bg-white dark:bg-[#313338]">
                <!-- Channel Header -->
                <div v-if="selectedChannel || selectedDM" class="h-12 px-4 flex items-center border-b border-gray-200 dark:border-[#1e1f22] shadow-sm bg-white dark:bg-[#313338]">
                    <div class="flex items-center">
                        <span v-if="selectedChannel" class="text-gray-500 dark:text-gray-400 text-xl mr-2">#</span>
                        <div v-else-if="selectedDM" class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center mr-2 text-white text-xs font-medium">
                            @
                        </div>
                        <h3 class="font-semibold text-gray-800 dark:text-white">
                            {{ selectedChannel?.name || (selectedDM?.from_user_id === auth.user.id ? selectedDM?.recipient?.name : selectedDM?.sender?.name) }}
                        </h3>
                    </div>
                    <div v-if="selectedChannel?.description" class="ml-4 pl-4 border-l border-gray-300 dark:border-gray-600 text-sm text-gray-500 dark:text-gray-400 truncate">
                        {{ selectedChannel.description }}
                    </div>
                </div>

                <!-- Messages Area -->
                <div 
                    v-if="selectedChannel || selectedDM" 
                    ref="messagesContainer"
                    class="flex-1 overflow-y-auto px-4 py-4 space-y-1 bg-white dark:bg-[#313338] scrollbar-thin"
                >
                    <div v-if="displayedMessages.length === 0" class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400">
                        <div class="w-16 h-16 rounded-full bg-gray-200 dark:bg-[#404249] flex items-center justify-center mb-4">
                            <span class="text-3xl">#</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Welcome to #{{ selectedChannel?.name || 'Chat' }}</h3>
                        <p class="text-sm">This is the start of the conversation.</p>
                    </div>

                    <div 
                        v-for="(message, index) in displayedMessages" 
                        :key="message.id"
                        :class="[
                            'group flex hover:bg-gray-50 dark:hover:bg-[#2e3035] -mx-4 px-4 py-0.5 transition-colors',
                            shouldShowHeader(message, index) ? 'mt-4 pt-1' : ''
                        ]"
                    >
                        <!-- Avatar (only show for first message in group) -->
                        <div class="w-10 flex-shrink-0 mr-4">
                            <img 
                                v-if="shouldShowHeader(message, index)"
                                :src="message.user?.profile_photo_url || '/img/default-avatar.png'" 
                                :alt="message.user?.name || 'User'" 
                                class="w-10 h-10 rounded-full cursor-pointer hover:opacity-80"
                            >
                        </div>
                        
                        <!-- Message Content -->
                        <div class="flex-1 min-w-0">
                            <!-- Header (username & timestamp) -->
                            <div v-if="shouldShowHeader(message, index)" class="flex items-baseline mb-1">
                                <span class="font-medium text-gray-900 dark:text-white hover:underline cursor-pointer">
                                    {{ message.user?.name || 'Unknown User' }}
                                </span>
                                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                    {{ formatDateTime(message.created_at) }}
                                </span>
                                <span v-if="message.is_edited" class="ml-1 text-xs text-gray-400 dark:text-gray-500">(edited)</span>
                            </div>

                            <!-- Message Text -->
                            <div class="flex items-start group/message">
                                <span v-if="!shouldShowHeader(message, index)" class="text-xs text-gray-400 dark:text-gray-500 w-10 flex-shrink-0 text-right mr-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                    {{ formatTime(message.created_at) }}
                                </span>
                                <p class="text-gray-800 dark:text-gray-200 break-words whitespace-pre-wrap">{{ message.message }}</p>
                            </div>

                            <!-- Reactions -->
                            <div v-if="message.reactions && message.reactions.length > 0" class="flex gap-1 mt-1 flex-wrap">
                                <button
                                    v-for="reaction in groupReactions(message.reactions)"
                                    :key="reaction.emoji"
                                    @click="toggleReaction(message, reaction.emoji)"
                                    :class="[
                                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-sm border transition-colors',
                                        reaction.users.includes(auth.user.name)
                                            ? 'bg-indigo-100 dark:bg-indigo-500/20 border-indigo-300 dark:border-indigo-500/50 text-indigo-700 dark:text-indigo-300'
                                            : 'bg-gray-100 dark:bg-[#2b2d31] border-gray-300 dark:border-[#3f4147] text-gray-600 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                                    ]"
                                    :title="reaction.users.join(', ')"
                                >
                                    <span>{{ reaction.emoji }}</span>
                                    <span class="text-xs">{{ reaction.count }}</span>
                                </button>
                            </div>

                            <!-- Message Actions (on hover) -->
                            <div class="absolute right-4 -top-4 opacity-0 group-hover:opacity-100 transition-opacity bg-white dark:bg-[#313338] border border-gray-200 dark:border-[#3f4147] rounded-md shadow-lg flex">
                                <button 
                                    @click="showReactionPicker(message)" 
                                    class="p-1.5 hover:bg-gray-100 dark:hover:bg-[#3f4147] text-gray-500 dark:text-gray-400 rounded-l-md"
                                    title="Add Reaction"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                <button 
                                    v-if="message.user?.id === auth.user.id"
                                    @click="editMessage(message)" 
                                    class="p-1.5 hover:bg-gray-100 dark:hover:bg-[#3f4147] text-gray-500 dark:text-gray-400"
                                    title="Edit"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button 
                                    v-if="message.user?.id === auth.user.id"
                                    @click="deleteMessage(message)" 
                                    class="p-1.5 hover:bg-red-100 dark:hover:bg-red-500/20 text-gray-500 dark:text-gray-400 hover:text-red-500 rounded-r-md"
                                    title="Delete"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Selection State -->
                <div v-else class="flex-1 flex flex-col items-center justify-center bg-white dark:bg-[#313338]">
                    <div class="w-24 h-24 rounded-full bg-gray-200 dark:bg-[#404249] flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Select a channel or conversation</h3>
                    <p class="text-gray-500 dark:text-gray-400">Choose from the sidebar to start chatting</p>
                </div>

                <!-- Message Input -->
                <div v-if="selectedChannel || selectedDM" class="px-4 pb-6 bg-white dark:bg-[#313338]">
                    <!-- Typing Indicator -->
                    <div v-if="typingUsers.length > 0" class="text-xs text-gray-500 dark:text-gray-400 mb-2 flex items-center">
                        <span class="flex space-x-1 mr-2">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                        </span>
                        {{ typingUsers.join(', ') }} {{ typingUsers.length === 1 ? 'is' : 'are' }} typing...
                    </div>

                    <!-- Input Box -->
                    <form @submit.prevent="sendMessage" class="relative">
                        <div class="flex items-end bg-gray-100 dark:bg-[#383a40] rounded-lg">
                            <!-- Attachment Button -->
                            <button type="button" class="p-3 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                            
                            <!-- Text Input -->
                            <textarea
                                v-model="newMessage"
                                @keydown.enter.exact.prevent="sendMessage"
                                @input="handleInput"
                                :placeholder="`Message ${selectedChannel ? '#' + selectedChannel.name : (selectedDM?.from_user_id === auth.user.id ? selectedDM?.recipient?.name : selectedDM?.sender?.name)}`"
                                class="flex-1 bg-transparent border-0 text-gray-800 dark:text-gray-200 placeholder-gray-500 dark:placeholder-gray-400 resize-none py-3 px-2 focus:ring-0 max-h-48"
                                rows="1"
                                ref="messageInput"
                            ></textarea>

                            <!-- Emoji Button -->
                            <button type="button" class="p-3 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Members Sidebar (optional - can be toggled) -->
            <div v-if="selectedChannel && showMembers" class="w-60 bg-gray-100 dark:bg-[#2b2d31] border-l border-gray-200 dark:border-[#1e1f22] overflow-y-auto">
                <div class="p-4">
                    <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                        Members — {{ selectedChannel.members_count || 0 }}
                    </h4>
                    <div v-for="member in selectedChannel.members" :key="member.id" class="flex items-center py-1.5 px-2 rounded hover:bg-gray-200 dark:hover:bg-[#35373c] cursor-pointer">
                        <div class="relative">
                            <img :src="member.profile_photo_url" class="w-8 h-8 rounded-full" :alt="member.name">
                            <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-gray-100 dark:border-[#2b2d31]"></div>
                        </div>
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ member.name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const auth = page.props.auth;
const isAdmin = computed(() => auth.user.roles && (auth.user.roles.includes('admin') || auth.user.roles.includes('staff')));

const channels = ref([]);
const directMessages = ref([]);
const selectedChannel = ref(null);
const selectedDM = ref(null);
const newMessage = ref('');
const typingUsers = ref([]);
const messages = ref([]);
const messagesContainer = ref(null);
const messageInput = ref(null);
const showMembers = ref(false);

const displayedMessages = computed(() => {
    let msgs = [];
    if (selectedChannel.value) {
        msgs = messages.value.filter(m => m.channel_id === selectedChannel.value.id);
    } else if (selectedDM.value) {
        msgs = messages.value.filter(m =>
            (m.from_user_id === selectedDM.value.from_user_id && m.to_user_id === selectedDM.value.to_user_id) ||
            (m.from_user_id === selectedDM.value.to_user_id && m.to_user_id === selectedDM.value.from_user_id)
        );
    }
    // Sort by created_at ascending (oldest first)
    return [...msgs].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
});

// Check if we should show the message header (different user or time gap)
function shouldShowHeader(message, index) {
    if (index === 0) return true;
    const prevMessage = displayedMessages.value[index - 1];
    if (!prevMessage) return true;
    
    // Different user
    if (message.user?.id !== prevMessage.user?.id) return true;
    
    // More than 5 minutes apart
    const timeDiff = new Date(message.created_at) - new Date(prevMessage.created_at);
    if (timeDiff > 5 * 60 * 1000) return true;
    
    return false;
}

onMounted(async () => {
    await fetchChannels();
    await fetchDirectMessages();
});

async function fetchChannels() {
    try {
        const response = await axios.get('/api/channels');
        channels.value = response.data;
    } catch (error) {
        console.error('Error fetching channels:', error.response?.data || error.message);
    }
}

async function fetchDirectMessages() {
    try {
        const response = await axios.get('/api/direct-messages');
        directMessages.value = response.data;
    } catch (error) {
        console.error('Error fetching DMs:', error.response?.data || error.message);
    }
}

async function selectChannel(channel) {
    selectedChannel.value = channel;
    selectedDM.value = null;
    newMessage.value = '';
    await fetchChannelMessages();
    scrollToBottom();
}

async function selectDM(dm) {
    selectedDM.value = dm;
    selectedChannel.value = null;
    newMessage.value = '';
    await fetchDMMessages();
    scrollToBottom();
}

async function fetchChannelMessages() {
    try {
        const response = await axios.get(`/api/channels/${selectedChannel.value.id}/messages`);
        messages.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error fetching messages:', error);
    }
}

async function fetchDMMessages() {
    try {
        const otherUserId = selectedDM.value.from_user_id === auth.user.id ? selectedDM.value.to_user_id : selectedDM.value.from_user_id;
        const response = await axios.get(`/api/direct-messages/${otherUserId}`);
        messages.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error fetching DM messages:', error);
    }
}

async function sendMessage() {
    if (!newMessage.value.trim()) return;

    try {
        if (selectedChannel.value) {
            await axios.post(`/api/channels/${selectedChannel.value.id}/messages`, {
                message: newMessage.value
            });
        } else if (selectedDM.value) {
            const otherUserId = selectedDM.value.from_user_id === auth.user.id ? selectedDM.value.to_user_id : selectedDM.value.from_user_id;
            await axios.post('/api/direct-messages', {
                to_user_id: otherUserId,
                message: newMessage.value
            });
        }

        newMessage.value = '';
        if (messageInput.value) {
            messageInput.value.style.height = 'auto';
        }
        selectedChannel.value ? await fetchChannelMessages() : await fetchDMMessages();
        scrollToBottom();
    } catch (error) {
        console.error('Error sending message:', error);
    }
}

function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
}

function groupReactions(reactions) {
    if (!reactions) return [];
    const grouped = {};
    reactions.forEach(r => {
        if (!grouped[r.emoji]) {
            grouped[r.emoji] = { emoji: r.emoji, count: 0, users: [] };
        }
        grouped[r.emoji].count++;
        if (r.user?.name) {
            grouped[r.emoji].users.push(r.user.name);
        }
    });
    return Object.values(grouped);
}

async function toggleReaction(message, emoji) {
    try {
        await axios.post(`/api/messages/${message.id}/reactions`, { emoji });
        selectedChannel.value ? await fetchChannelMessages() : await fetchDMMessages();
    } catch (error) {
        console.error('Error toggling reaction:', error);
    }
}

function showReactionPicker(message) {
    const emojis = ['👍', '❤️', '😂', '😮', '😢', '🔥', '🎉', '👀'];
    const emoji = prompt(`Choose reaction:\n${emojis.join('  ')}`);
    if (emoji && emoji.trim()) {
        toggleReaction(message, emoji.trim());
    }
}

async function editMessage(message) {
    const newText = prompt('Edit message:', message.message);
    if (newText && newText !== message.message) {
        try {
            await axios.patch(`/api/messages/${message.id}`, { message: newText });
            selectedChannel.value ? await fetchChannelMessages() : await fetchDMMessages();
        } catch (error) {
            console.error('Error editing message:', error);
        }
    }
}

async function deleteMessage(message) {
    if (confirm('Are you sure you want to delete this message?')) {
        try {
            await axios.delete(`/api/messages/${message.id}`);
            selectedChannel.value ? await fetchChannelMessages() : await fetchDMMessages();
        } catch (error) {
            console.error('Error deleting message:', error);
        }
    }
}

function formatDateTime(timestamp) {
    const date = new Date(timestamp);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    
    if (date.toDateString() === today.toDateString()) {
        return `Today at ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    } else if (date.toDateString() === yesterday.toDateString()) {
        return `Yesterday at ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    } else {
        return date.toLocaleDateString([], { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + 
               date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
}

function formatTime(timestamp) {
    return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function handleInput(event) {
    // Auto-resize textarea
    const textarea = event.target;
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 192) + 'px';
    
    // TODO: Implement typing indicators via WebSocket
}
</script>

<style scoped>
/* Custom scrollbar for Discord-like feel */
.scrollbar-thin::-webkit-scrollbar {
    width: 8px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #1e1f22;
    border-radius: 4px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #3f4147;
}

/* Animation for typing indicator */
@keyframes bounce {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-4px);
    }
}
</style>
