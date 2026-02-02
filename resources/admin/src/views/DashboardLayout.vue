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

        <div class="nav-section">User Management</div>
        <router-link to="/users" class="nav-item">
          <span class="icon">👥</span>
          <span class="label">Users</span>
        </router-link>
        <router-link to="/roles" class="nav-item">
          <span class="icon">🔐</span>
          <span class="label">Roles & Permissions</span>
        </router-link>
        <router-link to="/ip-bans" class="nav-item">
          <span class="icon">🚫</span>
          <span class="label">IP Bans</span>
        </router-link>

        <div class="nav-section">Game Configuration</div>
        <router-link to="/settings" class="nav-item">
          <span class="icon">⚙️</span>
          <span class="label">Settings</span>
        </router-link>        <router-link to="/module-settings" class="nav-item">
          <span class="icon">🎮</span>
          <span class="label">Modules</span>
        </router-link>        <router-link to="/locations" class="nav-item">
          <span class="icon">📍</span>
          <span class="label">Locations</span>
        </router-link>
        <router-link to="/ranks" class="nav-item">
          <span class="icon">🏅</span>
          <span class="label">Ranks</span>
        </router-link>

        <div class="nav-section">Crime System</div>
        <router-link to="/crimes" class="nav-item">
          <span class="icon">🎯</span>
          <span class="label">Crimes</span>
        </router-link>
        <router-link to="/organized-crimes" class="nav-item">
          <span class="icon">🏴</span>
          <span class="label">Organized Crimes</span>
        </router-link>
        <router-link to="/theft-types" class="nav-item">
          <span class="icon">🚗</span>
          <span class="label">Theft Types</span>
        </router-link>

        <div class="nav-section">Economy</div>
        <router-link to="/drugs" class="nav-item">
          <span class="icon">💊</span>
          <span class="label">Drugs</span>
        </router-link>
        <router-link to="/items" class="nav-item">
          <span class="icon">🎒</span>
          <span class="label">Items</span>
        </router-link>
        <router-link to="/properties" class="nav-item">
          <span class="icon">🏠</span>
          <span class="label">Properties</span>
        </router-link>
        <router-link to="/cars" class="nav-item">
          <span class="icon">🏎️</span>
          <span class="label">Cars</span>
        </router-link>
        <router-link to="/memberships" class="nav-item">
          <span class="icon">💎</span>
          <span class="label">Memberships</span>
        </router-link>
        <router-link to="/companies" class="nav-item">
          <span class="icon">🏢</span>
          <span class="label">Companies</span>
        </router-link>
        <router-link to="/job-positions" class="nav-item">
          <span class="icon">💼</span>
          <span class="label">Job Positions</span>
        </router-link>
        <router-link to="/courses" class="nav-item">
          <span class="icon">🎓</span>
          <span class="label">Education</span>
        </router-link>
        <router-link to="/stocks" class="nav-item">
          <span class="icon">📈</span>
          <span class="label">Stock Market</span>
        </router-link>
        <router-link to="/casino-games" class="nav-item">
          <span class="icon">🎰</span>
          <span class="label">Casino Games</span>
        </router-link>
        <router-link to="/lotteries" class="nav-item">
          <span class="icon">🎟️</span>
          <span class="label">Lotteries</span>
        </router-link>

        <div class="nav-section">Combat & Social</div>
        <router-link to="/bounties" class="nav-item">
          <span class="icon">💰</span>
          <span class="label">Bounties</span>
        </router-link>
        <router-link to="/gangs" class="nav-item">
          <span class="icon">👥</span>
          <span class="label">Gangs</span>
        </router-link>
        <router-link to="/combat-logs" class="nav-item">
          <span class="icon">⚔️</span>
          <span class="label">Combat Logs</span>
        </router-link>
        <router-link to="/races" class="nav-item">
          <span class="icon">🏁</span>
          <span class="label">Races</span>
        </router-link>

        <div class="nav-section">Progression</div>
        <router-link to="/missions" class="nav-item">
          <span class="icon">🎯</span>
          <span class="label">Missions</span>
        </router-link>
        <router-link to="/achievements" class="nav-item">
          <span class="icon">🏆</span>
          <span class="label">Achievements</span>
        </router-link>

        <div class="nav-section">Content</div>
        <router-link to="/announcements" class="nav-item">
          <span class="icon">📢</span>
          <span class="label">Announcements</span>
        </router-link>
        <router-link to="/faq" class="nav-item">
          <span class="icon">❓</span>
          <span class="label">FAQ</span>
        </router-link>
        <router-link to="/wiki" class="nav-item">
          <span class="icon">📖</span>
          <span class="label">Wiki</span>
        </router-link>
        <router-link to="/forum-categories" class="nav-item">
          <span class="icon">💬</span>
          <span class="label">Forum Categories</span>
        </router-link>

        <div class="nav-section">Support</div>
        <router-link to="/tickets" class="nav-item">
          <span class="icon">🎫</span>
          <span class="label">Tickets</span>
        </router-link>

        <div class="nav-section">System</div>
        <router-link to="/user-timers" class="nav-item">
          <span class="icon">⏱️</span>
          <span class="label">User Timers</span>
        </router-link>
        <router-link to="/error-logs" class="nav-item">
          <span class="icon">📝</span>
          <span class="label">Error Logs</span>
        </router-link>

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

// Load saved sidebar state from localStorage
onMounted(() => {
  const savedState = localStorage.getItem('sidebar_collapsed')
  if (savedState !== null) {
    sidebarCollapsed.value = savedState === 'true'
  }
})

const user = computed(() => {
  const userData = localStorage.getItem('admin_user')
  return userData ? JSON.parse(userData) : null
})

const pageTitle = computed(() => {
  const titles = {
    '/dashboard': 'Dashboard Overview'
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
  width: 260px;
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
  width: 70px;
}

.sidebar-header {
  padding: 2rem 1.5rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  position: relative;
}

.sidebar.collapsed .sidebar-header {
  padding: 1rem 0.5rem;
  flex-direction: column;
  gap: 0.5rem;
}

.sidebar-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #ffffff;
  text-align: center;
  transition: opacity 0.2s;
}

.sidebar-header .collapsed-logo {
  font-size: 2rem;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.sidebar.collapsed .sidebar-nav {
  padding: 0.5rem 0.25rem;
}

.nav-section {
  padding: 0.75rem 1rem 0.5rem;
  color: #64748b;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-top: 0.5rem;
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
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  border-radius: 0.5rem;
  color: #94a3b8;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s;
  background: transparent;
  border: none;
  width: 100%;
  cursor: pointer;
  font-size: 0.95rem;
  position: relative;
}

.sidebar.collapsed .nav-item {
  justify-content: center;
  padding: 0.875rem 0.5rem;
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
  font-size: 1.25rem;
}

.nav-divider {
  height: 1px;
  background: rgba(148, 163, 184, 0.1);
  margin: 0.5rem 0;
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
  margin-left: 260px;
  display: flex;
  flex-direction: column;
  transition: margin-left 0.3s ease;
}

.main-content.sidebar-collapsed {
  margin-left: 70px;
}

.header {
  padding: 2rem;
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
  font-size: 1.875rem;
  color: #ffffff;
}

.user-info {
  color: #94a3b8;
  font-weight: 500;
}

.content {
  flex: 1;
  padding: 2rem;
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
