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
                                <!-- <Button
                                    v-if="can('skin-edit')"
                                    v-tooltip.top="'Editar skin'"
                                    icon="pi pi-pencil"
                                    rounded
                                    text
                                    severity="secondary"
                                    size="small"
                                    @click="openEditDialog(slotProps.data)"
                                /> -->
                                <Button
                                    v-if="can('skin-delete')"
                                    v-tooltip.top="'Eliminar Skin'"
                                    icon="pi pi-trash"
                                    rounded
                                    text
                                    severity="danger"
                                    size="small"
                                    @click="confirmarEliminarSkin(slotProps.data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable> 
            </template>
        </Card>
    <Dialog
    v-model:visible="dialogOpen"
    modal
    header="Crear Skin"
    :style="{ width: '400px' }"
>
    <div class="flex flex-col gap-4">
        <div>
            <label class="dialog-label">Nombre de la Skin</label>
            <InputText
                v-model="nuevaSkin.nombre"
                placeholder="Nombre"
                class="w-full"
                :class="{ 'p-invalid': errors.nombre }"
            />
            <small v-if="errors.nombre" class="dialog-error">
                {{ errors.nombre[0] }}
            </small>
        </div>

        <div>
            <label class="dialog-label">Precio</label>
            <InputNumber
                v-model="nuevaSkin.precio"
                :mode="'decimal'"
                class="w-full"
            />
            <small v-if="errors.precio" class="dialog-error">
                {{ errors.precio[0] }}
            </small>
        </div>

        <div class="flex items-center gap-2">
            <Checkbox v-model="nuevaSkin.activo" binary /> Activo
        </div>
    </div>

    <template #footer>
        <Button label="Cancelar" severity="secondary" @click="closeDialog" />
        <Button
            label="Crear"
            severity="primary"
            :loading="isSubmitting"
            @click="botonCrear"
        />
    </template>
</Dialog>
    </div>
</template>
<script setup>
    import { ref, reactive , onMounted } from 'vue';
    import { useSkins} from '@/composables/skins';

    //Llamamamos a la función useSkins para obtener los datos de las skins y el estado de carga
    const { skins, getSkins, isLoading, crearSkin, isSubmitting, errors  } = useSkins();
    
    // Formulario crear
    const nuevaSkin = reactive({
        nombre: '',
        precio: null,
        activo: true
    });
    // Estado del dialog
    const dialogOpen = ref(false);

    const openCreateDialog = () => {
        nuevaSkin.nombre = '';
        nuevaSkin.precio = null;
        nuevaSkin.activo = true;
        dialogOpen.value = true;
    };
    const closeDialog = () => {
        dialogOpen.value = false;
    };

    // Crear skin
    const botonCrear = async () => {
    const crear = await crearSkin({ ...nuevaSkin });
        if (crear) {
            closeDialog();
        }
    };
    //  Función para mostrar la fecha en formato legible
    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString("es-ES");
    };
    // Cargar las skins cuando se monta el componente
    onMounted(() => {
        getSkins();
    });

</script>