import axios from 'axios'
import type { AxiosInstance, AxiosResponse } from 'axios'
import type {
  ApiResponse,
  PaginatedResponse,
  Device,
  Symptom,
  Component,
  RepairGuide,
  ReplacementPart,
  SecretCode,
  SecretCodeDetail,
  EvolutionEvent,
  DiagnosticResult,
  Analysis,
  PanneType,
  PhoneEra,
  ProTool,
  Resource,
  CodeByModel,
  Repair,
  SessionUser,
} from '@/types'

// ============================================================
// CLIENT API - AIDE PHONE RÉPARATION
// ============================================================

const apiClient: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 15000,
})

// ── Intercepteur Requête ───────────────────────────────────
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token') || sessionStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    // Log en dev
    if (import.meta.env.DEV) {
      console.log(`[API] ${config.method?.toUpperCase()} ${config.url}`, config.params || config.data)
    }
    return config
  },
  (error) => Promise.reject(error)
)

// ── Intercepteur Réponse ───────────────────────────────────
apiClient.interceptors.response.use(
  (response: AxiosResponse<ApiResponse<unknown>>) => {
    if (import.meta.env.DEV) {
      console.log(`[API] ✅ ${response.config.url}`, response.data)
    }
    return response
  },
  (error) => {
    const status = error.response?.status
    const message = error.response?.data?.message || error.message

    console.error(`[API] ❌ ${status} - ${message}`, error.config?.url)

    if (status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('ap_session')
      sessionStorage.removeItem('token')
      sessionStorage.removeItem('ap_session')
      // Ne pas rediriger automatiquement, laisser le composant gérer
    }
    if (status === 422) {
      console.error('[API] Validation errors:', error.response?.data?.errors)
    }

    return Promise.reject(error)
  }
)

export default apiClient

// ============================================================
// SERVICES API PAR DOMAINE
// ============================================================

// ── HEALTH / PING ───────────────────────────────────────────
export const healthApi = {
  check: () => apiClient.get<ApiResponse<{ status: string; version: string }>>('/health'),
  ping: () => apiClient.get<ApiResponse<{ pong: boolean }>>('/ping'),
}

// ── AUTHENTIFICATION ─────────────────────────────────────────
export const authApi = {
  /** Connexion — retourne un token Bearer + les infos utilisateur */
  login: (email: string, password: string) =>
    apiClient.post<ApiResponse<{ token: string; user: SessionUser }>>('/auth/login', { email, password }),

  /** Déconnexion — révoque le token courant */
  logout: () => apiClient.post<ApiResponse<{ success: boolean }>>('/auth/logout'),

  /** Utilisateur actuellement authentifié */
  me: () => apiClient.get<ApiResponse<SessionUser>>('/auth/me'),
}

// ── RÉPARATIONS ───────────────────────────────────────────────
export const repairApi = {
  /** Liste des réparations (filtres optionnels) */
  getAll: (params?: { status?: string; priority?: string; search?: string; per_page?: number }) =>
    apiClient.get<ApiResponse<Repair[]>>('/repairs', { params }),

  /** Détail d'une réparation */
  getById: (id: string) => apiClient.get<ApiResponse<Repair>>('/repairs/' + id),

  /** Statistiques globales */
  stats: () =>
    apiClient.get<ApiResponse<{ total: number; active: number; pending: number; ready: number; completed: number; urgent: number }>>(
      '/repairs/stats'
    ),

  /** Créer une réparation */
  create: (data: Partial<Repair>) => apiClient.post<ApiResponse<Repair>>('/repairs', data),

  /** Modifier une réparation */
  update: (id: string, data: Partial<Repair>) => apiClient.put<ApiResponse<Repair>>('/repairs/' + id, data),

  /** Changer uniquement le statut */
  updateStatus: (id: string, status: string) =>
    apiClient.patch<ApiResponse<Repair>>('/repairs/' + id + '/status', { status }),

  /** Supprimer une réparation */
  delete: (id: string) => apiClient.delete<ApiResponse<{ success: boolean }>>('/repairs/' + id),

  /** Import ponctuel des réparations stockées localement (migration) */
  import: (repairs: Repair[]) =>
    apiClient.post<ApiResponse<{ imported: number; skipped: number; failed: number; errors: unknown[] }>>(
      '/repairs/import',
      { repairs }
    ),
}

// ── APPAREILS ───────────────────────────────────────────────
export const deviceApi = {
  /** Liste toutes les marques uniques */
  getBrands: () => apiClient.get<ApiResponse<string[]>>('/devices/brands'),

  /** Liste tous les appareils (paginé) */
  getAll: (params?: { page?: number; per_page?: number }) =>
    apiClient.get<PaginatedResponse<Device>>('/devices', { params }),

  /** Appareils par marque */
  getByBrand: (brand: string) =>
    apiClient.get<ApiResponse<Device[]>>('/devices/by-brand/' + encodeURIComponent(brand)),

  /** Recherche d'appareils */
  search: (query: string) =>
    apiClient.get<ApiResponse<Device[]>>('/devices/search', { params: { q: query } }),

  /** Détail d'un appareil par slug */
  getBySlug: (slug: string) =>
    apiClient.get<ApiResponse<Device>>('/devices/' + encodeURIComponent(slug)),
}

