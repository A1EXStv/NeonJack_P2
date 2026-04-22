<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        header="Registrar Nueva Transacción"
        :style="{ width: '450px' }"
        class="transaction-dialog"
        :closable="!isLoading"
    >
        <div class="flex flex-col gap-4">
            <div class="field">
                <label for="user_id" class="font-bold block mb-2">Usuario</label>
                <Select 
                    v-model="transaccion.user_id" 
                    :options="usuariosList" 
                    optionLabel="label" 
                    optionValue="id" 
                    placeholder="Seleccionar usuario"
                    filter
                    class="w-full"
                    :class="{ 'p-invalid': hasError('user_id') }"
                    :disabled="isLoading"
                >
                    <template #option="slotProps">
                        <div class="flex flex-line">
                            <span class="font-bold text-sm">{{ slotProps.option.name }}&nbsp;</span>
                            <small class="font-bold text-sm">{{ slotProps.option.surname1 }}</small>
                        </div>
                    </template>
                </Select>
                <small v-if="hasError('user_id')" class="p-error block mt-1">
                    {{ getError('user_id') }}
                </small>
            </div>

            <div class="field">
                <label for="tipo" class="font-bold block mb-2">Tipo de Movimiento</label>
                <SelectButton 
                    v-model="transaccion.tipo" 
                    :options="tiposOpciones" 
                    optionLabel="label" 
                    optionValue="value"
                    class="w-full"
                    :disabled="isLoading"
                />
            </div>

            <div class="field">
                <label for="cantidad" class="font-bold block mb-2">Monto / Cantidad</label>
                <InputNumber
                    v-model="transaccion.cantidad"
                    mode="decimal"
                    :minFractionDigits="2"
                    :maxFractionDigits="2"
                    placeholder="0.00"
                    class="w-full"
                    :class="{ 'p-invalid': hasError('cantidad') }"
                    :disabled="isLoading"
                />
                <small v-if="hasError('cantidad')" class="p-error block mt-1">
                    {{ getError('cantidad') }}
                </small>
            </div>

            <Message v-if="transaccion.tipo" severity="secondary" :closable="false" size="small">
                {{ getHelpText(transaccion.tipo) }}
            </Message>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text severity="secondary" @click="close" :disabled="isLoading" />
            <Button label="Registrar" icon="pi pi-check" severity="primary" @click="handleSave" :loading="isLoading" />
        </template>
    </Dialog>
</template>

<script setup>
import { ref, defineProps, defineEmits, onMounted } from 'vue';
import axios from 'axios';
import useTransacciones from "@/composables/transacciones";

// PrimeVue Components (Actualizado Select)
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import SelectButton from 'primevue/selectbutton';
import Message from 'primevue/message';
import Select from 'primevue/select'; // Cambio aquí de Dropdown a Select

const props = defineProps({ visible: Boolean });
const emit = defineEmits(['update:visible', 'created']);

const { transaccion, storeTransaccion, resetTransaccion, hasError, getError, isLoading } = useTransacciones();

const usuariosList = ref([]);
const tiposOpciones = [
    { label: 'Depósito', value: 'deposito' },
    { label: 'Retiro', value: 'retirada' },
];

const fetchUsers = async () => {
    try {
        const response = await axios.get('/api/users');
        let data = response.data.data ?? response.data;
        
        if (data && typeof data === 'object' && !Array.isArray(data)) {
            data = data.data ?? [];
        }

        usuariosList.value = Array.isArray(data) ? data.map(u => ({
            ...u,
            label: `${u.name} ${u.surname1}`
        })) : [];
        
    } catch (error) {
        console.error("Error cargando usuarios:", error);
        usuariosList.value = [];
    }
};

onMounted(() => {
    fetchUsers();
});

const getHelpText = (tipo) => {
    return tipo === 'deposito' ? 'Aumentará el saldo del usuario.' : 'Se descontará del saldo del usuario.';
};

const close = () => {
    resetTransaccion();
    emit('update:visible', false);
};

const handleSave = async () => {
    const result = await storeTransaccion();
    if (result) {
        emit('created', result);
        close();
    }
};
</script>

<style scoped>
.p-error { color: #ef4444; font-size: 0.875rem; }
</style>