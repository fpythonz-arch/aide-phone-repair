import { ref, computed } from 'vue'
import type { Repair, RepairStatus } from '@/types'

const STORAGE_KEY = 'ap_repairs'
const repairs = ref<Repair[]>([])
let initialized = false

function load(): Repair[] {
  try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]') } catch { return [] }
}
function save(data: Repair[]) { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)) }
function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 7) }
function nextNumber() {
  const y = new Date().getFullYear()
  const n = load().filter(r => r.number?.startsWith(`REP-${y}`)).length + 1
  return `REP-${y}-${String(n).padStart(3, '0')}`
}

export function useRepairs() {
  if (!initialized) { repairs.value = load(); initialized = true }

  const stats = computed(() => ({
    total:     repairs.value.length,
    active:    repairs.value.filter(r => ['in_progress', 'diagnosing', 'testing'].includes(r.status)).length,
    pending:   repairs.value.filter(r => ['new', 'received', 'waiting_quote', 'waiting_parts'].includes(r.status)).length,
    ready:     repairs.value.filter(r => r.status === 'ready').length,
    completed: repairs.value.filter(r => r.status === 'delivered').length,
    urgent:    repairs.value.filter(r => r.priority === 'urgent').length,
  }))

  function getById(id: string) { return repairs.value.find(r => r.id === id) }

  function createRepair(data: Omit<Repair, 'id' | 'number' | 'created_at' | 'updated_at'>): Repair {
    const now = new Date().toISOString()
    const r: Repair = { ...data, id: uid(), number: nextNumber(), created_at: now, updated_at: now, currency: data.currency || 'FCFA', status: data.status || 'new', priority: data.priority || 'normal' }
    repairs.value = [r, ...repairs.value]
    save(repairs.value)
    return r
  }

  function updateRepair(id: string, data: Partial<Repair>): Repair | null {
    const i = repairs.value.findIndex(r => r.id === id)
    if (i === -1) return null
    const updated = { ...repairs.value[i], ...data, updated_at: new Date().toISOString() }
    repairs.value = [...repairs.value.slice(0, i), updated, ...repairs.value.slice(i + 1)]
    save(repairs.value)
    return updated
  }

  function updateStatus(id: string, status: RepairStatus) { return updateRepair(id, { status }) }

  function deleteRepair(id: string) {
    repairs.value = repairs.value.filter(r => r.id !== id)
    save(repairs.value)
  }

  function filterRepairs(f: { status?: string; priority?: string; search?: string }) {
    let r = repairs.value
    if (f.status && f.status !== 'all') r = r.filter(x => x.status === f.status)
    if (f.priority && f.priority !== 'all') r = r.filter(x => x.priority === f.priority)
    if (f.search) {
      const q = f.search.toLowerCase()
      r = r.filter(x => [x.client_name, x.client_phone, x.device_brand, x.device_model, x.number].some(v => v?.toLowerCase().includes(q)))
    }
    const ord: Record<string, number> = { urgent: 0, high: 1, normal: 2, low: 3 }
    return [...r].sort((a, b) => (ord[a.priority] ?? 2) - (ord[b.priority] ?? 2) || new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
  }

  return { repairs: computed(() => repairs.value), stats, getById, createRepair, updateRepair, updateStatus, deleteRepair, filterRepairs }
}
