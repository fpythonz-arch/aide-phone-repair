<template>
  <div>
    <!-- Overlay zoom plein écran -->
    <div
      v-if="isZoomed && component.image_url"
      class="fixed inset-0 z-[60] bg-black/90 flex items-center justify-center p-4 cursor-zoom-out"
      @click="isZoomed = false"
    >
      <img 
        :src="component.image_url" 
        :alt="component.name"
        class="max-w-full max-h-full object-contain"
      />
      <button 
        @click.stop="isZoomed = false"
        class="absolute top-4 right-4 w-10 h-10 bg-white/20 hover:bg-white/40 rounded-full flex items-center justify-center text-white text-xl transition-colors"
      >
        ✕
      </button>
    </div>

    <!-- Modal normal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="$emit('close')">
      <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Header avec image cliquable -->
        <div 
          class="relative h-64 bg-gradient-to-br from-blue-500 to-purple-600 rounded-t-2xl overflow-hidden flex items-center justify-center cursor-zoom-in"
          @click="component.image_url ? isZoomed = true : null"
        >
          <img 
            v-if="component.image_url" 
            :src="component.image_url" 
            :alt="component.name"
            class="max-w-full max-h-full object-contain p-6 drop-shadow-lg"
          />
          <span v-else class="text-8xl">🔧</span>
          
          <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent pt-16 pb-4 px-6">
            <h2 class="text-2xl font-bold text-white">{{ component.name }}</h2>
            <p class="text-white/80 text-sm">{{ formatCategory(component.category) }}</p>
          </div>
          
          <button 
            @click.stop="$emit('close')" 
            class="absolute top-4 right-4 w-8 h-8 bg-white/20 hover:bg-white/40 rounded-full flex items-center justify-center text-white transition-colors"
          >
            ✕
          </button>
        </div>

        <div class="p-6 space-y-6">
          <!-- Description -->
          <div>
            <h3 class="font-semibold text-gray-900 mb-2">📝 Description</h3>
            <p class="text-gray-600 leading-relaxed">{{ component.description }}</p>
          </div>

          <!-- Badges -->
          <div class="flex flex-wrap gap-2">
            <span :class="['px-3 py-1 rounded-full text-sm font-medium', difficultyColor(component.replacement_difficulty)]">
              🔧 Difficulté: {{ '⭐'.repeat(component.replacement_difficulty || 1) }}
            </span>
            <span v-if="component.category !== 'logiciel'" class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700">
              🔩 Matériel
            </span>
            <span v-else class="px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-700">
              💻 Logiciel
            </span>
            <span v-if="getPriceRange(component)" class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
              💰 {{ getPriceRange(component) }}
            </span>
          </div>

          <!-- Spécifications techniques -->
          <div v-if="component.technical_specs && Object.keys(component.technical_specs).length > 0" class="bg-gray-50 rounded-xl p-4">
            <h3 class="font-semibold text-gray-900 mb-3">⚙️ Spécifications techniques</h3>
            <div class="grid grid-cols-2 gap-3">
              <div v-for="(value, key) in component.technical_specs" :key="key" class="flex flex-col">
                <span class="text-xs text-gray-500 uppercase">{{ key }}</span>
                <span class="text-sm font-medium text-gray-800">{{ formatValue(value) }}</span>
              </div>
            </div>
          </div>

          <!-- Problèmes fréquents -->
          <div v-if="component.common_failures?.length">
            <h3 class="font-semibold text-gray-900 mb-3">⚠️ Problèmes fréquents</h3>
            <ul class="space-y-2">
              <li
                v-for="(issue, i) in component.common_failures"
                :key="i"
                class="flex items-start gap-2 text-gray-700 bg-red-50 p-3 rounded-lg"
              >
                <span class="text-red-500 mt-0.5">•</span>
                <span>{{ issue }}</span>
              </li>
            </ul>
          </div>

          <!-- Appareils compatibles -->
          <div v-if="component.compatible_devices?.length">
            <h3 class="font-semibold text-gray-900 mb-3">📱 Appareils compatibles</h3>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="(device, i) in component.compatible_devices"
                :key="i"
                class="text-sm bg-blue-50 text-blue-700 px-3 py-1 rounded-full"
              >
                {{ device }}
              </span>
            </div>
          </div>

          <!-- Disponibilité -->
          <div v-if="component.availability" class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Disponibilité:</span>
            <span :class="['text-sm font-medium px-2 py-0.5 rounded', availabilityColor(component.availability)]">
              {{ formatAvailability(component.availability) }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
  component: any;
}>();

defineEmits(['close']);

const isZoomed = ref(false);

function formatCategory(cat: string): string {
  const map: Record<string, string> = {
    capteurs: '📡 Capteurs',
    carte_mere: '📟 Carte Mère',
    ecrans: '📱 Écrans',
    connectique: '🔌 Connectique',
    radio_frequence: '📶 Radio Fréquence',
    circuit_charge: '⚡ Circuit de Charge',
    vibreur: '📳 Vibreur',
    camera: '📷 Caméra',
    bluetooth: '🔵 Bluetooth',
    emetteur_recepteur: '📡 Émetteur-Récepteur',
    audio: '🔊 Audio',
    buzzer: '🔔 Buzzer',
    clavier: '⌨️ Clavier',
    logiciel: '💻 Logiciel',
  };
  return map[cat] || cat;
}

function difficultyColor(diff: number): string {
  if (!diff || diff <= 1) return 'bg-green-100 text-green-700';
  if (diff === 2) return 'bg-blue-100 text-blue-700';
  if (diff === 3) return 'bg-yellow-100 text-yellow-700';
  if (diff === 4) return 'bg-orange-100 text-orange-700';
  return 'bg-red-100 text-red-700';
}

function getPriceRange(comp: any): string {
  if (!comp.price_range || comp.price_range.min === undefined) return '';
  if (comp.price_range.min === 0 && comp.price_range.max === 0) return 'N/A';
  if (comp.price_range.min === comp.price_range.max) return `${comp.price_range.min}€`;
  return `${comp.price_range.min}-${comp.price_range.max}€`;
}

function formatValue(value: any): string {
  if (Array.isArray(value)) return value.join(', ');
  if (typeof value === 'boolean') return value ? 'Oui' : 'Non';
  return String(value);
}

function availabilityColor(availability: string): string {
  const map: Record<string, string> = {
    'in_stock': 'bg-green-100 text-green-700',
    'low_stock': 'bg-yellow-100 text-yellow-700',
    'special_order': 'bg-orange-100 text-orange-700',
    'not_available': 'bg-red-100 text-red-700',
  };
  return map[availability] || 'bg-gray-100 text-gray-700';
}

function formatAvailability(availability: string): string {
  const map: Record<string, string> = {
    'in_stock': 'En stock',
    'low_stock': 'Stock faible',
    'special_order': 'Sur commande',
    'not_available': 'Non disponible',
  };
  return map[availability] || availability;
}
</script>