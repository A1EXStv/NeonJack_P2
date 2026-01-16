import { ref } from 'vue'

/**
 * Sistema de validación reutilizable basado en Yup.
 */
export function useValidation() {
  const errors = ref({})

  /** Valida un formulario completo de forma síncrona */
  const validate = (schema, data, options = { abortEarly: false }) => {
    if (!schema) throw new Error('Se requiere un esquema de validación')
    try {
      schema.validateSync(data, options)
      clearErrors()
      return { isValid: true, errors: {} }
    } catch (validationErrors) {
      mapValidationErrors(validationErrors)
      return { isValid: false, errors: errors.value }
    }
  }

  /** Valida un campo específico de forma síncrona */
  const validateField = (schema, field, data) => {
    if (!schema) throw new Error('Se requiere un esquema de validación')
    try {
      schema.validateSyncAt(field, data)
      clearFieldError(field)
      return { isValid: true }
    } catch (error) {
      setFieldError(field, error.message)
      return { isValid: false, error: error.message }
    }
  }

  const setFieldError = (field, message) => {
    if (field.includes('.')) {
      const [parent, child] = field.split('.')
      if (!errors.value[parent]) errors.value[parent] = {}
      errors.value[parent][child] = message
    } else {
      errors.value[field] = message
    }
  }

  const clearFieldError = (field) => {
    if (field.includes('.')) {
      const [parent, child] = field.split('.')
      if (errors.value[parent]) {
        delete errors.value[parent][child]
        if (Object.keys(errors.value[parent]).length === 0) {
          delete errors.value[parent]
        }
      }
    } else {
      delete errors.value[field]
    }
  }

  const mapValidationErrors = (validationErrors) => {
    errors.value = {};
    (validationErrors.inner || []).forEach(err => {
      setFieldError(err.path, err.message)
    })
  }

  const clearErrors = () => { errors.value = {} }
  const hasError = (field) => field.includes('.') ? !!errors.value[field.split('.')[0]]?.[field.split('.')[1]] : !!errors.value[field]
  const getError = (field) => field.includes('.') ? errors.value[field.split('.')[0]]?.[field.split('.')[1]] : errors.value[field]
  const hasErrors = () => Object.keys(errors.value).length > 0

  return {
    errors,
    validate,
    validateField,
    setFieldError,
    clearFieldError,
    clearErrors,
    hasError,
    getError,
    hasErrors
  }
}
