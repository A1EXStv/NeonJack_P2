<template>
    <div class="flex justify-center">
        <Card class="w-full max-w-3xl">
            
            <template #title>
                <h2 class="text-xl font-semibold">Configuración del Perfil</h2>
            </template>

            <template #content>
                <form @submit.prevent="submitForm" class="flex flex-col gap-6">

                    <h3 class="text-base font-medium text-surface-700 mb-2">Información personal</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div class="flex flex-col gap-1">
                            <label for="name" class="text-sm font-medium text-surface-700">Nombre</label>
                            <InputText id="name" v-model="profile.name" autocomplete="name" :invalid="hasError('name')" />
                            <small v-if="hasError('name')" class="p-error">{{ getError('name') }}</small>
                        </div>

                    </div>
                
                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="button" label="Restablecer" icon="pi pi-refresh" severity="secondary" text :disabled="isLoading" @click="getProfile" />
                        <Button type="submit" label="Guardar cambios" icon="pi pi-save" :loading="isLoading" />
                    </div>

                </form>
            </template>

        </Card>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import useProfile from "@/composables/profile";

const { profile,getProfile,updateProfile,isLoading,hasError,getError } = useProfile()

function submitForm() {
    updateProfile()
}

onMounted(() => {
    getProfile()
})
</script>
