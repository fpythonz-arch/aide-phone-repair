<template>
  <div class="step-indicator-wrapper">
    <!-- Barre de progression en arrière-plan -->
    <div class="progress-bar-bg">
      <div
        class="progress-bar-fill"
        :style="{ width: progress + '%' }"
      ></div>
    </div>

    <!-- Étapes -->
    <div class="step-indicator">
      <div
        v-for="(step, index) in steps"
        :key="step.key || index"
        :class="['step', {
          'step--completed': index < currentStep,
          'step--active': index === currentStep,
          'step--upcoming': index > currentStep,
        }]"
      >
        <div class="step-circle">
          <Transition name="scale" mode="out-in">
            <span v-if="index < currentStep" key="check">✓</span>
            <span v-else-if="index === currentStep" key="icon">{{ step.icon }}</span>
            <span v-else key="number">{{ index + 1 }}</span>
          </Transition>
        </div>
        <span class="step-label">{{ step.label }}</span>
        <span v-if="step.description && index === currentStep" class="step-description">
          {{ step.description }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Step {
  key: string
  label: string
  icon: string
  description?: string
}

const props = defineProps<{
  currentStep: number
  steps: Step[]
  progress?: number
}>()

// Progression par défaut basée sur l'étape courante
const progress = computed(() => {
  if (props.progress !== undefined) return props.progress
  return ((props.currentStep + 1) / props.steps.length) * 100
})

import { computed } from 'vue'
</script>

<style scoped>
.step-indicator-wrapper {
  margin-bottom: 2rem;
  padding: 1.5rem 0;
}

/* Barre de progression */
.progress-bar-bg {
  width: 100%;
  height: 4px;
  background: #e2e8f0;
  border-radius: 2px;
  margin-bottom: 1rem;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  border-radius: 2px;
  transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Étapes */
.step-indicator {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  position: relative;
  max-width: 800px;
  margin: 0 auto;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  flex: 1;
  transition: all 0.3s ease;
}

.step-circle {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 1;
  border: 2px solid transparent;
}

/* État : Complété */
.step--completed .step-circle {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  border-color: #22c55e;
}

.step--completed .step-label {
  color: #16a34a;
}

/* État : Actif */
.step--active .step-circle {
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: white;
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2), 0 4px 12px rgba(59, 130, 246, 0.3);
  transform: scale(1.1);
}

.step--active .step-label {
  color: #3b82f6;
  font-weight: 700;
}

/* État : À venir */
.step--upcoming .step-circle {
  background: #f1f5f9;
  color: #94a3b8;
  border-color: #e2e8f0;
}

.step--upcoming .step-label {
  color: #94a3b8;
}

/* Labels */
.step-label {
  margin-top: 0.75rem;
  font-size: 0.85rem;
  font-weight: 600;
  text-align: center;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.step-description {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: #64748b;
  text-align: center;
  max-width: 120px;
}

/* Ligne connectrice entre les étapes */
.step::after {
  content: '';
  position: absolute;
  top: 22px;
  left: 50%;
  width: 100%;
  height: 2px;
  background: #e2e8f0;
  z-index: 0;
  transition: background 0.3s ease;
}

.step:last-child::after {
  display: none;
}

.step--completed::after {
  background: linear-gradient(90deg, #22c55e, #22c55e);
}

/* Animation de transition */
.scale-enter-active,
.scale-leave-active {
  transition: all 0.2s ease;
}

.scale-enter-from,
.scale-leave-to {
  transform: scale(0);
  opacity: 0;
}

/* Responsive */
@media (max-width: 640px) {
  .step-indicator {
    padding: 0 0.5rem;
  }

  .step-circle {
    width: 36px;
    height: 36px;
    font-size: 0.85rem;
  }

  .step-label {
    font-size: 0.7rem;
  }

  .step-description {
    display: none;
  }

  .step::after {
    top: 18px;
  }
}
</style>