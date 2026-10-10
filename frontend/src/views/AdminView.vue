<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Administration de la plateforme</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Vue d'ensemble des comptes et des volumes. Les réparations et les clients des ateliers restent privés :
        cet espace n'affiche que des compteurs.
      </p>
    </div>

    <p v-if="loadError" class="text-sm text-red-600">{{ loadError }}</p>
    <p v-else-if="loading" class="text-sm text-gray-500">Chargement…</p>

    <template v-else-if="overview">
      <p class="text-sm text-gray-700 dark:text-gray-300">
        <strong>{{ overview.totals.workshops }}</strong> atelier{{ overview.totals.workshops > 1 ? 's' : '' }} ·
        <strong>{{ overview.totals.users }}</strong> compte{{ overview.totals.users > 1 ? 's' : '' }} ·
        <strong>{{ overview.totals.repairs }}</strong> réparation{{ overview.totals.repairs > 1 ? 's' : '' }}
      </p>

      <section class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Ateliers</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 border-b border-gray-200 dark:border-slate-700">
                <th class="py-2 pr-4 font-medium">Atelier</th>
                <th class="py-2 pr-4 font-medium">Créé le</th>
                <th class="py-2 pr-4 font-medium text-right">Membres</th>
                <th class="py-2 font-medium text-right">Réparations</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="w in overview.workshops" :key="w.id" class="border-b border-gray-100 dark:border-slate-700/50">
                <td class="py-2 pr-4 text-gray-900 dark:text-white">{{ w.name }}</td>
                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">{{ formatDate(w.created_at) }}</td>
                <td class="py-2 pr-4 text-right text-gray-600 dark:text-gray-300">{{ w.members_count }}</td>
                <td class="py-2 text-right text-gray-600 dark:text-gray-300">{{ w.repairs_count }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Derniers comptes créés</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 border-b border-gray-200 dark:border-slate-700">
                <th class="py-2 pr-4 font-medium">Nom</th>
                <th class="py-2 pr-4 font-medium">E-mail</th>
                <th class="py-2 pr-4 font-medium">Atelier</th>
                <th class="py-2 pr-4 font-medium">Rôle</th>
                <th class="py-2 font-medium">Créé le</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in overview.recent_users" :key="u.id" class="border-b border-gray-100 dark:border-slate-700/50">
                <td class="py-2 pr-4 text-gray-900 dark:text-white">{{ u.name }}</td>
                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">{{ u.email }}</td>
                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">{{ u.workshop ?? '—' }}</td>
                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">
                  {{ u.is_platform_admin ? 'Administrateur de la plateforme' : roleLabelOf(u.role) }}
                </td>
                <td class="py-2 text-gray-600 dark:text-gray-300">{{ formatDate(u.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { adminApi } from '@/api/client'
import { roleLabelOf } from '@/composables/usePermissions'
import type { PlatformOverview } from '@/types'

const overview = ref<PlatformOverview | null>(null)
const loading = ref(true)
const loadError = ref('')

function formatDate(value: string): string {
  return new Date(value).toLocaleDateString('fr-FR')
}

onMounted(async () => {
  try {
    const { data } = await adminApi.overview()
    overview.value = data.data
  } catch (err: any) {
    loadError.value = err?.response?.status === 403
      ? 'Cet espace est réservé aux administrateurs de la plateforme.'
      : "Impossible de charger l'administration. Réessayez."
  } finally {
    loading.value = false
  }
})
</script>
