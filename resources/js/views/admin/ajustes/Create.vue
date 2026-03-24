<template>
  <Dialog
    v-model:visible="showModal"
    header="Crear Ajuste"
    modal
    :style="{ width: '450px' }"
    :closable="false"
  >
    <div class="flex flex-col gap-4">
      <div>
        <label class="block text-sm mb-1">Clave</label>
        <InputText v-model="form.clave" class="w-full" placeholder="Ej: site_name" />
      </div>

      <div>
        <label class="block text-sm mb-1">Valor</label>
        <InputText v-model="form.valor" class="w-full" placeholder="Ej: Mi App" />
      </div>

      <div>
        <label class="block text-sm mb-1">Descripción</label>
        <Textarea v-model="form.descripcion" class="w-full" rows="3" placeholder="Opcional" />
      </div>
    </div>

    <template #footer>
      <Button label="Cancelar" severity="secondary" @click="closeModal" />
      <Button
        label="Guardar"
        severity="primary"
        :loading="isLoading"
        @click="saveAjuste"
      />
    </template>
  </Dialog>
</template>
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAjustes } from '@/composables/useAjustes'

const router = useRouter()

const showModal = ref(true)
const isLoading = ref(false)

const form = ref({
  clave: '',
  valor: '',
  descripcion: ''
})

const { createAjuste } = useAjustes()

const closeModal = () => {
  router.push({ name: 'ajustes.index' })
}

const saveAjuste = async () => {
  if (!form.value.clave || !form.value.valor) {
    alert('Clave y Valor son obligatorios')
    return
  }

  isLoading.value = true
  try {
    await createAjuste(form.value) 
    router.push({ name: 'ajustes.index' })
  } catch (error) {
    console.error(error)
    alert('Error al crear el ajuste')
  } finally {
    isLoading.value = false
  }
}
</script>º