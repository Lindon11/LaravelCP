<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';
import axios from 'axios';

const props = defineProps({
    title: String,
});

const page = usePage();
const { isDark, toggleDarkMode } = useDarkMode();

// Force dark mode for game layout
onMounted(() => {
    document.documentElement.classList.add('dark');
});

const player = computed(() => page.props.player || page.props.auth?.user);
const auth = computed(() => page.props.auth);

const showMobileMenu = ref(false);
const showUserMenu = ref(false);
const showNotifications = ref(false);
const notifications = ref([]);
const loadingNotifications = ref(false);

const fetchNotifications = async () => {
    if (loadingNotifications.value) return;
    
    loadingNotifications.value = true;
    try {
        const response = await axios.get(route('notifications.recent'), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        notifications.value = response.data.notifications;
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    } finally {
        loadingNotifications.value = false;
    }
};

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    if (showNotifications.value && notifications.value.length === 0) {
        fetchNotifications();
    }
};

const markAsRead = async (notificationId) => {
    try {
        await axios.post(route('notifications.mark-read', notificationId), {}, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const notification = notifications.value.find(n => n.id === notificationId);
        if (notification) {
            notification.is_read = true;
        }
        // Update unread count
        page.props.unreadNotifications = Math.max(0, (page.props.unreadNotifications || 0) - 1);
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
        notifications.value.forEach(n => n.is_read = true);
        page.props.unreadNotifications = 0;
    } catch (error) {
        console.error('Failed to mark all notifications as read:', error);
    }
};

const logout = () => {
    router.post(route('logout'));
};

const formatMoney = (amount) => {
    if (amount >= 1000000) {
        return '$' + (amount / 1000000).toFixed(1) + 'M';
    } else if (amount >= 1000) {
        return '$' + (amount / 1000).toFixed(1) + 'K';
    }
    return '$' + amount?.toLocaleString();
};

// XP calculations
const xpToNextLevel = computed(() => (player.value?.level || 1) * 1000);
const xpProgress = computed(() => {
    const current = player.value?.experience || 0;
    const needed = xpToNextLevel.value;
    return Math.min(100, (current / needed) * 100);
});

// Navigation items with icons
const navItems = [
    { name: 'Home', route: 'dashboard', icon: 'home' },
    { name: 'Crimes', route: 'crimes.index', icon: 'crime' },
    { name: 'Gym', route: 'gym.index', icon: 'gym' },
    { name: 'Hospital', route: 'hospital.index', icon: 'hospital' },
    { name: 'Jail', route: 'jail.index', icon: 'jail' },
    { name: 'Bank', route: 'bank.index', icon: 'bank' },
    { name: 'Travel', route: 'travel.index', icon: 'travel' },
    { name: 'Shop', route: 'shop.index', icon: 'shop' },
    { name: 'Chat', route: 'chat', icon: 'chat' },
];
</script>

<template>
    <div class="min-h-screen bg-[#1a1d21] text-gray-200">
        <Head :title="title" />

        <!-- Top Header Bar -->
        <header class="bg-[#0d1117] border-b border-[#21262d] sticky top-0 z-50">
            <div class="px-4">
                <div class="flex items-center justify-between h-12">
                    <!-- Logo -->
                    <Link :href="route('dashboard')" class="flex items-center space-x-2">
                        <span class="text-xl font-bold text-cyan-400">Open</span>
                        <span class="text-xl font-bold text-white">PBBG</span>
                    </Link>

                    <!-- Resource Bars (Desktop) -->
                    <div v-if="player" class="hidden lg:flex items-center space-x-6">
                        <!-- Energy -->
                        <div class="flex items-center space-x-2">
                            <span class="text-yellow-400">⚡</span>
                            <div class="w-32 bg-[#21262d] rounded-full h-3 relative overflow-hidden">
                                <div 
                                    class="h-full bg-gradient-to-r from-yellow-500 to-yellow-400 rounded-full transition-all"
                                    :style="{ width: `${(player.energy / player.max_energy) * 100}%` }"
                                ></div>
                            </div>
                            <span class="text-sm font-mono text-yellow-400">{{ player.energy }}/{{ player.max_energy }}</span>
                        </div>

                        <!-- Health -->
                        <div class="flex items-center space-x-2">
                            <span class="text-red-400">❤️</span>
                            <div class="w-32 bg-[#21262d] rounded-full h-3 relative overflow-hidden">
                                <div 
                                    class="h-full bg-gradient-to-r from-red-600 to-red-400 rounded-full transition-all"
                                    :style="{ width: `${(player.health / player.max_health) * 100}%` }"
                                ></div>
                            </div>
                            <span class="text-sm font-mono text-red-400">{{ player.health }}/{{ player.max_health }}</span>
                        </div>

                        <!-- XP Bar -->
                        <div class="flex items-center space-x-2">
                            <span class="text-cyan-400">⭐</span>
                            <div class="w-32 bg-[#21262d] rounded-full h-3 relative overflow-hidden">
                                <div 
                                    class="h-full bg-gradient-to-r from-purple-500 to-blue-500 rounded-full transition-all"
                                    :style="{ width: `${xpProgress}%` }"
                                ></div>
                            </div>
                            <span class="text-sm font-mono text-cyan-400">{{ player.experience || 0 }}/{{ xpToNextLevel }}</span>
                        </div>

                        <!-- Nerve (if available) -->
                        <div v-if="player.nerve !== undefined" class="flex items-center space-x-2">
                            <span class="text-purple-400">💜</span>
                            <div class="w-32 bg-[#21262d] rounded-full h-3 relative overflow-hidden">
                                <div 
                                    class="h-full bg-gradient-to-r from-purple-600 to-purple-400 rounded-full transition-all"
                                    :style="{ width: `${(player.nerve / (player.max_nerve || 100)) * 100}%` }"
                                ></div>
                            </div>
                            <span class="text-sm font-mono text-purple-400">{{ player.nerve }}/{{ player.max_nerve || 100 }}</span>
                        </div>
                    </div>

                    <!-- Currency & User (Desktop) -->
                    <div class="hidden lg:flex items-center space-x-4">
                        <!-- Location -->
                        <div v-if="player?.location_name" class="flex items-center space-x-1 text-indigo-400">
                            <span>📍</span>
                            <span class="font-mono font-semibold text-sm">{{ player.location_name }}</span>
                        </div>
                        
                        <!-- Cash -->
                        <div class="flex items-center space-x-1 text-green-400">
                            <span>💵</span>
                            <span class="font-mono font-semibold">{{ formatMoney(player?.cash || 0) }}</span>
                        </div>
                        
                        <!-- Bank -->
                        <div class="flex items-center space-x-1 text-blue-400">
                            <span>🏦</span>
                            <span class="font-mono font-semibold">{{ formatMoney(player?.bank || 0) }}</span>
                        </div>

                        <!-- Respect/XP -->
                        <div class="flex items-center space-x-1 text-yellow-300">
                            <span>⭐</span>
                            <span class="font-mono font-semibold">{{ player?.respect || 0 }}</span>
                        </div>

                        <!-- Notifications -->
                        <div class="relative">
                            <button 
                                @click="toggleNotifications"
                                class="relative p-2 text-gray-400 hover:text-white transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span v-if="page.props.unreadNotifications > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
                                    {{ page.props.unreadNotifications > 9 ? '9+' : page.props.unreadNotifications }}
                                </span>
                            </button>

                            <!-- Notifications Dropdown -->
                            <div 
                                v-if="showNotifications"
                                @click.away="showNotifications = false"
                                class="absolute right-0 mt-2 w-96 bg-[#21262d] border border-[#30363d] rounded-lg shadow-xl z-50 max-h-[600px] flex flex-col"
                            >
                                <!-- Header -->
                                <div class="flex items-center justify-between px-4 py-3 border-b border-[#30363d]">
                                    <h3 class="font-bold text-white">Notifications</h3>
                                    <div class="flex items-center space-x-2">
                                        <button 
                                            v-if="notifications.some(n => !n.is_read)"
                                            @click="markAllAsRead"
                                            class="text-xs text-cyan-400 hover:text-cyan-300 transition"
                                        >
                                            Mark all read
                                        </button>
                                        <Link 
                                            :href="route('notifications.index')"
                                            class="text-xs text-gray-400 hover:text-white transition"
                                        >
                                            View All
                                        </Link>
                                    </div>
                                </div>

                                <!-- Notifications List -->
                                <div class="overflow-y-auto flex-1">
                                    <div v-if="loadingNotifications" class="flex items-center justify-center py-8">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-500"></div>
                                    </div>

                                    <div v-else-if="notifications.length === 0" class="flex flex-col items-center justify-center py-8 text-gray-500">
                                        <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p>No notifications</p>
                                    </div>

                                    <div v-else>
                                        <div 
                                            v-for="notification in notifications" 
                                            :key="notification.id"
                                            :class="[
                                                'px-4 py-3 border-b border-[#30363d] hover:bg-[#30363d] transition cursor-pointer',
                                                !notification.is_read ? 'bg-cyan-500/5' : ''
                                            ]"
                                            @click="markAsRead(notification.id)"
                                        >
                                            <div class="flex items-start space-x-3">
                                                <div class="text-2xl flex-shrink-0">{{ notification.icon }}</div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-start justify-between">
                                                        <h4 class="font-semibold text-white text-sm">{{ notification.title }}</h4>
                                                        <span v-if="!notification.is_read" class="ml-2 w-2 h-2 bg-cyan-500 rounded-full flex-shrink-0"></span>
                                                    </div>
                                                    <p class="text-sm text-gray-400 mt-1">{{ notification.message }}</p>
                                                    <span class="text-xs text-gray-500 mt-1 block">{{ notification.time_ago }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Dropdown -->
                        <div class="relative">
                            <button 
                                @click="showUserMenu = !showUserMenu"
                                class="flex items-center space-x-2 p-1 rounded hover:bg-[#21262d] transition"
                            >
                                <img 
                                    :src="auth?.user?.profile_photo_url || '/img/default-avatar.png'" 
                                    class="w-8 h-8 rounded-full border-2 border-cyan-500"
                                    :alt="player?.username"
                                >
                                <span class="text-sm font-semibold">{{ player?.username || auth?.user?.name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div 
                                v-if="showUserMenu"
                                @click.away="showUserMenu = false"
                                class="absolute right-0 mt-2 w-48 bg-[#21262d] border border-[#30363d] rounded-lg shadow-xl py-1 z-50"
                            >
                                <Link :href="route('profile.show')" class="block px-4 py-2 text-sm hover:bg-[#30363d] transition">
                                    👤 Profile
                                </Link>
                                <Link :href="route('inventory.index')" class="block px-4 py-2 text-sm hover:bg-[#30363d] transition">
                                    🎒 Inventory
                                </Link>
                                <a v-if="auth?.user?.roles?.includes('admin')" href="/admin" class="block px-4 py-2 text-sm hover:bg-[#30363d] transition text-cyan-400">
                                    ⚙️ Admin Panel
                                </a>
                                <hr class="border-[#30363d] my-1">
                                <button @click="logout" class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-[#30363d] transition">
                                    🚪 Logout
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button 
                        @click="showMobileMenu = !showMobileMenu"
                        class="lg:hidden p-2 text-gray-400 hover:text-white"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Navigation Bar -->
        <nav class="bg-[#161b22] border-b border-[#21262d]">
            <div class="px-4 overflow-x-auto scrollbar-thin">
                <div class="flex items-center space-x-1 py-2 min-w-max">
                    <Link 
                        v-for="item in navItems" 
                        :key="item.route"
                        :href="route(item.route)"
                        :class="[
                            'flex flex-col items-center px-4 py-2 rounded-lg transition-all min-w-[70px]',
                            route().current(item.route) 
                                ? 'bg-cyan-600/20 text-cyan-400 border border-cyan-500/30' 
                                : 'text-gray-400 hover:bg-[#21262d] hover:text-white'
                        ]"
                    >
                        <!-- Icons -->
                        <div class="text-2xl mb-1">
                            <span v-if="item.icon === 'home'">🏠</span>
                            <span v-else-if="item.icon === 'crime'">💰</span>
                            <span v-else-if="item.icon === 'gym'">💪</span>
                            <span v-else-if="item.icon === 'hospital'">🏥</span>
                            <span v-else-if="item.icon === 'jail'">⛓️</span>
                            <span v-else-if="item.icon === 'bank'">🏦</span>
                            <span v-else-if="item.icon === 'travel'">✈️</span>
                            <span v-else-if="item.icon === 'shop'">🛒</span>
                            <span v-else-if="item.icon === 'chat'">💬</span>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-wide">{{ item.name }}</span>
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Mobile Resource Bars -->
        <div v-if="player" class="lg:hidden bg-[#0d1117] border-b border-[#21262d] p-3">
            <div class="grid grid-cols-2 gap-3">
                <!-- Energy -->
                <div class="flex items-center space-x-2">
                    <span class="text-yellow-400 text-sm">⚡</span>
                    <div class="flex-1 bg-[#21262d] rounded-full h-2 overflow-hidden">
                        <div 
                            class="h-full bg-gradient-to-r from-yellow-500 to-yellow-400"
                            :style="{ width: `${(player.energy / player.max_energy) * 100}%` }"
                        ></div>
                    </div>
                    <span class="text-xs font-mono text-yellow-400">{{ player.energy }}/{{ player.max_energy }}</span>
                </div>
                <!-- Health -->
                <div class="flex items-center space-x-2">
                    <span class="text-red-400 text-sm">❤️</span>
                    <div class="flex-1 bg-[#21262d] rounded-full h-2 overflow-hidden">
                        <div 
                            class="h-full bg-gradient-to-r from-red-600 to-red-400"
                            :style="{ width: `${(player.health / player.max_health) * 100}%` }"
                        ></div>
                    </div>
                    <span class="text-xs font-mono text-red-400">{{ player.health }}/{{ player.max_health }}</span>
                </div>
            </div>
            <!-- XP Bar Row -->
            <div class="mt-3">
                <div class="flex items-center space-x-2">
                    <span class="text-cyan-400 text-sm">⭐</span>
                    <div class="flex-1 bg-[#21262d] rounded-full h-2 overflow-hidden">
                        <div 
                            class="h-full bg-gradient-to-r from-purple-500 to-blue-500"
                            :style="{ width: `${xpProgress}%` }"
                        ></div>
                    </div>
                    <span class="text-xs font-mono text-cyan-400">{{ player.experience || 0 }}/{{ xpToNextLevel }}</span>
                </div>
            </div>
            <!-- Currency Row -->
            <div class="flex items-center justify-around mt-3 text-sm">
                <div class="flex items-center space-x-1 text-green-400">
                    <span>💵</span>
                    <span class="font-mono">{{ formatMoney(player.cash || 0) }}</span>
                </div>
                <div class="flex items-center space-x-1 text-blue-400">
                    <span>🏦</span>
                    <span class="font-mono">{{ formatMoney(player.bank || 0) }}</span>
                </div>
                <div class="flex items-center space-x-1 text-yellow-300">
                    <span>⭐</span>
                    <span class="font-mono">{{ player.respect || 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div 
            v-if="showMobileMenu" 
            class="lg:hidden fixed inset-0 z-50 bg-black/80"
            @click="showMobileMenu = false"
        >
            <div class="bg-[#161b22] w-64 h-full p-4" @click.stop>
                <div class="flex items-center justify-between mb-6">
                    <span class="text-xl font-bold text-cyan-400">Menu</span>
                    <button @click="showMobileMenu = false" class="text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-2">
                    <Link 
                        v-for="item in navItems" 
                        :key="item.route"
                        :href="route(item.route)"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-[#21262d] transition"
                    >
                        <span class="text-xl">
                            <span v-if="item.icon === 'home'">🏠</span>
                            <span v-else-if="item.icon === 'crime'">💰</span>
                            <span v-else-if="item.icon === 'gym'">💪</span>
                            <span v-else-if="item.icon === 'hospital'">🏥</span>
                            <span v-else-if="item.icon === 'jail'">⛓️</span>
                            <span v-else-if="item.icon === 'bank'">🏦</span>
                            <span v-else-if="item.icon === 'travel'">✈️</span>
                            <span v-else-if="item.icon === 'shop'">🛒</span>
                            <span v-else-if="item.icon === 'chat'">💬</span>
                        </span>
                        <span>{{ item.name }}</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main class="p-4">
            <slot />
        </main>

        <!-- Global Chat Indicator (Bottom Right) -->
        <Link 
            :href="route('chat')"
            class="fixed bottom-4 right-4 bg-[#21262d] hover:bg-[#30363d] border border-[#30363d] rounded-lg px-4 py-2 flex items-center space-x-2 transition shadow-lg z-40"
        >
            <span class="text-cyan-400">💬</span>
            <span class="text-sm text-gray-300">GLOBAL</span>
            <span class="bg-cyan-600 text-white text-xs px-2 py-0.5 rounded-full">Chat</span>
        </Link>
    </div>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
    height: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: #21262d;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #30363d;
    border-radius: 2px;
}
</style>
