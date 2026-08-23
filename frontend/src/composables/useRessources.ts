import { ref, computed } from 'vue'
import apiClient from "@/api/client";

export interface Resource {
  id: number
  title: string
  slug: string
  type: string
  category: string
  level: string
  description: string
  content?: string
  videoUrl?: string
  thumbnailUrl: string
  author: string
  duration: number
  tags: string[]
  isBookmarked: boolean
  views: number
  createdAt: string
  relatedComponents: string[]
}

export interface ResourceFilters {
  type: string
  category: string
  level: string
  search: string
}

export type ResourceType = 'guide' | 'video' | 'fiche' | 'astuce'
export type ResourceLevel = 'debutant' | 'intermediaire' | 'avance' | 'expert'
export type ResourceCategory = 'screen' | 'battery' | 'charging' | 'audio' | 'network' | 'software' | 'tools' | 'safety'

// ── Données mockées (toutes catégories et niveaux synchronisés) ──
const MOCK_RESOURCES: Resource[] = [
  {
    id: 1,
    title: 'Remplacement d\'écran OLED iPhone',
    slug: 'remplacement-ecran-oled-iphone',
    type: 'guide',
    category: 'screen',
    level: 'intermediaire',
    description: 'Guide complet pour remplacer un écran OLED sur iPhone 12/13/14. Nécessite un démontage complet et une calibration Face ID.',
    content: '<h2>Introduction</h2><p>Le remplacement d\'un écran OLED est l\'une des réparations les plus courantes...</p>',
    thumbnailUrl: '',
    author: 'TechRepair Pro',
    duration: 45,
    tags: ['iPhone', 'OLED', 'Face ID', 'Écran'],
    isBookmarked: false,
    views: 15420,
    createdAt: '2024-03-15T10:00:00Z',
    relatedComponents: ['Écran OLED', 'Nappe Face ID', 'Adhésif étanche'],
  },
  {
    id: 2,
    title: 'Diagnostic batterie : quand remplacer ?',
    slug: 'diagnostic-batterie-remplacement',
    type: 'fiche',
    category: 'battery',
    level: 'debutant',
    description: 'Apprenez à lire les indicateurs de santé batterie et à identifier les signes d\'usure critique.',
    thumbnailUrl: '',
    author: 'BatterieExpert',
    duration: 10,
    tags: ['Batterie', 'Santé', 'Cycles', 'Surchauffe'],
    isBookmarked: false,
    views: 8930,
    createdAt: '2024-02-20T14:30:00Z',
    relatedComponents: ['Batterie Li-Ion', 'Connecteur charge'],
  },
  {
    id: 3,
    title: 'Micro-soudure : techniques de base',
    slug: 'micro-soudure-techniques-base',
    type: 'video',
    category: 'tools',
    level: 'expert',
    description: 'Tutoriel vidéo complet sur la micro-soudure pour la réparation de cartes mères. Station, flux, et gestuelle.',
    videoUrl: 'https://example.com/video-micro-soudure',
    thumbnailUrl: '',
    author: 'SoudurePro',
    duration: 120,
    tags: ['Soudure', 'Carte mère', 'Station', 'Flux'],
    isBookmarked: false,
    views: 5620,
    createdAt: '2024-01-10T09:00:00Z',
    relatedComponents: ['Carte mère', 'Connecteur FPC', 'IC de charge'],
  },
  {
    id: 4,
    title: 'Nettoyage port de charge en 5 minutes',
    slug: 'nettoyage-port-charge-rapide',
    type: 'astuce',
    category: 'charging',
    level: 'debutant',
    description: 'Astuce rapide pour déboucher un port de charge sans démontage. 80% des problèmes de charge résolus.',
    thumbnailUrl: '',
    author: 'QuickFix',
    duration: 5,
    tags: ['Charge', 'Nettoyage', 'Port', 'Dépannage rapide'],
    isBookmarked: false,
    views: 32100,
    createdAt: '2024-04-01T11:00:00Z',
    relatedComponents: ['Connecteur de charge'],
  },
  {
    id: 5,
    title: 'Remplacement connecteur de charge USB-C',
    slug: 'remplacement-connecteur-usb-c',
    type: 'guide',
    category: 'charging',
    level: 'expert',
    description: 'Guide avancé pour le remplacement d\'un connecteur USB-C soudé. Nécessite une station de soudage et un microscope.',
    thumbnailUrl: '',
    author: 'MicroSoudure FR',
    duration: 90,
    tags: ['USB-C', 'Soudure', 'Microscope', 'Connecteur'],
    isBookmarked: false,
    views: 7800,
    createdAt: '2024-03-28T16:00:00Z',
    relatedComponents: ['Connecteur USB-C', 'Carte mère', 'Nappe'],
  },
  {
    id: 6,
    title: 'Sécurité en atelier : ESD et manipulation',
    slug: 'securite-atelier-esd',
    type: 'fiche',
    category: 'safety',
    level: 'debutant',
    description: 'Les règles essentielles de sécurité en atelier de réparation. Protection ESD, outils isolés, et bonnes pratiques.',
    thumbnailUrl: '',
    author: 'SafeRepair',
    duration: 15,
    tags: ['ESD', 'Sécurité', 'Outils', 'Protection'],
    isBookmarked: false,
    views: 12400,
    createdAt: '2024-01-05T08:00:00Z',
    relatedComponents: ['Bracelet ESD', 'Tapis antistatique'],
  },
  {
    id: 7,
    title: 'Remplacement haut-parleur et vibreur',
    slug: 'remplacement-haut-parleur-vibreur',
    type: 'guide',
    category: 'audio',
    level: 'debutant',        // ← CORRIGÉ : 'easy' → 'debutant'
    description: 'Guide pas à pas pour remplacer le haut-parleur et le vibreur sur la plupart des smartphones Android.',
    thumbnailUrl: '',
    author: 'AudioFix',
    duration: 25,
    tags: ['Audio', 'Haut-parleur', 'Vibreur', 'Android'],
    isBookmarked: false,
    views: 9800,
    createdAt: '2024-02-14T13:00:00Z',
    relatedComponents: ['Haut-parleur', 'Vibreur Taptic', 'Nappe'],
  },
  {
    id: 8,
    title: 'Résoudre les problèmes WiFi et Bluetooth',
    slug: 'problemes-wifi-bluetooth',
    type: 'video',
    category: 'network',
    level: 'intermediaire',
    description: 'Tutoriel vidéo pour diagnostiquer et réparer les problèmes de connectivité sans fil. Antennes, circuits RF et logiciel.',
    videoUrl: 'https://example.com/video-wifi-bt',
    thumbnailUrl: '',
    author: 'NetworkRepair',
    duration: 35,
    tags: ['WiFi', 'Bluetooth', 'Antenne', 'RF'],
    isBookmarked: false,
    views: 11200,
    createdAt: '2024-03-01T10:30:00Z',
    relatedComponents: ['Antenne WiFi', 'Module Bluetooth', 'Câble coaxial'],
  },
  // ── Ressources supplémentaires pour couvrir toutes les catégories ──
  {
    id: 9,
    title: 'Remplacement caméra arrière iPhone',
    slug: 'remplacement-camera-arriere-iphone',
    type: 'guide',
    category: 'software',      // ← Logiciel (pour couvrir cette catégorie)
    level: 'intermediaire',
    description: 'Guide pour remplacer le module caméra arrière et recalibrer le stabilisateur.',
    thumbnailUrl: '',
    author: 'CameraPro',
    duration: 40,
    tags: ['Caméra', 'iPhone', 'Stabilisateur', 'Module'],
    isBookmarked: false,
    views: 8700,
    createdAt: '2024-03-10T11:00:00Z',
    relatedComponents: ['Module caméra', 'Nappe caméra', 'Lentille'],
  },
  {
    id: 10,
    title: 'Mise à jour logicielle bloquée : solutions',
    slug: 'mise-a-jour-logicielle-bloquee',
    type: 'fiche',
    category: 'software',
    level: 'debutant',
    description: 'Que faire quand une mise à jour iOS/Android reste bloquée ? Solutions pas à pas.',
    thumbnailUrl: '',
    author: 'SoftFix',
    duration: 20,
    tags: ['Logiciel', 'Mise à jour', 'iOS', 'Android', 'Bloqué'],
    isBookmarked: false,
    views: 15600,
    createdAt: '2024-04-05T09:00:00Z',
    relatedComponents: ['Carte mère', 'Stockage NAND'],
  },
]

export function useRessources() {
  const resources = ref<Resource[]>([...MOCK_RESOURCES])
  const selectedResource = ref<Resource | null>(null)
  const loading = ref(false)

  const filters = ref<ResourceFilters>({
    type: '',
    category: '',
    level: '',
    search: '',
  })

  const bookmarkedResources = computed(() => {
    return resources.value.filter(r => r.isBookmarked)
  })

  const toggleBookmark = (resource: Resource) => {
    resource.isBookmarked = !resource.isBookmarked
  }

  const fetchResources = async () => {
    loading.value = true
    try {
      const response = await apiClient.get('/resources')
      if (response.data?.data?.length > 0) {
        resources.value = response.data.data
      }
    } catch (err) {
      console.log('API non disponible, utilisation des données locales')
    } finally {
      loading.value = false
    }
  }

  return {
    resources,
    selectedResource,
    loading,
    filters,
    bookmarkedResources,
    fetchResources,
    toggleBookmark,
  }
}