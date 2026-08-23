<template>
  <div class="secret-code-card">
    <div class="card-header">
      <span class="code-badge">{{ code.code }}</span>
      <span class="function-tag">{{ code.function }}</span>
    </div>
    <p class="description">{{ code.description }}</p>
    <div class="card-footer">
      <span class="model-badge">{{ code.model || 'Tous modèles' }}</span>
      <button class="copy-btn" @click="copyCode">
        {{ copied ? '✓ Copié' : '📋 Copier' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  code: {
    id: number
    code: string
    function: string
    description: string
    brand: string
    model?: string
  }
}

defineProps<Props>()

const copied = ref(false)

const copyCode = () => {
  navigator.clipboard.writeText(props.code.code)
  copied.value = true
  setTimeout(() => copied.value = false, 2000)
}
</script>

<style scoped>
.secret-code-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.25rem;
  transition: box-shadow 0.2s;
}

.secret-code-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}

.code-badge {
  font-family: 'Courier New', monospace;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
  background: #f3f4f6;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
}

.function-tag {
  font-size: 0.75rem;
  color: #6b7280;
  background: #fef3c7;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.description {
  color: #4b5563;
  font-size: 0.9rem;
  line-height: 1.5;
  margin-bottom: 1rem;
}

.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.model-badge {
  font-size: 0.75rem;
  color: #6b7280;
}

.copy-btn {
  padding: 0.4rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  cursor: pointer;
  font-size: 0.8rem;
  transition: all 0.2s;
}

.copy-btn:hover {
  background: #f3f4f6;
}
</style>