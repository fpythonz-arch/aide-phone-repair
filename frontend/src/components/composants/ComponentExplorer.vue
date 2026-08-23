<template>
  <div class="component-explorer">
    <div class="explorer-header">
      <h3>🔧 Exploration des Composants</h3>
      <div class="view-toggle">
        <button 
          :class="['toggle-btn', { active: viewMode === 'grid' }]"
          @click="viewMode = 'grid'"
        >
          ⊞ Grille
        </button>
        <button 
          :class="['toggle-btn', { active: viewMode === 'list' }]"
          @click="viewMode = 'list'"
        >
          ☰ Liste
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des composants...</p>
    </div>

    <div v-else-if="components.length === 0" class="empty-state">
      <div class="empty-icon">🔧</div>
      <p>Aucun composant trouvé</p>
    </div>

    <div v-else :class="['components-container', viewMode]">
      <div
        v-for="component in components"
        :key="component.id"
        :class="['component-item', `difficulty-${component.difficulty}`]"
        @click="$emit('select', component)"
      >
        <div class="component-icon">{{ getIcon(component.type) }}</div>
        
        <div class="component-info">
          <div class="component-header">
            <h4>{{ component.name }}</h4>
            <span :class="['difficulty-badge', component.difficulty]">
              {{ difficultyLabel(component.difficulty) }}
            </span>
          </div>
          
          <p class="component-desc">{{ component.description }}</p>
          
          <div class="component-meta">
            <span class="meta-item">📍 {{ component.position }}</span>
            <span class="meta-item">⏱️ {{ component.estimatedTime }}min</span>
            <span v-if="component.averagePrice" class="meta-item">
              💰 ~{{ component.averagePrice }}€
            </span>
          </div>

          <div v-if="component.symptoms?.length" class="symptom-tags">
            <span 
              v-for="symptom in component.symptoms.slice(0, 3)" 
              :key="symptom.id"
              class="symptom-tag"
              :class="`severity-${symptom.severity}`"
            >
              {{ symptom.name }}
            </span>
            <span v-if="component.symptoms.length > 3" class="more-tag">
              +{{ component.symptoms.length - 3 }}
            </span>
          </div>
        </div>

        <div class="component-arrow">→</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { Component } from '@/composables/useComponents'

interface Props {
  components: Component[]
  loading?: boolean
}

defineProps<Props>()
defineEmits<{
  select: [component: Component]
}>()

const viewMode = ref<'grid' | 'list'>('grid')

const getIcon = (type: string) => {
  const icons: Record<string, string> = {
    screen: '📱',
    battery: '🔋',
    camera: '📷',
    motherboard: '🧠',
    charging: '🔌',
    speaker: '🔊',
    sensor: '📡',
    button: '🔘',
    antenna: '📶',
    other: '🔧',
  }
  return icons[type] || '🔧'
}

const difficultyLabel = (difficulty: string) => {
  const labels: Record<string, string> = {
    easy: 'Facile',
    medium: 'Moyen',
    hard: 'Difficile',
    expert: 'Expert',
  }
  return labels[difficulty] || difficulty
}
</script>

<style scoped>
.component-explorer {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
}

.explorer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.explorer-header h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
}

.view-toggle {
  display: flex;
  gap: 0.25rem;
  background: #f3f4f6;
  padding: 0.25rem;
  border-radius: 8px;
}

.toggle-btn {
  padding: 0.4rem 0.75rem;
  border: none;
  background: transparent;
  border-radius: 6px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
  color: #6b7280;
}

.toggle-btn.active {
  background: white;
  color: #1f2937;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.components-container {
  display: grid;
  gap: 1rem;
}

.components-container.grid {
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}

.components-container.list {
  grid-template-columns: 1fr;
}

.component-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.25rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  background: white;
}

.component-item:hover {
  border-color: #3b82f6;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.component-icon {
  font-size: 2.5rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f9fafb;
  border-radius: 12px;
  flex-shrink: 0;
}

.component-info {
  flex: 1;
  min-width: 0;
}

.component-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.component-header h4 {
  font-size: 1rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.difficulty-badge {
  padding: 0.2rem 0.6rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  white-space: nowrap;
}

.difficulty-badge.easy {
  background: #dcfce7;
  color: #166534;
}

.difficulty-badge.medium {
  background: #fef3c7;
  color: #92400e;
}

.difficulty-badge.hard {
  background: #fee2e2;
  color: #991b1b;
}

.difficulty-badge.expert {
  background: #f3e8ff;
  color: #6b21a8;
}

.component-desc {
  font-size: 0.85rem;
  color: #6b7280;
  line-height: 1.5;
  margin-bottom: 0.75rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.component-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 0.75rem;
  flex-wrap: wrap;
}

.meta-item {
  font-size: 0.8rem;
  color: #6b7280;
}

.symptom-tags {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.symptom-tag {
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.symptom-tag.severity-low {
  background: #dcfce7;
  color: #166534;
}

.symptom-tag.severity-medium {
  background: #fef3c7;
  color: #92400e;
}

.symptom-tag.severity-high {
  background: #fee2e2;
  color: #991b1b;
}

.symptom-tag.severity-critical {
  background: #fecaca;
  color: #7f1d1d;
}

.more-tag {
  padding: 0.2rem 0.5rem;
  background: #f3f4f6;
  color: #6b7280;
  border-radius: 4px;
  font-size: 0.75rem;
}

.component-arrow {
  color: #d1d5db;
  font-size: 1.25rem;
  align-self: center;
  transition: color 0.2s;
}
</style>