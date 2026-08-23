<template>
  <div class="full-guide-view">
    <header class="guide-header">
      <button class="back-btn" @click="goBack">
        ← Retour au diagnostic
      </button>
      <div class="header-title">
        <span class="header-icon">{{ fullGuide?.category?.icon || '📚' }}</span>
        <div>
          <h1>📚 Guide Complet : {{ fullGuide?.category?.name || 'Ressources' }}</h1>
          <p>Vidéos, tutoriels, forums, pièces de rechange et conseils d'experts</p>
        </div>
      </div>
    </header>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des ressources...</p>
    </div>

    <div v-else-if="fullGuide" class="guide-content">
      <!-- Avertissements -->
      <div v-if="fullGuide.warnings.length" class="warnings-section">
        <h3>⚠️ Avertissements importants</h3>
        <div class="warning-list">
          <div v-for="(warning, idx) in fullGuide.warnings" :key="idx" class="warning-item">
            <span class="warning-icon">⚠️</span>
            <span>{{ warning }}</span>
          </div>
        </div>
      </div>

      <!-- Conseils pro -->
      <div v-if="fullGuide.tips.length" class="tips-section">
        <h3>💡 Conseils d'experts</h3>
        <div class="tips-list">
          <div v-for="(tip, idx) in fullGuide.tips" :key="idx" class="tip-item">
            <span class="tip-icon">💡</span>
            <span>{{ tip }}</span>
          </div>
        </div>
      </div>

      <!-- Ressources par type -->
      <div class="resources-section">
        <h3>📚 Ressources & Tutoriels</h3>
        
        <!-- Vidéos -->
        <div v-if="videoResources.length" class="resource-group">
          <h4>🎥 Vidéos</h4>
          <div class="resource-grid">
            <div
              v-for="resource in videoResources"
              :key="resource.id"
              class="resource-card video-card"
              @click="openResource(resource.url)"
            >
              <div class="resource-thumbnail">
                <span class="play-icon">▶️</span>
                <img v-if="resource.thumbnail" :src="resource.thumbnail" :alt="resource.title" />
                <div v-else class="placeholder-thumb">🎥</div>
              </div>
              <div class="resource-info">
                <h5>{{ resource.title }}</h5>
                <p>{{ resource.description }}</p>
                <div class="resource-meta">
                  <span v-if="resource.duration">⏱️ {{ resource.duration }}</span>
                  <span v-if="resource.author">👤 {{ resource.author }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Articles -->
        <div v-if="articleResources.length" class="resource-group">
          <h4>📄 Articles & Guides</h4>
          <div class="resource-list">
            <div
              v-for="resource in articleResources"
              :key="resource.id"
              class="resource-row"
              @click="openResource(resource.url)"
            >
              <span class="row-icon">📄</span>
              <div class="row-info">
                <h5>{{ resource.title }}</h5>
                <p>{{ resource.description }}</p>
              </div>
              <span class="row-arrow">→</span>
            </div>
          </div>
        </div>

        <!-- Forums -->
        <div v-if="forumResources.length" class="resource-group">
          <h4>💬 Forums & Communautés</h4>
          <div class="resource-list">
            <div
              v-for="resource in forumResources"
              :key="resource.id"
              class="resource-row"
              @click="openResource(resource.url)"
            >
              <span class="row-icon">💬</span>
              <div class="row-info">
                <h5>{{ resource.title }}</h5>
                <p>{{ resource.description }}</p>
              </div>
              <span class="row-arrow">→</span>
            </div>
          </div>
        </div>

        <!-- Boutiques -->
        <div v-if="shopResources.length" class="resource-group">
          <h4>🛒 Pièces de rechange & Outils</h4>
          <div class="resource-grid">
            <div
              v-for="resource in shopResources"
              :key="resource.id"
              class="resource-card shop-card"
              @click="openResource(resource.url)"
            >
              <div class="shop-icon">🛒</div>
              <div class="resource-info">
                <h5>{{ resource.title }}</h5>
                <p>{{ resource.description }}</p>
                <span class="shop-author" v-if="resource.author">{{ resource.author }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Outils -->
        <div v-if="toolResources.length" class="resource-group">
          <h4>🛠️ Outils recommandés</h4>
          <div class="tools-grid">
            <div
              v-for="resource in toolResources"
              :key="resource.id"
              class="tool-card"
              @click="openResource(resource.url)"
            >
              <span class="tool-icon">🛠️</span>
              <span class="tool-name">{{ resource.title }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pannes liées -->
      <div v-if="fullGuide.relatedPannes.length" class="related-section">
        <h3>🔗 Pannes liées</h3>
        <div class="related-grid">
          <div
            v-for="panne in fullGuide.relatedPannes"
            :key="panne.id"
            class="related-card"
            @click="goToPanne(panne.slug)"
          >
            <span class="related-icon">{{ panne.icon }}</span>
            <span class="related-name">{{ panne.name }}</span>
            <span class="related-type" :class="panne.type">{{ panne.type === 'hardware' ? 'Hardware' : 'Software' }}</span>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <div class="cta-section">
        <h3>🔧 Prêt à réparer ?</h3>
        <p>Suivez notre guide de réparation étape par étape.</p>
        <button class="cta-btn" @click="goToRepair">
          🔧 Commencer la réparation
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useDepannage } from '@/composables/useDepannage'
import type { ResourceItem } from '@/composables/useDepannage'

const route = useRoute()
const router = useRouter()
const routeType = computed(() => route.params.type as string)

const { fetchFullGuide } = useDepannage()

const fullGuide = ref<Awaited<ReturnType<typeof fetchFullGuide>> | null>(null)
const loading = ref(true)

const videoResources = computed(() => 
  fullGuide.value?.resources.filter(r => r.type === 'video') || []
)
const articleResources = computed(() => 
  fullGuide.value?.resources.filter(r => r.type === 'article') || []
)
const forumResources = computed(() => 
  fullGuide.value?.resources.filter(r => r.type === 'forum') || []
)
const shopResources = computed(() => 
  fullGuide.value?.resources.filter(r => r.type === 'shop') || []
)
const toolResources = computed(() => 
  fullGuide.value?.resources.filter(r => r.type === 'tool') || []
)

const openResource = (url: string) => {
  window.open(url, '_blank', 'noopener,noreferrer')
}

const goToPanne = (slug: string) => {
  router.push(`/depannage/${slug}`)
}

const goToRepair = () => {
  router.push(`/depannage/${routeType.value}/repair`)
}

const goBack = () => {
  router.push(`/depannage/${routeType.value}`)
}

onMounted(async () => {
  if (routeType.value) {
    loading.value = true
    fullGuide.value = await fetchFullGuide(routeType.value)
    loading.value = false
  }
})
</script>

<style scoped>
.full-guide-view {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  min-height: 100vh;
}

.guide-header {
  margin-bottom: 2rem;
}

.back-btn {
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  cursor: pointer;
  margin-bottom: 1rem;
  transition: background 0.2s;
}

.back-btn:hover {
  background: #e5e7eb;
}

.header-title {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-icon {
  font-size: 2.5rem;
}

.header-title h1 {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1f2937;
  margin: 0;
}

.header-title p {
  color: #6b7280;
  margin: 0.25rem 0 0 0;
}

/* Warnings */
.warnings-section {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.warnings-section h3 {
  color: #991b1b;
  margin: 0 0 1rem 0;
  font-size: 1rem;
}

.warning-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.warning-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #7f1d1d;
  font-weight: 500;
}

/* Tips */
.tips-section {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.tips-section h3 {
  color: #166534;
  margin: 0 0 1rem 0;
  font-size: 1rem;
}

.tips-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.tip-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #166534;
}

/* Resources */
.resources-section {
  margin-bottom: 2rem;
}

.resources-section > h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.resource-group {
  margin-bottom: 2rem;
}

.resource-group h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e5e7eb;
}

/* Resource cards */
.resource-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.resource-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
}

.resource-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.1);
}

