<template>
    <div class="min-h-screen flex items-center justify-center py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Logo y título -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold">
                    Bienvenido a SQL Check!
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Inicia sesión para continuar
                </p>
            </div>

            <!-- Formulario -->
            <Card>
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
                        <Button
                            type="submit"
                            :label="$t('login')"
                            :loading="processing"
                            :disabled="processing"
                            class="w-full"
                            size="large"
                        />

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

/* Estilos para InputText de PrimeVue */
:deep(.p-inputtext) {
    width: 100%;
}

/* Estilos para Password de PrimeVue */
:deep(.p-password) {
    width: 100%;
}

:deep(.p-password-input) {
    width: 100%;
}

/* Estilos para Button de PrimeVue */
:deep(.p-button) {
    width: 100%;
}
</style>
