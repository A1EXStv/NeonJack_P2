<template>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" style="background-color: #110c22;">
        <div class="max-w-2xl w-full">
            <!-- Logo y título -->
            <div class="text-center mb-8">
                <img src="/images/logo_sin_fondo.png" alt="Logo">
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Regístrate para comenzar
                </p>
            </div>

            <!-- Formulario -->
            <Card class="custom-card p-6 rounded-lg">
                <template #content>
                    <form @submit.prevent="submitRegister" class="space-y-6">
                        <!-- Name -->
                        <div class="flex flex-col gap-2">
                            <label for="name" class="font-medium">{{ $t('name') }}</label>
                            <InputText
                                id="name"
                                v-model="registerForm.name"
                                placeholder="Nombre completo"
                                :invalid="!!validationErrors?.name"
                            />
                            <small v-if="validationErrors?.name" class="text-red-500">
                                {{ validationErrors.name[0] }}
                            </small>
                        </div>

                        <!-- Surname1 y Surname2 -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="surname1" class="font-medium">{{ $t('surname1') }}</label>
                                <InputText
                                    id="surname1"
                                    v-model="registerForm.surname1"
                                    placeholder="Primer apellido"
                                    :invalid="!!validationErrors?.surname1"
                                />
                                <small v-if="validationErrors?.surname1" class="text-red-500">
                                    {{ validationErrors.surname1[0] }}
                                </small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="surname2" class="font-medium">{{ $t('surname2') }}</label>
                                <InputText
                                    id="surname2"
                                    v-model="registerForm.surname2"
                                    placeholder="Segundo apellido"
                                    :invalid="!!validationErrors?.surname2"
                                />
                                <small v-if="validationErrors?.surname2" class="text-red-500">
                                    {{ validationErrors.surname2[0] }}
                                </small>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex flex-col gap-2">
                            <label for="email" class="font-medium">{{ $t('email') }}</label>
                            <InputText
                                id="email"
                                type="email"
                                v-model="registerForm.email"
                                placeholder="tu@email.com"
                                :invalid="!!validationErrors?.email"
                            />
                            <small v-if="validationErrors?.email" class="text-red-500">
                                {{ validationErrors.email[0] }}
                            </small>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="postal_code" class="font-medium">{{ $t('codigo_postal') }}</label>
                                <InputText
                                    id="postal_code"
                                    v-model="registerForm.postal_code"
                                    placeholder="08001"
                                    :invalid="!!validationErrors?.postal_code"
                                />
                                <small v-if="validationErrors?.postal_code" class="text-red-500">
                                    {{ validationErrors.postal_code[0] }}
                                </small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="dni" class="font-medium">{{ $t('dni') }}</label>
                                <InputText
                                    id="dni"
                                    v-model="registerForm.dni"
                                    placeholder="12345678A"
                                    :invalid="!!validationErrors?.dni"
                                />
                                <small v-if="validationErrors?.dni" class="text-red-500">
                                    {{ validationErrors.dni[0] }}
                                </small>
                            </div>
                        </div>

                        <!-- Dirección -->
                        <div class="flex flex-col gap-2">
                            <label for="address" class="font-medium">{{ $t('direccion') }}</label>
                            <InputText
                                id="address"
                                v-model="registerForm.address"
                                placeholder="Calle, número, piso..."
                                :invalid="!!validationErrors?.address"
                            />
                            <small v-if="validationErrors?.address" class="text-red-500">
                                {{ validationErrors.address[0] }}
                            </small>
                        </div>

                        <!-- Fecha de nacimiento -->
                        <div class="flex flex-col gap-2">
                            <label for="fecha_nacimiento" class="font-medium">{{ $t('fecha_nacimiento') }}</label>
                            <InputText
                                id="fecha_nacimiento"
                                type="date"
                                v-model="registerForm.fecha_nacimiento"
                                :invalid="!!validationErrors?.fecha_nacimiento"
                            />
                            <small v-if="validatxionErrors?.fecha_nacimiento" class="text-red-500">
                                {{ validationErrors.fecha_nacimiento[0] }}
                            </small>
                        </div>

                        <!-- Password y Confirm Password -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="password" class="font-medium">{{ $t('password') }}</label>
                                <Password
                                    id="password"
                                    v-model="registerForm.password"
                                    placeholder="••••••••"
                                    toggleMask
                                    :feedback="false"
                                    :invalid="!!validationErrors?.password"
                                    fluid
                                />
                                <small v-if="validationErrors?.password" class="text-red-500">
                                    {{ validationErrors.password[0] }}
                                </small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="password_confirmation" class="font-medium">{{ $t('confirm_password') }}</label>
                                <Password
                                    id="password_confirmation"
                                    v-model="registerForm.password_confirmation"
                                    placeholder="••••••••"
                                    toggleMask
                                    :feedback="false"
                                    :invalid="!!validationErrors?.password_confirmation"
                                    fluid
                                />
                                <small v-if="validationErrors?.password_confirmation" class="text-red-500">
                                    {{ validationErrors.password_confirmation[0] }}
                                </small>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <BotonesPrincipal
                            type="submit"
                            :label="$t('register')"
                            :loading="processing"
                            :disabled="processing"
                            class="w-full"
                            size="large" 
                        />

                        <!-- Login link -->
                        <div class="text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                ¿Ya tienes una cuenta?
                                <router-link
                                    :to="{ name: 'auth.login' }"
                                    class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                >
                                    Inicia sesión aquí
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
import BotonesPrincipal from '../../../components/botones-principal.vue';

const { registerForm, validationErrors, processing, submitRegister } = useAuth();
</script>
<style scoped>
:deep(.custom-card.p-card) {
    background: rgba(255, 255, 255, 0.08) !important;
    backdrop-filter: blur(12px) !important;
    -webkit-backdrop-filter: blur(12px) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    border-radius: 0.75rem;
}

:deep(.custom-card .p-card-body) {
    background: transparent !important;
}
</style>