// ── DIAGNOSTIC ──────────────────────────────────────────────
export const diagnosticApi = {
  /** Initialise un nouveau diagnostic */
  initialize: (deviceInfo: { brand: string; model: string; imei?: string }) =>
    apiClient.post<ApiResponse<{ session_id: string; device: Device }>>('/diagnostic/initialize', deviceInfo),

  /** Analyse les symptômes sélectionnés */
  analyze: (data: { symptoms: number[]; device_id?: number; session_id?: string }) =>
    apiClient.post<ApiResponse<DiagnosticResult>>('/diagnostic/analyze', data),

  /** Valide les résultats du diagnostic */
  validateResults: (data: { session_id: string; confirmed_symptoms: number[]; notes?: string }) =>
    apiClient.post<ApiResponse<{ success: boolean; repair_plan: unknown }>>('/diagnostic/validate', data),

  /** Prochaines étapes suggérées */
  getNextSteps: (sessionId: string) =>
    apiClient.get<ApiResponse<{ steps: string[]; priority: string }>>('/diagnostic/next-steps', {
      params: { session_id: sessionId },
    }),

  /** Historique des diagnostics */
  getHistory: () => apiClient.get<ApiResponse<DiagnosticResult[]>>('/diagnostic/history'),
}

// ── SYMPTÔMES ───────────────────────────────────────────────
export const symptomApi = {
  /** Symptômes par appareil */
  getByDevice: (deviceId: number) =>
    apiClient.get<ApiResponse<Symptom[]>>('/symptoms/by-device/' + deviceId),

  /** Analyse d'un symptôme spécifique */
  analyze: (symptomId: number, deviceId?: number) =>
    apiClient.post<ApiResponse<Analysis>>('/symptoms/analyze', { symptom_id: symptomId, device_id: deviceId }),
}

// ── COMPOSANTS ──────────────────────────────────────────────
export const componentApi = {
  /** Liste tous les composants */
  getAll: () => apiClient.get<ApiResponse<Component[]>>('/components'),

  /** Catégories de composants */
  getCategories: () => apiClient.get<ApiResponse<string[]>>('/components/categories'),

  /** Composants par catégorie */
  getByCategory: (category: string) =>
    apiClient.get<ApiResponse<Component[]>>('/components/by-category/' + encodeURIComponent(category)),

  /** Mapping composants par symptômes */
  mapBySymptoms: (symptomIds: number[]) =>
    apiClient.post<ApiResponse<Component[]>>('/components/map', { symptoms: symptomIds }),

  /** Détail d'un composant */
  getById: (id: number) => apiClient.get<ApiResponse<Component>>('/components/' + id),

  /** Faisabilité de remplacement */
  getFeasibility: (id: number) =>
    apiClient.get<ApiResponse<{ feasible: boolean; difficulty: string; estimated_time: number }>>(
      '/components/' + id + '/feasibility'
    ),

  /** Alternatives de pièces */
  getAlternatives: (id: number) =>
    apiClient.get<ApiResponse<ReplacementPart[]>>('/components/' + id + '/alternatives'),

  /** Composants compatibles */
  getCompatible: (params: { brand?: string; model?: string }) =>
    apiClient.get<ApiResponse<Component[]>>('/components/compatible', { params }),
}

// ── GUIDES DE RÉPARATION ───────────────────────────────────
export const repairGuideApi = {
  /** Guides par composant */
  getByComponent: (componentId: number) =>
    apiClient.get<ApiResponse<RepairGuide[]>>('/repair-guides/by-component/' + componentId),

  /** Guide par ID */
  getById: (id: number) => apiClient.get<ApiResponse<RepairGuide>>('/repair-guides/' + id),
}

// ── PIÈCES DE RECHANGE ──────────────────────────────────────
export const replacementPartApi = {
  /** Pièces par composant */
  getByComponent: (componentId: number) =>
    apiClient.get<ApiResponse<ReplacementPart[]>>('/replacement-parts/by-component/' + componentId),
}