.resource-thumbnail {
  height: 160px;
  background: #1f2937;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.play-icon {
  position: absolute;
  font-size: 3rem;
  z-index: 2;
}

.resource-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.7;
}

.placeholder-thumb {
  font-size: 3rem;
}

.resource-info {
  padding: 1rem;
}

.resource-info h5 {
  margin: 0 0 0.5rem 0;
  font-size: 0.95rem;
  color: #1f2937;
}

.resource-info p {
  margin: 0 0 0.75rem 0;
  font-size: 0.85rem;
  color: #6b7280;
  line-height: 1.5;
}

.resource-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.8rem;
  color: #9ca3af;
}

/* Resource rows */
.resource-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.resource-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
}

.resource-row:hover {
  background: #f9fafb;
  border-color: #3b82f6;
}

.row-icon {
  font-size: 1.5rem;
}

.row-info {
  flex: 1;
}

.row-info h5 {
  margin: 0 0 0.25rem 0;
  font-size: 0.95rem;
  color: #1f2937;
}

.row-info p {
  margin: 0;
  font-size: 0.85rem;
  color: #6b7280;
}

.row-arrow {
  color: #9ca3af;
  font-size: 1.25rem;
}

/* Shop cards */
.shop-card {
  text-align: center;
}

.shop-icon {
  font-size: 2.5rem;
  padding: 1.5rem;
  background: #fef3c7;
}

.shop-author {
  font-size: 0.8rem;
  color: #f59e0b;
  font-weight: 600;
}

/* Tools grid */
.tools-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 0.75rem;
}

.tool-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1.25rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
}

.tool-card:hover {
  background: #eff6ff;
  border-color: #3b82f6;
}

.tool-icon {
  font-size: 2rem;
}

.tool-name {
  font-size: 0.85rem;
  font-weight: 500;
  color: #374151;
  text-align: center;
}

/* Related */
.related-section {
  margin-bottom: 2rem;
}

.related-section h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 1rem;
}

.related-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.related-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1.25rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.related-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
}

.related-icon {
  font-size: 2rem;
}

.related-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.9rem;
}

.related-type {
  font-size: 0.75rem;
  padding: 0.2rem 0.6rem;
  border-radius: 4px;
  font-weight: 500;
}

.related-type.hardware {
  background: #fef2f2;
  color: #dc2626;
}

.related-type.software {
  background: #eff6ff;
  color: #2563eb;
}

/* CTA */
.cta-section {
  background: linear-gradient(135deg, #1f2937, #374151);
  border-radius: 16px;
  padding: 2.5rem;
  text-align: center;
  color: white;
}

.cta-section h3 {
  font-size: 1.5rem;
  margin: 0 0 0.5rem 0;
}

.cta-section p {
  opacity: 0.8;
  margin-bottom: 1.5rem;
}

.cta-btn {
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #ef4444, #f97316);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cta-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 24px -4px rgba(239, 68, 68, 0.4);
}

/* Loading */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
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

@media (max-width: 768px) {
  .full-guide-view {
    padding: 1rem;
  }

  .header-title {
    flex-direction: column;
    text-align: center;
  }

  .resource-grid {
    grid-template-columns: 1fr;
  }

  .related-grid {
    grid-template-columns: 1fr;
  }
}
</style>