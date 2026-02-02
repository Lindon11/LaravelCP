<template>
  <div class="admin-layout">
    <button class="mobile-menu-toggle" @click="toggleMobileMenu" aria-label="Toggle menu">
      <span class="hamburger">☰</span>
    </button>

    <aside class="sidebar" :class="{ 'mobile-open': mobileMenuOpen, 'collapsed': sidebarCollapsed }">
      <div class="sidebar-header">
        <h2 v-show="!sidebarCollapsed">⚡ LaravelCP</h2>
        <h2 v-show="sidebarCollapsed" class="collapsed-logo">⚡</h2>
        <button class="sidebar-toggle" @click="toggleSidebarCollapse" aria-label="Toggle sidebar">
          <span v-if="sidebarCollapsed">→</span>
          <span v-else>←</span>
        </button>
        <button class="mobile-close" @click="closeMobileMenu" aria-label="Close menu">✕</button>
      </div>

      <nav class="sidebar-nav" @click="handleNavClick">
        <router-link to="/dashboard" class="nav-item">
          <span class="icon">📊</span>
          <span class="label">Dashboard</span>
        </router-link>

        <router-link to="/calendar" class="nav-item">
          <span class="icon">📅</span>
          <span class="label">Calendar</span>
        </router-link>

        <router-link to="/tasks" class="nav-item">
          <span class="icon">📋</span>
          <span class="label">Tasks</span>
        </router-link>

        <div v-for="section in menuSections" :key="section.id" class="nav-dropdown">
          <button
            class="nav-dropdown-toggle"
            :class="{ open: isMenuOpen(section.id) }"
            @click.stop="toggleMenu(section.id)"
          >
            <span class="icon">{{ section.icon }}</span>
            <span class="label">{{ section.label }}</span>
            <span class="arrow">{{ isMenuOpen(section.id) ? '▼' : '▶' }}</span>
          </button>

          <transition name="dropdown">
            <div v-show="isMenuOpen(section.id)" class="nav-dropdown-content">
              <router-link
                v-for="item in section.children"
                :key="item.path"
                :to="item.path"
                class="nav-item submenu"
              >
                <span class="icon">{{ item.icon }}</span>
                <span class="label">{{ item.label }}</span>
              </router-link>
            </div>
          </transition>
        </div>

        <div class="nav-divider"></div>

        <button @click="logout" class="nav-item logout">
          <span class="icon">🚪</span>
          <span class="label">Logout</span>
        </button>
      </nav>
    </aside>

    <main class="main-content" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
      <header class="header">
        <h1>{{ pageTitle }}</h1>
        <div class="user-info">
          <span>{{ user?.username || 'Admin' }}</span>
        </div>
      </header>

      <div class="content">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const mobileMenuOpen = ref(false)
const sidebarCollapsed = ref(false)
const openMenus = ref(new Set())

