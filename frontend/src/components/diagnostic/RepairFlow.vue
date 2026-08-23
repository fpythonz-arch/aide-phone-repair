<template>
  <Teleport to="body">
    <div class="repair-flow-overlay" @click.self="close">
      <div class="repair-flow-modal">
        <!-- Header -->
        <div class="repair-flow-header">
          <div class="header-info">
            <h3 class="header-title">🔧 Guide de réparation</h3>
            <p v-if="guide" class="header-subtitle">{{ guide.title }}</p>
          </div>
          <button class="close-btn" @click="close">✕</button>
        </div>

        <!-- Progress -->
        <div class="repair-progress">
          <div class="progress-bar">
            <div 
              class="progress-fill"
              :style="{ width: progressPercentage + '%' }"
            ></div>
          </div>
          <span class="progress-text">
            Étape {{ currentStepIndex + 1 }} / {{ steps.length }}
          </span>
        </div>

        <!-- Content -->
        <div class="repair-flow-content">
          <!-- Loading -->
          <div v-if="!guide" class="loading-state">
            <div class="spinner"></div>
            <p>Chargement du guide...</p>
          </div>

          <!-- Steps -->
          <div v-else class="step-display">
            <!-- Step Image -->
            <div v-if="currentStep.image_url" class="step-image-container">
              <img 
                :src="currentStep.image_url" 
                :alt="currentStep.title"
                class="step-image"
              />
            </div>

            <!-- Step Content -->
            <div class="step-detail">
              <div class="step-header">
                <span class="step-number">{{ currentStepIndex + 1 }}</span>
                <h4 class="step-title">{{ currentStep.title }}</h4>
              </div>
              
              <p class="step-description">{{ currentStep.description }}</p>

              <!-- Warning -->
              <div v-if="currentStep.warning" class="step-warning">
                <span class="warning-icon">⚠️</span>
                <div class="warning-content">
                  <strong>Attention</strong>
                  <p>{{ currentStep.warning }}</p>
                </div>
              </div>

              <!-- Estimated Time -->
              <div v-if="currentStep.estimated_time" class="step-time">
                <span class="time-icon">⏱️</span>
                <span>{{ currentStep.estimated_time }} minutes estimées</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Tools needed (first step) -->
        <div v-if="currentStepIndex === 0 && guide?.tools_needed.length" class="tools-section">
          <h4 class="tools-title">🛠️ Outils nécessaires</h4>
          <div class="tools-list">
            <span v-for="tool in guide.tools_needed" :key="tool" class="tool-tag">
              {{ tool }}
            </span>
          </div>
        </div>

        <!-- Navigation -->
        <div class="repair-flow-footer">
          <button 
            class="btn btn--secondary"
            :disabled="currentStepIndex === 0"
            @click="prevStep"
          >
            ← Précédent
          </button>

          <div class="step-dots">
            <button
              v-for="(_, idx) in steps"
              :key="idx"
              class="dot"
              :class="{ 
                'dot--active': idx === currentStepIndex,
                'dot--completed': idx < currentStepIndex 
              }"
              @click="goToStep(idx)"
            ></button>
          </div>

          <button 
            v-if="currentStepIndex < steps.length - 1"
            class="btn btn--primary"
            @click="nextStep"
          >
            Suivant →
          </button>
          <button 
            v-else
            class="btn btn--success"
            @click="complete"
          >
            ✅ Terminer
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import type { RepairGuide, Component } from '@/types'

// ── Props ───────────────────────────────────────────
const props = defineProps<{
  guide: RepairGuide | null
  component?: Component | null
}>()

// ── Emits ───────────────────────────────────────────
const emit = defineEmits<{
  (e: 'close'): void
  (e: 'complete'): void
}>()

// ── State ───────────────────────────────────────────
const currentStepIndex = ref(0)

// ── Computed ────────────────────────────────────────
const steps = computed(() => {
  if (!props.guide) return []
  return props.guide.steps
})

const currentStep = computed(() => {
  return steps.value[currentStepIndex.value] || { title: '', description: '' }
})

const progressPercentage = computed(() => {
  if (steps.value.length === 0) return 0
  return ((currentStepIndex.value + 1) / steps.value.length) * 100
})

const isFirstStep = computed(() => currentStepIndex.value === 0)
const isLastStep = computed(() => currentStepIndex.value === steps.value.length - 1)

// ── Watchers ────────────────────────────────────────
watch(() => props.guide, () => {
  currentStepIndex.value = 0
})

// ── Méthodes ────────────────────────────────────────
const nextStep = () => {
  if (!isLastStep.value) {
    currentStepIndex.value++
  }
}

