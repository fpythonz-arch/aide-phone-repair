<template>
  <div class="component-detail">
    <button class="back-btn" @click="$emit('back')">
      ← Retour à la liste
    </button>

    <div class="detail-header">
      <div class="detail-icon">{{ getIcon(component.type) }}</div>
      <div class="detail-title">
        <h2>{{ component.name }}</h2>
        <span :class="['type-badge', component.type]">{{ typeLabel }}</span>
      </div>
    </div>

    <div class="detail-grid">
      <!-- Colonne info -->
      <div class="detail-main">
        <div class="info-card">
          <h4>📋 Description</h4>
          <p>{{ component.description }}</p>
        </div>

        <div class="info-card">
          <h4>📍 Localisation</h4>
          <p>{{ component.position }}</p>
          <div class="position-visual">
            <!-- Schéma simplifié du téléphone -->
            <div class="phone-outline">
              <div 
                class="component-position-marker"
                :style="getPositionStyle(component.position)"
              >
                📍
              </div>
            </div>
          </div>
        </div>

        <div class="info-card">
          <h4>⏱️ Temps estimé</h4>
          <div class="time-display">
            <span class="time-value">{{ component.estimatedTime }}</span>
            <span class="time-unit">minutes</span>
          </div>
          <div class="difficulty-bar">
            <div 
              class="difficulty-fill"
              :class="component.difficulty"
              :style="{ width: difficultyPercent + '%' }"
            ></div>
          </div>
          <span class="difficulty-text">
            Difficulté : {{ difficultyLabel(component.difficulty) }}
          </span>
        </div>

        <div v-if="component.tools?.length" class="info-card">
          <h4>🔧 Outils nécessaires</h4>
          <div class="tools-list">
            <span v-for="tool in component.tools" :key="tool" class="tool-tag">
              {{ tool }}
            </span>
          </div>
        </div>
      </div>

      <!-- Colonne symptômes & prix -->
      <div class="detail-side">
        <div v-if="component.averagePrice" class="price-card">
          <span class="price-label">Prix moyen de la pièce</span>
          <span class="price-value">{{ component.averagePrice }}€</span>
          <span class="price-note">* Prix indicatif</span>
        </div>

        <div class="symptoms-card">
          <h4>⚠️ Symptômes associés</h4>
          <div class="symptoms-list">
            <div 
              v-for="symptom in component.symptoms" 
              :key="symptom.id"
              class="symptom-item"
              :class="`severity-${symptom.severity}`"
            >
              <div class="symptom-header">
                <span class="symptom-name">{{ symptom.name }}</span>
                <span :class="['severity-badge', symptom.severity]">
                  {{ severityLabel(symptom.severity) }}
                </span>
              </div>
              <p class="symptom-desc">{{ symptom.description }}</p>
            </div>
          </div>
        </div>

        <div class="actions-card">
          <h4>🚀 Actions</h4>
          <button class="action-btn primary" @click="startDiagnostic">
            🔍 Lancer un diagnostic
          </button>
          <button class="action-btn" @click="findGuide">
            📖 Voir le guide de remplacement
          </button>
          <button class="action-btn" @click="findParts">
            🛒 Trouver des pièces compatibles
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import type { Component } from '@/composables/useComponents'

interface Props {
  component: Component
}

const props = defineProps<Props>()
const emit = defineEmits<{
  back: []
}>()

const router = useRouter()

const typeLabel = computed(() => {
  const labels: Record<string, string> = {
    screen: 'Écran',
    battery: 'Batterie',
    camera: 'Caméra',
    motherboard: 'Carte mère',
    charging: 'Connecteur de charge',
    speaker: 'Haut-parleur',
    sensor: 'Capteur',
    button: 'Bouton',
    antenna: 'Antenne',
    other: 'Autre',
  }
  return labels[props.component.type] || props.component.type
})

const difficultyPercent = computed(() => {
  const percents: Record<string, number> = {
    easy: 25,
    medium: 50,
    hard: 75,
    expert: 100,
  }
  return percents[props.component.difficulty] || 50
})

const getIcon = (type: string) => {
  const icons: Record<string, string> = {
    screen: '📱',
    battery: '🔋',
    camera: '📷',
    motherboard: '🧠',
    charging: '🔌',
    speaker: '🔊',
    sensor: '📡',
    button: '🔘',
    antenna: '📶',
    other: '🔧',
  }
  return icons[type] || '🔧'
}

const difficultyLabel = (difficulty: string) => {
  const labels: Record<string, string> = {
    easy: 'Facile',
    medium: 'Moyen',
    hard: 'Difficile',
    expert: 'Expert',
  }
  return labels[difficulty] || difficulty
}

const severityLabel = (severity: string) => {
  const labels: Record<string, string> = {
    low: 'Faible',
    medium: 'Moyen',
    high: 'Élevé',
    critical: 'Critique',
  }
  return labels[severity] || severity
}

const getPositionStyle = (position: string) => {
  // Positions approximatives sur le schéma du téléphone
  const positions: Record<string, { top: string; left: string }> = {
    'avant': { top: '20%', left: '50%' },
    'arrière': { top: '20%', left: '50%' },
    'haut': { top: '5%', left: '50%' },
    'bas': { top: '90%', left: '50%' },
    'gauche': { top: '50%', left: '10%' },
    'droite': { top: '50%', left: '90%' },
    'intérieur': { top: '50%', left: '50%' },
  }
  const pos = positions[position.toLowerCase()] || { top: '50%', left: '50%' }
  return {
    top: pos.top,
    left: pos.left,
    transform: 'translate(-50%, -50%)',
  }
}

