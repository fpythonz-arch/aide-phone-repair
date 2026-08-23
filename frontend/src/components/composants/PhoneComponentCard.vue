<template>
  <div
    class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden hover:shadow-md transition-all cursor-pointer transition-colors"
    @click="$emit('click', component)"
  >
    <div class="h-40 bg-gray-100 dark:bg-slate-700 flex items-center justify-center">
      <img
        v-if="component.image_url"
        :src="component.image_url"
        :alt="component.name"
        class="h-full w-full object-cover"
      />
      <span v-else class="text-4xl">{{ categoryIcon }}</span>
    </div>
    <div class="p-4">
      <div class="flex items-center justify-between mb-2">
        <span
          :class="[
            'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
            categoryColor
          ]"
        >
          {{ categoryLabel }}
        </span>
        <span
          :class="[
            'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
            difficultyColor
          ]"
        >
          {{ difficultyLabel }}
        </span>
      </div>
      <h3 class="font-semibold text-gray-900 dark:text-white mb-1">{{ component.name }}</h3>
      <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">{{ component.description }}</p>
      <div class="text-xs text-gray-500 dark:text-gray-400">
        <p>📍 {{ component.location }}</p>
        <p v-if="component.price_range" class="mt-1">
          💰 {{ component.price_range.min }}-{{ component.price_range.max }} {{ component.price_range.currency }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { PhoneComponent } from '@/types'

const props = defineProps<{
  component: PhoneComponent
}>()

defineEmits<{
  click: [component: PhoneComponent]
}>()

const categoryIcons: Record<string, string> = {
  display: '📱', battery: '🔋', processor: '🧠', memory: '💾',
  camera: '📷', audio: '🔊', connectivity: '📡', sensor: '📐',
  housing: '📦', port: '🔌', antenna: '📶', security: '🔒',
}

const categoryLabels: Record<string, string> = {
  display: 'Écran', battery: 'Batterie', processor: 'Processeur', memory: 'Mémoire',
  camera: 'Caméra', audio: 'Audio', connectivity: 'Connectivité', sensor: 'Capteur',
  housing: 'Châssis', port: 'Port', antenna: 'Antenne', security: 'Sécurité',
}

const categoryColors: Record<string, string> = {
  display: 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300',
  battery: 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300',
  processor: 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300',
  default: 'bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300',
}

const difficultyColors = {
  easy: 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300',
  medium: 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300',
  hard: 'bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300',
  expert: 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300',
}

const difficultyLabels = {
  easy: 'Facile', medium: 'Moyen', hard: 'Difficile', expert: 'Expert',
}

const categoryIcon = computed(() => categoryIcons[props.component.category] || '🔧')
const categoryLabel = computed(() => categoryLabels[props.component.category] || props.component.category)
const categoryColor = computed(() => categoryColors[props.component.category] || categoryColors.default)
const difficultyColor = computed(() => difficultyColors[props.component.replacement_difficulty])
const difficultyLabel = computed(() => difficultyLabels[props.component.replacement_difficulty])
</script>