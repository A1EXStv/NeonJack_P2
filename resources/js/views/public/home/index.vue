<template>
    <section id="header">
        <!--TItulo-->
        <div class="col-12 col-md-6 header-content">
            <div class="titulo-card">
                <h1>EL FUTURO <br class="d-none d-md-inline"> ES DE LOS QUE <br class="d-none d-md-inline"> ARRIESGAN
                </h1>
                <div class="d-flex  text-center justify-content-center">
                    <p class="d-none d-md-block">Vive la experiencia del casino definitiva con tecnología de vanguardia
                        y premios reales</p>
                </div>
                <div class="d-flex justify-content-center">
                    <a class="btn btn-primary" href="#">JUEGA AHORA</a>
                </div>
            </div>
        </div>
    </section>
    <!-----------------Section 2------------------------>
    <section>
        <div class="titulo-skins text-center">
            <h2 class="pt-4">¡COMIENZA TU COLECCIÓN!</h2>
        </div>
        <p class=" text-white text-center p-3 mb-4">    Colecciona skins exclusivas, desbloquéalas en la tienda y equípalas para personalizar tu experiencia en el casino.</p>

        <div v-if="loading" class="text-center text-white">
            Cargando skins...
        </div>
        <div v-else class="section2Cartas d-flex justify-content-center align-items-center gap-5 flex-wrap mb-4">

            <!-- SKINS -->
            <div v-for="skin in skins" :key="skin.id" class="card-container">

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
            </div>
        </div>
        <div class="d-flex justify-content-center align-items-center p-4">
            <a class="btn btn-primary " href="/shop">¡CONSIGUE TU CARTA AHORA!</a>
        </div>
    </section>
    <section class="pb-4">
        <div class="titulo-skins text-center">
            <h2>¡AÑADE TU NOMBRE CON LOS MÁS GRANDES!</h2>
        </div>
        <div class="tabla-separacion d-flex justify-content-center align-items-center flex-wrap mt-4">
            <div class="ranking-card overflow-hidden">
                <div class="ranking-header text-center text-white fw-bold">
                    TOP RONDAS GANADAS
                </div>

                <div v-if="loading" class="ranking-item text-center text-white">
                    Cargando ranking...
                </div>

                <div v-else v-for="(jugador, index) in rankings" :key="jugador.id"
                    class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">{{ index + 1 }}</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">{{ jugador.name.trim() }}</span>
                        <span class="points">{{ jugador.gana }} rondas</span>
                    </div>
                    <img :src="jugador.avatar" alt="Avatar" class="avatar rounded-5">
                </div>
            </div>
            <div class="ranking-card overflow-hidden">
                <div class="ranking-header text-center text-white fw-bold">
                    TOP BENEFICIO POR MANO
                </div>

                <div v-if="loading" class="ranking-item text-center text-white">
                    Cargando ranking...
                </div>

                <div v-else v-for="(user, index) in rankingMano" :key="user.id"
                    class="ranking-item d-flex align-items-center justify-content-between text-white">

                    <span class="rank fw-bold">{{ index + 1 }}</span>

                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">{{ user.name }}</span>
                        <span class="points">{{ user.total }} €</span>
                    </div>

                    <img :src="user.avatar ? user.avatar : '/images/blacjackFondo.png'" alt="Avatar"
                        class="avatar rounded-5">
                </div>
            </div>
            <div class="ranking-card overflow-hidden">
                <div class="ranking-header text-center text-white fw-bold">
                    TOP BENEFICIO TOTAL
                </div>
                <div v-if="loading" class="ranking-item text-center text-white">
                    Cargando ranking...
                </div>

                <div v-else v-for="(user, index) in rankingBeneficio" :key="user.id"
                    class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">{{ index + 1 }}</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">{{ user.name }}</span>
                        <span class="points">{{ user.total }} €</span>
                    </div>
                    <img :src="user.avatar" class="avatar rounded-5">
                </div>
            </div>
        </div>
    </section>
    <section class="degradado">
        <div class="container">
            <div class="titulo-skins text-center">
                <h2>NEONJACK TU DINERO SIEMPRE PROTEGIDO</h2>
            </div>
            <div class="row text-center g-4 mt-2 mb-4">
                <div class="col-12 col-md-4">
                    <div class="p-3 d-flex flex-column align-items-center prueba">
                        <svg class="section3-iconos mt-3" viewBox="0 0 512 512">
                            <path
                                d="M256 0c4.6 0 9.2 1 13.4 2.9L457.8 82.8c22 9.3 38.4 31 38.3 57.2-.5 99.2-41.3 280.7-213.6 363.2-16.7 8-36.1 8-52.8 0-172.4-82.5-213.1-264-213.6-363.2-.1-26.2 16.3-47.9 38.3-57.2L242.7 2.9C246.9 1 251.4 0 256 0z" />
                        </svg>
                        <h5 class="fw-bold mt-4">BLINDAJE TOTAL</h5>
                        <p class=" small ">
                            Utilizamos tecnología de encriptación de grado bancario para asegurar que cada movimiento en
                            tu
                            cuenta sea privado.
                            Tu capital está protegido por los estándares de ciberseguridad más estrictos del mercado.
                        </p>
                    </div>
                </div>
                <div class="col-12 col-md-4 ">
                    <div class="p-3 d-flex flex-column align-items-center prueba">
                        <svg class="section3-iconos mt-3" viewBox="0 0 384 512">
                            <path
                                d="M128 96l0 64 128 0 0-64c0-35.3-28.7-64-64-64s-64 28.7-64 64zM64 160l0-64C64 25.3 121.3-32 192-32S320 25.3 320 96l0 64c35.3 0 64 28.7 64 64l0 224c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 224c0-35.3 28.7-64 64-64z" />
                        </svg>
                        <h5 class="fw-bold mt-4">PASARELA SEGURA</h5>
                        <p class=" small">
                            Gestiona tus fondos con total tranquilidad. Soportamos los métodos de pago más fiables,
                            garantizando que tus depósitos
                            y transferencias se ejecuten de forma íntegra y sin fricciones.
                        </p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 d-flex flex-column align-items-center prueba">
                        <svg class="section3-iconos mt-3" viewBox="0 0 448 512">
                            <path
                                d="M168.5 0c-13.3 0-24 10.7-24 24s10.7 24 24 24l32 0 0 25.3c-108 11.9-192 103.5-192 214.7 0 119.3 96.7 216 216 216s216-96.7 216-216c0-39.8-10.8-77.1-29.6-109.2l28.2-28.2c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-23.4 23.4c-32.9-30.2-75.2-50.3-122-55.5l0-25.3 32 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-112 0zm80 184l0 104c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-104c0-13.3 10.7 24 24-24s24 10.7 24 24z" />
                        </svg>
                        <h5 class="fw-bold mt-4">LIQUIDEZ EN 48-72h</h5>
                        <p class=" small ">
                            No te hacemos esperar por lo que es tuyo. Una vez solicitada, procesamos tu retirada para
                            que
                            recibas tus ganancias
                            en un plazo garantizado de 48 a 72 horas hábiles.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const skins = ref([])
