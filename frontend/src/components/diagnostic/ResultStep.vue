<template>
  <div class="result-step">
    <h2 class="step-title">
      🎯 Solutions recommandées
    </h2>
    <p class="step-subtitle">
      Basé sur l'analyse de {{ result?.symptoms.length || 0 }} symptôme(s), voici les solutions adaptées
    </p>

    <!-- Résumé de sévérité -->
    <div v-if="result" class="severity-summary" :class="`severity-summary--${result.severity}`">
      <div class="severity-info">
        <SeverityBadge :severity="result.severity" />
        <span class="confidence">Confiance : {{ Math.round(result.confidence * 100) }}%</span>
      </div>
      <p class="recommendation-text">{{ mainRecommendation }}</p>
    </div>

    <!-- Composants affectés -->
    <div v-if="result?.components.length" class="components-section">
      <h3 class="section-title">🔧 Composants concernés</h3>
      <div class="components-grid">
        <div 
          v-for="component in result.components" 
          :key="component.id"
          class="component-card"
        >
          <div class="component-icon">{{ getComponentIcon(component.category) }}</div>
          <div class="component-info">
            <span class="component-name">{{ component.name }}</span>
            <span class="component-type">{{ component.type }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Solutions : Réparation étape par étape -->
    <div class="solutions-section">
      <h3 class="section-title">🛠️ Réparation étape par étape</h3>
      
      <div v-if="result?.repair_guides.length" class="guides-list">
        <div
          v-for="(guide, index) in result.repair_guides"
          :key="guide.id"
          class="guide-card"
          :class="{ 'guide-card--recommended': index === 0 }"
        >
          <div v-if="index === 0" class="recommended-badge">⭐ Recommandé</div>
          
          <div class="guide-header">
            <h4 class="guide-title">{{ guide.title }}</h4>
            <span :class="['difficulty-badge', `difficulty--${guide.difficulty}`]">
              {{ difficultyLabel(guide.difficulty) }}
            </span>
          </div>

          <div class="guide-meta">
            <div class="meta-item">
              <span class="meta-icon">⏱️</span>
              <span>{{ guide.estimated_time }} min</span>
            </div>
            <div class="meta-item">
              <span class="meta-icon">🛠️</span>
              <span>{{ guide.tools_needed.length }} outil(s)</span>
            </div>
            <div class="meta-item">
              <span class="meta-icon">📋</span>
              <span>{{ guide.steps.length }} étape(s)</span>
            </div>
          </div>

          <div v-if="guide.warnings.length" class="warnings-box">
            <div class="warnings-title">⚠️ Attention</div>
            <ul class="warnings-list">
              <li v-for="(warning, idx) in guide.warnings" :key="idx">
                {{ warning }}
              </li>
            </ul>
          </div>

          <div class="guide-actions">
            <button class="btn btn--outline" @click="viewGuideDetail(guide)">
              📖 Voir le guide complet
            </button>
            <button class="btn btn--primary" @click="startRepair(guide)">
              🔧 Commencer la réparation
            </button>
          </div>
        </div>
      </div>

      <div v-else class="empty-guides">
        <p>Aucun guide de réparation disponible pour ces symptômes.</p>
      </div>
    </div>

    <!-- Ressources liées -->
    <div v-if="analysisResults.length" class="resources-section">
      <h3 class="section-title">📚 Ressources & Apprentissage</h3>
      <p class="resources-subtitle">
        Guides, vidéos et articles pour comprendre et résoudre chaque problème
      </p>

      <div class="resources-list">
        <div
          v-for="analysis in analysisResults"
          :key="analysis.symptomId"
          class="resource-group"
        >
          <h4 class="resource-symptom">
            {{ getSymptomName(analysis.symptomId) }}
          </h4>
          <p class="resource-cause">
            <strong>Cause probable :</strong> {{ analysis.probableCause }}
            <span class="confidence-badge">{{ analysis.confidence }}% confiance</span>
          </p>

          <div class="solutions-grid">
            <div
              v-for="solution in analysis.solutions"
              :key="solution.id"
              class="solution-item"
            >
              <div class="solution-header">
                <h5 class="solution-title">{{ solution.title }}</h5>
                <span :class="['difficulty-badge', `difficulty--${solution.difficulty}`]">
                  {{ difficultyLabel(solution.difficulty) }}
                </span>
              </div>
              <p class="solution-desc">{{ solution.description }}</p>
              <div class="solution-footer">
                <span class="solution-cost" v-if="solution.cost > 0">
                  💰 ~{{ solution.cost }}€
                </span>
                <span class="solution-cost" v-else>
                  💰 Gratuit
                </span>
                <span class="solution-duration">
                  ⏱️ {{ solution.duration }} min
                </span>
                <button 
                  class="btn btn--small btn--outline"
                  @click="viewResource(solution)"
                >
                  Voir →
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recommandations générales -->
    <div v-if="result?.recommendations.length" class="recommendations-section">
      <h3 class="section-title">💡 Conseils du réparateur</h3>
      <ul class="recommendations-list">
        <li v-for="(rec, idx) in result.recommendations" :key="idx" class="recommendation-item">
          <span class="rec-bullet">💡</span>
          <span>{{ rec }}</span>
        </li>
      </ul>
    </div>

    <!-- Actions -->
    <div class="step-actions">
      <button class="btn btn--secondary" @click="$emit('prev')">
        ← Retour à l'analyse
      </button>
      <button class="btn btn--success" @click="$emit('finish')">
        ✅ Terminer le diagnostic
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import type { DiagnosticResult, Analysis, RepairGuide, Component } from '@/types'
import SeverityBadge from './SeverityBadge.vue'

const router = useRouter()

// ── Props ───────────────────────────────────────────
const props = defineProps<{
  result: DiagnosticResult | null
  analysisResults: Analysis[]
  severityColor: string
}>()

// ── Emits ───────────────────────────────────────────
const emit = defineEmits<{
  (e: 'prev'): void
  (e: 'finish'): void
  (e: 'start-repair', guide: RepairGuide, component?: Component): void
  (e: 'view-guide', guideId: number): void
}>()

// ── Computed ────────────────────────────────────────
const mainRecommendation = computed(() => {
  if (!props.result) return ''
  const recs = props.result.recommendations
  return recs[0] || 'Consultez un professionnel pour un diagnostic approfondi.'
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

const getSymptomName = (symptomId: number): string => {
  const symptom = props.result?.symptoms.find(s => s.id === symptomId)
  return symptom?.name || `Symptôme #${symptomId}`
}

const viewGuideDetail = (guide: RepairGuide) => {
  emit('view-guide', guide.id)
}

const startRepair = (guide: RepairGuide) => {
  // Trouver le composant associé
  const component = props.result?.components.find(c => c.id === guide.component_id)
  emit('start-repair', guide, component)
}

const viewResource = (solution: any) => {
  if (solution.guideSlug) {
    router.push({
      path: '/ressources',
      query: { guide: solution.guideSlug, from: 'diagnostic' },
    })
  }
}
</script>

<style scoped>
/* ── Layout ────────────────────────────────────────── */
.result-step {
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
  margin-bottom: 1.5rem;
}

/* ── Severity Summary ──────────────────────────────── */
.severity-summary {
  padding: 1.25rem;
  border-radius: 1rem;
  margin-bottom: 1.5rem;
  border: 2px solid;
}

.severity-summary--low {
  background: linear-gradient(135deg, #f0fdf4, #dcfce7);
  border-color: #86efac;
}

.severity-summary--medium {
  background: linear-gradient(135deg, #fefce8, #fef9c3);
  border-color: #fde047;
}

.severity-summary--high {
  background: linear-gradient(135deg, #fff7ed, #ffedd5);
  border-color: #fdba74;
}

.severity-summary--critical {
  background: linear-gradient(135deg, #fef2f2, #fee2e2);
  border-color: #fca5a5;
}

.severity-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.confidence {
  font-size: 0.875rem;
  color: #475569;
  font-weight: 500;
}

.recommendation-text {
  color: #374151;
  font-size: 0.95rem;
  line-height: 1.5;
}

/* ── Section Title ─────────────────────────────────── */
.section-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* ── Components ────────────────────────────────────── */
.components-section {
  margin-bottom: 2rem;
}

.components-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

@media (min-width: 768px) {
  .components-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.component-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  transition: all 0.2s;
}

.component-card:hover {
  border-color: #3b82f6;
  background: #eff6ff;
}

.component-icon {
  font-size: 1.5rem;
}

.component-info {
  display: flex;
  flex-direction: column;
}

.component-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.875rem;
}

.component-type {
  font-size: 0.75rem;
  color: #94a3b8;
}

/* ── Guides ────────────────────────────────────────── */
.solutions-section {
  margin-bottom: 2rem;
}

.guides-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.guide-card {
  position: relative;
  padding: 1.5rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 1rem;
  transition: all 0.2s;
}

.guide-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.guide-card--recommended {
  border-color: #3b82f6;
  background: linear-gradient(135deg, #fafafa, #f0f9ff);
}

.recommended-badge {
  position: absolute;
  top: -10px;
  left: 1.5rem;
  padding: 0.25rem 0.875rem;
  background: linear-gradient(135deg, #3b82f6, #4f46e5);
  color: white;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.guide-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.guide-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.guide-meta {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.875rem;
  color: #64748b;
}

.meta-icon {
  font-size: 1rem;
}

/* ── Warnings ──────────────────────────────────────── */
.warnings-box {
  background: #fef3c7;
  border: 1px solid #fde68a;
  border-radius: 0.5rem;
  padding: 0.875rem;
  margin-bottom: 1rem;
}

.warnings-title {
  font-weight: 600;
  color: #92400e;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.warnings-list {
  margin: 0;
  padding-left: 1.25rem;
  font-size: 0.85rem;
  color: #78350f;
}

.warnings-list li {
  margin-bottom: 0.25rem;
}

/* ── Resources ───────────────────────────────────── */
.resources-section {
  margin-bottom: 2rem;
}

.resources-subtitle {
  color: #64748b;
  font-size: 0.9rem;
  margin-bottom: 1rem;
}

.resources-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.resource-group {
  padding: 1.25rem;
  background: #f8fafc;
  border-radius: 1rem;
  border: 1px solid #e2e8f0;
}

.resource-symptom {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.resource-cause {
  font-size: 0.875rem;
  color: #475569;
  margin-bottom: 1rem;
}

.confidence-badge {
  margin-left: 0.5rem;
  padding: 0.125rem 0.5rem;
  background: #dbeafe;
  color: #1d4ed8;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.solutions-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.75rem;
}

@media (min-width: 768px) {
  .solutions-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.solution-item {
  padding: 1rem;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  transition: all 0.2s;
}

.solution-item:hover {
  border-color: #3b82f6;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}

.solution-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.375rem 0;
}

.solution-desc {
  font-size: 0.85rem;
  color: #64748b;
  margin-bottom: 0.75rem;
  line-height: 1.4;
}

.solution-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.solution-cost, .solution-duration {
  font-size: 0.8rem;
  color: #94a3b8;
}

/* ── Recommendations ───────────────────────────────── */
.recommendations-section {
  margin-bottom: 2rem;
}

.recommendations-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.recommendation-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.875rem;
  background: #f0fdf4;
  border-radius: 0.5rem;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  color: #166534;
}

.rec-bullet {
  flex-shrink: 0;
}

/* ── Difficulty Badge ──────────────────────────────── */
.difficulty-badge {
  padding: 0.25rem 0.625rem;
  border-radius: 0.375rem;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
}

.difficulty--easy {
  background: #dcfce7;
  color: #166534;
}

.difficulty--medium {
  background: #fef9c3;
  color: #a16207;
}

.difficulty--hard {
  background: #ffedd5;
  color: #c2410c;
}

.difficulty--expert {
  background: #f3e8ff;
  color: #7c3aed;
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
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.375rem;
}

.btn--small {
  padding: 0.375rem 0.75rem;
  font-size: 0.8rem;
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

.btn--outline {
  background: white;
  color: #3b82f6;
  border: 2px solid #3b82f6;
}

.btn--outline:hover {
  background: #eff6ff;
}

.btn--secondary {
  background: #f1f5f9;
  color: #475569;
}

.btn--secondary:hover {
  background: #e2e8f0;
}

.btn--success {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  box-shadow: 0 4px 14px rgba(34, 197, 94, 0.3);
}

.btn--success:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
}

/* ── Step Actions ─────────────────────────────────── */
.step-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
  margin-top: 2rem;
}

/* ── Empty States ──────────────────────────────────── */
.empty-guides {
  text-align: center;
  padding: 2rem;
  color: #9ca3af;
  background: #f8fafc;
  border-radius: 0.75rem;
}

/* ── Animations ───────────────────────────────────── */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 640px) {
  .guide-actions {
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }

  .step-actions {
    flex-direction: column;
    gap: 0.75rem;
  }
}
</style>