<template>
  <div class="screen-test">
    <h3>📱 Testeur d'Écran</h3>
    
    <div class="test-grid">
      <div 
        v-for="(test, index) in tests" 
        :key="index"
        class="test-cell"
        :class="{ passed: test.passed, failed: test.failed }"
        @click="runTest(index)"
      >
        <div class="test-icon">{{ test.icon }}</div>
        <div class="test-name">{{ test.name }}</div>
        <div class="test-status">
          <span v-if="test.passed">✅ OK</span>
          <span v-else-if="test.failed">❌ Défaut</span>
          <span v-else class="pending">Cliquer pour tester</span>
        </div>
      </div>
    </div>

    <div v-if="allTested" class="test-summary">
      <div class="summary-header">
        <h4>Résultat global</h4>
        <span :class="['summary-badge', summaryClass]">
          {{ passedCount }}/{{ tests.length }} tests OK
        </span>
      </div>
      <p class="summary-advice">{{ summaryAdvice }}</p>
    </div>

    <div class="color-test">
      <h4>Test des couleurs</h4>
      <div class="color-bars">
        <div class="color-bar red" @click="showColorInfo('Rouge')"></div>
        <div class="color-bar green" @click="showColorInfo('Vert')"></div>
        <div class="color-bar blue" @click="showColorInfo('Bleu')"></div>
        <div class="color-bar white" @click="showColorInfo('Blanc')"></div>
        <div class="color-bar black" @click="showColorInfo('Noir')"></div>
      </div>
      <p class="color-hint">Touchez chaque bande pour vérifier l'absence de pixels morts</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface Test {
  name: string
  icon: string
  passed: boolean
  failed: boolean
}

const tests = ref<Test[]>([
  { name: 'Touché', icon: '👆', passed: false, failed: false },
  { name: 'Luminosité', icon: '☀️', passed: false, failed: false },
  { name: 'Contraste', icon: '◐', passed: false, failed: false },
  { name: 'Dead Pixels', icon: '🔍', passed: false, failed: false },
])

const runTest = (index: number) => {
  // Simulation - dans la vraie app, tu utiliserais des APIs ou des interactions
  const test = tests.value[index]
  if (test.passed || test.failed) {
    test.passed = false
    test.failed = false
    return
  }
  // Simulation aléatoire pour la démo
  const success = Math.random() > 0.3
  test.passed = success
  test.failed = !success
}

const allTested = computed(() => {
  return tests.value.every(t => t.passed || t.failed)
})

const passedCount = computed(() => {
  return tests.value.filter(t => t.passed).length
})

const summaryClass = computed(() => {
  if (passedCount.value === tests.value.length) return 'all-good'
  if (passedCount.value >= tests.value.length / 2) return 'partial'
  return 'critical'
})

const summaryAdvice = computed(() => {
  if (passedCount.value === tests.value.length) {
    return 'Écran en parfait état. Aucun problème détecté.'
  }
  if (passedCount.value >= tests.value.length / 2) {
    return 'Quelques défauts détectés. Évaluez si un remplacement est nécessaire selon l\'usage.'
  }
  return 'Plusieurs défauts critiques. Remplacement de l\'écran fortement recommandé.'
})

const showColorInfo = (color: string) => {
  alert(`Vérifiez visuellement que la bande ${color} est uniforme sans pixels morts ou décoloration.`)
}
</script>

<style scoped>
.screen-test {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  border: 1px solid #e5e7eb;
}

.screen-test h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.test-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.test-cell {
  padding: 1.25rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.test-cell:hover {
  border-color: #3b82f6;
  transform: translateY(-2px);
}

.test-cell.passed {
  border-color: #22c55e;
  background: #f0fdf4;
}

.test-cell.failed {
  border-color: #ef4444;
  background: #fef2f2;
}

.test-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.test-name {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.test-status {
  font-size: 0.8rem;
}

.pending {
  color: #9ca3af;
}

.test-summary {
  padding: 1.25rem;
  border-radius: 12px;
  background: #f9fafb;
  margin-bottom: 1.5rem;
}

.summary-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.summary-header h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

.summary-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
}

.summary-badge.all-good {
  background: #dcfce7;
  color: #166534;
}

.summary-badge.partial {
  background: #fef3c7;
  color: #92400e;
}

.summary-badge.critical {
  background: #fee2e2;
  color: #991b1b;
}

.summary-advice {
  color: #4b5563;
  font-size: 0.9rem;
  line-height: 1.5;
}

.color-test h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.color-bars {
  display: flex;
  height: 60px;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
}

.color-bar {
  flex: 1;
  transition: opacity 0.2s;
}

.color-bar:hover {
  opacity: 0.8;
}

.color-bar.red { background: #ef4444; }
.color-bar.green { background: #22c55e; }
.color-bar.blue { background: #3b82f6; }
.color-bar.white { background: #ffffff; border: 1px solid #e5e7eb; }
.color-bar.black { background: #1f2937; }

.color-hint {
  margin-top: 0.75rem;
  font-size: 0.8rem;
  color: #6b7280;
  text-align: center;
}
</style>