const rankings = ref([])
const rankingBeneficio = ref([])
const rankingMano = ref([])
const loading = ref(true)

const getImage = (skin) => {
    return skin.skin || '/images/blacjackFondo.png'
}

onMounted(async () => {
    try {
        const resSkins = await axios.get('/api/skins')
        skins.value = resSkins.data.data.slice(0, 4)
    } catch (e) { console.error('Error skins:', e) }

    try {
        const resRankings = await axios.get('/api/ranking')
        rankings.value = resRankings.data.data ?? resRankings.data
    } catch (e) { console.error('Error ranking:', e) }

    try {
        const resRankingBeneficio = await axios.get('/api/ranking-beneficio')
        rankingBeneficio.value = resRankingBeneficio.data.data ?? resRankingBeneficio.data
    } catch (e) { console.error('Error ranking-beneficio:', e) }

    try {
        const resRankingTopMano = await axios.get('/api/ranking/top-mano')
        rankingMano.value = resRankingTopMano.data.data ?? resRankingTopMano.data
    } catch (e) { console.error('Error top-mano:', e) }

    loading.value = false
})
</script>

<style scoped>
#header {
    padding-top: 44px;
    height: 725px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-image: url(/images/neonjackHomeCalidad.png);
}

@media (max-width: 576px) {
    #header {
        height: 400px;
        background-size: cover;
        background-position: center top;
    }
}

section {
    padding: 20px 0;
}

.header-content {
    margin-top: 104px;
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
    text-align: left;
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

@media (max-width: 576px) {
    .header-content {
        margin: 0 auto;
        width: 300px;
        text-align: center;
        margin-top: 30px;
    }

    .titulo-card h1 {
        font-size: 1.25rem;
    }

    .btn-primary {
        padding: 10px 25px;
        font-size: 0.9rem;
    }
}

/***********************Section 2****************************************/
.titulo-skins {
    justify-content: center;
}

.titulo-skins h2 {
    font-weight: bold;
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
    text-align: center;
}

.section2Cartas {
    perspective: 1000px;
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
    border-radius: 15px;

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

/***********************Section 3****************************************/

.ranking-card {
    width: 345px;
    border-radius: 7px;
}

.ranking-header {
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    padding: 20px;
    font-size: 20px;
}

.ranking-item {
    padding: 12px 15px;
    border-left: 3px solid #3BC3DB;
    border-right: 3px solid #3BC3DB;
    border-bottom: 3px solid #3BC3DB;
}

.tabla-separacion {
    gap: 50px;
}

.rank {
    font-size: 32px;
}

.info {
    flex: 1;
    margin-left: 10px;
}

.name {
    display: block;
    font-size: 16px;
}

.points {
    font-size: 14px;
    color: #ccc;
    font-weight: bold;
}

.avatar {
    width: 33px;
    height: 33px;
}

.section3-iconos {
    width: 120px;
    height: 120px;
    fill: #8781C9;
}

.text-justify {
    text-align: justify;
}

.prueba {
    padding: 25px;
    border-radius: 7px;
    background: rgba(96, 92, 92, 0.5) !important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    height: 100%;
}

.degradado {
    background: linear-gradient(180deg, #110c22 50%, #7E5290 200%);
}
</style>
