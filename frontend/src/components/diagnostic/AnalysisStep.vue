<template>
  <div class="analysis-step">
    <h2 class="step-title">
      ⚙️ Analyse en cours
    </h2>
    <p class="step-subtitle">
      Notre système analyse les symptômes sélectionnés pour déterminer les causes probables
    </p>

    <!-- Analyse en cours -->
    <div v-if="isLoading" class="analysis-loading">
      <div class="analysis-animation">
        <div class="pulse-ring"></div>
        <div class="pulse-ring pulse-ring--delay"></div>
        <div class="analysis-icon">🔍</div>
      </div>
      
      <h3 class="loading-title">Analyse en cours...</h3>
      <p class="loading-subtitle">Identification des composants affectés</p>
      
      <!-- Barre de progression -->
      <div class="progress-container">
        <div class="progress-bar">
          <div 
            class="progress-fill"
            :style="{ width: progress + '%' }"
          ></div>
        </div>
        <span class="progress-text">{{ Math.round(progress) }}%</span>
      </div>

      <!-- Symptômes en cours d'analyse -->
      <div class="analyzing-symptoms">
        <div 
          v-for="(symptom, index) in selectedSymptoms" 
          :key="symptom.id"
          class="analyzing-item"
          :class="{ 'analyzing-item--done': progress > (index + 1) * (100 / selectedSymptoms.length) }"
        >
          <span class="item-icon">
            {{ progress > (index + 1) * (100 / selectedSymptoms.length) ? '✓' : '⏳' }}
          </span>
          <span class="item-name">{{ symptom.name }}</span>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-state">
      <div class="error-icon">❌</div>
      <h3 class="error-title">Analyse échouée</h3>
      <p class="error-message">{{ error }}</p>
      <button @click="$emit('prev')" class="retry-btn">
        ← Retour et réessayer
      </button>
    </div>

    <!-- Résultat de l'analyse (affiché brièvement avant transition auto) -->
    <div v-else-if="result" class="analysis-result">
      <div class="result-header">
        <div class="result-icon">✅</div>
        <h3 class="result-title">Analyse terminée</h3>
        <p class="result-subtitle">Nous avons identifié les problèmes et les solutions</p>
      </div>

      <div class="result-summary" :class="`result-summary--${result.severity}`">
        <div class="summary-header">
          <span class="summary-label">Sévérité globale</span>
          <SeverityBadge :severity="result.severity" />
        </div>
        <div class="summary-stats">
          <div class="stat">
            <span class="stat-value">{{ result.symptoms.length }}</span>
            <span class="stat-label">Symptômes</span>
          </div>
          <div class="stat">
            <span class="stat-value">{{ result.components.length }}</span>
            <span class="stat-label">Composants</span>
          </div>
          <div class="stat">
            <span class="stat-value">{{ result.repair_guides.length }}</span>
            <span class="stat-label">Guides</span>
          </div>
          <div class="stat">
            <span class="stat-value">{{ Math.round(result.confidence * 100) }}%</span>
            <span class="stat-label">Confiance</span>
          </div>
        </div>
      </div>

      <div class="result-actions">
        <button @click="$emit('next')" class="btn btn--primary">
          Voir les solutions →
        </button>
      </div>
    </div>

    <!-- Aucun résultat -->
    <div v-else class="empty-state">
      <div class="empty-icon">🤔</div>
      <p>Aucune donnée d'analyse disponible</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { DiagnosticResult, Symptom } from '@/types'
import SeverityBadge from './SeverityBadge.vue'

// ── Props ───────────────────────────────────────────
const props = defineProps<{
  isLoading: boolean
  progress: number
  selectedSymptoms: Symptom[]
  result: DiagnosticResult | null
  error?: string | null
}>()

// ── Emits ───────────────────────────────────────────
defineEmits<{
  (e: 'next'): void
  (e: 'prev'): void
}>()
</script>

<style scoped>
/* ── Layout ────────────────────────────────────────── */
.analysis-step {
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

/* ── Loading State ─────────────────────────────────── */
.analysis-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 2rem 0;
}

