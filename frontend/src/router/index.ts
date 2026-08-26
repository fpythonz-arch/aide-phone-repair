import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  // ── Authentification ──────────────────────────────────────
  { path: '/login', name: 'login', component: () => import('@/views/LoginView.vue'), meta: { title: 'Connexion', public: true } },

  // ── Atelier ───────────────────────────────────────────────
  { path: '/',                     name: 'dashboard',         component: () => import('@/views/AtelierDashboard.vue'),    meta: { title: 'Tableau de bord' } },
  { path: '/reparations',          name: 'reparations',       component: () => import('@/views/ReparationsView.vue'),     meta: { title: 'Réparations' } },
  { path: '/reparations/nouvelle', name: 'reparation-new',    component: () => import('@/views/ReparationFormView.vue'),  meta: { title: 'Nouvelle réparation' } },
  { path: '/reparations/:id',      name: 'reparation-detail', component: () => import('@/views/ReparationDetailView.vue'),meta: { title: 'Réparation' } },
  { path: '/reparations/:id/edit', name: 'reparation-edit',   component: () => import('@/views/ReparationFormView.vue'),  meta: { title: 'Modifier réparation' } },
  { path: '/diagnostic',           name: 'diagnostic',        component: () => import('@/views/DiagnosticView.vue'),      meta: { title: 'Diagnostic' } },

  // ── Connaissances ─────────────────────────────────────────
  { path: '/depannage',                name: 'depannage',        component: () => import('@/views/DepannageView.vue'),       meta: { title: 'Dépannage' } },
  { path: '/depannage/:type',          name: 'depannage-detail', component: () => import('@/views/DepannageDetailView.vue'), meta: { title: 'Dépannage' } },
  { path: '/depannage/:type/repair',   name: 'depannage-repair', component: () => import('@/views/RepairView.vue'),          meta: { title: 'Guide de réparation' } },
  { path: '/depannage/:type/guide',    name: 'depannage-guide',  component: () => import('@/views/FullGuideView.vue'),       meta: { title: 'Guide complet' } },
  { path: '/composants',          name: 'composants',   component: () => import('@/views/ComposantsView.vue'),   meta: { title: 'Composants' } },
  { path: '/codes-secrets',       name: 'codes-secrets',component: () => import('@/views/CodesSecretsView.vue'), meta: { title: 'Codes secrets' } },
  { path: '/ressources',          name: 'ressources',   component: () => import('@/views/RessourcesView.vue'),   meta: { title: 'Ressources' } },

  // ── Outils & Performance ──────────────────────────────────
  { path: '/outils',    name: 'outils',    component: () => import('@/views/OutilsView.vue'),   meta: { title: 'Outils' } },
  { path: '/evolution', name: 'evolution', component: () => import('@/views/EvolutionView.vue'), meta: { title: 'Évolution' } },

  // ── Fallback ──────────────────────────────────────────────
  { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('@/views/NotFoundView.vue'), meta: { title: 'Page introuvable', public: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { top: 0, behavior: 'smooth' }
  },
})

function isAuthenticated(): boolean {
  try {
    return !!(localStorage.getItem('ap_session') || sessionStorage.getItem('ap_session'))
  } catch {
    return false
  }
}

router.beforeEach((to) => {
  document.title = `${to.meta.title as string || 'Aide Phone'} — Aide Phone`

  const publicRoute = to.meta.public === true
  const authenticated = isAuthenticated()

  // Redirige vers login si non connecté et route protégée
  if (!publicRoute && !authenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  // Redirige vers dashboard si déjà connecté et tente d'accéder au login
  if (to.name === 'login' && authenticated) {
    return { name: 'dashboard' }
  }
})

export default router
