<template>
  <div
    class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 hover:shadow-md transition-all transition-colors"
  >
    <div class="flex items-start justify-between mb-4">
      <div class="flex items-center gap-3">
        <div
          :class="[
            'w-14 h-14 rounded-lg flex items-center justify-center text-2xl',
            categoryColor
          ]"
        >
          {{ categoryIcon }}
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ tool.name }}</h3>
          <p v-if="tool.brand" class="text-sm text-gray-500 dark:text-gray-400">{{ tool.brand }} {{ tool.model }}</p>
        </div>
      </div>
      <span
        v-if="tool.is_essential"
        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300"
      >
        ⭐ Essentiel
      </span>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ tool.description }}</p>

    <div class="mb-4">
      <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Fonctionnalités</h4>
      <div class="flex flex-wrap gap-1">
        <span
          v-for="feature in tool.features"
          :key="feature"
          class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300"
        >
          {{ feature }}
        </span>
      </div>
    </div>

    <div class="mb-4">
      <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Marques supportées</h4>
      <div class="flex flex-wrap gap-1">
        <span
          v-for="brand in tool.supported_brands"
          :key="brand"
          class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300"
        >
          {{ brand }}
        </span>
      </div>
    </div>

    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-slate-700">
      <div>
        <p class="text-lg font-bold text-primary-600 dark:text-primary-400">
          {{ tool.price_range.min }} - {{ tool.price_range.max }} {{ tool.price_range.currency }}
        </p>
        <span
          :class="[
            'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1',
            difficultyColor
          ]"
        >
          {{ difficultyLabel }}
        </span>
      </div>
      <a
        v-if="tool.purchase_url"
        :href="tool.purchase_url"
        target="_blank"
        class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm rounded-md hover:bg-primary-700 transition-colors"
      >
        Acheter →
      </a>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { ProTool } from '@/types'

const props = defineProps<{
  tool: ProTool
}>()

const categoryIcons: Record<string, string> = {
  unlock: '🔓', flash: '⚡', diagnostic: '🔍', repair: '🔧',
  security: '🔒', backup: '💾', data_recovery: '🔄', soldering: '🔥',
  microscope: '🔬', multimeter: '📊', oscilloscope: '📈', programmer: '💻',
  heating: '🌡️', cleaning: '🧼', measurement: '📏',
}

const categoryColors: Record<string, string> = {
  unlock: 'bg-purple-100 dark:bg-purple-900/50 text-purple-600',
  flash: 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600',
  diagnostic: 'bg-blue-100 dark:bg-blue-900/50 text-blue-600',
  repair: 'bg-green-100 dark:bg-green-900/50 text-green-600',
  security: 'bg-red-100 dark:bg-red-900/50 text-red-600',
  default: 'bg-gray-100 dark:bg-slate-700 text-gray-600',
}

const difficultyColors = {
  beginner: 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300',
  intermediate: 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300',
  advanced: 'bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300',
  expert: 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300',
}

const difficultyLabels = {
  beginner: 'Débutant', intermediate: 'Intermédiaire', advanced: 'Avancé', expert: 'Expert',
}

const categoryIcon = computed(() => categoryIcons[props.tool.category] || '🛠️')
const categoryColor = computed(() => categoryColors[props.tool.category] || categoryColors.default)
const difficultyColor = computed(() => difficultyColors[props.tool.difficulty])
const difficultyLabel = computed(() => difficultyLabels[props.tool.difficulty])
</script>