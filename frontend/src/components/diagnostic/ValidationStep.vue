<template>
  <div class="validation-step">
    <!-- Header -->
    <div class="validation-header">
      <div class="success-animation">
        <div class="success-icon">✅</div>
        <div class="success-ring"></div>
      </div>
      <h2 class="validation-title">Diagnostic terminé !</h2>
      <p class="validation-subtitle">
        Voici le récapitulatif complet de votre diagnostic
      </p>
    </div>

    <!-- Carte récapitulative -->
    <div class="summary-card">
      <div class="summary-header">
        <div class="device-info">
          <span class="device-icon">📱</span>
          <div>
            <h3 class="device-name">
              {{ device?.brand }} {{ device?.model }}
            </h3>
            <p v-if="device?.year" class="device-year">
              Année : {{ device.year }}
            </p>
          </div>
        </div>
        <SeverityBadge :severity="result?.severity || 'low'" />
      </div>

      <!-- Statistiques -->
      <div class="summary-stats">
        <div class="stat">
          <span class="stat-value">{{ result?.symptoms.length || 0 }}</span>
          <span class="stat-label">Symptômes</span>
        </div>
        <div class="stat">
          <span class="stat-value">{{ result?.components.length || 0 }}</span>
          <span class="stat-label">Composants</span>
        </div>
        <div class="stat">
          <span class="stat-value">{{ result?.repair_guides.length || 0 }}</span>
          <span class="stat-label">Guides</span>
        </div>
        <div class="stat">
          <span class="stat-value">{{ Math.round((result?.confidence || 0) * 100) }}%</span>
          <span class="stat-label">Confiance</span>
        </div>
      </div>

      <!-- Détails -->
      <div class="summary-details">
        <div class="detail-section">
          <h4 class="detail-title">🔍 Symptômes identifiés</h4>
          <div class="detail-list">
            <div 
              v-for="symptom in result?.symptoms" 
              :key="symptom.id"
              class="detail-item"
            >
              <SeverityBadge :severity="symptom.severity" />
              <span class="detail-name">{{ symptom.name }}</span>
              <span class="detail-category">{{ formatCategory(symptom.category) }}</span>
            </div>
          </div>
        </div>

        <div class="detail-section">
          <h4 class="detail-title">🔧 Composants affectés</h4>
          <div class="detail-list">
            <div 
              v-for="component in result?.components" 
              :key="component.id"
              class="detail-item"
            >
              <span class="detail-icon">{{ getComponentIcon(component.category) }}</span>
              <span class="detail-name">{{ component.name }}</span>
              <span class="detail-type">{{ component.type }}</span>
            </div>
          </div>
        </div>

        <div class="detail-section">
          <h4 class="detail-title">📋 Solutions recommandées</h4>
          <div class="detail-list">
            <div 
              v-for="(guide, index) in result?.repair_guides" 
              :key="guide.id"
              class="detail-item detail-item--guide"
            >
              <span class="guide-number">{{ index + 1 }}</span>
              <div class="guide-info">
                <span class="detail-name">{{ guide.title }}</span>
                <div class="guide-meta">
                  <span :class="['difficulty-tag', `difficulty--${guide.difficulty}`]">
                    {{ difficultyLabel(guide.difficulty) }}
                  </span>
                  <span class="time-tag">⏱️ {{ guide.estimated_time }} min</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="result?.recommendations.length" class="detail-section">
          <h4 class="detail-title">💡 Recommandations</h4>
          <ul class="recommendations-list">
            <li v-for="(rec, idx) in result.recommendations" :key="idx">
              {{ rec }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Coût et temps estimés -->
      <div class="estimates">
        <div class="estimate">
          <span class="estimate-label">Coût estimé</span>
          <span class="estimate-value" :class="{ 'estimate-value--free': totalCost === 0 }">
            {{ totalCost === 0 ? 'Gratuit' : '~' + totalCost + '€' }}
          </span>
        </div>
        <div class="estimate">
          <span class="estimate-label">Temps total estimé</span>
          <span class="estimate-value">{{ totalTime }} min</span>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="actions-grid">
      <button class="btn btn--primary" @click="saveDiagnostic">
        <span class="btn-icon">💾</span>
        <span>Sauvegarder</span>
      </button>
      <button class="btn btn--secondary" @click="shareDiagnostic">
        <span class="btn-icon">🔗</span>
        <span>Partager</span>
      </button>
      <button class="btn btn--secondary" @click="printDiagnostic">
        <span class="btn-icon">🖨️</span>
        <span>Imprimer</span>
      </button>
    </div>

    <button class="btn btn--restart" @click="$emit('restart')">
      🔄 Nouveau diagnostic
    </button>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { DiagnosticResult, Device } from '@/types'
import SeverityBadge from './SeverityBadge.vue'

// ── Props ───────────────────────────────────────────
const props = defineProps<{
  result: DiagnosticResult | null
  device: Device | null
}>()

// ── Emits ───────────────────────────────────────────
const emit = defineEmits<{
  (e: 'restart'): void
  (e: 'save'): void
  (e: 'print'): void
}>()

// ── Computed ────────────────────────────────────────
const totalCost = computed(() => {
  if (!props.result) return 0
  // Calcul basé sur les guides (tu peux ajouter un champ cost dans RepairGuide)
  return 0
})

const totalTime = computed(() => {
  if (!props.result) return 0
  return props.result.repair_guides.reduce((sum, g) => sum + g.estimated_time, 0)
})

// ── Méthodes ────────────────────────────────────────
const difficultyLabel = (difficulty: string): string => {
  const labels: Record<string, string> = {
    easy: 'Facile',
    medium: 'Moyen',
    hard: 'Difficile',
    expert: 'Expert',
  }
  return labels[difficulty] || difficulty
}

const formatCategory = (category: string): string => {
  const labels: Record<string, string> = {
    screen: 'Écran',
    battery: 'Batterie',
    charging: 'Charge',
    audio: 'Audio',
    network: 'Réseau',
    camera: 'Caméra',
    software: 'Logiciel',
    buttons: 'Boutons',
    water: 'Eau',
    overheating: 'Surchauffe',
    performance: 'Performance',
    storage: 'Stockage',
    connectivity: 'Connectivité',
    sensor: 'Capteur',
  }
  return labels[category] || category
}

const getComponentIcon = (category: string): string => {
  const icons: Record<string, string> = {
    display: '📱',
    battery: '🔋',
    processor: '⚡',
    memory: '🧠',
    camera: '📷',
    audio: '🔊',
    connectivity: '📡',
    sensor: '🎯',
    housing: '🏠',
    port: '🔌',
    antenna: '📶',
    security: '🔒',
    motherboard: '🖥️',
    power: '⚡',
  }
  return icons[category] || '🔧'
}

const saveDiagnostic = () => {
  // Sauvegarder dans le localStorage ou via API
  if (props.result && props.device) {
    const diagnostic = {
      id: Date.now().toString(),
      date: new Date().toISOString(),
      device: props.device,
      result: props.result,
    }
    const history = JSON.parse(localStorage.getItem('diagnostic_history') || '[]')
    history.unshift(diagnostic)
    localStorage.setItem('diagnostic_history', JSON.stringify(history))
    
    // Émettre l'événement pour la vue parent
    emit('save')
  }
}

const shareDiagnostic = async () => {
  if (!props.result || !props.device) return
  
  const text = `Diagnostic ${props.device.brand} ${props.device.model}: ${props.result.symptoms.map(s => s.name).join(', ')}`
  
  if (navigator.share) {
    try {
      await navigator.share({
        title: 'Diagnostic Aide Phone Réparation',
        text: text,
        url: window.location.href,
      })
    } catch (err) {
      console.log('Partage annulé')
    }
  } else {
    // Fallback : copier dans le presse-papiers
    await navigator.clipboard.writeText(text)
    alert('Récapitulatif copié dans le presse-papiers !')
  }
}

const printDiagnostic = () => {
  emit('print')
  // La vue parent gère l'impression
  setTimeout(() => window.print(), 100)
}
</script>

<style scoped>
/* ── Layout ────────────────────────────────────────── */
.validation-step {
  animation: fadeIn 0.5s ease-out;
}

/* ── Header ────────────────────────────────────────── */
.validation-header {
  text-align: center;
  margin-bottom: 2rem;
}

.success-animation {
  position: relative;
  width: 80px;
  height: 80px;
  margin: 0 auto 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.success-icon {
  font-size: 3rem;
  z-index: 1;
  animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.success-ring {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 3px solid #22c55e;
  animation: ringPulse 2s ease-out infinite;
}

@keyframes popIn {
  0% { transform: scale(0); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

@keyframes ringPulse {
  0% { transform: scale(0.8); opacity: 1; }
  100% { transform: scale(1.5); opacity: 0; }
}

.validation-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.validation-subtitle {
  color: #64748b;
}

/* ── Summary Card ─────────────────────────────────── */
.summary-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 1.25rem;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.summary-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.device-info {
  display: flex;
  align-items: center;
  gap: 0.875rem;
}

.device-icon {
  font-size: 2rem;
}

.device-name {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.device-year {
  font-size: 0.8rem;
  color: #94a3b8;
  margin: 0;
}

/* ── Stats ─────────────────────────────────────────── */
.summary-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat {
  text-align: center;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 0.75rem;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #3b82f6;
}

.stat-label {
  font-size: 0.75rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* ── Details ───────────────────────────────────────── */
.summary-details {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}

.detail-section {
  padding: 1rem;
  background: #f8fafc;
  border-radius: 0.75rem;
}

.detail-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.75rem;
}

.detail-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.5rem 0.75rem;
  background: white;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.detail-item--guide {
  align-items: flex-start;
}

.detail-icon {
  font-size: 1.25rem;
}

.detail-name {
  flex: 1;
  font-weight: 500;
  color: #1e293b;
}

.detail-category, .detail-type {
  font-size: 0.75rem;
  color: #94a3b8;
  padding: 0.125rem 0.5rem;
  background: #f1f5f9;
  border-radius: 0.25rem;
}

.guide-number {
  width: 1.5rem;
  height: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #3b82f6;
  color: white;
  border-radius: 50%;
  font-size: 0.75rem;
  font-weight: 700;
  flex-shrink: 0;
}

.guide-info {
  flex: 1;
}

.guide-meta {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.25rem;
}

.difficulty-tag {
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-size: 0.7rem;
  font-weight: 600;
}

.difficulty--easy { background: #dcfce7; color: #166534; }
.difficulty--medium { background: #fef9c3; color: #a16207; }
.difficulty--hard { background: #ffedd5; color: #c2410c; }
.difficulty--expert { background: #f3e8ff; color: #7c3aed; }

.time-tag {
  font-size: 0.75rem;
  color: #64748b;
}

.recommendations-list {
  margin: 0;
  padding-left: 1.25rem;
  font-size: 0.875rem;
  color: #475569;
}

.recommendations-list li {
  margin-bottom: 0.375rem;
}

/* ── Estimates ────────────────────────────────────── */
.estimates {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #f1f5f9;
}

.estimate {
  text-align: center;
}

.estimate-label {
  display: block;
  font-size: 0.75rem;
  color: #94a3b8;
  margin-bottom: 0.25rem;
}

.estimate-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

.estimate-value--free {
  color: #22c55e;
}

/* ── Actions ───────────────────────────────────────── */
.actions-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.2s;
  cursor: pointer;
  border: none;
}

.btn-icon {
  font-size: 1.1rem;
}

.btn--primary {
  background: linear-gradient(135deg, #2563eb, #4f46e5);
  color: white;
  box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
}

.btn--primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.btn--secondary {
  background: white;
  color: #475569;
  border: 2px solid #e2e8f0;
}

.btn--secondary:hover {
  border-color: #3b82f6;
  color: #3b82f6;
}

.btn--restart {
  width: 100%;
  background: #f1f5f9;
  color: #475569;
}

.btn--restart:hover {
  background: #e2e8f0;
}

/* ── Animations ───────────────────────────────────── */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 640px) {
  .summary-stats {
    grid-template-columns: repeat(2, 1fr);
  }

  .actions-grid {
    grid-template-columns: 1fr;
  }

  .estimates {
    grid-template-columns: 1fr;
  }
}
</style>