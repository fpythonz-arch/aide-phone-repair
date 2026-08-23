<template>
  <div class="diagnostic-view">
    <header class="diagnostic-header">
      <h1>🔧 Diagnostic de réparation</h1>
      <p class="subtitle">Identifiez le problème de votre appareil étape par étape</p>
    </header>

    <StepIndicator :current-step="currentStepIndex ?? 0" :steps="stepConfig" :progress="progressPercentage" />

    <div class="diagnostic-content">
      <DeviceStep v-if="currentStep === 'device'" :brands="brands" :devices="devices" :loading="deviceStore.isLoading"
        :selected-device="selectedDevice" @select-brand="onSelectBrand" @select-device="onSelectDevice"
        @next="nextStep" />

      <SymptomStep v-else-if="currentStep === 'symptom'" :symptoms="availableSymptoms"
        :selected-symptoms="selectedSymptoms" :loading="isLoading" :device="selectedDevice"
        @toggle-symptom="toggleSymptom" @select-all="selectAllSymptoms" @clear-all="clearSymptoms" @next="nextStep"
        @prev="prevStep" />

      <AnalysisStep v-else-if="currentStep === 'analysis'" :is-loading="isLoading" :progress="analysisProgress"
        :selected-symptoms="selectedSymptomsDetails" :result="diagnosticResult" @next="nextStep" @prev="prevStep" />

      <ResultStep v-else-if="currentStep === 'result'" :result="diagnosticResult" :analysis-results="analysisResults"
        :severity-color="severityColor" @prev="prevStep" @finish="nextStep" @start-repair="onStartRepair"
        @view-guide="onViewGuide" />

      <ValidationStep v-else-if="currentStep === 'validation'" :result="diagnosticResult" :device="selectedDevice"
        @restart="reset" @save="onSaveDiagnostic" @print="onPrintReport" />

      <RepairFlow v-if="showRepairFlow" :guide="selectedGuide" :component="selectedComponent"
        @close="showRepairFlow = false" @complete="onRepairComplete" />
    </div>

    <div class="toast-container">
      <TransitionGroup name="toast">
        <div v-for="toast in toasts" :key="toast.id" :class="['toast', `toast--${toast.type}`]">
          {{ toast.message }}
        </div>
      </TransitionGroup>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useDiagnostic } from '@/composables/useDiagnostic'
import { useDeviceStore, useUiStore } from '@/stores'
import StepIndicator from '@/components/diagnostic/StepIndicator.vue'
import DeviceStep from '@/components/diagnostic/DeviceStep.vue'
import SymptomStep from '@/components/diagnostic/SymptomStep.vue'
import AnalysisStep from '@/components/diagnostic/AnalysisStep.vue'
import ResultStep from '@/components/diagnostic/ResultStep.vue'
import ValidationStep from '@/components/diagnostic/ValidationStep.vue'
import RepairFlow from '@/components/diagnostic/RepairFlow.vue'
import type { Device, RepairGuide, Component } from '@/types'

const router = useRouter()
const uiStore = useUiStore()
const deviceStore = useDeviceStore()

const {
  currentStep,
  currentStepIndex,
  progressPercentage,
  selectedDevice,
  selectedSymptoms,
  availableSymptoms,
  selectedSymptomsDetails,
  diagnosticResult,
  analysisResults,
  severityColor,
  isLoading,
  fetchBrands,
  fetchDevicesByBrand,
  fetchSymptomsByDevice,
  selectDevice,
  toggleSymptom,
  selectAllSymptoms,
  clearSymptoms,
  initializeDiagnostic,
  analyzeSymptoms,
  nextStep,
  prevStep,
  reset,
} = useDiagnostic()

const stepConfig = [
  { key: 'device', label: 'Appareil', icon: '📱', description: 'Sélectionnez votre téléphone' },
  { key: 'symptom', label: 'Symptômes', icon: '🔍', description: 'Quels problèmes rencontrez-vous ?' },
  { key: 'analysis', label: 'Analyse', icon: '⚙️', description: 'Analyse en cours...' },
  { key: 'result', label: 'Solutions', icon: '💡', description: 'Voici les solutions' },
  { key: 'validation', label: 'Validation', icon: '✅', description: 'Confirmez la réparation' },
]

const brands = ref<string[]>([])
const devices = ref<Device[]>([])

onMounted(async () => {
  try {
    brands.value = await fetchBrands()
  } catch (err) {
    uiStore.showError('Impossible de charger les marques')
  }
})

const onSelectBrand = async (brand: string) => {
  try {
    devices.value = await fetchDevicesByBrand(brand)
  } catch (err) {
    uiStore.showError('Impossible de charger les modèles')
  }
}

const onSelectDevice = async (device: Device) => {
  selectDevice(device)
  try {
    await initializeDiagnostic()
  } catch (err) {
    console.warn('[DiagnosticView] initializeDiagnostic a échoué:', err)
  }
  try {
    await fetchSymptomsByDevice(device.id)
    if (availableSymptoms.value.length > 0) {
      nextStep()
    } else {
      uiStore.showError('Aucun symptôme disponible pour cet appareil')
    }
  } catch (err) {
    uiStore.showError('Erreur lors du chargement des symptômes')
  }
}

const analysisProgress = ref(0)

watch(currentStep, async (step) => {
  if (step === 'analysis') {
    analysisProgress.value = 0
    const interval = setInterval(() => {
      analysisProgress.value += 10
      if (analysisProgress.value >= 90) clearInterval(interval)
    }, 300)
    try {
      await analyzeSymptoms()
      analysisProgress.value = 100
      setTimeout(() => nextStep(), 500)
    } catch (err) {
      clearInterval(interval)
      analysisProgress.value = 0
      uiStore.showError('Erreur lors de l\'analyse')
    }
  }
})

const showRepairFlow = ref(false)
const selectedGuide = ref<RepairGuide | null>(null)
const selectedComponent = ref<Component | null>(null)

const onStartRepair = (guide: RepairGuide, component?: Component) => {
  selectedGuide.value = guide
  selectedComponent.value = component || null
  showRepairFlow.value = true
}

const onRepairComplete = () => {
  showRepairFlow.value = false
  nextStep()
}

const onViewGuide = (guideId: number) => {
  router.push(`/depannage/guide/${guideId}`)
}

const onSaveDiagnostic = () => {
  uiStore.showSuccess('Diagnostic sauvegardé')
}

const onPrintReport = () => {
  window.print()
}

const toasts = computed(() => uiStore.toasts)
</script>

<style scoped>
.diagnostic-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  padding: 2rem 1rem;
}

.diagnostic-header {
  text-align: center;
  margin-bottom: 2rem;
}

.diagnostic-header h1 {
  font-size: 2.5rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 0.5rem;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.subtitle {
  color: #64748b;
  font-size: 1.1rem;
}

.diagnostic-content {
  max-width: 1000px;
  margin: 0 auto;
  background: white;
  border-radius: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  padding: 2rem;
  min-height: 400px;
}

.toast-container {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.toast {
  padding: 1rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 500;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  animation: slideIn 0.3s ease-out;
}

.toast--success {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.toast--error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.toast--warning {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fde68a;
}

.toast--info {
  background: #dbeafe;
  color: #1e40af;
  border: 1px solid #bfdbfe;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }

  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

@media (max-width: 768px) {
  .diagnostic-header h1 {
    font-size: 1.75rem;
  }

  .diagnostic-content {
    padding: 1rem;
    border-radius: 1rem;
  }
}
</style>