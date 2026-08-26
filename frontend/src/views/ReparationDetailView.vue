<template>
  <div class="animate-fade-in max-w-3xl mx-auto">
    <div v-if="loading" class="card">
      <div class="empty-state">
        <p class="empty-state-title">Chargement...</p>
      </div>
    </div>

    <div v-else-if="!repair" class="card">
      <div class="empty-state">
        <ExclamationCircleIcon class="empty-state-icon" />
        <p class="empty-state-title">Réparation introuvable</p>
        <p class="empty-state-description">Cette réparation n'existe pas ou a été supprimée.</p>
        <router-link to="/reparations" class="btn btn-primary btn-sm mt-3">Retour à la liste</router-link>
      </div>
    </div>

    <template v-else>
      <div class="page-header">
        <div>
          <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span class="font-mono text-sm text-gray-500 dark:text-gray-400">{{ repair.number }}</span>
            <span :class="['badge', statusBadge(repair.status)]">{{ statusLabel(repair.status) }}</span>
            <span :class="['badge', priorityBadge(repair.priority)]">{{ priorityLabel(repair.priority) }}</span>
          </div>
          <h1 class="page-title">{{ repair.device_brand }} {{ repair.device_model }}</h1>
          <p class="page-subtitle">{{ repair.client_name }} · {{ repair.client_phone }}</p>
        </div>
        <div class="flex gap-2">
          <router-link :to="`/reparations/${repair.id}/edit`" class="btn btn-secondary btn-sm"><PencilIcon class="w-4 h-4" />Modifier</router-link>
          <button class="btn btn-secondary btn-sm" @click="$router.back()"><ArrowLeftIcon class="w-4 h-4" /></button>
        </div>
      </div>

      <!-- Changer statut -->
      <div class="card mb-4">
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Changer le statut</p>
        <div class="flex flex-wrap gap-2">
          <button v-for="s in STATUS_FLOW" :key="s.v" :class="['btn btn-sm', repair.status === s.v ? 'btn-primary' : 'btn-secondary']" @click="changeStatus(s.v)">{{ s.l }}</button>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div class="card">
          <h2 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2"><UserIcon class="w-4 h-4 text-blue-500" />Client</h2>
          <dl class="space-y-2">
            <div class="flex justify-between text-sm"><dt class="text-gray-500">Nom</dt><dd class="font-medium text-gray-900 dark:text-white">{{ repair.client_name }}</dd></div>
            <div class="flex justify-between text-sm"><dt class="text-gray-500">Tél.</dt><dd><a :href="`tel:${repair.client_phone}`" class="font-medium text-blue-600 hover:underline">{{ repair.client_phone }}</a></dd></div>
            <div v-if="repair.client_email" class="flex justify-between text-sm"><dt class="text-gray-500">Email</dt><dd class="truncate max-w-[160px] text-gray-700 dark:text-gray-300">{{ repair.client_email }}</dd></div>
          </dl>
        </div>
        <div class="card">
          <h2 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2"><WrenchScrewdriverIcon class="w-4 h-4 text-blue-500" />Réparation</h2>
          <dl class="space-y-2">
            <div class="flex justify-between text-sm"><dt class="text-gray-500">Créée</dt><dd class="font-medium text-gray-900 dark:text-white">{{ fmtDate(repair.created_at) }}</dd></div>
            <div v-if="repair.estimated_ready" class="flex justify-between text-sm"><dt class="text-gray-500">Estimée</dt><dd class="font-medium text-gray-900 dark:text-white">{{ fmtDate(repair.estimated_ready) }}</dd></div>
            <div v-if="repair.technician" class="flex justify-between text-sm"><dt class="text-gray-500">Technicien</dt><dd class="font-medium text-gray-900 dark:text-white">{{ repair.technician }}</dd></div>
            <div v-if="repair.warranty_days" class="flex justify-between text-sm"><dt class="text-gray-500">Garantie</dt><dd class="font-medium text-gray-900 dark:text-white">{{ repair.warranty_days }}j</dd></div>
          </dl>
        </div>
      </div>

      <div class="card mb-4">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2"><ExclamationCircleIcon class="w-4 h-4 text-red-500" />Problème</h2>
        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ repair.problem_description }}</p>
        <template v-if="repair.diagnosis">
          <div class="divider" />
          <h2 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2"><MagnifyingGlassIcon class="w-4 h-4 text-blue-500" />Diagnostic</h2>
          <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ repair.diagnosis }}</p>
        </template>
      </div>

      <div v-if="repair.cost_estimate || repair.cost_final" class="card mb-4">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2"><BanknotesIcon class="w-4 h-4 text-green-500" />Coût</h2>
        <div class="flex gap-6">
          <div v-if="repair.cost_estimate"><p class="text-xs text-gray-500 mb-1">Devis</p><p class="text-lg font-bold text-gray-900 dark:text-white">{{ fmtCost(repair.cost_estimate, repair.currency) }}</p></div>
          <div v-if="repair.cost_final"><p class="text-xs text-gray-500 mb-1">Coût final</p><p class="text-lg font-bold text-green-600">{{ fmtCost(repair.cost_final, repair.currency) }}</p></div>
        </div>
      </div>

      <div v-if="repair.notes" class="card mb-4">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2"><DocumentTextIcon class="w-4 h-4 text-gray-400" />Notes</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ repair.notes }}</p>
      </div>

      <div class="flex gap-3 pb-4">
        <router-link to="/reparations" class="btn btn-secondary flex-1"><ArrowLeftIcon class="w-4 h-4" />Retour</router-link>
        <router-link :to="`/reparations/${repair.id}/edit`" class="btn btn-primary flex-1"><PencilIcon class="w-4 h-4" />Modifier</router-link>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeftIcon, PencilIcon, UserIcon, WrenchScrewdriverIcon, BanknotesIcon, DocumentTextIcon, ExclamationCircleIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { useRepairs } from '@/composables/useRepairs'
