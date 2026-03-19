<template>
  <Dialog
    v-model:visible="showModal"
    header="Editar Ajuste"
    modal
    :style="{ width: '450px' }"
    :closable="false"
  >
    <div class="flex flex-col gap-4">

      <!-- CLAVE -->
      <div>
        <label class="block text-sm mb-1">Clave</label>
        <InputText v-model="form.clave" class="w-full" />
      </div>

      <!-- VALOR -->
      <div>
        <label class="block text-sm mb-1">Valor</label>
        <InputText v-model="form.valor" class="w-full" />
      </div>

      <!-- DESCRIPCIÓN -->
      <div>
        <label class="block text-sm mb-1">Descripción</label>
        <Textarea v-model="form.descripcion" rows="3" class="w-full" />
      </div>

    </div>

    <template #footer>
      <Button
        label="Cancelar"
        severity="secondary"
        @click="closeModal"
      />

      <Button
        label="Actualizar"
        icon="pi pi-save"
        severity="primary"
        :loading="isLoading"
        @click="saveAjuste"
      />
    </template>
  </Dialog>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAjustes } from '@/composables/useAjustes'

const router = useRouter()
const route = useRoute()

const id = route.params.id

const showModal = ref(true)
const isLoading = ref(false)

const form = ref({
  clave: '',
  valor: '',
  descripcion: ''
})

const { getAjuste, updateAjuste } = useAjustes()

onMounted(async () => {
  try {
    const data = await getAjuste(id)

    form.value = {
      clave: data.clave || '',
      valor: data.valor || '',
      descripcion: data.descripcion || ''
    }
  } catch (error) {
    console.error(error)
    alert('Error al cargar el ajuste')
    router.push({ name: 'ajustes.index' })
  }
})

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
    await updateAjuste(id, form.value)
    router.push({ name: 'ajustes.index' })
  } catch (error) {
    console.error(error)
    alert('Error al actualizar')
  } finally {
    isLoading.value = false
  }
}
</script>