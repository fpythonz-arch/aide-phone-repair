<template>
  <div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">🔐 Codes Secrets</h2>
    
    <div class="flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Rechercher un code ou une fonction..."
          class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-colors"
        />
      </div>
      <select
        v-model="selectedBrand"
        class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white transition-colors"
      >
        <option value="">Toutes les marques</option>
        <option value="samsung">Samsung</option>
        <option value="xiaomi">Xiaomi</option>
        <option value="huawei">Huawei</option>
        <option value="oppo">OPPO</option>
        <option value="vivo">Vivo</option>
        <option value="oneplus">OnePlus</option>
        <option value="google">Google Pixel</option>
      </select>
    </div>

    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto"></div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="code in filteredCodes"
        :key="code.id"
        class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6 hover:shadow-md transition-shadow transition-colors"
      >
        <div class="flex items-start justify-between mb-3">
          <div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-300">
              {{ code.device_type }}
            </span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900/50 text-primary-800 dark:text-primary-300 ml-2">
              {{ code.category }}
            </span>
          </div>
          <button
            class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
            @click="copyCode(code.code)"
            title="Copier"
          >
            📋
          </button>
        </div>
        
        <div class="bg-gray-900 rounded-lg p-3 mb-3">
          <code class="text-green-400 font-mono text-lg">{{ code.code }}</code>
        </div>
        
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ code.description }}</p>
      </div>
    </div>

    <div v-if="!loading && filteredCodes.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
      Aucun code trouvé.
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useSecretCodes } from '@/composables/useSecretCodes'
import type { SecretCode } from '@/types'

const { codes, loading, fetchCodes } = useSecretCodes()

const searchQuery = ref('')
const selectedBrand = ref('')

const filteredCodes = computed(() => {
  return codes.value.filter((code: SecretCode) => {
    const matchesSearch = !searchQuery.value ||
      code.code.includes(searchQuery.value) ||
      code.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesBrand = !selectedBrand.value || code.device_type.toLowerCase() === selectedBrand.value
    return matchesSearch && matchesBrand
  })
})

function copyCode(code: string) {
  navigator.clipboard.writeText(code)
  alert(`Code ${code} copié dans le presse-papiers !`)
}

onMounted(() => {
  fetchCodes()
})
</script>