import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import DashboardLayout from '../views/DashboardLayout.vue'
import DashboardHome from '../views/DashboardHome.vue'
import UsersView from '../views/UsersView.vue'

const router = createRouter({
  history: createWebHistory('/admin'),
  routes: [
    {
      path: '/',
      redirect: '/login'
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true }
    },
    {
      path: '/dashboard',
      component: DashboardLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'dashboard',
          component: DashboardHome
        },
        {
          path: '/users',
          name: 'users',
          component: UsersView
        },
        {
          path: '/roles',
          name: 'roles',
          component: () => import('../views/RolesView.vue')
        },
        {
          path: '/settings',
          name: 'settings',
          component: () => import('../views/SettingsView.vue')
        },
        {
          path: '/locations',
          name: 'locations',
          component: () => import('../views/LocationsView.vue')
        },
        {
          path: '/ranks',
          name: 'ranks',
          component: () => import('../views/RanksView.vue')
        },
        {
          path: '/crimes',
          name: 'crimes',
          component: () => import('../views/CrimesManagementView.vue')
        },
        {
          path: '/organized-crimes',
          name: 'organized-crimes',
          component: () => import('../views/OrganizedCrimesView.vue')
        },
        {
          path: '/drugs',
          name: 'drugs',
          component: () => import('../views/DrugsManagementView.vue')
        },
        {
          path: '/items',
          name: 'items',
          component: () => import('../views/ItemsView.vue')
        },
        {
          path: '/properties',
          name: 'properties',
          component: () => import('../views/PropertiesView.vue')
        },
        {
          path: '/cars',
          name: 'cars',
          component: () => import('../views/CarsView.vue')
        },
        {
          path: '/bounties',
          name: 'bounties',
          component: () => import('../views/BountiesView.vue')
        },
        {
          path: '/gangs',
          name: 'gangs',
          component: () => import('../views/GangsView.vue')
        },
        {
          path: '/announcements',
          name: 'announcements',
          component: () => import('../views/AnnouncementsView.vue')
        },
        {
          path: '/faq',
          name: 'faq',
          component: () => import('../views/FaqView.vue')
        },
        {
          path: '/wiki',
          name: 'wiki',
          component: () => import('../views/WikiView.vue')
        },
        {
          path: '/tickets',
          name: 'tickets',
          component: () => import('../views/TicketsView.vue')
        },
        {
          path: '/error-logs',
          name: 'error-logs',
          component: () => import('../views/ErrorLogsView.vue')
        }
      ]
    }
  ]
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('admin_token')
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest)

  if (requiresAuth && !token) {
    next('/login')
  } else if (requiresGuest && token) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
