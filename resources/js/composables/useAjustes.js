import { ref } from 'vue'
import axios from 'axios'

export function useAjustes() {
  const ajustes = ref([])
  const isLoading = ref(false)
  const error = ref(null)

  const fetchAjustes = async () => {
    isLoading.value = true
    try {
      const { data } = await axios.get('/api/ajustes')
      ajustes.value = data
    } catch (e) {
      console.error(e)
      error.value = 'Error al cargar ajustes'
    } finally {
      isLoading.value = false
    }
  }

  const getAjuste = async (id) => {
    try {
      const { data } = await axios.get(`/api/ajustes/${id}`)
      return data
    } catch (e) {
      console.error(e)
      throw e
    }
  }

  const updateAjuste = async (id, form) => {
    try {
        const { data } = await axios.post(`/api/ajustes/${id}`, form)
        
        const index = ajustes.value.findIndex(a => a.id == id)
        if (index !== -1) ajustes.value[index] = data

        return data
    } catch (e) {
        console.error('Error updateAjuste:', e.response || e)
        throw e
    }
    }

  const createAjuste = async (form) => {
    try {
      const { data } = await axios.post('/api/ajustes', form)
      ajustes.value.push(data)
      return data
    } catch (e) {
      console.error(e)
      throw e
    }
  }

  const deleteAjuste = async (id) => {
    try {
      await axios.delete(`/api/ajustes/${id}`)
      ajustes.value = ajustes.value.filter(a => a.id != id)
    } catch (e) {
      console.error(e)
      throw e
    }
  }

  return {
    ajustes,
    isLoading,
    error,
    fetchAjustes,
    getAjuste,
    updateAjuste, 
    createAjuste,
    deleteAjuste
  }
}