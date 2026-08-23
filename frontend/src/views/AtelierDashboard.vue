<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">🏭 Tableau de bord Atelier</h1>
      <router-link
        to="/diagnostic"
        class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 dark:bg-primary-500 text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-700 dark:hover:bg-primary-600 transition-colors"
      >
        + Nouveau Diagnostic
      </router-link>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 transition-colors">
        <div class="flex items-center">
          <div class="w-12 h-12 rounded-lg bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-2xl mr-4">
            🔍
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Diagnostics aujourd'hui</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.diagnosticsToday }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 transition-colors">
        <div class="flex items-center">
          <div class="w-12 h-12 rounded-lg bg-success-100 dark:bg-green-900 flex items-center justify-center text-2xl mr-4">
            🔧
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Réparations en cours</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.activeRepairs }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 transition-colors">
        <div class="flex items-center">
          <div class="w-12 h-12 rounded-lg bg-warning-100 dark:bg-yellow-900 flex items-center justify-center text-2xl mr-4">
            ⏱️
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Temps moyen</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.avgTime }}min</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 transition-colors">
        <div class="flex items-center">
          <div class="w-12 h-12 rounded-lg bg-danger-100 dark:bg-red-900 flex items-center justify-center text-2xl mr-4">
            ⚠️
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Incidents critiques</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.criticalIncidents }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Accès rapide -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 transition-colors">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">🚀 Accès rapide</h2>
        <div class="grid grid-cols-2 gap-4">
          <router-link to="/diagnostic" class="p-4 rounded-lg bg-primary-50 dark:bg-primary-900/30 hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors text-center">
            <div class="text-3xl mb-2">🔍</div>
            <div class="font-medium text-primary-700 dark:text-primary-300">Diagnostic</div>
          </router-link>
          <router-link to="/composants" class="p-4 rounded-lg bg-success-50 dark:bg-green-900/30 hover:bg-success-100 dark:hover:bg-green-900/50 transition-colors text-center">
            <div class="text-3xl mb-2">🔧</div>
            <div class="font-medium text-success-700 dark:text-green-300">Composants</div>
          </router-link>
          <router-link to="/evolution" class="p-4 rounded-lg bg-warning-50 dark:bg-yellow-900/30 hover:bg-warning-100 dark:hover:bg-yellow-900/50 transition-colors text-center">
            <div class="text-3xl mb-2">📈</div>
            <div class="font-medium text-warning-700 dark:text-yellow-300">Évolution</div>
          </router-link>
          <div class="p-4 rounded-lg bg-gray-50 dark:bg-slate-700 hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors text-center cursor-pointer" @click="showTools = true">
            <div class="text-3xl mb-2">🧰</div>
            <div class="font-medium text-gray-700 dark:text-gray-300">Outils</div>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 transition-colors">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">📋 Derniers diagnostics</h2>
        <div v-if="recentDiagnostics.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
          Aucun diagnostic récent.
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="diag in recentDiagnostics"
            :key="diag.id"
            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-700 rounded-lg"
          >
            <div>
              <p class="font-medium text-gray-900 dark:text-white">{{ diag.device }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ diag.date }}</p>
            </div>
            <SeverityBadge :severity="diag.severity" />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Outils -->
    <div v-if="showTools" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-slate-800 rounded-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-6 transition-colors">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold dark:text-white">🧰 Boîte à Outils</h2>
          <button @click="showTools = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl">&times;</button>
        </div>
        <ToolBox />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import SeverityBadge from '@/components/diagnostic/SeverityBadge.vue'
import ToolBox from '@/components/software/ToolBox.vue'

const showTools = ref(false)

const stats = ref({
  diagnosticsToday: 0,
  activeRepairs: 0,
  avgTime: 0,
  criticalIncidents: 0,
})

const recentDiagnostics = ref([
  // Données de démo - remplacer par appel API
])

onMounted(async () => {
  stats.value = {
    diagnosticsToday: 12,
    activeRepairs: 5,
    avgTime: 45,
    criticalIncidents: 1,
  }
})
</script>