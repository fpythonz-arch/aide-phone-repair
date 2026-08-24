<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white">
      <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold">🔧 Composants de Smartphone</h1>
        <p class="mt-2 text-blue-100 text-lg">Base de données complète des composants matériels et logiciels</p>

        <!-- Stats -->
        <div class="flex gap-6 mt-6">
          <div class="bg-white/20 backdrop-blur rounded-lg px-4 py-2">
            <span class="text-2xl font-bold">{{ hardwareCount }}</span>
            <span class="text-sm ml-1">Matériels</span>
          </div>
          <div class="bg-white/20 backdrop-blur rounded-lg px-4 py-2">
            <span class="text-2xl font-bold">{{ softwareCount }}</span>
            <span class="text-sm ml-1">Logiciels</span>
          </div>
          <div class="bg-white/20 backdrop-blur rounded-lg px-4 py-2">
            <span class="text-2xl font-bold">{{ totalCount }}</span>
            <span class="text-sm ml-1">Total</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="max-w-7xl mx-auto px-4 py-6 sticky top-0 bg-gray-50/95 backdrop-blur z-30 border-b">
      <div class="flex flex-col md:flex-row gap-4">
        <!-- Recherche -->
        <div class="flex-1 relative">
          <input v-model="searchQuery" type="text" placeholder="🔍 Rechercher un composant..."
            class="w-full px-4 py-3 pl-12 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all" />
          <span class="absolute left-4 top-3.5 text-gray-400">🔍</span>
        </div>

        <!-- Filtre catégorie -->
        <select v-model="selectedCategory"
          class="px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
          <option value="">Toutes les catégories</option>
          <option v-for="cat in allCategories" :key="cat" :value="cat">
            {{ formatCategory(cat) }}
          </option>
        </select>

        <!-- Filtre difficulté -->
        <select v-model="selectedDifficulty"
          class="px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
          <option value="">Toutes difficultés</option>
          <option value="1">⭐ Débutant</option>
          <option value="2">⭐⭐ Facile</option>
          <option value="3">⭐⭐⭐ Moyen</option>
          <option value="4">⭐⭐⭐⭐ Difficile</option>
          <option value="5">⭐⭐⭐⭐⭐ Expert</option>
        </select>
      </div>

      <!-- Tags de filtres actifs -->
      <div v-if="searchQuery || selectedCategory || selectedDifficulty" class="flex flex-wrap gap-2 mt-3">
        <span v-if="searchQuery"
          class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm flex items-center gap-1">
          🔍 {{ searchQuery }}
          <button @click="searchQuery = ''" class="hover:text-blue-900">×</button>
        </span>
        <span v-if="selectedCategory"
          class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm flex items-center gap-1">
          📁 {{ formatCategory(selectedCategory) }}
          <button @click="selectedCategory = ''" class="hover:text-green-900">×</button>
        </span>
        <span v-if="selectedDifficulty"
          class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm flex items-center gap-1">
          ⭐ Difficulté {{ selectedDifficulty }}
          <button @click="selectedDifficulty = ''" class="hover:text-purple-900">×</button>
        </span>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-200 border-t-blue-600"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="max-w-7xl mx-auto px-4 py-10 text-center">
      <div class="bg-red-50 border border-red-200 rounded-2xl p-8">
        <p class="text-red-700 font-medium text-lg">❌ {{ error }}</p>
        <button @click="fetchComponents"
          class="mt-4 px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors">
          Réessayer
        </button>
      </div>
    </div>

    <!-- Content -->
    <div v-else class="max-w-7xl mx-auto px-4 pb-12">

      <!-- ════════════════════════════════════════ -->
      <!-- SECTION 1: COMPOSANTS MATÉRIELS -->
      <!-- ════════════════════════════════════════ -->
      <div class="mb-12">
        <div class="flex items-center gap-3 mb-6">
          <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center text-2xl">🔩</div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Les Matériels</h2>
            <p class="text-gray-500">{{ hardwareCount }} composants physiques</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <div v-for="component in paginatedHardware" :key="component.id" @click="selectComponent(component)"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all cursor-pointer overflow-hidden group">
            <!-- IMAGE DU COMPOSANT -->
            <div
              class="h-40 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center relative overflow-hidden">
              <img v-if="component.image_url" :src="component.image_url" :alt="component.name"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                loading="lazy" />
              <span v-else class="text-5xl group-hover:scale-110 transition-transform">
                {{ getCategoryIcon(component.category) }}
              </span>
              <div class="absolute top-2 right-2">
                <span
                  :class="['px-2 py-0.5 rounded-full text-xs font-bold', difficultyColor(component.replacement_difficulty)]">
                  {{ '⭐'.repeat(component.replacement_difficulty || 1) }}
                </span>
              </div>
            </div>

            <div class="p-4">
              <div class="flex items-start justify-between mb-2">
                <h3 class="font-bold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-1">
                  {{ component.name }}
                </h3>
              </div>

              <p class="text-sm text-gray-500 line-clamp-2 mb-3 h-10">{{ component.description }}</p>

              <!-- Badges -->
              <div class="flex flex-wrap gap-1.5 mb-3">
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md">
                  📁 {{ formatCategory(component.category) }}
                </span>
                <span v-if="getPriceRange(component)"
                  class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-md">
                  💰 {{ getPriceRange(component) }}
                </span>
              </div>

              <!-- Problèmes fréquents -->
              <div v-if="component.common_failures?.length" class="space-y-1">
                <p class="text-xs text-gray-400 font-medium">⚠️ Problèmes fréquents:</p>
                <div class="flex flex-wrap gap-1">
                  <span v-for="(issue, i) in component.common_failures.slice(0, 2)" :key="i"
                    class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded">
                    {{ issue }}
                  </span>
                  <span v-if="component.common_failures.length > 2" class="text-xs text-gray-400">
                    +{{ component.common_failures.length - 2 }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination Matériel -->
        <div v-if="hardwarePages > 1" class="flex justify-center mt-6 gap-2">
          <button v-for="page in hardwarePages" :key="page" @click="hardwarePage = page" :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
            hardwarePage === page
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-600 hover:bg-gray-100 border'
          ]">
            {{ page }}
          </button>
        </div>
      </div>
      <!-- ════════════════════════════════════════ -->
      <!-- SECTION 2: COMPOSANTS LOGICIELS -->
      <!-- ════════════════════════════════════════ -->
      <div class="mt-12">
        <div class="flex items-center gap-3 mb-6">
          <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-2xl">💻</div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Les Logiciels</h2>
            <p class="text-gray-500">{{ softwareComponents.length }} composants logiciels</p>
          </div>
        </div>

        <!-- Si vide -->
        <div v-if="softwareComponents.length === 0"
          class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center">
          <p class="text-gray-400 text-lg">Aucun composant logiciel</p>
        </div>

        <!-- Grille -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <div v-for="component in softwareComponents" :key="component.id" @click="selectComponent(component)"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all cursor-pointer overflow-hidden group">
            <!-- IMAGE -->
            <div
              class="h-48 bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center relative overflow-hidden p-6">
              <img v-if="component.image_url" :src="component.image_url" :alt="component.name"
                class="max-w-full max-h-full object-contain group-hover:scale-110 transition-transform duration-500"
                loading="lazy" />
              <span v-else class="text-6xl group-hover:scale-110 transition-transform">💾</span>
              <div class="absolute top-2 right-2">
                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                  LOGICIEL
                </span>
              </div>
            </div>

            <div class="p-4">
              <h3 class="font-bold text-gray-900 group-hover:text-purple-600 transition-colors line-clamp-1 mb-2">
                {{ component.name }}
              </h3>

              <p class="text-sm text-gray-500 line-clamp-2 mb-3 h-10">{{ component.description }}</p>

              <div class="flex flex-wrap gap-1.5">
                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-md">
                  💻 Logiciel
                </span>
                <span v-if="component.technical_specs?.type"
                  class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md">
                  {{ component.technical_specs.type }}
                </span>
              </div>

              <div v-if="component.common_failures?.length" class="mt-3 space-y-1">
                <p class="text-xs text-gray-400 font-medium">⚠️ Problèmes fréquents:</p>
                <div class="flex flex-wrap gap-1">
                  <span v-for="(issue, i) in component.common_failures.slice(0, 2)" :key="i"
                    class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded">
                    {{ issue }}
                  </span>
                  <span v-if="component.common_failures.length > 2" class="text-xs text-gray-400">
                    +{{ component.common_failures.length - 2 }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal détail -->
    <ComponentDetail v-if="selectedComponent" :component="selectedComponent" @close="selectedComponent = null" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useComponents } from '@/composables/useComponents';
