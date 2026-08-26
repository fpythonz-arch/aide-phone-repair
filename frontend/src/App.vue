<template>
  <div class="app-shell" :class="{ dark: isDark }">

    <!-- Sidebar desktop -->
    <aside class="sidebar">
      <div class="sidebar-logo">
        <div class="sidebar-logo-icon">
          <WrenchScrewdriverIcon class="w-4 h-4 text-white" />
        </div>
        <span class="sidebar-logo-text">Aide<strong>Phone</strong></span>
      </div>

      <nav class="sidebar-nav">
        <p class="sidebar-group-label">Atelier</p>
        <router-link to="/" class="sidebar-link" :class="{ active: $route.path === '/' }">
          <HomeIcon class="w-4 h-4 flex-shrink-0" /> Tableau de bord
        </router-link>
        <router-link to="/reparations" class="sidebar-link" :class="{ active: $route.path.startsWith('/reparations') }">
          <WrenchScrewdriverIcon class="w-4 h-4 flex-shrink-0" /> Réparations
          <span v-if="pendingCount > 0" class="ml-auto badge badge-red text-xs px-1.5 py-0">{{ pendingCount }}</span>
        </router-link>
        <router-link to="/diagnostic" class="sidebar-link" :class="{ active: $route.path === '/diagnostic' }">
          <MagnifyingGlassIcon class="w-4 h-4 flex-shrink-0" /> Diagnostic
        </router-link>

        <p class="sidebar-group-label">Connaissances</p>
        <router-link to="/depannage" class="sidebar-link" :class="{ active: $route.path.startsWith('/depannage') }">
          <BoltIcon class="w-4 h-4 flex-shrink-0" /> Dépannage
        </router-link>
        <router-link to="/composants" class="sidebar-link" :class="{ active: $route.path === '/composants' }">
          <CpuChipIcon class="w-4 h-4 flex-shrink-0" /> Composants
        </router-link>
        <router-link to="/codes-secrets" class="sidebar-link" :class="{ active: $route.path === '/codes-secrets' }">
          <HashtagIcon class="w-4 h-4 flex-shrink-0" /> Codes secrets
        </router-link>
        <router-link to="/ressources" class="sidebar-link" :class="{ active: $route.path === '/ressources' }">
          <BookOpenIcon class="w-4 h-4 flex-shrink-0" /> Ressources
        </router-link>

        <p class="sidebar-group-label">Outils</p>
        <router-link to="/outils" class="sidebar-link" :class="{ active: $route.path === '/outils' }">
          <CalculatorIcon class="w-4 h-4 flex-shrink-0" /> Outils techniques
        </router-link>
        <router-link to="/evolution" class="sidebar-link" :class="{ active: $route.path === '/evolution' }">
          <ChartBarIcon class="w-4 h-4 flex-shrink-0" /> Évolution
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <div v-if="currentUser" class="px-2 py-2 mb-1">
          <div class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg bg-gray-50 dark:bg-slate-800">
            <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
              {{ currentUser.name[0] }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ currentUser.name }}</p>
              <p class="text-xs text-gray-400 truncate">{{ currentUser.role }}</p>
            </div>
          </div>
        </div>
        <button class="sidebar-link w-full" @click="toggleDark">
          <SunIcon v-if="isDark" class="w-4 h-4 flex-shrink-0" />
          <MoonIcon v-else class="w-4 h-4 flex-shrink-0" />
          {{ isDark ? 'Mode clair' : 'Mode sombre' }}
        </button>
        <button class="sidebar-link w-full text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" @click="handleLogout">
          <ArrowRightOnRectangleIcon class="w-4 h-4 flex-shrink-0" />
          Déconnexion
        </button>
      </div>
    </aside>

    <!-- Main -->
    <div class="main-wrapper">
      <!-- Topbar mobile -->
      <header class="topbar">
        <button class="topbar-btn" @click="drawerOpen = true"><Bars3Icon class="w-5 h-5" /></button>
        <span class="topbar-logo">Aide<strong>Phone</strong></span>
        <div class="flex items-center gap-2">
          <button class="btn btn-ghost btn-icon" @click="toggleDark">
            <SunIcon v-if="isDark" class="w-4 h-4" /><MoonIcon v-else class="w-4 h-4" />
          </button>
          <router-link to="/diagnostic" class="btn btn-primary btn-sm">
            <PlusIcon class="w-4 h-4" /><span class="hidden sm:inline">Diagnostic</span>
          </router-link>
        </div>
      </header>

      <main class="main-content"><router-view /></main>
    </div>

    <!-- Bottom nav mobile -->
    <nav class="bottom-nav">
      <router-link to="/" class="bottom-nav-item" :class="{ active: $route.path === '/' }">
        <HomeIcon class="w-5 h-5" /><span>Accueil</span>
      </router-link>
      <router-link to="/diagnostic" class="bottom-nav-item" :class="{ active: $route.path === '/diagnostic' }">
        <MagnifyingGlassIcon class="w-5 h-5" /><span>Diagnostic</span>
      </router-link>
      <router-link to="/reparations" class="bottom-nav-item" :class="{ active: $route.path.startsWith('/reparations') }">
        <WrenchScrewdriverIcon class="w-5 h-5" /><span>Réparations</span>
      </router-link>
      <router-link to="/depannage" class="bottom-nav-item" :class="{ active: $route.path.startsWith('/depannage') }">
        <BoltIcon class="w-5 h-5" /><span>Dépannage</span>
      </router-link>
      <button class="bottom-nav-item" @click="drawerOpen = true">
        <Bars3Icon class="w-5 h-5" /><span>Plus</span>
      </button>
    </nav>

    <!-- Drawer mobile -->
    <Transition name="fade-overlay">
      <div v-if="drawerOpen" class="mobile-overlay" @click="drawerOpen = false" />
    </Transition>
    <Transition name="slide-drawer">
      <div v-if="drawerOpen" class="mobile-drawer">
        <div class="mobile-drawer-header">
          <span class="topbar-logo">Aide<strong>Phone</strong></span>
          <button class="btn btn-ghost btn-icon" @click="drawerOpen = false"><XMarkIcon class="w-5 h-5" /></button>
        </div>
        <nav class="mobile-drawer-nav">
          <p class="sidebar-group-label" style="margin-top:0">Atelier</p>
          <router-link to="/" class="sidebar-link" @click="drawerOpen = false"><HomeIcon class="w-4 h-4" />Tableau de bord</router-link>
          <router-link to="/reparations" class="sidebar-link" @click="drawerOpen = false"><WrenchScrewdriverIcon class="w-4 h-4" />Réparations</router-link>
          <router-link to="/diagnostic" class="sidebar-link" @click="drawerOpen = false"><MagnifyingGlassIcon class="w-4 h-4" />Diagnostic</router-link>
          <p class="sidebar-group-label">Connaissances</p>
          <router-link to="/depannage" class="sidebar-link" @click="drawerOpen = false"><BoltIcon class="w-4 h-4" />Dépannage</router-link>
          <router-link to="/composants" class="sidebar-link" @click="drawerOpen = false"><CpuChipIcon class="w-4 h-4" />Composants</router-link>
          <router-link to="/codes-secrets" class="sidebar-link" @click="drawerOpen = false"><HashtagIcon class="w-4 h-4" />Codes secrets</router-link>
          <router-link to="/ressources" class="sidebar-link" @click="drawerOpen = false"><BookOpenIcon class="w-4 h-4" />Ressources</router-link>
          <p class="sidebar-group-label">Outils</p>
          <router-link to="/outils" class="sidebar-link" @click="drawerOpen = false"><CalculatorIcon class="w-4 h-4" />Outils techniques</router-link>
          <router-link to="/evolution" class="sidebar-link" @click="drawerOpen = false"><ChartBarIcon class="w-4 h-4" />Évolution</router-link>
        </nav>
        <div class="mobile-drawer-footer">
          <button class="sidebar-link w-full" @click="toggleDark; drawerOpen = false">
            <SunIcon v-if="isDark" class="w-4 h-4" /><MoonIcon v-else class="w-4 h-4" />
            {{ isDark ? 'Mode clair' : 'Mode sombre' }}
          </button>
          <button class="sidebar-link w-full text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" @click="handleLogout">
            <ArrowRightOnRectangleIcon class="w-4 h-4" />Déconnexion
          </button>
        </div>
      </div>
    </Transition>

    <!-- Toasts -->
    <div class="toast-portal" role="region" aria-live="polite">
      <TransitionGroup name="toast">
        <div v-for="t in uiStore.toasts" :key="t.id" :class="['toast', `toast-${t.type}`]">
          <CheckCircleIcon v-if="t.type === 'success'" class="w-4 h-4 flex-shrink-0" />
          <ExclamationCircleIcon v-else-if="t.type === 'error'" class="w-4 h-4 flex-shrink-0" />
          <ExclamationTriangleIcon v-else-if="t.type === 'warning'" class="w-4 h-4 flex-shrink-0" />
          <InformationCircleIcon v-else class="w-4 h-4 flex-shrink-0" />
          <span class="text-sm font-medium">{{ t.message }}</span>
          <button class="ml-auto opacity-60 hover:opacity-100" @click="uiStore.removeToast(t.id)"><XMarkIcon class="w-4 h-4" /></button>
        </div>
      </TransitionGroup>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useUiStore } from '@/stores'
