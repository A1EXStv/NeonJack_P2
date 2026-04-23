<template>
      <!-------------------Menuuuuuuu------------------>
        <nav class="navbar custom-navbar navbar-expand-lg">
            <div class="container-fluid position-relative d-flex align-items-center">

                <button class="navbar-toggler d-lg-none me-2" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#menu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <a class="logo navbar-brand mx-auto mx-lg-0" href="/"><img src="/images/logo_sin_fondo.webp"
                        alt="Logo"></a>
                <div class="menu-center d-none d-lg-flex">
                    <ul class="navbar-nav gap-5">
                        <li class="nav-item"><a class="nav-link" href="/rules">Reglas</a></li>
                        <li class="nav-item"><router-link class="nav-link" :to="isLogged ? '/app' : '/'">Inicio</router-link></li>
                        <li class="nav-item"><a class="nav-link" href="/shop">Tienda</a></li>
                    </ul>
                </div>
                <div class="d-flex align-items-center">
                    <ul>
                        <li v-if="isLogged">{{ balance }} F</li>
                    </ul>
                    <router-link v-if="!isLogged" to="/login">
                        <svg class="user-icon ms-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path d="M144 128a80 80 0 1 1 160 0 80 80 0 1 1 -160 0zm208 0a128 128 0 1 0 -256 0 128 128 0 1 0 256 0zM48 480c0-70.7 57.3-128 128-128l96 0c70.7 0 128 57.3 128 128l0 8c0 13.3 10.7 24 24 24s24-10.7 24-24l0-8c0-97.2-78.8-176-176-176l-96 0C78.8 304 0 382.8 0 480l0 8c0 13.3 10.7 24 24 24s24-10.7 24-24l0-8z"/>
                        </svg>
                    </router-link>

                    <!-- Usuario logueado -->
                    <router-link v-else to="/app/profile">
                        <svg class="user-icon ms-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path d="M144 128a80 80 0 1 1 160 0 80 80 0 1 1 -160 0zm208 0a128 128 0 1 0 -256 0 128 128 0 1 0 256 0zM48 480c0-70.7 57.3-128 128-128l96 0c70.7 0 128 57.3 128 128l0 8c0 13.3 10.7 24 24 24s24-10.7 24-24l0-8c0-97.2-78.8-176-176-176l-96 0C78.8 304 0 382.8 0 480l0 8c0 13.3 10.7 24 24 24s24-10.7 24-24l0-8z"/>
                        </svg>
                    </router-link>
                </div>
                <!-- Usuario NO logueado -->
            </div>
        </nav>

          <!-- Offcanvas (menú móvil) -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="menu">
            <div class="offcanvas-header">
                <h5 class="menu-offcanvas">NEON JACK</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link " href="#">REGLAS</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">INICIO</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">TIENDA</a></li>
                </ul>
            </div>
        </div>
        <router-view />
        <FooterLayout /> 
        
</template>
<script setup>
import { computed } from 'vue'
import FooterLayout from './FooterLayout.vue'
import { authStore } from '@/store/auth'

const auth = authStore()

const balance = computed(() => auth.user?.wallet || 0)
const isLogged = computed(() => auth.authenticated)
</script>
<style scoped>
.custom-navbar {
    position: absolute;
    top: 25px;
    left: 50%;
    transform: translateX(-50%);
    width: 95%;
    max-width: 1400px;  
    background: rgba(96, 92, 92, 0.5) !important;
    backdrop-filter: blur(16px); 
    -webkit-backdrop-filter: blur(16px);
    border-radius: 7px;
    border: 1px solid rgba(255,255,255,0.2);

    padding: 10px; 
}
.menu-center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}
.nav-link {
    color: white !important;
    
}
.user-icon {
    width: 24px;
    height: 24px;
    fill: white;
}
.navbar-toggler {
    border: none;
}
.navbar-toggler-icon {
    filter: invert(1);
}
.offcanvas{
    background: rgba(17, 21, 36, 0.5) !important;
    backdrop-filter: blur(16px); 
    -webkit-backdrop-filter: blur(16px);
    border-radius: 7px;
    border: 1px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
}
.menu-offcanvas{
    color: white !important;
}
.logo img {
    height: 50px;
}

@media (max-width: 991px) {
    .navbar-brand {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }
}
</style>



