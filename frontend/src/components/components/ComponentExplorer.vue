<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Rechercher un composant..."
          class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-colors"
        />
      </div>
      <select
        v-model="selectedCategory"
        class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-colors"
      >
        <option value="">Toutes les catégories</option>
        <option value="screen">Écran</option>
        <option value="battery">Batterie</option>
        <option value="charging">Charge</option>
        <option value="audio">Audio</option>
        <option value="network">Réseau</option>
        <option value="camera">Caméra</option>
      </select>
    </div>

    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto"></div>
      <p class="mt-4 text-gray-500 dark:text-gray-400">Chargement des composants...</p>
    </div>

    <div v-else-if="error" class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-red-200 dark:border-red-800 p-6">
      <p class="text-red-700 dark:text-red-400">{{ error }}</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <ComponentDetail
        v-for="component in filteredComponents"
        :key="component.id"
        :component="component"
        @select="selectComponent"
      />
    </div>

    <div v-if="!loading && filteredComponents.length === 0" class="text-center py-12">
      <p class="text-gray-500 dark:text-gray-400">Aucun composant trouvé.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useComponents } from '@/composables/useComponents'
import ComponentDetail from './ComponentDetail.vue'
import type { Component } from '@/types'

const { components, loading, error, fetchComponents } = useComponents()

const searchQuery = ref('')
const selectedCategory = ref('')

const filteredComponents = computed(() => {
  return components.value.filter((c: Component) => {
    const matchesSearch = !searchQuery.value || 
      c.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      c.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = !selectedCategory.value || c.type === selectedCategory.value
    return matchesSearch && matchesCategory
  })
})

function selectComponent(component: Component) {
  console.log('Composant sélectionné:', component)
}

onMounted(() => {
  fetchComponents()
})
</script>

<style scoped>
/* Styles spécifiques si nécessaire */
</style>