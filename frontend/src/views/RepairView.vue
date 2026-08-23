<template>
  <div class="repair-view">
    <header class="repair-header">
      <button class="back-btn" @click="goBack">
        ← Retour au diagnostic
      </button>
      <div class="header-title">
        <span class="header-icon">{{ category?.icon || '🔧' }}</span>
        <div>
          <h1>🔧 Réparation : {{ category?.name || 'Guide' }}</h1>
          <p>Guide étape par étape pour réparer votre appareil</p>
        </div>
      </div>
    </header>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement du guide de réparation...</p>
    </div>

    <div v-else-if="guide" class="repair-content">
      <!-- Progression -->
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: progressPercent + '%' }"></div>
        <span class="progress-text">Étape {{ currentStepIndex + 1 }} / {{ guide.steps.length }}</span>
      </div>

      <!-- Étape actuelle -->
      <div class="step-card">
        <div class="step-header">
          <span class="step-number">{{ currentStepIndex + 1 }}</span>
          <div class="step-meta">
            <h2>{{ currentStep?.title }}</h2>
            <span class="step-time" v-if="currentStep?.estimatedTime">
              ⏱️ {{ currentStep.estimatedTime }} min
            </span>
          </div>
        </div>

        <p class="step-description">{{ currentStep?.description }}</p>

        <div class="step-instruction">
          <h3>📋 Instructions</h3>
          <p>{{ currentStep?.instruction }}</p>
        </div>

        <div class="step-warning" v-if="currentStep?.warning">
          <h3>⚠️ Attention</h3>
          <p>{{ currentStep.warning }}</p>
        </div>

        <!-- Checklist -->
        <div class="checklist" v-if="currentStep?.checkItems?.length">
          <h3>✅ Vérifications</h3>
          <label
            v-for="item in currentStep.checkItems"
            :key="item.id"
            class="check-item"
            :class="{ checked: item.checked }"
          >
            <input
              type="checkbox"
              :checked="item.checked"
              @change="toggleCheck(item.id)"
            />
            <span>{{ item.label }}</span>
          </label>
        </div>

        <!-- Outils pour cette étape -->
        <div class="step-tools" v-if="currentStep?.tools?.length">
          <h3>🛠️ Outils nécessaires</h3>
          <div class="tools-list">
            <span v-for="tool in currentStep.tools" :key="tool" class="tool-tag">
              {{ tool }}
            </span>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="repair-navigation">
        <button
          class="nav-btn prev"
          :disabled="currentStepIndex === 0"
          @click="prevStep"
        >
          ← Précédent
        </button>

        <button
          v-if="!isLastStep"
          class="nav-btn next"
          :disabled="!canProceed"
          @click="nextStep"
        >
          Suivant →
        </button>
        <button
          v-else
          class="nav-btn finish"
          @click="finishRepair"
        >
          ✅ Réparation terminée
        </button>
      </div>

      <!-- Résumé des étapes -->
      <div class="steps-overview">
        <h3>📑 Résumé des étapes</h3>
        <div class="steps-list">
          <div
            v-for="(step, idx) in guide.steps"
            :key="step.id"
            class="overview-step"
            :class="{
              completed: completedSteps.includes(step.id),
              current: idx === currentStepIndex
            }"
            @click="goToStep(idx)"
          >
            <span class="overview-number">{{ idx + 1 }}</span>
            <span class="overview-title">{{ step.title }}</span>
            <span v-if="completedSteps.includes(step.id)" class="overview-check">✓</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de fin -->
    <div v-if="showFinishModal" class="modal-overlay" @click.self="showFinishModal = false">
      <div class="modal-content">
        <div class="modal-icon">🎉</div>
        <h2>Réparation terminée !</h2>
        <p>Félicitations, vous avez suivi toutes les étapes de réparation.</p>
        
        <div class="modal-solutions">
          <h3>Solutions recommandées :</h3>
          <div
            v-for="solution in guide?.solutions"
            :key="solution.id"
            class="solution-card"
            :class="solution.difficulty"
          >
            <h4>{{ solution.title }}</h4>
            <p>{{ solution.description }}</p>
            <div class="solution-meta">
              <span class="difficulty-badge" :class="solution.difficulty">
                {{ difficultyLabel(solution.difficulty) }}
              </span>
              <span class="cost" v-if="solution.estimatedCost > 0">
                ~{{ solution.estimatedCost }}€
              </span>
              <span class="cost free" v-else>Gratuit</span>
            </div>
          </div>
        </div>

        <div class="modal-actions">
          <button class="modal-btn primary" @click="goToFullGuide">
            📚 Voir le guide complet
          </button>
          <button class="modal-btn secondary" @click="resetAndGoBack">
            🔧 Nouvelle réparation
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useDepannage } from '@/composables/useDepannage'

