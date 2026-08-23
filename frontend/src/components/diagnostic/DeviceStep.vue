<template>
  <div class="device-step">
    <h2 class="step-title">
      📱 Sélectionnez l'appareil
    </h2>
    <p class="step-subtitle">
      Choisissez la marque et le modèle de l'appareil à diagnostiquer
    </p>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <span>Chargement des appareils...</span>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <button @click="reload" class="retry-btn">
        🔄 Réessayer
      </button>
    </div>

    <!-- Contenu -->
    <div v-else class="step-content">
      <!-- Étape 1 : Choix de la marque -->
      <div v-if="!localSelectedBrand" class="brand-section">
        <h3 class="section-title">Choisir une marque</h3>
        <div class="brand-grid">
          <button
            v-for="brand in brands"
            :key="brand"
            @click="onSelectBrand(brand)"
            class="brand-card"
            :class="{ 'brand-card--active': localSelectedBrand === brand }"
          >
            <div class="brand-logo">
              {{ getBrandInitial(brand) }}
            </div>
            <span class="brand-name">{{ brand }}</span>
            <span class="brand-count">{{ getDeviceCount(brand) }} modèles</span>
          </button>
        </div>
      </div>

      <!-- Étape 2 : Choix du modèle -->
      <div v-else-if="!localSelectedDevice" class="model-section">
        <div class="section-header">
          <h3 class="section-title">
            Modèles {{ localSelectedBrand }}
          </h3>
          <button 
            @click="backToBrands"
            class="back-btn"
          >
            ← Changer de marque
          </button>
        </div>

        <div v-if="filteredDevices.length === 0" class="empty-state">
          <p>Aucun modèle trouvé pour cette marque</p>
        </div>

        <div v-else class="model-grid">
          <button
            v-for="device in filteredDevices"
            :key="device.id"
            @click="onSelectDevice(device)"
            class="model-card"
            :class="{ 'model-card--active': localSelectedDevice?.id === device.id }"
          >
            <div class="model-info">
              <div class="model-header">
                <h4 class="model-name">{{ device.model }}</h4>
                <span v-if="device.year" class="model-year">{{ device.year }}</span>
              </div>
              <p v-if="device.specs" class="model-specs">
                {{ formatSpecs(device.specs) }}
              </p>
            </div>
            <div class="model-arrow">→</div>
          </button>
        </div>
      </div>

      <!-- Étape 3 : Confirmation -->
      <div v-else class="confirmation-section">
        <div class="confirmation-card">
          <div class="confirmation-header">
            <h3 class="section-title">Appareil sélectionné</h3>
            <button 
              @click="backToModels"
              class="back-btn"
            >
              Changer de modèle
            </button>
          </div>

          <div class="device-summary">
            <div class="device-icon">📱</div>
            <div class="device-details">
              <h4 class="device-name">
                {{ localSelectedDevice.brand }} {{ localSelectedDevice.model }}
              </h4>
              <p v-if="localSelectedDevice.year" class="device-meta">
                Sorti en {{ localSelectedDevice.year }}
              </p>
            </div>
          </div>

          <!-- Champs optionnels -->
          <div class="optional-fields">
            <div class="field-group">
              <label class="field-label">
                IMEI (optionnel)
              </label>
              <input
                v-model="imei"
                type="text"
                placeholder="35xxxxxxxxxxxx"
                maxlength="15"
                class="field-input"
              />
            </div>
            <div class="field-group">
              <label class="field-label">
                Version OS (optionnel)
              </label>
              <input
                v-model="osVersion"
                type="text"
                placeholder="Ex: Android 14, iOS 17"
                class="field-input"
              />
            </div>
          </div>

          <button
            @click="confirmDevice"
            :disabled="initializing"
            class="confirm-btn"
          >
            <span v-if="initializing" class="btn-loading">
              <span class="spinner-sm"></span>
              Initialisation...
            </span>
            <span v-else>
              🚀 Démarrer le diagnostic
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { Device } from '@/types'

// ── Props ───────────────────────────────────────────
const props = defineProps<{
  brands: string[]
  devices: Device[]
  loading: boolean
  selectedDevice: Device | null
}>()

// ── Emits ───────────────────────────────────────────
const emit = defineEmits<{
  (e: 'select-brand', brand: string): void
  (e: 'select-device', device: Device): void
  (e: 'next'): void
}>()

// ── State local ─────────────────────────────────────
const localSelectedBrand = ref<string | null>(null)
const localSelectedDevice = ref<Device | null>(null)
const imei = ref('')
const osVersion = ref('')
const initializing = ref(false)
const error = ref<string | null>(null)

// ── Computed ────────────────────────────────────────
const filteredDevices = computed(() => {
  if (!localSelectedBrand.value) return []
  return props.devices.filter(d => 
    d.brand.toLowerCase() === localSelectedBrand.value?.toLowerCase()
  )
})

// ── Watchers ────────────────────────────────────────
watch(() => props.selectedDevice, (newVal) => {
  if (newVal) {
    localSelectedDevice.value = newVal
    localSelectedBrand.value = newVal.brand
  }
})

// ── Méthodes ────────────────────────────────────────
const getBrandInitial = (brand: string): string => {
  return brand.charAt(0).toUpperCase()
}

const getDeviceCount = (brand: string): number => {
  return props.devices.filter(d => d.brand === brand).length
}