.analysis-animation {
  position: relative;
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.pulse-ring {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 3px solid #3b82f6;
  animation: pulse-ring 2s ease-out infinite;
}

.pulse-ring--delay {
  animation-delay: 1s;
}

@keyframes pulse-ring {
  0% {
    transform: scale(0.8);
    opacity: 1;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

.analysis-icon {
  font-size: 2.5rem;
  z-index: 1;
  animation: bounce 1s ease-in-out infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.loading-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.loading-subtitle {
  color: #64748b;
  margin-bottom: 2rem;
}

/* ── Progress Bar ──────────────────────────────────── */
.progress-container {
  width: 100%;
  max-width: 400px;
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  border-radius: 4px;
  transition: width 0.3s ease;
  position: relative;
}

.progress-fill::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.3),
    transparent
  );
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.progress-text {
  font-weight: 700;
  color: #3b82f6;
  font-size: 0.9rem;
  min-width: 3rem;
}

/* ── Analyzing Symptoms ───────────────────────────── */
.analyzing-symptoms {
  width: 100%;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.analyzing-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 1rem;
  background: #f8fafc;
  border-radius: 0.5rem;
  border: 1px solid #e2e8f0;
  transition: all 0.3s;
}

.analyzing-item--done {
  background: #f0fdf4;
  border-color: #86efac;
}

.item-icon {
  width: 1.5rem;
  height: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #fef3c7;
  font-size: 0.75rem;
}

.analyzing-item--done .item-icon {
  background: #dcfce7;
  color: #15803d;
}

.item-name {
  font-size: 0.875rem;
  color: #475569;
}

.analyzing-item--done .item-name {
  color: #15803d;
  text-decoration: line-through;
}

/* ── Error State ──────────────────────────────────── */
.error-state {
  text-align: center;
  padding: 2rem;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.error-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #dc2626;
  margin-bottom: 0.5rem;
}

.error-message {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.retry-btn {
  padding: 0.625rem 1.5rem;
  background: #fee2e2;
  color: #dc2626;
  border-radius: 0.5rem;
  font-weight: 600;
  transition: all 0.2s;
}

.retry-btn:hover {
  background: #fecaca;
}

/* ── Result State ──────────────────────────────────── */
.analysis-result {
  animation: fadeIn 0.4s ease-out;
}

.result-header {
  text-align: center;
  margin-bottom: 2rem;
}

.result-icon {
  font-size: 3rem;
  margin-bottom: 0.5rem;
}

.result-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
}

.result-subtitle {
  color: #64748b;
}

.result-summary {
  border-radius: 1rem;
  padding: 1.5rem;
  margin-bottom: 2rem;
  border: 2px solid;
}

.result-summary--low {
  background: linear-gradient(135deg, #f0fdf4, #dcfce7);
  border-color: #86efac;
}

.result-summary--medium {
  background: linear-gradient(135deg, #fefce8, #fef9c3);
  border-color: #fde047;
}

.result-summary--high {
  background: linear-gradient(135deg, #fff7ed, #ffedd5);
  border-color: #fdba74;
}

.result-summary--critical {
  background: linear-gradient(135deg, #fef2f2, #fee2e2);
  border-color: #fca5a5;
}

.summary-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
}

.summary-label {
  font-weight: 600;
  color: #374151;
}

.summary-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.stat {
  text-align: center;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.6);
  border-radius: 0.5rem;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* ── Actions ───────────────────────────────────────── */
.result-actions {
  display: flex;
  justify-content: center;
}

.btn {
  padding: 0.875rem 2rem;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.2s;
  cursor: pointer;
  border: none;
}

.btn--primary {
  background: linear-gradient(135deg, #2563eb, #4f46e5);
  color: white;
  box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
}

.btn--primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

/* ── Empty State ───────────────────────────────────── */
.empty-state {
  text-align: center;
  padding: 3rem;
  color: #9ca3af;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

/* ── Animations ────────────────────────────────────── */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 640px) {
  .summary-stats {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>