const route = useRoute()
const router = useRouter()
const routeType = computed(() => route.params.type as string)

const {
  currentGuide: guide,
  loading,
  currentStepIndex,
  completedSteps,
  currentStep,
  canProceed,
  isLastStep,
  fetchGuideByType,
  goToStep,
  nextStep,
  prevStep,
  toggleCheckItem,
  resetGuide,
} = useDepannage()

const showFinishModal = ref(false)

const category = computed(() => guide.value?.category)

const progressPercent = computed(() => {
  if (!guide.value) return 0
  return Math.round(((currentStepIndex.value + 1) / guide.value.steps.length) * 100)
})

const toggleCheck = (itemId: number) => {
  if (currentStep.value) {
    toggleCheckItem(currentStep.value.id, itemId)
  }
}

const finishRepair = () => {
  showFinishModal.value = true
}

const goToFullGuide = () => {
  showFinishModal.value = false
  router.push(`/depannage/${routeType.value}/guide`)
}

const resetAndGoBack = () => {
  showFinishModal.value = false
  resetGuide()
  router.push('/depannage')
}

const goBack = () => {
  router.push(`/depannage/${routeType.value}`)
}

const difficultyLabel = (diff: string): string => {
  const labels: Record<string, string> = {
    easy: 'Facile',
    medium: 'Moyen',
    hard: 'Difficile',
    expert: 'Expert',
  }
  return labels[diff] || diff
}

onMounted(() => {
  if (routeType.value) {
    fetchGuideByType(routeType.value)
  }
})
</script>

<style scoped>
.repair-view {
  padding: 2rem;
  max-width: 900px;
  margin: 0 auto;
  min-height: 100vh;
}

.repair-header {
  margin-bottom: 2rem;
}

.back-btn {
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  cursor: pointer;
  margin-bottom: 1rem;
  transition: background 0.2s;
}

.back-btn:hover {
  background: #e5e7eb;
}

.header-title {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-icon {
  font-size: 2.5rem;
}

.header-title h1 {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1f2937;
  margin: 0;
}

.header-title p {
  color: #6b7280;
  margin: 0.25rem 0 0 0;
}

/* Progress bar */
.progress-bar {
  background: #e5e7eb;
  border-radius: 999px;
  height: 8px;
  margin-bottom: 2rem;
  position: relative;
  overflow: hidden;
}

.progress-fill {
  background: linear-gradient(90deg, #3b82f6, #06b6d4);
  height: 100%;
  border-radius: 999px;
  transition: width 0.5s ease;
}

.progress-text {
  position: absolute;
  right: 0;
  top: -24px;
  font-size: 0.85rem;
  color: #6b7280;
  font-weight: 500;
}

/* Step card */
.step-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 1.5rem;
}

.step-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.step-number {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  font-weight: 700;
}

.step-meta h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #1f2937;
}

.step-time {
  font-size: 0.85rem;
  color: #6b7280;
}

.step-description {
  color: #4b5563;
  font-size: 1.05rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.step-instruction, .step-warning, .checklist, .step-tools {
  margin-bottom: 1.5rem;
}

.step-instruction h3, .step-warning h3, .checklist h3, .step-tools h3 {
  font-size: 0.95rem;
  font-weight: 700;
  color: #374151;
  margin-bottom: 0.75rem;
}

.step-instruction p {
  color: #4b5563;
  line-height: 1.7;
  background: #f9fafb;
  padding: 1rem;
  border-radius: 8px;
  margin: 0;
}

.step-warning {
  background: #fef3c7;
  border: 1px solid #f59e0b;
  border-radius: 8px;
  padding: 1rem;
}

.step-warning h3 {
  color: #92400e;
}

.step-warning p {
  color: #92400e;
  margin: 0;
}

/* Checklist */
.check-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 0.5rem;
}

