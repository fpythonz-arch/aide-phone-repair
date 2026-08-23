<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">📈 Chronologie</h2>
      <div class="flex gap-2">
        <select
          v-model="filterType"
          class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-colors"
        >
          <option value="">Tous les types</option>
          <option value="diagnostic">Diagnostic</option>
          <option value="repair">Réparation</option>
          <option value="maintenance">Maintenance</option>
          <option value="incident">Incident</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto"></div>
    </div>

    <div v-else-if="events.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
      Aucun événement enregistré.
    </div>

    <div v-else class="relative">
      <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-slate-700"></div>

      <div class="space-y-6">
        <div
          v-for="event in filteredEvents"
          :key="event.id"
          class="relative flex items-start gap-4"
        >
          <div
            :class="[
              'relative z-10 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
              getEventColor(event.event_type)
            ]"
          >
            <span class="text-sm">{{ getEventIcon(event.event_type) }}</span>
          </div>

          <div class="flex-1 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 transition-colors">
            <div class="flex items-start justify-between mb-2">
              <div>
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    getBadgeColor(event.event_type)
                  ]"
                >
                  {{ event.event_type }}
                </span>
                <SeverityBadge :severity="event.severity" class="ml-2" />
              </div>
              <time class="text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(event.created_at) }}
              </time>
            </div>
            <p class="text-gray-700 dark:text-gray-300">{{ event.description }}</p>
            <div v-if="event.metadata" class="mt-3 p-3 bg-gray-50 dark:bg-slate-700 rounded-lg">
              <pre class="text-xs text-gray-600 dark:text-gray-400 overflow-x-auto">{{ JSON.stringify(event.metadata, null, 2) }}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import SeverityBadge from '../diagnostic/SeverityBadge.vue'
import type { EvolutionEvent } from '@/types'

const props = defineProps<{
  events: EvolutionEvent[]
  loading?: boolean
}>()

const filterType = ref('')

const filteredEvents = computed(() => {
  if (!filterType.value) return props.events
  return props.events.filter(e => e.event_type === filterType.value)
})

function getEventColor(type: string): string {
  const colors: Record<string, string> = {
    diagnostic: 'bg-primary-500 text-white',
    repair: 'bg-success-500 text-white',
    maintenance: 'bg-warning-500 text-white',
    incident: 'bg-danger-500 text-white',
  }
  return colors[type] || 'bg-gray-500 text-white'
}

function getBadgeColor(type: string): string {
  const colors: Record<string, string> = {
    diagnostic: 'bg-primary-100 dark:bg-primary-900/50 text-primary-800 dark:text-primary-300',
    repair: 'bg-success-100 dark:bg-green-900/50 text-success-800 dark:text-green-300',
    maintenance: 'bg-warning-100 dark:bg-yellow-900/50 text-warning-800 dark:text-yellow-300',
    incident: 'bg-danger-100 dark:bg-red-900/50 text-red-800 dark:text-red-300',
  }
  return colors[type] || 'bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-300'
}

function getEventIcon(type: string): string {
  const icons: Record<string, string> = {
    diagnostic: '🔍',
    repair: '🔧',
    maintenance: '🛠️',
    incident: '⚠️',
  }
  return icons[type] || '📋'
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>