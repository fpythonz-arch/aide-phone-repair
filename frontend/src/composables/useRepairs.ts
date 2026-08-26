import { ref, computed } from 'vue'
import { repairApi } from '@/api/client'
import type { Repair, RepairStatus } from '@/types'

const repairs = ref<Repair[]>([])
const loading = ref(false)
const loaded = ref(false)

export function useRepairs() {
  const stats = computed(() => ({
    total:     repairs.value.length,
    active:    repairs.value.filter(r => ['in_progress', 'diagnosing', 'testing'].includes(r.status)).length,
    pending:   repairs.value.filter(r => ['new', 'received', 'waiting_quote', 'waiting_parts'].includes(r.status)).length,
    ready:     repairs.value.filter(r => r.status === 'ready').length,
    completed: repairs.value.filter(r => r.status === 'delivered').length,
    urgent:    repairs.value.filter(r => r.priority === 'urgent').length,
  }))

  async function fetchRepairs(force = false) {
    if (loaded.value && !force) return repairs.value
    loading.value = true
    try {
      const { data } = await repairApi.getAll({ per_page: 500 })
      repairs.value = data.data
      loaded.value = true
      return repairs.value
    } catch {
      return repairs.value
    } finally {
      loading.value = false
    }
  }

  async function fetchRepairById(id: string) {
    loading.value = true
    try {
      const { data } = await repairApi.getById(id)
      upsert(data.data)
      return data.data
    } catch {
      return null
    } finally {
      loading.value = false
    }
  }

  function upsert(repair: Repair) {
    const i = repairs.value.findIndex(r => r.id === repair.id)
    if (i === -1) repairs.value = [repair, ...repairs.value]
    else repairs.value = [...repairs.value.slice(0, i), repair, ...repairs.value.slice(i + 1)]
  }

  function getById(id: string) {
    return repairs.value.find(r => r.id === id)
  }

  async function createRepair(data: Omit<Repair, 'id' | 'number' | 'created_at' | 'updated_at'>): Promise<Repair> {
    const { data: res } = await repairApi.create(data)
    upsert(res.data)
    return res.data
  }

  async function updateRepair(id: string, data: Partial<Repair>): Promise<Repair | null> {
    const { data: res } = await repairApi.update(id, data)
    upsert(res.data)
    return res.data
  }

  async function updateStatus(id: string, status: RepairStatus) {
    const { data: res } = await repairApi.updateStatus(id, status)
    upsert(res.data)
    return res.data
  }

  async function deleteRepair(id: string) {
    await repairApi.delete(id)
    repairs.value = repairs.value.filter(r => r.id !== id)
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

  return {
    repairs: computed(() => repairs.value),
    loading: computed(() => loading.value),
    stats,
    fetchRepairs,
    fetchRepairById,
    getById,
    createRepair,
    updateRepair,
    updateStatus,
    deleteRepair,
    filterRepairs,
  }
}
