<template>
    <div class="min-h-screen mt-30 flex items-center justify-center py-8 px-4 sm:px-6 lg:px-8" style="background-color: #110c22;">    
        <div class="max-w-md w-full">
            <!-- Logo y título -->
            <div class="text-center mb-8">
                <img src="/images/logo_sin_fondo.webp" alt="Logo">
                <p class="mt-2 text-sm text-gray-600 gray:text-gray-400">
                    Inicia sesión para continuar
                </p>
            </div>

            <!-- Formulario -->
            <Card class="custom-card p-6 rounded-lg">
                <template #content>
                    <form @submit.prevent="submitLogin" class="space-y-6">
                        <!-- Email -->
                        <div class="flex flex-col gap-2">
                            <label for="email" class="font-medium">{{ $t('email') }}</label>
                            <InputText
                                id="email"
                                type="email"
                                v-model="loginForm.email"
                                placeholder="tu@email.com"
                                :class="{ 'p-invalid': validationErrors?.email }"
                            />
                            <small v-if="validationErrors?.email" class="text-red-500">
                                <div v-for="message in validationErrors.email" :key="message">
                                    {{ message }}
                                </div>
                            </small>
                        </div>

                        <!-- Password -->
                        <div class="flex flex-col gap-2">
                            <label for="password" class="font-medium">{{ $t('password') }}</label>
                            <Password
                                id="password"
                                v-model="loginForm.password"
                                placeholder="••••••••"
                                :toggleMask="true"
                                :feedback="false"
                                inputClass="w-full"
                                :class="{ 'p-invalid': validationErrors?.password }"
                                fluid
                            />
                            <small v-if="validationErrors?.password" class="text-red-500">
                                <div v-for="message in validationErrors.password" :key="message">
                                    {{ message }}
                                </div>
                            </small>
                        </div>

                        <!-- Remember me y Forgot password -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    v-model="loginForm.remember"
                                    inputId="remember"
                                    binary
                                />
                                <label for="remember" class="text-sm cursor-pointer">
                                    {{ $t('remember_me') }}
                                </label>
                            </div>
                            <router-link
                                :to="{ name: 'auth.forgot-password' }"
                                class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                            >
                                {{ $t('forgot_password') }}
                            </router-link>
                        </div>

                        <!-- Submit Button -->
                        <div class="boton-login"> 
                            <BotonesPrincipal
                                type="submit"
                                :label="$t('login')"
                                variant="primary"
                                :disabled="processing"
                                @click="submitLogin"
                            />
                        </div>
                        <!-- Register link -->
                        <div class="text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                ¿No tienes una cuenta?
                                <router-link
                                    :to="{ name: 'auth.register' }"
                                    class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                >
                                    Regístrate aquí
                                </router-link>
                            </p>
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </div>
</template>

<script setup>
import useAuth from '@/composables/auth';
import BotonesPrincipal from '@/components/BotonesPrincipal.vue';

const { loginForm, validationErrors, processing, submitLogin } = useAuth();
</script>
<style scoped>
/* Asegurar que PrimeIcons se muestren correctamente */
:deep(.pi) {
    font-family: 'primeicons' !important;
    font-style: normal;
    font-weight: normal;
    font-variant: normal;
    text-transform: none;
    line-height: 1;
    display: inline-block;
}

:deep(.p-inputtext:enabled:hover) {
    border-color: #818AC8; 
}

:deep(.p-inputtext:enabled:focus) {
    border-color: #818AC8;
    box-shadow: none;
}

:deep(.p-password-input:enabled:hover) {
    border-color: #818AC8;
}

:deep(.p-password-input:enabled:focus) {
    border-color: #818AC8;
    box-shadow: none;
}

:deep(.p-button) {
    width: 100%;
}

:deep(.custom-card.p-card) {
    background: rgba(255, 255, 255, 0.08) ;
    backdrop-filter: blur(12px) ;
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.15) ;
    border-radius: 0.75rem;
}

:deep(.custom-card .p-card-body) {
    background: transparent ;
}
.boton-login button {
    border-radius: 50px;
}

:deep(.p-checkbox .p-checkbox-box:hover) {
    border-color: #A5ACF5;
    background: transparent;
}

:deep(.p-checkbox.p-highlight .p-checkbox-box) {
    background-color: #818AC8;
}

:deep(.p-checkbox.p-highlight .p-checkbox-box:hover) {
    background: #6f78b8;
    border-color: #6f78b8;
}

:deep(.p-checkbox .p-checkbox-icon) {
    color: white;
}

:deep(.p-checkbox-checked .p-checkbox-box) {
    background: linear-gradient(90deg, #9C5CCB, #818AC8, #3BC3DB);
    border-color: #818AC8;
    background-color: #818AC8 !important;
}

</style>
