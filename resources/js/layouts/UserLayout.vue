<template>
    <MainLayout :menuItems="items" />
</template>

<script setup>
import { ref } from 'vue';
import MainLayout from './MainLayout.vue';

const items = ref([
    {
        label: 'Juego',
        items: [
            {
                label: 'Salas',
                icon: 'pi pi-table',
                route: '/app/salas'
            },
        ]
    },
    {
        label: 'Cuenta',
        items: [
            {
                label: 'Perfil',
                icon: 'pi pi-user',
                route: '/app/profile'
            }
        ]
    }
]);
</script>

