<template>
  <div class="login-shell">

    <!-- Panneau gauche — branding -->
    <div class="login-brand">
      <div class="login-brand-content">
        <div class="login-logo">
          <WrenchScrewdriverIcon class="w-7 h-7 text-white" />
        </div>
        <h1 class="login-brand-title">AidePhone</h1>
        <p class="login-brand-subtitle">Créez votre atelier numérique : réparations, clients et diagnostic au même endroit.</p>

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

        <div class="login-logo-mobile">
          <div class="login-logo" style="width:2.5rem;height:2.5rem">
            <WrenchScrewdriverIcon class="w-5 h-5 text-white" />
          </div>
          <span class="text-xl font-bold text-gray-900 dark:text-white">AidePhone</span>
        </div>

        <div class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Créer un compte</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gratuit. Votre atelier et vos clients restent privés.</p>
        </div>

        <Transition name="fade-overlay">
          <div v-if="error" class="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg mb-5 text-sm text-red-700 dark:text-red-400">
            <ExclamationCircleIcon class="w-4 h-4 flex-shrink-0" />
            {{ error }}
          </div>
        </Transition>

        <form @submit.prevent="handleRegister" class="space-y-4" novalidate>
          <div>
            <label class="label" for="reg-name">Votre nom</label>
            <div class="relative">
              <UserIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input id="reg-name" v-model="form.name" type="text" class="input pl-9" placeholder="Awa Konaté" autocomplete="name" required />
            </div>
            <p v-if="fieldError('name')" class="text-xs text-red-600 mt-1">{{ fieldError('name') }}</p>
          </div>

          <div>
            <label class="label" for="reg-workshop">Nom de votre atelier</label>
            <div class="relative">
              <BuildingStorefrontIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input id="reg-workshop" v-model="form.workshop_name" type="text" class="input pl-9" placeholder="Atelier Awa Mobile" autocomplete="organization" required />
            </div>
            <p v-if="fieldError('workshop_name')" class="text-xs text-red-600 mt-1">{{ fieldError('workshop_name') }}</p>
          </div>

          <div>
            <label class="label" for="reg-email">Adresse email</label>
            <div class="relative">
              <EnvelopeIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input id="reg-email" v-model="form.email" type="email" class="input pl-9" placeholder="vous@exemple.com" autocomplete="email" required />
            </div>
            <p v-if="fieldError('email')" class="text-xs text-red-600 mt-1">{{ fieldError('email') }}</p>
          </div>

          <div>
            <label class="label" for="reg-password">Mot de passe</label>
            <div class="relative">
              <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                id="reg-password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="input pl-9 pr-10"
                placeholder="10 caractères minimum"
                autocomplete="new-password"
                required
              />
              <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                <EyeSlashIcon v-if="showPassword" class="w-4 h-4" />
                <EyeIcon v-else class="w-4 h-4" />
              </button>
            </div>
            <p class="text-xs text-gray-500 mt-1">Au moins 10 caractères, avec des lettres et des chiffres.</p>
            <p v-if="fieldError('password')" class="text-xs text-red-600 mt-1">{{ fieldError('password') }}</p>
          </div>

          <div>
            <label class="label" for="reg-confirm">Confirmer le mot de passe</label>
            <div class="relative">
              <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input id="reg-confirm" v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" class="input pl-9" autocomplete="new-password" required />
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-full" :disabled="registering" style="height:2.75rem">
            <span v-if="registering">Création en cours...</span>
            <span v-else>Créer mon compte</span>
          </button>
        </form>

        <p class="text-xs text-center text-gray-400 mt-6">
          Vous avez déjà un compte ?
          <router-link to="/login" class="text-blue-600 hover:underline dark:text-blue-400 font-medium">Se connecter</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  WrenchScrewdriverIcon, CheckCircleIcon, EnvelopeIcon, LockClosedIcon, UserIcon,
  BuildingStorefrontIcon, EyeIcon, EyeSlashIcon, ExclamationCircleIcon
} from '@heroicons/vue/24/outline'
import { useAuth } from '@/composables/useAuth'
import { useRepairs } from '@/composables/useRepairs'

const router = useRouter()
const { register, registerError, registerFieldErrors, registering } = useAuth()
const { fetchRepairs } = useRepairs()
const year = new Date().getFullYear()

const form = ref({ name: '', workshop_name: '', email: '', password: '', password_confirmation: '' })
const error = ref('')
const clientErrors = ref<Record<string, string>>({})
const showPassword = ref(false)

const features = [
  { text: 'Un espace privé pour votre atelier' },
  { text: 'Gestion complète des réparations' },
  { text: 'Diagnostic guidé étape par étape' },
  { text: 'Base de données de composants' },
  { text: 'Suivi des appareils et clients' },
]

function fieldError(field: string): string {
  return clientErrors.value[field] || registerFieldErrors.value[field] || ''
}

/** Contrôles rapides côté navigateur (le serveur revérifie toujours). */
function validate(): boolean {
  const errors: Record<string, string> = {}
  const f = form.value

  if (!f.name.trim()) errors.name = 'Votre nom est obligatoire.'
  if (!f.workshop_name.trim()) errors.workshop_name = "Le nom de l'atelier est obligatoire."
  if (!/^\S+@\S+\.\S+$/.test(f.email.trim())) errors.email = "L'adresse e-mail n'est pas valide."
  if (f.password.length < 10) errors.password = 'Le mot de passe doit contenir au moins 10 caractères.'
  else if (!/[A-Za-z\u00C0-\u024F]/.test(f.password) || !/\d/.test(f.password)) errors.password = 'Le mot de passe doit contenir au moins une lettre et un chiffre.'
  else if (f.password !== f.password_confirmation) errors.password = 'La confirmation du mot de passe ne correspond pas.'

  clientErrors.value = errors
  return Object.keys(errors).length === 0
}

async function handleRegister() {
  error.value = ''
  if (!validate()) {
    error.value = 'Certaines informations sont à corriger.'
    return
  }

  const ok = await register({ ...form.value, email: form.value.email.trim() })

  if (ok) {
    await fetchRepairs(true)
    router.push('/dashboard')
  } else {
    error.value = registerError.value || 'Inscription impossible. Réessayez.'
  }
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
