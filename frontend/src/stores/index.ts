// ============================================================
// STORES PINIA - AIDE PHONE RÉPARATION
// ============================================================

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/api/client'
import type {
  Device,
  Symptom,
  Component,
  RepairGuide,
  ReplacementPart,
  SecretCode,
  SecretCodeDetail,
  EvolutionEvent,
  ProTool,
  Resource,
  PanneType,
  DiagnosticResult,
  Analysis,
  Toast,
  DiagnosticStep,
  DeviceInfo,
} from '@/types'

// ============================================
// STORE UI (Toasts, Loading global)
// ============================================
export const useUiStore = defineStore('ui', () => {
  const toasts = ref<Toast[]>([])
  const isGlobalLoading = ref(false)

  const addToast = (toast: Omit<Toast, 'id'>) => {
    const id = Date.now().toString() + Math.random().toString(36).slice(2, 7)
    toasts.value.push({ ...toast, id, duration: toast.duration ?? 4000 })
    setTimeout(() => removeToast(id), toast.duration ?? 4000)
  }

  const removeToast = (id: string) => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  const showSuccess = (message: string) => addToast({ type: 'success', message })
  const showError = (message: string) => addToast({ type: 'error', message })
  const showWarning = (message: string) => addToast({ type: 'warning', message })
  const showInfo = (message: string) => addToast({ type: 'info', message })

  return {
    toasts,
    isGlobalLoading,
    addToast,
    removeToast,
    showSuccess,
    showError,
    showWarning,
    showInfo,
  }
})

// ============================================
// STORE APPAREILS (Marques & Modèles)
// ============================================
export const useDeviceStore = defineStore('device', () => {
  const brands = ref<string[]>([])
  const devices = ref<Device[]>([])
  const currentDevice = ref<Device | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const devicesByBrand = computed(() => {
    const map = new Map<string, Device[]>()
    devices.value.forEach(d => {
      const list = map.get(d.brand) || []
      list.push(d)
      map.set(d.brand, list)
    })
    return map
  })

  const getDeviceBySlug = (slug: string) => devices.value.find(d => d.slug === slug)

  const fetchBrands = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/devices/brands')
      brands.value = response.data.brands || []
      console.log('[Store] Marques reçues:', brands.value)
      return brands.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des marques'
      console.error('[Store] fetchBrands:', error.value)
      return []
    } finally {
      isLoading.value = false
    }
  }

  const fetchDevicesByBrand = async (brand: string) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await apiClient.get(`/devices/by-brand/${encodeURIComponent(brand)}`)
      devices.value = response.data.devices || []
      console.log('[Store] Appareils reçus:', devices.value.length)
      return devices.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des appareils'
      console.error('[Store] fetchDevicesByBrand:', error.value)
      return []
    } finally {
      isLoading.value = false
    }
  }

  const fetchDeviceDetail = async (slug: string) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await apiClient.get(`/devices/${encodeURIComponent(slug)}`)
      currentDevice.value = response.data.device || null
      return currentDevice.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement du détail'
      console.error('[Store] fetchDeviceDetail:', error.value)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const searchDevices = async (query: string) => {
    if (!query || query.length < 2) return
    isLoading.value = true
    try {
      const response = await apiClient.get('/devices/search', { params: { q: query } })
      devices.value = response.data.results || []
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erreur de recherche'
    } finally {
      isLoading.value = false
    }
  }

  return {
    brands,
    devices,
    currentDevice,
    isLoading,
    error,
    devicesByBrand,
    getDeviceBySlug,
    fetchBrands,
    fetchDevicesByBrand,
    fetchDeviceDetail,
    searchDevices,
  }
})