// Menu structure with dropdowns
const menuSections = [
  {
    id: 'user-management',
    label: 'User Management',
    icon: '👥',
    children: [
      { path: '/users', label: 'Users', icon: '👥' },
      { path: '/roles', label: 'Roles & Permissions', icon: '🔐' },
      { path: '/ip-bans', label: 'IP Bans', icon: '🚫' }
    ]
  },
  {
    id: 'game-config',
    label: 'Game Configuration',
    icon: '⚙️',
    children: [
      { path: '/settings', label: 'Settings', icon: '⚙️' },
      { path: '/module-settings', label: 'Modules', icon: '🎮' },
      { path: '/locations', label: 'Locations', icon: '📍' },
      { path: '/ranks', label: 'Ranks', icon: '🏅' }
    ]
  },
  {
    id: 'crime-system',
    label: 'Crime System',
    icon: '🎯',
    children: [
      { path: '/crimes', label: 'Crimes', icon: '🎯' },
      { path: '/organized-crimes', label: 'Organized Crimes', icon: '🏴' },
      { path: '/theft-types', label: 'Theft Types', icon: '🚗' }
    ]
  },
  {
    id: 'economy',
    label: 'Economy',
    icon: '💰',
    children: [
      { path: '/drugs', label: 'Drugs', icon: '💊' },
      { path: '/items', label: 'Items', icon: '🎒' },
      { path: '/properties', label: 'Properties', icon: '🏠' },
      { path: '/cars', label: 'Cars', icon: '🏎️' },
      { path: '/memberships', label: 'Memberships', icon: '💎' },
      { path: '/companies', label: 'Companies', icon: '🏢' },
      { path: '/job-positions', label: 'Job Positions', icon: '💼' },
      { path: '/courses', label: 'Education', icon: '🎓' },
      { path: '/stocks', label: 'Stock Market', icon: '📈' },
      { path: '/casino-games', label: 'Casino Games', icon: '🎰' },
      { path: '/lotteries', label: 'Lotteries', icon: '🎟️' }
    ]
  },
  {
    id: 'combat-social',
    label: 'Combat & Social',
    icon: '⚔️',
    children: [
      { path: '/combat-locations', label: 'Combat Locations', icon: '🎯' },
      { path: '/combat-areas', label: 'Combat Areas', icon: '🗺️' },
      { path: '/combat-enemies', label: 'Combat Enemies', icon: '👹' },
      { path: '/combat-logs', label: 'Combat Logs', icon: '⚔️' },
      { path: '/bounties', label: 'Bounties', icon: '💰' },
      { path: '/gangs', label: 'Gangs', icon: '👥' },
      { path: '/races', label: 'Races', icon: '🏁' }
    ]
  },
  {
    id: 'progression',
    label: 'Progression',
    icon: '🏆',
    children: [
      { path: '/missions', label: 'Missions', icon: '🎯' },
      { path: '/achievements', label: 'Achievements', icon: '🏆' }
    ]
  },
  {
    id: 'content',
    label: 'Content',
    icon: '📢',
    children: [
      { path: '/announcements', label: 'Announcements', icon: '📢' },
      { path: '/faq', label: 'FAQ', icon: '❓' },
      { path: '/wiki', label: 'Wiki', icon: '📖' },
      { path: '/forum-categories', label: 'Forum Categories', icon: '💬' }
    ]
  },
  {
    id: 'support',
    label: 'Support',
    icon: '🎫',
    children: [
      { path: '/tickets', label: 'Tickets', icon: '🎫' }
    ]
  },
  {
    id: 'system',
    label: 'System',
    icon: '🔧',
    children: [
      { path: '/user-timers', label: 'User Timers', icon: '⏱️' },
      { path: '/error-logs', label: 'Error Logs', icon: '📝' }
    ]
  }
]

// Load saved sidebar state and open menus from localStorage
onMounted(() => {
  const savedState = localStorage.getItem('sidebar_collapsed')
  if (savedState !== null) {
    sidebarCollapsed.value = savedState === 'true'
  }

  const savedMenus = localStorage.getItem('open_menus')
  if (savedMenus) {
    openMenus.value = new Set(JSON.parse(savedMenus))
  }

  // Auto-open menu containing current route
  const currentSection = menuSections.find(section =>
    section.children.some(child => child.path === route.path)
  )
  if (currentSection && !openMenus.value.has(currentSection.id)) {
    openMenus.value.add(currentSection.id)
    saveOpenMenus()
  }
})

const toggleMenu = (menuId) => {
  if (openMenus.value.has(menuId)) {
    openMenus.value.delete(menuId)
  } else {
    openMenus.value.add(menuId)
  }
  saveOpenMenus()
}

const saveOpenMenus = () => {
  localStorage.setItem('open_menus', JSON.stringify([...openMenus.value]))
}

const isMenuOpen = (menuId) => {
  return openMenus.value.has(menuId)
}

const user = computed(() => {
  const userData = localStorage.getItem('admin_user')
  return userData ? JSON.parse(userData) : null
})

const pageTitle = computed(() => {
  const titles = {
    '/dashboard': 'Dashboard Overview',
    '/calendar': 'Calendar',
    '/tasks': 'Tasks'
  }
  return titles[route.path] || 'Admin Panel'
})

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

const toggleSidebarCollapse = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('sidebar_collapsed', sidebarCollapsed.value.toString())
}

const handleNavClick = (e) => {
  // Close mobile menu when a nav link is clicked
  if (e.target.closest('.nav-item') || e.target.closest('button')) {
    closeMobileMenu()
  }
}

const logout = () => {
  localStorage.removeItem('admin_token')
  localStorage.removeItem('admin_user')
  router.push('/login')
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
}

.mobile-menu-toggle {
  display: none;
  position: fixed;
  top: 1rem;
  left: 1rem;
  z-index: 1001;
  background: rgba(30, 41, 59, 0.95);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #ffffff;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 1.5rem;
  line-height: 1;
  transition: all 0.2s;
}

.mobile-menu-toggle:hover {
  background: rgba(30, 41, 59, 1);
  transform: scale(1.05);
}

