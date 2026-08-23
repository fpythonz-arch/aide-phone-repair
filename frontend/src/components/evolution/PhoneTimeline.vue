<template>
  <div class="space-y-8">
    <div v-for="era in eras" :key="era.id" class="relative">
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden transition-colors">
        <div class="h-48 bg-gradient-to-r from-primary-600 to-purple-600 relative">
          <img
            v-if="era.image_url"
            :src="era.image_url"
            :alt="era.name"
            class="h-full w-full object-cover opacity-50"
          />
          <div class="absolute inset-0 flex items-end p-6">
            <div>
              <span class="text-white/80 text-sm font-medium">{{ era.year_start }} - {{ era.year_end }}</span>
              <h2 class="text-3xl font-bold text-white">{{ era.name }}</h2>
            </div>
          </div>
        </div>
        <div class="p-6">
          <p class="text-gray-600 dark:text-gray-400 mb-4">{{ era.description }}</p>
          
          <div class="mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-2">Innovations clés</h3>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="feature in era.key_features"
                :key="feature"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300"
              >
                ✨ {{ feature }}
              </span>
            </div>
          </div>

          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-3">Modèles emblématiques</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div
                v-for="model in era.iconic_models"
                :key="model.id"
                class="bg-gray-50 dark:bg-slate-700 rounded-lg p-3 text-center"
              >
                <div class="h-24 bg-white dark:bg-slate-600 rounded-lg mb-2 flex items-center justify-center">
                  <img
                    v-if="model.image_url"
                    :src="model.image_url"
                    :alt="model.model"
                    class="h-full w-full object-contain"
                  />
                  <span v-else class="text-3xl">📱</span>
                </div>
                <p class="font-medium text-gray-900 dark:text-white text-sm">{{ model.brand }} {{ model.model }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ model.year }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ model.significance }}</p>
              </div>
            </div>
          </div>

          <div v-if="era.video_url" class="mt-4">
            <a
              :href="era.video_url"
              target="_blank"
              class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
              ▶️ Voir la vidéo de l'époque
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PhoneEra } from '@/types'

defineProps<{
  eras: PhoneEra[]
}>()
</script>