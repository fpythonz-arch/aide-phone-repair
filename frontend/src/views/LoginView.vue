<template>
  <div class="login-shell">

    <!-- Panneau gauche — branding -->
    <div class="login-brand">
      <div class="login-brand-content">
        <div class="login-logo">
          <WrenchScrewdriverIcon class="w-7 h-7 text-white" />
        </div>
        <h1 class="login-brand-title">AidePhone</h1>
        <p class="login-brand-subtitle">Plateforme professionnelle de gestion et d'assistance à la réparation électronique.</p>

        <div class="login-features">
          <div v-for="f in features" :key="f.text" class="login-feature-item">
            <CheckCircleIcon class="w-4 h-4 text-blue-300 flex-shrink-0" />
            <span>{{ f.text }}</span>
          </div>
        </div>
      </div>
      <p class="login-brand-footer">© {{ year }} AidePhone · Conçu pour les techniciens africains</p>
    </div>

    <!-- Panneau droit — formulaire -->
    <div class="login-form-panel">
      <div class="login-form-container">

        <!-- Logo mobile -->
        <div class="login-logo-mobile">
          <div class="login-logo" style="width:2.5rem;height:2.5rem">
            <WrenchScrewdriverIcon class="w-5 h-5 text-white" />
          </div>
          <span class="text-xl font-bold text-gray-900 dark:text-white">AidePhone</span>
        </div>

        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Connexion</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Accédez à votre espace atelier</p>
        </div>

        <!-- Message d'erreur -->
        <Transition name="fade-overlay">
          <div v-if="error" class="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg mb-5 text-sm text-red-700 dark:text-red-400">
            <ExclamationCircleIcon class="w-4 h-4 flex-shrink-0" />
            {{ error }}
          </div>
        </Transition>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="label">Adresse email</label>
            <div class="relative">
              <EnvelopeIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="form.email"
                type="email"
                class="input pl-9"
                placeholder="technicien@atelier.com"
                autocomplete="email"
                required
              />
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="label" style="margin-bottom:0">Mot de passe</label>
              <button type="button" class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400">Mot de passe oublié ?</button>
            </div>
            <div class="relative">
              <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="input pl-9 pr-10"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              />
              <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" @click="showPassword = !showPassword">
                <EyeSlashIcon v-if="showPassword" class="w-4 h-4" />
                <EyeIcon v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <input id="remember" v-model="form.remember" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
            <label for="remember" class="text-sm text-gray-600 dark:text-gray-400 cursor-pointer">Se souvenir de moi</label>
          </div>

          <button type="submit" class="btn btn-primary w-full" :disabled="loading" style="height:2.75rem">
            <span v-if="loading" class="flex items-center gap-2">
              <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              Connexion en cours...
            </span>
            <span v-else>Se connecter</span>
          </button>
        </form>

        <!-- Séparateur -->
        <div class="flex items-center gap-3 my-5">
          <div class="flex-1 h-px bg-gray-200 dark:bg-slate-700" />
          <span class="text-xs text-gray-400">Accès de démonstration</span>
          <div class="flex-1 h-px bg-gray-200 dark:bg-slate-700" />
        </div>

        <!-- Comptes démo -->
        <div class="space-y-2">
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Connectez-vous rapidement avec un compte démo :</p>
          <button
            v-for="demo in demoAccounts"
            :key="demo.email"
            type="button"
            class="w-full flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors text-left"
            @click="fillDemo(demo)"
          >
            <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0', demo.color]">
              {{ demo.name[0] }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ demo.name }}</p>
              <p class="text-xs text-gray-400 truncate">{{ demo.email }} · {{ demo.role }}</p>
            </div>
            <ArrowRightIcon class="w-4 h-4 text-gray-300 flex-shrink-0" />
          </button>
        </div>

        <p class="text-xs text-center text-gray-400 mt-6">
          Vous n'avez pas de compte ?
          <button type="button" class="text-blue-600 hover:underline dark:text-blue-400 font-medium">Contacter l'administrateur</button>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  WrenchScrewdriverIcon, CheckCircleIcon, EnvelopeIcon, LockClosedIcon,
  EyeIcon, EyeSlashIcon, ExclamationCircleIcon, ArrowRightIcon
} from '@heroicons/vue/24/outline'
import { useAuth } from '@/composables/useAuth'
import { useRepairs } from '@/composables/useRepairs'
import { useUiStore } from '@/stores'

