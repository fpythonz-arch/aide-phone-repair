<template>
  <div
    class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 transition-colors"
  >
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ code.brand }} {{ code.model }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ code.model_range }} • {{ code.os_version }}</p>
      </div>
      <span class="text-2xl">📱</span>
    </div>

    <div class="space-y-3">
      <div
        v-for="detail in code.codes"
        :key="detail.id"
        class="p-3 bg-gray-50 dark:bg-slate-700 rounded-lg"
      >
        <div class="flex items-center justify-between mb-1">
          <code class="text-lg font-mono text-primary-600 dark:text-primary-400 font-bold">{{ detail.code }}</code>
          <span
            :class="[
              'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
              categoryColor(detail.category)
            ]"
          >
            {{ categoryLabel(detail.category) }}
          </span>
        </div>
        <p class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ detail.function }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ detail.description }}</p>
        <p v-if="detail.warning" class="text-xs text-red-600 dark:text-red-400 mt-1">⚠️ {{ detail.warning }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { CodeByModel } from '@/types'

defineProps<{
  code: CodeByModel
}>()

const categoryColors: Record<string, string> = {
  test: 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300',
  info: 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300',
  settings: 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300',
  service: 'bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300',
  debug: 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300',
  reset: 'bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300',
}

const categoryLabels: Record<string, string> = {
  test: 'Test', info: 'Info', settings: 'Paramètres', service: 'Service', debug: 'Debug', reset: 'Reset',
}

function categoryColor(cat: string): string {
  return categoryColors[cat] || categoryColors.debug
}

function categoryLabel(cat: string): string {
  return categoryLabels[cat] || cat
}
</script>