import { useUiStore } from '@/stores'
import type { RepairStatus, RepairPriority } from '@/types'

const route = useRoute(), router = useRouter()
const { getById, updateStatus, fetchRepairById } = useRepairs()
const uiStore = useUiStore()
const repair = computed(() => getById(route.params.id as string))
const loading = ref(true)

onMounted(async () => {
  if (!repair.value) await fetchRepairById(route.params.id as string)
  loading.value = false
})

const STATUS_FLOW = [
  { v: 'received', l: 'Reçu' }, { v: 'diagnosing', l: 'Diagnostic' }, { v: 'in_progress', l: 'En cours' },
  { v: 'waiting_parts', l: 'Attente pièces' }, { v: 'testing', l: 'Tests' }, { v: 'ready', l: 'Prêt' }, { v: 'delivered', l: 'Livré' },
]

async function changeStatus(s: string) {
  if (!repair.value) return
  try {
    await updateStatus(repair.value.id, s as RepairStatus)
    uiStore.showSuccess(`Statut mis à jour : ${statusLabel(s)}`)
  } catch {
    uiStore.showError('Impossible de mettre à jour le statut')
  }
}

const SL: Record<string,string> = { new:'Nouveau', received:'Reçu', diagnosing:'Diagnostic', waiting_quote:'Devis', quote_accepted:'Accepté', in_progress:'En cours', waiting_parts:'Pièces', testing:'Tests', ready:'Prêt', delivered:'Livré', cancelled:'Annulé' }
const SB: Record<string,string> = { new:'badge-gray', received:'badge-blue', diagnosing:'badge-blue', waiting_quote:'badge-yellow', quote_accepted:'badge-purple', in_progress:'badge-blue', waiting_parts:'badge-orange', testing:'badge-purple', ready:'badge-green', delivered:'badge-green', cancelled:'badge-gray' }
const PL: Record<string,string> = { urgent:'Urgent', high:'Élevée', normal:'Normale', low:'Faible' }
const PB: Record<string,string> = { urgent:'badge-red', high:'badge-orange', normal:'badge-blue', low:'badge-gray' }
function statusLabel(s:string) { return SL[s]??s } function statusBadge(s:string) { return SB[s]??'badge-gray' }
function priorityLabel(p:string) { return PL[p]??p } function priorityBadge(p:string) { return PB[p]??'badge-gray' }
function fmtDate(d?:string) { if(!d) return ''; try { return new Date(d).toLocaleDateString('fr-FR',{day:'2-digit',month:'long',year:'numeric'}) } catch { return d } }
function fmtCost(a?:number, c='FCFA') { if(!a) return `0 ${c}`; return `${a.toLocaleString('fr-FR')} ${c}` }
</script>
