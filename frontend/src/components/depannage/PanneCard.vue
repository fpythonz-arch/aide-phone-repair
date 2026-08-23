<template>
  <div
    class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 hover:shadow-md transition-all cursor-pointer transition-colors"
    @click="$emit('click', panne)"
  >
    <div class="flex items-start justify-between mb-3">
      <div class="flex items-center gap-3">
        <div
          :class="[
            'w-12 h-12 rounded-lg flex items-center justify-center text-2xl',
            categoryColor
          ]"
        >
          {{ categoryIcon }}
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ panne.title }}</h3>
          <span
            :class="[
              'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
              difficultyColor
            ]"
          >
            {{ difficultyLabel }}
          </span>
        </div>
      </div>
      <span class="text-sm text-gray-500 dark:text-gray-400">{{ panne.estimated_time }}min</span>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">{{ panne.description }}</p>

    <div class="flex flex-wrap gap-1 mb-3">
      <span
        v-for="symptom in panne.symptoms.slice(0, 3)"
        :key="symptom"
        class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300"
      >
        {{ symptom }}
      </span>
      <span v-if="panne.symptoms.length > 3" class="text-xs text-gray-400 dark:text-gray-500">
        +{{ panne.symptoms.length - 3 }}
      </span>
    </div>

    <div class="flex items-center justify-between text-sm">
      <div class="flex gap-2">
        <span v-if="panne.software_fixes?.length" class="text-primary-600 dark:text-primary-400">
          💻 {{ panne.software_fixes.length }} fix logiciel(s)
        </span>
        <span v-if="panne.hardware_fixes?.length" class="text-success-600 dark:text-success-400">
          🔧 {{ panne.hardware_fixes.length }} fix matériel(s)
        </span>
      </div>
      <span class="text-gray-400 dark:text-gray-500">→</span>
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
  click: [panne: PanneType]
}>()

const categoryIcons: Record<string, string> = {
  screen: '📱', battery: '🔋', charging: '🔌', audio: '🔊',
  network: '📡', camera: '📷', software: '💻', water: '💧',
  overheating: '🌡️', button: '🔘', sensor: '📐', memory: '💾',
  boot: '🔄', touch: '👆', display: '🖥️', wifi: '📶',
  bluetooth: '🔵', microphone: '🎤', speaker: '🔊', vibration: '📳',
  gps: '🗺️', nfc: '📲', fingerprint: '🖐️', faceid: '👤',
  port: '🔌', sim: '📟', sd: '💾', flash: '⚡',
  notification: '🔔', update: '⬆️', virus: '🦠', backup: '💾',
  recovery: '🔄', brick: '🧱', fastboot: '⚡', download: '⬇️',
  edf: '🔓', icloud: '☁️', frp: '🔒', mdm: '🏢',
  carrier: '📡', baseband: '📻', imei: '#️⃣', serial: '🔢',
  motherboard: '🧩', power: '⚡', backlight: '💡', proximity: '📏',
  accelerometer: '📐', gyroscope: '🔄', compass: '🧭', barometer: '🌡️',
  hall: '🚪', flex: '🔀',
}

const categoryColors: Record<string, string> = {
  screen: 'bg-blue-100 dark:bg-blue-900/50 text-blue-600',
  battery: 'bg-green-100 dark:bg-green-900/50 text-green-600',
  charging: 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600',
  software: 'bg-purple-100 dark:bg-purple-900/50 text-purple-600',
  water: 'bg-cyan-100 dark:bg-cyan-900/50 text-cyan-600',
  overheating: 'bg-red-100 dark:bg-red-900/50 text-red-600',
  default: 'bg-gray-100 dark:bg-slate-700 text-gray-600',
}

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

const categoryIcon = computed(() => categoryIcons[props.panne.category] || '🔧')
const categoryColor = computed(() => categoryColors[props.panne.category] || categoryColors.default)
const difficultyColor = computed(() => difficultyColors[props.panne.difficulty])
const difficultyLabel = computed(() => difficultyLabels[props.panne.difficulty])
</script>