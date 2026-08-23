<template>
  <div class="guide-reader">
    <button class="back-btn" @click="$emit('back')">
      ← Retour aux ressources
    </button>

    <article class="guide-content">
      <header class="guide-header">
        <div class="guide-meta-top">
          <span :class="['type-badge', resource.type]">{{ typeLabel }}</span>
          <span :class="['level-badge', resource.level]">{{ levelLabel }}</span>
          <span class="duration">⏱️ {{ resource.duration }} min</span>
        </div>
        
        <h1>{{ resource.title }}</h1>
        <p class="guide-description">{{ resource.description }}</p>
        
        <div class="guide-author-bar">
          <div class="author-info">
            <span class="author-avatar">✍️</span>
            <div>
              <span class="author-name">{{ resource.author }}</span>
              <span class="author-date">{{ formatDate(resource.createdAt) }}</span>
            </div>
          </div>
          <div class="guide-stats">
            <span>👁️ {{ resource.views }} vues</span>
            <BookmarkButton 
              :is-bookmarked="resource.isBookmarked" 
              @toggle="$emit('toggle-bookmark', resource.id)"
            />
          </div>
        </div>
      </header>

      <div v-if="resource.videoUrl" class="video-container">
        <div class="video-placeholder">
          🎬 Lecteur vidéo
          <p>{{ resource.videoUrl }}</p>
        </div>
      </div>

      <div class="guide-body">
        <div v-if="resource.content" class="content-rendered" v-html="renderedContent"></div>
        
        <div v-else class="content-placeholder">
          <h2>📋 Contenu du guide</h2>
          <div class="placeholder-section" v-for="n in 4" :key="n">
            <h3>Section {{ n }}</h3>
            <p v-for="m in 3" :key="m">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </p>
          </div>
        </div>
      </div>

      <div v-if="resource.tags.length" class="guide-tags">
        <h4>Tags</h4>
        <div class="tags-list">
          <span v-for="tag in resource.tags" :key="tag" class="tag">{{ tag }}</span>
        </div>
      </div>

      <div v-if="resource.relatedComponents?.length" class="related-components">
        <h4>Composants concernés</h4>
        <div class="components-list">
          <span 
            v-for="comp in resource.relatedComponents" 
            :key="comp" 
            class="component-tag"
            @click="$emit('navigate-component', comp)"
          >
            {{ comp }}
          </span>
        </div>
      </div>
    </article>

    <aside class="guide-sidebar">
      <div class="toc-card">
        <h4>📑 Sommaire</h4>
        <ul>
          <li><a href="#introduction">Introduction</a></li>
          <li><a href="#materiel">Matériel nécessaire</a></li>
          <li><a href="#etapes">Étapes</a></li>
          <li><a href="#conseils">Conseils</a></li>
        </ul>
      </div>

      <div class="action-card">
        <h4>🚀 Actions</h4>
        <button class="action-btn" @click="printGuide">
          🖨️ Imprimer
        </button>
        <button class="action-btn" @click="shareGuide">
          🔗 Partager
        </button>
        <button class="action-btn primary" @click="startDiagnostic">
          🔍 Lancer un diagnostic
        </button>
      </div>
    </aside>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import type { Resource } from '@/composables/useRessources'
import BookmarkButton from './BookmarkButton.vue'

interface Props {
  resource: Resource
}

const props = defineProps<Props>()
const emit = defineEmits<{
  back: []
  'toggle-bookmark': [id: number]
  'navigate-component': [component: string]
}>()

const router = useRouter()

