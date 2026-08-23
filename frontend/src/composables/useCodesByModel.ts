import { ref } from 'vue'
//import { get } from '@/api/client'
import type { CodeByModel } from '@/types'
import apiClient from "@/api/client";

export function useCodesByModel() {
  const codes = ref<CodeByModel[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchCodes(params?: Record<string, unknown>) {
    loading.value = true
    error.value = null
    try {
      const data = await get<CodeByModel[]>('/codes-by-model', params)
      codes.value = data
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur'
    } finally {
      loading.value = false
    }
  }

  async function fetchCodesByBrand(brand: string) {
    return fetchCodes({ brand })
  }

  return {
    codes,
    loading,
    error,
    fetchCodes,
    fetchCodesByBrand,
  }
}