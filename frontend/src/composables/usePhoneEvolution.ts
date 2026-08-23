import { ref } from 'vue'
import { get } from '@/api/client'
import type { PhoneEra } from '@/types'
import apiClient from "@/api/client";

export function usePhoneEvolution() {
  const eras = ref<PhoneEra[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchEras() {
    loading.value = true
    error.value = null
    try {
      const data = await get<PhoneEra[]>('/phone-evolution')
      eras.value = data
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur'
    } finally {
      loading.value = false
    }
  }

  async function fetchEraById(id: number) {
    loading.value = true
    error.value = null
    try {
      return await get<PhoneEra>(`/phone-evolution/${id}`)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    eras,
    loading,
    error,
    fetchEras,
    fetchEraById,
  }
}