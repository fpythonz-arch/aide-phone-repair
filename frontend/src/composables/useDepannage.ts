import { ref, computed } from 'vue'
import { depannageApi } from '@/api/client'

// ============================================================
// TYPES
// ============================================================

export interface Category {
  id: number
  slug: string
  name: string
  icon: string
  description: string
  color: string
  type: 'hardware' | 'software'
}

export interface CheckItem {
  id: number
  label: string
  checked: boolean
}

export interface GuideStep {
  id: number
  order: number
  title: string
  description: string
  instruction: string
  warning?: string
  checkItems: CheckItem[]
  tools: string[]
  estimatedTime: number
}

export interface Solution {
  id: number
  title: string
  description: string
  difficulty: 'easy' | 'medium' | 'hard' | 'expert'
  estimatedCost: number
  needsReplacement: boolean
  replacementPart?: string
  guideUrl: string | null
}

export interface Guide {
  id: number
  category: Category | undefined
  symptoms: string[]
  commonCauses: string[]
  steps: GuideStep[]
  solutions: Solution[]
}

export interface ResourceItem {
  id: number
  title: string
  type: 'video' | 'article' | 'forum' | 'shop' | 'tool'
  url: string
  description: string
  thumbnail?: string
  duration?: string
  author?: string
  date?: string
}

export interface FullGuide {
  category: Category
  resources: ResourceItem[]
  relatedPannes: Category[]
  tips: string[]
  warnings: string[]
}

// ============================================================
// CATÉGORIES HARDWARE
// ============================================================

const hardwareCategories: Category[] = [
  { id: 1, slug: 'ecran', name: "Problème d'écran", icon: '📱', description: 'Écran noir, lignes, tactile mort, pixels défectueux, backlight...', color: '#3b82f6', type: 'hardware' },
  { id: 2, slug: 'batterie', name: 'Problème de batterie', icon: '🔋', description: 'Ne charge pas, décharge rapide, surchauffe, gonflement...', color: '#22c55e', type: 'hardware' },
  { id: 3, slug: 'charge', name: 'Problème de charge', icon: '🔌', description: 'Connecteur loose, charge intermittente, port de charge cassé...', color: '#f59e0b', type: 'hardware' },
  { id: 4, slug: 'audio', name: 'Problème audio', icon: '🔊', description: 'Pas de son, grésillement, micro coupé, haut-parleur mort...', color: '#8b5cf6', type: 'hardware' },
  { id: 5, slug: 'reseau', name: 'Problème réseau', icon: '📶', description: 'Pas de signal, WiFi faible, Bluetooth, NFC, GPS...', color: '#ef4444', type: 'hardware' },
  { id: 6, slug: 'demarrage', name: 'Problème de démarrage', icon: '⚡', description: "Ne s'allume pas, bootloop, redémarrages intempestifs...", color: '#ec4899', type: 'hardware' },
  { id: 7, slug: 'camera', name: 'Problème caméra', icon: '📷', description: 'Caméra floue, noire, ne focus pas, flash ne marche pas...', color: '#06b6d4', type: 'hardware' },
  { id: 8, slug: 'boutons', name: 'Boutons défectueux', icon: '🔘', description: 'Power, volume, home cassés ou non réactifs...', color: '#f97316', type: 'hardware' },
  { id: 9, slug: 'eau', name: 'Dégâts des liquides', icon: '💧', description: "Chute dans l'eau, oxydation, corrosion...", color: '#0ea5e9', type: 'hardware' },
  { id: 10, slug: 'chauffe', name: 'Chauffe excessive', icon: '🌡️', description: 'Téléphone très chaud, surchauffe en charge, en jeu...', color: '#dc2626', type: 'hardware' },
  { id: 11, slug: 'vibreur', name: 'Vibreur / Moteur Taptic', icon: '📳', description: 'Vibreur ne fonctionne pas, bruit anormal, Taptic Engine mort...', color: '#a855f7', type: 'hardware' },
  { id: 12, slug: 'capteurs', name: 'Capteurs défectueux', icon: '👆', description: 'Proximité, luminosité, accéléromètre, gyroscope, boussole...', color: '#14b8a6', type: 'hardware' },
  { id: 13, slug: 'carte-mere', name: 'Problème carte mère', icon: '🧠', description: 'Court-circuit, IC power mort, baseband, problème de soudure...', color: '#be123c', type: 'hardware' },
  { id: 14, slug: 'faceid', name: 'Face ID / Touch ID', icon: '👁️', description: 'Reconnaissance faciale non disponible, empreinte non reconnue...', color: '#7c3aed', type: 'hardware' },
  { id: 15, slug: 'sim-sd', name: 'Port SIM / SD', icon: '💳', description: 'SIM non détectée, port SD cassé, lecteur défectueux...', color: '#059669', type: 'hardware' },
]

// ============================================================
// CATÉGORIES SOFTWARE
// ============================================================

const softwareCategories: Category[] = [
  { id: 101, slug: 'lenteur', name: 'Smartphone lent', icon: '🐌', description: 'Ralentissements, freeze, applications lentes, UI qui rame...', color: '#f59e0b', type: 'software' },
  { id: 102, slug: 'plantages', name: 'Applications qui plantent', icon: '💥', description: 'App crash, fermeture forcée, écran figé, ANR...', color: '#ef4444', type: 'software' },
  { id: 103, slug: 'batterie-soft', name: 'Batterie qui se vide (soft)', icon: '🔋', description: "Drain anormal causé par un logiciel, application en arrière-plan...", color: '#22c55e', type: 'software' },
  { id: 104, slug: 'mise-a-jour', name: 'Mise à jour bloquée', icon: '⬆️', description: 'Mise à jour échoue, téléchargement bloqué, erreur OTA...', color: '#3b82f6', type: 'software' },
  { id: 105, slug: 'bootloop', name: 'Bootloop / Redémarrage', icon: '🔄', description: 'Redémarrage en boucle, bootloop après MAJ, recovery mode...', color: '#ec4899', type: 'software' },
  { id: 106, slug: 'ecran-noir-soft', name: 'Écran noir (soft)', icon: '⬛', description: "Écran noir mais téléphone allumé, rétro-éclairage mort (soft)...", color: '#374151', type: 'software' },
  { id: 107, slug: 'virus', name: 'Virus / Malware', icon: '🦠', description: 'Publicités intempestives, comportement suspect, données volées...', color: '#dc2626', type: 'software' },
  { id: 108, slug: 'stockage-plein', name: 'Stockage plein', icon: '💾', description: "Mémoire saturée, impossible d'installer, photos/vidéos bloquées...", color: '#8b5cf6', type: 'software' },
  { id: 109, slug: 'wifi-4g-soft', name: 'WiFi / 4G (soft)', icon: '📡', description: 'Connexion instable, déconnexions, DNS, problème de pilote...', color: '#06b6d4', type: 'software' },
  { id: 110, slug: 'compte-bloque', name: 'Compte bloqué', icon: '🔒', description: 'Google FRP, Apple ID, iCloud, MDM, compte oublié...', color: '#f97316', type: 'software' },
  { id: 111, slug: 'restauration', name: 'Restauration échoue', icon: '🏭', description: 'Factory reset échoue, erreur lors du formatage, recovery corrompu...', color: '#64748b', type: 'software' },
  { id: 112, slug: 'notifications', name: 'Notifications', icon: '🔔', description: 'Notifications qui ne marchent pas, retardées, doublées...', color: '#14b8a6', type: 'software' },
  { id: 113, slug: 'clavier', name: 'Clavier / Saisie', icon: '⌨️', description: 'Clavier qui bug, autocorrect fou, saisie tactile décalée...', color: '#a855f7', type: 'software' },
  { id: 114, slug: 'appareil-photo-soft', name: 'Appareil photo (soft)', icon: '📷', description: 'App photo qui plante, écran noir en photo, flash non dispo (soft)...', color: '#0ea5e9', type: 'software' },
  { id: 115, slug: 'bluetooth-soft', name: 'Bluetooth (soft)', icon: '🔵', description: 'Paire impossible, déconnexions, audio BT qui coupe...', color: '#3b82f6', type: 'software' },
  { id: 116, slug: 'gps-soft', name: 'GPS imprécis (soft)', icon: '📍', description: "Position erronée, GPS qui ne fixe pas, localisation désactivée...", color: '#22c55e', type: 'software' },
]

