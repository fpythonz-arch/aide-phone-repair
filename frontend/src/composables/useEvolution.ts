// ============================================================
// COMPOSABLE useEvolution - AIDE PHONE RÉPARATION
// ============================================================

import { computed } from 'vue'
import { useEvolutionStore, useUiStore } from '@/stores'
import type { EvolutionEvent } from '@/types'

export function useEvolution() {
  const evolutionStore = useEvolutionStore()
  const uiStore = useUiStore()

  // ── Getters ─────────────────────────────────────────
  const events = computed(() => evolutionStore.events)
  const trends = computed(() => evolutionStore.trends)
  const loading = computed(() => evolutionStore.isLoading)

  // ── Actions ─────────────────────────────────────────
  const fetchEvents = async (deviceId?: string) => {
    try {
      await evolutionStore.fetchEvents()
      // Si deviceId est fourni, filtrer côté client
      // (ou ajoute un paramètre dans le store)
    } catch (err: any) {
      uiStore.showError('Erreur lors du chargement des événements')
    }
  }

  const fetchTrends = async () => {
    try {
      await evolutionStore.fetchTrends()
    } catch (err: any) {
      uiStore.showError('Erreur lors du chargement des tendances')
    }
  }

  const addEvent = async (eventData: Omit<EvolutionEvent, 'id' | 'created_at'>) => {
    try {
      await evolutionStore.createEvent(eventData)
      uiStore.showSuccess('Événement ajouté avec succès')
    } catch (err: any) {
      uiStore.showError('Erreur lors de l\'ajout de l\'événement')
      throw err
    }
  }

  const getEventsByType = (type: string): EvolutionEvent[] => {
    return events.value.filter(e => e.event_type === type)
  }

  const getEventsBySeverity = (severity: string): EvolutionEvent[] => {
    return events.value.filter(e => e.severity === severity)
  }

  const getRecentEvents = (limit: number = 10): EvolutionEvent[] => {
    return [...events.value]
      .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      .slice(0, limit)
  }

  return {
    // State
    events,
    trends,
    loading,
    // Actions
    fetchEvents,
    fetchTrends,
    addEvent,
    // Helpers
    getEventsByType,
    getEventsBySeverity,
    getRecentEvents,
  }
}