const prevStep = () => {
  if (!isFirstStep.value) {
    currentStepIndex.value--
  }
}

const goToStep = (index: number) => {
  if (index >= 0 && index < steps.value.length) {
    currentStepIndex.value = index
  }
}

const close = () => {
  emit('close')
}

const complete = () => {
  emit('complete')
}
</script>

<style scoped>
/* ── Overlay ───────────────────────────────────────── */
.repair-flow-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
  animation: fadeIn 0.2s ease-out;
}

/* ── Modal ─────────────────────────────────────────── */
.repair-flow-modal {
  background: white;
  border-radius: 1.5rem;
  width: 100%;
  max-width: 700px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  animation: slideUp 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

/* ── Header ────────────────────────────────────────── */
.repair-flow-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.header-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.header-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0.25rem 0 0 0;
}

.close-btn {
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  border: none;
  background: #f1f5f9;
  color: #64748b;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
}

.close-btn:hover {
  background: #e2e8f0;
  color: #1e293b;
}

/* ── Progress ──────────────────────────────────────── */
.repair-progress {
  padding: 1rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.progress-bar {
  flex: 1;
  height: 6px;
  background: #e2e8f0;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  border-radius: 3px;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
  white-space: nowrap;
}

/* ── Content ───────────────────────────────────────── */
.repair-flow-content {
  flex: 1;
  overflow-y: auto;
  padding: 0 1.5rem;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #64748b;
}

.spinner {
  width: 2rem;
  height: 2rem;
  border: 3px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.step-display {
  animation: fadeIn 0.3s ease-out;
}

.step-image-container {
  width: 100%;
  height: 200px;
  border-radius: 1rem;
  overflow: hidden;
  margin-bottom: 1.5rem;
  background: #f8fafc;
}

.step-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.step-detail {
  padding-bottom: 1rem;
}

.step-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.step-number {
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #3b82f6, #4f46e5);
  color: white;
  border-radius: 50%;
  font-weight: 700;
  font-size: 1rem;
  flex-shrink: 0;
}

.step-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.step-description {
  font-size: 1rem;
  color: #475569;
  line-height: 1.7;
  margin-bottom: 1.25rem;
}

/* ── Warning ───────────────────────────────────────── */
.step-warning {
  display: flex;
  gap: 0.75rem;
  padding: 1rem;
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  border-radius: 0.75rem;
  margin-bottom: 1rem;
}

.warning-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
}

.warning-content strong {
  display: block;
  color: #92400e;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.warning-content p {
  color: #78350f;
  font-size: 0.875rem;
  margin: 0;
}

/* ── Time ──────────────────────────────────────────── */
.step-time {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: #f0f9ff;
  border-radius: 0.5rem;
  color: #0369a1;
  font-size: 0.875rem;
}

.time-icon {
  font-size: 1rem;
}

/* ── Tools ─────────────────────────────────────────── */
.tools-section {
  padding: 1rem 1.5rem;
  border-top: 1px solid #f1f5f9;
}

.tools-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.625rem;
}

.tools-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tool-tag {
  padding: 0.375rem 0.75rem;
  background: #f1f5f9;
  color: #475569;
  border-radius: 0.375rem;
  font-size: 0.8rem;
  font-weight: 500;
}

/* ── Footer ────────────────────────────────────────── */
.repair-flow-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  border-top: 1px solid #f1f5f9;
  gap: 1rem;
}

.btn {
  padding: 0.625rem 1.25rem;
  border-radius: 0.625rem;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.2s;
  cursor: pointer;
  border: none;
  white-space: nowrap;
}

.btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.btn--secondary {
  background: #f1f5f9;
  color: #475569;
}

.btn--secondary:hover:not(:disabled) {
  background: #e2e8f0;
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

.btn--success {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  box-shadow: 0 4px 14px rgba(34, 197, 94, 0.3);
}

.btn--success:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
}

/* ── Step Dots ─────────────────────────────────────── */
.step-dots {
  display: flex;
  gap: 0.5rem;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  border: none;
  background: #e2e8f0;
  cursor: pointer;
  transition: all 0.2s;
  padding: 0;
}

.dot--active {
  background: #3b82f6;
  transform: scale(1.3);
}

.dot--completed {
  background: #22c55e;
}

/* ── Animations ───────────────────────────────────── */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 640px) {
  .repair-flow-modal {
    max-height: 95vh;
    border-radius: 1rem;
  }

  .repair-flow-header,
  .repair-progress,
  .repair-flow-content,
  .tools-section,
  .repair-flow-footer {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .step-title {
    font-size: 1.1rem;
  }

  .step-image-container {
    height: 150px;
  }
}
</style>