const typeLabel = computed(() => {
  const labels: Record<string, string> = {
    guide: 'Guide complet',
    video: 'Tutoriel vidéo',
    fiche: 'Fiche technique',
    astuce: 'Astuce rapide',
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

const renderedContent = computed(() => {
  // Simple markdown-like rendering - dans une vraie app, utilisez marked
  return props.resource.content?.replace(/\n/g, '<br>') || ''
})

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const printGuide = () => {
  window.print()
}

const shareGuide = () => {
  if (navigator.share) {
    navigator.share({
      title: props.resource.title,
      text: props.resource.description,
      url: window.location.href,
    })
  } else {
    navigator.clipboard.writeText(window.location.href)
    alert('Lien copié dans le presse-papiers !')
  }
}

const startDiagnostic = () => {
  router.push('/diagnostic')
}
</script>

<style scoped>
.guide-reader {
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 2rem;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.back-btn {
  grid-column: 1 / -1;
  justify-self: start;
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  cursor: pointer;
  margin-bottom: 0.5rem;
}

.back-btn:hover {
  background: #e5e7eb;
}

.guide-content {
  background: white;
  border-radius: 16px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.guide-header {
  padding: 2rem;
  border-bottom: 1px solid #e5e7eb;
}

.guide-meta-top {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.type-badge {
  padding: 0.3rem 0.875rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
}

.type-badge.guide { background: #dbeafe; color: #1e40af; }
.type-badge.video { background: #fee2e2; color: #991b1b; }
.type-badge.fiche { background: #dcfce7; color: #166534; }
.type-badge.astuce { background: #fef3c7; color: #92400e; }

.level-badge {
  padding: 0.3rem 0.875rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 700;
}

.level-badge.debutant { background: #dcfce7; color: #166534; }
.level-badge.intermediaire { background: #fef3c7; color: #92400e; }
.level-badge.avance { background: #fee2e2; color: #991b1b; }
.level-badge.expert { background: #f3e8ff; color: #6b21a8; }

.duration {
  padding: 0.3rem 0.875rem;
  background: #f3f4f6;
  border-radius: 6px;
  font-size: 0.8rem;
  color: #6b7280;
  font-weight: 500;
}

.guide-header h1 {
  font-size: 2rem;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 0.75rem 0;
  line-height: 1.2;
}

.guide-description {
  font-size: 1.1rem;
  color: #6b7280;
  line-height: 1.6;
  margin: 0 0 1.5rem 0;
}

.guide-author-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.author-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.author-avatar {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  border-radius: 50%;
  font-size: 1.25rem;
}

.author-name {
  display: block;
  font-weight: 600;
  color: #1f2937;
  font-size: 0.9rem;
}

.author-date {
  display: block;
  font-size: 0.8rem;
  color: #9ca3af;
}

.guide-stats {
  display: flex;
  align-items: center;
  gap: 1rem;
  color: #6b7280;
  font-size: 0.85rem;
}

.video-container {
  padding: 2rem;
  background: #0f172a;
}

.video-placeholder {
  aspect-ratio: 16/9;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #1e293b;
  border-radius: 12px;
  color: white;
  font-size: 3rem;
}

.video-placeholder p {
  font-size: 0.9rem;
  opacity: 0.5;
  margin-top: 0.5rem;
}

.guide-body {
  padding: 2rem;
}

.content-rendered {
  font-size: 1.05rem;
  line-height: 1.8;
  color: #374151;
}

.content-placeholder h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.placeholder-section {
  margin-bottom: 2rem;
}

.placeholder-section h3 {
  font-size: 1.15rem;
  font-weight: 700;
  color: #374151;
  margin-bottom: 0.75rem;
}

.placeholder-section p {
  color: #6b7280;
  line-height: 1.7;
  margin-bottom: 0.75rem;
}

.guide-tags, .related-components {
  padding: 1.5rem 2rem;
  border-top: 1px solid #e5e7eb;
}

.guide-tags h4, .related-components h4 {
  font-size: 0.875rem;
  font-weight: 700;
  color: #374151;
  margin: 0 0 0.75rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.tags-list, .components-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tag {
  padding: 0.4rem 0.875rem;
  background: #f3f4f6;
  border-radius: 6px;
  font-size: 0.85rem;
  color: #4b5563;
}

.component-tag {
  padding: 0.4rem 0.875rem;
  background: #eff6ff;
  color: #1e40af;
  border-radius: 6px;
  font-size: 0.85rem;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.component-tag:hover {
  background: #dbeafe;
}

/* Sidebar */
.guide-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  position: sticky;
  top: 2rem;
}

.toc-card, .action-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.25rem;
}

.toc-card h4, .action-card h4 {
  font-size: 0.875rem;
  font-weight: 700;
  color: #374151;
  margin: 0 0 1rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.toc-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.toc-card li {
  margin-bottom: 0.5rem;
}

.toc-card a {
  color: #4b5563;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.2s;
}

.toc-card a:hover {
  color: #3b82f6;
}

.action-btn {
  width: 100%;
  padding: 0.75rem;
  margin-bottom: 0.5rem;
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

.action-btn.primary {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.action-btn.primary:hover {
  background: #2563eb;
  border-color: #2563eb;
}

@media (max-width: 1024px) {
  .guide-reader {
    grid-template-columns: 1fr;
  }

  .guide-sidebar {
    position: static;
  }
}

@media print {
  .back-btn, .guide-sidebar, .video-container {
    display: none;
  }
}
</style>