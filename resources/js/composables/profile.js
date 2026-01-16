import { inject, ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useProfile() {
  const initialProfile = {
    name: '',
    email: ''
  }

  const profile = ref({ ...initialProfile })
  const isLoading = ref(false)
  const swal = inject('$swal')
  const toast = useToast()
  const { errors, validate, clearErrors, hasError, getError, setFieldError } = useValidation()

  const profileSchema = yup.object({
    name: yup.string().trim().required('El nombre es obligatorio').min(3, 'Debe tener al menos 3 caracteres')
  })

  const withLoading = async (fn) => {
    if (isLoading.value) throw new Error('Operación en curso')
    isLoading.value = true
    try {
      return await fn()
    } finally {
      isLoading.value = false
    }
  }

  const resetProfile = () => {
    profile.value = { ...initialProfile }
    clearErrors()
  }

  const setProfile = (data = {}) => {
    profile.value = {
      name: data.name ?? '',
      email: data.email ?? ''
    }
    clearErrors()
  }

  const getProfile = async () => {
    setProfile({
      name: auth.user?.value?.name,
      email: auth.user?.value?.email
    })
  }

  const updateProfile = async () => {
    const { isValid } = validate(profileSchema, profile.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() => axios.put(`/api/user`, { name: profile.value.name }))
      const data = response.data?.data ?? response.data
      toast.crud.updated('Usuario')
      return data
    } catch (error) {
      toast.error('Error', 'No se pudo actualizar el usuario')
      throw error
    }
  }  

  return {
    profile,
    errors,
    isLoading,
    hasError,
    getError,
    clearErrors,
    resetProfile,
    setProfile,
    getProfile,
    updateProfile
  }
}
