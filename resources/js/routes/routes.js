import { authStore } from "../store/auth";

const AuthenticatedLayout = () => import('../layouts/AdminLayout.vue');
const AuthenticatedUserLayout = () => import('../layouts/UserLayout.vue');
const GuestLayout = () => import('../layouts/GuestLayout.vue');
const BlankLayout = () => import('../layouts/BlankLayout.vue');
const LobbyLayout = () => import('../layouts/LobbyLayout.vue');

async function requireLogin(to, from, next) {
    const auth = authStore();
    const isLogin = !!auth.authenticated;

    if (isLogin) {
        next()
    } else {
        next('/login')
    }
}

const hasAdmin = (roles = []) =>
    roles.some((role) => role?.name?.toLowerCase().includes('admin'));

async function guest(to, from, next) {
    const auth = authStore()
    let isLogin = !!auth.authenticated;

    if (isLogin) {
        next('/')
    } else {
        next()
    }
}

async function requireAdmin(to, from, next) {
    const auth = authStore();
    let isLogin = !!auth.authenticated;
    let user = auth.user;

    if (isLogin) {
        if (hasAdmin(user.roles)) {
            next()
        } else {
            next('/app')
        }
    } else {
        next('/login')
    }
}

export default [
    {
        path: '/',
        component: GuestLayout,
        children: [
            {
                path: '/',
                name: 'home',
                component: () => import('../views/public/home/index.vue'),
            },

            {
                path: 'login',
                name: 'auth.login',
                component: () => import('../views/auth/login/Login.vue'),
                beforeEnter: guest,
            },
            {
                path: 'register',
                name: 'auth.register',
                component: () => import('../views/auth/register/index.vue'),
                beforeEnter: guest,
            },
            {
                path: 'forgot-password',
                name: 'auth.forgot-password',
                component: () => import('../views/auth/passwords/Email.vue'),
                beforeEnter: guest,
            },
            {
                path: 'reset-password/:token',
                name: 'auth.reset-password',
                component: () => import('../views/auth/passwords/Reset.vue'),
                beforeEnter: guest,
            },
        ]
    },
    {
        
    },
    {
        path: '/app',
        component: AuthenticatedUserLayout,
        name: 'app',
        beforeEnter: requireLogin,
        meta: { breadCrumb: '.' },
        children: [
            {
                name: 'app.profile',
                path: 'profile',
                component: () => import('../views/user/profile.vue'),
                meta: { breadCrumb: 'Perfil' },
            },
        ]
    },
    {
        path: '/app',
        component: LobbyLayout,
        beforeEnter: requireLogin,
        children: [
            {
                name: 'game.lobby',
                path: 'salas',
                component: () => import('../views/user/game/Lobby.vue'),
                meta: { breadCrumb: 'Salas', hideBreadcrumb: true },
            },
        ]
    },
    {
        path: '/app',
        component: BlankLayout,
        beforeEnter: requireLogin,
        children: [
            {
                name: 'game.table',
                path: 'game/:salaId/:partidaId',
                component: () => import('../views/user/game/GameTable.vue'),
                meta: { breadCrumb: 'Partida', hideBreadcrumb: true },
            },
        ]
    },


    {
        path: '/admin',
        component: AuthenticatedLayout,
        beforeEnter: requireAdmin,
        meta: { breadCrumb: 'Dashboard' },
        children: [
            {
                name: 'admin.index',
                path: '',
                component: () => import('../views/admin/index.vue'),
                meta: {
                    breadCrumb: 'Admin',
                    hideBreadcrumb: true
                }
            },
            {
                name: 'profile.index',
                path: 'profile',
                component: () => import('../views/admin/profile/index.vue'),
                meta: { breadCrumb: 'Profile' }
            },

            {
                name: 'categories',
                path: 'categories',
                meta: { breadCrumb: 'Categories' },
                children: [
                    {
                        name: 'categories.index',
                        path: '',
                        component: () => import('../views/admin/categories/Index.vue'),
                        meta: {
                            breadCrumb: 'View category',
                            hideBreadcrumb: true
                        }
                    },
                ]
            },

            /***********Ruta transacciones**************** */
             {
                name: 'transacciones',
                path: 'transacciones',
                meta: { breadCrumb: 'Transacciones' },
                children: [
                    {
                        name: 'transacciones.index',
                        path: '',
                        component: () => import('../views/admin/transacciones/Index.vue'),
                        meta: {
                            breadCrumb: 'Transacciones',
                            hideBreadcrumb: true
                        }
                    }
                ]
            },
            {
                name: 'skins',
                path: 'skins',
                meta: { breadCrumb: 'Skins'},
                children: [
                    {
                        name: 'skins.index',
                        path: '',
                        component: () => import('../views/admin/skins/Index.vue'),
                        meta: {
                            breadCrumb: 'Skins',
                            hideBreadcrumb: true
                        }
                    },
                    {
                        name: 'admin.skins.edit',
                        path: 'edit/:id',
                        component: () => import('../views/admin/skins/Edit.vue'),
                        meta: {
                            breadCrumb: 'Editar Skins',
                            linked: false
                        }
                    }
                ]
            },

            /**********Ruta Ajustes *************************/
           {
                name: 'ajustes',
                path: 'ajustes',
                component: { template: '<router-view />' }, 
                meta: { breadCrumb: 'Ajustes' },
                children: [
                    {
                        name: 'ajustes.index',
                        path: '',
                        component: () => import('../views/admin/ajustes/index.vue')
                    },
                    {
                        name: 'ajustes.create',
                        path: 'create',
                        component: () => import('../views/admin/ajustes/Create.vue')
                    },
                    {
                        name: 'ajustes.update',
                        path: 'update/:id',
                        component: () => import('../views/admin/ajustes/Update.vue')
                    }
                ]
            },


            {
                name: 'permissions',
                path: 'permissions',
                meta: { breadCrumb: 'Permisos' },
                children: [
                    {
                        name: 'permissions.index',
                        path: '',
                        component: () => import('../views/admin/permissions/Index.vue'),
                        meta: {
                            breadCrumb: 'Permissions',
                            hideBreadcrumb: true
                        }
                    },
                ]
            },
            {
                name: 'users',
                path: 'users',
                meta: { breadCrumb: 'Usuarios' },
                children: [
                    {
                        name: 'users.index',
                        path: '',
                        component: () => import('../views/admin/users/Index.vue'),
                        meta: {
                            breadCrumb: 'Usuarios',
                            hideBreadcrumb: true // Ocultar breadcrumb del layout porque la Card tiene su propio header
                        }
                    },
                    {
                        name: 'users.create',
                        path: 'create',
                        component: () => import('../views/admin/users/Create.vue'),
                        meta: {
                            breadCrumb: 'Crear Usuario',
                            linked: false
                        }
                    },
                    {
                        name: 'users.edit',
                        path: 'edit/:id',
                        component: () => import('../views/admin/users/Edit.vue'),
                        meta: {
                            breadCrumb: 'Editar Usuario',
                            linked: false
                        }
                    }
                ]
            },

            {
                name: 'roles',
                path: 'roles',
                meta: { breadCrumb: 'Roles' },
                children: [
                    {
                        name: 'roles.index',
                        path: '',
                        component: () => import('../views/admin/roles/Index.vue'),
                        meta: {
                            breadCrumb: 'Roles',
                            hideBreadcrumb: true
                        }
                    },
                    {
                        name: 'admin.roles.edit',
                        path: 'edit/:id',
                        component: () => import('../views/admin/roles/Edit.vue'),
                        meta: {
                            breadCrumb: 'Editar Rol',
                            linked: false
                        }
                    }
                ]
            },
        ]
    },
    {
        path: "/:pathMatch(.*)*",
        name: 'NotFound',
        component: () => import("../views/errors/404.vue"),
    },
];
