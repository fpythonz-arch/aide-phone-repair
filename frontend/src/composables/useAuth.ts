import { ref, computed } from 'vue'
import { authApi, repairApi } from '@/api/client'
import { resetRepairs } from '@/composables/useRepairs'
import type { RegisterPayload, SessionUser } from '@/types'

const session = ref<SessionUser | null>(null)

function loadSession(): SessionUser | null {
  try {
    const stored = localStorage.getItem('ap_session') || sessionStorage.getItem('ap_session')
    return stored ? JSON.parse(stored) : null
  } catch {
    return null
  }
}

// Initialiser au chargement
session.value = loadSession()

export function useAuth() {
  const isAuthenticated = computed(() => session.value !== null)
  const currentUser = computed(() => session.value)
  const loginError = ref<string | null>(null)
  const loggingIn = ref(false)
  const registerError = ref<string | null>(null)
  const registerFieldErrors = ref<Record<string, string>>({})
  const registering = ref(false)

  async function login(email: string, password: string, remember = false): Promise<boolean> {
    loggingIn.value = true
    loginError.value = null
    try {
      const { data } = await authApi.login(email, password)
      const { token, user } = data.data

      const sessionData: SessionUser = { ...user, loggedAt: new Date().toISOString(), remember }
      const storage = remember ? localStorage : sessionStorage

      storage.setItem('token', token)
      storage.setItem('ap_session', JSON.stringify(sessionData))
      session.value = sessionData

      return true
    } catch (err: any) {
      const status = err?.response?.status
      if (status === 401 || status === 422) {
        loginError.value = 'Email ou mot de passe incorrect.'
      } else if (status === 429) {
        loginError.value = 'Trop de tentatives. Patientez une minute avant de réessayer.'
      } else if (!err?.response) {
        loginError.value = 'Serveur injoignable. Vérifiez votre connexion internet.'
      } else {
        loginError.value = 'Erreur du serveur. Réessayez dans quelques instants.'
      }
      return false
    } finally {
      loggingIn.value = false
    }
  }

  /**
   * Inscription libre : crée un compte et un atelier privé, puis ouvre la session.
   * Aucune migration de données locales n'est faite ici : un nouveau compte démarre vide.
   */
  async function register(payload: RegisterPayload): Promise<boolean> {
    registering.value = true
    registerError.value = null
    registerFieldErrors.value = {}
    try {
      const { data } = await authApi.register(payload)
      const { token, user } = data.data

      const sessionData: SessionUser = { ...user, loggedAt: new Date().toISOString(), remember: false }
      sessionStorage.setItem('token', token)
      sessionStorage.setItem('ap_session', JSON.stringify(sessionData))
      session.value = sessionData

      return true
    } catch (err: any) {
      const status = err?.response?.status
      if (status === 422) {
        const errors: Record<string, string[]> = err.response?.data?.errors ?? {}
        for (const [field, messages] of Object.entries(errors)) {
          registerFieldErrors.value[field] = messages[0]
        }
        registerError.value = 'Certaines informations sont à corriger.'
      } else if (status === 429) {
        registerError.value = 'Trop de créations de compte depuis cette connexion. Réessayez dans quelques minutes.'
      } else if (!err?.response) {
        registerError.value = 'Serveur injoignable. Vérifiez votre connexion internet.'
      } else {
        registerError.value = 'Erreur du serveur. Réessayez dans quelques instants.'
      }
      return false
    } finally {
      registering.value = false
    }
  }

  function logout() {
    authApi.logout().catch(() => {})
    session.value = null
    resetRepairs()
    localStorage.removeItem('token')
    localStorage.removeItem('ap_session')
    sessionStorage.removeItem('token')
    sessionStorage.removeItem('ap_session')
  }

  /**
   * Met à jour le profil depuis le serveur (rôle, atelier, droits de plateforme).
   * Évite d'afficher un espace périmé si les droits ont changé depuis la dernière connexion.
   */
  async function syncFromServer() {
    if (!session.value) return
    try {
      const { data } = await authApi.me()
      const updated = { ...session.value, ...data.data } as SessionUser
      session.value = updated
      const storage = updated.remember ? localStorage : sessionStorage
      storage.setItem('ap_session', JSON.stringify(updated))
    } catch {
      // Un 401 est géré par l'intercepteur HTTP ; toute autre erreur est ignorée ici.
    }
  }

  function refreshSession() {
    session.value = loadSession()
  }

  /**
   * Importe une seule fois les réparations créées avant la migration vers l'API
   * (stockées en localStorage sous 'ap_repairs'). Ne supprime jamais la clé d'origine :
   * elle est renommée en backup une fois l'import confirmé côté serveur.
   */
  async function migrateLocalRepairsIfNeeded(): Promise<{ imported: number; skipped: number } | null> {
    try {
      const raw = localStorage.getItem('ap_repairs')
      if (!raw) return null

      const legacyRepairs = JSON.parse(raw)
      if (!Array.isArray(legacyRepairs) || legacyRepairs.length === 0) {
        localStorage.removeItem('ap_repairs')
        return null
      }

      const { data } = await repairApi.import(legacyRepairs)
      localStorage.setItem(`ap_repairs_migrated_${Date.now()}`, raw)
      localStorage.removeItem('ap_repairs')
      return { imported: data.data.imported, skipped: data.data.skipped }
    } catch {
      // Échec silencieux : la clé 'ap_repairs' reste intacte, l'import sera retenté au prochain login.
      return null
    }
  }

  return { isAuthenticated, currentUser, loginError, loggingIn, login, register, registerError, registerFieldErrors, registering, syncFromServer, logout, refreshSession, migrateLocalRepairsIfNeeded }
}
