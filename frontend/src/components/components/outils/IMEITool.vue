<template>
  <div class="imei-tool">
    <h3>📋 Vérificateur IMEI</h3>
    
    <div class="input-group">
      <input
        v-model="imei"
        type="text"
        placeholder="Entrez l'IMEI (15 chiffres)"
        maxlength="15"
        @input="validateInput"
      />
      <button 
        class="verify-btn"
        :disabled="!isValid || loading"
        @click="verifyIMEI"
      >
        Vérifier
      </button>
    </div>

    <div v-if="error" class="error-msg">{{ error }}</div>

    <div v-if="result" class="imei-result">
      <div class="result-header">
        <span class="valid-badge" :class="{ valid: result.valid }">
          {{ result.valid ? '✅ IMEI Valide' : '❌ IMEI Invalide' }}
        </span>
      </div>

      <div v-if="result.valid" class="device-info">
        <div class="info-row">
          <span class="info-label">Marque</span>
          <span class="info-value">{{ result.brand || 'Inconnue' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Modèle</span>
          <span class="info-value">{{ result.model || 'Inconnu' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Type</span>
          <span class="info-value">{{ result.type || 'Non déterminé' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Pays d'origine</span>
          <span class="info-value">{{ result.country || 'Non déterminé' }}</span>
        </div>
      </div>

      <div v-if="result.blacklisted" class="warning-box">
        ⚠️ Cet IMEI est signalé comme volé ou blacklisté !
      </div>
    </div>

    <div class="quick-actions">
      <h4>Actions rapides</h4>
      <div class="actions-grid">
        <button class="action-btn" @click="generateRandom">
          🎲 Générer un IMEI test
        </button>
        <button class="action-btn" @click="scanBarcode">
          📷 Scanner un code-barres
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useTools } from '@/composables/useTools'

const { executeTool, loading } = useTools()

const imei = ref('')
const error = ref('')
const result = ref<any>(null)

const isValid = computed(() => {
  return /^\d{15}$/.test(imei.value) && luhnCheck(imei.value)
})

const validateInput = () => {
  imei.value = imei.value.replace(/\D/g, '').slice(0, 15)
  error.value = ''
  result.value = null
}

// Algorithme de Luhn pour vérifier la validité de l'IMEI
const luhnCheck = (imei: string): boolean => {
  let sum = 0
  let isEven = false
  for (let i = imei.length - 1; i >= 0; i--) {
    let digit = parseInt(imei[i], 10)
    if (isEven) {
      digit *= 2
      if (digit > 9) digit -= 9
    }
    sum += digit
    isEven = !isEven
  }
  return sum % 10 === 0
}

const verifyIMEI = async () => {
  if (!isValid.value) {
    error.value = 'IMEI invalide. Vérifiez les 15 chiffres.'
    return
  }
  
  try {
    const res = await executeTool('imei-checker', { imei: imei.value })
    result.value = res.data
  } catch (err) {
    // Error handled by composable
  }
}

const generateRandom = () => {
  // Génère un IMEI valide pour les tests
  let prefix = '35' + Math.floor(Math.random() * 1000000000).toString().padStart(9, '0')
  let checkDigit = 0
  let sum = 0
  let isEven = false
  for (let i = prefix.length - 1; i >= 0; i--) {
    let digit = parseInt(prefix[i], 10)
    if (isEven) {
      digit *= 2
      if (digit > 9) digit -= 9
    }
    sum += digit
    isEven = !isEven
  }
  checkDigit = (10 - (sum % 10)) % 10
  imei.value = prefix + checkDigit
  validateInput()
}

const scanBarcode = () => {
  alert('Fonctionnalité de scan : utilise l\'API MediaDevices ou un composant dédié.')
}
</script>

<style scoped>
.imei-tool {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  border: 1px solid #e5e7eb;
}

.imei-tool h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.input-group {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.input-group input {
  flex: 1;
  padding: 0.875rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1.1rem;
  font-family: 'Courier New', monospace;
  letter-spacing: 2px;
  transition: border-color 0.2s;
}

.input-group input:focus {
  outline: none;
  border-color: #3b82f6;
}

.verify-btn {
  padding: 0.875rem 1.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  white-space: nowrap;
}

.verify-btn:hover:not(:disabled) {
  background: #2563eb;
}

.verify-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-msg {
  color: #ef4444;
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.imei-result {
  margin-top: 1.5rem;
  padding: 1.25rem;
  border-radius: 12px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
}

.result-header {
  margin-bottom: 1rem;
}

.valid-badge {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.9rem;
}

.valid-badge.valid {
  background: #dcfce7;
  color: #166534;
}

.valid-badge:not(.valid) {
  background: #fee2e2;
  color: #991b1b;
}

.device-info {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  color: #6b7280;
  font-size: 0.9rem;
}

.info-value {
  font-weight: 600;
  color: #1f2937;
}

.warning-box {
  margin-top: 1rem;
  padding: 1rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  color: #991b1b;
  font-weight: 600;
  text-align: center;
}

.quick-actions {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.quick-actions h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.action-btn {
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  font-size: 0.85rem;
  transition: all 0.2s;
}

.action-btn:hover {
  border-color: #3b82f6;
  background: #eff6ff;
}
</style>