import {
  HomeIcon, WrenchScrewdriverIcon, MagnifyingGlassIcon, BoltIcon,
  CpuChipIcon, HashtagIcon, BookOpenIcon, CalculatorIcon, ChartBarIcon,
  SunIcon, MoonIcon, Bars3Icon, XMarkIcon, PlusIcon,
  CheckCircleIcon, ExclamationCircleIcon, ExclamationTriangleIcon, InformationCircleIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'
import { useAuth } from '@/composables/useAuth'
import { useRepairs } from '@/composables/useRepairs'
import { useRouter } from 'vue-router'

const uiStore = useUiStore()
const { currentUser, isAuthenticated, logout } = useAuth()
const { stats, fetchRepairs } = useRepairs()
const router = useRouter()
const isDark = ref(false)
const drawerOpen = ref(false)

function handleLogout() {
  logout()
  router.push('/login')
}

const pendingCount = computed(() => stats.value.active + stats.value.pending)

function toggleDark() {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('ap_theme', isDark.value ? 'dark' : 'light')
}

onMounted(() => {
  const saved = localStorage.getItem('ap_theme')
  if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }
  if (isAuthenticated.value) fetchRepairs()
})
</script>

<style>
/* App Shell */
.app-shell { display: flex; min-height: 100vh; background-color: #f8fafc; }
.dark .app-shell { background-color: #0b1120; }

/* Sidebar */
.sidebar {
  display: none; width: 220px; flex-shrink: 0; flex-direction: column;
  background: #fff; border-right: 1px solid #e2e8f0;
  position: sticky; top: 0; height: 100vh; overflow-y: auto; overflow-x: hidden;
}
.dark .sidebar { background: #111827; border-right-color: #1e293b; }
@media (min-width: 1024px) { .sidebar { display: flex; } }

.sidebar-logo {
  display: flex; align-items: center; gap: 0.625rem;
  padding: 1rem 0.875rem; border-bottom: 1px solid #f1f5f9; min-height: 3.5rem;
}
.dark .sidebar-logo { border-bottom-color: #1e293b; }
.sidebar-logo-icon {
  width: 1.75rem; height: 1.75rem; background: #2563eb; border-radius: 0.5rem;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.sidebar-logo-text { font-size: 1rem; font-weight: 600; color: #0f172a; }
.dark .sidebar-logo-text { color: #f8fafc; }
.sidebar-logo-text strong { color: #2563eb; }

.sidebar-nav { flex: 1; padding: 0.75rem 0.5rem; display: flex; flex-direction: column; gap: 0.125rem; overflow-y: auto; }
.sidebar-footer { padding: 0.75rem 0.5rem; border-top: 1px solid #f1f5f9; }
.dark .sidebar-footer { border-top-color: #1e293b; }

/* Main */
.main-wrapper { flex: 1; min-width: 0; display: flex; flex-direction: column; }

/* Topbar */
.topbar {
  display: flex; align-items: center; gap: 0.75rem; padding: 0 1rem; height: 3.25rem;
  background: #fff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 30;
}
.dark .topbar { background: #111827; border-bottom-color: #1e293b; }
@media (min-width: 1024px) { .topbar { display: none; } }
.topbar-btn { padding: 0.375rem; border-radius: 0.375rem; background: none; border: none; cursor: pointer; color: #374151; display: flex; align-items: center; }
.dark .topbar-btn { color: #cbd5e1; }
.topbar-logo { font-size: 1rem; font-weight: 700; color: #0f172a; flex: 1; text-align: center; }
.dark .topbar-logo { color: #f8fafc; }
.topbar-logo strong { color: #2563eb; }

/* Main content */
.main-content { flex: 1; padding: 1.25rem 1rem 5rem; }
@media (min-width: 640px) { .main-content { padding: 1.5rem 1.5rem 2rem; } }
@media (min-width: 1024px) { .main-content { padding: 2rem; } }

/* Bottom nav */
.bottom-nav {
  display: flex; position: fixed; bottom: 0; left: 0; right: 0; height: 3.75rem;
  background: #fff; border-top: 1px solid #e2e8f0; z-index: 40;
  padding-bottom: env(safe-area-inset-bottom);
}
.dark .bottom-nav { background: #111827; border-top-color: #1e293b; }
@media (min-width: 1024px) { .bottom-nav { display: none; } }

.bottom-nav-item {
  flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 0.1875rem; font-size: 0.625rem; font-weight: 500; color: #64748b;
  text-decoration: none; background: none; border: none; cursor: pointer;
  transition: color 0.15s; padding: 0.5rem 0.25rem; min-height: 44px;
}
.bottom-nav-item:hover, .bottom-nav-item.active { color: #2563eb; }
.dark .bottom-nav-item:hover, .dark .bottom-nav-item.active { color: #60a5fa; }

/* Mobile overlay & drawer */
.mobile-overlay { position: fixed; inset: 0; background: rgb(0 0 0 / 0.5); z-index: 50; }
.mobile-drawer {
  position: fixed; left: 0; top: 0; bottom: 0; width: min(280px, 85vw);
  background: #fff; z-index: 51; display: flex; flex-direction: column;
  overflow-y: auto; box-shadow: 4px 0 24px rgb(0 0 0 / 0.15);
}
.dark .mobile-drawer { background: #111827; }
.mobile-drawer-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1rem; border-bottom: 1px solid #f1f5f9;
}
.dark .mobile-drawer-header { border-bottom-color: #1e293b; }
.mobile-drawer-nav { flex: 1; padding: 0.75rem; display: flex; flex-direction: column; gap: 0.125rem; }
.mobile-drawer-footer { padding: 0.75rem; border-top: 1px solid #f1f5f9; }
.dark .mobile-drawer-footer { border-top-color: #1e293b; }

/* Toasts */
.toast-portal {
  position: fixed; bottom: 4.5rem; right: 1rem; z-index: 100;
  display: flex; flex-direction: column; gap: 0.5rem;
  pointer-events: none; max-width: 20rem;
}
@media (min-width: 1024px) { .toast-portal { bottom: 1.5rem; right: 1.5rem; } }
.toast {
  display: flex; align-items: center; gap: 0.625rem; padding: 0.75rem 0.875rem;
  border-radius: 0.625rem; font-size: 0.875rem; box-shadow: 0 8px 24px rgb(0 0 0 / 0.15);
  pointer-events: auto; border: 1px solid; animation: fadeIn 0.2s ease;
}
.toast-success { background: #f0fdf4; color: #166534; border-color: #86efac; }
.toast-error   { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }
.toast-warning { background: #fffbeb; color: #92400e; border-color: #fde68a; }
.toast-info    { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.dark .toast-success { background: #14532d; color: #86efac; border-color: #166534; }
.dark .toast-error   { background: #450a0a; color: #fca5a5; border-color: #991b1b; }
.dark .toast-warning { background: #451a03; color: #fde68a; border-color: #92400e; }
.dark .toast-info    { background: #1e3a8a; color: #bfdbfe; border-color: #1d4ed8; }

/* Transitions */
.fade-overlay-enter-active, .fade-overlay-leave-active { transition: opacity 0.2s; }
.fade-overlay-enter-from, .fade-overlay-leave-to { opacity: 0; }
.slide-drawer-enter-active, .slide-drawer-leave-active { transition: transform 0.25s ease; }
.slide-drawer-enter-from, .slide-drawer-leave-to { transform: translateX(-100%); }
.toast-enter-active  { animation: fadeIn 0.2s ease; }
.toast-leave-active  { transition: opacity 0.2s, transform 0.2s; }
.toast-leave-to      { opacity: 0; transform: translateX(1rem); }
.toast-move          { transition: transform 0.2s; }
</style>
