<template>
  <div class="symptom-step">
    <h2 class="step-title">
      🩺 Sélectionnez les symptômes
    </h2>
    <p class="step-subtitle">
      Cochez tous les symptômes observés sur <strong v-if="device">{{ device.brand }} {{ device.model }}</strong>
    </p>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <span>Chargement des symptômes...</span>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <button @click="reload" class="retry-btn">
        🔄 Réessayer
      </button>
    </div>

    <!-- Empty -->
    <div v-else-if="Object.keys(groupedSymptoms).length === 0" class="empty-state">
      <p>Aucun symptôme disponible pour cet appareil</p>
    </div>

    <!-- Symptoms by Category -->
    <div v-else class="symptoms-container">
      <div v-for="(symptoms, category) in groupedSymptoms" :key="category" class="category-section">
        <h3 class="category-title">
          <span class="category-icon">{{ getCategoryIcon(category) }}</span>
          <span>{{ formatCategory(category) }}</span>
          <span class="category-count">({{ symptoms.length }})</span>
        </h3>
        
        <div class="symptoms-grid">
          <label
            v-for="symptom in symptoms"
            :key="symptom.id"
            class="symptom-card"
            :class="{ 
              'symptom-card--selected': isSelected(symptom.id),
              'symptom-card--critical': symptom.severity === 'critical',
              'symptom-card--high': symptom.severity === 'high',
            }"
          >
            <input
              type="checkbox"
              :value="symptom.id"
              :checked="isSelected(symptom.id)"
              @change="toggleSymptom(symptom.id)"
              class="hidden-input"
            />
            <div class="symptom-content">
              <div class="symptom-header">
                <span class="symptom-name">{{ symptom.name }}</span>
                <SeverityBadge :severity="symptom.severity" />
              </div>
              <p class="symptom-desc">{{ symptom.description }}</p>
              <div v-if="symptom.common_causes?.length" class="symptom-causes">
                <span class="causes-label">Causes probables :</span>
                <span 
                  v-for="cause in symptom.common_causes.slice(0, 3)" 
                  :key="cause"
                  class="cause-tag"
                >
                  {{ cause }}
                </span>
              </div>
            </div>
            <div class="symptom-check">
              <span v-if="isSelected(symptom.id)" class="check-icon">✓</span>
              <span v-else class="check-placeholder"></span>
            </div>
          </label>
        </div>
      </div>

      <!-- Barre d'actions fixe -->
      <div class="actions-bar">
        <div class="actions-content">
          <div class="selection-info">
            <span class="selection-count">
              <strong>{{ selectedSymptoms.length }}</strong> symptôme(s) sélectionné(s)
            </span>
            <div v-if="hasCritical" class="critical-warning">
              ⚠️ Symptôme critique détecté — intervention urgente recommandée
            </div>
            <div v-else-if="hasHigh" class="high-warning">
              ⚡ Problème sérieux détecté
            </div>
          </div>
          <div class="actions-buttons">
            <button
              @click="$emit('prev')"
              class="btn btn--secondary"
            >
              ← Retour
            </button>
            <button
              @click="confirmSelection"
              :disabled="selectedSymptoms.length === 0"
              class="btn btn--primary"
              :class="{ 'btn--disabled': selectedSymptoms.length === 0 }"
            >
              Analyser →
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Device, Symptom } from '@/types'
import SeverityBadge from './SeverityBadge.vue'

// ── Props ───────────────────────────────────────────
const props = defineProps<{
  symptoms: Symptom[]
  selectedSymptoms: number[]
  loading: boolean
  device: Device | null
  error?: string | null
}>()

// ── Emits ───────────────────────────────────────────
const emit = defineEmits<{
  (e: 'toggle-symptom', symptomId: number): void
  (e: 'select-all'): void
  (e: 'clear-all'): void
  (e: 'next'): void
  (e: 'prev'): void
}>()

// ── Computed ────────────────────────────────────────
const groupedSymptoms = computed(() => {
  const grouped: Record<string, Symptom[]> = {}
  props.symptoms.forEach(s => {
    if (!grouped[s.category]) grouped[s.category] = []
    grouped[s.category].push(s)
  })
  // Trier par ordre de catégorie prédéfini
  const order = ['screen', 'battery', 'charging', 'audio', 'network', 'camera', 'software', 'buttons', 'water', 'overheating', 'performance', 'storage', 'connectivity', 'sensor']
  const sorted: Record<string, Symptom[]> = {}
  order.forEach(cat => {
    if (grouped[cat]) sorted[cat] = grouped[cat]
  })
  // Ajouter les catégories restantes
  Object.keys(grouped).forEach(cat => {
    if (!sorted[cat]) sorted[cat] = grouped[cat]
  })
  return sorted
})

const hasCritical = computed(() => {
  return props.selectedSymptoms.some(id => {
    const s = props.symptoms.find(s => s.id === id)
    return s?.severity === 'critical'
  })
})

const hasHigh = computed(() => {
  return props.selectedSymptoms.some(id => {
    const s = props.symptoms.find(s => s.id === id)
    return s?.severity === 'high'
  })
})

// ── Méthodes ────────────────────────────────────────
const isSelected = (symptomId: number): boolean => {
  return props.selectedSymptoms.includes(symptomId)
}

const toggleSymptom = (symptomId: number) => {
  emit('toggle-symptom', symptomId)
}

