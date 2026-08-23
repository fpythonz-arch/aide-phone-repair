import { ref, computed } from 'vue';
import apiClient from '@/api/client';

export interface PriceRange {
  min: number;
  max: number;
  currency: string;
}

export interface TechnicalSpecs {
  type?: string;
  [key: string]: any;
}

export interface ComponentItem {
  id: number;
  name: string;
  slug: string;
  category: string;
  description: string;
  image_url: string | null;
  common_failures: string[];
  replacement_difficulty: number;
  price_range: PriceRange | null;
  technical_specs: TechnicalSpecs | null;
  compatible_devices: string[];
  availability: 'in_stock' | 'special_order' | 'not_available';
  created_at?: string;
}

export function useComponents() {
  const components = ref<ComponentItem[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const categories = computed(() => {
    const cats = new Set(components.value.map(c => c.category));
    return Array.from(cats).sort();
  });

  const groupedComponents = computed(() => {
    const groups: Record<string, ComponentItem[]> = {};
    components.value.forEach(comp => {
      if (!groups[comp.category]) groups[comp.category] = [];
      groups[comp.category].push(comp);
    });
    return groups;
  });

  function safeJsonParse<T>(value: any, fallback: T): T {
    if (value === null || value === undefined) return fallback;
    if (Array.isArray(value)) return value as unknown as T;
    if (typeof value === 'object') return value as T;
    if (typeof value === 'string') {
      try {
        return JSON.parse(value) as T;
      } catch {
        return fallback;
      }
    }
    return fallback;
  }

  async function fetchComponents() {
    loading.value = true;
    error.value = null;
    try {
      const response = await apiClient.get('/components');
      const rawData = response.data?.data ?? response.data ?? [];

      components.value = rawData.map((c: any) => ({
        id: c.id,
        name: c.name,
        slug: c.slug,
        category: c.category,
        description: c.description ?? '',
        image_url: c.image_url || null,
        common_failures: safeJsonParse<string[]>(c.common_failures, []),
        replacement_difficulty: Number(c.replacement_difficulty) || 1,
        price_range: safeJsonParse<PriceRange | null>(c.price_range, null),
        technical_specs: safeJsonParse<TechnicalSpecs | null>(c.technical_specs, null),
        compatible_devices: safeJsonParse<string[]>(c.compatible_devices, []),
        availability: c.availability || 'not_available',
        created_at: c.created_at,
      }));

      console.log('✅ Total:', components.value.length);
      console.log('✅ Logiciels:', components.value.filter(c => c.category === 'logiciel').length);

    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erreur de chargement';
      components.value = [];
    } finally {
      loading.value = false;
    }
  }

  async function fetchComponent(slug: string): Promise<ComponentItem | null> {
    loading.value = true;
    error.value = null;
    try {
      const response = await apiClient.get(`/components/${slug}`);
      const c = response.data?.data ?? response.data;

      return {
        id: c.id,
        name: c.name,
        slug: c.slug,
        category: c.category,
        description: c.description ?? '',
        image_url: c.image_url || null,
        common_failures: safeJsonParse<string[]>(c.common_failures, []),
        replacement_difficulty: Number(c.replacement_difficulty) || 1,
        price_range: safeJsonParse<PriceRange | null>(c.price_range, null),
        technical_specs: safeJsonParse<TechnicalSpecs | null>(c.technical_specs, null),
        compatible_devices: safeJsonParse<string[]>(c.compatible_devices, []),
        availability: c.availability || 'not_available',
        created_at: c.created_at,
      };
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Composant non trouvé';
      return null;
    } finally {
      loading.value = false;
    }
  }

  return {
    components,
    loading,
    error,
    categories,
    groupedComponents,
    fetchComponents,
    fetchComponent,
  };
}