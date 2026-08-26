import { ref, computed } from 'vue'

interface Session {
  name: string
  email: string
  role: string
  loggedAt: string
  remember: boolean
}

const session = ref<Session | null>(null)

function loadSession(): Session | null {
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

  function logout() {
    session.value = null
    localStorage.removeItem('ap_session')
    sessionStorage.removeItem('ap_session')
  }

  function refreshSession() {
    session.value = loadSession()
  }

  return { isAuthenticated, currentUser, logout, refreshSession }
}
