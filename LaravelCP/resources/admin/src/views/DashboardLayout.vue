<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>⚙️ LaravelCP</h2>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/dashboard" class="nav-item">
          <span class="icon">📊</span>
          <span>Dashboard</span>
        </router-link>

        <div class="nav-section">User Management</div>
        <router-link to="/users" class="nav-item">
          <span class="icon">👥</span>
          <span>Users</span>
        </router-link>
        <router-link to="/roles" class="nav-item">
          <span class="icon">🔐</span>
          <span>Roles & Permissions</span>
        </router-link>

        <div class="nav-section">Game Configuration</div>
        <router-link to="/modules" class="nav-item">
          <span class="icon">🧩</span>
          <span>Modules</span>
        </router-link>
        <router-link to="/settings" class="nav-item">
          <span class="icon">⚙️</span>
          <span>Settings</span>
        </router-link>
        <router-link to="/locations" class="nav-item">
          <span class="icon">📍</span>
          <span>Locations</span>
        </router-link>
        <router-link to="/ranks" class="nav-item">
          <span class="icon">🏅</span>
          <span>Ranks</span>
        </router-link>

        <div class="nav-section">Crime System</div>
        <router-link to="/crimes" class="nav-item">
          <span class="icon">🎯</span>
          <span>Crimes</span>
        </router-link>
        <router-link to="/organized-crimes" class="nav-item">
          <span class="icon">🏴</span>
          <span>Organized Crimes</span>
        </router-link>

        <div class="nav-section">Economy</div>
        <router-link to="/drugs" class="nav-item">
          <span class="icon">💊</span>
          <span>Drugs</span>
        </router-link>
        <router-link to="/items" class="nav-item">
          <span class="icon">🎒</span>
          <span>Items</span>
        </router-link>
        <router-link to="/properties" class="nav-item">
          <span class="icon">🏠</span>
          <span>Properties</span>
        </router-link>
        <router-link to="/cars" class="nav-item">
          <span class="icon">🚗</span>
          <span>Cars</span>
        </router-link>

        <div class="nav-section">Combat & Social</div>
        <router-link to="/bounties" class="nav-item">
          <span class="icon">💰</span>
          <span>Bounties</span>
        </router-link>
        <router-link to="/gangs" class="nav-item">
          <span class="icon">👥</span>
          <span>Gangs</span>
        </router-link>

        <div class="nav-section">Content</div>
        <router-link to="/announcements" class="nav-item">
          <span class="icon">📢</span>
          <span>Announcements</span>
        </router-link>
        <router-link to="/faq" class="nav-item">
          <span class="icon">❓</span>
          <span>FAQ</span>
        </router-link>
        <router-link to="/wiki" class="nav-item">
          <span class="icon">📖</span>
          <span>Wiki</span>
        </router-link>

        <div class="nav-section">Support</div>
        <router-link to="/tickets" class="nav-item">
          <span class="icon">🎫</span>
          <span>Tickets</span>
        </router-link>

        <div class="nav-section">System</div>
        <router-link to="/error-logs" class="nav-item">
          <span class="icon">📝</span>
          <span>Error Logs</span>
        </router-link>

        <div class="nav-divider"></div>

        <button @click="logout" class="nav-item logout">
          <span class="icon">🚪</span>
          <span>Logout</span>
        </button>
      </nav>
    </aside>

    <main class="main-content">
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
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

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
  background: #0f172a;
}

.sidebar {
  width: 260px;
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  border-right: 1px solid rgba(148, 163, 184, 0.1);
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  overflow-y: auto;
}

.sidebar-header {
  padding: 2rem 1.5rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.sidebar-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #ffffff;
  text-align: center;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.nav-section {
  padding: 0.75rem 1rem 0.5rem;
  color: #64748b;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-top: 0.5rem;
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
}

.nav-item:hover {
  background: rgba(148, 163, 184, 0.1);
  color: #ffffff;
}

.nav-item.router-link-active {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
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
}

.header {
  padding: 2rem;
  background: rgba(30, 41, 59, 0.5);
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
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
</style>
