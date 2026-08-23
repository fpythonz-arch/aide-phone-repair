<template>
  <div class="space-y-6">
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 transition-colors">
      <div class="flex items-start justify-between mb-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ panne.title }}</h1>
          <div class="flex gap-2 mt-2">
            <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', difficultyColor]">
              {{ difficultyLabel }}
            </span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-300">
              ⏱️ {{ panne.estimated_time }} min
            </span>
          </div>
        </div>
        <button
          @click="$emit('back')"
          class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-300 transition-colors"
        >
          ← Retour
        </button>
      </div>

      <p class="text-gray-600 dark:text-gray-400 mb-6">{{ panne.description }}</p>

      <!-- Outils nécessaires -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">🔧 Outils nécessaires</h3>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="tool in panne.tools_needed"
            :key="tool"
            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300"
          >
            {{ tool }}
          </span>
        </div>
      </div>

      <!-- Causes -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">❓ Causes possibles</h3>
        <ul class="space-y-2">
          <li
            v-for="(cause, index) in panne.causes"
            :key="index"
            class="flex items-start gap-2 text-gray-700 dark:text-gray-300"
          >
            <span class="text-primary-500 mt-1">•</span>
            {{ cause }}
          </li>
        </ul>
      </div>

      <!-- Solutions logicielles -->
      <div v-if="panne.software_fixes?.length" class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">💻 Solutions Logicielles</h3>
        <div class="space-y-4">
          <div
            v-for="fix in panne.software_fixes"
            :key="fix.id"
            class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800"
          >
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-medium text-purple-900 dark:text-purple-300">{{ fix.title }}</h4>
              <span
                :class="[
                  'inline-flex items-center px-2 py-0.5 rounded text-xs',
                  fix.warning_level === 'safe' ? 'bg-green-100 text-green-700' :
                  fix.warning_level === 'caution' ? 'bg-yellow-100 text-yellow-700' :
                  'bg-red-100 text-red-700'
                ]"
              >
                {{ fix.warning_level }}
              </span>
            </div>
            <ol class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
              <li v-for="(step, i) in fix.steps" :key="i" class="flex gap-2">
                <span class="text-purple-500 font-medium">{{ i + 1 }}.</span>
                {{ step }}
              </li>
            </ol>
            <div v-if="fix.tools.length" class="mt-2 flex flex-wrap gap-1">
              <span
                v-for="tool in fix.tools"
                :key="tool"
                class="text-xs px-2 py-0.5 bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded"
              >
                {{ tool }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Solutions matérielles -->
      <div v-if="panne.hardware_fixes?.length" class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">🔧 Solutions Matérielles</h3>
        <div class="space-y-4">
          <div
            v-for="fix in panne.hardware_fixes"
            :key="fix.id"
            class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800"
          >
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-medium text-green-900 dark:text-green-300">{{ fix.title }}</h4>
              <span
                :class="[
                  'inline-flex items-center px-2 py-0.5 rounded text-xs',
                  fix.warning_level === 'safe' ? 'bg-green-100 text-green-700' :
                  fix.warning_level === 'caution' ? 'bg-yellow-100 text-yellow-700' :
                  'bg-red-100 text-red-700'
                ]"
              >
                {{ fix.warning_level }}
              </span>
            </div>
            <ol class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
              <li v-for="(step, i) in fix.steps" :key="i" class="flex gap-2">
                <span class="text-green-500 font-medium">{{ i + 1 }}.</span>
                {{ step }}
              </li>
            </ol>
            <div v-if="fix.parts_needed.length" class="mt-2">
              <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Pièces nécessaires :</p>
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="part in fix.parts_needed"
                  :key="part"
                  class="text-xs px-2 py-0.5 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 rounded"
                >
                  {{ part }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Marques affectées -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">📱 Marques concernées</h3>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="brand in panne.brands_affected"
            :key="brand"
            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300"
          >
            {{ brand }}
          </span>
        </div>
      </div>

      <!-- Vidéo -->
      <div v-if="panne.video_url" class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">🎥 Tutoriel vidéo</h3>
        <div class="aspect-video bg-gray-900 rounded-lg flex items-center justify-center">
          <a
            :href="panne.video_url"
            target="_blank"
            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            ▶️ Voir sur YouTube
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { PanneType } from '@/types'

const props = defineProps<{
  panne: PanneType
}>()

defineEmits<{
  back: []
}>()

const difficultyColors = {
  easy: 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300',
  medium: 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300',
  hard: 'bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300',
  expert: 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300',
}

const difficultyLabels = {
  easy: 'Facile',
  medium: 'Moyen',
  hard: 'Difficile',
  expert: 'Expert',
}

const difficultyColor = computed(() => difficultyColors[props.panne.difficulty])
const difficultyLabel = computed(() => difficultyLabels[props.panne.difficulty])
</script>