.mobile-menu-toggle .hamburger {
  display: block;
}

.mobile-close {
  display: none;
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
  transition: color 0.2s;
}

.mobile-close:hover {
  color: #ffffff;
}

.sidebar-toggle {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.5rem;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sidebar-toggle:hover {
  color: #ffffff;
  background: rgba(148, 163, 184, 0.1);
  border-radius: 0.25rem;
}

.sidebar {
  width: 220px;
  background: rgba(30, 41, 59, 0.95);
  border-right: 1px solid rgba(148, 163, 184, 0.1);
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  overflow-y: auto;
  transition: width 0.3s ease, transform 0.3s ease;
}

.sidebar.collapsed {
  width: 60px;
}

.sidebar-header {
  padding: 1rem 1rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  position: relative;
}

.sidebar.collapsed .sidebar-header {
  padding: 0.75rem 0.5rem;
  flex-direction: column;
  gap: 0.5rem;
}

.sidebar-header h2 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 700;
  color: #ffffff;
  text-align: center;
  transition: opacity 0.2s;
}

.sidebar-header .collapsed-logo {
  font-size: 1.5rem;
}

.sidebar-nav {
  flex: 1;
  padding: 0.5rem 0.375rem;
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.sidebar.collapsed .sidebar-nav {
  padding: 0.5rem 0.25rem;
}

.nav-section {
  padding: 0.5rem 0.75rem 0.25rem;
  color: #64748b;
  font-size: 0.625rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-top: 0.25rem;
  transition: opacity 0.2s;
}

.sidebar.collapsed .nav-section {
  opacity: 0;
  height: 0;
  padding: 0;
  margin: 0;
  overflow: hidden;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4rem 0.625rem;
  border-radius: 0.375rem;
  color: #94a3b8;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s;
  background: transparent;
  border: none;
  width: 100%;
  cursor: pointer;
  font-size: 0.8125rem;
  position: relative;
}

.sidebar.collapsed .nav-item {
  justify-content: center;
  padding: 0.625rem 0.375rem;
  gap: 0;
}

.sidebar.collapsed .nav-item .label {
  opacity: 0;
  position: absolute;
  width: 0;
  overflow: hidden;
  white-space: nowrap;
}

.nav-item:hover {
  background: rgba(148, 163, 184, 0.1);
  color: #ffffff;
}

.sidebar.collapsed .nav-item:hover::after {
  content: attr(data-label);
  position: absolute;
  left: 100%;
  margin-left: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: rgba(30, 41, 59, 0.95);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  white-space: nowrap;
  z-index: 1000;
  pointer-events: none;
  color: #ffffff;
  font-size: 0.875rem;
}

.nav-item.router-link-active {
  background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
  color: #ffffff;
}

.nav-item .icon {
  font-size: 1rem;
  width: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-divider {
  height: 1px;
  background: rgba(148, 163, 184, 0.1);
  margin: 0.5rem 0;
}

/* Dropdown Menu Styles */
.nav-dropdown {
  margin-bottom: 0.125rem;
}

.nav-dropdown-toggle {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  color: #94a3b8;
  background: transparent;
  border: none;
  width: 100%;
  cursor: pointer;
  font-size: 0.8125rem;
  font-weight: 600;
  transition: all 0.2s;
  position: relative;
}

.sidebar.collapsed .nav-dropdown-toggle {
  justify-content: center;
  padding: 0.875rem 0.5rem;
  gap: 0;
}

.sidebar.collapsed .nav-dropdown-toggle .label,
.sidebar.collapsed .nav-dropdown-toggle .arrow {
  opacity: 0;
  position: absolute;
  width: 0;
  overflow: hidden;
}

.nav-dropdown-toggle:hover {
  background: rgba(148, 163, 184, 0.1);
  color: #ffffff;
}

.nav-dropdown-toggle.open {
  color: #ffffff;
  background: rgba(148, 163, 184, 0.05);
}

.nav-dropdown-toggle .arrow {
  margin-left: auto;
  font-size: 0.5rem;
  transition: transform 0.2s;
  opacity: 0.6;
}

.nav-dropdown-toggle.open .arrow {
  transform: rotate(0deg);
}

.nav-dropdown-content {
  padding-left: 0.5rem;
  margin-top: 0.125rem;
  margin-bottom: 0.125rem;
  overflow: hidden;
}

.sidebar.collapsed .nav-dropdown-content {
  display: none;
}

.nav-item.submenu {
  padding: 0.375rem 0.625rem;
  padding-left: 0.75rem;
  font-size: 0.75rem;
  border-left: 2px solid rgba(148, 163, 184, 0.2);
  margin-left: 1.75rem;
  border-radius: 0 0.25rem 0.25rem 0;
}

.nav-item.submenu.router-link-active {
  border-left-color: #f59e0b;
}

/* Dropdown Animation */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.3s ease;
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-10px);
  max-height: 0;
}

