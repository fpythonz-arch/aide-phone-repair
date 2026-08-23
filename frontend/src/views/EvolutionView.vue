<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">📈 Suivi d'Évolution</h1>
      <button
        class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 dark:bg-primary-500 text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-700 dark:hover:bg-primary-600 transition-colors"
        @click="showAddEvent = true"
      >
        + Ajouter un événement
      </button>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 transition-colors">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID Appareil / IMEI</label>
          <div class="flex gap-2">
            <input
              v-model="deviceId"
              type="text"
              placeholder="Entrez l'IMEI ou l'ID..."
              class="flex-1 px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-colors"
            />
            <button
              class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 dark:bg-primary-500 text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-700 dark:hover:bg-primary-600 transition-colors"
              @click="loadEvents"
            >
              Rechercher
            </button>
          </div>
        </div>
      </div>
    </div>

    <Timeline :events="events" :loading="loading" />

    <div v-if="showAddEvent" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-slate-800 rounded-xl max-w-lg w-full p-6 transition-colors">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold dark:text-white">Ajouter un événement</h2>
          <button @click="showAddEvent = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl">&times;</button>
        </div>

        <form @submit.prevent="submitEvent" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID Appareil</label>
            <input
              v-model="newEvent.device_id"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-colors"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
            <select
              v-model="newEvent.event_type"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-colors"
            >
              <option value="diagnostic">Diagnostic</option>
              <option value="repair">Réparation</option>
              <option value="maintenance">Maintenance</option>
              <option value="incident">Incident</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sévérité</label>
            <select
              v-model="newEvent.severity"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-colors"
            >
              <option value="low">Faible</option>
              <option value="medium">Moyenne</option>
              <option value="high">Élevée</option>
              <option value="critical">Critique</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea
              v-model="newEvent.description"
              rows="3"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-colors"
            ></textarea>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="button"
              class="flex-1 px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-300 transition-colors"
              @click="showAddEvent = false"
            >
              Annuler
            </button>
            <button
              type="submit"
              class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-primary-600 dark:bg-primary-500 text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-700 dark:hover:bg-primary-600 transition-colors"
              :disabled="submitting"
            >
              {{ submitting ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useEvolution } from '@/composables/useEvolution'
import Timeline from '@/components/evolution/Timeline.vue'
import type { EvolutionEvent } from '@/types'

const { events, loading, fetchEvents, addEvent } = useEvolution()

const deviceId = ref('')
const showAddEvent = ref(false)
const submitting = ref(false)

const newEvent = ref({
  device_id: '',
  event_type: 'diagnostic' as EvolutionEvent['event_type'],
  severity: 'low' as EvolutionEvent['severity'],
  description: '',
})

async function loadEvents() {
  await fetchEvents(deviceId.value || undefined)
}

async function submitEvent() {
  submitting.value = true
  try {
    await addEvent(newEvent.value)
    showAddEvent.value = false
    newEvent.value = {
      device_id: '',
      event_type: 'diagnostic',
      severity: 'low',
      description: '',
    }
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchEvents()
})
</script>