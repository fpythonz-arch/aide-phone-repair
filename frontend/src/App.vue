<template>
  <div class="min-h-screen bg-gray-50 dark:bg-slate-900 transition-colors duration-300">
    <nav class="bg-white dark:bg-slate-800 shadow-sm border-b dark:border-slate-700 transition-colors duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <router-link to="/" class="text-xl font-bold text-primary-600 dark:text-primary-400">
              🔧 Aide Phone
            </router-link>
          </div>
          <div class="flex items-center space-x-2 overflow-x-auto">
            <router-link to="/" class="nav-link">Dashboard</router-link>
            <router-link to="/diagnostic" class="nav-link">Diagnostic</router-link>
            <router-link to="/depannage" class="nav-link">Dépannage</router-link>
            <router-link to="/composants" class="nav-link">Composants</router-link>
            <router-link to="/outils" class="nav-link">Outils</router-link>
            <router-link to="/codes-secrets" class="nav-link">Codes</router-link>
            <router-link to="/ressources" class="nav-link">Ressources</router-link>
            <router-link to="/evolution" class="nav-link">Évolution</router-link>
            <button
              @click="toggleDark"
              class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
              :title="isDark ? 'Mode clair' : 'Mode sombre'"
            >
              <span v-if="isDark">☀️</span>
              <span v-else>🌙</span>
            </button>
          </div>
        </div>
      </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <router-view />
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

const isDark = ref(false)

function toggleDark() {
  isDark.value = !isDark.value
  if (isDark.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

onMounted(() => {
  const saved = localStorage.getItem('theme')
  if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }
})
</script>

<style scoped>
.nav-link {
  padding-inline: 0.6rem;
  padding-block: 0.4rem;
  border-radius: 0.375rem;
  font-size: 0.8rem;
  font-weight: 500;
  color: #374151;
  transition: color 150ms, background-color 150ms;
  white-space: nowrap;
}

.dark .nav-link {
  color: #d1d5db;
}

.nav-link:hover {
  color: #2563eb;
  background-color: #f3f4f6;
}

.dark .nav-link:hover {
  color: #60a5fa;
  background-color: #1e293b;
}

.router-link-active {
  color: #2563eb;
  background-color: #eff6ff;
}

.dark .router-link-active {
  color: #60a5fa;
  background-color: #1e293b;
}
</style>