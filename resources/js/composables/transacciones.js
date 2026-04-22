import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useTransacciones() {
  const transacciones = ref([])
  const isLoading = ref(false)
  const toast = useToast()

  const initialTransaccion = { id: null, user_id: null, tipo: 'deposito', cantidad: 0 }
  const transaccion = ref({ ...initialTransaccion })

  const { errors, validate, setFieldError, clearErrors, hasError, getError } = useValidation()

  const handleRequestError = (error) => {
    const status = error?.response?.status
    const message = error?.response?.data?.message ?? 'Error inesperado'
    if (status === 422) {
      const serverErrors = error.response.data.errors ?? {}
      Object.entries(serverErrors).forEach(([field, messages]) => {
        setFieldError(field, Array.isArray(messages) ? messages[0] : messages)
      })
    } else {
      toast.error('Error', message)
    }
  }

  const transaccionSchema = yup.object({
    user_id: yup.mixed().required('Debes seleccionar un usuario'),
    tipo: yup.string().oneOf(['deposito', 'retirada']).required('El tipo es obligatorio'),
    cantidad: yup.number().typeError('Debe ser un número').positive('Mayor a 0').required('Requerido')
  })

  const getTransacciones = async (params = {}) => {
    try {
      const response = await axios.get('/api/transacciones', { params })
      transacciones.value = response.data.data ?? response.data
    } catch (error) { handleRequestError(error) }
  }

  const storeTransaccion = async () => {
    clearErrors()
    const { isValid } = await validate(transaccionSchema, transaccion.value)
    if (!isValid) return false
    isLoading.value = true
    try {
      const response = await axios.post('/api/transacciones', transaccion.value)
      toast.success('Éxito', 'Transacción registrada')
      return response.data
    } catch (error) {
      handleRequestError(error)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const deleteTransaccion = async (id) => {
    try {
      await axios.delete(`/api/transacciones/${id}`)
      toast.success('Eliminado', 'Borrado')
    } catch (error) { handleRequestError(error) }
  }

  const resetTransaccion = () => {
    transaccion.value = { ...initialTransaccion };
    clearErrors();
  }

  return { transacciones, transaccion, isLoading, errors, hasError, getError, getTransacciones, storeTransaccion, deleteTransaccion, resetTransaccion }
}