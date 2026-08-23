<template>
  <span class="severity-badge" :class="badgeClass">
    <span v-if="showIcon" class="badge-icon">{{ icon }}</span>
    {{ label }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  severity: 'low' | 'medium' | 'high' | 'critical'
  showIcon?: boolean
}>()

const badgeClass = computed(() => {
  const classes: Record<string, string> = {
    low: 'severity--low',
    medium: 'severity--medium',
    high: 'severity--high',
    critical: 'severity--critical',
  }
  return classes[props.severity] || 'severity--default'
})

const label = computed(() => {
  const labels: Record<string, string> = {
    low: 'Faible',
    medium: 'Moyen',
    high: 'Élevé',
    critical: 'Critique',
  }
  return labels[props.severity] || props.severity
})

const icon = computed(() => {
  const icons: Record<string, string> = {
    low: '●',
    medium: '●',
    high: '▲',
    critical: '◆',
  }
  return icons[props.severity] || '●'
})
</script>

<style scoped>
.severity-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.625rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
  border: 1px solid;
  white-space: nowrap;
  transition: all 0.2s;
}

.badge-icon {
  font-size: 0.6rem;
  line-height: 1;
}

/* ── Faible ────────────────────────────────────────── */
.severity--low {
  background: linear-gradient(135deg, #dcfce7, #d1fae5);
  color: #15803d;
  border-color: #86efac;
}

/* ── Moyen ───────────────────────────────────────── */
.severity--medium {
  background: linear-gradient(135deg, #fef9c3, #fef3c7);
  color: #a16207;
  border-color: #fde047;
}

/* ── Élevé ─────────────────────────────────────────── */
.severity--high {
  background: linear-gradient(135deg, #ffedd5, #fed7aa);
  color: #c2410c;
  border-color: #fdba74;
}

/* ── Critique ────────────────────────────────────── */
.severity--critical {
  background: linear-gradient(135deg, #fee2e2, #fecaca);
  color: #b91c1c;
  border-color: #fca5a5;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.2);
  }
  50% {
    box-shadow: 0 0 0 4px rgba(220, 38, 38, 0);
  }
}

/* ── Défaut ────────────────────────────────────────── */
.severity--default {
  background: #f3f4f6;
  color: #374151;
  border-color: #d1d5db;
}
</style>