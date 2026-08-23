<template>
  <div class="depannage-detail-view">
    <!-- Vue liste des catégories (quand aucun type n'est sélectionné OU c'est hardware/software) -->
    <div v-if="!routeType || isCategoryFilter" class="categories-view">
          <header class="page-header">
        <h1>🔧 Dépannage</h1>
        <p>Sélectionnez une catégorie de problème pour commencer le diagnostic guidé</p>
      </header>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Chargement des catégories...</p>
      </div>

      <div v-else class="categories-grid">
        <DepannageCategoryCard
          v-for="category in filteredCategories"
          :key="category.id"
          :category="category"
          @select="goToCategory"
        />
      </div>
    </div>

    <!-- Vue guide de dépannage (quand un type est sélectionné) -->
<div v-else-if="routeType" class="guide-view">
        <header class="guide-header">
        <button class="back-btn" @click="goBack">
          ← Retour aux catégories
        </button>
        <div class="guide-title">
          <span class="guide-icon">{{ currentGuide?.category?.icon || '🔧' }}</span>
          <div>
            <h1>{{ currentGuide?.category?.name || 'Dépannage' }}</h1>
            <p>{{ currentGuide?.category?.description }}</p>
          </div>
        </div>
      </header>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Chargement du guide...</p>
      </div>

      <div v-else-if="error" class="error-state">
        <div class="error-icon">😕</div>
        <h3>{{ error }}</h3>
        <button class="retry-btn" @click="loadGuide">Réessayer</button>
      </div>

            <template v-else-if="currentGuide">
        <!-- Progression -->
        <StepProgress
          :steps="currentGuide.steps"
          :current-index="currentStepIndex"
          :completed-steps="completedSteps"
          :progress="progress"
          @go-to="goToStep"
        />

        <!-- Info barre : temps estimé -->
        <div class="time-estimate" v-if="!isGuideComplete">
          <span>⏱️ Temps estimé total : {{ formatTime(totalEstimatedTime) }}</span>
          <span>🔧 Outils nécessaires : {{ allTools.length }} outil(s)</span>
        </div>

        <!-- Contenu principal + Sidebar -->
        <div class="guide-content">
          <!-- Colonne gauche : étapes ou solutions -->
          <div class="main-column">
            <!-- Étape en cours -->
            <div v-if="currentStep && !isGuideComplete" class="step-section">
              <TroubleshootingStep
                :step="currentStep"
                :step-number="currentStepIndex + 1"
                @toggle="toggleCheckItem(currentStep.id, $event)"
              />

              <!-- Navigation entre étapes -->
              <div class="step-navigation">
                <button class="nav-btn prev" :disabled="currentStepIndex === 0" @click="prevStep">
                  ← Précédent
                </button>
                <button class="nav-btn next" :class="{ complete: isLastStep }" :disabled="!canProceed" @click="nextStep">
                  {{ isLastStep ? 'Voir les solutions →' : 'Suivant →' }}
                </button>
              </div>
            </div>

            <!-- Résultats finaux avec les DEUX BOUTONS -->
            <div v-else class="solutions-section">
              <SolutionResult :solutions="currentGuide.solutions" @restart="resetGuide" />
              
              <div class="action-buttons">
                <button class="action-btn repair-btn" @click="startRepair">
                  🔧 Commencer la réparation
                  <span class="btn-sub">Guide étape par étape avec outils</span>
                </button>
                <button class="action-btn guide-btn" @click="viewFullGuide">
                  📚 Guide complet de ressources
                  <span class="btn-sub">Vidéos, liens, forums, pièces</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Info sidebar -->
          <aside class="guide-sidebar">
            <div class="info-card symptoms-card">
              <h4>⚠️ Symptômes courants</h4>
              <ul>
                <li v-for="(symptom, idx) in currentGuide.symptoms" :key="idx">
                  {{ symptom }}
                </li>
              </ul>
            </div>

            <div class="info-card causes-card">
              <h4>🔍 Causes fréquentes</h4>
              <ul>
                <li v-for="(cause, idx) in currentGuide.commonCauses" :key="idx">
                  {{ cause }}
                </li>
              </ul>
            </div>

            <div class="info-card tools-card">
              <h4>🛠️ Outils nécessaires</h4>
              <ul v-if="allTools.length > 0">
                <li v-for="(tool, idx) in allTools" :key="idx">
                  {{ tool }}
                </li>
              </ul>
              <p v-else class="no-tools">Aucun outil spécial requis pour cette étape</p>
            </div>

            <div class="info-card help-card">
              <h4>💡 Besoin d'aide ?</h4>
              <p>Si vous ne trouvez pas de solution, notre équipe peut vous accompagner.</p>
              <button class="contact-btn" @click="contactSupport">
                Contacter le support
              </button>
            </div>
          </aside>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useDepannage } from '@/composables/useDepannage'
import DepannageCategoryCard from '@/components/depannage/DepannageCategoryCard.vue'
import StepProgress from '@/components/depannage/StepProgress.vue'
import TroubleshootingStep from '@/components/depannage/TroubleshootingStep.vue'
import SolutionResult from '@/components/depannage/SolutionResult.vue'

const route = useRoute()
const router = useRouter()

const {
  categories,
  currentGuide,
  loading,
  error,
  currentStepIndex,
  completedSteps,
  currentStep,
  progress,
  isLastStep,
  canProceed,
  isGuideComplete,
  allTools,
  totalEstimatedTime,
  fetchCategories,
  fetchGuideByType,
  goToStep,
  nextStep,
  prevStep,
  toggleCheckItem,
  resetGuide,
} = useDepannage()

const routeType = computed(() => route.params.type as string)

