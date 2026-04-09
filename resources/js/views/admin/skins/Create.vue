<template>
<Dialog :visible="visible" @update:visible="emit('update:visible', $event)" modal header="Crear Skin" :style="{ width: '400px' }" >        <div class="flex flex-col gap-4">
            <div>
                <label class="dialog-label">Nombre de la Skin</label>
                <InputText v-model="skin.nombre" placeholder="Nombre" class="w-full"
                    :class="{ 'p-invalid': errors.nombre }" />
                <small v-if="errors.nombre" class="dialog-error">
                    {{ errors.nombre[0] }}
                </small>
            </div>

            <div>
                <label class="dialog-label">Precio</label>
                <InputNumber v-model="skin.precio" :mode="'decimal'" class="w-full" />
                <small v-if="errors.precio" class="dialog-error">
                    {{ errors.precio[0] }}
                </small>
            </div>

            <div class="flex items-center gap-2">
                <Checkbox v-model="skin.activo" binary /> Activo
            </div>

            <div>
                <label class="dialog-label">Imagen</label>
                <input type="file" @change="seleccionarImagen" />
            </div>  
            
        </div>

        <template #footer>
            <Button label="Cancelar" severity="secondary" @click="closeDialog" />
            <Button label="Crear" severity="primary" :loading="isSubmitting" @click="botonCrear" />
        </template>
    </Dialog>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useSkins } from '@/composables/skins'

const props = defineProps({
    visible: Boolean
})

const emit = defineEmits(['update:visible'])

const { skin, crearSkin, errors, isSubmitting, resetSkin } = useSkins()

// guardar imagen seleccionada
const imagen = ref(null)

const seleccionarImagen = (event) => {
    imagen.value = event.target.files[0]
}

const closeDialog = () => {
    emit('update:visible', false)
}

const botonCrear = async () => {
    const data = {
        nombre: skin.value.nombre,
        precio: skin.value.precio,
        activo: !!skin.value.activo
    };

    const crear = await crearSkin(data, imagen.value);

    if (crear) {
        resetSkin();
        closeDialog();
    } else {
        console.error("No se creó la skin correctamente");
    }
};
</script>