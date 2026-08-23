<template>
  <div class="tool-calculator">
    <h3>💰 Calculateur de Devis Réparation</h3>
    
    <div class="form-group">
      <label>Type de réparation</label>
      <select v-model="form.repairType">
        <option value="">Sélectionner...</option>
        <option value="screen">Écran / Vitre</option>
        <option value="battery">Batterie</option>
        <option value="charging">Connecteur de charge</option>
        <option value="camera">Caméra</option>
        <option value="speaker">Haut-parleur</option>
        <option value="water">Dégât des eaux</option>
      </select>
    </div>

    <div class="form-group">
      <label>Marque du téléphone</label>
      <select v-model="form.brand">
        <option value="">Sélectionner...</option>
        <option v-for="brand in brands" :key="brand" :value="brand">
          {{ brand }}
        </option>
      </select>
    </div>

    <div class="form-group">
      <label>Gamme</label>
      <div class="range-selector">
        <button 
          v-for="range in ranges" 
          :key="range.value"
          :class="['range-btn', { active: form.range === range.value }]"
          @click="form.range = range.value"
        >
          {{ range.label }}
        </button>
      </div>
    </div>

    <div class="form-group">
      <label>Options supplémentaires</label>
      <div class="checkbox-group">
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.options.genuine" />
          <span>Pièce d'origine (+30%)</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.options.warranty" />
          <span>Garantie 1 an (+15€)</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.options.express" />
          <span>Réparation express 24h (+20€)</span>
        </label>
      </div>
    </div>

    <button 
      class="calculate-btn"
      :disabled="!canCalculate || loading"
      @click="calculate"
    >
      <span v-if="loading">Calcul...</span>
      <span v-else>Calculer le devis</span>
    </button>

    <div v-if="result" class="result-box" :class="{ success: result.success }">
      <div class="result-header">
        <span class="result-price">{{ result.price }}€</span>
        <span class="result-label">Estimation</span>
      </div>
      <div class="result-details">
        <div class="detail-row">
          <span>Pièce</span>
          <span>{{ result.partsCost }}€</span>
        </div>
        <div class="detail-row">
          <span>Main d'œuvre</span>
          <span>{{ result.laborCost }}€</span>
        </div>
        <div class="detail-row" v-if="result.optionsCost > 0">
          <span>Options</span>
          <span>{{ result.optionsCost }}€</span>
        </div>
        <div class="detail-row total">
          <span>Total estimé</span>
          <span>{{ result.price }}€</span>
        </div>
      </div>
      <p class="result-note">* Prix indicatif, sujet à validation en atelier</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useTools } from '@/composables/useTools'

const { executeTool, loading } = useTools()

const brands = ['Apple', 'Samsung', 'Xiaomi', 'Google', 'OnePlus', 'Huawei', 'Sony']
const ranges = [
  { value: 'entry', label: 'Entrée de gamme' },
  { value: 'mid', label: 'Milieu de gamme' },
  { value: 'premium', label: 'Premium' },
  { value: 'flagship', label: 'Flagship' },
]

const form = ref({
  repairType: '',
  brand: '',
  range: 'mid',
  options: {
    genuine: false,
    warranty: false,
    express: false,
  },
})

const result = ref<any>(null)

const canCalculate = computed(() => {
  return form.value.repairType && form.value.brand
})

const calculate = async () => {
  try {
    const res = await executeTool('repair-calculator', {
      repair_type: form.value.repairType,
      brand: form.value.brand,
      range: form.value.range,
      options: form.value.options,
    })
    result.value = res.data
  } catch (err) {
    // Error handled by composable
  }
}
</script>

<style scoped>
.tool-calculator {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  border: 1px solid #e5e7eb;
}

.tool-calculator h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
  background: white;
  cursor: pointer;
}

.range-selector {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.range-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  font-size: 0.85rem;
  transition: all 0.2s;
}

.range-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.9rem;
  color: #4b5563;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  accent-color: #3b82f6;
}

.calculate-btn {
  width: 100%;
  padding: 0.875rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  margin-top: 0.5rem;
}

.calculate-btn:hover:not(:disabled) {
  background: #2563eb;
}

.calculate-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.result-box {
  margin-top: 1.5rem;
  padding: 1.5rem;
  border-radius: 12px;
  background: #f9fafb;
  border: 2px solid #e5e7eb;
}

.result-box.success {
  border-color: #22c55e;
  background: #f0fdf4;
}

.result-header {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.result-price {
  font-size: 2.5rem;
  font-weight: 800;
  color: #1f2937;
}

.result-label {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.result-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  color: #4b5563;
}

.detail-row.total {
  margin-top: 0.5rem;
  padding-top: 0.5rem;
  border-top: 1px solid #e5e7eb;
  font-weight: 700;
  color: #1f2937;
}

.result-note {
  margin-top: 1rem;
  font-size: 0.75rem;
  color: #9ca3af;
  font-style: italic;
}
</style>