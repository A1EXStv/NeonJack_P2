<template>
    <!--Header---->
    <div class="user-new-layout">
        <nav class="navbar custom-navbar navbar-expand-lg">
            <div class="container-fluid position-relative d-flex align-items-center">
                <button class="navbar-toggler d-lg-none me-2" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#menu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <router-link class="logo navbar-brand mx-auto mx-lg-0" to="/">
                    <img src="/images/logoSinFondo.png" alt="Logo">
                </router-link>

                <div class="menu-center d-none d-lg-flex">
                    <ul class="navbar-nav gap-5">
                        <li class="nav-item"><router-link class="nav-link" to="/rules">Reglas</router-link></li>
                        <li class="nav-item"><router-link class="nav-link" to="/app">Inicio</router-link></li>
                        <li class="nav-item"><router-link class="nav-link" to="/shop">Tienda</router-link></li>
                        <li class="nav-item"><router-link class="nav-link" to="/app/salas">Jugar</router-link></li>
                    </ul>
                </div>

                <router-link to="/app/profile" class="ms-auto">
                    <svg class="user-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path
                            d="M144 128a80 80 0 1 1 160 0 80 80 0 1 1 -160 0zm208 0a128 128 0 1 0 -256 0 128 128 0 1 0 256 0zM48 480c0-70.7 57.3-128 128-128l96 0c70.7 0 128 57.3 128 128l0 8c0 13.3 10.7 24 24 24s24-10.7 24-24l0-8c0-97.2-78.8-176-176-176l-96 0C78.8 304 0 382.8 0 480l0 8c0 13.3 10.7 24 24 24s24-10.7 24-24l0-8z" />
                    </svg>
                </router-link>
            </div>
        </nav>
        <!----Menu lateral-->
        <aside v-if="!route.meta.hideSidebar" class="custom-sidebar">
            <!--Avatar-->
            <div class="sidebar-inner">
                <div class="sidebar-user">
                    <Avatar 
                        :image="auth.user.avatar || 'https://bootdey.com/img/Content/avatar/avatar7.png'" 
                        size="large" 
                        shape="circle"
                        class="sidebar-avatar"
                    />
                    <span class="sidebar-name">{{ auth.user.name }}</span>
                </div>
                <div class="sidebar-section">CONTENIDO</div>
                <router-link to="/app/profile" class="sidebar-link">
                    <i class="pi pi-user"></i>
                    <span>Perfil</span>
                </router-link>
                <router-link to="/app/transacciones" class="sidebar-link">
                    <i class="pi pi-shopping-cart"></i>
                    <span>Transacciones</span>
                </router-link>
                <router-link to="/app/skins" class="sidebar-link">
                    <i class="pi pi-palette"></i>
                    <span>Skins</span>
                </router-link>
                <div class="sidebar-footer">
                    <button class="btn-logout " @click="handleLogout">
                        <i class="pi pi-sign-out"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </div>
            </div>
        </aside>
        <!---Movile-->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="menu">
            <div class="offcanvas-header">
                <h5 class="menu-offcanvas">NEON JACK</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav">
                    <li class="nav-item"><router-link class="nav-link" to="/app">INICIO</router-link></li>
                    <li class="nav-item"><router-link class="nav-link" to="/app/posts">POSTS</router-link></li>
                    <li class="nav-item"><router-link class="nav-link" to="/app/profile">MI PERFIL</router-link></li>
                </ul>
            </div>
        </div>

        <main class="main-user-content" :class="{ 'no-sidebar': route.meta.hideSidebar }">
            <router-view />
        </main>

        <FooterLayout />
    </div>
</template>

<script setup>
import FooterLayout from './FooterLayout.vue'
import { authStore } from "@/store/auth";
import Avatar from "primevue/avatar";
import { useRouter, useRoute } from "vue-router";

const auth = authStore();
const router = useRouter();
const route = useRoute();

const handleLogout = async () => {
    await auth.logout(); 
    router.push('/'); 
};
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
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 10px;
    z-index: 1000;
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

.logo img {
    height: 50px;
}

.main-user-content {
    padding-top: 140px;
    padding-bottom: 40px;
  
}
@media (min-width: 992px) {
    .main-user-content:not(.no-sidebar) {
        padding-left: 300px;
        padding-right: 40px;
    }
    .main-user-content.no-sidebar {
        padding-left: 40px;
        padding-right: 40px;
    }
}
@media (max-width: 991px) {
    .navbar-brand {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }
}

/*Sidebar */
.custom-sidebar {
   position: absolute;
    top: 140px;    
    left: 60px;  
    width: 260px;
  /*  height: 100%;*/
    z-index: 999;
}

.sidebar-inner {
    background: rgba(96, 92, 92, 0.5) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 7px;
    min-height: 820px;
    padding: 20px;
    display: flex;
    flex-direction: column;

}

.sidebar-section {
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1.5px;
    margin: 25px 0 10px 12px;
    text-transform: uppercase;
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 18px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    margin-bottom: 4px;
}

.sidebar-link i {
    font-size: 1.1rem;
}

.custom-sidebar .router-link-active {
    position: relative;
}

.custom-sidebar .router-link-active::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #9C5CCB, #818AC8, #3BC3DB);
    border-radius: 4px;
}

.sidebar-footer {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-logout {
    width: 100%;
    background: transparent;
    border: none;
    color: #ff5f5f;
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 18px;
    cursor: pointer;
    transition: 0.3s;
}

.btn-logout:hover {
    background: rgba(255, 95, 95, 0.1);
    border-radius: 10px;
}

/****AVATAR***/

.sidebar-user {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 20px;
    gap: 10px;
}

.sidebar-avatar {
    margin-top: 25px;
    border: 2px solid transparent;
    background:
        linear-gradient(#12122a, #12122a) padding-box,
        linear-gradient(90deg, #9C5CCB, #3BC3DB) border-box;
    width: 80px;
    height: 80px;
}

.sidebar-name {
    font-size: 20px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.5);
    letter-spacing: 1.5px;
    text-transform: uppercase;;
}
@media (max-width: 991px) {
    .custom-sidebar {
        display: none;
    }
}
</style>