<template>
    <div class="ajustes-index">
        <Card title="Ajustes" class="mb-4">
            <template #content>
                <!-- Tabla -->
                <DataTable
                    :value="ajustes"
                    paginator
                    :rows="10"
                    class="mt-4"
                >
                    <!-- Header personalizado con botones -->
                    <template #header>
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold">Lista de Ajustes</h2>
                            <div class="flex gap-2">
                                <!-- Botón actualizar -->
                                <Button
                                    label="Actualizar"
                                    icon="pi pi-refresh"
                                    severity="secondary"
                                    @click="fetchAjustes"
                                />
                                <!-- Botón crear -->
                                <Button
                                    label="Nuevo"
                                    icon="pi pi-plus"
                                    severity="primary"
                                    @click="$router.push({ name: 'ajustes.create' })"
                                />
                            </div>
                        </div>
                    </template>

                    <!-- Columnas -->
                    <Column field="id" header="ID" />
                    <Column field="clave" header="Clave" />
                    <Column field="valor" header="Valor" />
                    <Column field="descripcion" header="Descripción" />

                    <!-- Acciones -->
                    <Column header="Acciones">
                        <template #body="{ data }">
                            <div class="flex gap-2">
                                <Button
                                    label="Editar"
                                    icon="pi pi-pencil"
                                    severity="warning"
                                    @click="$router.push({ name: 'ajustes.update', params: { id: data.id } })"
                                    
                                />

                                <Button
                                    label="Eliminar"
                                    icon="pi pi-trash"
                                    severity="danger"
                                    @click="deleteAjuste(data.id)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>

            </template>
        </Card>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAjustes } from '@/composables/useAjustes'

const { ajustes, fetchAjustes, deleteAjuste } = useAjustes()

onMounted(fetchAjustes)
</script>