// Détermine si on est en mode "filtre par type" (hardware/software) ou "guide détaillé" (ecran, batterie...)
const isCategoryFilter = computed(() => {
  return routeType.value === 'hardware' || routeType.value === 'software'
})

// Filtrer les catégories selon le contexte (hardware/software)
const filteredCategories = computed(() => {
  // Si on est sur /depannage/hardware ou /depannage/software
  if (routeType.value === 'hardware' || routeType.value === 'software') {
    return categories.value.filter(c => c.type === routeType.value)
  }
  // Si on est sur /depannage?type=hardware ou /depannage?type=software
  if (route.query.type) {
    return categories.value.filter(c => c.type === route.query.type)
  }
  return categories.value
})

const goToCategory = (slug: string) => {
  router.push(`/depannage/${slug}`)
}

const goBack = () => {
  router.push('/depannage')
}

const loadGuide = () => {
  if (routeType.value) {
    fetchGuideByType(routeType.value)
  }
}

const contactSupport = () => {
  alert('Redirection vers le formulaire de contact...')
}

const startRepair = () => {
  router.push(`/depannage/${routeType.value}/repair`)
}

const viewFullGuide = () => {
  router.push(`/depannage/${routeType.value}/guide`)
}

const formatTime = (minutes: number): string => {
  if (minutes < 60) return `${minutes} min`
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  return m > 0 ? `${h}h ${m}min` : `${h}h`
}

// Watch route changes
watch(() => route.params.type, (newType) => {
  if (newType && newType !== 'hardware' && newType !== 'software') {
    // Mode guide détaillé : charger le guide
    loadGuide()
  } else {
    // Mode liste ou filtre : pas de guide à charger
    currentGuide.value = null
    resetGuide()
  }
})

onMounted(() => {
  fetchCategories()
  if (routeType.value && routeType.value !== 'hardware' && routeType.value !== 'software') {
    loadGuide()
  } else {
    currentGuide.value = null
    resetGuide()
  }
})

</script>

<style scoped>
.depannage-detail-view {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
  min-height: 100vh;
}

/* Header commun */
.page-header, .guide-header {
  margin-bottom: 2rem;
}

.page-header h1, .guide-title h1 {
  font-size: 2rem;
  font-weight: 800;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.page-header p, .guide-title p {
  color: #6b7280;
  font-size: 1.05rem;
  margin: 0;
}

/* Vue catégories */
.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.25rem;
}

/* Vue guide */
.guide-header {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.back-btn {
  align-self: flex-start;
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.2s;
}

.back-btn:hover {
  background: #e5e7eb;
}

.guide-title {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.guide-icon {
  font-size: 3rem;
  width: 72px;
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f9fafb;
  border-radius: 16px;
}

.time-estimate {
  display: flex;
  gap: 2rem;
  background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
  padding: 1rem 1.5rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  font-weight: 500;
  color: #0369a1;
}

.guide-content {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 1.5rem;
  align-items: start;
}

.step-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.step-navigation {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
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
  background: #f9fafb;
}

.nav-btn.next {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
  margin-left: auto;
}

.nav-btn.next:hover:not(:disabled) {
  background: #2563eb;
  border-color: #2563eb;
}

.nav-btn.next.complete {
  background: #22c55e;
  border-color: #22c55e;
}

.nav-btn.next.complete:hover:not(:disabled) {
  background: #16a34a;
  border-color: #16a34a;
}

.nav-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Solutions & Boutons d'action */
.solutions-section {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.action-buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-top: 1rem;
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 2rem 1.5rem;
  border-radius: 16px;
  border: none;
  cursor: pointer;
  font-size: 1.25rem;
  font-weight: 700;
  transition: all 0.3s ease;
  text-align: center;
}

.action-btn .btn-sub {
  font-size: 0.85rem;
  font-weight: 400;
  opacity: 0.9;
}

.repair-btn {
  background: linear-gradient(135deg, #ef4444, #f97316);
  color: white;
  box-shadow: 0 10px 30px -5px rgba(239, 68, 68, 0.3);
}

.repair-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 40px -5px rgba(239, 68, 68, 0.4);
}

.guide-btn {
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
  box-shadow: 0 10px 30px -5px rgba(59, 130, 246, 0.3);
}

.guide-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 40px -5px rgba(59, 130, 246, 0.4);
}

/* Sidebar */
.guide-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  position: sticky;
  top: 2rem;
}

.info-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.25rem;
}

.info-card h4 {
  font-size: 0.875rem;
  font-weight: 700;
  color: #374151;
  margin: 0 0 0.75rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.info-card ul {
  margin: 0;
  padding-left: 1.25rem;
  color: #4b5563;
  font-size: 0.9rem;
  line-height: 1.7;
}

.info-card p {
  color: #6b7280;
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0 0 1rem 0;
}

.no-tools {
  color: #9ca3af;
  font-style: italic;
  padding-left: 0 !important;
}

.contact-btn {
  width: 100%;
  padding: 0.75rem;
  background: #1f2937;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.contact-btn:hover {
  background: #111827;
}

/* Loading & Error */
.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  text-align: center;
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

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.error-state h3 {
  color: #1f2937;
  margin-bottom: 1rem;
}

.retry-btn {
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.retry-btn:hover {
  background: #2563eb;
}

@media (max-width: 1024px) {
  .guide-content {
    grid-template-columns: 1fr;
  }

  .guide-sidebar {
    position: static;
  }

  .action-buttons {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .depannage-detail-view {
    padding: 1rem;
  }

  .guide-title {
    flex-direction: column;
    text-align: center;
  }

  .step-navigation {
    flex-direction: column;
  }

  .nav-btn.next {
    margin-left: 0;
  }

  .time-estimate {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>