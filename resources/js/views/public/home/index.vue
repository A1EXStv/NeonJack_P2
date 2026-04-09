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
        <p class=" text-white text-center p-3">bdmgoishuohgouihreohgfoihgioghioshioghiohgiohgio</p>

        <div v-if="loading" class="text-center text-white">
            Cargando skins...
        </div>
        <div v-else class="section2Cartas d-flex justify-content-center align-items-center gap-5 flex-wrap">

            <!-- SKINS -->
            <div v-for="skin in skins" :key="skin.id" class="card-container position-relative">
                <!-- CARA FRONTAL -->
                <div class="page page1 w-100 h-100 position-absolute d-flex justify-content-center">
                    <img :src="getImage(skin)" alt="">
                </div>
                
                <div class="page page2">
                     <!-- CARA TRASERA -->
                    <div class=" page2 w-100 h-100 d-flex justify-content-start align-items-start">
                        <h2 class="fw-bold titulo-carta">prueb</h2>
                        
                    </div>
                    <!-- CARA TRASERA -->
                    <div class="page page2 w-100 h-100 d-flex justify-content-center align-items-center">
                        <h2 class="fw-bold titulo-carta">{{ skin.nombre }}</h2>
                        <p>Skin exclusiva del casino</p>
                        <span class="fw-bold">{{ skin.precio }} €</span>
                    </div>

                    <!-- CARA TRASERA -->
                    <div class="page page2 w-100 h-100 d-flex justify-content-start align-items-end ">
                        <h2 class="fw-bold titulo-carta">prueb</h2>
                        
                    </div>
                </div>
               
            </div>
        </div>
        <div class="d-flex justify-content-center align-items-center p-4">
            <a class="btn btn-primary " href="#">¡CONSIGUE TU CARTA AHORA!</a>
        </div>
    </section>
    <section class="pb-4">
        <div class="titulo-skins text-center">
            <h2>¡AÑADE TU NOMBRE CON LOS MÁS GRANDES!</h2>
        </div>
        <div class="tabla-separacion d-flex justify-content-center align-items-center flex-wrap mt-4">
            <div class="ranking-card overflow-hidden">
                <div class="ranking-header text-center text-white fw-bold">
                    TOP VICTORIAS
                </div>
                <div class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">1</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">VEGETTA777</span>
                        <span class="points">777 partidas</span>
                    </div>
                    <img src="/images/blacjackFondo.png" alt="Avatar" class="avatar rounded-5">
                </div>

                <div class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">2</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">ANUEL AA</span>
                        <span class="points">510 partidas</span>
                    </div>
                    <img src="/images/blacjackFondo.png" alt="Avatar" class="avatar rounded-5">
                </div>

                <div class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">3</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">JORDI WILD</span>
                        <span class="points">420 partidas</span>
                    </div>
                    <img src="/images/blacjackFondo.png" alt="Avatar" class="avatar rounded-5">
                </div>

                <div class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">4</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">BROMAPOMA</span>
                        <span class="points">220 partidas</span>
                    </div>
                    <img src="/images/blacjackFondo.png" alt="Avatar" class="avatar rounded-5">
                </div>

            </div>
            <div class="ranking-card overflow-hidden">
                <div class="ranking-header text-center text-white fw-bold">
                    TOP VICTORIAS
                </div>

                <div class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">1</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">VEGETTA777</span>
                        <span class="points">777 partidas</span>
                    </div>
                    <img src="/images/blacjackFondo.png" alt="Avatar" class="avatar rounded-5">
                </div>

                <div class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">2</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">ANUEL AA</span>
                        <span class="points">510 partidas</span>
                    </div>
                    <img src="/images/blacjackFondo.png" alt="Avatar" class="avatar rounded-5">
                </div>

                <div class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">3</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">JORDI WILD</span>
                        <span class="points">420 partidas</span>
                    </div>
                    <img src="/images/blacjackFondo.png" alt="Avatar" class="avatar rounded-5">
                </div>

                <div class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">4</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">BROMAPOMA</span>
                        <span class="points">220 partidas</span>
                    </div>
                    <img src="/images/blacjackFondo.png" alt="Avatar" class="avatar rounded-5">
                </div>

            </div>
            <div class="ranking-card overflow-hidden">
                <div class="ranking-header text-center text-white fw-bold">
                    TOP VICTORIAS
                </div>

                <div class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">1</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">VEGETTA777</span>
                        <span class="points">777 partidas</span>
                    </div>
                    <img src="/images/blacjackFondo.png" alt="Avatar" class="avatar rounded-5">
                </div>

                <div class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">2</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">ANUEL AA</span>
                        <span class="points">510 partidas</span>
                    </div>
                    <img src="/images/blacjackFondo.png" alt="Avatar" class="avatar rounded-5">
                </div>

                <div class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">3</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">JORDI WILD</span>
                        <span class="points">420 partidas</span>
                    </div>
                    <img src="/images/blacjackFondo.png" alt="Avatar" class="avatar rounded-5">
                </div>

                <div class="ranking-item d-flex align-items-center justify-content-between text-white">
                    <span class="rank fw-bold">4</span>
                    <div class="info d-flex flex-column align-items-center">
                        <span class="name fw-bold">BROMAPOMA</span>
                        <span class="points">220 partidas</span>
                    </div>
                    <img src="/images/blacjackFondo.png" alt="Avatar" class="avatar rounded-5">
                </div>
            </div>
        </div>
    </section>
    <section class="container px-5">
        <div class="titulo-skins text-center">
            <h2>NEONJACK TU DINERO SIEMPRE PROTEGIDO</h2>
        </div>
        <div class="row g-4 text-center align-content-center">
            <div class="col-md-4">
                <div class="p-3">
                    <svg class="section3-iconos text-primary mt-3" viewBox="0 0 512 512">
                        <path
                            d="M256 0c4.6 0 9.2 1 13.4 2.9L457.8 82.8c22 9.3 38.4 31 38.3 57.2-.5 99.2-41.3 280.7-213.6 363.2-16.7 8-36.1 8-52.8 0-172.4-82.5-213.1-264-213.6-363.2-.1-26.2 16.3-47.9 38.3-57.2L242.7 2.9C246.9 1 251.4 0 256 0z" />
                    </svg>
                    <h5 class="fw-bold mt-4">BLINDAJE TOTAL</h5>
                    <p class="small text-justify ">
                        Utilizamos tecnología de encriptación de grado bancario para asegurar que cada movimiento en tu
                        cuenta sea privado.
                        Tu capital está protegido por los estándares de ciberseguridad más estrictos del mercado.
                    </p>
                </div>
            </div>
            <div class="col-md-4 ">
                <div class="p-3">
                    <svg class="section3-iconos text-primary mt-3" viewBox="0 0 384 512">
                        <path
                            d="M128 96l0 64 128 0 0-64c0-35.3-28.7-64-64-64s-64 28.7-64 64zM64 160l0-64C64 25.3 121.3-32 192-32S320 25.3 320 96l0 64c35.3 0 64 28.7 64 64l0 224c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 224c0-35.3 28.7-64 64-64z" />
                    </svg>
                    <h5 class="fw-bold mt-4">PASARELA SEGURA</h5>
                    <p class=" small text-justify">
                        Gestiona tus fondos con total tranquilidad. Soportamos los métodos de pago más fiables,
                        garantizando que tus depósitos
                        y transferencias se ejecuten de forma íntegra y sin fricciones.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <svg class="section3-iconos mt-3 text-primary" viewBox="0 0 448 512">
                        <path
                            d="M168.5 0c-13.3 0-24 10.7-24 24s10.7 24 24 24l32 0 0 25.3c-108 11.9-192 103.5-192 214.7 0 119.3 96.7 216 216 216s216-96.7 216-216c0-39.8-10.8-77.1-29.6-109.2l28.2-28.2c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-23.4 23.4c-32.9-30.2-75.2-50.3-122-55.5l0-25.3 32 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-112 0zm80 184l0 104c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-104c0-13.3 10.7 24 24-24s24 10.7 24 24z" />
                    </svg>
                    <h5 class="fw-bold mt-4">LIQUIDEZ EN 48-72h</h5>
                    <p class=" small text-justify ">
                        No te hacemos esperar por lo que es tuyo. Una vez solicitada, procesamos tu retirada para que
                        recibas tus ganancias
                        en un plazo garantizado de 48 a 72 horas hábiles.
                    </p>
                </div>
            </div>

        </div>
    </section>
    <footer class="footer-neon text-white py-5">
        <div class="container">
            <div class="row align-items-center mb-4 text-center text-md-start">

                <div class="col-12 col-md-4 mb-4 mb-md-0">
                    <div class="footer-logo">
                        <img src="/images/logoSinFondo.png" alt="Logo" style="height: 80px;">
                    </div>
                </div>

                <div class="col-12 col-md-4 mb-4 mb-md-0 text-center">
                    <ul class="list-unstyled mb-0">
                        <li><a href="#" class="text-white text-decoration-none fw-bold">Términos y Condiciones</a></li>
                        <li><a href="#" class="text-white text-decoration-none fw-bold">Política de Privacidad</a></li>
                        <li><a href="#" class="text-white text-decoration-none fw-bold">Juego Responsable</a></li>
                    </ul>
                </div>

                <div class="col-12 col-md-4 d-flex justify-content-center justify-content-md-end gap-3">
                    <a href="#" class="social-icon d-flex align-items-center justify-content-center"><svg
                            xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                            viewBox="0 0 320 512">
                            <path
                                d="M80 299.3l0 212.7 116 0 0-212.7 86.5 0 18-97.8-104.5 0 0-34.6c0-51.7 20.3-71.5 72.7-71.5 16.3 0 29.4 .4 37 1.2l0-88.7C291.4 4 256.4 0 236.2 0 129.3 0 80 50.5 80 159.4l0 42.1-66 0 0 97.8 66 0z" />
                        </svg></a>
                    <a href="#" class="social-icon d-flex align-items-center justify-content-center"><svg
                            xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                            viewBox="0 0 512 512">
                            <path
                                d="M459.4 151.7c.3 4.5 .3 9.1 .3 13.6 0 138.7-105.6 298.6-298.6 298.6-59.5 0-114.7-17.2-161.1-47.1 8.4 1 16.6 1.3 25.3 1.3 49.1 0 94.2-16.6 130.3-44.8-46.1-1-84.8-31.2-98.1-72.8 6.5 1 13 1.6 19.8 1.6 9.4 0 18.8-1.3 27.6-3.6-48.1-9.7-84.1-52-84.1-103l0-1.3c14 7.8 30.2 12.7 47.4 13.3-28.3-18.8-46.8-51-46.8-87.4 0-19.5 5.2-37.4 14.3-53 51.7 63.7 129.3 105.3 216.4 109.8-1.6-7.8-2.6-15.9-2.6-24 0-57.8 46.8-104.9 104.9-104.9 30.2 0 57.5 12.7 76.7 33.1 23.7-4.5 46.5-13.3 66.6-25.3-7.8 24.4-24.4 44.8-46.1 57.8 21.1-2.3 41.6-8.1 60.4-16.2-14.3 20.8-32.2 39.3-52.6 54.3z" />
                        </svg></a>
                    <a href="#" class="social-icon d-flex align-items-center justify-content-center"><svg
                            xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                            viewBox="0 0 448 512">
                            <path
                                d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM402.4 344.6c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z" />
                        </svg></a>

                </div>

            </div>

            <hr class="border-secondary">

            <div class="text-center mt-4">
                <p class="text-secondary small mb-0">2024 Neon Jack Casino. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { ro } from 'yup-locales'

