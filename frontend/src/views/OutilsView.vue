<template>
  <div class="outils-view">
    <header class="page-header">
      <h1>🧰 Boîte à Outils Pro</h1>
      <p>Utilitaires et calculateurs pour la réparation mobile</p>
    </header>

    <div class="tools-layout">
      <!-- Sidebar : liste des outils -->
      <aside class="tools-sidebar">
        <div class="category-section" v-for="category in categories" :key="category.id">
          <h3 class="category-title">{{ category.label }}</h3>
          <div class="tools-list">
            <ToolCard
              v-for="tool in toolsByCategory[category.id] || []"
              :key="tool.id"
              :tool="tool"
              :is-active="selectedTool?.id === tool.id"
              @select="selectTool"
            />
          </div>
        </div>
      </aside>

      <!-- Zone principale : outil actif -->
      <main class="tool-workspace">
        <div v-if="!selectedTool" class="empty-state">
          <div class="empty-icon">🧰</div>
          <h3>Sélectionnez un outil</h3>
          <p>Choisissez un utilitaire dans la liste pour commencer</p>
        </div>

        <component 
          v-else-if="toolComponent"
          :is="toolComponent" 
          :key="selectedTool.id"
        />
        
        <div v-else class="empty-state">
          <div class="empty-icon">⚠️</div>
          <h3>Outil non disponible</h3>
          <p>Le composant pour cet outil n'est pas encore implémenté</p>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, markRaw } from 'vue'
import ToolCard from '@/components/outils/ToolCard.vue'
import ToolCalculator from '@/components/outils/ToolCalculator.vue'
import BatteryHealthTool from '@/components/outils/BatteryHealthTool.vue'
import ScreenTestTool from '@/components/outils/ScreenTestTool.vue'
import IMEITool from '@/components/outils/IMEITool.vue'
import { useTools, type Tool } from '@/composables/useTools'

const { tools, fetchTools } = useTools()

const selectedTool = ref<Tool | null>(null)

const categories = [
  { id: 'calculator', label: '💰 Calculateurs' },
  { id: 'tester', label: '🔍 Testeurs' },
  { id: 'checker', label: '✅ Vérificateurs' },
  { id: 'guide', label: '📖 Guides' },
]

// Computed pour regrouper les outils par catégorie (remplace getToolsByCategory)
const toolsByCategory = computed(() => {
  const groups: Record<string, Tool[]> = {}
  tools.value.forEach(tool => {
    if (!groups[tool.category]) groups[tool.category] = []
    groups[tool.category].push(tool)
  })
  return groups
})

// Mapping slug → composant
const toolComponents: Record<string, any> = {
  'repair-calculator': markRaw(ToolCalculator),
  'battery-health': markRaw(BatteryHealthTool),
  'screen-test': markRaw(ScreenTestTool),
  'imei-checker': markRaw(IMEITool),
}

const toolComponent = computed(() => {
  if (!selectedTool.value) return null
  return toolComponents[selectedTool.value.slug] || null
})

const selectTool = (tool: Tool) => {
  selectedTool.value = tool
}

onMounted(() => {
  fetchTools()
  // Fallback : si le backend n'a pas encore d'outils, on injecte des outils par défaut
  if (tools.value.length === 0) {
    tools.value = [
      {
        id: 1,
        name: 'Calculateur de Devis',
        slug: 'repair-calculator',
        description: 'Estimez le coût d\'une réparation selon le modèle et la panne',
        category: 'calculator',
        icon: '💰',
        is_active: true,
      },
      {
        id: 2,
        name: 'Diagnostic Batterie',
        slug: 'battery-health',
        description: 'Évaluez l\'état de santé de la batterie',
        category: 'tester',
        icon: '🔋',
        is_active: true,
      },
      {
        id: 3,
        name: 'Testeur d\'Écran',
        slug: 'screen-test',
        description: 'Vérifiez les pixels morts et la réactivité tactile',
        category: 'tester',
        icon: '📱',
        is_active: true,
      },
      {
        id: 4,
        name: 'Vérificateur IMEI',
        slug: 'imei-checker',
        description: 'Vérifiez la validité et l\'authenticité d\'un IMEI',
        category: 'checker',
        icon: '📋',
        is_active: true,
      },
    ]
  }
})
</script>

<style scoped>
.outils-view {
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

.tools-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 2rem;
  align-items: start;
}

.tools-sidebar {
  background: #f9fafb;
  border-radius: 16px;
  padding: 1.5rem;
  position: sticky;
  top: 2rem;
  max-height: calc(100vh - 4rem);
  overflow-y: auto;
}

.category-section {
  margin-bottom: 1.5rem;
}

.category-section:last-child {
  margin-bottom: 0;
}

.category-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.75rem;
}

.tools-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.tool-workspace {
  min-height: 600px;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 500px;
  color: #9ca3af;
  text-align: center;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.empty-state p {
  font-size: 0.95rem;
}

.tools-sidebar::-webkit-scrollbar {
  width: 6px;
}

.tools-sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.tools-sidebar::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

@media (max-width: 1024px) {
  .tools-layout {
    grid-template-columns: 1fr;
  }

  .tools-sidebar {
    position: static;
    max-height: none;
  }
}
</style>