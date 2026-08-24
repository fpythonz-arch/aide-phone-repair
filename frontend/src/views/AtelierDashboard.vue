<template>
  <div class="animate-fade-in">

    <div class="page-header">
      <div>
        <h1 class="page-title">Tableau de bord</h1>
        <p class="page-subtitle">Centre de contrôle de votre atelier</p>
      </div>
      <router-link to="/diagnostic" class="btn btn-primary">
        <PlusIcon class="w-4 h-4" />Nouveau diagnostic
      </router-link>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
      <div class="stat-card">
        <div class="stat-icon bg-blue-50 text-blue-600"><WrenchScrewdriverIcon class="w-5 h-5" /></div>
        <div><p class="stat-label">En cours</p><p class="stat-value">{{ stats.active }}</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-amber-50 text-amber-600"><ClockIcon class="w-5 h-5" /></div>
        <div><p class="stat-label">En attente</p><p class="stat-value">{{ stats.pending }}</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-green-50 text-green-600"><CheckCircleIcon class="w-5 h-5" /></div>
        <div><p class="stat-label">Terminées</p><p class="stat-value">{{ stats.completed }}</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-red-50 text-red-600"><ExclamationCircleIcon class="w-5 h-5" /></div>
        <div><p class="stat-label">Urgentes</p><p class="stat-value">{{ stats.urgent }}</p></div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

      <!-- Colonne principale -->
      <div class="lg:col-span-2 space-y-4">

        <!-- Réparations actives -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              <WrenchScrewdriverIcon class="w-4 h-4 text-blue-500" />Réparations en cours
            </h2>
            <router-link to="/reparations" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">Voir tout →</router-link>
          </div>
          <div v-if="activeRepairs.length === 0" class="empty-state py-8">
            <WrenchScrewdriverIcon class="empty-state-icon" />
            <p class="empty-state-title">Aucune réparation en cours</p>
            <p class="empty-state-description">Créez votre première réparation pour démarrer.</p>
            <router-link to="/reparations/nouvelle" class="btn btn-primary btn-sm mt-3">
              <PlusIcon class="w-4 h-4" />Créer une réparation
            </router-link>
          </div>
          <div v-else class="divide-y divide-gray-100 dark:divide-slate-700">
            <div v-for="r in activeRepairs.slice(0, 5)" :key="r.id" class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
              <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                <DevicePhoneMobileIcon class="w-5 h-5 text-gray-500 dark:text-gray-400" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ r.device_brand }} {{ r.device_model }}</p>
                  <span :class="['badge text-xs', priorityBadge(r.priority)]">{{ priorityLabel(r.priority) }}</span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ r.client_name }} · {{ r.problem_description }}</p>
              </div>
              <div class="flex flex-col items-end gap-1 flex-shrink-0">
                <span :class="['badge text-xs', statusBadge(r.status)]">{{ statusLabel(r.status) }}</span>
                <span class="text-xs text-gray-400">{{ fmtDate(r.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions rapides -->
        <div class="card">
          <h2 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <BoltIcon class="w-4 h-4 text-amber-500" />Actions rapides
          </h2>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <router-link v-for="a in quickActions" :key="a.to" :to="a.to"
              class="flex flex-col items-center gap-2 p-3 rounded-lg border border-gray-100 dark:border-slate-700 hover:border-blue-200 dark:hover:border-blue-800 hover:bg-blue-50/50 dark:hover:bg-blue-900/20 transition-all text-center group">
              <div :class="['w-9 h-9 rounded-lg flex items-center justify-center', a.bg]">
                <component :is="a.icon" :class="['w-5 h-5', a.color]" />
              </div>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 leading-tight">{{ a.label }}</span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- Colonne latérale -->
      <div class="space-y-4">

        <!-- Activité récente -->
        <div class="card">
          <h2 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <ClockIcon class="w-4 h-4 text-gray-400" />Activité récente
          </h2>
          <div v-if="recentActivity.length === 0" class="empty-state py-6">
            <ClockIcon class="empty-state-icon" />
            <p class="empty-state-title">Aucune activité</p>
            <p class="empty-state-description">Les actions récentes apparaîtront ici.</p>
          </div>
          <div v-else class="space-y-3">
            <div v-for="a in recentActivity" :key="a.id" class="flex items-start gap-2.5">
              <div :class="['w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5', a.iconBg]">
                <component :is="a.icon" :class="['w-3.5 h-3.5', a.iconColor]" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-900 dark:text-gray-100 leading-snug">{{ a.title }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ a.time }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Guides populaires -->
        <div class="card">
          <h2 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <BookOpenIcon class="w-4 h-4 text-gray-400" />Guides populaires
          </h2>
          <div class="space-y-1">
            <router-link v-for="g in popularGuides" :key="g.slug" :to="`/depannage/${g.slug}`"
              class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors group">
              <div :class="['w-7 h-7 rounded-md flex items-center justify-center flex-shrink-0', g.bg]">
                <component :is="g.icon" :class="['w-4 h-4', g.color]" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate">{{ g.name }}</p>
                <p class="text-xs text-gray-400">{{ g.type }}</p>
              </div>
              <ChevronRightIcon class="w-3.5 h-3.5 text-gray-300 group-hover:text-blue-400 flex-shrink-0" />
            </router-link>
          </div>
          <router-link to="/depannage" class="btn btn-secondary btn-sm w-full mt-3">Tous les guides</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import {
  WrenchScrewdriverIcon, ClockIcon, CheckCircleIcon, ExclamationCircleIcon, PlusIcon,
  BoltIcon, BookOpenIcon, ChevronRightIcon, DevicePhoneMobileIcon,
  MagnifyingGlassIcon, CpuChipIcon, HashtagIcon
} from '@heroicons/vue/24/outline'
import { useRepairs } from '@/composables/useRepairs'
import type { Repair, RepairStatus, RepairPriority } from '@/types'

const { repairs, stats, filterRepairs } = useRepairs()

const activeRepairs = computed(() =>
  filterRepairs({ status: 'all' }).filter(r => !['delivered', 'cancelled'].includes(r.status))
)

const recentActivity = computed(() =>
  [...repairs.value]
    .sort((a, b) => new Date(b.updated_at).getTime() - new Date(a.updated_at).getTime())
    .slice(0, 4)
    .map(r => ({
      id: r.id,
      title: `${r.device_brand} ${r.device_model} — ${statusLabel(r.status)}`,
      time: fmtRelative(r.updated_at),
      icon: ['ready', 'delivered'].includes(r.status) ? CheckCircleIcon : WrenchScrewdriverIcon,
      iconBg: ['ready', 'delivered'].includes(r.status) ? 'bg-green-100 dark:bg-green-900/30' : 'bg-blue-100 dark:bg-blue-900/30',
      iconColor: ['ready', 'delivered'].includes(r.status) ? 'text-green-600' : 'text-blue-600',
    }))
)

const quickActions = [
  { to: '/reparations/nouvelle', label: 'Nouvelle réparation', icon: WrenchScrewdriverIcon, bg: 'bg-blue-50 dark:bg-blue-900/30', color: 'text-blue-600' },
  { to: '/diagnostic',           label: 'Diagnostic',          icon: MagnifyingGlassIcon,   bg: 'bg-indigo-50 dark:bg-indigo-900/30', color: 'text-indigo-600' },
  { to: '/composants',           label: 'Composants',          icon: CpuChipIcon,            bg: 'bg-green-50 dark:bg-green-900/30', color: 'text-green-600' },
  { to: '/codes-secrets',        label: 'Codes secrets',       icon: HashtagIcon,            bg: 'bg-purple-50 dark:bg-purple-900/30', color: 'text-purple-600' },
]

const popularGuides = [
  { slug: 'batterie', name: 'Remplacement batterie',  type: 'Hardware', icon: BoltIcon,               bg: 'bg-green-50 dark:bg-green-900/30',  color: 'text-green-600' },
  { slug: 'ecran',    name: "Réparation écran",        type: 'Hardware', icon: DevicePhoneMobileIcon,  bg: 'bg-blue-50 dark:bg-blue-900/30',    color: 'text-blue-600' },
  { slug: 'charge',   name: 'Connecteur de charge',   type: 'Hardware', icon: WrenchScrewdriverIcon,  bg: 'bg-amber-50 dark:bg-amber-900/30',  color: 'text-amber-600' },
  { slug: 'camera',   name: 'Module caméra',           type: 'Hardware', icon: CpuChipIcon,            bg: 'bg-purple-50 dark:bg-purple-900/30',color: 'text-purple-600' },
]

// ── Helpers ──────────────────────────────────────────────────
const STATUS_LABELS: Record<string, string> = { new: 'Nouveau', received: 'Reçu', diagnosing: 'Diagnostic', waiting_quote: 'Devis', quote_accepted: 'Accepté', in_progress: 'En cours', waiting_parts: 'Pièces', testing: 'Tests', ready: 'Prêt', delivered: 'Livré', cancelled: 'Annulé' }
const STATUS_BADGE: Record<string, string>  = { new: 'badge-gray', received: 'badge-blue', diagnosing: 'badge-blue', waiting_quote: 'badge-yellow', quote_accepted: 'badge-purple', in_progress: 'badge-blue', waiting_parts: 'badge-orange', testing: 'badge-purple', ready: 'badge-green', delivered: 'badge-green', cancelled: 'badge-gray' }
const PRIORITY_LABELS: Record<string, string> = { urgent: 'Urgent', high: 'Élevée', normal: 'Normale', low: 'Faible' }
const PRIORITY_BADGE: Record<string, string>  = { urgent: 'badge-red', high: 'badge-orange', normal: 'badge-blue', low: 'badge-gray' }

function statusLabel(s: string) { return STATUS_LABELS[s] ?? s }
function statusBadge(s: string) { return STATUS_BADGE[s] ?? 'badge-gray' }
function priorityLabel(p: string) { return PRIORITY_LABELS[p] ?? p }
function priorityBadge(p: string) { return PRIORITY_BADGE[p] ?? 'badge-gray' }
function fmtDate(d: string) { try { return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' }) } catch { return '' } }
function fmtRelative(d: string) {
  try {
    const diff = Date.now() - new Date(d).getTime(), m = Math.floor(diff / 60000)
    if (m < 1) return "À l'instant"
    if (m < 60) return `Il y a ${m} min`
    const h = Math.floor(m / 60)
    if (h < 24) return `Il y a ${h}h`
    return `Il y a ${Math.floor(h / 24)}j`
  } catch { return '' }
}
</script>
