<template>
  <div class="space-y-6 max-w-3xl">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion de l'atelier</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Espace réservé au responsable. Vos réparations et vos clients restent privés à votre atelier.
      </p>
    </div>

    <p v-if="loadError" class="text-sm text-red-600">{{ loadError }}</p>
    <p v-else-if="loading" class="text-sm text-gray-500">Chargement…</p>

    <template v-else-if="workshop">
      <section class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Nom de l'atelier</h2>
        <form class="flex flex-col sm:flex-row gap-3" @submit.prevent="save">
          <input v-model="name" type="text" maxlength="100" class="input flex-1" aria-label="Nom de l'atelier" required />
          <button type="submit" class="btn btn-primary" :disabled="saving || !name.trim() || name.trim() === workshop.name">
            {{ saving ? 'Enregistrement…' : 'Enregistrer' }}
          </button>
        </form>
        <p v-if="saveMessage" class="text-sm text-green-600 mt-2">{{ saveMessage }}</p>
        <p v-if="saveError" class="text-sm text-red-600 mt-2">{{ saveError }}</p>
        <p class="text-xs text-gray-500 mt-4">
          Créé le {{ formatDate(workshop.created_at) }} · {{ workshop.repairs_count }}
          réparation{{ workshop.repairs_count > 1 ? 's' : '' }}
        </p>
      </section>

      <section class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Équipe</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 border-b border-gray-200 dark:border-slate-700">
                <th class="py-2 pr-4 font-medium">Nom</th>
                <th class="py-2 pr-4 font-medium">E-mail</th>
                <th class="py-2 font-medium">Rôle</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in workshop.members" :key="m.id" class="border-b border-gray-100 dark:border-slate-700/50">
                <td class="py-2 pr-4 text-gray-900 dark:text-white">{{ m.name }}</td>
                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">{{ m.email }}</td>
                <td class="py-2 text-gray-600 dark:text-gray-300">{{ roleLabelOf(m.role) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-if="workshop.members.length <= 1" class="text-xs text-gray-500 mt-4">
          Vous êtes seul dans cet atelier pour l'instant. L'invitation de techniciens arrivera prochainement.
        </p>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { workshopApi } from '@/api/client'
import { useAuth } from '@/composables/useAuth'
import { roleLabelOf } from '@/composables/usePermissions'
import type { WorkshopDetails } from '@/types'

const { syncFromServer } = useAuth()

const workshop = ref<WorkshopDetails | null>(null)
const name = ref('')
const loading = ref(true)
const loadError = ref('')
const saving = ref(false)
const saveMessage = ref('')
const saveError = ref('')

function formatDate(value: string): string {
  return new Date(value).toLocaleDateString('fr-FR')
}

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await workshopApi.get()
    workshop.value = data.data
    name.value = data.data.name
  } catch (err: any) {
    loadError.value = err?.response?.status === 403
      ? "Cet espace est réservé au responsable de l'atelier."
      : "Impossible de charger l'atelier. Réessayez."
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  saveMessage.value = ''
  saveError.value = ''
  try {
    const { data } = await workshopApi.rename(name.value.trim())
    if (workshop.value) workshop.value.name = data.data.name
    saveMessage.value = 'Nom enregistré.'
    await syncFromServer()
  } catch (err: any) {
    saveError.value = err?.response?.data?.errors?.name?.[0] ?? "Enregistrement impossible. Réessayez."
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>
