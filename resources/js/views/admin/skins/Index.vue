<template>
    <div class="skins-page">
        <Card>
            <template #title>
                <div class="flex items-center justify-between w-full">
                    <!-- Título de la página -->
                    <span>Gestión de Skins</span>
                    <!---Boton actualizar-->
                    <Button
                        label="Actualizar"
                        icon="pi pi-refresh"
                        size="small"
                        outlined
                        severity="secondary"
                        :loading="isLoading"
                        @click="getSkins"
                    />
                    <Button
                        label="Nueva Skin"
                        icon="pi pi-plus"
                        size="small"
                        severity="primary"
                        @click="openCreateDialog"
                        />
                </div>
            </template>
            <!----Tablaaa---->
            <template #content>
                <DataTable
                    :value="skins || []"
                    :loading="isLoading"
                    data-key="id"
                    striped-rows
                    size="small"
                >
                <!---Columnas de la tabla--->
                  <Column field="id" header="ID" sortable />
                    <Column field="nombre" header="Nombre" sortable />
                    <Column field="precio" header="Precio" sortable />
                    <Column field="activo" header="Activo" sortable>
                        <template #body="slotProps">
                            <span :class="slotProps.data.activo ? 'text-green-500' : 'text-red-500'">
                                {{ slotProps.data.activo ? 'Sí' : 'No' }}
                            </span>
                        </template>
                    </Column>
                    <Column field="created_at" header="Fecha creación" sortable>
                        <template #body="slotProps">
                            {{ formatDate(slotProps.data.created_at) }}
                        </template>
                    </Column>
                    <!----------------Acciones------------------>
                    <Column header="Acciones" class="w-[150px]">
                        <template #body="slotProps">
                            <Skeleton v-if="isLoading" width="4rem" height="2rem" />
                            <div v-else class="flex gap-2">
                                <Button
                                    icon="pi pi-pencil"
                                    severity="secondary"
                                    size="small"
                                    @click="$router.push({ name: 'admin.skins.edit', params: { id: slotProps.data.id } })"                                />
                                <Button  
                                    icon="pi pi-trash"
                                    severity="danger"
                                    text
                                    @click="confirmarEliminarSkin(slotProps.data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable> 
            </template>
        </Card>

        <Create v-model:visible="dialogOpen" />
   
    </div>
</template>
<script setup>
    import { ref, reactive , onMounted } from 'vue';
    import { useSkins} from '@/composables/skins';
    import Create from './Create.vue'

    //Llamamamos a la función useSkins para obtener los datos de las skins y el estado de carga
    const { skins, getSkins, isLoading, eliminarSkin ,isSubmitting, errors  } = useSkins();
    
   
    //  Función para mostrar la fecha en formato legible
    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString("es-ES");
    };

    //creamos skins
    const dialogOpen = ref(false)
    const openCreateDialog = () => {
        dialogOpen.value = true
    }

    //eliminar skin
    const confirmarEliminarSkin = async (skin) => {
    if (confirm(`¿Seguro que quieres eliminar "${skin.nombre}"?`)) {
        await eliminarSkin(skin.id);
    }
};

    // Cargar las skins cuando se monta el componente
    onMounted(() => {
        getSkins();
    });

</script>