import { ref } from 'vue'
import { get } from '@/api/client'
import type { ProTool } from '@/types'
import apiClient from "@/api/client";

export function useProTools() {
  const tools = ref<ProTool[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchTools(params?: Record<string, unknown>) {
    loading.value = true
    error.value = null
    try {
      const data = await get<ProTool[]>('/pro-tools', params)
      tools.value = data
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur'
    } finally {
      loading.value = false
    }
  }

  async function fetchToolsByCategory(category: string) {
    return fetchTools({ category })
  }

  return {
    tools,
    loading,
    error,
    fetchTools,
    fetchToolsByCategory,
  }
}