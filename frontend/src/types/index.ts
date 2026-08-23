// ==================== TYPES DE BASE ====================

export type DeviceType = 'smartphone' | 'tablette'
export type DeviceOS = 'android' | 'ios'
export type DeviceBrand = 'samsung' | 'apple' | 'xiaomi' | 'huawei' | 'google' | 'oneplus' | 'sony' | 'lg' | 'autre'

export type RepairCategory = 'hardware' | 'software'

export type SeverityLevel = 'low' | 'medium' | 'high' | 'critical'

// ==================== APPAREIL ====================

export interface Device {
  id: string
  type: DeviceType
  os: DeviceOS
  brand: DeviceBrand
  model: string
  name: string
}

// ==================== SYMPTÔME / PANNE ====================

export interface Symptom {
  id: string
  category: RepairCategory
  title: string
  description: string
  icon: string
  commonIssues: string[]
  severity: SeverityLevel
}

// ==================== SOLUTION ====================

export interface Solution {
  id: string
  type: 'quick' | 'hardware' | 'advanced'
  title: string
  description: string
  steps: string[]
  estimatedTime: string
  difficulty: 'easy' | 'medium' | 'hard' | 'expert'
  toolsNeeded?: string[]
  partsNeeded?: ReplacementPart[]
  videoUrl?: string
  ifixitUrl?: string
}

// ==================== PIÈCE DE RECHANGE ====================

export interface ReplacementPart {
  id: number;
  component_id: number;
  name: string;
  part_number: string;
  price: number;
  stock: number;
  compatible_models: string[];
}

// ==================== GUIDE DE RÉPARATION ====================

export interface RepairGuide {
  id: number;
  component_id: number;
  title: string;
  steps: string[];
  tools_needed: string[];
  estimated_time: number;
  difficulty: string;
}

export interface RepairStep {
  id: string
  order: number
  title: string
  description: string
  imageUrl?: string
  caution?: string
}

// ==================== RÉSULTAT DIAGNOSTIC ====================

export interface DiagnosticResult {
  device: Device
  selectedSymptoms: Symptom[]
  solutions: Solution[]
  recommendedGuides: RepairGuide[]
  estimatedCost: {
    min: number
    max: number
    currency: string
  }
  severity: SeverityLevel
  canSelfRepair: boolean
}

// ==================== ÉVÉNEMENT ÉVOLUTION ====================

export interface EvolutionEvent {
  id: string
  deviceId: string
  eventType: 'diagnostic' | 'repair' | 'maintenance' | 'upgrade'
  description: string
  date: string
  technician?: string
  cost?: number
  status: 'completed' | 'pending' | 'cancelled'
}

// ==================== ÉTAT DU WIZARD ====================

export interface WizardState {
  currentStep: number
  totalSteps: number
  device: Device | null
  selectedCategory: RepairCategory | null
  selectedSymptoms: Symptom[]
  diagnosticResult: DiagnosticResult | null
  isLoading: boolean
  error: string | null
}

// ==================== API RESPONSES ====================

export interface ApiResponse<T> {
  success: boolean
  data: T
  message?: string
  errors?: Record<string, string[]>
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface Component {
  id: number;
  name: string;
  slug: string;
  category: string;
  description: string;
  common_issues: string[];
  repair_difficulty: 'facile' | 'moyen' | 'difficile' | 'très_difficile';
  average_repair_cost: number;
  is_replaceable: boolean;
}