<template>
  <div class="solution-result">
    <div class="result-header">
      <div class="result-icon">🎯</div>
      <h3>Solutions trouvées</h3>
      <p>D'après vos vérifications, voici les solutions recommandées</p>
    </div>

    <div class="solutions-list">
      <div
        v-for="solution in solutions"
        :key="solution.id"
        :class="['solution-card', `difficulty-${solution.difficulty}`]"
      >
        <div class="solution-header">
          <h4>{{ solution.title }}</h4>
          <span :class="['difficulty-badge', solution.difficulty]">
            {{ difficultyLabel(solution.difficulty) }}
          </span>
        </div>
        
        <p class="solution-desc">{{ solution.description }}</p>
        
        <div class="solution-meta">
          <div class="meta-item cost">
            <span class="meta-label">Coût estimé</span>
            <span class="meta-value">{{ solution.estimatedCost }}€</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Remplacement</span>
            <span :class="['meta-value', solution.needsReplacement ? 'needed' : 'not-needed']">
              {{ solution.needsReplacement ? 'Oui' : 'Non' }}
            </span>
          </div>
          <div v-if="solution.replacementPart" class="meta-item">
            <span class="meta-label">Pièce</span>
            <span class="meta-value">{{ solution.replacementPart }}</span>
          </div>
        </div>

        <div class="solution-actions">
          <button class="action-btn guide" @click="openGuide(solution)">
            📖 Voir le guide
          </button>
          <button class="action-btn order" @click="orderPart(solution)">
            🛒 Commander la pièce
          </button>
        </div>
      </div>
    </div>

    <div class="result-footer">
      <button class="restart-btn" @click="$emit('restart')">
        🔄 Recommencer le diagnostic
      </button>
      <button class="expert-btn" @click="contactExpert">
        👨‍🔧 Contacter un expert
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Solution } from '@/composables/useDepannage'

interface Props {
  solutions: Solution[]
}

defineProps<Props>()
defineEmits<{
  restart: []
}>()

const difficultyLabel = (difficulty: string) => {
  const labels: Record<string, string> = {
    easy: 'Facile',
    medium: 'Moyen',
    hard: 'Difficile',
    expert: 'Expert',
  }
  return labels[difficulty] || difficulty
}

const openGuide = (solution: Solution) => {
  if (solution.guideUrl) {
    window.open(solution.guideUrl, '_blank')
  } else {
    alert('Guide détaillé à venir')
  }
}

const orderPart = (solution: Solution) => {
  if (solution.replacementPart) {
    alert(`Recherche de : ${solution.replacementPart}`)
  }
}

const contactExpert = () => {
  alert('Redirection vers le formulaire de contact expert...')
}
</script>

<style scoped>
.solution-result {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  border: 1px solid #e5e7eb;
  animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.result-header {
  text-align: center;
  margin-bottom: 2rem;
}

.result-icon {
  font-size: 3rem;
  margin-bottom: 0.5rem;
}

.result-header h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 0.5rem 0;
}

.result-header p {
  color: #6b7280;
  margin: 0;
}

.solutions-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.solution-card {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.2s;
}

.solution-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.solution-card.difficulty-easy { border-left: 4px solid #22c55e; }
.solution-card.difficulty-medium { border-left: 4px solid #eab308; }
.solution-card.difficulty-hard { border-left: 4px solid #f97316; }
.solution-card.difficulty-expert { border-left: 4px solid #ef4444; }

.solution-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.solution-header h4 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.difficulty-badge {
  padding: 0.25rem 0.625rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.difficulty-badge.easy { background: #dcfce7; color: #166534; }
.difficulty-badge.medium { background: #fef3c7; color: #92400e; }
.difficulty-badge.hard { background: #fee2e2; color: #991b1b; }
.difficulty-badge.expert { background: #f3e8ff; color: #6b21a8; }

.solution-desc {
  color: #4b5563;
  font-size: 0.9rem;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.solution-meta {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.meta-label {
  font-size: 0.75rem;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.meta-value {
  font-size: 1rem;
  font-weight: 700;
  color: #1f2937;
}

.meta-value.needed {
  color: #ef4444;
}

.meta-value.not-needed {
  color: #22c55e;
}

.solution-actions {
  display: flex;
  gap: 0.75rem;
}

.action-btn {
  flex: 1;
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn:hover {
  border-color: #3b82f6;
  color: #3b82f6;
}

.action-btn.guide {
  background: #eff6ff;
  border-color: #bfdbfe;
  color: #1e40af;
}

.action-btn.guide:hover {
  background: #dbeafe;
}

.action-btn.order {
  background: #f0fdf4;
  border-color: #bbf7d0;
  color: #166534;
}

.action-btn.order:hover {
  background: #dcfce7;
}

.result-footer {
  display: flex;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.restart-btn, .expert-btn {
  flex: 1;
  padding: 0.875rem;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.restart-btn {
  background: #f3f4f6;
  border: 2px solid #e5e7eb;
  color: #374151;
}

.restart-btn:hover {
  background: #e5e7eb;
}

.expert-btn {
  background: #1f2937;
  border: 2px solid #1f2937;
  color: white;
}

.expert-btn:hover {
  background: #111827;
}

@media (max-width: 640px) {
  .solution-actions,
  .result-footer {
    flex-direction: column;
  }
}
</style>