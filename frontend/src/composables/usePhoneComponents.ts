import { ref } from 'vue'
import { get } from '@/api/client'
import type { PhoneComponent } from '@/types'
import apiClient from "@/api/client";

export function usePhoneComponents() {
  const components = ref<PhoneComponent[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchComponents(params?: Record<string, unknown>) {
    loading.value = true
    error.value = null
    try {
      const data = await get<PhoneComponent[]>('/phone-components', params)
      components.value = data
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur'
    } finally {
      loading.value = false
    }
  }

  async function fetchComponentById(id: number) {
    loading.value = true
    error.value = null
    try {
      return await get<PhoneComponent>(`/phone-components/${id}`)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    components,
    loading,
    error,
    fetchComponents,
    fetchComponentById,
  }
}