<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4">📱 Vérificateur IMEI</h3>
    
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Numéro IMEI (15 chiffres)</label>
        <input 
          v-model="imei"
          type="text"
          maxlength="15"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono"
          placeholder="123456789012345"
        />
      </div>

      <button 
        @click="validateIMEI"
        class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        :disabled="imei.length !== 15"
      >
        Vérifier
      </button>

      <div v-if="result" class="p-4 rounded-lg" :class="result.valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
        <p class="font-bold">{{ result.valid ? '✅ IMEI valide' : '❌ IMEI invalide' }}</p>
        <p class="text-sm mt-1" v-if="result.valid">Checksum Luhn correct</p>
        <p class="text-sm mt-1" v-else>Le checksum ne correspond pas</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const imei = ref('');
const result = ref<{ valid: boolean } | null>(null);

function validateIMEI() {
  if (imei.value.length !== 15 || !/^\d+$/.test(imei.value)) {
    result.value = { valid: false };
    return;
  }
  
  // Algorithme de Luhn
  let sum = 0;
  for (let i = 0; i < 14; i++) {
    let digit = parseInt(imei.value[i]);
    if (i % 2 === 1) {
      digit *= 2;
      if (digit > 9) digit -= 9;
    }
    sum += digit;
  }
  
  const checkDigit = (10 - (sum % 10)) % 10;
  result.value = { valid: checkDigit === parseInt(imei.value[14]) };
}
</script>