// ============================================
// STORE DIAGNOSTIC (Moteur de diagnostic)
// ============================================
export const useDiagnosticStore = defineStore('diagnostic', () => {
  // ── State ─────────────────────────────────
  const currentStep = ref<DiagnosticStep>('device')
  const steps = ref([
    { key: 'device' as DiagnosticStep, label: 'Appareil', icon: '📱' },
    { key: 'symptom' as DiagnosticStep, label: 'Symptômes', icon: '🔍' },
    { key: 'analysis' as DiagnosticStep, label: 'Analyse', icon: '⚙️' },
    { key: 'result' as DiagnosticStep, label: 'Solutions', icon: '💡' },
    { key: 'validation' as DiagnosticStep, label: 'Validation', icon: '✅' },
  ])
  const deviceInfo = ref<DeviceInfo | null>(null)
  const selectedDevice = ref<Device | null>(null)
  const selectedSymptoms = ref<number[]>([])
  const availableSymptoms = ref<Symptom[]>([])
  const diagnosticResult = ref<DiagnosticResult | null>(null)
  const analysisResults = ref<Analysis[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const sessionId = ref<string | null>(null)

  // ── Getters ───────────────────────────────
  const currentStepIndex = computed(() =>
    steps.value.findIndex(s => s.key === currentStep.value)
  )

  const isFirstStep = computed(() => currentStep.value === 'device')
  const isLastStep = computed(() => currentStep.value === 'validation')

  const canProceed = computed(() => {
    switch (currentStep.value) {
      case 'device': return !!selectedDevice.value
      case 'symptom': return selectedSymptoms.value.length > 0
      case 'analysis': return !isLoading.value
      case 'result': return !!diagnosticResult.value
      case 'validation': return true
      default: return false
    }
  })

  const progressPercentage = computed(() =>
    ((currentStepIndex.value + 1) / steps.value.length) * 100
  )

  const selectedSymptomsDetails = computed(() =>
    availableSymptoms.value.filter(s => selectedSymptoms.value.includes(s.id))
  )

  const severityColor = computed(() => {
    const severity = diagnosticResult.value?.severity
    switch (severity) {
      case 'critical': return 'text-red-600 bg-red-50 border-red-200'
      case 'high': return 'text-orange-600 bg-orange-50 border-orange-200'
      case 'medium': return 'text-yellow-600 bg-yellow-50 border-yellow-200'
      case 'low': return 'text-green-600 bg-green-50 border-green-200'
      default: return 'text-gray-600 bg-gray-50 border-gray-200'
    }
  })

  // ── Actions ───────────────────────────────
  const setDevice = (device: Device) => {
    selectedDevice.value = device
    deviceInfo.value = { brand: device.brand, model: device.model }
  }

  const toggleSymptom = (symptomId: number) => {
    const index = selectedSymptoms.value.indexOf(symptomId)
    if (index > -1) {
      selectedSymptoms.value.splice(index, 1)
    } else {
      selectedSymptoms.value.push(symptomId)
    }
  }

  const selectAllSymptoms = () => {
    selectedSymptoms.value = availableSymptoms.value.map(s => s.id)
  }

  const clearSymptoms = () => {
    selectedSymptoms.value = []
  }

  const fetchSymptomsByDevice = async (deviceId: number) => {
    isLoading.value = true
    error.value = null
    try {
      // Essayer par appareil
      try {
        const response = await apiClient.get(`/symptoms/by-device/${deviceId}`)
        availableSymptoms.value = response.data.data || []
        if (availableSymptoms.value.length > 0) {
          console.log('[Store] Symptômes par appareil:', availableSymptoms.value.length)
          return
        }
      } catch (e) {
        console.warn('[Store] /symptoms/by-device indisponible')
      }
      
      // Fallback : tous les symptômes
      const response = await apiClient.get('/symptoms')
      availableSymptoms.value = response.data.data || []
      console.log('[Store] Symptômes fallback:', availableSymptoms.value.length)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erreur chargement symptômes'
      console.error('[Store] fetchSymptomsByDevice:', error.value)
    } finally {
      isLoading.value = false
    }
  }

  const initializeDiagnostic = async () => {
    if (!selectedDevice.value) return
    isLoading.value = true
    error.value = null
    try {
      const response = await apiClient.post('/diagnostic/initialize', {
        brand: selectedDevice.value.brand,
        model: selectedDevice.value.model,
      })
      sessionId.value = response.data.data?.session_id || null
      await fetchSymptomsByDevice(selectedDevice.value.id)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erreur initialisation'
      console.error('[Store] initializeDiagnostic:', error.value)
    } finally {
      isLoading.value = false
    }
  }

  const analyzeSymptoms = async () => {
    if (selectedSymptoms.value.length === 0 || !sessionId.value) return
    isLoading.value = true
    error.value = null
    try {
      const response = await apiClient.post('/diagnostic/analyze', {
        symptoms: selectedSymptoms.value,
        device_id: selectedDevice.value?.id,
        session_id: sessionId.value,
      })
      diagnosticResult.value = response.data.data || null
      analysisResults.value = []
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erreur analyse'
      console.error('[Store] analyzeSymptoms:', error.value)
    } finally {
      isLoading.value = false
    }
  }

  const validateResults = async (notes?: string) => {
    if (!sessionId.value || !diagnosticResult.value) return
    isLoading.value = true
    try {
      await apiClient.post('/diagnostic/validate', {
        session_id: sessionId.value,
        confirmed_symptoms: selectedSymptoms.value,
        notes,
      })
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erreur validation'
    } finally {
      isLoading.value = false
    }
  }

  const nextStep = () => {
    const stepOrder: DiagnosticStep[] = ['device', 'symptom', 'analysis', 'result', 'validation']
    const currentIndex = stepOrder.indexOf(currentStep.value)
    if (currentIndex < stepOrder.length - 1 && canProceed.value) {
      currentStep.value = stepOrder[currentIndex + 1]
    }
  }

  const prevStep = () => {
    const stepOrder: DiagnosticStep[] = ['device', 'symptom', 'analysis', 'result', 'validation']
    const currentIndex = stepOrder.indexOf(currentStep.value)
    if (currentIndex > 0) {
      currentStep.value = stepOrder[currentIndex - 1]
    }
  }

  const goToStep = (step: DiagnosticStep) => {
    const stepOrder: DiagnosticStep[] = ['device', 'symptom', 'analysis', 'result', 'validation']
    const targetIndex = stepOrder.indexOf(step)
    const currentIndex = stepOrder.indexOf(currentStep.value)
    if (targetIndex <= currentIndex || (targetIndex === currentIndex + 1 && canProceed.value)) {
      currentStep.value = step
    }
  }

  const reset = () => {
    currentStep.value = 'device'
    deviceInfo.value = null
    selectedDevice.value = null
    selectedSymptoms.value = []
    availableSymptoms.value = []
    diagnosticResult.value = null
    analysisResults.value = []
    sessionId.value = null
    error.value = null
    isLoading.value = false
  }

  return {
    currentStep,
    steps,
    deviceInfo,
    selectedDevice,
    selectedSymptoms,
    availableSymptoms,
    diagnosticResult,
    analysisResults,
    isLoading,
    error,
    sessionId,
    currentStepIndex,
    isFirstStep,
    isLastStep,
    canProceed,
    progressPercentage,
    selectedSymptomsDetails,
    severityColor,
    setDevice,
    toggleSymptom,
    selectAllSymptoms,
    clearSymptoms,
    fetchSymptomsByDevice,
    initializeDiagnostic,
    analyzeSymptoms,
    validateResults,
    nextStep,
    prevStep,
    goToStep,
    reset,
  }
})

// ============================================
// STORE COMPOSANTS
// ============================================
export const useComponentStore = defineStore('component', () => {
  const components = ref<Component[]>([])
  const categories = ref<string[]>([])
  const currentComponent = ref<Component | null>(null)
  const isLoading = ref(false)

  const fetchComponents = async () => {
    isLoading.value = true
    try {
      const response = await apiClient.get('/components')
      components.value = response.data.data || []
    } catch (err) {
      console.error('[Store] fetchComponents:', err)
    } finally {
      isLoading.value = false
    }
  }

  const fetchCategories = async () => {
    try {
      const response = await apiClient.get('/components/categories')
      categories.value = response.data.data || []
    } catch (err) {
      console.error('[Store] fetchCategories:', err)
    }
  }

  const fetchComponentById = async (id: number) => {
    isLoading.value = true
    try {
      const response = await apiClient.get(`/components/${id}`)
      currentComponent.value = response.data.data || null
    } catch (err) {
      console.error('[Store] fetchComponentById:', err)
    } finally {
      isLoading.value = false
    }
  }

  return {
    components,
    categories,
    currentComponent,
    isLoading,
    fetchComponents,
    fetchCategories,
    fetchComponentById,
  }
})

// ============================================
// STORE CODES SECRETS
// ============================================
export const useCodeStore = defineStore('code', () => {
  const codes = ref<SecretCode[]>([])
  const codesByModel = ref<SecretCodeDetail[]>([])
  const categories = ref<string[]>([])
  const isLoading = ref(false)

  const fetchAllCodes = async () => {
    isLoading.value = true
    try {
      const response = await apiClient.get('/codes')
      codes.value = response.data.data || []
    } catch (err) {
      console.error('[Store] fetchAllCodes:', err)
    } finally {
      isLoading.value = false
    }
  }

  const fetchCodesByBrand = async (brand: string) => {
    isLoading.value = true
    try {
      const response = await apiClient.get(`/codes/by-brand/${encodeURIComponent(brand)}`)
      codesByModel.value = response.data.data || []
    } catch (err) {
      console.error('[Store] fetchCodesByBrand:', err)
    } finally {
      isLoading.value = false
    }
  }

  const fetchCategories = async () => {
    try {
      const response = await apiClient.get('/codes/categories')
      categories.value = response.data.data || []
    } catch (err) {
      console.error('[Store] fetchCategories:', err)
    }
  }

  return {
    codes,
    codesByModel,
    categories,
    isLoading,
    fetchAllCodes,
    fetchCodesByBrand,
    fetchCategories,
  }
})

// ============================================
// STORE ÉVOLUTION
// ============================================
export const useEvolutionStore = defineStore('evolution', () => {
  const events = ref<EvolutionEvent[]>([])
  const trends = ref<Record<string, number>>({})
  const isLoading = ref(false)

  const fetchEvents = async () => {
    isLoading.value = true
    try {
      const response = await apiClient.get('/evolution')
      events.value = response.data.data || []
    } catch (err) {
      console.error('[Store] fetchEvents:', err)
    } finally {
      isLoading.value = false
    }
  }

  const fetchTrends = async () => {
    try {
      const response = await apiClient.get('/evolution/trends')
      trends.value = response.data.data || {}
    } catch (err) {
      console.error('[Store] fetchTrends:', err)
    }
  }

  const createEvent = async (data: Partial<EvolutionEvent>) => {
    try {
      await apiClient.post('/evolution', data)
      await fetchEvents()
    } catch (err) {
      console.error('[Store] createEvent:', err)
    }
  }

  return {
    events,
    trends,
    isLoading,
    fetchEvents,
    fetchTrends,
    createEvent,
  }
})

// ============================================
// STORE OUTILS
// ============================================
export const useToolStore = defineStore('tool', () => {
  const tools = ref<ProTool[]>([])
  const starterKit = ref<ProTool[]>([])
  const isLoading = ref(false)

  const fetchTools = async () => {
    isLoading.value = true
    try {
      const response = await apiClient.get('/tools')
      tools.value = response.data.data || []
    } catch (err) {
      console.error('[Store] fetchTools:', err)
    } finally {
      isLoading.value = false
    }
  }

  const fetchStarterKit = async () => {
    try {
      const response = await apiClient.get('/tools/starter-kit')
      starterKit.value = response.data.data || []
    } catch (err) {
      console.error('[Store] fetchStarterKit:', err)
    }
  }

  return {
    tools,
    starterKit,
    isLoading,
    fetchTools,
    fetchStarterKit,
  }
})

// ============================================
// STORE RESSOURCES
// ============================================
export const useResourceStore = defineStore('resource', () => {
  const resources = ref<Resource[]>([])
  const isLoading = ref(false)

  const fetchResources = async () => {
    isLoading.value = true
    try {
      const response = await apiClient.get('/resources')
      resources.value = response.data.data || []
    } catch (err) {
      console.error('[Store] fetchResources:', err)
    } finally {
      isLoading.value = false
    }
  }

  const softwareResources = computed(() =>
    resources.value.filter(r => r.category === 'software' || r.category === 'both')
  )

  const hardwareResources = computed(() =>
    resources.value.filter(r => r.category === 'hardware' || r.category === 'both')
  )

  return {
    resources,
    isLoading,
    softwareResources,
    hardwareResources,
    fetchResources,
  }
})

// ============================================
// STORE RECHERCHE GLOBALE
// ============================================
export const useSearchStore = defineStore('search', () => {
  const query = ref('')
  const results = ref<Array<{ type: string; title: string; url: string }>>([])
  const isSearching = ref(false)

  const search = async (q: string) => {
    if (!q || q.length < 2) {
      results.value = []
      return
    }
    query.value = q
    isSearching.value = true
    try {
      const response = await apiClient.get('/devices/search', { params: { q } })
      const deviceResults = (response.data.results || []).map((d: Device) => ({
        type: 'device',
        title: `${d.brand} ${d.model}`,
        url: `/diagnostic?device=${d.slug}`,
      }))
      results.value = deviceResults
    } catch (err) {
      console.error('[Store] search:', err)
    } finally {
      isSearching.value = false
    }
  }

  return {
    query,
    results,
    isSearching,
    search,
  }
})