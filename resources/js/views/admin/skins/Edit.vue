<template>
    <div class="skins-page">
        <Card>
            <template #title>
                <span>Editar Skin</span>
            </template>

            <template #content>
                 <div class="flex flex-col gap-4 mt-4">
                 <div v-if="skin.skin" class="mb-4">
                    <img :src="skin.skin" alt="Skin" class="w-40 rounded shadow" />
                </div>
                <div>
                    <label class="dialog-label">Nombre</label>
                    <InputText v-model="skin.nombre" class="w-full" />
                </div>

                <div>
                    <label class="dialog-label">Precio</label>
                    <InputNumber v-model="skin.precio" :mode="'decimal'" class="w-full" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox v-model="skin.activo" binary /> Activo
                </div>
                <div>
                    <label class="dialog-label">Imagen</label>
                    <input type="file" @change="seleccionarImagen" />
                </div>

                <div class="mt-4 flex gap-2">
                    <Button label="Cancelar" severity="secondary" @click="volver" />
                    <Button label="Guardar" severity="primary" :loading="isSubmitting" @click="guardarSkin" />
                </div>
            </div>
            </template>
        </Card>
    </div>
</template>


<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useSkins } from '@/composables/skins'
import { useRoute, useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()

const { skin, getSkinById, isSubmitting, actualizarSkin } = useSkins()

// guardar archivo de imagen
const imagen = ref(null)

// cuando se selecciona una imagen
const seleccionarImagen = (event) => {
    imagen.value = event.target.files[0]
}

// Obtener la skin por ID al montar
onMounted(async () => {
    const id = route.params.id
    await getSkinById(id)
})

// volver a la lista
const volver = () => {
    router.push({ name: 'skins.index' })
}

// guardar cambios
const guardarSkin = async () => {

    const updated = await actualizarSkin()

    if (updated) {

        // si hay imagen subirla
        if (imagen.value) {

            const formData = new FormData()
            formData.append('id', skin.value.id)
            formData.append('picture', imagen.value)

            await axios.post('/api/skins/updateimg', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
        }

        volver()
    }
}
</script>