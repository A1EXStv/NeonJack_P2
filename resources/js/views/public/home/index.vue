<template>
    <div class="flex flex-col items-center justify-center min-h-[60vh] text-center">
        <h1 class="text-4xl font-bold text-surface-900 dark:text-surface-0 mb-4">Bienvenido a Laravel VUE API Base</h1>
        <p class="text-xl text-surface-600 dark:text-surface-400 mb-8">Una base sólida para tus proyectos con Laravel y Vue.js</p>
        
        <div class="flex gap-4">
            <template v-if="!authStore().user?.name">
                <Button label="Iniciar Sesión" as="router-link" to="/login" size="large" />
                <Button label="Registrarse" as="router-link" to="/register" severity="secondary" size="large" />
            </template>
            <template v-else>
                <Button label="Ir al Dashboard" as="router-link" to="/app" size="large" />
            </template>
        </div>
    </div>
</template>

<script setup>
import { authStore } from "@/store/auth";
</script>
