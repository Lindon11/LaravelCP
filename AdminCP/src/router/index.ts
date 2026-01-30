import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import AdminLayout from '../views/AdminLayout.vue'
import DashboardView from '../views/admin/DashboardView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/admin'
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true }
    },
    {
      path: '/admin',
      component: AdminLayout,
      meta: { requiresAuth: true, requiresAdmin: true },
      children: [
        {
          path: '',
          name: 'admin-dashboard',
          component: DashboardView
        },
        {
          path: 'users',
          name: 'admin-users',
          component: () => import('../views/admin/UsersView.vue')
        },
        {
          path: 'settings',
          name: 'admin-settings',
          component: () => import('../views/admin/SettingsView.vue')
        },
        {
          path: 'modules',
          name: 'admin-modules',
          component: () => import('../views/admin/ModulesView.vue')
        },
        {
          path: 'logs',
          name: 'admin-logs',
          component: () => import('../views/admin/LogsView.vue')
        }
      ]
    }
  ]
})
import GymView from '../views/modules/GymView.vue'
import HospitalView from '../views/modules/HospitalView.vue'
import BankView from '../views/modules/BankView.vue'
import TravelView from '../views/modules/TravelView.vue'
import DrugsView from '../views/modules/DrugsView.vue'
import TheftView from '../views/modules/TheftView.vue'
import RacingView from '../views/modules/RacingView.vue'
import JailView from '../views/modules/JailView.vue'
import InventoryView from '../views/modules/InventoryView.vue'
import PropertiesView from '../views/modules/PropertiesView.vue'
import CombatView from '../views/modules/CombatView.vue'
import BountyView from '../views/modules/BountyView.vue'
import DetectiveView from '../views/modules/DetectiveView.vue'
import BulletsView from '../views/modules/BulletsView.vue'
import GangView from '../views/modules/GangView.vue'
import MissionsView from '../views/modules/MissionsView.vue'
import AchievementsView from '../views/modules/AchievementsView.vue'
import LeaderboardsView from '../views/modules/LeaderboardsView.vue'
import ForumView from '../views/modules/ForumView.vue'
import OrganizedCrimeView from '../views/modules/OrganizedCrimeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
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
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { requiresGuest: true }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: DashboardView,
      meta: { requiresAuth: true }
    },
    {
      path: '/crimes',
      name: 'crimes',
      component: CrimesView,
      meta: { requiresAuth: true }
    },
    {
      path: '/gym',
      name: 'gym',
      component: GymView,
      meta: { requiresAuth: true }
    },
    {
      path: '/hospital',
      name: 'hospital',
      component: HospitalView,
      meta: { requiresAuth: true }
    },
    {
      path: '/bank',
      name: 'bank',
      component: BankView,
      meta: { requiresAuth: true }
    },
    {
      path: '/travel',
      name: 'travel',
      component: TravelView,
      meta: { requiresAuth: true }
    },
    {
      path: '/drugs',
      name: 'drugs',
      component: DrugsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/theft',
      name: 'theft',
      component: TheftView,
      meta: { requiresAuth: true }
    },
    {
      path: '/racing',
      name: 'racing',
      component: RacingView,
      meta: { requiresAuth: true }
    },
    {
      path: '/jail',
      name: 'jail',
      component: JailView,
      meta: { requiresAuth: true }
    },
    {
      path: '/inventory',
      name: 'inventory',
      component: InventoryView,
      meta: { requiresAuth: true }
    },
    {
      path: '/properties',
      name: 'properties',
      component: PropertiesView,
      meta: { requiresAuth: true }
    },
    {
      path: '/combat',
      name: 'combat',
      component: CombatView,
      meta: { requiresAuth: true }
    },
    {
      path: '/bounty',
      name: 'bounty',
      component: BountyView,
      meta: { requiresAuth: true }
    },
    {
      path: '/detective',
      name: 'detective',
      component: DetectiveView,
      meta: { requiresAuth: true }
    },
    {
      path: '/bullets',
      name: 'bullets',
      component: BulletsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/gang',
      name: 'gang',
      component: GangView,
      meta: { requiresAuth: true }
    },
    {
      path: '/missions',
      name: 'missions',
      component: MissionsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/achievements',
      name: 'achievements',
      component: AchievementsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/leaderboards',
      name: 'leaderboards',
      component: LeaderboardsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/forum',
      name: 'forum',
      component: ForumView,
      meta: { requiresAuth: true }
    },
    {
      path: '/organized-crime',
      name: 'organized-crime',
      component: OrganizedCrimeView,
      meta: { requiresAuth: true }
    },
  ],
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token')
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest)

  if (requiresAuth && !token) {
    next('/login')
  } else if (requiresGuest && token) {
    next('/admin')
  } else {
    next()
  }
})

export default router
