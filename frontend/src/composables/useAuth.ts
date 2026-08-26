import { ref, computed } from 'vue'
import { authApi, repairApi } from '@/api/client'
import type { SessionUser } from '@/types'

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
      loginError.value = err.response?.data?.message || 'Email ou mot de passe incorrect.'
      return false
    } finally {
      loggingIn.value = false
    }
  }

  function logout() {
    authApi.logout().catch(() => {})
    session.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('ap_session')
    sessionStorage.removeItem('token')
    sessionStorage.removeItem('ap_session')
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

  return { isAuthenticated, currentUser, loginError, loggingIn, login, logout, refreshSession, migrateLocalRepairsIfNeeded }
}
