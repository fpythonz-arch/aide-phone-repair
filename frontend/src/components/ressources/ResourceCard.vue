<template>
  <div 
    class="resource-card"
    :class="[resource.type, resource.level]"
    @click="$emit('select', resource)"
  >
    <div class="card-thumbnail">
      <div class="thumbnail-placeholder">
        {{ getTypeIcon(resource.type) }}
      </div>
      <span class="type-badge">{{ typeLabel }}</span>
      <span class="duration-badge">⏱️ {{ resource.duration }}min</span>
    </div>

    <div class="card-content">
      <div class="card-header">
        <h3>{{ resource.title }}</h3>
        <BookmarkButton
          :is-bookmarked="resource.isBookmarked"
          @toggle="$emit('toggle-bookmark', resource.id)"
        />
      </div>

      <p class="card-description">{{ resource.description }}</p>

      <div class="card-meta">
        <span :class="['level-badge', resource.level]">{{ levelLabel }}</span>
        <span class="category-badge">{{ categoryLabel }}</span>
        <span class="views-badge">👁️ {{ formatViews(resource.views) }}</span>
      </div>

      <div class="card-tags">
        <span v-for="tag in resource.tags.slice(0, 3)" :key="tag" class="tag">
          {{ tag }}
        </span>
      </div>

      <div class="card-footer">
        <span class="author">✍️ {{ resource.author }}</span>
        <span class="date">{{ formatDate(resource.createdAt) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Resource } from '@/composables/useRessources'
import BookmarkButton from './BookmarkButton.vue'

interface Props {
  resource: Resource
}

const props = defineProps<Props>()
defineEmits<{
  select: [resource: Resource]
  'toggle-bookmark': [id: number]
}>()

const typeLabel = computed(() => {
  const labels: Record<string, string> = {
    guide: 'Guide',
    video: 'Vidéo',
    fiche: 'Fiche',
    astuce: 'Astuce',
  }
  return labels[props.resource.type] || props.resource.type
})

const levelLabel = computed(() => {
  const labels: Record<string, string> = {
    debutant: 'Débutant',
    intermediaire: 'Intermédiaire',
    avance: 'Avancé',
    expert: 'Expert',
  }
  return labels[props.resource.level] || props.resource.level
})

const categoryLabel = computed(() => {
  const labels: Record<string, string> = {
    screen: 'Écran',
    battery: 'Batterie',
    charging: 'Charge',
    audio: 'Audio',
    network: 'Réseau',
    software: 'Logiciel',
    tools: 'Outils',
    safety: 'Sécurité',
  }
  return labels[props.resource.category] || props.resource.category
})

const getTypeIcon = (type: string) => {
  const icons: Record<string, string> = {
    guide: '📖',
    video: '🎬',
    fiche: '📋',
    astuce: '💡',
  }
  return icons[type] || '📄'
}

const formatViews = (views: number) => {
  if (views >= 1000) return (views / 1000).toFixed(1) + 'k'
  return views.toString()
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<style scoped>
.resource-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  border: 2px solid #e5e7eb;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
}

.resource-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
  border-color: #3b82f6;
}

.card-thumbnail {
  position: relative;
  height: 160px;
  background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.thumbnail-placeholder {
  font-size: 4rem;
  opacity: 0.3;
}

.type-badge {
  position: absolute;
  top: 0.75rem;
  left: 0.75rem;
  padding: 0.3rem 0.75rem;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.duration-badge {
  position: absolute;
  bottom: 0.75rem;
  right: 0.75rem;
  padding: 0.3rem 0.625rem;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.card-content {
  padding: 1.25rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.card-header h3 {
  font-size: 1rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-description {
  font-size: 0.85rem;
  color: #6b7280;
  line-height: 1.5;
  margin: 0 0 0.75rem 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-meta {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  flex-wrap: wrap;
}

.level-badge {
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
}

.level-badge.debutant { background: #dcfce7; color: #166534; }
.level-badge.intermediaire { background: #fef3c7; color: #92400e; }
.level-badge.avance { background: #fee2e2; color: #991b1b; }
.level-badge.expert { background: #f3e8ff; color: #6b21a8; }

.category-badge {
  padding: 0.2rem 0.5rem;
  background: #eff6ff;
  color: #1e40af;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
}

.views-badge {
  padding: 0.2rem 0.5rem;
  background: #f3f4f6;
  color: #6b7280;
  border-radius: 4px;
  font-size: 0.7rem;
}

.card-tags {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-bottom: 0.75rem;
}

.tag {
  padding: 0.15rem 0.5rem;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  font-size: 0.75rem;
  color: #6b7280;
}

.card-footer {
  margin-top: auto;
  padding-top: 0.75rem;
  border-top: 1px solid #f3f4f6;
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #9ca3af;
}

.author {
  font-weight: 500;
}

/* Type-specific accents */
.resource-card.guide { border-top: 4px solid #3b82f6; }
.resource-card.video { border-top: 4px solid #ef4444; }
.resource-card.fiche { border-top: 4px solid #22c55e; }
.resource-card.astuce { border-top: 4px solid #f59e0b; }
</style>