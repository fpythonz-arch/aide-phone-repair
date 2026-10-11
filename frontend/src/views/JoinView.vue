<template>
  <div class="login-shell">

    <!-- Panneau gauche — branding -->
    <div class="login-brand">
      <div class="login-brand-content">
        <div class="login-logo">
          <WrenchScrewdriverIcon class="w-7 h-7 text-white" />
        </div>
        <h1 class="login-brand-title">AidePhone</h1>
        <p class="login-brand-subtitle">Rejoignez l'équipe d'un atelier et travaillez sur ses réparations.</p>

        <div class="login-features">
          <div v-for="f in features" :key="f.text" class="login-feature-item">
            <CheckCircleIcon class="w-4 h-4 text-blue-300 flex-shrink-0" />
            <span>{{ f.text }}</span>
          </div>
        </div>
      </div>
      <p class="login-brand-footer">© {{ year }} AidePhone · Conçu pour les techniciens africains</p>
    </div>

    <!-- Panneau droit -->
    <div class="login-form-panel">
      <div class="login-form-container">

        <div class="login-logo-mobile">
          <div class="login-logo" style="width:2.5rem;height:2.5rem">
            <WrenchScrewdriverIcon class="w-5 h-5 text-white" />
          </div>
          <span class="text-xl font-bold text-gray-900 dark:text-white">AidePhone</span>
        </div>

        <p v-if="state === 'loading'" class="text-sm text-gray-500">Vérification de l'invitation…</p>

        <!-- Lien invalide, expiré, utilisé ou annulé -->
        <div v-else-if="state === 'invalid'">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Invitation indisponible</h2>
          <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">{{ invalidMessage }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">
            Demandez un nouveau lien au responsable de l'atelier.
          </p>
          <router-link to="/login" class="btn btn-primary mt-6 inline-flex">Aller à la connexion</router-link>
        </div>

        <div v-else-if="invitation">
          <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Rejoindre l'atelier</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
              Vous êtes invité(e) à rejoindre <strong>{{ invitation.workshop_name }}</strong>
              en tant que <strong>{{ roleLabelOf(invitation.role) }}</strong>.
            </p>
          </div>

          <!-- Déjà connecté avec un autre compte -->
          <div v-if="isAuthenticated" class="p-3 mb-5 rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-800 text-sm text-amber-800 dark:text-amber-300">
            Vous êtes actuellement connecté(e) en tant que <strong>{{ currentUser?.name }}</strong>.
            Pour accepter cette invitation, déconnectez-vous d'abord.
            <button type="button" class="btn btn-primary mt-3 w-full" @click="logout()">Se déconnecter et continuer</button>
          </div>

          <template v-else>
            <Transition name="fade-overlay">
              <div v-if="error" class="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg mb-5 text-sm text-red-700 dark:text-red-400">
                <ExclamationCircleIcon class="w-4 h-4 flex-shrink-0" />
                {{ error }}
              </div>
            </Transition>

            <form @submit.prevent="handleJoin" class="space-y-4" novalidate>
              <div>
                <label class="label" for="join-name">Votre nom</label>
                <div class="relative">
                  <UserIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input id="join-name" v-model="form.name" type="text" class="input pl-9" placeholder="Ibrahim Koné" autocomplete="name" required />
                </div>
                <p v-if="fieldError('name')" class="text-xs text-red-600 mt-1">{{ fieldError('name') }}</p>
              </div>

              <div>
                <label class="label" for="join-email">Adresse email</label>
                <div class="relative">
                  <EnvelopeIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input id="join-email" v-model="form.email" type="email" class="input pl-9" :readonly="!!invitation.email" placeholder="vous@exemple.com" autocomplete="email" required />
                </div>
                <p v-if="invitation.email" class="text-xs text-gray-500 mt-1">Cette invitation est réservée à cette adresse.</p>
                <p v-if="fieldError('email')" class="text-xs text-red-600 mt-1">{{ fieldError('email') }}</p>
              </div>

              <div>
                <label class="label" for="join-password">Mot de passe</label>
                <div class="relative">
                  <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input
                    id="join-password"
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
                <label class="label" for="join-confirm">Confirmer le mot de passe</label>
                <div class="relative">
                  <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input id="join-confirm" v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" class="input pl-9" autocomplete="new-password" required />
                </div>
              </div>

              <button type="submit" class="btn btn-primary w-full" :disabled="registering" style="height:2.75rem">
                <span v-if="registering">Création en cours...</span>
                <span v-else>Rejoindre l'atelier</span>
              </button>
            </form>

            <p class="text-xs text-center text-gray-400 mt-6">
              Vous avez déjà un compte ?
              <router-link to="/login" class="text-blue-600 hover:underline dark:text-blue-400 font-medium">Se connecter</router-link>
            </p>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  WrenchScrewdriverIcon, CheckCircleIcon, EnvelopeIcon, LockClosedIcon, UserIcon,
  EyeIcon, EyeSlashIcon, ExclamationCircleIcon
} from '@heroicons/vue/24/outline'
import { invitationApi } from '@/api/client'
import { useAuth } from '@/composables/useAuth'
import { useRepairs } from '@/composables/useRepairs'
import { roleLabelOf } from '@/composables/usePermissions'
import type { InvitationInfo } from '@/types'

