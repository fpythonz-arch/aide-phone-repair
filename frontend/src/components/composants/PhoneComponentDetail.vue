<template>
  <div class="space-y-6">
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 transition-colors">
      <div class="flex items-start justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ component.name }}</h1>
          <span
            :class="[
              'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2',
              categoryColor
            ]"
          >
            {{ categoryLabel }}
          </span>
        </div>
        <button
          @click="$emit('back')"
          class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-300 transition-colors"
        >
          ← Retour
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
          <div class="aspect-square bg-gray-100 dark:bg-slate-700 rounded-lg flex items-center justify-center mb-4">
            <img
              v-if="component.image_url"
              :src="component.image_url"
              :alt="component.name"
              class="h-full w-full object-contain rounded-lg"
            />
            <span v-else class="text-6xl">{{ categoryIcon }}</span>
          </div>
          <div v-if="component.schematic_url" class="text-center">
            <a
              :href="component.schematic_url"
              target="_blank"
              class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
            >
              📐 Voir le schéma
            </a>
          </div>
        </div>

        <div class="space-y-6">
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Fonction</h3>
            <p class="text-gray-600 dark:text-gray-400">{{ component.function }}</p>
          </div>

          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Emplacement</h3>
            <p class="text-gray-600 dark:text-gray-400">{{ component.location }}</p>
          </div>

          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Procédure de test</h3>
            <ol class="space-y-2">
              <li
                v-for="(step, i) in component.test_procedure"
                :key="i"
                class="flex gap-2 text-gray-700 dark:text-gray-300"
              >
                <span class="text-primary-500 font-bold">{{ i + 1 }}.</span>
                {{ step }}
              </li>
            </ol>
          </div>

          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Pannes fréquentes</h3>
            <ul class="space-y-1">
              <li
                v-for="(failure, i) in component.common_failures"
                :key="i"
                class="flex items-start gap-2 text-gray-700 dark:text-gray-300"
              >
                <span class="text-danger-500 mt-1">⚠️</span>
                {{ failure }}
              </li>
            </ul>
          </div>

          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Difficulté de remplacement</h3>
            <div class="flex items-center gap-2">
              <div class="flex-1 h-2 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                <div
                  :class="[
                    'h-full rounded-full',
                    component.replacement_difficulty === 'easy' ? 'w-1/4 bg-green-500' :
                    component.replacement_difficulty === 'medium' ? 'w-2/4 bg-yellow-500' :
                    component.replacement_difficulty === 'hard' ? 'w-3/4 bg-orange-500' :
                    'w-full bg-red-500'
                  ]"
                ></div>
              </div>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ difficultyLabel }}</span>
            </div>
          </div>

          <div v-if="component.price_range">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Prix de remplacement</h3>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
              {{ component.price_range.min }} - {{ component.price_range.max }} {{ component.price_range.currency }}
            </p>
          </div>

          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Modèles compatibles</h3>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="model in component.compatible_models"
                :key="model"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300"
              >
                {{ model }}
              </span>
            </div>
          </div>
        </div>
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
  back: []
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

const difficultyLabels = {
  easy: 'Facile', medium: 'Moyen', hard: 'Difficile', expert: 'Expert',
}

const categoryIcon = computed(() => categoryIcons[props.component.category] || '🔧')
const categoryLabel = computed(() => categoryLabels[props.component.category] || props.component.category)
const categoryColor = computed(() => categoryColors[props.component.category] || categoryColors.default)
const difficultyLabel = computed(() => difficultyLabels[props.component.replacement_difficulty])
</script>