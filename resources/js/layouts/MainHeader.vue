<template>
    <header :class="[
        'sticky top-0 z-999 flex w-full transition-all duration-300 border-b shadow-sm backdrop-blur-md',
        isAdmin ? 'tail-admin-header' : 'tail-user-header'
    ]">
        <div class="flex grow items-center justify-between p-1 md:px-6 2xl:px-11">
            <div class="flex items-center gap-2 sm:gap-4">

                <!-- ADMIN -->
                <template v-if="isAdmin">
                    <!-- Mobile -->
                    <button 
                        @click="emit('toggleSidebar')" 
                        class="z-99999 flex items-center justify-center w-9 h-9 rounded-lg border lg:hidden"
                    >
                        <i class="pi pi-bars text-lg"></i>
                    </button>

                    <!-- Desktop -->
                    <button 
                        @click="emit('toggleCollapse')" 
                        class="hidden lg:flex items-center justify-center w-9 h-9 rounded-lg border"
                    >
                        <i :class="props.isCollapsed ? 'pi pi-angle-right' : 'pi pi-angle-left'" class="text-lg"></i>
                    </button>
                </template>

                <!-- USER -->
                <template v-else>
                    <h1 class="text-lg font-bold">Mi App</h1>

                    <p>Prubaaaaa</p>
                </template>

            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <ul class="flex items-center gap-1.5 sm:gap-2">
                    <li>
                        <button @click="toggleDarkMode" class="header-icon-button relative flex h-10 w-10 items-center justify-center rounded-lg border transition-all duration-200">
                            <i :class="isDarkTheme ? 'pi pi-sun' : 'pi pi-moon'" class="text-base"></i>
                        </button>
                    </li>

                    <li>
                        <div class="relative">
                            <button @click="toggleDropdown" class="header-user-button flex items-center gap-3 rounded-lg px-2 py-1.5 transition-all duration-200">
                                <span class="hidden text-right lg:block min-w-[80px]">
                                    <span class="block text-sm font-semibold leading-tight user-name">{{ user?.name || 'Usuario' }}</span>
                                    <span class="block text-xs leading-tight user-role opacity-80">{{ user?.roles?.[0]?.name || 'Rol' }}</span>
                                </span>
                                <div class="header-avatar relative h-10 w-10 shrink-0 rounded-full overflow-hidden ring-2 ring-offset-2">
                                    <img v-if="user?.avatar" :src="user.avatar" alt="User" class="h-full w-full object-cover"/>
                                    <div v-else class="flex h-full w-full items-center justify-center text-sm font-semibold avatar-initials">
                                        {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                                    </div>
                                </div>
                                <i class="pi pi-chevron-down text-xs transition-transform duration-200" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>

                            <transition name="dropdown-fade">
                                <div v-show="dropdownOpen" class="header-dropdown absolute right-0 mt-2 z-50 rounded-xl overflow-hidden shadow-2xl border min-w-[240px]">
                                    <div class="header-dropdown-header p-4 border-b">
                                        <p class="font-semibold text-sm">{{ user?.name || 'Usuario' }}</p>
                                        <p class="text-xs opacity-70 truncate">{{ user?.email || '' }}</p>
                                    </div>
                                    <ul class="p-2">
                                        <li>
                                            <router-link :to="isAdminPath ? '/admin/profile' : '/app/profile'" class="dropdown-menu-item" @click="dropdownOpen = false">
                                                <i class="pi pi-user text-primary"></i>
                                                <span>Mi Perfil</span>
                                            </router-link>
                                        </li>
                                        <li v-if="auth.is('admin') || auth.is('docent')">
                                            <router-link to="/admin" class="dropdown-menu-item" @click="dropdownOpen = false">
                                                <i class="pi pi-shield text-orange-500"></i>
                                                <span>Panel Admin</span>
                                            </router-link>
                                        </li>
                                        <li>
                                            <router-link to="/app" class="dropdown-menu-item" @click="dropdownOpen = false">
                                                <i class="pi pi-graduation-cap text-blue-500"></i>
                                                <span>Panel Usuario</span>
                                            </router-link>
                                        </li>
                                    </ul>
                                    <div class="border-t p-2">
                                        <button @click="logout" class="dropdown-menu-item text-red-500 hover:bg-red-50 w-full">
                                            <i class="pi pi-sign-out"></i>
                                            <span>Cerrar Sesión</span>
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useLayout } from '../composables/layout';
import useAuth from '../composables/auth';
import { authStore } from '../store/auth';

const route = useRoute();
const auth = authStore();
const { toggleDarkMode, isDarkTheme } = useLayout();
const { logout: logoutAuth } = useAuth();

// Props y Emits
const props = defineProps({
    sidebarOpen: Boolean,
    isCollapsed: Boolean
});
const emit = defineEmits(['toggleSidebar', 'toggleCollapse']);

// Lógica de estado
const dropdownOpen = ref(false);
const user = computed(() => auth.user);

// Determinar si estamos en la ruta de Admin para el diseño
const isAdminPath = computed(() => route.path.startsWith('/admin'));
const isAdmin = computed(() => auth.is('admin') && isAdminPath.value);

const toggleDropdown = () => dropdownOpen.value = !dropdownOpen.value;

const logout = () => {
    dropdownOpen.value = false;
    logoutAuth();
};

// Cerrar al hacer click fuera
const handleClickOutside = (event) => {
    if (dropdownOpen.value && !event.target.closest('.relative')) {
        dropdownOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<style scoped>
/* ============================================
   ESTILOS BASE (Admin / Claro)
   ============================================ */
.tail-admin-header {
    background-color: #ffffff;
    border-color: #e5e7eb;
    color: #1e293b;
}

.tail-admin-header .menu-toggle-btn,
.tail-admin-header .header-icon-button {
    background: #ffffff;
    border-color: #e5e7eb;
    color: #64748b;
}

/* ============================================
   ESTILOS USUARIO (Oscuro Profesional)
   ============================================ */
.tail-user-header {
    background-color: #0f172a; /* Slate 900 */
    border-color: #1e293b;
    color: #f8fafc;
}

.tail-user-header .user-name { color: #ffffff; }
.tail-user-header .user-role { color: #94a3b8; }
.tail-user-header .pi-chevron-down { color: #94a3b8; }

.tail-user-header .menu-toggle-btn,
.tail-user-header .header-icon-button {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.1);
    color: #f8fafc;
}

.tail-user-header .menu-toggle-btn:hover,
.tail-user-header .header-icon-button:hover {
    background: rgba(255, 255, 255, 0.15);
}

/* ============================================
   DROPDOWN & OTROS
   ============================================ */
.header-dropdown {
    background: white;
    color: #1e293b;
}

.dropdown-menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.625rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
}

.dropdown-menu-item:hover {
    background-color: #f1f5f9;
}

.avatar-initials {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    color: white;
}

/* Transiciones */
.dropdown-fade-enter-active, .dropdown-fade-leave-active {
    transition: all 0.2s ease;
}
.dropdown-fade-enter-from, .dropdown-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>