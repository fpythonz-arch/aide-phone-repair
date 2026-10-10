import { computed } from 'vue'
import { useAuth } from '@/composables/useAuth'

/**
 * Ce que l'utilisateur voit dans l'interface.
 *
 * ATTENTION : ceci ne sert qu'à afficher les bons menus. La vraie protection est faite par le serveur
 * (rôles, administrateur de plateforme, isolation par atelier) : masquer un bouton ne protège rien.
 */
export function usePermissions() {
  const { currentUser } = useAuth()

  const isPlatformAdmin = computed(() => currentUser.value?.is_platform_admin === true)
  const isWorkshopOwner = computed(() => currentUser.value?.role === 'Admin')
  const isSenior = computed(() => ['Admin', 'Technicien senior'].includes(currentUser.value?.role ?? ''))

  const roleLabel = computed(() => {
    if (isPlatformAdmin.value) return 'Administrateur de la plateforme'
    switch (currentUser.value?.role) {
      case 'Admin': return "Responsable d'atelier"
      case 'Technicien senior': return 'Technicien senior'
      case 'Technicien': return 'Technicien'
      default: return currentUser.value?.role ?? ''
    }
  })

  const workshopName = computed(() => currentUser.value?.workshop?.name ?? '')

  /** Les événements d'évolution sont communs à tous les ateliers : écriture réservée à la plateforme. */
  const canManageEvolution = computed(() => isPlatformAdmin.value)

  return { isPlatformAdmin, isWorkshopOwner, isSenior, roleLabel, workshopName, canManageEvolution }
}

/** Libellé lisible d'un rôle d'atelier (pour les listes de membres). */
export function roleLabelOf(role: string): string {
  return role === 'Admin' ? "Responsable d'atelier" : role
}
