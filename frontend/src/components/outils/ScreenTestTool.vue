<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4">🖥️ Test d'écran</h3>
    
    <div class="space-y-4">
      <p class="text-sm text-gray-600">Cliquez sur les couleurs pour afficher un écran pleine page et détecter les pixels morts.</p>
      
      <div class="grid grid-cols-2 gap-3">
        <button 
          v-for="color in testColors" 
          :key="color.name"
          @click="openFullscreen(color.hex)"
          class="h-16 rounded-lg font-medium text-white shadow-sm hover:scale-105 transition-transform"
          :style="{ backgroundColor: color.hex }"
        >
          {{ color.name }}
        </button>
      </div>

      <div v-if="isFullscreen" 
           class="fixed inset-0 z-50 flex items-center justify-center cursor-pointer"
           :style="{ backgroundColor: currentColor }"
           @click="isFullscreen = false"
      >
        <p class="text-white/50 text-xl font-bold">Cliquez pour fermer</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const isFullscreen = ref(false);
const currentColor = ref('');

const testColors = [
  { name: 'Rouge', hex: '#ef4444' },
  { name: 'Vert', hex: '#22c55e' },
  { name: 'Bleu', hex: '#3b82f6' },
  { name: 'Blanc', hex: '#ffffff' },
  { name: 'Noir', hex: '#000000' },
  { name: 'Jaune', hex: '#eab308' },
];

function openFullscreen(color: string) {
  currentColor.value = color;
  isFullscreen.value = true;
}
</script>