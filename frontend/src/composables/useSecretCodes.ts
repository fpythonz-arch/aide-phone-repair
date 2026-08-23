// ============================================================
// COMPOSABLE useSecretCodes - AIDE PHONE RÉPARATION
// ============================================================

import { computed } from 'vue'
import { useCodeStore, useUiStore } from '@/stores'
import { api } from '@/api/client'
import type { SecretCode, SecretCodeDetail, CodeByModel } from '@/types'

export function useSecretCodes() {
  const codeStore = useCodeStore()
  const uiStore = useUiStore()

  // ── Getters ─────────────────────────────────────────
  const codes = computed(() => codeStore.codes)
  const codesByModel = computed(() => codeStore.codesByModel)
  const categories = computed(() => codeStore.categories)
  const loading = computed(() => codeStore.isLoading)

  // ── Actions ─────────────────────────────────────────
  const fetchCodes = async () => {
    try {
      await codeStore.fetchAllCodes()
    } catch (err: any) {
      uiStore.showError('Erreur lors du chargement des codes secrets')
    }
  }

  const fetchCodesByBrand = async (brand: string) => {
    try {
      await codeStore.fetchCodesByBrand(brand)
    } catch (err: any) {
      uiStore.showError(`Erreur lors du chargement des codes pour ${brand}`)
    }
  }

  const fetchCategories = async () => {
    try {
      await codeStore.fetchCategories()
    } catch (err: any) {
      uiStore.showError('Erreur lors du chargement des catégories')
    }
  }

  const resolveCode = async (code: string, brand?: string, model?: string): Promise<SecretCodeDetail | null> => {
    try {
      const response = await api.codes.resolve(code, brand, model)
      uiStore.showSuccess('Code résolu avec succès')
      return response.data.data
    } catch (err: any) {
      uiStore.showError('Impossible de résoudre ce code')
      return null
    }
  }

  const validateCodeSafety = async (code: string): Promise<{ safe: boolean; warnings: string[] } | null> => {
    try {
      const response = await api.codes.validateSafety(code)
      return response.data.data
    } catch (err: any) {
      uiStore.showError('Erreur de validation de sécurité')
      return null
    }
  }

  const searchCodes = (query: string): SecretCode[] => {
    if (!query) return codes.value
    const q = query.toLowerCase()
    return codes.value.filter(c =>
      c.code.toLowerCase().includes(q) ||
      c.description.toLowerCase().includes(q) ||
      c.function.toLowerCase().includes(q)
    )
  }

  const getCodesByCategory = (category: string): SecretCode[] => {
    return codes.value.filter(c => c.category === category)
  }

  return {
    // State
    codes,
    codesByModel,
    categories,
    loading,
    // Actions
    fetchCodes,
    fetchCodesByBrand,
    fetchCategories,
    resolveCode,
    validateCodeSafety,
    searchCodes,
    getCodesByCategory,
  }
}