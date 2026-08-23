// ============================================================
// COMPOSABLE useDiagnostic - AIDE PHONE RÉPARATION
// ============================================================

import { computed } from 'vue'
import { useDiagnosticStore, useDeviceStore, useUiStore } from '@/stores'
import type { DeviceInfo, Device, Symptom, DiagnosticResult } from '@/types'

export function useDiagnostic() {
  const diagnosticStore = useDiagnosticStore()
  const deviceStore = useDeviceStore()
  const uiStore = useUiStore()

  const sessionId = computed(() => diagnosticStore.sessionId)
  const deviceInfo = computed(() => diagnosticStore.deviceInfo)
  const selectedDevice = computed(() => diagnosticStore.selectedDevice)
  const selectedSymptoms = computed(() => diagnosticStore.selectedSymptoms)
  const availableSymptoms = computed(() => diagnosticStore.availableSymptoms)
  const diagnosticResult = computed(() => diagnosticStore.diagnosticResult)
  const analysisResults = computed(() => diagnosticStore.analysisResults)
  const isLoading = computed(() => diagnosticStore.isLoading || deviceStore.isLoading)
  const error = computed(() => diagnosticStore.error)
  const currentStep = computed(() => diagnosticStore.currentStep)
  const canProceed = computed(() => diagnosticStore.canProceed)
  const progressPercentage = computed(() => diagnosticStore.progressPercentage)
  const selectedSymptomsDetails = computed(() => diagnosticStore.selectedSymptomsDetails)
  const severityColor = computed(() => diagnosticStore.severityColor)

  const fetchBrands = async (): Promise<string[]> => {
    await deviceStore.fetchBrands()
    return deviceStore.brands
  }

  const fetchDevicesByBrand = async (brand: string): Promise<Device[]> => {
    await deviceStore.fetchDevicesByBrand(brand)
    return deviceStore.devices
  }

  const fetchSymptomsByDevice = async (deviceId: number): Promise<void> => {
    await diagnosticStore.fetchSymptomsByDevice(deviceId)
  }

  const selectDevice = (device: Device) => {
    diagnosticStore.setDevice(device)
  }

  const initializeDiagnostic = async (info?: DeviceInfo): Promise<void> => {
    if (!selectedDevice.value && !info) {
      uiStore.showError('Veuillez sélectionner un appareil')
      throw new Error('Aucun appareil sélectionné')
    }
    try {
      await diagnosticStore.initializeDiagnostic()
      uiStore.showSuccess('Diagnostic initialisé avec succès')
    } catch (err: any) {
      uiStore.showError(err.message || 'Erreur lors de l\'initialisation')
      throw err
    }
  }

  const toggleSymptom = (symptomId: number) => {
    diagnosticStore.toggleSymptom(symptomId)
  }

  const selectAllSymptoms = () => {
    diagnosticStore.selectAllSymptoms()
  }

  const clearSymptoms = () => {
    diagnosticStore.clearSymptoms()
  }

  const analyzeSymptoms = async (): Promise<DiagnosticResult | null> => {
    if (selectedSymptoms.value.length === 0) {
      uiStore.showWarning('Veuillez sélectionner au moins un symptôme')
      return null
    }
    try {
      await diagnosticStore.analyzeSymptoms()
      uiStore.showSuccess('Analyse terminée')
      return diagnosticStore.diagnosticResult
    } catch (err: any) {
      uiStore.showError(err.message || 'Erreur lors de l\'analyse')
      throw err
    }
  }

  const validateResults = async (notes?: string): Promise<void> => {
    try {
      await diagnosticStore.validateResults(notes)
      uiStore.showSuccess('Résultats validés')
    } catch (err: any) {
      uiStore.showError(err.message || 'Erreur lors de la validation')
      throw err
    }
  }

  const nextStep = () => {
    if (!canProceed.value) {
      uiStore.showWarning('Veuillez compléter cette étape avant de continuer')
      return
    }
    diagnosticStore.nextStep()
  }

  const prevStep = () => {
    diagnosticStore.prevStep()
  }

  const reset = () => {
    diagnosticStore.reset()
    deviceStore.$reset()
    uiStore.showInfo('Diagnostic réinitialisé')
  }

  const hasCriticalSymptom = computed(() => {
    if (!diagnosticResult.value) return false
    return diagnosticResult.value.severity === 'critical'
  })

  const getSymptomById = (id: number): Symptom | undefined => {
    return availableSymptoms.value.find(s => s.id === id)
  }

  const isSymptomSelected = (id: number): boolean => {
    return selectedSymptoms.value.includes(id)
  }

  return {
    sessionId,
    deviceInfo,
    selectedDevice,
    selectedSymptoms,
    availableSymptoms,
    diagnosticResult,
    analysisResults,
    isLoading,
    error,
    currentStep,
    canProceed,
    progressPercentage,
    selectedSymptomsDetails,
    severityColor,
    fetchBrands,
    fetchDevicesByBrand,
    fetchSymptomsByDevice,
    selectDevice,
    toggleSymptom,
    selectAllSymptoms,
    clearSymptoms,
    getSymptomById,
    isSymptomSelected,
    initializeDiagnostic,
    analyzeSymptoms,
    validateResults,
    nextStep,
    prevStep,
    reset,
    hasCriticalSymptom,
  }
}