import ComponentDetail from '@/components/components/ComponentDetail.vue';

const {
  components,
  loading,
  error,
  fetchComponents,
} = useComponents();

const searchQuery = ref('');
const selectedCategory = ref('');
const selectedDifficulty = ref('');
const selectedComponent = ref<any>(null);
const hardwarePage = ref(1);
const softwarePage = ref(1);
const itemsPerPage = 12;

// Gestion erreur image
function handleImageError(event: Event) {
  const img = event.target as HTMLImageElement;
  img.style.display = 'none';
  // Le parent a déjà le v-if/v-else, donc l'emoji de fallback s'affiche automatiquement
  // Pas besoin de manipuler le DOM manuellement
}

// Filtrer les composants
const filteredComponents = computed(() => {
  let result = components.value;

  // Recherche texte
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(c =>
      c.name.toLowerCase().includes(q) ||
      c.description?.toLowerCase().includes(q) ||
      c.category?.toLowerCase().includes(q) ||
      c.common_failures?.some((f: string) => f.toLowerCase().includes(q))
    );
  }

  // Filtre catégorie
  if (selectedCategory.value) {
    result = result.filter(c => c.category === selectedCategory.value);
  }

  // Filtre difficulté
  if (selectedDifficulty.value) {
    result = result.filter(c => String(c.replacement_difficulty) === selectedDifficulty.value);
  }

  return result;
});

