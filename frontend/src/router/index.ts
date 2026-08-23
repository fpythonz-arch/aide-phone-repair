import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'dashboard',
    component: () => import('@/views/AtelierDashboard.vue'),
    meta: { title: 'Tableau de bord' },
  },
  {
    path: '/diagnostic',
    name: 'diagnostic',
    component: () => import('@/views/DiagnosticView.vue'),
    meta: { title: 'Nouveau Diagnostic' },
  },
  {
    path: '/depannage',
    name: 'depannage',
    component: () => import('@/views/DepannageView.vue'),
    meta: { title: 'Dépannage' },
  },
  {
    path: '/depannage/:type',
    name: 'depannage-detail',
    component: () => import('@/views/DepannageDetailView.vue'),
    meta: { title: 'Dépannage Détail' },
  },
  {
    path: '/depannage/:type/repair',
    name: 'depannage-repair',
    component: () => import('@/views/RepairView.vue'),
    meta: { title: 'Guide de Réparation' },
  },
  {
    path: '/depannage/:type/guide',
    name: 'depannage-guide',
    component: () => import('@/views/FullGuideView.vue'),
    meta: { title: 'Guide Complet' },
  },
  {
    path: '/composants',
    name: 'composants',
    component: () => import('@/views/ComposantsView.vue'),
    meta: { title: 'Composants du Téléphone' },
  },
  {
    path: '/evolution',
    name: 'evolution',
    component: () => import('@/views/EvolutionView.vue'),
    meta: { title: 'Évolution des Téléphones' },
  },
  {
    path: '/ressources',
    name: 'ressources',
    component: () => import('@/views/RessourcesView.vue'),
    meta: { title: 'Ressources & Apprentissage' },
  },
  {
    path: '/outils',
    name: 'outils',
    component: () => import('@/views/OutilsView.vue'),
    meta: { title: 'Boîte à Outils Pro' },
  },
  {
    path: '/codes-secrets',
    name: 'codes-secrets',
    component: () => import('@/views/CodesSecretsView.vue'),
    meta: { title: 'Codes Secrets par Modèle' },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/AtelierDashboard.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  document.title = `${to.meta.title as string || 'Aide Phone'} - Réparation`
})

export default router