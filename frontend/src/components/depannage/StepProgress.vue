<template>
  <div class="step-progress">
    <div class="progress-header">
      <span class="progress-label">Progression</span>
      <span class="progress-value">{{ progress }}%</span>
    </div>
    <div class="progress-bar">
      <div class="progress-fill" :style="{ width: progress + '%' }"></div>
    </div>
    <div class="steps-track">
      <button
        v-for="(step, index) in steps"
        :key="step.id"
        :class="['step-dot', {
          completed: completedSteps.includes(step.id),
          current: currentIndex === index,
          upcoming: currentIndex < index,
        }]"
        @click="$emit('go-to', index)"
      >
        <span class="dot-number">{{ index + 1 }}</span>
        <span class="dot-label">{{ step.title }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { TroubleshootingStep } from '@/composables/useDepannage'

interface Props {
  steps: TroubleshootingStep[]
  currentIndex: number
  completedSteps: number[]
  progress: number
}

defineProps<Props>()
defineEmits<{
  'go-to': [index: number]
}>()
</script>

<style scoped>
.step-progress {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
  margin-bottom: 1.5rem;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.progress-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.progress-value {
  font-size: 0.875rem;
  font-weight: 700;
  color: #3b82f6;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 1.5rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  border-radius: 4px;
  transition: width 0.5s ease;
}

.steps-track {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.step-dot {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  border: none;
  background: none;
  cursor: pointer;
  min-width: 80px;
  opacity: 0.5;
  transition: all 0.2s;
}

.step-dot:hover {
  opacity: 0.8;
}

.step-dot.completed {
  opacity: 1;
}

.step-dot.current {
  opacity: 1;
}

.step-dot .dot-number {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #e5e7eb;
  color: #6b7280;
  font-weight: 700;
  font-size: 0.875rem;
  transition: all 0.3s;
  flex-shrink: 0;
}

.step-dot.completed .dot-number {
  background: #22c55e;
  color: white;
}

.step-dot.current .dot-number {
  background: #3b82f6;
  color: white;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}

.step-dot .dot-label {
  font-size: 0.7rem;
  color: #6b7280;
  text-align: center;
  white-space: nowrap;
  max-width: 80px;
  overflow: hidden;
  text-overflow: ellipsis;
}

.step-dot.current .dot-label {
  color: #3b82f6;
  font-weight: 600;
}

.steps-track::-webkit-scrollbar {
  height: 4px;
}

.steps-track::-webkit-scrollbar-track {
  background: transparent;
}

.steps-track::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 2px;
}
</style>