const route = useRoute()
const router = useRouter()
const { login } = useAuth()
const { fetchRepairs } = useRepairs()
const uiStore = useUiStore()
const year = new Date().getFullYear()

const form = ref({ email: '', password: '', remember: false })
const loading = ref(false)
const error = ref('')
const showPassword = ref(false)

const features = [
  { text: 'Gestion complète des réparations' },
  { text: 'Diagnostic guidé étape par étape' },
  { text: 'Base de données de composants' },
  { text: 'Codes secrets par marque' },
  { text: 'Guides de dépannage Hardware & Software' },
  { text: 'Suivi des appareils et clients' },
]

const demoAccounts = [
  { name: 'Abdoul Diallo',  email: 'abdoul@atelier.com',  password: 'demo1234', role: 'Technicien senior', color: 'bg-blue-500' },
  { name: 'Ibrahim Koné',   email: 'ibrahim@atelier.com', password: 'demo1234', role: 'Technicien',        color: 'bg-green-500' },
  { name: 'Moussa Traoré',  email: 'moussa@atelier.com',  password: 'demo1234', role: 'Admin',             color: 'bg-purple-500' },
]

function fillDemo(demo: typeof demoAccounts[0]) {
  form.value.email    = demo.email
  form.value.password = demo.password
  error.value = ''
}

async function handleLogin() {
  loading.value = true
  error.value   = ''

  const ok = await login(form.value.email, form.value.password, form.value.remember)

  if (ok) {
    const migration = await migrateThenFetch()
    if (migration && migration.imported > 0) {
      uiStore.showSuccess(`${migration.imported} réparation(s) locale(s) importée(s)`)
    }
    router.push((route.query.redirect as string) || '/')
  } else {
    error.value = 'Email ou mot de passe incorrect.'
  }

  loading.value = false
}

async function migrateThenFetch() {
  const { migrateLocalRepairsIfNeeded } = useAuth()
  const result = await migrateLocalRepairsIfNeeded()
  await fetchRepairs(true)
  return result
}
</script>

<style scoped>
.login-shell {
  min-height: 100vh;
  display: flex;
  background-color: #f8fafc;
}

/* Panneau gauche */
.login-brand {
  display: none;
  width: 42%;
  background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #2563eb 100%);
  padding: 3rem;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
  overflow: hidden;
}
.login-brand::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
@media (min-width: 1024px) {
  .login-brand { display: flex; }
}

.login-brand-content { position: relative; z-index: 1; }

.login-logo {
  width: 3rem;
  height: 3rem;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(4px);
  border-radius: 0.875rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
  border: 1px solid rgba(255,255,255,0.2);
}

.login-brand-title {
  font-size: 2rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.03em;
  margin-bottom: 0.75rem;
}

.login-brand-subtitle {
  font-size: 1rem;
  color: rgba(255,255,255,0.75);
  line-height: 1.6;
  max-width: 28rem;
  margin-bottom: 2.5rem;
}

.login-features { display: flex; flex-direction: column; gap: 0.75rem; }
.login-feature-item {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  font-size: 0.875rem;
  color: rgba(255,255,255,0.85);
}

.login-brand-footer {
  font-size: 0.75rem;
  color: rgba(255,255,255,0.4);
  position: relative;
  z-index: 1;
}

/* Panneau droit */
.login-form-panel {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1.5rem;
  overflow-y: auto;
}

.login-form-container {
  width: 100%;
  max-width: 26rem;
}

/* Logo mobile */
.login-logo-mobile {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  margin-bottom: 2rem;
}
@media (min-width: 1024px) {
  .login-logo-mobile { display: none; }
}
</style>