// ── CODES SECRETS ───────────────────────────────────────────
export const codeApi = {
  /** Tous les codes */
  getAll: () => apiClient.get<ApiResponse<SecretCode[]>>('/codes'),

  /** Catégories */
  getCategories: () => apiClient.get<ApiResponse<string[]>>('/codes/categories'),

  /** Codes populaires */
  getPopular: () => apiClient.get<ApiResponse<SecretCode[]>>('/codes/popular'),

  /** Statistiques */
  getStatistics: () => apiClient.get<ApiResponse<Record<string, number>>>('/codes/statistics'),

  /** Résoudre un code */
  resolve: (code: string, brand?: string, model?: string) =>
    apiClient.post<ApiResponse<SecretCodeDetail>>('/codes/resolve', { code, brand, model }),

  /** Valider la sécurité */
  validateSafety: (code: string) =>
    apiClient.post<ApiResponse<{ safe: boolean; warnings: string[] }>>('/codes/validate', { code }),

  /** Codes par marque */
  getByBrand: (brand: string) =>
    apiClient.get<ApiResponse<CodeByModel[]>>('/codes/by-brand/' + encodeURIComponent(brand)),

  /** Codes par catégorie */
  getByCategory: (category: string) =>
    apiClient.get<ApiResponse<SecretCode[]>>('/codes/by-category/' + encodeURIComponent(category)),

  /** Détail d'un code */
  getById: (id: number) => apiClient.get<ApiResponse<SecretCode>>('/codes/' + id),
}

// ── ÉVOLUTION ───────────────────────────────────────────────
export const evolutionApi = {
  /** Tous les événements */
  getAll: () => apiClient.get<ApiResponse<EvolutionEvent[]>>('/evolution'),

  /** Créer un événement */
  create: (data: Partial<EvolutionEvent>) =>
    apiClient.post<ApiResponse<EvolutionEvent>>('/evolution', data),

  /** Tendances */
  getTrends: () => apiClient.get<ApiResponse<Record<string, number>>>('/evolution/trends'),

  /** Timeline */
  getTimeline: () => apiClient.get<ApiResponse<EvolutionEvent[]>>('/evolution/timeline'),

  /** Stats par symptôme */
  getSymptomStats: (symptomId: number) =>
    apiClient.get<ApiResponse<{ count: number; severity_breakdown: Record<string, number> }>>(
      '/evolution/symptom/' + symptomId + '/stats'
    ),

  /** Détail */
  getById: (id: number) => apiClient.get<ApiResponse<EvolutionEvent>>('/evolution/' + id),

  /** Modifier */
  update: (id: number, data: Partial<EvolutionEvent>) =>
    apiClient.put<ApiResponse<EvolutionEvent>>('/evolution/' + id, data),

  /** Supprimer */
  delete: (id: number) => apiClient.delete<ApiResponse<{ success: boolean }>>('/evolution/' + id),
}

// ── OUTILS ──────────────────────────────────────────────────
export const toolApi = {
  /** Outils pour réparation */
  getForRepair: () => apiClient.get<ApiResponse<ProTool[]>>('/tools/for-repair'),

  /** Kit de démarrage */
  getStarterKit: () => apiClient.get<ApiResponse<ProTool[]>>('/tools/starter-kit'),

  /** Vérifier l'inventaire */
  checkInventory: (toolIds: number[]) =>
    apiClient.post<ApiResponse<{ available: number[]; unavailable: number[] }>>('/tools/check-inventory', {
      tools: toolIds,
    }),

  /** Tous les outils */
  getAll: () => apiClient.get<ApiResponse<ProTool[]>>('/tools'),

  /** Exécuter un outil */
  execute: (slug: string, params?: Record<string, unknown>) =>
    apiClient.post<ApiResponse<unknown>>('/tools/' + slug + '/execute', params),
}

// ── DÉPANNAGE ───────────────────────────────────────────────
export const depannageApi = {
  getCategories: () => apiClient.get<ApiResponse<Category[]>>('/depannage/categories'),
  getByType: (type: string) =>
    apiClient.get<ApiResponse<Guide>>('/depannage/' + encodeURIComponent(type)),
}
// ── RESSOURCES ──────────────────────────────────────────────
export const resourceApi = {
  /** Toutes les ressources */
  getAll: () => apiClient.get<ApiResponse<Resource[]>>('/resources'),

  /** Par type */
  getByType: (type: string) =>
    apiClient.get<ApiResponse<Resource[]>>('/resources/by-type/' + encodeURIComponent(type)),

  /** Par catégorie */
  getByCategory: (category: string) =>
    apiClient.get<ApiResponse<Resource[]>>('/resources/by-category/' + encodeURIComponent(category)),
}

// ============================================================
// EXPORT UNIQUE
// ============================================================
export const api = {
  health: healthApi,
  auth: authApi,
  repairs: repairApi,
  devices: deviceApi,
  diagnostic: diagnosticApi,
  symptoms: symptomApi,
  components: componentApi,
  repairGuides: repairGuideApi,
  replacementParts: replacementPartApi,
  codes: codeApi,
  evolution: evolutionApi,
  tools: toolApi,
  depannage: depannageApi,
  resources: resourceApi,
}

export type Api = typeof api