const formatSpecs = (specs: Record<string, string>): string => {
  const priority = ['screen', 'processor', 'battery', 'ram', 'storage']
  const parts: string[] = []
  priority.forEach(key => {
    if (specs[key]) parts.push(specs[key])
  })
  return parts.join(' · ') || 'Spécifications non disponibles'
}

const onSelectBrand = (brand: string) => {
  localSelectedBrand.value = brand
  emit('select-brand', brand)
}

const onSelectDevice = (device: Device) => {
  localSelectedDevice.value = device
  emit('select-device', device)
}

const backToBrands = () => {
  localSelectedBrand.value = null
  localSelectedDevice.value = null
  imei.value = ''
  osVersion.value = ''
}

const backToModels = () => {
  localSelectedDevice.value = null
}

const confirmDevice = async () => {
  if (!localSelectedDevice.value) return
  
  initializing.value = true
  error.value = null

  try {
    const deviceData = {
      brand: localSelectedDevice.value.brand,
      model: localSelectedDevice.value.model,
      imei: imei.value || undefined,
      os_version: osVersion.value || undefined,
    }
    
    // Émettre l'événement pour que la vue parent gère l'initialisation
    emit('select-device', localSelectedDevice.value)
    emit('next')
  } catch (err: any) {
    error.value = err.message || 'Erreur lors de l\'initialisation'
  } finally {
    initializing.value = false
  }
}

const reload = () => {
  error.value = null
  // La vue parent rechargera les données
}
</script>

<style scoped>
/* ── Layout ────────────────────────────────────────── */
.device-step {
  animation: fadeIn 0.4s ease-out;
}

.step-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.step-subtitle {
  color: #64748b;
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 1rem;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

/* ── Loading & Error ───────────────────────────────── */
.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #6b7280;
}

.spinner {
  width: 2rem;
  height: 2rem;
  border: 3px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-right: 0.75rem;
}

.spinner-sm {
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-right: 0.5rem;
  display: inline-block;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state {
  text-align: center;
  padding: 2rem;
  color: #dc2626;
}

.retry-btn {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
  background: #fee2e2;
  color: #dc2626;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s;
}

.retry-btn:hover {
  background: #fecaca;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #9ca3af;
}

/* ── Brand Grid ────────────────────────────────────── */
.brand-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

@media (min-width: 640px) {
  .brand-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 1024px) {
  .brand-grid {
    grid-template-columns: repeat(5, 1fr);
  }
}

.brand-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.25rem 1rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.brand-card:hover {
  border-color: #3b82f6;
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.15);
}

.brand-card--active {
  border-color: #3b82f6;
  background: #eff6ff;
}

.brand-logo {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  color: white;
  font-size: 1.5rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.75rem;
}

.brand-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
}

.brand-count {
  font-size: 0.75rem;
  color: #94a3b8;
  margin-top: 0.25rem;
}

/* ── Model Grid ────────────────────────────────────── */
.model-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.75rem;
}

@media (min-width: 768px) {
  .model-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.model-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 0.75rem;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s ease;
}

.model-card:hover {
  border-color: #3b82f6;
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.model-card--active {
  border-color: #3b82f6;
  background: #eff6ff;
}

.model-info {
  flex: 1;
}

.model-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.model-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
}

.model-year {
  padding: 0.125rem 0.5rem;
  background: #dbeafe;
  color: #1d4ed8;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.model-specs {
  font-size: 0.8rem;
  color: #6b7280;
  margin-top: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.model-arrow {
  color: #cbd5e1;
  font-size: 1.25rem;
  transition: color 0.2s;
}

.model-card:hover .model-arrow {
  color: #3b82f6;
}

/* ── Back Button ───────────────────────────────────── */
.back-btn {
  font-size: 0.875rem;
  color: #3b82f6;
  font-weight: 500;
  transition: color 0.2s;
}

.back-btn:hover {
  color: #1d4ed8;
  text-decoration: underline;
}

/* ── Confirmation ──────────────────────────────────── */
.confirmation-card {
  background: linear-gradient(135deg, #eff6ff, #eef2ff);
  border: 2px solid #bfdbfe;
  border-radius: 1rem;
  padding: 1.5rem;
}

.confirmation-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.device-summary {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 0.75rem;
  margin-bottom: 1.5rem;
}

.device-icon {
  font-size: 2.5rem;
}

.device-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

.device-meta {
  font-size: 0.875rem;
  color: #6b7280;
}

/* ── Optional Fields ───────────────────────────────── */
.optional-fields {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

@media (min-width: 640px) {
  .optional-fields {
    grid-template-columns: repeat(2, 1fr);
  }
}

.field-group {
  display: flex;
  flex-direction: column;
}

.field-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.375rem;
}

.field-input {
  padding: 0.625rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  background: white;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.field-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.field-input::placeholder {
  color: #9ca3af;
}

/* ── Confirm Button ────────────────────────────────── */
.confirm-btn {
  width: 100%;
  padding: 0.875rem;
  background: linear-gradient(135deg, #2563eb, #4f46e5);
  color: white;
  font-weight: 600;
  font-size: 1rem;
  border-radius: 0.75rem;
  transition: all 0.2s;
  box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
}

.confirm-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.confirm-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-loading {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── Animations ────────────────────────────────────── */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.brand-section, .model-section, .confirmation-section {
  animation: fadeIn 0.3s ease-out;
}
</style>