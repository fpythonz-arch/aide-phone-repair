<template>
  <div class="codes-secrets-view">
    <header class="page-header">
      <h1>🔐 Codes Secrets</h1>
      <p>Accédez aux codes secrets par modèle de téléphone</p>
    </header>

    <div class="content-grid">
      <!-- Sélecteur de marque -->
      <aside class="brand-sidebar">
        <h3>Marques</h3>
        <div class="brand-list">
          <button
            v-for="brand in brands"
            :key="brand"
            :class="['brand-btn', { active: selectedBrand === brand }]"
            @click="selectBrand(brand)"
          >
            {{ brand }}
          </button>
        </div>
      </aside>

      <!-- Liste des codes -->
      <main class="codes-main">
        <div v-if="loading" class="loading">Chargement...</div>
        
        <div v-else-if="filteredCodes.length === 0" class="empty">
          Sélectionnez une marque pour voir les codes secrets
        </div>

        <div v-else class="codes-grid">
          <SecretCodeCard
            v-for="code in filteredCodes"
            :key="code.id"
            :code="code"
          />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import SecretCodeCard from '@/components/codes-secrets/SecretCodeCard.vue'
import { useSecretCodes } from '@/composables/useSecretCodes'

const { codes, loading, fetchCodes } = useSecretCodes()

const brands = ['Samsung', 'Apple', 'Xiaomi', 'Huawei', 'Google', 'OnePlus', 'Sony', 'LG']
const selectedBrand = ref<string>('')

const filteredCodes = computed(() => {
  if (!selectedBrand.value) return []
  return codes.value.filter(c => c.brand === selectedBrand.value)
})

const selectBrand = (brand: string) => {
  selectedBrand.value = brand
}

onMounted(() => {
  fetchCodes()
})
</script>

<style scoped>
.codes-secrets-view {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #6b7280;
}

.content-grid {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 2rem;
}

.brand-sidebar {
  background: #f9fafb;
  border-radius: 12px;
  padding: 1.5rem;
  height: fit-content;
}

.brand-sidebar h3 {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #374151;
}

.brand-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.brand-btn {
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
  font-weight: 500;
}

.brand-btn:hover {
  border-color: #3b82f6;
  color: #3b82f6;
}

.brand-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.codes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.loading, .empty {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
}
</style>