<template>
    <section id="home-user">
        <div class="col-12 col-md-6 header-content">
            <div class="titulo-card">
                <h1>BIENVENIDO <br class="d-none d-md-inline"> DE VUELTA, <br class="d-none d-md-inline">
                    {{ auth.user?.name?.toUpperCase() }}
                </h1>
                <div class="d-flex text-center justify-content-center">
                    <p class="d-none d-md-block">Tu mesa te espera. Pon a prueba tu estrategia y llévate los mejores
                        premios.</p>
                </div>
                <div class="d-flex justify-content-center">
                    <router-link class="btn btn-primary" to="/app/salas">JUGAR AHORA</router-link>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { authStore } from '@/store/auth'

const auth = authStore()

onMounted(() => {
    document.body.style.overflow = 'hidden'
})

onUnmounted(() => {
    document.body.style.overflow = ''
})
</script>

<style scoped>
#home-user {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-image: url(/images/neonjackHomeCalidad.png);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    z-index: 0;
    display: flex;
    align-items: flex-start;
}

.header-content {
    margin-top: 200px;
    margin-left: 107px;
    width: 450px;
}

.titulo-card {
    padding: 25px;
    border-radius: 7px;
    background: rgba(96, 92, 92, 0.5) !important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.titulo-card h1 {
    color: white;
    font-weight: 800;
    font-size: 3rem;
    line-height: 1;
    margin-bottom: 25px;
    text-transform: uppercase;
}

.titulo-card p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 1.1rem;
    margin-bottom: 35px;
}

.btn-primary {
    display: inline-block;
    padding: 12px 35px;
    border-radius: 50px;
    text-decoration: none;
    color: white;
    font-weight: bold;
    text-transform: uppercase;
    background: linear-gradient(90deg, #9C5CCB, #818AC8, #3BC3DB);
    border: none;
}

@media (max-width: 991px) {
    .header-content {
        margin-left: 0;
        margin-top: 160px;
        width: 100%;
        display: flex;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .header-content {
        margin-top: 120px;
        padding: 0 16px;
    }

    .titulo-card {
        width: 100%;
        max-width: 300px;
        text-align: center;
    }

    .titulo-card h1 {
        font-size: 1.4rem;
    }

    .btn-primary {
        padding: 10px 25px;
        font-size: 0.9rem;
    }
}
</style>
