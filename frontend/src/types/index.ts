// ============================================================
// AIDE PHONE — Types TypeScript centralisés
// ============================================================

export type DeviceType = 'smartphone' | 'tablette' | 'laptop' | 'ordinateur' | 'console' | 'autre'
export type DeviceOS = 'android' | 'ios' | 'windows' | 'macos' | 'linux' | 'autre'
export type DeviceBrand = 'samsung' | 'apple' | 'xiaomi' | 'huawei' | 'google' | 'oneplus' | 'sony' | 'infinix' | 'tecno' | 'motorola' | 'oppo' | 'realme' | 'lg' | 'autre'
export type RepairCategory = 'hardware' | 'quick' | 'advanced'
export type SeverityLevel = 'low' | 'medium' | 'high' | 'critical'
export type DiagnosticStep = 'device' | 'symptom' | 'analysis' | 'result' | 'validation'
export type RepairStatus = 'new' | 'received' | 'diagnosing' | 'waiting_quote' | 'quote_accepted' | 'in_progress' | 'waiting_parts' | 'testing' | 'ready' | 'delivered' | 'cancelled'
export type RepairPriority = 'low' | 'normal' | 'high' | 'urgent'

export interface Device {
  id: string | number
  type?: DeviceType
  os?: DeviceOS
  brand: string
  model: string
  name?: string
  slug?: string
  release_year?: number
  specifications?: Record<string, string>
  image_url?: string
}

export interface Symptom {
  id: number | string
  name?: string
  title?: string
  description: string
  category: string
  severity_level?: number
  severity?: SeverityLevel
  icon?: string
  common_devices?: string[]
  keywords?: string[]
  commonIssues?: string[]
  common_causes?: string[]
}

export interface Component {
  id: number
  name: string
  slug: string
  category: string
  sub_category?: string
  description: string
  image_url?: string | null
  common_failures?: string[] | string
  replacement_difficulty: number
  price_range?: { min: number; max: number; currency: string } | string | null
  technical_specs?: Record<string, string> | string | null
  compatible_devices?: string[] | string
  availability?: 'in_stock' | 'out_of_stock' | 'special_order' | 'not_available'
  common_issues?: string[]
  repair_difficulty?: string
  average_repair_cost?: number
  is_replaceable?: boolean
}

export interface ReplacementPart {
  id: number
  component_id: number
  name: string
  part_number?: string
  sku?: string
  price: number
  currency?: string
  stock?: number
  supplier?: string
  compatible_models?: string[]
}

export interface RepairGuide {
  id: number
  component_id: number
  symptom_id?: number
  title: string
  description?: string
  steps: string[] | RepairStep[]
  tools_needed?: string[]
  required_tools?: string[]
  estimated_time?: number
  difficulty?: string
  difficulty_level?: number
}

export interface RepairStep {
  id: string | number
  order: number
  title: string
  description: string
  imageUrl?: string
  image_url?: string
  caution?: string
  warning?: string
  estimated_time?: number
  tools?: string[]
}

export interface Solution {
  id: string | number
  type?: 'quick' | 'hardware' | 'advanced'
  title: string
  description: string
  steps?: string[]
  estimatedTime?: string
  difficulty: 'easy' | 'medium' | 'hard' | 'expert'
  toolsNeeded?: string[]
  partsNeeded?: ReplacementPart[]
  videoUrl?: string
  estimatedCost?: number
  needsReplacement?: boolean
  replacementPart?: string
  guideUrl?: string | null
}

export interface DiagnosticResult {
  device?: Device
  selectedSymptoms?: Symptom[]
  symptoms?: Symptom[]
  solutions?: Solution[]
  recommendedGuides?: RepairGuide[]
  repair_guides?: RepairGuide[]
  components?: Component[]
  confidence?: number
  recommendations?: string[]
  estimatedCost?: { min: number; max: number; currency: string }
  severity: SeverityLevel
  canSelfRepair?: boolean
}

export interface Analysis {
  id?: string
  symptomId?: number
  component?: string
  probability?: number
  description?: string
}

export interface EvolutionEvent {
  id?: string | number
  device_id?: string | number
  deviceId?: string
  eventType?: 'diagnostic' | 'repair' | 'maintenance' | 'upgrade'
  event_type?: string
  description: string
  date?: string
  created_at?: string
  technician?: string
  cost?: number
  status?: 'completed' | 'pending' | 'cancelled'
  severity?: SeverityLevel
  metadata?: Record<string, unknown>
}

export interface Repair {
  id: string
  number: string
  client_id?: string | null
  device_id?: number | null
  technician_id?: number | null
  client_name: string
  client_phone: string
  client_email?: string
  device_brand: string
  device_model: string
  device_imei?: string
  problem_description: string
  diagnosis?: string
  technician?: string
  status: RepairStatus
  priority: RepairPriority
  cost_estimate?: number
  cost_final?: number
  currency?: string
  parts_used?: string[]
  notes?: string
  created_at: string
  updated_at: string
  estimated_ready?: string
  warranty_days?: number
}

export interface SessionUser {
  id: number
  name: string
  email: string
  role: string
  loggedAt?: string
  remember?: boolean
}

export interface Client {
  id: string
  name: string
  phone: string
  email?: string
  address?: string
  notes?: string
  created_at: string
}

export interface SecretCode {
  id: number
  code: string
  name: string
  description?: string
  functionality?: string
  compatible_brands?: string[] | string
  compatible_models?: string[] | string
  category: string
  instructions?: string[] | string
  warnings?: string[] | string
  is_verified?: boolean
  source?: string
  user_rating?: number
  brand?: string
  model?: string
}

export interface SecretCodeDetail extends SecretCode {
  detail?: string
  result?: string
}

export interface CodeByModel {
  model: string
  brand: string
  codes: SecretCode[]
}

export interface ProTool {
  id?: number
  name: string
  slug?: string
  description?: string
  category?: string
  icon?: string
  is_active?: boolean
  estimated_price?: number | string
}

export interface Resource {
  id: number | string
  title: string
  type: 'video' | 'article' | 'forum' | 'shop' | 'tool' | 'pdf' | 'guide'
  url?: string
  description?: string
  thumbnail?: string
  duration?: string
  author?: string
  date?: string
  category?: string
  level?: 'beginner' | 'intermediate' | 'advanced'
  bookmarked?: boolean
}

export interface PanneType {
  id: string | number
  slug: string
  name: string
  icon: string
  description?: string
  type?: 'hardware' | 'software'
  color?: string
  difficulty?: 'easy' | 'medium' | 'hard' | 'expert'
}

export interface PhoneEra {
  id: string
  name: string
  period: string
  description?: string
  devices?: Device[]
}

export interface Toast {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  message: string
  duration?: number
}

export interface DeviceInfo {
  brand: string
  model: string
  imei?: string
}

export interface ResourceFilters {
  type?: string
  category?: string
  level?: string
  search?: string
}

export interface ApiResponse<T> {
  success?: boolean
  data: T
  message?: string
  errors?: Record<string, string[]>
  brands?: string[]
  devices?: T
  results?: T
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
