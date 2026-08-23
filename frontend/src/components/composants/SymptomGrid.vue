<template>
  <div class="symptom-grid">
    <h3>⚠️ Explorer par Symptôme</h3>
    <p class="subtitle">Cliquez sur un symptôme pour trouver le composant concerné</p>
    
    <div class="symptoms-grid">
      <div
        v-for="symptom in allSymptoms"
        :key="symptom.id"
        :class="['symptom-card', `severity-${symptom.severity}`]"
        @click="$emit('select-symptom', symptom)"
      >
        <div class="symptom-icon">{{ getSymptomIcon(symptom.name) }}</div>
        <div class="symptom-info">
          <h4>{{ symptom.name }}</h4>
          <p>{{ symptom.description }}</p>
          <span class="affected-count">
            {{ symptom.components?.length || 0 }} composant(s) concerné(s)
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Component, Symptom } from '@/composables/useComponents'

interface Props {
  components: Component[]
}

const props = defineProps<Props>()
defineEmits<{
  'select-symptom': [symptom: Symptom & { components?: Component[] }]
}>()

const allSymptoms = computed(() => {
  const symptomMap = new Map()
  
  props.components.forEach(component => {
    component.symptoms?.forEach(symptom => {
      if (!symptomMap.has(symptom.id)) {
        symptomMap.set(symptom.id, { ...symptom, components: [] })
      }
      symptomMap.get(symptom.id).components.push(component)
    })
  })
  
  return Array.from(symptomMap.values())
})

const getSymptomIcon = (name: string) => {
  const icons: Record<string, string> = {
    'ne s\'allume pas': '🔌',
    'écran noir': '🖤',
    'pas de son': '🔇',
    'ne charge pas': '🔋',
    'surchauffe': '🌡️',
    'camera floue': '📷',
    'bouton cassé': '🔘',
    'wifi faible': '📶',
    'tactile mort': '👆',
    'vibration': '📳',
  }
  
  const lowerName = name.toLowerCase()
  for (const [key, icon] of Object.entries(icons)) {
    if (lowerName.includes(key)) return icon
  }
  return '⚠️'
}
</script>

<style scoped>
.symptom-grid {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
  margin-top: 1.5rem;
}

.symptom-grid h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
}

.subtitle {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 1.25rem;
}

.symptoms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}

.symptom-card {
  display: flex;
  gap: 1rem;
  padding: 1.25rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  background: white;
}

.symptom-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.symptom-card.severity-low:hover { border-color: #22c55e; }
.symptom-card.severity-medium:hover { border-color: #eab308; }
.symptom-card.severity-high:hover { border-color: #ef4444; }
.symptom-card.severity-critical:hover { border-color: #991b1b; }

.symptom-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.symptom-info {
  min-width: 0;
}

.symptom-info h4 {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
}

.symptom-info p {
  font-size: 0.85rem;
  color: #6b7280;
  margin: 0 0 0.5rem 0;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.affected-count {
  font-size: 0.75rem;
  color: #3b82f6;
  font-weight: 600;
}
</style>