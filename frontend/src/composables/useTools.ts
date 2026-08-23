import { ref } from 'vue'
import apiClient from "@/api/client";

export function useTools() {
  const tools = ref([])
  const loading = ref(false)

  const fetchTools = async () => {
    loading.value = true
    try {
      const response = await apiClient.get('/tools')
      tools.value = response.data.data || []
    } finally {
      loading.value = false
    }
  }

  return { tools, loading, fetchTools }
}