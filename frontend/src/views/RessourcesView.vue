<template>
  <div class="ressources-view">
    <header class="page-header">
      <h1>📚 Ressources & Apprentissage</h1>
      <p>Guides, tutoriels et fiches techniques pour maîtriser la réparation mobile</p>
    </header>

    <!-- Tabs -->
    <div class="view-tabs">
      <button 
        :class="['tab-btn', { active: activeTab === 'all' }]"
        @click="activeTab = 'all'"
      >
        📚 Toutes les ressources
      </button>
      <button 
        :class="['tab-btn', { active: activeTab === 'bookmarks' }]"
        @click="activeTab = 'bookmarks'"
      >
        ⭐ Mes favoris ({{ bookmarkedResources.length }})
      </button>
    </div>

    <!-- Filtres -->
    <ResourceFilter 
      v-if="activeTab === 'all'"
      v-model="filters" 
      :filtered-count="displayedResources.length"
    />

    <!-- Liste des ressources -->
    <div v-if="!selectedResource" class="resources-grid">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Chargement des ressources...</p>
      </div>

      <div v-else-if="displayedResources.length === 0" class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>Aucune ressource trouvée</h3>
        <p>Essayez de modifier vos filtres ou revenez plus tard.</p>
      </div>

      <ResourceCard
        v-for="resource in displayedResources"
        :key="resource.id"
        :resource="resource"
        @select="selectResource"
        @toggle-bookmark="toggleBookmark"
      />
    </div>

    <!-- Lecteur de guide -->
    <GuideReader
      v-else
      :resource="selectedResource"
      @back="selectedResource = null"
      @toggle-bookmark="toggleBookmark"
      @navigate-component="navigateToComponent"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useRessources, type Resource } from '@/composables/useRessources'
import ResourceFilter from '@/components/ressources/ResourceFilter.vue'
import ResourceCard from '@/components/ressources/ResourceCard.vue'
import GuideReader from '@/components/ressources/GuideReader.vue'

const router = useRouter()
const route = useRoute()

const {
  resources,
  selectedResource,
  loading,
  filters,
  bookmarkedResources,
  fetchResources,
  toggleBookmark,
} = useRessources()

const activeTab = ref<'all' | 'bookmarks'>('all')

// ── Lire les query params au montage ─────────────────
onMounted(() => {
  fetchResources()

  const urlFilter = route.query.filter as string
  const urlType = route.query.type as string

  if (urlFilter) {
    filters.value.search = urlFilter.replace(/-/g, ' ')
  }
  if (urlType) {
    filters.value.type = urlType
  }

  // Auto-sélectionner si match exact
  if (urlFilter) {
    const matched = resources.value.find(r => 
      r.slug === urlFilter || 
      r.title.toLowerCase().includes(urlFilter.replace(/-/g, ' ').toLowerCase())
    )
    if (matched) {
      selectedResource.value = matched
    }
  }
})

const displayedResources = computed(() => {
  if (activeTab.value === 'bookmarks') {
    return bookmarkedResources.value
  }
  return resources.value.filter(r => {
    if (filters.value.type && r.type !== filters.value.type) return false
    if (filters.value.category && r.category !== filters.value.category) return false
    if (filters.value.level && r.level !== filters.value.level) return false
    if (filters.value.search) {
      const q = filters.value.search.toLowerCase()
      return r.title.toLowerCase().includes(q) ||
             r.description.toLowerCase().includes(q) ||
             r.tags.some(t => t.toLowerCase().includes(q))
    }
    return true
  })
})

const selectResource = (resource: Resource) => {
  selectedResource.value = resource
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const navigateToComponent = (component: string) => {
  router.push(`/composants?search=${encodeURIComponent(component)}`)
}
</script>
<style scoped>
.ressources-view {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
  min-height: 100vh;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 800;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #6b7280;
  font-size: 1.05rem;
}

.view-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  border-bottom: 2px solid #e5e7eb;
  padding-bottom: 0;
}

.tab-btn {
  padding: 0.75rem 1.25rem;
  border: none;
  background: none;
  font-size: 0.95rem;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  position: relative;
  transition: color 0.2s;
}

.tab-btn:hover {
  color: #3b82f6;
}

.tab-btn.active {
  color: #3b82f6;
}

.tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: #3b82f6;
}

.resources-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.loading-state, .empty-state {
  grid-column: 1 / -1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  text-align: center;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #6b7280;
}

@media (max-width: 768px) {
  .ressources-view {
    padding: 1rem;
  }

  .resources-grid {
    grid-template-columns: 1fr;
  }
}
</style>