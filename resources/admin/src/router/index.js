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
    // Installer Routes
    {
      path: '/install',
      name: 'installer-welcome',
      component: () => import('../views/Installer/Welcome.vue'),
      meta: { requiresGuest: true, isInstaller: true }
    },
    {
      path: '/install/requirements',
      name: 'installer-requirements',
      component: () => import('../views/Installer/Requirements.vue'),
      meta: { requiresGuest: true, isInstaller: true }
    },
    {
      path: '/install/database',
      name: 'installer-database',
      component: () => import('../views/Installer/Database.vue'),
      meta: { requiresGuest: true, isInstaller: true }
    },
    {
      path: '/install/settings',
      name: 'installer-settings',
      component: () => import('../views/Installer/Settings.vue'),
      meta: { requiresGuest: true, isInstaller: true }
    },
    {
      path: '/install/setup-admin',
      name: 'installer-admin',
      component: () => import('../views/Installer/Admin.vue'),
      meta: { requiresGuest: true, isInstaller: true }
    },
    {
      path: '/install/install',
      name: 'installer-install',
      component: () => import('../views/Installer/Install.vue'),
      meta: { requiresGuest: true, isInstaller: true }
    },
    {
      path: '/install/complete',
      name: 'installer-complete',
      component: () => import('../views/Installer/Complete.vue'),
      meta: { requiresGuest: true, isInstaller: true }
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
          path: '/notifications',
          name: 'notifications',
          component: () => import('../views/NotificationsView.vue')
        },
        {
          path: '/calendar',
          name: 'calendar',
          component: () => import('../views/CalendarView.vue')
        },
        {
          path: '/tasks',
          name: 'tasks',
          component: () => import('../views/TasksView.vue')
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
          path: '/module-settings',
          name: 'module-settings',
          component: () => import('../views/ModulesView.vue')
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
          path: '/forum-categories',
          name: 'forum-categories',
          component: () => import('../views/ForumCategoriesView.vue')
        },
        {
          path: '/missions',
          name: 'missions',
          component: () => import('../views/MissionsView.vue')
        },
        {
          path: '/achievements',
          name: 'achievements',
          component: () => import('../views/AchievementsView.vue')
        },
        {
          path: '/theft-types',
          name: 'theft-types',
          component: () => import('../views/TheftTypesView.vue')
        },
        {
          path: '/memberships',
          name: 'memberships',
          component: () => import('../views/MembershipsView.vue')
        },
        {
          path: '/companies',
          name: 'companies',
          component: () => import('../views/CompaniesView.vue')
        },
        {
          path: '/job-positions',
          name: 'job-positions',
          component: () => import('../views/JobPositionsView.vue')
        },
        {
          path: '/courses',
          name: 'courses',
          component: () => import('../views/CoursesView.vue')
        },
        {
          path: '/stocks',
          name: 'stocks',
          component: () => import('../views/StocksView.vue')
        },
        {
          path: '/casino-games',
          name: 'casino-games',
          component: () => import('../views/CasinoGamesView.vue')
        },
        {
          path: '/lotteries',
          name: 'lotteries',
          component: () => import('../views/LotteriesView.vue')
        },
        {
          path: '/ip-bans',
          name: 'ip-bans',
          component: () => import('../views/IpBansView.vue')
        },
        {
          path: '/user-timers',
          name: 'user-timers',
          component: () => import('../views/UserTimersView.vue')
        },
        {
          path: '/combat-logs',
          name: 'combat-logs',
          component: () => import('../views/CombatLogsView.vue')
        },
        {
          path: '/combat-locations',
          name: 'combat-locations',
          component: () => import('../views/CombatLocationsView.vue')
        },
        {
          path: '/combat-areas',
          name: 'combat-areas',
          component: () => import('../views/CombatAreasView.vue')
        },
        {
          path: '/combat-enemies',
          name: 'combat-enemies',
          component: () => import('../views/CombatEnemiesView.vue')
        },
        {
          path: '/races',
          name: 'races',
          component: () => import('../views/RacesView.vue')
        },
        {
          path: '/tickets/:id',
          name: 'ticket-detail',
          component: () => import('../views/TicketDetailView.vue')
        },
        {
          path: '/tickets',
          name: 'tickets',
          component: () => import('../views/TicketListView.vue')
        },
        {
          path: '/ticket-categories',
          name: 'ticket-categories',
          component: () => import('../views/TicketCategoriesView.vue')
        },
        {
          path: '/error-logs',
          name: 'error-logs',
          component: () => import('../views/ErrorLogsView.vue')
        },
        {
          path: '/item-market',
          name: 'item-market',
          component: () => import('../views/ItemMarketView.vue')
        },
        {
          path: '/activity-logs',
          name: 'activity-logs',
          component: () => import('../views/ActivityLogsView.vue')
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
