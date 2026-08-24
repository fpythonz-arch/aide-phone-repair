<template>
  <div class="animate-fade-in">
    <div class="page-header">
      <div>
        <h1 class="page-title">Réparations</h1>
        <p class="page-subtitle">
          <span class="font-medium text-blue-600">{{ stats.active }}</span> en cours ·
          <span class="font-medium text-amber-600">{{ stats.pending }}</span> en attente ·
          <span class="font-medium text-green-600">{{ stats.ready }}</span> prêtes
        </p>
      </div>
      <router-link to="/reparations/nouvelle" class="btn btn-primary">
        <PlusIcon class="w-4 h-4" />Nouvelle réparation
      </router-link>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input v-model="search" type="search" placeholder="Client, appareil, numéro..." class="input pl-9" />
        </div>
        <select v-model="filterStatus" class="select sm:w-44">
          <option value="all">Tous les statuts</option>
          <option value="new">Nouveau</option>
          <option value="received">Reçu</option>
          <option value="diagnosing">Diagnostic</option>
          <option value="in_progress">En cours</option>
          <option value="waiting_parts">Attente pièces</option>
          <option value="testing">Tests</option>
          <option value="ready">Prêt</option>
          <option value="delivered">Livré</option>
          <option value="cancelled">Annulé</option>
        </select>
        <select v-model="filterPriority" class="select sm:w-36">
          <option value="all">Priorité</option>
          <option value="urgent">Urgent</option>
          <option value="high">Élevée</option>
          <option value="normal">Normale</option>
          <option value="low">Faible</option>
        </select>
      </div>
    </div>

    <!-- Vide -->
    <div v-if="filtered.length === 0" class="card">
      <div class="empty-state">
        <WrenchScrewdriverIcon class="empty-state-icon" />
        <p class="empty-state-title">{{ repairs.length === 0 ? 'Aucune réparation' : 'Aucun résultat' }}</p>
        <p class="empty-state-description">{{ repairs.length === 0 ? 'Créez votre première réparation.' : 'Modifiez les filtres.' }}</p>
        <router-link v-if="repairs.length === 0" to="/reparations/nouvelle" class="btn btn-primary btn-sm mt-3">
          <PlusIcon class="w-4 h-4" />Créer
        </router-link>
        <button v-else class="btn btn-secondary btn-sm mt-3" @click="search=''; filterStatus='all'; filterPriority='all'">Effacer les filtres</button>
      </div>
    </div>

    <!-- Tableau desktop -->
    <div v-else class="hidden md:block table-container">
      <table>
        <thead>
          <tr>
            <th>N° / Appareil</th><th>Client</th><th>Problème</th>
            <th>Priorité</th><th>Statut</th><th>Date</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in filtered" :key="r.id" class="cursor-pointer" @click="$router.push(`/reparations/${r.id}`)">
            <td>
              <p class="font-mono text-xs text-gray-400">{{ r.number }}</p>
              <p class="font-medium text-gray-900 dark:text-white">{{ r.device_brand }} {{ r.device_model }}</p>
            </td>
            <td>
              <p class="font-medium text-gray-900 dark:text-white">{{ r.client_name }}</p>
              <p class="text-xs text-gray-400">{{ r.client_phone }}</p>
            </td>
            <td class="max-w-xs"><p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">{{ r.problem_description }}</p></td>
            <td><span :class="['badge text-xs', priorityBadge(r.priority)]">{{ priorityLabel(r.priority) }}</span></td>
            <td><span :class="['badge text-xs', statusBadge(r.status)]">{{ statusLabel(r.status) }}</span></td>
            <td class="text-xs text-gray-400 whitespace-nowrap">{{ fmtDate(r.created_at) }}</td>
            <td @click.stop>
              <div class="flex items-center gap-1">
                <router-link :to="`/reparations/${r.id}`" class="btn btn-ghost btn-icon btn-sm"><EyeIcon class="w-4 h-4" /></router-link>
                <router-link :to="`/reparations/${r.id}/edit`" class="btn btn-ghost btn-icon btn-sm"><PencilIcon class="w-4 h-4" /></router-link>
                <button class="btn btn-ghost btn-icon btn-sm text-red-500" @click="toDelete = r"><TrashIcon class="w-4 h-4" /></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Cartes mobile -->
    <div v-if="filtered.length > 0" class="md:hidden space-y-3">
      <div v-for="r in filtered" :key="r.id" class="card card-hover cursor-pointer" @click="$router.push(`/reparations/${r.id}`)">
        <div class="flex items-start justify-between gap-3 mb-2">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <p class="font-semibold text-gray-900 dark:text-white">{{ r.device_brand }} {{ r.device_model }}</p>
              <span :class="['badge text-xs', priorityBadge(r.priority)]">{{ priorityLabel(r.priority) }}</span>
            </div>
            <p class="text-xs text-gray-400 font-mono">{{ r.number }}</p>
          </div>
          <span :class="['badge text-xs flex-shrink-0', statusBadge(r.status)]">{{ statusLabel(r.status) }}</span>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2 mb-2">{{ r.problem_description }}</p>
        <div class="flex justify-between text-xs text-gray-400">
          <span>{{ r.client_name }} · {{ r.client_phone }}</span>
          <span>{{ fmtDate(r.created_at) }}</span>
        </div>
      </div>
    </div>

    <!-- Modal suppression -->
    <Transition name="fade-overlay">
      <div v-if="toDelete" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="toDelete = null">
        <div class="card max-w-sm w-full animate-scale-in">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
              <ExclamationTriangleIcon class="w-5 h-5 text-red-600" />
            </div>
            <div>
              <p class="font-semibold text-gray-900 dark:text-white">Supprimer la réparation ?</p>
              <p class="text-sm text-gray-500">{{ toDelete.number }} — {{ toDelete.client_name }}</p>
            </div>
          </div>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Cette action est irréversible.</p>
          <div class="flex gap-3">
            <button class="btn btn-secondary flex-1" @click="toDelete = null">Annuler</button>
            <button class="btn btn-danger flex-1" @click="doDelete">Supprimer</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { PlusIcon, MagnifyingGlassIcon, WrenchScrewdriverIcon, EyeIcon, PencilIcon, TrashIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import { useRepairs } from '@/composables/useRepairs'
import type { Repair } from '@/types'

const { repairs, stats, filterRepairs, deleteRepair } = useRepairs()
const search = ref(''), filterStatus = ref('all'), filterPriority = ref('all'), toDelete = ref<Repair | null>(null)
const filtered = computed(() => filterRepairs({ status: filterStatus.value, priority: filterPriority.value, search: search.value }))

function doDelete() { if (toDelete.value) { deleteRepair(toDelete.value.id); toDelete.value = null } }

const SL: Record<string,string> = { new:'Nouveau', received:'Reçu', diagnosing:'Diagnostic', waiting_quote:'Devis', quote_accepted:'Accepté', in_progress:'En cours', waiting_parts:'Pièces', testing:'Tests', ready:'Prêt', delivered:'Livré', cancelled:'Annulé' }
const SB: Record<string,string> = { new:'badge-gray', received:'badge-blue', diagnosing:'badge-blue', waiting_quote:'badge-yellow', quote_accepted:'badge-purple', in_progress:'badge-blue', waiting_parts:'badge-orange', testing:'badge-purple', ready:'badge-green', delivered:'badge-green', cancelled:'badge-gray' }
const PL: Record<string,string> = { urgent:'Urgent', high:'Élevée', normal:'Normale', low:'Faible' }
const PB: Record<string,string> = { urgent:'badge-red', high:'badge-orange', normal:'badge-blue', low:'badge-gray' }
function statusLabel(s:string) { return SL[s]??s } function statusBadge(s:string) { return SB[s]??'badge-gray' }
function priorityLabel(p:string) { return PL[p]??p } function priorityBadge(p:string) { return PB[p]??'badge-gray' }
function fmtDate(d:string) { try { return new Date(d).toLocaleDateString('fr-FR',{day:'2-digit',month:'short',year:'2-digit'}) } catch { return '' } }
</script>
