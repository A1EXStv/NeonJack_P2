<template>
    <div class="lobby-wrapper">
        <!-- Navbar -->
        <nav class="navbar custom-navbar navbar-expand-lg">
            <div class="container-fluid position-relative d-flex align-items-center">

                <button class="navbar-toggler d-lg-none me-2" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#lobbyMenu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <router-link class="logo navbar-brand mx-auto mx-lg-0" to="/">
                    <img src="/images/logoSinFondo.png" alt="Logo">
                </router-link>

                <div class="menu-center d-none d-lg-flex">
                    <ul class="navbar-nav gap-5">
                        <li class="nav-item">
                            <router-link class="nav-link" to="/">Inicio</router-link>
                        </li>
                        <li class="nav-item">
                            <router-link class="nav-link active-link" to="/app/salas">Salas</router-link>
                        </li>
                        <li class="nav-item">
                            <router-link class="nav-link" to="/app/profile">Perfil</router-link>
                        </li>
                    </ul>
                </div>

                <!-- User menu -->
                <div class="user-section ms-auto d-flex align-items-center gap-3">
                    <span class="user-name d-none d-lg-inline">{{ authUser?.name }}</span>
                    <button class="logout-btn" @click="handleLogout" title="Cerrar sesión">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Offcanvas (menú móvil) -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="lobbyMenu">
            <div class="offcanvas-header">
                <h5 class="menu-offcanvas">NEON JACK</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav mb-4">
                    <li class="nav-item"><router-link class="nav-link" to="/" data-bs-dismiss="offcanvas">INICIO</router-link></li>
                    <li class="nav-item"><router-link class="nav-link" to="/app/salas" data-bs-dismiss="offcanvas">SALAS</router-link></li>
                    <li class="nav-item"><router-link class="nav-link" to="/app/profile" data-bs-dismiss="offcanvas">PERFIL</router-link></li>
                </ul>
                <button class="btn btn-outline-light w-100" @click="handleLogout">Cerrar sesión</button>
            </div>
        </div>

        <!-- Page content -->
        <main class="lobby-content">
            <router-view />
        </main>
    </div>
</template>

<script setup>
import { authStore } from '../store/auth';
import useAuth from '@/composables/auth';

const auth = authStore();
const authUser = auth.user;
const { logout } = useAuth();

const handleLogout = () => {
    logout();
};
</script>

<style scoped>
.lobby-wrapper {
    min-height: 100vh;
    background: #110c22;
    background-image:
        radial-gradient(ellipse at 15% 40%, rgba(156, 92, 203, 0.15) 0%, transparent 50%),
        radial-gradient(ellipse at 85% 20%, rgba(59, 195, 219, 0.10) 0%, transparent 45%),
        radial-gradient(ellipse at 50% 90%, rgba(129, 138, 200, 0.08) 0%, transparent 50%);
}

.custom-navbar {
    position: fixed;
    top: 16px;
    left: 50%;
    transform: translateX(-50%);
    width: 95%;
    max-width: 1400px;
    background: rgba(96, 92, 92, 0.5) !important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: 7px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 10px;
    z-index: 100;
}

.menu-center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.nav-link {
    color: rgba(255, 255, 255, 0.8) !important;
    transition: color 0.2s;
}

.nav-link:hover,
.active-link {
    color: white !important;
}

.user-section {
    color: white;
}

.user-name {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.85);
    font-weight: 500;
}

.logout-btn {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.logout-btn svg {
    width: 20px;
    height: 20px;
    fill: rgba(255, 255, 255, 0.8);
    transition: fill 0.2s;
}

.logout-btn:hover svg {
    fill: #ff6b6b;
}

.navbar-toggler {
    border: none;
}

.navbar-toggler-icon {
    filter: invert(1);
}

.offcanvas {
    background: rgba(17, 21, 36, 0.95) !important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-left: 1px solid rgba(255, 255, 255, 0.1);
}

.menu-offcanvas {
    color: white !important;
}

.logo img {
    height: 50px;
}

.lobby-content {
    padding-top: 96px;
    min-height: 100vh;
}

@media (max-width: 991px) {
    .navbar-brand {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }
}
</style>
