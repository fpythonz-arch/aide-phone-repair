<template>
  <div class="troubleshooting-step">
    <div class="step-header">
      <div class="step-badge">Étape {{ stepNumber }}</div>
      <h3 class="step-title">{{ step.title }}</h3>
    </div>

    <p class="step-description">{{ step.description }}</p>

    <div v-if="step.warning" class="warning-box">
      <span class="warning-icon">⚠️</span>
      <p>{{ step.warning }}</p>
    </div>

    <div class="instruction-card">
      <h4>📋 Instructions</h4>
      <p class="instruction-text">{{ step.instruction }}</p>
    </div>

    <div v-if="step.tools?.length" class="tools-needed">
      <h4>🔧 Outils nécessaires</h4>
      <div class="tools-list">
        <span v-for="tool in step.tools" :key="tool" class="tool-chip">
          {{ tool }}
        </span>
      </div>
    </div>

    <div class="checklist">
      <h4>✅ Vérifications</h4>
      <div
        v-for="item in step.checkItems"
        :key="item.id"
        :class="['check-item', { checked: item.checked }]"
        @click="toggleItem(item.id)"
      >
        <div class="check-box">
          <span v-if="item.checked">✓</span>
        </div>
        <span class="check-label">{{ item.label }}</span>
      </div>
    </div>

    <div class="step-footer">
      <span class="time-estimate">⏱️ ~{{ step.estimatedTime }} min</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { TroubleshootingStep as StepType } from '@/composables/useDepannage'

interface Props {
  step: StepType
  stepNumber: number
}

const props = defineProps<Props>()
const emit = defineEmits<{
  toggle: [itemId: number]
}>()

const toggleItem = (itemId: number) => {
  emit('toggle', itemId)
}
</script>

<style scoped>
.troubleshooting-step {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  border: 1px solid #e5e7eb;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from { opacity: 0; transform: translateX(20px); }
  to { opacity: 1; transform: translateX(0); }
}

.step-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.step-badge {
  padding: 0.4rem 1rem;
  background: #3b82f6;
  color: white;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  white-space: nowrap;
}

.step-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.step-description {
  color: #6b7280;
  font-size: 1rem;
  line-height: 1.6;
  margin-bottom: 1.25rem;
}

.warning-box {
  display: flex;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background: #fef3c7;
  border-left: 4px solid #f59e0b;
  border-radius: 8px;
  margin-bottom: 1.25rem;
}

.warning-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
}

.warning-box p {
  margin: 0;
  color: #92400e;
  font-size: 0.9rem;
  line-height: 1.5;
}

.instruction-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.25rem;
  margin-bottom: 1.25rem;
}

.instruction-card h4 {
  font-size: 0.875rem;
  font-weight: 700;
  color: #374151;
  margin: 0 0 0.75rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.instruction-text {
  color: #4b5563;
  line-height: 1.7;
  margin: 0;
  font-size: 0.95rem;
}

.tools-needed {
  margin-bottom: 1.25rem;
}

.tools-needed h4 {
  font-size: 0.875rem;
  font-weight: 700;
  color: #374151;
  margin: 0 0 0.75rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.tools-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tool-chip {
  padding: 0.4rem 0.875rem;
  background: #eff6ff;
  color: #1e40af;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
}

.checklist {
  margin-bottom: 1.25rem;
}

.checklist h4 {
  font-size: 0.875rem;
  font-weight: 700;
  color: #374151;
  margin: 0 0 0.75rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.check-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.875rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  margin-bottom: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  background: white;
}

.check-item:hover {
  border-color: #3b82f6;
}

.check-item.checked {
  border-color: #22c55e;
  background: #f0fdf4;
}

.check-box {
  width: 24px;
  height: 24px;
  border: 2px solid #d1d5db;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  font-weight: 700;
  color: #22c55e;
  transition: all 0.2s;
  flex-shrink: 0;
}

.check-item.checked .check-box {
  border-color: #22c55e;
  background: #22c55e;
  color: white;
}

.check-label {
  font-size: 0.95rem;
  color: #374151;
  font-weight: 500;
}

.check-item.checked .check-label {
  color: #166534;
  text-decoration: line-through;
  opacity: 0.7;
}

.step-footer {
  display: flex;
  justify-content: flex-end;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.time-estimate {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}
</style>