const confirmSelection = () => {
  if (props.selectedSymptoms.length > 0) {
    emit('next')
  }
}

const reload = () => {
  // La vue parent rechargera les données
}

const getCategoryIcon = (category: string): string => {
  const icons: Record<string, string> = {
    screen: '📱',
    battery: '🔋',
    charging: '🔌',
    audio: '🔊',
    network: '📡',
    camera: '📷',
    software: '💿',
    buttons: '🔘',
    water: '💧',
    overheating: '🌡️',
    performance: '⚡',
    storage: '💾',
    connectivity: '📶',
    sensor: '🎯',
  }
  return icons[category] || '🔧'
}

const formatCategory = (category: string): string => {
  const labels: Record<string, string> = {
    screen: 'Écran & Affichage',
    battery: 'Batterie',
    charging: 'Charge & Alimentation',
    audio: 'Audio & Son',
    network: 'Réseau & Connexion',
    camera: 'Caméra',
    software: 'Logiciel & Système',
    buttons: 'Boutons & Contrôles',
    water: 'Dégâts des eaux',
    overheating: 'Surchauffe',
    performance: 'Performance',
    storage: 'Stockage',
    connectivity: 'Connectivité',
    sensor: 'Capteurs',
  }
  return labels[category] || category
}
</script>

<style scoped>
/* ── Layout ────────────────────────────────────────── */
.symptom-step {
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

.step-subtitle strong {
  color: #3b82f6;
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
  padding: 3rem;
  color: #9ca3af;
}

/* ── Category Section ──────────────────────────────── */
.category-section {
  margin-bottom: 2rem;
  animation: fadeIn 0.3s ease-out;
}

.category-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.category-icon {
  font-size: 1.25rem;
}

.category-count {
  font-size: 0.875rem;
  color: #9ca3af;
  font-weight: 400;
}

/* ── Symptoms Grid ─────────────────────────────────── */
.symptoms-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.75rem;
}

@media (min-width: 768px) {
  .symptoms-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* ── Symptom Card ──────────────────────────────────── */
.symptom-card {
  display: flex;
  align-items: flex-start;
  gap: 0.875rem;
  padding: 1rem 1.25rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.symptom-card:hover {
  border-color: #93c5fd;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.symptom-card--selected {
  border-color: #3b82f6;
  background: linear-gradient(135deg, #eff6ff, #eef2ff);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.symptom-card--critical {
  border-left: 4px solid #ef4444;
}

.symptom-card--critical:hover {
  border-color: #ef4444;
}

.symptom-card--critical.symptom-card--selected {
  border-color: #ef4444;
  background: linear-gradient(135deg, #fef2f2, #fef2f2);
}

.symptom-card--high {
  border-left: 4px solid #f97316;
}

.symptom-card--high:hover {
  border-color: #f97316;
}

.hidden-input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.symptom-content {
  flex: 1;
  min-width: 0;
}

.symptom-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  margin-bottom: 0.375rem;
}

.symptom-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.95rem;
}

.symptom-desc {
  font-size: 0.85rem;
  color: #64748b;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* ── Causes ────────────────────────────────────────── */
.symptom-causes {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.375rem;
  margin-top: 0.5rem;
}

.causes-label {
  font-size: 0.75rem;
  color: #94a3b8;
}

.cause-tag {
  padding: 0.125rem 0.5rem;
  background: #f1f5f9;
  color: #475569;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
}

/* ── Check Indicator ───────────────────────────────── */
.symptom-check {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 50%;
  border: 2px solid #cbd5e1;
  transition: all 0.2s;
}

.symptom-card--selected .symptom-check {
  background: #3b82f6;
  border-color: #3b82f6;
}

.check-icon {
  color: white;
  font-weight: 700;
  font-size: 0.875rem;
}

.check-placeholder {
  width: 0.75rem;
  height: 0.75rem;
  border-radius: 50%;
  background: #e2e8f0;
}

/* ── Actions Bar ─────────────────────────────────── */
.actions-bar {
  position: sticky;
  bottom: 1rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  padding: 1rem 1.25rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  margin-top: 2rem;
}

.actions-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}

.selection-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.selection-count {
  font-size: 0.9rem;
  color: #475569;
}

.selection-count strong {
  color: #3b82f6;
  font-size: 1.1rem;
}

.critical-warning {
  font-size: 0.8rem;
  color: #dc2626;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.high-warning {
  font-size: 0.8rem;
  color: #f97316;
  font-weight: 500;
}

.actions-buttons {
  display: flex;
  gap: 0.75rem;
}

/* ── Buttons ───────────────────────────────────────── */
.btn {
  padding: 0.625rem 1.25rem;
  border-radius: 0.625rem;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.2s;
  cursor: pointer;
  border: none;
}

.btn--secondary {
  background: #f1f5f9;
  color: #475569;
}

.btn--secondary:hover {
  background: #e2e8f0;
}

.btn--primary {
  background: linear-gradient(135deg, #2563eb, #4f46e5);
  color: white;
  box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
}

.btn--primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.btn--disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ── Animations ────────────────────────────────────── */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ── Responsive ────────────────────────────────────── */
@media (max-width: 640px) {
  .actions-content {
    flex-direction: column;
    align-items: stretch;
  }

  .actions-buttons {
    width: 100%;
  }

  .btn {
    flex: 1;
    text-align: center;
  }
}
</style>