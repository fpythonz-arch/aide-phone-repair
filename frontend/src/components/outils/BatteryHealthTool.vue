<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4">🔋 Santé de la batterie</h3>
    
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Capacité actuelle (mAh)</label>
        <input 
          v-model.number="currentCapacity" 
          type="number" 
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          placeholder="Ex: 3200"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Capacité nominale (mAh)</label>
        <input 
          v-model.number="nominalCapacity" 
          type="number" 
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          placeholder="Ex: 4000"
        />
      </div>

      <button 
        @click="calculateHealth"
        class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
      >
        Calculer
      </button>

      <div v-if="health !== null" class="mt-4 p-4 rounded-lg" :class="healthColor">
        <p class="text-center text-lg font-bold">
          Santé : {{ health }}%
        </p>
        <p class="text-center text-sm mt-1">{{ healthAdvice }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

const currentCapacity = ref<number | null>(null);
const nominalCapacity = ref<number | null>(null);
const health = ref<number | null>(null);

const healthColor = computed(() => {
  if (health.value === null) return '';
  if (health.value >= 80) return 'bg-green-100 text-green-800';
  if (health.value >= 60) return 'bg-yellow-100 text-yellow-800';
  return 'bg-red-100 text-red-800';
});

const healthAdvice = computed(() => {
  if (health.value === null) return '';
  if (health.value >= 80) return 'Bonne santé, pas besoin de remplacement';
  if (health.value >= 60) return 'Santé dégradée, envisagez un remplacement bientôt';
  return 'Remplacez la batterie immédiatement';
});

function calculateHealth() {
  if (!currentCapacity.value || !nominalCapacity.value || nominalCapacity.value === 0) return;
  health.value = Math.round((currentCapacity.value / nominalCapacity.value) * 100);
}
</script>