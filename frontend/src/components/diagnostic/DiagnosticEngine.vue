<template>
  <div class="diagnostic-engine min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="max-w-4xl mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">🔧 Aide Phone Réparation</h1>
            <p class="text-sm text-gray-500">Diagnostic intelligent par symptômes</p>
          </div>
          <StepIndicator :current-step="currentStepIndex" :total-steps="4" />
        </div>
        
        <!-- Device Info Bar -->
        <div v-if="deviceInfo" class="mt-3 flex items-center gap-2 text-sm">
          <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-medium">
            {{ deviceInfo.brand }} {{ deviceInfo.model }}
          </span>
          <span v-if="deviceInfo.imei" class="text-gray-400">
            IMEI: {{ deviceInfo.imei }}
          </span>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-6">
      <!-- Step 1: Device Selection -->
      <DeviceStep 
        v-if="currentStep === 'device'"
        @device-selected="onDeviceSelected"
      />

      <!-- Step 2: Symptom Selection -->
      <SymptomStep
        v-else-if="currentStep === 'symptoms'"
        @symptoms-selected="onSymptomsSelected"
        @back="currentStep = 'device'"
      />

      <!-- Step 3: Analysis -->
      <AnalysisStep
        v-else-if="currentStep === 'analysis'"
        :result="diagnosticResult"
        :is-loading="isLoading"
        :error="error"
        @next="currentStep = 'result'"
        @back="currentStep = 'symptoms'"
      />

      <!-- Step 4: Result -->
      <ResultStep
        v-else-if="currentStep === 'result'"
        :result="diagnosticResult"
        @restart="restart"
        @back="currentStep = 'analysis'"
      />
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useDiagnostic } from '@/composables/useDiagnostic'
import StepIndicator from './StepIndicator.vue'
import DeviceStep from './DeviceStep.vue'
import SymptomStep from './SymptomStep.vue'
import AnalysisStep from './AnalysisStep.vue'
import ResultStep from './ResultStep.vue'

const currentStep = ref<'device' | 'symptoms' | 'analysis' | 'result'>('device')

const { 
  deviceInfo, 
  diagnosticResult, 
  isLoading, 
  error, 
  initializeDiagnostic,
  analyzeSymptoms,
  resetDiagnostic 
} = useDiagnostic()

const currentStepIndex = computed(() => {
  const map: Record<string, number> = {
    device: 0,
    symptoms: 1,
    analysis: 2,
    result: 3,
  }
  return map[currentStep.value] || 0
})

const onDeviceSelected = async (data: { brand: string; model: string; imei?: string; os_version?: string }) => {
  try {
    await initializeDiagnostic(data)
    currentStep.value = 'symptoms'
  } catch (err) {
    console.error('Init failed:', err)
  }
}

const onSymptomsSelected = async (symptomIds: number[]) => {
  currentStep.value = 'analysis'
  try {
    await analyzeSymptoms(symptomIds)
    currentStep.value = 'result'
  } catch (err) {
    console.error('Analysis failed:', err)
  }
}

const restart = () => {
  resetDiagnostic()
  currentStep.value = 'device'
}
</script>