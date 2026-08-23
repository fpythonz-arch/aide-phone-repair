<template>
  <div 
    class="tool-card"
    :class="{ active: isActive }"
    @click="$emit('select', tool)"
  >
    <div class="tool-icon">{{ tool.icon }}</div>
    <div class="tool-info">
      <h3>{{ tool.name }}</h3>
      <p>{{ tool.description }}</p>
      <span class="category-badge" :class="tool.category">
        {{ categoryLabel }}
      </span>
    </div>
    <div class="tool-arrow">→</div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Tool } from '@/composables/useTools'

interface Props {
  tool: Tool
  isActive?: boolean
}

const props = defineProps<Props>()
defineEmits<{
  select: [tool: Tool]
}>()

const categoryLabel = computed(() => {
  const labels: Record<string, string> = {
    calculator: 'Calculateur',
    tester: 'Testeur',
    checker: 'Vérificateur',
    guide: 'Guide',
  }
  return labels[props.tool.category] || props.tool.category
})
</script>

<style scoped>
.tool-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tool-card:hover {
  border-color: #3b82f6;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.tool-card.active {
  border-color: #3b82f6;
  background: #eff6ff;
}

.tool-icon {
  font-size: 2rem;
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  border-radius: 12px;
  flex-shrink: 0;
}

.tool-info {
  flex: 1;
  min-width: 0;
}

.tool-info h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.tool-info p {
  font-size: 0.85rem;
  color: #6b7280;
  line-height: 1.4;
  margin-bottom: 0.5rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.category-badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.category-badge.calculator {
  background: #dbeafe;
  color: #1e40af;
}

.category-badge.tester {
  background: #dcfce7;
  color: #166534;
}

.category-badge.checker {
  background: #fef3c7;
  color: #92400e;
}

.category-badge.guide {
  background: #f3e8ff;
  color: #6b21a8;
}

.tool-arrow {
  color: #9ca3af;
  font-size: 1.25rem;
  transition: color 0.2s;
}

.tool-card:hover .tool-arrow {
  color: #3b82f6;
}
</style>