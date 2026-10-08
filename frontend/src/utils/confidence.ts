/**
 * Affichage d'un score de confiance.
 * Une confiance absente (null) signifie « données insuffisantes » : on ne la
 * transforme JAMAIS en 0 % ni en valeur plausible.
 */
export const CONFIDENCE_UNKNOWN_LABEL = 'Données insuffisantes'

export function formatConfidence(confidence: number | null | undefined): string {
  if (confidence === null || confidence === undefined) return CONFIDENCE_UNKNOWN_LABEL
  return `${Math.round(confidence * 100)}%`
}