const route = useRoute()
const router = useRouter()
const token = String(route.params.token ?? '')

const { joinWorkshop, registerError, registerFieldErrors, registering, isAuthenticated, currentUser, logout } = useAuth()
const { fetchRepairs } = useRepairs()
const year = new Date().getFullYear()

const state = ref<'loading' | 'invalid' | 'ready'>('loading')
const invalidMessage = ref('')
const invitation = ref<InvitationInfo | null>(null)

const form = ref({ name: '', email: '', password: '', password_confirmation: '' })
const error = ref('')
const clientErrors = ref<Record<string, string>>({})
const showPassword = ref(false)

const features = [
  { text: 'Les réparations et clients de votre atelier' },
  { text: 'Diagnostic guidé étape par étape' },
  { text: 'Base de données de composants' },
]

function fieldError(field: string): string {
  return clientErrors.value[field] || registerFieldErrors.value[field] || ''
}

/** Contrôles rapides côté navigateur (le serveur revérifie toujours). */
function validate(): boolean {
  const errors: Record<string, string> = {}
  const f = form.value

  if (!f.name.trim()) errors.name = 'Votre nom est obligatoire.'
  if (!/^\S+@\S+\.\S+$/.test(f.email.trim())) errors.email = "L'adresse e-mail n'est pas valide."
  if (f.password.length < 10) errors.password = 'Le mot de passe doit contenir au moins 10 caractères.'
  else if (!/[A-Za-z\u00C0-\u024F]/.test(f.password) || !/\d/.test(f.password)) errors.password = 'Le mot de passe doit contenir au moins une lettre et un chiffre.'
  else if (f.password !== f.password_confirmation) errors.password = 'La confirmation du mot de passe ne correspond pas.'

  clientErrors.value = errors
  return Object.keys(errors).length === 0
}

async function handleJoin() {
  error.value = ''
  if (!validate()) {
    error.value = 'Certaines informations sont à corriger.'
    return
  }

  const ok = await joinWorkshop(token, { ...form.value, email: form.value.email.trim() })

  if (ok) {
    await fetchRepairs(true)
    router.push('/dashboard')
  } else {
    error.value = registerError.value || 'Impossible de rejoindre l\'atelier. Réessayez.'
  }
}

onMounted(async () => {
  try {
    const { data } = await invitationApi.check(token)
    invitation.value = data.data
    form.value.email = data.data.email ?? ''
    state.value = 'ready'
  } catch (err: any) {
    invalidMessage.value = err?.response?.data?.message
      ?? (err?.response ? "Cette invitation n'est pas valable." : 'Serveur injoignable. Vérifiez votre connexion internet.')
    state.value = 'invalid'
  }
})
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