const allCategories = [...hardwareCategories, ...softwareCategories]

// ============================================================
// GUIDES COMPLÈTS PAR CATÉGORIE
// ============================================================

const guidesDatabase: Record<string, Guide> = {
  // ── HARDWARE ─────────────────────────────────────────────
  ecran: {
    id: 1,
    category: hardwareCategories.find(c => c.slug === 'ecran'),
    symptoms: ['Écran noir', 'Lignes verticales/horizontales', 'Tactile non réactif', 'Pixels morts', 'Flickering/scintillement', 'Backlight mort', 'Taches jaunes', 'Écran qui clignote'],
    commonCauses: ['Chute ou impact', 'Pression excessive (poche avant)', 'Contact avec liquide', 'Usure connecteur FPC', 'Défaut de fabrication', 'Mise à jour iOS/Android incompatible'],
    steps: [
      {
        id: 1, order: 1,
        title: 'Redémarrage forcé (soft reset)',
        description: 'Avant tout démontage, écartez un bug logiciel avec un redémarrage forcé.',
        instruction: 'Maintenez simultanément le bouton Power et le bouton Volume Bas (ou Home selon modèle) pendant 10 à 15 secondes jusqu\'à ce que le logo constructeur apparaisse. Sur iPhone 8+ : Volume Haut, Volume Bas, puis Power long.',
        warning: 'Ne pas confondre avec un simple redémarrage. Le forced shutdown coupe complètement l\'alimentation du SoC.',
        checkItems: [
          { id: 1, label: 'Le téléphone vibre ou émet un son au démarrage', checked: false },
          { id: 2, label: 'Le logo constructeur apparaît à l\'écran', checked: false },
          { id: 3, label: 'L\'écran s\'allume normalement après redémarrage', checked: false },
        ],
        tools: [],
        estimatedTime: 2,
      },
      {
        id: 2, order: 2,
        title: 'Vérification connecteur écran (FPC)',
        description: 'Le connecteur de la nappe d\'écran peut se déloger suite à un choc ou une chute.',
        instruction: '1. Éteignez le téléphone. 2. Retirez les vis du bas (Pentalobe/Torx). 3. Utilisez une ventouse pour soulever l\'écran. 4. Débranchez la batterie FIRST. 5. Localisez le connecteur FPC de l\'écran sur la carte mère. 6. Débranchez et rebranchez-le fermement. 7. Vérifiez l\'absence d\'oxydation verte/blanche.',
        warning: '⚠️ DÉBRANCHEZ TOUJOURS LA BATTERIE AVANT TOUTE MANIPULATION INTERNE. Risque de court-circuit sinon.',
        checkItems: [
          { id: 4, label: 'Le connecteur FPC est bien en place (clic audible)', checked: false },
          { id: 5, label: 'Pas d\'oxydation visible sur le connecteur', checked: false },
          { id: 6, label: 'La nappe n\'est pas pliée, déchirée ou coupée', checked: false },
        ],
        tools: ['Ventouse', 'Spudger nylon', 'Tournevis Pentalobe/Torx', 'Pincette ESD'],
        estimatedTime: 15,
      },
      {
        id: 3, order: 3,
        title: 'Test avec écran de rechange',
        description: 'Si le redémarrage et le connecteur ne résolvent pas le problème, testez avec un écran de rechange pour isoler le défaut.',
        instruction: '1. Commandez un écran de test compatible (même modèle exact). 2. Branchez l\'écran de test SANS le monter complètement (juste la nappe). 3. Rebranchez la batterie. 4. Allumez le téléphone. 5. Testez le tactile et l\'affichage. Si l\'écran test fonctionne → votre écran d\'origine est HS.',
        warning: 'Ne forcez pas sur les nappes. Un mauvais angle de 15° peut les endommager définitivement. Utilisez une pincette.',
        checkItems: [
          { id: 7, label: 'L\'écran de test s\'allume correctement', checked: false },
          { id: 8, label: 'Le tactile fonctionne sur toute la surface', checked: false },
          { id: 9, label: 'Pas de lignes ni de taches sur l\'écran test', checked: false },
        ],
        tools: ['Écran de test compatible', 'Pincette ESD fine'],
        estimatedTime: 10,
      },
      {
        id: 4, order: 4,
        title: 'Test du circuit backlight / IC display',
        description: 'Si l\'écran test ne s\'allume pas non plus, le problème vient de la carte mère (circuit backlight ou IC display).',
        instruction: '1. Vérifiez la diode backlight avec un multimètre en mode diode. 2. Mesurez la tension sur les points de test backlight (généralement 15-45V). 3. Si pas de tension → IC backlight (U1501 sur iPhone) ou bobine backlight à remplacer. 4. Nécessite micro-soudure et station à air chaud.',
        warning: '⚠️ NIVEAU EXPERT. La micro-soudure sur carte mère nécessite un microscope et une station à air chaud précise. Risque de destruction permanente.',
        checkItems: [
          { id: 10, label: 'Tension backlight mesurée et présente', checked: false },
          { id: 11, label: 'IC display chauffe anormalement (caméra thermique)', checked: false },
          { id: 12, label: 'Pas de pistes coupées ou brûlées visibles', checked: false },
        ],
        tools: ['Multimètre', 'Station air chaud', 'Microscope', 'Caméra thermique (optionnel)'],
        estimatedTime: 45,
      },
    ],
    solutions: [
      {
        id: 1,
        title: 'Remplacement écran complet (OLED/LCD + vitre tactile)',
        description: 'L\'écran est physiquement endommagé (fissures, lignes, pixels morts). Le remplacement complet est la seule solution durable. Prix variable selon modèle : iPhone 15 Pro ~€350, Samsung S24 ~€280, Xiaomi ~€80-150.',
        difficulty: 'hard',
        estimatedCost: 85,
        needsReplacement: true,
        replacementPart: 'Écran OLED/LCD complet avec chassis et nappe',
        guideUrl: null,
      },
      {
        id: 2,
        title: 'Réparation connecteur/nappe FPC',
        description: 'Si seul le connecteur est oxydé ou la nappe pliée, une micro-soudure ou un remplacement de nappe peut suffire. Coût réduit.',
        difficulty: 'expert',
        estimatedCost: 45,
        needsReplacement: false,
        guideUrl: null,
      },
      {
        id: 3,
        title: 'Réparation circuit backlight (carte mère)',
        description: 'IC backlight, bobine, diode ou condensateur à remplacer. Nécessite un réparateur spécialisé en micro-soudure.',
        difficulty: 'expert',
        estimatedCost: 120,
        needsReplacement: false,
        guideUrl: null,
      },
    ],
  },

  batterie: {
    id: 2,
    category: hardwareCategories.find(c => c.slug === 'batterie'),
    symptoms: ['Ne charge pas du tout', 'Décharge rapide (< 3h)', 'Surchauffe en charge', 'Gonflement visible', 'Pourcentage erratique (saute de 50% à 10%)', 'Téléphone s\'éteint à 20-30%'],
    commonCauses: ['Usure normale (>500 cycles)', 'Chargeur non original/cheap', 'Surchauffe prolongée (voiture, soleil)', 'Défaut logiciel (calibration)', 'Court-circuit interne', 'Défaut de fabrication (rappel Samsung Note 7)'],
    steps: [
      {
        id: 10, order: 1,
        title: 'Vérification chargeur et câble',
        description: 'Écartez un problème de chargeur avant de suspecter la batterie. 50% des "problèmes de batterie" viennent du chargeur.',
        instruction: '1. Testez avec un chargeur et un câble certifiés d\'origine (Apple MFi, Samsung original). 2. Vérifiez que le port de charge n\'est pas obstrué par de la poussière ou des peluches. 3. Utilisez une aiguille ou une brosse douce pour nettoyer. 4. Testez sur une prise murale (pas USB PC).',
        warning: 'N\'utilisez JAMAIS de chargeur endommagé (câble dénudé, fiche brûlée). Risque d\'incendie.',
        checkItems: [
          { id: 11, label: 'Le câble fonctionne sur un autre appareil', checked: false },
          { id: 12, label: 'Le port de charge est propre et dégagé', checked: false },
          { id: 13, label: 'Le téléphone charge avec un autre chargeur original', checked: false },
        ],
        tools: ['Aiguille/épingle', 'Brosse douce (type denture)', 'Chargeur original de test'],
        estimatedTime: 5,
      },
      {
        id: 11, order: 2,
        title: 'Diagnostic batterie logiciel',
        description: 'Vérifiez l\'état de santé de la batterie via les outils système ou des apps tierces.',
        instruction: 'iPhone : Réglages > Batterie > Santé de la batterie & Recharge. Notez la capacité maximale. Android : composez *#*#4636#*#* (Info téléphone > Batterie) ou utilisez AccuBattery/CPU-Z. Notez : capacité max, voltage, température, cycles.',
        warning: 'Un état inférieur à 80% indique un remplacement nécessaire. Au-dessus de 500 cycles, la batterie est considérée usagée.',
        checkItems: [
          { id: 14, label: 'Capacité max > 80%', checked: false },
          { id: 15, label: 'Pas de message "Service batterie" ou "Remplacer maintenant"', checked: false },
          { id: 16, label: 'Cycles < 500 et température < 45°C', checked: false },
        ],
        tools: ['AccuBattery (Android)', '3uTools (iPhone sur PC)'],
        estimatedTime: 3,
      },
      {
        id: 12, order: 3,
        title: 'Inspection physique et test de gonflement',
        description: 'Un gonflement ou une déformation du chassis indique une batterie défectueuse DANGEREUSE.',
        instruction: '1. Retirez la coque arrière (si amovible) ou observez le téléphone sur une surface plane. 2. Un mouvement de rotation indique un gonflement. 3. Regardez les bords de l\'écran : un écran qui se décolle = batterie gonflée. 4. NE PERCEZ JAMAIS une batterie gonflée.',
        warning: '⚠️ BATTERIE GONFLÉE = RISQUE D\'INCENDIE/EXPLOSION. Manipulez avec des gants, dans un endroit ventilé, loin des matières inflammables. Ne la chargez plus.',
        checkItems: [
          { id: 17, label: 'Pas de gonflement visible (téléphone plat sur table)', checked: false },
          { id: 18, label: 'Pas d\'écran qui se décolle du chassis', checked: false },
          { id: 19, label: 'Pas d\'odeur chimique (solvants, acide)', checked: false },
        ],
        tools: ['Gants de protection', 'Surface plane'],
        estimatedTime: 5,
      },
      {
        id: 13, order: 4,
        title: 'Calibration batterie (dernier recours soft)',
        description: 'Parfois le contrôleur de charge (gas gauge) est désynchronisé. Une calibration peut aider temporairement.',
        instruction: '1. Utilisez le téléphone jusqu\'à ce qu\'il s\'éteigne à 0%. 2. Laissez-le éteint 4-6 heures. 3. Rechargez à 100% sans interruption (nuit idéale). 4. Laissez encore 2h à 100%. 5. Redémarrez. Cela resynchronise le gas gauge IC.',
        warning: 'Cela ne répare PAS une batterie usée. C\'est juste une mesure temporaire avant remplacement.',
        checkItems: [
          { id: 20, label: 'Décharge complète jusqu\'à extinction', checked: false },
          { id: 21, label: 'Recharge complète sans interruption', checked: false },
          { id: 22, label: 'Pourcentage stable après calibration', checked: false },
        ],
        tools: [],
        estimatedTime: 480,
      },
    ],
    solutions: [
      {
        id: 4,
        title: 'Remplacement batterie Li-Ion',
        description: 'La batterie a atteint sa fin de vie (<80% capacité ou >500 cycles). Remplacement obligatoire pour retrouver une autonomie normale.',
        difficulty: 'medium',
        estimatedCost: 35,
        needsReplacement: true,
        replacementPart: 'Batterie Li-Ion compatible (certifiée CE/UL)',
        guideUrl: null,
      },
      {
        id: 5,
        title: 'Nettoyage port de charge + calibration',
        description: 'Parfois le problème vient juste d\'un port encrassé ou d\'une mauvaise calibration. Solution gratuite.',
        difficulty: 'easy',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
      {
        id: 6,
        title: 'Remplacement circuit charge (carte mère)',
        description: 'Si la batterie neuve ne charge pas non plus, le circuit de charge (IC U2/Tristar sur iPhone) est mort. Nécessite micro-soudure.',
        difficulty: 'expert',
        estimatedCost: 95,
        needsReplacement: false,
        guideUrl: null,
      },
    ],
  },

  charge: {
    id: 3,
    category: hardwareCategories.find(c => c.slug === 'charge'),
    symptoms: ['Ne charge pas du tout', 'Charge intermittente (câble bouge)', 'Charge très lente', 'Message "Accessoire non supporté"', 'Port de charge lousse', 'Chauffe anormale du port'],
    commonCauses: ['Peluches/poussière dans le port', 'Câble/chargeur non original', 'Flex de charge déchiré', 'IC charge (Tristar/U2) mort', 'Soudure port de charge lâche', 'Oxydation après contact eau'],
    steps: [
      {
        id: 20, order: 1,
        title: 'Nettoyage du port de charge',
        description: '80% des problèmes de charge viennent d\'un port encrassé. C\'est la première chose à vérifier.',
        instruction: '1. Éteignez le téléphone. 2. Utilisez une aiguille ou un cure-dent en bois pour retirer délicatement les peluches. 3. Soufflez avec une bombe d\'air comprimé. 4. Utilisez une brosse à dents sèche et douce. 5. Testez avec un câble original.',
        warning: 'Ne mettez JAMAIS d\'objet métallique conducteur dans le port. Risque de court-circuit des pins.',
        checkItems: [
          { id: 21, label: 'Peluches/poussière retirées visuellement', checked: false },
          { id: 22, label: 'Pins du port droits et non tordus', checked: false },
          { id: 23, label: 'Charge stable après nettoyage', checked: false },
        ],
        tools: ['Aiguille/cure-dent bois', 'Brosse à dents douce', 'Bombe air comprimé'],
        estimatedTime: 5,
      },
      {
        id: 21, order: 2,
        title: 'Test câble et chargeur',
        description: 'Écartez un problème de câble/chargeur défectueux.',
        instruction: '1. Testez avec 3 câbles différents (dont 1 original). 2. Testez sur 2 prises murales différentes. 3. Testez sur un ordinateur (charge lente mais stable = câble OK). 4. Vérifiez le ampérage du chargeur (minimum 1A, idéal 2A+).',
        warning: 'Les chargeurs cheap peuvent endommager le circuit de charge à long terme. Utilisez des chargeurs certifiés.',
        checkItems: [
          { id: 24, label: 'Câble original fonctionne', checked: false },
          { id: 25, label: 'Chargeur délivre >1A (testé avec USB tester)', checked: false },
          { id: 26, label: 'Charge stable sur prise murale', checked: false },
        ],
        tools: ['Câble original', 'USB power tester (optionnel)'],
        estimatedTime: 5,
      },
      {
        id: 22, order: 3,
        title: 'Vérification flex de charge',
        description: 'Le flex (nappe) qui relie le port de charge à la carte mère peut se déchirer ou oxyder.',
        instruction: '1. Ouvrez le téléphone. 2. Localisez le flex de charge. 3. Inspectez visuellement : traces noires = brûlé, vert = oxydation, pliure = cassure. 4. Testez la continuité avec un multimètre. 5. Si défectueux → remplacement du flex.',
        warning: 'Sur certains modèles (iPhone 7+), le flex est soudé à la carte mère. Nécessite micro-soudure.',
        checkItems: [
          { id: 27, label: 'Flex intact visuellement', checked: false },
          { id: 28, label: 'Continuité électrique OK (multimètre)', checked: false },
          { id: 29, label: 'Connecteur FPC bien en place', checked: false },
        ],
        tools: ['Multimètre', 'Tournevis', 'Spudger'],
        estimatedTime: 20,
      },
      {
        id: 23, order: 4,
        title: 'Test IC de charge (Tristar/U2)',
        description: 'Si tout le reste est OK, l\'IC de charge sur la carte mère est probablement mort.',
        instruction: '1. Mesurez la tension VBUS (5V) sur le port. 2. Mesurez la tension batterie pendant charge (doit monter). 3. Si VBUS présent mais batterie ne charge pas → IC Tristar/U2 mort. 4. Nécessite reballing/remplacement de l\'IC.',
        warning: '⚠️ NIVEAU EXPERT. Le reballing d\'un IC BGA nécessite une station précise et un microscope. Coût réparation ~€80-120.',
        checkItems: [
          { id: 30, label: 'VBUS = 5V au port', checked: false },
          { id: 31, label: 'Tension batterie augmente pendant charge', checked: false },
          { id: 32, label: 'IC Tristar ne chauffe pas anormalement', checked: false },
        ],
        tools: ['Multimètre', 'Station air chaud', 'Microscope', 'IC Tristar de rechange'],
        estimatedTime: 60,
      },
    ],
    solutions: [
      {
        id: 7,
        title: 'Nettoyage port de charge',
        description: 'Solution simple et gratuite. 80% des cas résolus.',
        difficulty: 'easy',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
      {
        id: 8,
        title: 'Remplacement flex de charge',
        description: 'Le flex/nappe est déchiré ou oxydé. Remplacement par un neuf.',
        difficulty: 'medium',
        estimatedCost: 25,
        needsReplacement: true,
        replacementPart: 'Flex de charge compatible',
        guideUrl: null,
      },
      {
        id: 9,
        title: 'Remplacement IC Tristar/U2 (carte mère)',
        description: 'L\'IC de charge est mort. Nécessite un réparateur spécialisé en micro-soudure BGA.',
        difficulty: 'expert',
        estimatedCost: 95,
        needsReplacement: false,
        guideUrl: null,
      },
    ],
  },

  // ── SOFTWARE ─────────────────────────────────────────────
  lenteur: {
    id: 101,
    category: softwareCategories.find(c => c.slug === 'lenteur'),
    symptoms: ['Applications lentes à ouvrir', 'UI qui rame/scintille', 'Latence au toucher', 'Multitâche qui lag', 'Animations saccadées', 'Temps de chargement longs'],
    commonCauses: ['Stockage presque plein (>85%)', 'Trop d\'apps en arrière-plan', 'Cache système saturé', 'Animation système lourde', 'App mal optimisée', 'Virus/malware', 'Mise à jour système buguée'],
    steps: [
      {
        id: 100, order: 1,
        title: 'Libérer l\'espace de stockage',
        description: 'Un stockage plein (>85%) ralentit drastiquement le téléphone car le système n\'a plus de place pour les fichiers temporaires.',
        instruction: '1. Allez dans Paramètres > Stockage. 2. Supprimez les photos/vidéos (backup sur cloud d\'abord). 3. Videz le cache des apps (Paramètres > Apps > [App] > Stockage > Vider le cache). 4. Désinstallez les apps inutilisées. 5. Utilisez Files by Google ou CCleaner pour nettoyer les fichiers temporaires.',
        warning: 'Ne supprimez PAS les données système. Vider le cache est sans risque, mais "Effacer les données" réinitialise l\'app.',
        checkItems: [
          { id: 101, label: 'Stockage utilisé < 80%', checked: false },
          { id: 102, label: 'Cache des apps lourdes vidé (Facebook, Instagram)', checked: false },
          { id: 103, label: 'Apps inutilisées désinstallées', checked: false },
        ],
        tools: ['Files by Google', 'CCleaner', 'Google Photos (backup)'],
        estimatedTime: 20,
      },
      {
        id: 101, order: 2,
        title: 'Désactiver les animations système',
        description: 'Les animations fluides consomment des ressources GPU/CPU. Les désactiver donne un sentiment de réactivité immédiat.',
        instruction: 'Android : Paramètres > À propos du téléphone > Cliquez 7x sur "Numéro de build" pour activer mode développeur. Puis Paramètres > Système > Options développeur > Échelle animation fenêtre / transition / durée → 0.5x ou OFF. iPhone : Réglages > Accessibilité > Réduire les animations > ON.',
        warning: 'Le mode développeur expose des options avancées. Ne touchez pas aux options que vous ne comprenez pas.',
        checkItems: [
          { id: 104, label: 'Mode développeur activé (Android)', checked: false },
          { id: 105, label: 'Échelle animation réduite à 0.5x ou OFF', checked: false },
          { id: 106, label: 'UI plus réactive après redémarrage', checked: false },
        ],
        tools: [],
        estimatedTime: 5,
      },
      {
        id: 102, order: 3,
        title: 'Identifier l\'app coupable (CPU/RAM)',
        description: 'Une seule app mal optimisée peut monopoliser 80% des ressources.',
        instruction: 'Android : Paramètres > Batterie > Utilisation batterie par app. Ou Options développeur > Processus en cours. iPhone : Réglages > Batterie > Afficher par app. Cherchez l\'app qui consomme anormalement. Désinstallez-la ou forcez l\'arrêt.',
        warning: 'Ne forcez pas l\'arrêt des apps système (Android System, Services Google). Cela peut rendre le téléphone instable.',
        checkItems: [
          { id: 107, label: 'App coupable identifiée (conso batterie >20%)', checked: false },
          { id: 108, label: 'App désinstallée ou mise à jour', checked: false },
          { id: 109, label: 'Utilisation CPU/RAM revenu à la normale', checked: false },
        ],
        tools: ['CPU-Z', 'AccuBattery', 'Options développeur'],
        estimatedTime: 10,
      },
      {
        id: 103, order: 4,
        title: 'Mode sans échec / Diagnostic malware',
        description: 'Un malware peut ralentir le téléphone en arrière-plan sans être visible.',
        instruction: 'Android : Maintenez Power, appuyez longuement sur "Éteindre" → "Mode sans échec". Si le téléphone est fluide en mode sans échec = app tierce coupable. Désinstallez les apps récentes. iPhone : Pas de mode sans échec. Utilisez un antivirus comme Malwarebytes.',
        warning: 'En mode sans échec, les apps tierces sont désactivées. Vos widgets et fonds d\'écran personnalisés seront temporairement inactifs.',
        checkItems: [
          { id: 110, label: 'Mode sans échec testé (Android)', checked: false },
          { id: 111, label: 'Malwarebytes scan terminé (0 menaces)', checked: false },
          { id: 112, label: 'Téléphone fluide après suppression apps suspectes', checked: false },
        ],
        tools: ['Malwarebytes', 'Avast Mobile', 'Mode sans échec'],
        estimatedTime: 15,
      },
    ],
    solutions: [
      {
        id: 100,
        title: 'Nettoyage complet + optimisation',
        description: 'Combinaison de nettoyage stockage, cache, apps en arrière-plan et réduction animations. Solution la plus efficace et gratuite.',
        difficulty: 'easy',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
      {
        id: 101,
        title: 'Réinitialisation usine (Factory Reset)',
        description: 'Si le nettoyage ne suffit pas, une réinitialisation complète élimine tous les résidus logiciels. PENSEZ À BACKUP.',
        difficulty: 'medium',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
      {
        id: 102,
        title: 'Remplacement si ancien modèle',
        description: 'Si le téléphone a >4 ans, la lenteur peut être due au hardware (RAM insuffisante, stockage eMMC lent). Remplacement conseillé.',
        difficulty: 'easy',
        estimatedCost: 300,
        needsReplacement: true,
        replacementPart: 'Nouveau smartphone (recommandé >4GB RAM, UFS storage)',
        guideUrl: null,
      },
    ],
  },

  virus: {
    id: 107,
    category: softwareCategories.find(c => c.slug === 'virus'),
    symptoms: ['Publicités intempestives (pop-ups)', 'Apps installées sans consentement', 'Batterie qui se vide anormalement', 'Données mobiles consommées en excès', 'Téléphone qui chauffe sans raison', 'Comportements suspects (SMS envoyés seuls)'],
    commonCauses: ['APK téléchargée hors Play Store', 'Clique sur lien phishing', 'App clone/fake (ex: WhatsApp Gold)', 'Publicité malveillante (adware)', 'Root/jailbreak non sécurisé', 'WiFi public non sécurisé'],
    steps: [
      {
        id: 200, order: 1,
        title: 'Identifier et arrêter l\'app malveillante',
        description: 'Localisez l\'app responsable avant de la supprimer.',
        instruction: '1. Paramètres > Apps > Voir toutes les apps. 2. Triez par "Dernière utilisation" ou "Consommation batterie". 3. Cherchez des apps suspectes (noms étranges, pas d\'icône, installées récemment). 4. Notez le nom exact. 5. Forcez l\'arrêt puis Désinstallez.',
        warning: 'Certaines apps malveillantes se cachent sous des noms système (ex: "System Update", "Google Services" fake). Méfiez-vous.',
        checkItems: [
          { id: 201, label: 'App suspecte identifiée', checked: false },
          { id: 202, label: 'App désinstallée avec succès', checked: false },
          { id: 203, label: 'Publicités/pop-ups ont cessé', checked: false },
        ],
        tools: [],
        estimatedTime: 10,
      },
      {
        id: 201, order: 2,
        title: 'Scan antivirus complet',
        description: 'Utilisez un antivirus réputé pour détecter les menaces résiduelles.',
        instruction: '1. Installez Malwarebytes (gratuit et efficace) ou Avast Mobile. 2. Lancez un scan COMPLET (pas rapide). 3. Supprimez toutes les menaces détectées. 4. Vérifiez les permissions des apps restantes (Paramètres > Apps > Permissions).',
        warning: 'N\'installez pas plusieurs antivirus simultanément. Cela ralentit le téléphone et crée des conflits.',
        checkItems: [
          { id: 204, label: 'Scan antivirus complet terminé', checked: false },
          { id: 205, label: '0 menaces détectées après nettoyage', checked: false },
          { id: 206, label: 'Permissions suspectes révoquées', checked: false },
        ],
        tools: ['Malwarebytes', 'Avast Mobile Security'],
        estimatedTime: 20,
      },
      {
        id: 202, order: 3,
        title: 'Vérifier les droits d\'administration',
        description: 'Certaines apps malveillantes s\'accordent des droits d\'admin pour empêcher leur désinstallation.',
        instruction: 'Android : Paramètres > Sécurité > Administrateurs appareil. Désactivez tout ce qui est suspect. iPhone : Pas d\'équivalent, mais vérifiez Profils (Réglages > Général > VPN & Gestion appareil).',
        warning: 'Ne désactivez PAS les administrateurs légitimes (Google, Samsung, votre MDM entreprise).',
        checkItems: [
          { id: 207, label: 'Administrateurs suspects désactivés', checked: false },
          { id: 208, label: 'Profils MDM inconnus supprimés', checked: false },
          { id: 209, label: 'App non réinstallée automatiquement', checked: false },
        ],
        tools: [],
        estimatedTime: 5,
      },
      {
        id: 203, order: 4,
        title: 'Changer les mots de passe et vérifier les comptes',
        description: 'Si un malware a volé des données, sécurisez vos comptes immédiatement.',
        instruction: '1. Changez le mot de passe Google/Apple ID depuis un AUTRE appareil (PC). 2. Vérifiez les connexions actives (Google > Sécurité > Vos appareils). 3. Déconnectez les appareils inconnus. 4. Activez la 2FA si pas déjà fait. 5. Vérifiez vos SMS envoyés pour détecter des spams.',
        warning: 'Ne changez PAS les mots de passe depuis le téléphone infecté. Le malware pourrait les capturer.',
        checkItems: [
          { id: 210, label: 'Mot de passe Google/Apple ID changé', checked: false },
          { id: 211, label: 'Appareils inconnus déconnectés', checked: false },
          { id: 212, label: '2FA activée sur tous les comptes critiques', checked: false },
        ],
        tools: ['PC/Mac sécurisé', 'Google Account', 'Have I Been Pwned'],
        estimatedTime: 15,
      },
    ],
    solutions: [
      {
        id: 200,
        title: 'Nettoyage antivirus + révocation permissions',
        description: 'Si le malware est détecté et supprimé par l\'antivirus, cette solution suffit.',
        difficulty: 'easy',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
      {
        id: 201,
        title: 'Réinitialisation usine (Factory Reset)',
        description: 'Si le malware persiste (rootkit), seule une réinitialisation complète garantit l\'éradication. BACKUP d\'abord !',
        difficulty: 'medium',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
      {
        id: 202,
        title: 'Flash ROM stock (Android avancé)',
        description: 'Pour les utilisateurs avancés. Réinstallation complète du firmware d\'usine via Odin/Flash Tool. Élimine TOUT.',
        difficulty: 'expert',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
    ],
  },

  bootloop: {
    id: 105,
    category: softwareCategories.find(c => c.slug === 'bootloop'),
    symptoms: ['Redémarrage en boucle sur le logo', 'Écran noir puis logo en boucle', 'Recovery mode inaccessible', 'Bootloader verrouillé', 'Écran bleu (BSOD Android)', 'Kernel panic'],
    commonCauses: ['Mise à jour système interrompue', 'Root/jailbreak mal fait', 'App incompatible avec le système', 'Partition corrompue', 'Bootloader modifié', 'Batterie faible pendant MAJ', 'Custom ROM incompatible'],
    steps: [
      {
        id: 300, order: 1,
        title: 'Redémarrage forcé (hard reset)',
        description: 'Parfois le bootloop est temporaire et un hard reset suffit.',
        instruction: 'iPhone : Volume Haut (rapide), Volume Bas (rapide), puis Power long jusqu\'au redémarrage. Android : Power + Volume Bas 10-15s. Certains modèles : Power + Volume Haut + Home.',
        warning: 'Si le téléphone redémarre en boucle après 3 tentatives, passez à l\'étape suivante.',
        checkItems: [
          { id: 301, label: 'Hard reset effectué', checked: false },
          { id: 302, label: 'Téléphone démarre normalement', checked: false },
          { id: 303, label: 'Pas de redémarrage spontané dans les 5 min', checked: false },
        ],
        tools: [],
        estimatedTime: 2,
      },
      {
        id: 301, order: 2,
        title: 'Mode Recovery / Safe Mode',
        description: 'Tentez de démarrer en mode recovery pour accéder aux options de réparation.',
        instruction: 'Android Recovery : Power + Volume Haut (quand logo apparaît, relâchez Power, gardez Volume Haut). Wipe cache partition. iPhone Recovery : Branchez au PC/Mac, forcez redémarrage mais gardez Power appuyé jusqu\'au mode recovery. Restaurez via iTunes/Finder.',
        warning: 'Wipe cache partition est sans risque. Wipe data/factory reset EFFACE TOUTES LES DONNÉES.',
        checkItems: [
          { id: 304, label: 'Mode recovery accessible', checked: false },
          { id: 305, label: 'Wipe cache partition effectué', checked: false },
          { id: 306, label: 'Téléphone démarre après wipe cache', checked: false },
        ],
        tools: ['Câble USB', 'PC/Mac', 'iTunes/Finder (iPhone)'],
        estimatedTime: 10,
      },
      {
        id: 302, order: 3,
        title: 'Restauration usine (Factory Reset)',
        description: 'Si le wipe cache ne suffit pas, une réinitialisation complète est nécessaire.',
        instruction: 'Android Recovery : "Wipe data/factory reset" > "Yes". iPhone : Mode recovery > "Restaurer" sur iTunes/Finder. Cela efface TOUTES les données. Assurez-vous d\'avoir un backup iCloud/Google.',
        warning: '⚠️ TOUTES LES DONNÉES SERONT PERDUES. Photos, contacts, messages, apps. Backup obligatoire avant si possible.',
        checkItems: [
          { id: 307, label: 'Backup iCloud/Google confirmé (si accessible)', checked: false },
          { id: 308, label: 'Factory reset effectué', checked: false },
          { id: 309, label: 'Téléphone démarre après reset', checked: false },
        ],
        tools: ['PC/Mac', 'iTunes/Finder', 'Compte iCloud/Google'],
        estimatedTime: 30,
      },
      {
        id: 303, order: 4,
        title: 'Flash firmware stock (dernier recours)',
        description: 'Si même le factory reset échoue, le firmware est corrompu. Il faut le reflasher.',
        instruction: 'Android : Téléchargez le firmware stock sur le site du constructeur. Utilisez Odin (Samsung), SP Flash Tool (MediaTek), ou Fastboot. iPhone : Mode DFU (Power + Volume Bas 10s, puis Power seul 5s) > Restaurer sur iTunes.',
        warning: '⚠️ NIVEAU AVANCÉ. Un mauvais firmware peut briquer définitivement le téléphone. Vérifiez le modèle EXACT (SM-G991B vs SM-G991U).',
        checkItems: [
          { id: 310, label: 'Firmware correct téléchargé (modèle exact)', checked: false },
          { id: 311, label: 'Flash réussi sans erreur', checked: false },
          { id: 312, label: 'Téléphone démarre après flash', checked: false },
        ],
        tools: ['Odin/SP Flash Tool/Fastboot', 'Firmware stock', 'PC Windows', 'Câble USB original'],
        estimatedTime: 45,
      },
    ],
    solutions: [
      {
        id: 300,
        title: 'Wipe cache partition',
        description: 'Solution rapide et sans perte de données. Efface les fichiers temporaires corrompus.',
        difficulty: 'easy',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
      {
        id: 301,
        title: 'Factory Reset (perte de données)',
        description: 'Réinitialisation complète. Nécessite un backup préalable.',
        difficulty: 'medium',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
      {
        id: 302,
        title: 'Flash firmware stock',
        description: 'Réinstallation complète du système. Nécessite un PC et des outils de flash.',
        difficulty: 'expert',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
    ],
  },

  'compte-bloque': {
    id: 110,
    category: softwareCategories.find(c => c.slug === 'compte-bloque'),
    symptoms: ['Google FRP lock (Factory Reset Protection)', 'Apple ID/iCloud bloqué', 'MDM (Mobile Device Management) lock', 'Compte oublié', 'Téléphone volé signalé', 'SIM lock / Carrier lock'],
    commonCauses: ['Factory reset sans déconnexion compte', 'Achat d\'occasion verrouillé', 'Compte oublié', 'Téléphone volé/perdu signalé', 'Appareil entreprise avec MDM', 'SIM lock opérateur'],
    steps: [
      {
        id: 400, order: 1,
        title: 'Vérifier le type de verrouillage',
        description: 'Identifiez exactement quel verrou bloque le téléphone. Chaque type a une solution différente.',
        instruction: 'FRP : Écran "Vérifier votre compte" après reset. iCloud : "Activer l\'iPhone" avec Apple ID. MDM : Message entreprise/système de gestion. SIM lock : "SIM non valide" avec carte d\'un autre opérateur.',
        warning: 'Ne payez JAMAIS pour un "déverrouillage" en ligne. 99% sont des arnaques.',
        checkItems: [
          { id: 401, label: 'Type de verrou identifié (FRP/iCloud/MDM/SIM)', checked: false },
          { id: 402, label: 'IMEI vérifié (pas volé/signalé)', checked: false },
          { id: 403, label: 'Preuve d\'achat disponible', checked: false },
        ],
        tools: ['IMEI.info', 'SNDeep.info'],
        estimatedTime: 5,
      },
      {
        id: 401, order: 2,
        title: 'Contacter le support officiel',
        description: 'Avec une preuve d\'achat légitime, Apple/Google peuvent déverrouiller.',
        instruction: 'Apple : support.apple.com > Contact > Désactivation Activation Lock. Fournissez : facture, IMEI, preuve d\'achat. Google : FRP ne peut être contourné que par le propriétaire original du compte. Contactez le vendeur.',
        warning: 'Sans preuve d\'achat, Apple/Google ne déverrouilleront PAS. C\'est une mesure anti-vol.',
        checkItems: [
          { id: 404, label: 'Demande de déverrouillage envoyée au support', checked: false },
          { id: 405, label: 'Documents requis préparés (facture, IMEI)', checked: false },
          { id: 406, label: 'Réponse du support reçue', checked: false },
        ],
        tools: ['Facture d\'achat', 'Photo ID', 'IMEI du téléphone'],
        estimatedTime: 1440,
      },
      {
        id: 402, order: 3,
        title: 'Solutions alternatives (risquées)',
        description: 'Si le support refuse, certaines méthodes techniques existent mais sont limitées.',
        instruction: 'FRP Android ancien (<8.0) : méthodes via TalkBack, clavier, ou APK. Android récent : impossible sans compte. iCloud : aucune méthode fiable (sauf serveurs de bypass payants = arnaque). MDM : contactez l\'admin IT de l\'entreprise. SIM lock : demandez à l\'opérateur d\'origine.',
        warning: '⚠️ Les méthodes de bypass FRP/iCloud sont souvent temporaires ou illégales. Le téléphone peut se reverrouiller après MAJ.',
        checkItems: [
          { id: 407, label: 'Méthode bypass testée (si applicable)', checked: false },
          { id: 408, label: 'Téléphone fonctionnel après bypass', checked: false },
          { id: 409, label: 'Pas de reverrouillage après redémarrage', checked: false },
        ],
        tools: ['PC', 'Outils FRP spécifiques (SamFw, etc.)'],
        estimatedTime: 30,
      },
    ],
    solutions: [
      {
        id: 400,
        title: 'Déverrouillage officiel (support Apple/Google)',
        description: 'La seule méthode légale et permanente. Nécessite une preuve d\'achat légitime.',
        difficulty: 'easy',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
      {
        id: 401,
        title: 'Contacter le vendeur d\'origine',
        description: 'Si acheté d\'occasion, demandez au vendeur de retirer le compte à distance (Find My iPhone / Google Device Manager).',
        difficulty: 'easy',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
      {
        id: 402,
        title: 'Service de déverrouillage professionnel',
        description: 'Certains services professionnels peuvent aider (changement de SN/IMEI = illégal dans certains pays).',
        difficulty: 'expert',
        estimatedCost: 50,
        needsReplacement: false,
        guideUrl: null,
      },
    ],
  },
}

// Guides par défaut pour les catégories non définies
function getDefaultGuide(slug: string): Guide {
  const cat = allCategories.find(c => c.slug === slug)
  return {
    id: 999,
    category: cat,
    symptoms: ['Symptôme non listé dans la base de données'],
    commonCauses: ['Cause inconnue - diagnostic nécessaire'],
    steps: [
      {
        id: 9990, order: 1,
        title: 'Diagnostic général',
        description: 'Aucun guide spécifique n\'est disponible pour cette panne. Procédure de diagnostic général.',
        instruction: '1. Redémarrez le téléphone. 2. Vérifiez les mises à jour système. 3. Testez en mode sans échec (Android). 4. Si le problème persiste, consultez un réparateur professionnel ou utilisez notre diagnostic avancé.',
        warning: 'N\'effectuez pas de manipulations risquées sans guide spécifique.',
        checkItems: [
          { id: 9991, label: 'Redémarrage effectué', checked: false },
          { id: 9992, label: 'Mise à jour système vérifiée', checked: false },
          { id: 9993, label: 'Mode sans échec testé', checked: false },
        ],
        tools: [],
        estimatedTime: 10,
      },
    ],
    solutions: [
      {
        id: 999,
        title: 'Consultation professionnelle recommandée',
        description: 'Cette panne nécessite un diagnostic approfondi par un professionnel.',
        difficulty: 'medium',
        estimatedCost: 0,
        needsReplacement: false,
        guideUrl: null,
      },
    ],
  }
}

// ============================================================
// RESSOURCES PAR CATÉGORIE
// ============================================================

const resourcesDatabase: Record<string, ResourceItem[]> = {
  ecran: [
    { id: 1, title: 'Remplacement écran iPhone 14 Pro - Tutoriel complet', type: 'video', url: 'https://www.youtube.com/watch?v=example1', description: 'Guide étape par étape pour remplacer un écran iPhone 14 Pro avec les outils nécessaires.', thumbnail: 'https://img.youtube.com/vi/example1/0.jpg', duration: '25:30', author: 'iFixit', date: '2024-03-15' },
    { id: 2, title: 'Comment remplacer un écran Samsung Galaxy', type: 'video', url: 'https://www.youtube.com/watch?v=example2', description: 'Tutoriel détaillé pour le remplacement écran Samsung.', thumbnail: 'https://img.youtube.com/vi/example2/0.jpg', duration: '18:45', author: 'JerryRigEverything', date: '2024-01-20' },
    { id: 3, title: 'Guide complet : Diagnostic écran noir', type: 'article', url: 'https://www.ifixit.com/Wiki/Display_Troubleshooting', description: 'Article détaillé sur le diagnostic des problèmes d\'écran.', author: 'iFixit', date: '2024-02-10' },
    { id: 4, title: 'Outils nécessaires pour réparation smartphone', type: 'shop', url: 'https://www.amazon.fr/s?k=kit+reparation+smartphone', description: 'Kit complet avec ventouse, spudger, tournevis Pentalobe et Torx.', author: 'Amazon' },
    { id: 5, title: 'Forum : Problème écran tactile mort après chute', type: 'forum', url: 'https://forum.xda-developers.com/t/screen-replacement-guide', description: 'Discussion communautaire sur les solutions après chute.', author: 'XDA Developers', date: '2024-05-01' },
    { id: 6, title: 'Écran OLED iPhone 15 Pro - Pièce de rechange', type: 'shop', url: 'https://www.ifixit.com/Store/Parts/iPhone-15-Pro-Screen', description: 'Écran OLED original de remplacement pour iPhone 15 Pro.', author: 'iFixit' },
  ],
  batterie: [
    { id: 10, title: 'Remplacement batterie iPhone - Guide iFixit', type: 'video', url: 'https://www.youtube.com/watch?v=example3', description: 'Tutoriel complet pour remplacer la batterie d\'un iPhone.', duration: '15:20', author: 'iFixit', date: '2024-02-28' },
    { id: 11, title: 'Batterie Samsung S24 Ultra - Avis et test', type: 'article', url: 'https://www.gsmarena.com/battery-test', description: 'Test et comparatif des batteries pour Samsung.', author: 'GSMArena', date: '2024-04-15' },
    { id: 12, title: 'Batterie Li-Ion compatible iPhone 13', type: 'shop', url: 'https://www.amazon.fr/s?k=batterie+iphone+13', description: 'Batterie de remplacement certifiée CE pour iPhone 13.', author: 'Amazon' },
    { id: 13, title: 'Calibration batterie : mythe ou réalité ?', type: 'article', url: 'https://www.androidauthority.com/battery-calibration', description: 'Article expliquant la calibration et son efficacité réelle.', author: 'Android Authority', date: '2024-03-01' },
  ],
}

function getResourcesForCategory(slug: string): ResourceItem[] {
  return resourcesDatabase[slug] || [
    { id: 9000, title: 'Guide général de réparation smartphone', type: 'article', url: 'https://www.ifixit.com/Device/Phone', description: 'Ressources générales pour la réparation de smartphones.', author: 'iFixit' },
    { id: 9001, title: 'Forum d\'entraide smartphone', type: 'forum', url: 'https://forum.xda-developers.com', description: 'Communauté d\'entraide pour tous les problèmes smartphone.', author: 'XDA Developers' },
  ]
}

function getRelatedPannes(slug: string): Category[] {
  const cat = allCategories.find(c => c.slug === slug)
  if (!cat) return []
  return allCategories.filter(c => c.type === cat.type && c.slug !== slug).slice(0, 4)
}

function getTipsForCategory(slug: string): string[] {
  const tips: Record<string, string[]> = {
    ecran: ['Toujours tester avec un écran de rechange avant d\'acheter', 'Vérifiez la garantie constructeur avant toute réparation', 'Un film de protection peut éviter les fissures futures'],
    batterie: ['Ne laissez jamais la batterie à 0% pendant longtemps', 'Évitez les chargeurs non certifiés', 'La température idéale de charge est 15-25°C'],
    charge: ['Nettoyez le port de charge toutes les 2 semaines', 'Utilisez un chargeur d\'au moins 2A', 'Évitez de charger sur USB PC (trop lent)'],
    lenteur: ['Gardez au moins 15% d\'espace libre', 'Redémarrez votre téléphone une fois par semaine', 'Désactivez les animations pour plus de fluidité'],
    virus: ['N\'installez jamais d\'APK hors Play Store', 'Activez Google Play Protect', 'Changez vos mots de passe régulièrement'],
  }
  return tips[slug] || ['Sauvegardez toujours vos données avant toute manipulation', 'Consultez un professionnel si vous n\'êtes pas sûr']
}

function getWarningsForCategory(slug: string): string[] {
  const warnings: Record<string, string[]> = {
    ecran: ['Débranchez la batterie avant tout démontage', 'Ne forcez pas sur les nappes FPC'],
    batterie: ['Batterie gonflée = risque d\'incendie', 'Ne percez jamais une batterie'],
    charge: ['Ne mettez pas d\'objet métallique dans le port', 'Chargeur endommagé = risque électrique'],
    eau: ["N'allumez PAS un téléphone mouillé", 'Le riz ne suffit pas, utilisez du gel silice'],
    'carte-mere': ['Micro-soudure = niveau expert uniquement', 'Risque de destruction permanente'],
    virus: ['Ne changez pas les mots de passe depuis le téléphone infecté', 'Ne payez jamais pour un "déverrouillage" en ligne'],
  }
  return warnings[slug] || ['Faites un backup avant toute manipulation']
}

// ============================================================
// COMPOSABLE
// ============================================================

export function useDepannage() {
  const categories = ref<Category[]>([])
  const currentGuide = ref<Guide | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const currentStepIndex = ref(0)
  const completedSteps = ref<number[]>([])

  // ── Computed ──────────────────────────────────────────────
  const progress = computed(() => {
    if (!currentGuide.value || currentGuide.value.steps.length === 0) return 0
    return Math.round((completedSteps.value.length / currentGuide.value.steps.length) * 100)
  })

  const currentStep = computed(() => {
    if (!currentGuide.value) return null
    return currentGuide.value.steps[currentStepIndex.value] || null
  })

  const isLastStep = computed(() => {
    if (!currentGuide.value) return false
    return currentStepIndex.value >= currentGuide.value.steps.length - 1
  })

  const canProceed = computed(() => {
    if (!currentStep.value) return false
    if (currentStep.value.checkItems.length === 0) return true
    return currentStep.value.checkItems.some(item => item.checked)
  })

  const isGuideComplete = computed(() => {
    if (!currentGuide.value) return false
    return completedSteps.value.length >= currentGuide.value.steps.length
  })

  const allTools = computed(() => {
    if (!currentGuide.value?.steps) return []
    const tools = new Set<string>()
    currentGuide.value.steps.forEach((step: GuideStep) => {
      if (step.tools && Array.isArray(step.tools)) {
        step.tools.forEach((tool: string) => tools.add(tool))
      }
    })
    return Array.from(tools)
  })

  const totalEstimatedTime = computed(() => {
    if (!currentGuide.value?.steps) return 0
    return currentGuide.value.steps.reduce((total, step) => total + step.estimatedTime, 0)
  })

  // ── Méthodes ──────────────────────────────────────────────

// REMPLACE fetchCategories :
const fetchCategories = async () => {
  loading.value = true
  error.value = null
  try {
const response = await depannageApi.getCategories()
    categories.value = response.data.data || []
  } catch (err: any) {
    console.error('Erreur fetchCategories:', err)
    error.value = err.response?.data?.message || 'Erreur de connexion au serveur'
    // Fallback sur les données locales si l'API échoue
    categories.value = allCategories
  } finally {
    loading.value = false
  }
}

// REMPLACE fetchGuideByType :
const fetchGuideByType = async (type: string) => {
  loading.value = true
  error.value = null
  try {
    const response = await depannageApi.getByType(type)
    currentGuide.value = response.data.data || null
    currentStepIndex.value = 0
    completedSteps.value = []
  } catch (err: any) {
    console.error('Erreur fetchGuideByType:', err)
    error.value = err.response?.data?.message || 'Guide non trouvé'
    // Fallback sur les données locales
    currentGuide.value = guidesDatabase[type] || getDefaultGuide(type)
  } finally {
    loading.value = false
  }
}
  const fetchFullGuide = async (slug: string): Promise<FullGuide> => {
    loading.value = true
    await new Promise(resolve => setTimeout(resolve, 200))
    const category = allCategories.find(c => c.slug === slug)
    loading.value = false
    return {
      category: category || hardwareCategories[0],
      resources: getResourcesForCategory(slug),
      relatedPannes: getRelatedPannes(slug),
      tips: getTipsForCategory(slug),
      warnings: getWarningsForCategory(slug),
    }
  }

  const goToStep = (index: number) => {
    if (index >= 0 && currentGuide.value && index < currentGuide.value.steps.length) {
      currentStepIndex.value = index
    }
  }

  const nextStep = () => {
    if (!currentGuide.value) return
    if (currentStep.value && !completedSteps.value.includes(currentStep.value.id)) {
      completedSteps.value.push(currentStep.value.id)
    }
    if (currentStepIndex.value < currentGuide.value.steps.length - 1) {
      currentStepIndex.value++
    }
  }

  const prevStep = () => {
    if (currentStepIndex.value > 0) {
      currentStepIndex.value--
    }
  }

  const toggleCheckItem = (stepId: number, itemId: number) => {
    if (!currentGuide.value) return
    const step = currentGuide.value.steps.find(s => s.id === stepId)
    if (step) {
      const item = step.checkItems.find(i => i.id === itemId)
      if (item) {
        item.checked = !item.checked
      }
    }
  }

  const resetGuide = () => {
    currentStepIndex.value = 0
    completedSteps.value = []
    if (currentGuide.value) {
      currentGuide.value.steps.forEach(step => {
        step.checkItems.forEach(item => {
          item.checked = false
        })
      })
    }
  }

    return {
    categories,
    currentGuide,
    loading,
    error,
    currentStepIndex,
    completedSteps,
    currentStep,
    progress,
    isLastStep,
    canProceed,
    isGuideComplete,
    allTools,           // ← AJOUTE CECI
    totalEstimatedTime, // ← AJOUTE CECI
    fetchCategories,
    fetchGuideByType,
    fetchFullGuide,     // ← AJOUTE CECI aussi (utilisé pour le guide complet)
    goToStep,
    nextStep,
    prevStep,
    toggleCheckItem,
    resetGuide,
  }
}