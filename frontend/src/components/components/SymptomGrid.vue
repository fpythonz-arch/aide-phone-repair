<template>
  <div class="space-y-4">
    <div v-if="symptoms.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
      Aucun symptôme disponible pour ce filtre.
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="symptom in symptoms"
        :key="symptom.id"
        :class="[
          'relative p-4 rounded-lg border-2 cursor-pointer transition-all',
          isSelected(symptom.id)
            ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
            : 'border-gray-200 dark:border-slate-700 hover:border-gray-300 dark:hover:border-slate-600 bg-white dark:bg-slate-800'
        ]"
        @click="toggleSelection(symptom)"
      >
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h4 class="font-medium text-gray-900 dark:text-white">{{ symptom.name }}</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ symptom.description }}</p>
          </div>
          <SeverityBadge :severity="symptom.severity" class="ml-2" />
        </div>
        
        <div 
          v-if="isSelected(symptom.id)"
          class="absolute top-2 right-2 w-5 h-5 bg-primary-500 rounded-full flex items-center justify-center"
        >
          <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import SeverityBadge from '../diagnostic/SeverityBadge.vue'
import type { Symptom } from '@/types'

const props = defineProps<{
  symptoms: Symptom[]
  modelValue: number[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: number[]]
}>()

const selectedIds = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

function isSelected(id: number): boolean {
  return selectedIds.value.includes(id)
}

function toggleSelection(symptom: Symptom) {
  const current = [...selectedIds.value]
  const index = current.indexOf(symptom.id)
  if (index > -1) {
    current.splice(index, 1)
  } else {
    current.push(symptom.id)
  }
  selectedIds.value = current
}
</script>

<style scoped>
/* Styles spécifiques si nécessaire */
</style>