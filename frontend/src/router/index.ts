import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  { path: '/',                    name: 'dashboard',         component: () => import('@/views/AtelierDashboard.vue'),    meta: { title: 'Tableau de bord' } },
  { path: '/reparations',         name: 'reparations',       component: () => import('@/views/ReparationsView.vue'),     meta: { title: 'Réparations' } },
  { path: '/reparations/nouvelle',name: 'reparation-new',    component: () => import('@/views/ReparationFormView.vue'),  meta: { title: 'Nouvelle réparation' } },
  { path: '/reparations/:id',     name: 'reparation-detail', component: () => import('@/views/ReparationDetailView.vue'),meta: { title: 'Réparation' } },
  { path: '/reparations/:id/edit',name: 'reparation-edit',   component: () => import('@/views/ReparationFormView.vue'),  meta: { title: 'Modifier réparation' } },
  { path: '/diagnostic',          name: 'diagnostic',        component: () => import('@/views/DiagnosticView.vue'),      meta: { title: 'Diagnostic' } },
  { path: '/depannage',           name: 'depannage',         component: () => import('@/views/DepannageView.vue'),       meta: { title: 'Dépannage' } },
  { path: '/depannage/:type',     name: 'depannage-detail',  component: () => import('@/views/DepannageDetailView.vue'), meta: { title: 'Dépannage' } },
  { path: '/depannage/:type/repair', name: 'depannage-repair', component: () => import('@/views/RepairView.vue'),        meta: { title: 'Guide de réparation' } },
  { path: '/depannage/:type/guide',  name: 'depannage-guide',  component: () => import('@/views/FullGuideView.vue'),     meta: { title: 'Guide complet' } },
  { path: '/composants',          name: 'composants',        component: () => import('@/views/ComposantsView.vue'),      meta: { title: 'Composants' } },
  { path: '/codes-secrets',       name: 'codes-secrets',     component: () => import('@/views/CodesSecretsView.vue'),    meta: { title: 'Codes secrets' } },
  { path: '/ressources',          name: 'ressources',        component: () => import('@/views/RessourcesView.vue'),      meta: { title: 'Ressources' } },
  { path: '/outils',              name: 'outils',            component: () => import('@/views/OutilsView.vue'),          meta: { title: 'Outils' } },
  { path: '/evolution',           name: 'evolution',         component: () => import('@/views/EvolutionView.vue'),       meta: { title: 'Évolution' } },
  { path: '/:pathMatch(.*)*',     name: 'not-found',         component: () => import('@/views/NotFoundView.vue'),        meta: { title: 'Page introuvable' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { top: 0, behavior: 'smooth' }
  },
})

router.beforeEach((to) => {
  document.title = `${to.meta.title as string || 'Aide Phone'} — Aide Phone`
})

export default router
