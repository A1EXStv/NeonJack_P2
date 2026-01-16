import { computed, reactive, readonly, watch, nextTick } from 'vue';
import {styleStore} from "@/store/style";

// Función para aplicar el modo oscuro
const applyDarkMode = (isDark) => {
    const html = document.documentElement;
    const body = document.body;
    
    if (isDark) {
        html.classList.add('app-dark', 'dark');
        body.classList.add('dark');
    } else {
        html.classList.remove('app-dark', 'dark');
        body.classList.remove('dark');
    }
};

// Obtener el store y configurar el estado inicial
const store = styleStore();
let initialDarkTheme = false;

// Intentar obtener el tema desde localStorage primero
// pinia-plugin-persistedstate guarda directamente el estado del store
try {
    const savedTheme = localStorage.getItem('styleStore');
    if (savedTheme) {
        const themeData = JSON.parse(savedTheme);
        // pinia-plugin-persistedstate guarda el estado directamente
        // El formato puede ser: {darkTheme: false} o el valor directo
        if (themeData !== null && typeof themeData === 'object') {
            initialDarkTheme = themeData.darkTheme === true;
        }
    }
} catch (e) {
    console.error('Error al leer tema desde localStorage:', e);
}

// Si no hay tema guardado, usar false por defecto
// El store se sincronizará después cuando Pinia se inicialice

const layoutConfig = reactive({
    preset: 'Aura',
    primary: 'emerald',
    surface: null,
    darkTheme: initialDarkTheme,
    menuMode: 'static'
});

// Aplicar el modo oscuro inicial inmediatamente si estamos en el navegador
if (typeof document !== 'undefined') {
    applyDarkMode(initialDarkTheme);
}

// Watch para sincronizar cambios en darkTheme con el DOM
// Usar nextTick para asegurar que el DOM esté listo
watch(() => layoutConfig.darkTheme, (newValue) => {
    if (typeof document !== 'undefined') {
        // Usar requestAnimationFrame para asegurar que se aplique después del render
        requestAnimationFrame(() => {
            applyDarkMode(newValue);
        });
    }
});

const layoutState = reactive({
    staticMenuDesktopInactive: false,
    overlayMenuActive: false,
    profileSidebarVisible: false,
    configSidebarVisible: false,
    staticMenuMobileActive: false,
    menuHoverActive: false,
    activeMenuItem: null
});

export function useLayout() {
    const setPrimary = (value) => {
        layoutConfig.primary = value;
    };


    const setSurface = (value) => {
        layoutConfig.surface = value;
    };

    const setPreset = (value) => {
        layoutConfig.preset = value;
    };

    const setActiveMenuItem = (item) => {
        layoutState.activeMenuItem = item.value || item;
    };

    const setDefaultMode = () => {
        // Sincronizar con el store
        layoutConfig.darkTheme = store.getDarkTheme();
        applyDarkMode(layoutConfig.darkTheme);
    };

    const setMenuMode = (mode) => {
        layoutConfig.menuMode = mode;
    };

    const toggleDarkMode = () => {
        if (!document.startViewTransition) {
            executeDarkModeToggle();
            return;
        }

        document.startViewTransition(() => executeDarkModeToggle(event));
    };

    const executeDarkModeToggle = () => {
        const newTheme = !layoutConfig.darkTheme;
        layoutConfig.darkTheme = newTheme;
        store.setDarkTheme(newTheme);
        
        // Aplicar inmediatamente
        applyDarkMode(newTheme);
        
        // Forzar re-render de componentes reactivos
        // Esto asegura que todos los componentes se actualicen
        nextTick(() => {
            applyDarkMode(newTheme);
        });
    };

    const onMenuToggle = () => {
        if (layoutConfig.menuMode === 'overlay') {
            layoutState.overlayMenuActive = !layoutState.overlayMenuActive;
        }

        if (window.innerWidth > 991) {
            layoutState.staticMenuDesktopInactive = !layoutState.staticMenuDesktopInactive;
        } else {
            layoutState.staticMenuMobileActive = !layoutState.staticMenuMobileActive;
        }
    };

    const resetMenu = () => {
        layoutState.overlayMenuActive = false;
        layoutState.staticMenuMobileActive = false;
        layoutState.menuHoverActive = false;
    };


    const isSidebarActive = computed(() => layoutState.overlayMenuActive || layoutState.staticMenuMobileActive);

    const isDarkTheme = computed(() => layoutConfig.darkTheme);

    const getPrimary = computed(() => layoutConfig.primary);

    const getSurface = computed(() => layoutConfig.surface);

    return { layoutConfig: readonly(layoutConfig), layoutState: readonly(layoutState), onMenuToggle, isSidebarActive, isDarkTheme, getPrimary, getSurface, setActiveMenuItem, toggleDarkMode, setPrimary, setSurface, setPreset, resetMenu, setMenuMode, setDefaultMode };
}
