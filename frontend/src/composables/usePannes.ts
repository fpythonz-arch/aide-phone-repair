import { ref } from 'vue'
import { get } from '@/api/client'
import type { PanneType } from '@/types'
import apiClient from "@/api/client";

export function usePannes() {
  const pannes = ref<PanneType[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchPannes(params?: Record<string, unknown>) {
    loading.value = true
    error.value = null
    try {
      const data = await get<PanneType[]>('/pannes', params)
      pannes.value = data
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur'
    } finally {
      loading.value = false
    }
  }

  async function fetchPanneById(id: number) {
    loading.value = true
    error.value = null
    try {
      return await get<PanneType>(`/pannes/${id}`)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchPannesByCategory(category: string) {
    return fetchPannes({ category })
  }

  async function searchPannes(query: string) {
    return fetchPannes({ search: query })
  }

  return {
    pannes,
    loading,
    error,
    fetchPannes,
    fetchPanneById,
    fetchPannesByCategory,
    searchPannes,
  }
}