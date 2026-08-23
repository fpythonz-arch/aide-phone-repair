<template>
  <div class="resource-filter">
    <div class="search-box">
      <span class="search-icon">🔍</span>
      <input
        v-model="localFilters.search"
        type="text"
        placeholder="Rechercher un guide, tutoriel, astuce..."
        @input="emitFilters"
      />
      <button v-if="localFilters.search" class="clear-btn" @click="clearSearch">✕</button>
    </div>

    <div class="filter-chips">
      <div class="filter-group">
        <span class="filter-label">Type :</span>
        <button
          v-for="type in types"
          :key="type.value"
          :class="['filter-chip', { active: localFilters.type === type.value }]"
          @click="toggleFilter('type', type.value)"
        >
          {{ type.icon }} {{ type.label }}
        </button>
      </div>

      <div class="filter-group">
        <span class="filter-label">Niveau :</span>
        <button
          v-for="level in levels"
          :key="level.value"
          :class="['filter-chip', level.value, { active: localFilters.level === level.value }]"
          @click="toggleFilter('level', level.value)"
        >
          {{ level.label }}
        </button>
      </div>

      <div class="filter-group">
        <span class="filter-label">Catégorie :</span>
        <select v-model="localFilters.category" @change="emitFilters">
          <option value="">Toutes</option>
          <option v-for="cat in categories" :key="cat.value" :value="cat.value">
            {{ cat.label }}
          </option>
        </select>
      </div>
    </div>

    <div class="active-filters" v-if="hasActiveFilters">
      <span class="results-count">{{ filteredCount }} résultat(s)</span>
      <button class="reset-btn" @click="resetAll">Réinitialiser les filtres</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { ResourceFilters, ResourceType, ResourceLevel, ResourceCategory } from '@/composables/useRessources'

interface Props {
  modelValue: ResourceFilters
  filteredCount: number
}

const props = defineProps<Props>()
const emit = defineEmits<{
  'update:modelValue': [filters: ResourceFilters]
}>()

// Computed bidirectionnel pour v-model
const localFilters = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

const types = [
  { value: 'guide' as ResourceType, label: 'Guides', icon: '📖' },
  { value: 'video' as ResourceType, label: 'Vidéos', icon: '🎬' },
  { value: 'fiche' as ResourceType, label: 'Fiches', icon: '📋' },
  { value: 'astuce' as ResourceType, label: 'Astuces', icon: '💡' },
]

const levels = [
  { value: 'debutant' as ResourceLevel, label: 'Débutant' },
  { value: 'intermediaire' as ResourceLevel, label: 'Intermédiaire' },
  { value: 'avance' as ResourceLevel, label: 'Avancé' },
  { value: 'expert' as ResourceLevel, label: 'Expert' },
]

const categories = [
  { value: 'screen' as ResourceCategory, label: 'Écran' },
  { value: 'battery' as ResourceCategory, label: 'Batterie' },
  { value: 'charging' as ResourceCategory, label: 'Charge' },
  { value: 'audio' as ResourceCategory, label: 'Audio' },
  { value: 'network' as ResourceCategory, label: 'Réseau' },
  { value: 'software' as ResourceCategory, label: 'Logiciel' },
  { value: 'tools' as ResourceCategory, label: 'Outils' },
  { value: 'safety' as ResourceCategory, label: 'Sécurité' },
]

const hasActiveFilters = computed(() => {
  return localFilters.value.type !== '' ||
         localFilters.value.level !== '' ||
         localFilters.value.category !== '' ||
         localFilters.value.search !== ''
})

const toggleFilter = (key: keyof ResourceFilters, value: string) => {
  const newFilters = { ...localFilters.value }
  if (newFilters[key] === value) {
    newFilters[key] = '' as any
  } else {
    newFilters[key] = value as any
  }
  localFilters.value = newFilters
}

const clearSearch = () => {
  localFilters.value = { ...localFilters.value, search: '' }
}

const resetAll = () => {
  localFilters.value = { type: '', category: '', level: '', search: '' }
}
</script>

<style scoped>
.resource-filter {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
  margin-bottom: 1.5rem;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
  margin-bottom: 1.25rem;
}

.search-icon {
  position: absolute;
  left: 1rem;
  font-size: 1rem;
  opacity: 0.5;
}

.search-box input {
  width: 100%;
  padding: 0.875rem 2.5rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.2s;
}

.search-box input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.clear-btn {
  position: absolute;
  right: 1rem;
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  font-size: 1rem;
}

.clear-btn:hover {
  color: #ef4444;
}

.filter-chips {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.filter-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
}

.filter-chip {
  padding: 0.4rem 0.875rem;
  border: 1px solid #e5e7eb;
  border-radius: 20px;
  background: white;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.filter-chip:hover {
  border-color: #3b82f6;
  color: #3b82f6;
}

.filter-chip.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.filter-chip.debutant.active { background: #22c55e; border-color: #22c55e; }
.filter-chip.intermediaire.active { background: #eab308; border-color: #eab308; }
.filter-chip.avance.active { background: #f97316; border-color: #f97316; }
.filter-chip.expert.active { background: #ef4444; border-color: #ef4444; }

.filter-group select {
  padding: 0.5rem 2rem 0.5rem 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  font-size: 0.85rem;
  cursor: pointer;
}

.active-filters {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.25rem;
  padding-top: 1.25rem;
  border-top: 1px solid #e5e7eb;
}

.results-count {
  font-size: 0.9rem;
  color: #6b7280;
  font-weight: 500;
}

.reset-btn {
  padding: 0.4rem 1rem;
  background: #f3f4f6;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;
}

.reset-btn:hover {
  background: #fee2e2;
  color: #ef4444;
}
</style>