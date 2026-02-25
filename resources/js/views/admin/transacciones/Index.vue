<template>
    <div class="transactions-page">
        <Card>
            <template #title>
                <div class="flex items-center justify-between w-full">
                    <span>Gestión de Transacciones</span>
                    <div class="flex items-center gap-2">
                        <Button
                            label="Actualizar"
                            icon="pi pi-refresh"
                            size="small"
                            outlined
                            severity="secondary"
                            :loading="isLoading"
                            @click="getTransacciones"
                        />
                        <Button
                            label="Nueva Transacción"
                            icon="pi pi-plus"
                            size="small"
                            severity="primary"
                            @click="showCreateModal = true"
                        />
                    </div>
                </div>
            </template>

            <template #subtitle>
                Historial de movimientos financieros registrados en el sistema.
            </template>

            <template #content>
                <div v-if="isLoading && !transacciones.length" class="space-y-3">
                    <div v-for="row in 5" :key="row" class="flex gap-3 items-center">
                        <Skeleton width="60px" height="1.25rem" />
                        <Skeleton width="150px" height="1.25rem" />
                        <Skeleton width="120px" height="1.25rem" />
                        <Skeleton width="180px" height="1.25rem" />
                        <div class="flex gap-2 ml-auto">
                            <Skeleton width="2.5rem" height="2.5rem" shape="circle" />
                        </div>
                    </div>
                </div>

                <DataTable
                    v-else
                    v-model:filters="filters"
                    :value="transacciones || []"
                    :paginator="true"
                    :rows="10"
                    :rows-per-page-options="[10, 25, 50]"
                    data-key="id"
                    striped-rows
                    size="small"
                    filter-display="menu"
                    :global-filter-fields="['id', 'tipo', 'cantidad', 'user.name']"
                >
                    <template #header>
                        <div class="flex justify-end">
                            <IconField iconPosition="left">
                                <InputIcon class="pi pi-search" />
                                <InputText v-model="filters['global'].value" placeholder="Búsqueda global..." size="small" />
                            </IconField>
                        </div>
                    </template>

                    <template #empty>
                        <div class="text-center p-8 text-gray-400">
                            <i class="pi pi-filter-slash text-3xl mb-2"></i>
                            <p>No se encontraron transacciones registradas.</p>
                        </div>
                    </template>

                    <Column field="id" header="ID" sortable class="w-[100px]">
                        <template #body="{ data }">
                            <span class="font-mono text-xs font-bold">#{{ data.id }}</span>
                        </template>
                    </Column>

                    <Column field="user.name" header="Usuario" sortable>
                        <template #body="{ data }">
                            {{ data.user?.name || 'Usuario desconocido' }}
                        </template>
                    </Column>

                    <Column field="tipo" header="Tipo" sortable>
                        <template #body="{ data }">
                            <Tag :value="data.tipo" :severity="getTipoSeverity(data.tipo)" />
                        </template>
                    </Column>

                    <Column field="cantidad" header="Cantidad" sortable>
                        <template #body="{ data }">
                            <span :class="getAmountClass(data.tipo)" class="font-bold">
                                {{ formatCurrency(data.cantidad) }}
                            </span>
                        </template>
                    </Column>

                    <Column field="created_at" header="Fecha" sortable>
                        <template #body="{ data }">
                            <span class="text-sm">
                                <i class="pi pi-calendar mr-2 opacity-60"></i>
                                {{ formatDate(data.created_at) }}
                            </span>
                        </template>
                    </Column>

                    <Column header="Acciones" class="w-[100px]">
                        <template #body="{ data }">
                            <Button
                                icon="pi pi-trash"
                                rounded
                                text
                                severity="danger"
                                size="small"
                                @click="confirmDelete(data)"
                            />
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Create 
            v-model:visible="showCreateModal" 
            @created="getTransacciones" 
        />
    </div>
</template>

<script setup>
import { ref, onMounted, inject } from "vue";
import { FilterMatchMode } from "@primevue/core/api";
import useTransacciones from "@/composables/transacciones";
import Create from "./Create.vue";

// PrimeVue components (si no están globales)
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Skeleton from 'primevue/skeleton';

const { transacciones, getTransacciones, deleteTransaccion, isLoading } = useTransacciones();
const swal = inject('$swal');

const showCreateModal = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// Helpers de diseño
const getTipoSeverity = (tipo) => {
    return tipo === 'deposit' ? 'success' : 'danger';
};

const getAmountClass = (tipo) => {
    return tipo === 'deposit' ? 'text-green-600' : 'text-red-600';
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'USD' }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const confirmDelete = (data) => {
    swal({
        title: '¿Eliminar transacción?',
        text: `Se borrará el registro #${data.id}. Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteTransaccion(data.id).then(() => getTransacciones());
        }
    });
};

onMounted(() => {
    getTransacciones();
});
</script>

<style scoped>
.text-green-600 { color: #10b981; }
.text-red-600 { color: #ef4444; }
:deep(.p-datatable-sm) { font-size: 0.85rem; }
</style>