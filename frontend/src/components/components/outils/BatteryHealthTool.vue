<template>
  <div class="battery-tool">
    <h3>🔋 Diagnostic Batterie</h3>
    
    <div class="battery-visual">
      <div class="battery-body">
        <div class="battery-level" :style="{ width: health + '%', background: healthColor }"></div>
        <span class="battery-percentage">{{ health }}%</span>
      </div>
      <div class="battery-cap"></div>
    </div>

    <div class="input-section">
      <label>Capacité actuelle (mAh)</label>
      <input 
        type="number" 
        v-model.number="currentCapacity"
        placeholder="Ex: 2800"
      />
      <label>Capacité nominale (mAh)</label>
      <input 
        type="number" 
        v-model.number="nominalCapacity"
        placeholder="Ex: 3200"
      />
    </div>

    <button class="analyze-btn" @click="analyze" :disabled="!canAnalyze">
      Analyser la santé
    </button>

    <div v-if="analyzed" class="health-result">
      <div class="health-status" :class="healthStatus">
        <span class="status-icon">{{ statusIcon }}</span>
        <span class="status-text">{{ statusText }}</span>
      </div>
      <p class="recommendation">{{ recommendation }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const currentCapacity = ref<number | null>(null)
const nominalCapacity = ref<number | null>(null)
const analyzed = ref(false)

const health = computed(() => {
  if (!currentCapacity.value || !nominalCapacity.value) return 0
  return Math.round((currentCapacity.value / nominalCapacity.value) * 100)
})

const healthColor = computed(() => {
  if (health.value >= 80) return '#22c55e'
  if (health.value >= 60) return '#eab308'
  return '#ef4444'
})

const healthStatus = computed(() => {
  if (health.value >= 80) return 'good'
  if (health.value >= 60) return 'warning'
  return 'critical'
})

const statusIcon = computed(() => {
  if (health.value >= 80) return '✅'
  if (health.value >= 60) return '⚠️'
  return '🔴'
})

const statusText = computed(() => {
  if (health.value >= 80) return 'Bonne santé'
  if (health.value >= 60) return 'Usure modérée'
  return 'Remplacement recommandé'
})

const recommendation = computed(() => {
  if (health.value >= 80) return 'Votre batterie est en bon état. Aucune action nécessaire.'
  if (health.value >= 60) return 'Usure visible. Prévoyez un remplacement dans les 3-6 mois.'
  return 'Batterie fortement dégradée. Remplacement urgent recommandé pour éviter les dommages.'
})

const canAnalyze = computed(() => {
  return currentCapacity.value && nominalCapacity.value && nominalCapacity.value > 0
})

const analyze = () => {
  analyzed.value = true
}
</script>

<style scoped>
.battery-tool {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  border: 1px solid #e5e7eb;
}

.battery-tool h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.battery-visual {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  margin-bottom: 2rem;
}

.battery-body {
  width: 200px;
  height: 80px;
  border: 4px solid #374151;
  border-radius: 12px;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.battery-level {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  transition: all 0.5s ease;
}

.battery-percentage {
  position: relative;
  z-index: 1;
  font-size: 1.5rem;
  font-weight: 800;
  color: #1f2937;
}

.battery-cap {
  width: 12px;
  height: 30px;
  background: #374151;
  border-radius: 0 4px 4px 0;
}

.input-section {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.input-section label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.input-section input {
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 1rem;
}

.analyze-btn {
  width: 100%;
  padding: 0.875rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.analyze-btn:hover:not(:disabled) {
  background: #2563eb;
}

.analyze-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.health-result {
  margin-top: 1.5rem;
  padding: 1.25rem;
  border-radius: 12px;
  text-align: center;
}

.health-status {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  padding: 0.75rem;
  border-radius: 8px;
}

.health-status.good {
  background: #dcfce7;
  color: #166534;
}

.health-status.warning {
  background: #fef3c7;
  color: #92400e;
}

.health-status.critical {
  background: #fee2e2;
  color: #991b1b;
}

.status-icon {
  font-size: 1.5rem;
}

.status-text {
  font-weight: 700;
  font-size: 1.1rem;
}

.recommendation {
  color: #4b5563;
  font-size: 0.9rem;
  line-height: 1.5;
}
</style>