.dropdown-enter-to {
  opacity: 1;
  transform: translateY(0);
  max-height: 1000px;
}

.dropdown-leave-from {
  opacity: 1;
  transform: translateY(0);
  max-height: 1000px;
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
  max-height: 0;
}

.nav-item.logout {
  margin-top: auto;
  color: #ef4444;
}

.nav-item.logout:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.main-content {
  flex: 1;
  margin-left: 220px;
  display: flex;
  flex-direction: column;
  transition: margin-left 0.3s ease;
}

.main-content.sidebar-collapsed {
  margin-left: 60px;
}

.header {
  padding: 1rem 1.5rem;
  background: rgba(30, 41, 59, 0.5);
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.header h1 {
  margin: 0;
  font-size: 1.375rem;
  color: #ffffff;
  font-weight: 600;
}

.user-info {
  color: #94a3b8;
  font-weight: 500;
  font-size: 0.875rem;
}

.content {
  flex: 1;
  padding: 1.5rem;
  overflow-y: auto;
}

/* Tablet and below */
@media (max-width: 1024px) {
  .sidebar:not(.collapsed) {
    width: 220px;
  }

  .main-content:not(.sidebar-collapsed) {
    margin-left: 220px;
  }

  .header h1 {
    font-size: 1.5rem;
  }

  .content {
    padding: 1.5rem;
  }
}

/* Mobile */
@media (max-width: 768px) {
  .mobile-menu-toggle {
    display: block;
  }

  .sidebar-toggle {
    display: none;
  }

  .sidebar {
    transform: translateX(-100%);
    z-index: 1000;
    width: 280px;
  }

  .sidebar.collapsed {
    width: 280px;
  }

  .sidebar.mobile-open {
    transform: translateX(0);
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
  }

  .sidebar.mobile-open::before {
    content: '';
    position: fixed;
    top: 0;
    left: 280px;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: -1;
  }

  .mobile-close {
    display: block;
  }

  .sidebar-header {
    justify-content: space-between;
    padding: 2rem 1.5rem;
    flex-direction: row;
  }

  .sidebar-header h2 {
    flex: 1;
  }

  .sidebar.collapsed .sidebar-header h2,
  .sidebar.collapsed .sidebar-header .collapsed-logo {
    display: block;
    opacity: 1;
  }

  .sidebar.collapsed .nav-item .label {
    opacity: 1;
    position: static;
    width: auto;
    overflow: visible;
  }

  .sidebar.collapsed .nav-section {
    opacity: 1;
    height: auto;
    padding: 0.75rem 1rem 0.5rem;
    margin-top: 0.5rem;
  }

  .sidebar.collapsed .nav-item {
    justify-content: flex-start;
    padding: 0.875rem 1rem;
    gap: 0.75rem;
  }

  .main-content,
  .main-content.sidebar-collapsed {
    margin-left: 0;
    width: 100%;
  }

  .header {
    padding: 1rem 1rem 1rem 4rem;
  }

  .header h1 {
    font-size: 1.25rem;
  }

  .content {
    padding: 1rem;
  }

  .nav-section {
    font-size: 0.7rem;
  }

  .nav-item {
    padding: 0.75rem 0.875rem;
    font-size: 0.9rem;
  }
}

/* Small mobile */
@media (max-width: 480px) {
  .sidebar {
    width: 260px;
  }

  .sidebar.mobile-open::before {
    left: 260px;
  }

  .header {
    padding: 0.875rem 0.875rem 0.875rem 3.5rem;
  }

  .header h1 {
    font-size: 1.1rem;
  }

  .user-info {
    font-size: 0.875rem;
  }

  .content {
    padding: 0.875rem;
  }
}

/* Landscape mobile */
@media (max-width: 768px) and (orientation: landscape) {
  .sidebar {
    width: 240px;
  }

  .sidebar-header {
    padding: 1rem 1rem;
  }

  .sidebar-nav {
    padding: 0.5rem;
  }

  .nav-item {
    padding: 0.625rem 0.75rem;
  }
}
</style>
