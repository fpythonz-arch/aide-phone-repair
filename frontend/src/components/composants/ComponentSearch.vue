<template>
  <div class="component-search">
    <div class="search-box">
      <span class="search-icon">🔍</span>
      <input
        v-model="localQuery"
        type="text"
        placeholder="Rechercher un composant (écran, batterie, caméra...)"
        @input="onSearch"
      />
      <button v-if="localQuery" class="clear-btn" @click="clear">✕</button>
    </div>
    
    <div class="quick-filters">
      <button 
        v-for="filter in quickFilters" 
        :key="filter.value"
        :class="['filter-chip', { active: activeFilter === filter.value }]"
        @click="toggleFilter(filter.value)"
      >
        {{ filter.label }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
  modelValue: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'filter': [filter: string]
}>()

const localQuery = ref(props.modelValue)
const activeFilter = ref('')

const quickFilters = [
  { label: '🔋 Batteries', value: 'battery' },
  { label: '📱 Écrans', value: 'screen' },
  { label: '📷 Caméras', value: 'camera' },
  { label: '🔌 Charge', value: 'charging' },
  { label: '🔊 Audio', value: 'speaker' },
]

watch(() => props.modelValue, (newVal) => {
  localQuery.value = newVal
})

const onSearch = () => {
  emit('update:modelValue', localQuery.value)
}

const clear = () => {
  localQuery.value = ''
  activeFilter.value = ''
  emit('update:modelValue', '')
  emit('filter', '')
}

const toggleFilter = (value: string) => {
  if (activeFilter.value === value) {
    activeFilter.value = ''
    localQuery.value = ''
  } else {
    activeFilter.value = value
    localQuery.value = value
  }
  emit('update:modelValue', localQuery.value)
  emit('filter', activeFilter.value)
}
</script>

<style scoped>
.component-search {
  margin-bottom: 1.5rem;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 0 1rem;
  transition: border-color 0.2s;
}

.search-box:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-icon {
  font-size: 1.1rem;
  margin-right: 0.75rem;
  opacity: 0.5;
}

.search-box input {
  flex: 1;
  border: none;
  background: none;
  padding: 0.875rem 0;
  font-size: 1rem;
  outline: none;
}

.clear-btn {
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  font-size: 1rem;
  padding: 0.25rem;
}

.clear-btn:hover {
  color: #ef4444;
}

.quick-filters {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.75rem;
  flex-wrap: wrap;
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
</style>