const skins = ref([])
const loading = ref(true)

// funcion para obtener la imagen de la skin
const getImage = (skin) => {
  return skin.skin || '/images/blacjackFondo.png'
}

// componente cargar skins
onMounted(async () => {
  try {
    const response = await axios.get('/api/skins')

    // Accedemos al array de skins dentro del objeto `data` de la API
    skins.value = response.data.data.slice(0, 4)

  } catch (error) {
    console.error("Error cargando skins:", error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
#header {
    padding-top: 44px;
    height: 725px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-image: url(/images/neonjackHome.png);
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
    background: rgba(96, 92, 92, 0.5)!important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255,255,255,0.2);
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
    margin-bottom: 40px;
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

.page {
    border-radius: 15px;
    backface-visibility: hidden;
    transition: transform 0.6s;
}
.page1 {
    overflow: hidden;
}

.page1 img {
    object-fit: cover;
    width: 100%;
    height: 100%;
}

.page2 {
    background: white;
    color: black;
    transform: rotateY(180deg);
    flex-direction: column;
    padding: 20px;
    text-align: center;
}
.card-container:hover .page1 {
    transform: rotateY(-180deg);
}

.card-container:hover .page2 {
    transform: rotateY(0deg);
}
.titulo-carta{
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
}
.card-container:hover #page1 {
    transform: rotateY(-180deg);
}

.card-container:hover #page2 {
    transform: rotateY(0deg);
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
    border-right:3px solid #3BC3DB ;
    border-bottom: 3px solid #3BC3DB;
}
.tabla-separacion{
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


    /***************************Footer**************************/
.footer-neon{
    border-top: 5px solid #7E5290;
}
.social-icon {
    width: 40px;          
    height: 40px;         
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    border-radius: 50%; 
    color: white;        

}

.social-icon svg {
    display: block; 
}
.footer-neon ul li {
    margin-bottom: 8px;
}

</style>
