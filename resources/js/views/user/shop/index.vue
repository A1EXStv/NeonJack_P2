<template>
    <section id="header">
        <div class="col-12 col-md-6 header-content">
            <div class="titulo-card">
                <h1 class="">Tienda</h1>
                <p>Compra skins exclusivas para tus cartas!</p>
            </div>
        </div>
    </section>
    <section class="degradado mt-50">
        <div class="container glass-container">
            <div v-if="loading" class="text-center text-white">
                Cargando skins...
            </div>
            <div v-else class="section2Cartas d-flex justify-content-center align-items-center gap-5 flex-wrap  mt-25">

                <!-- SKINS -->
                <div v-for="skin in skins" :key="skin.id" class="card-container mb-50">

                    <div class="card-inner">
                        <!-- FRONT -->
                        <div class="page page1">
                            <img :src="getImage(skin)" alt="">
                        </div>

                        <!-- BACK -->
                        <div class="page page2">
                            <!-- dados esquinas -->
                            <img src="/images/dado.svg" class="dado dado-tl">
                            <img src="/images/dado.svg" class="dado dado-br">

                            <!-- cotenido -->
                            <div class="contenido">
                                <h2 class="fw-bold titulo-carta">{{ skin.nombre }}</h2>
                                <p>Skin exclusiva del casino</p>
                                <span class="fw-bold">{{ skin.precio }} €</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <h5 class="titulo-carta mt-4">{{ skin.nombre }}</h5>
                        <p class=" mt-1">{{ skin.precio }} €</p>
                        <!-- Submit Button -->
                        <BotonesPrincipal
                            :label="`Comprar `"
                            @click="comprarSkin(skin)"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import BotonesPrincipal from '@/components/BotonesPrincipal.vue';

const skins = ref([])


const getImage = (skin) => {
    return skin.skin || '/images/blacjackFondo.png'
}

onMounted(async () => {
    try {
        const [resSkins, resRankings, resRankingBeneficio] = await Promise.all([
            axios.get('/api/skins'),

        ])

        skins.value = resSkins.data.data

    } catch (error) {
        console.error("Error cargando los datos:", error)
    } finally {
        loading.value = false
    }
})
</script>

<style scoped>
#header {
    padding-top: 44px;
    height: 550px;
    background-image: linear-gradient(to bottom,rgba(126,82,144,0) 60%,#110c22 100%),url(/images/header_tienda.png);
    background-size: cover;
    background-position: 50% 40%;
    background-repeat: no-repeat;
}

.titulo-tienda {
    justify-content: center;
}

.titulo-tienda h1 {
    font-weight: bold;
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
    text-align: center;
}
.titulo-card {
    width: 50%;
    padding: 25px;
    margin-top: 150px;
    margin-left: 130px;
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

.section2Cartas {
    perspective: 1000px;
}

.glass-container {
    position: relative;

    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 20px;
    padding: 30px;
    z-index: 1;
}

.glass-container::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    padding: 1px; 
    background: linear-gradient(to bottom,rgba(255, 255, 255, 0.6),rgba(255, 255, 255, 0.15),rgba(255, 255, 255, 0));
    -webkit-mask: linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
}

.card-container {
    width: 248px;
    height: 367px;
}

.card-inner {
    width: 100%;
    height: 100%;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.6s;
}

.card-container:hover .card-inner {
    transform: rotateY(180deg);
}

.card-footer {
    margin-top: auto;
    text-align: center;
}

.titulo-carta{
    font-weight: bold;
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
    text-align: center;
}
.card-footer button {
    width: 100%;
    border-radius: 50px;
}

.page {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 15px;
    backface-visibility: hidden;
}

.page1 img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.page2 {
    background: white;
    transform: rotateY(180deg);
}


.contenido {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    color: black;
}

.titulo-carta {
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
}

.dado {
    width: 50px;
    height: 50px;
    position: absolute;
}

.dado-tl {
    top: 10px;
    left: 10px;
}

.dado-br {
    bottom: 10px;
    right: 10px;
}
.degradado {
    background: linear-gradient(180deg,#110c22 60%, #7E5290 150%);
    padding-bottom: 60px;
}  

@media (max-width: 480px) {
    .carousel-item .d-flex {
        flex-direction: column;
        align-items: center;
        gap: 0;
    }

    .carousel-item .img-hover-container {
        display: none;
        width: 90%;
        margin: 0 auto;
    }

    .carousel-item .img-hover-container:first-child {
        display: block;
    }

    .img-fija {
        height: 200px;
    }

    .hover-text {
        top: 70%;
        font-size: 0.7rem;
        height: 20%;
        padding: 5px;
    }

    .texto-menu {
        font-size: 0.7rem;
        line-height: 18px;
    }
}

</style>