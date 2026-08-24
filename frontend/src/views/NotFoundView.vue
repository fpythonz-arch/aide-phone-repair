<template>
  <div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4 animate-fade-in">
    <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center mb-4">
      <ExclamationTriangleIcon class="w-8 h-8 text-blue-500" />
    </div>
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Page introuvable</h1>
    <p class="text-gray-500 dark:text-gray-400 max-w-sm mb-6">La page que vous recherchez n'existe pas ou a été déplacée.</p>
    <router-link to="/" class="btn btn-primary"><HomeIcon class="w-4 h-4" />Tableau de bord</router-link>
  </div>
</template>
<script setup lang="ts">
import { ExclamationTriangleIcon, HomeIcon } from '@heroicons/vue/24/outline'
</script>