const startDiagnostic = () => {
  router.push({
    path: '/diagnostic',
    query: { component: props.component.slug }
  })
}

const findGuide = () => {
  // Rediriger vers ressources avec filtre
  router.push({
    path: '/ressources',
    query: { component: props.component.slug, type: 'guide' }
  })
}

const findParts = () => {
  // Rediriger vers outils ou une future page pièces
  alert('Fonctionnalité de recherche de pièces à implémenter')
}
</script>

<style scoped>
.component-detail {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  cursor: pointer;
  margin-bottom: 1.5rem;
  transition: background 0.2s;
}

.back-btn:hover {
  background: #e5e7eb;
}

.detail-header {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.detail-icon {
  font-size: 3.5rem;
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f9fafb;
  border-radius: 16px;
}

.detail-title h2 {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 0.5rem 0;
}

.type-badge {
  display: inline-block;
  padding: 0.3rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
}

.type-badge.screen { background: #dbeafe; color: #1e40af; }
.type-badge.battery { background: #dcfce7; color: #166534; }
.type-badge.camera { background: #fef3c7; color: #92400e; }
.type-badge.motherboard { background: #f3e8ff; color: #6b21a8; }
.type-badge.charging { background: #fee2e2; color: #991b1b; }
.type-badge.speaker { background: #e0e7ff; color: #3730a3; }
.type-badge.sensor { background: #cffafe; color: #0e7490; }
.type-badge.button { background: #fce7f3; color: #9d174d; }
.type-badge.antenna { background: #ecfdf5; color: #065f46; }
.type-badge.other { background: #f3f4f6; color: #374151; }

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 1.5rem;
}

.detail-main {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.info-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.25rem;
}

.info-card h4 {
  font-size: 0.9rem;
  font-weight: 700;
  color: #374151;
  margin: 0 0 0.75rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.info-card p {
  color: #4b5563;
  line-height: 1.6;
  margin: 0;
}

.position-visual {
  margin-top: 1rem;
  display: flex;
  justify-content: center;
}

.phone-outline {
  width: 120px;
  height: 220px;
  border: 3px solid #374151;
  border-radius: 16px;
  position: relative;
  background: #f9fafb;
}

.component-position-marker {
  position: absolute;
  font-size: 1.5rem;
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 100% { transform: translate(-50%, -50%) translateY(0); }
  50% { transform: translate(-50%, -50%) translateY(-5px); }
}

.time-display {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.time-value {
  font-size: 2.5rem;
  font-weight: 800;
  color: #1f2937;
}

.time-unit {
  color: #6b7280;
  font-size: 1rem;
}

.difficulty-bar {
  width: 100%;
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.difficulty-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s ease;
}

.difficulty-fill.easy { background: #22c55e; }
.difficulty-fill.medium { background: #eab308; }
.difficulty-fill.hard { background: #f97316; }
.difficulty-fill.expert { background: #ef4444; }

.difficulty-text {
  font-size: 0.85rem;
  color: #6b7280;
}

.tools-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tool-tag {
  padding: 0.4rem 0.75rem;
  background: #f3f4f6;
  border-radius: 6px;
  font-size: 0.85rem;
  color: #4b5563;
}

.detail-side {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.price-card {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
}

.price-label {
  display: block;
  font-size: 0.85rem;
  opacity: 0.9;
  margin-bottom: 0.5rem;
}

.price-value {
  display: block;
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 0.25rem;
}

.price-note {
  display: block;
  font-size: 0.75rem;
  opacity: 0.8;
}

.symptoms-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.25rem;
}

.symptoms-card h4 {
  font-size: 0.9rem;
  font-weight: 700;
  color: #374151;
  margin: 0 0 1rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.symptoms-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.symptom-item {
  padding: 0.875rem;
  border-radius: 8px;
  border-left: 4px solid;
}

.symptom-item.severity-low { background: #f0fdf4; border-left-color: #22c55e; }
.symptom-item.severity-medium { background: #fefce8; border-left-color: #eab308; }
.symptom-item.severity-high { background: #fef2f2; border-left-color: #ef4444; }
.symptom-item.severity-critical { background: #fef2f2; border-left-color: #991b1b; }

.symptom-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.25rem;
}

.symptom-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.9rem;
}

.severity-badge {
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
}

.severity-badge.low { background: #dcfce7; color: #166534; }
.severity-badge.medium { background: #fef3c7; color: #92400e; }
.severity-badge.high { background: #fee2e2; color: #991b1b; }
.severity-badge.critical { background: #fecaca; color: #7f1d1d; }

.symptom-desc {
  font-size: 0.8rem;
  color: #6b7280;
  margin: 0;
  line-height: 1.4;
}

.actions-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.25rem;
}

.actions-card h4 {
  font-size: 0.9rem;
  font-weight: 700;
  color: #374151;
  margin: 0 0 1rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.action-btn {
  width: 100%;
  padding: 0.875rem;
  margin-bottom: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn:last-child {
  margin-bottom: 0;
}

.action-btn:hover {
  border-color: #3b82f6;
  color: #3b82f6;
  background: #eff6ff;
}

.action-btn.primary {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.action-btn.primary:hover {
  background: #2563eb;
  border-color: #2563eb;
}

@media (max-width: 1024px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>