// Séparer matériel et logiciel
const hardwareComponents = computed(() =>
  filteredComponents.value.filter(c => c.category !== 'logiciel')
);
const softwareComponents = computed(() =>
  filteredComponents.value.filter(c => c.category === 'logiciel')
);

// Pagination
// Pagination
const paginatedHardware = computed(() => {
  const start = (hardwarePage.value - 1) * itemsPerPage;
  return hardwareComponents.value.slice(start, start + itemsPerPage);
});
const paginatedSoftware = computed(() => {
  const start = (softwarePage.value - 1) * itemsPerPage;
  return softwareComponents.value.slice(start, start + itemsPerPage);
});

const hardwarePages = computed(() => Math.ceil(hardwareComponents.value.length / itemsPerPage));
const softwarePages = computed(() => Math.ceil(softwareComponents.value.length / itemsPerPage));

// Compteurs
const hardwareCount = computed(() => hardwareComponents.value.length);
const softwareCount = computed(() => softwareComponents.value.length);
const totalCount = computed(() => components.value.length);

// Catégories uniques
const allCategories = computed(() => {
  const cats = new Set(components.value.map(c => c.category));
  return Array.from(cats).sort();
});

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

function getCategoryIcon(cat: string): string {
  const map: Record<string, string> = {
    capteurs: '📡',
    carte_mere: '📟',
    ecrans: '📱',
    connectique: '🔌',
    radio_frequence: '📶',
    circuit_charge: '⚡',
    vibreur: '📳',
    camera: '📷',
    bluetooth: '🔵',
    emetteur_recepteur: '📡',
    audio: '🔊',
    buzzer: '🔔',
    clavier: '⌨️',
    logiciel: '💻',

  };
  return map[cat] || '🔧';
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

function selectComponent(comp: any) {
  selectedComponent.value = comp;
}

// Réinitialiser pagination quand les filtres changent
watch([searchQuery, selectedCategory, selectedDifficulty], () => {
  hardwarePage.value = 1;
  softwarePage.value = 1;
});

onMounted(() => {
  fetchComponents();
});
</script>