.check-item:hover {
  background: #f3f4f6;
}

.check-item.checked {
  background: #d1fae5;
  color: #065f46;
}

.check-item input[type="checkbox"] {
  width: 20px;
  height: 20px;
  accent-color: #22c55e;
}

/* Tools */
.tools-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tool-tag {
  background: #eff6ff;
  color: #1d4ed8;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
}

/* Navigation */
.repair-navigation {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 2rem;
}

.nav-btn {
  padding: 0.875rem 1.5rem;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: 2px solid;
}

.nav-btn.prev {
  background: white;
  border-color: #e5e7eb;
  color: #374151;
}

.nav-btn.prev:hover:not(:disabled) {
  border-color: #d1d5db;
}

.nav-btn.next {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.nav-btn.next:hover:not(:disabled) {
  background: #2563eb;
}

.nav-btn.finish {
  background: #22c55e;
  border-color: #22c55e;
  color: white;
}

.nav-btn.finish:hover {
  background: #16a34a;
}

.nav-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Steps overview */
.steps-overview {
  background: #f9fafb;
  border-radius: 12px;
  padding: 1.5rem;
}

.steps-overview h3 {
  font-size: 1rem;
  font-weight: 700;
  color: #374151;
  margin-bottom: 1rem;
}

.steps-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.overview-step {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.overview-step:hover {
  background: #e5e7eb;
}

.overview-step.current {
  background: #dbeafe;
  border-left: 3px solid #3b82f6;
}

.overview-step.completed {
  background: #d1fae5;
}

.overview-number {
  width: 28px;
  height: 28px;
  background: #e5e7eb;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 600;
}

.overview-step.completed .overview-number {
  background: #22c55e;
  color: white;
}

.overview-step.current .overview-number {
  background: #3b82f6;
  color: white;
}

.overview-title {
  flex: 1;
  font-size: 0.9rem;
  color: #374151;
}

.overview-check {
  color: #22c55e;
  font-weight: 700;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 20px;
  padding: 2.5rem;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  text-align: center;
}

.modal-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.modal-content h2 {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.modal-content > p {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.modal-solutions {
  text-align: left;
  margin-bottom: 1.5rem;
}

.modal-solutions h3 {
  font-size: 1rem;
  margin-bottom: 1rem;
}

.solution-card {
  background: #f9fafb;
  border-radius: 12px;
  padding: 1.25rem;
  margin-bottom: 1rem;
  border-left: 4px solid #e5e7eb;
}

.solution-card.easy { border-left-color: #22c55e; }
.solution-card.medium { border-left-color: #f59e0b; }
.solution-card.hard { border-left-color: #ef4444; }
.solution-card.expert { border-left-color: #7c3aed; }

.solution-card h4 {
  margin: 0 0 0.5rem 0;
  font-size: 1rem;
}

.solution-card p {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 0.75rem;
}

.solution-meta {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.difficulty-badge {
  padding: 0.25rem 0.6rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.difficulty-badge.easy { background: #d1fae5; color: #065f46; }
.difficulty-badge.medium { background: #fef3c7; color: #92400e; }
.difficulty-badge.hard { background: #fee2e2; color: #991b1b; }
.difficulty-badge.expert { background: #ede9fe; color: #5b21b6; }

.cost {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1f2937;
}

.cost.free {
  color: #22c55e;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.modal-btn {
  padding: 0.875rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.modal-btn.primary {
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
}

.modal-btn.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px -4px rgba(59, 130, 246, 0.3);
}

.modal-btn.secondary {
  background: #f3f4f6;
  color: #374151;
}

.modal-btn.secondary:hover {
  background: #e5e7eb;
}

/* Loading */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 640px) {
  .repair-view {
    padding: 1rem;
  }

  .header-title {
    flex-direction: column;
    text-align: center;
  }

  .repair-navigation {
    flex-direction: column;
  }

  .modal-actions {
    flex-direction: column;
  }
}
</style>