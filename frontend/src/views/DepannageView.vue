<template>
  <div class="depannage-view">
    <header class="depannage-header">
      <h1>🔧 Centre de Dépannage</h1>
      <p class="subtitle">Choisissez le type de dépannage pour votre appareil</p>
    </header>

    <div class="depannage-grid">
      <!-- HARDWARE -->
      <div class="depannage-card hardware" @click="goToHardware">
        <div class="card-icon">🔧</div>
        <h2>Dépannage Hardware</h2>
        <p class="card-desc">
          Problèmes matériels : écran cassé, batterie, charge, caméra, audio, boutons, réseau, chauffe, eau...
        </p>
        <div class="card-stats">
<!-- PAR ça (computed est déjà un ref, pas besoin de .value dans le template) : -->
         <span class="stat">📱 {{ hardwarePannes.length }} pannes</span>
          <span class="stat">⚡ Réparations physiques</span>
        </div>
        <div class="card-tags">
          <span class="tag">Écran</span>
          <span class="tag">Batterie</span>
          <span class="tag">Charge</span>
          <span class="tag">Caméra</span>
          <span class="tag">Audio</span>
          <span class="tag">+15 autres</span>
        </div>
        <button class="card-btn">Commencer →</button>
      </div>

      <!-- SOFTWARE -->
      <div class="depannage-card software" @click="goToSoftware">
        <div class="card-icon">💻</div>
        <h2>Dépannage Software</h2>
        <p class="card-desc">
          Problèmes logiciels : lenteur, plantages, virus, mise à jour, bootloop, stockage, comptes, réseau...
        </p>
        <div class="card-stats">
          <span class="stat">📱 {{ softwarePannes.length }} pannes</span>
          <span class="stat">🔄 Solutions logicielles</span>
        </div>
        <div class="card-tags">
          <span class="tag">Lenteur</span>
          <span class="tag">Virus</span>
          <span class="tag">Mise à jour</span>
          <span class="tag">Bootloop</span>
          <span class="tag">Stockage</span>
          <span class="tag">+10 autres</span>
        </div>
        <button class="card-btn">Commencer →</button>
      </div>
    </div>

    <!-- Aperçu rapide des pannes populaires -->
    <section class="popular-section">
      <h3>🔥 Pannes les plus fréquentes</h3>
      <div class="popular-grid">
        <!-- ET dans le v-for popular : -->
        <div
          v-for="panne in popularPannes"
          :key="panne.id"
          class="popular-item"
          @click="goToPanne(panne)"
        >
          <span class="popular-icon">{{ panne.icon }}</span>
          <span class="popular-title">{{ panne.title }}</span>
          <span class="popular-type" :class="panne.type">{{ panne.type === 'hardware' ? 'Hardware' : 'Software' }}</span>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { computed } from 'vue'
import { hardwareSymptoms, softwareSymptoms } from '@/data/depannageData'
import type { Symptom } from '@/types'

const router = useRouter()

const hardwarePannes = computed(() => 
  hardwareSymptoms.map(s => ({
    id: s.id,
    title: s.name,
    icon: getIconForCategory(s.category),
    type: 'hardware' as const,
    severity: s.severity,
    description: s.description,
  }))
)

const softwarePannes = computed(() => 
  softwareSymptoms.map(s => ({
    id: s.id,
    title: s.name,
    icon: getIconForCategory(s.category),
    type: 'software' as const,
    severity: s.severity,
    description: s.description,
  }))
)

const popularPannes = computed(() => [
  ...hardwarePannes.value.slice(0, 4),
  ...softwarePannes.value.slice(0, 4),
])

function getIconForCategory(category: string): string {
  const icons: Record<string, string> = {
    screen: '📱', battery: '🔋', charging: '🔌', audio: '🔊',
    camera: '📷', network: '📡', performance: '⚡', storage: '💾',
    buttons: '🔘', sensors: '👆', os: '⚙️', apps: '📲',
    security: '🛡️', connectivity: '🔵',
  }
  return icons[category] || '🔧'
}

const goToHardware = () => {
  router.push('/depannage?type=hardware')
}

const goToSoftware = () => {
  router.push('/depannage?type=software')
}
const goToPanne = (panne: { id: string; type: string }) => {
  router.push(`/depannage/${panne.type}?panne=${panne.id}`)
}
</script>

<style scoped>
.depannage-view {
  min-height: 100vh;
  padding: 2rem 1rem;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.depannage-header {
  text-align: center;
  margin-bottom: 3rem;
}

.depannage-header h1 {
  font-size: 2.5rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 0.5rem;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.subtitle {
  color: #64748b;
  font-size: 1.1rem;
}

.depannage-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
  max-width: 1000px;
  margin: 0 auto 3rem;
}

.depannage-card {
  background: white;
  border-radius: 1.5rem;
  padding: 2.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  position: relative;
  overflow: hidden;
}

.depannage-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
}

.hardware::before {
  background: linear-gradient(90deg, #ef4444, #f97316);
}

.software::before {
  background: linear-gradient(90deg, #3b82f6, #06b6d4);
}

.hardware:hover {
  border-color: #ef4444;
  transform: translateY(-4px);
  box-shadow: 0 20px 40px -10px rgba(239, 68, 68, 0.2);
}

.software:hover {
  border-color: #3b82f6;
  transform: translateY(-4px);
  box-shadow: 0 20px 40px -10px rgba(59, 130, 246, 0.2);
}

.card-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.depannage-card h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.75rem;
}

.card-desc {
  color: #64748b;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.card-stats {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.stat {
  background: #f1f5f9;
  padding: 0.4rem 0.8rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  color: #475569;
  font-weight: 500;
}

.card-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.tag {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 0.3rem 0.7rem;
  border-radius: 0.375rem;
  font-size: 0.8rem;
  color: #64748b;
}

.card-btn {
  width: 100%;
  padding: 0.875rem;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.hardware .card-btn {
  background: linear-gradient(135deg, #ef4444, #f97316);
  color: white;
}

.hardware .card-btn:hover {
  background: linear-gradient(135deg, #dc2626, #ea580c);
}

.software .card-btn {
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
}

.software .card-btn:hover {
  background: linear-gradient(135deg, #2563eb, #0891b2);
}

/* Section populaire */
.popular-section {
  max-width: 1000px;
  margin: 0 auto;
}

.popular-section h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1.5rem;
  text-align: center;
}

.popular-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}

.popular-item {
  background: white;
  border-radius: 1rem;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 0.5rem;
}

.popular-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.1);
}

.popular-icon {
  font-size: 2rem;
}

.popular-title {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.9rem;
}

.popular-type {
  font-size: 0.75rem;
  padding: 0.2rem 0.6rem;
  border-radius: 0.375rem;
  font-weight: 500;
}

.popular-type.hardware {
  background: #fef2f2;
  color: #dc2626;
}

.popular-type.software {
  background: #eff6ff;
  color: #2563eb;
}

@media (max-width: 768px) {
  .depannage-grid {
    grid-template-columns: 1fr;
  }
  
  .depannage-header h1 {
    font-size: 1.75rem;
  }
  
  .depannage-card {
    padding: 1.5rem;
  }
}
</style>