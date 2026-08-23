<template>
  <div 
    class="category-card"
    :style="{ borderColor: category.color }"
    @click="$emit('select', category.slug)"
  >
    <div class="card-icon" :style="{ backgroundColor: category.color + '20' }">
      {{ category.icon }}
    </div>
    <div class="card-content">
      <h3>{{ category.name }}</h3>
      <p>{{ category.description }}</p>
    </div>
    <div class="card-arrow" :style="{ color: category.color }">→</div>
  </div>
</template>

<script setup lang="ts">
import type { TroubleshootingCategory } from '@/composables/useDepannage'

interface Props {
  category: TroubleshootingCategory
}

defineProps<Props>()
defineEmits<{
  select: [slug: string]
}>()
</script>

<style scoped>
.category-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.5rem;
  background: white;
  border: 2px solid;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.category-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.card-icon {
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  font-size: 1.75rem;
  flex-shrink: 0;
}

.card-content {
  flex: 1;
  min-width: 0;
}

.card-content h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 0.35rem 0;
}

.card-content p {
  font-size: 0.85rem;
  color: #6b7280;
  margin: 0;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-arrow {
  font-size: 1.5rem;
  font-weight: 700;
  opacity: 0;
  transform: translateX(-10px);
  transition: all 0.3s;
}

.category-card:hover .card-arrow {
  opacity: 1;
  transform: translateX(0);
}
</style>