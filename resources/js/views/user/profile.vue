<template>
    <div class="profile-container">
        <div class="perfil-contenedor">

            <!-- Avatar -->
            <div class="datos-card">
                <div class="custom-card">
                    <h3 class="card-title"> <span>PERFIL</span></h3>
                    <div class="flex flex-col md:flex-row items-center justify-between p-4 gap-6">
                        <div class="flex flex-col md:flex-row items-center gap-4 text-center md:text-left">
                            <div class="flex-shrink-0">
                                <Avatar :image="user.avatar || 'https://bootdey.com/img/Content/avatar/avatar7.png'"
                                    class="avatar-img" size="xlarge" shape="circle" />
                            </div>
                            <div class="flex flex-col">
                                <span class="avatar-title">
                                    {{ user.name }} {{ user.surname1 }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full md:w-auto">
                            <FileUpload name="picture" url="/api/users/updateimg" @before-upload="onBeforeUpload"
                                @upload="onTemplatedUpload($event)" accept="image/*" :maxFileSize="1500000" mode="basic"
                                :auto="true" chooseLabel="Cambiar Avatar" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos Personales -->
            <div class="datos-card">
                <h3 class="card-title"> <span>Datos Personales</span></h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="field-box">
                        <label>Nombre</label>
                        <input v-model="form.name" class="input" />
                    </div>
                    <div class="field-box">
                        <label>Email</label>
                        <input v-model="form.email" class="input" />
                    </div>
                    <div class="field-box">
                        <label>Primer Apellido</label>
                        <input v-model="form.surname1" class="input" />
                    </div>
                    <div class="field-box">
                        <label>Segundo Apellido</label>
                        <input v-model="form.surname2" class="input" />
                    </div>
                    <div class="field-box">
                        <label>Alias</label>
                        <input v-model="form.alias" class="input" />
                    </div>
                    <div class="field-box">
                        <label>Código Postal</label>
                        <input v-model="form.codigo_postal" class="input" />
                    </div>
                    <div class="field-box">
                        <label>Dirección</label>
                        <input v-model="form.direccion" class="input" />
                    </div>
                    <div class="field-box">
                        <label>Fecha Nacimiento</label>
                        <input type="date" v-model="form.fecha_nacimiento" class="input" />
                    </div>
                    <div class="field-box">
                        <label>DNI</label>
                        <input v-model="form.dni" class="input disabled" disabled />
                    </div>
                </div>

                <div class="mt-6 flex justify-center">
                    <button class="btn-primary" @click="updateUser">
                        Guardar cambios
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from "vue";
import { usePrimeVue } from 'primevue/config';
import useUsers from "@/composables/users";
import { authStore } from "@/store/auth";

const form = reactive({
    name: "",
    email: "",
    surname1: "",
    surname2: "",
    alias: "",
    codigo_postal: "",
    direccion: "",
    fecha_nacimiento: "",
    dni: ""
});

const auth = authStore();
const $primevue = usePrimeVue();
const { getUser, user } = useUsers();

onMounted(() => {
    getUser(auth.user.id);
});

watch(user, (newUser) => {
    if (newUser) {
        Object.assign(form, newUser);
    }
});

const updateUser = async () => {
    try {
        const response = await fetch(`/api/users/${auth.user.id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(form)
        });
        const data = await response.json();
        console.log("Respuesta backend:", data);
        auth.user = data.data ?? data;
    } catch (error) {
        console.error(error);
    }
};

const onBeforeUpload = (event) => {
    event.formData.append('id', user.value.id);
};

const onTemplatedUpload = async () => {
    await getUser(auth.user.id);
    auth.user.avatar = user.value.avatar;
};
</script>

<style scoped>
.profile-container {
    padding-left: 50px;
    padding-right: 20px;
}
.perfil-contenedor {
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.datos-card {
    background: #12122a;
    border-radius: 10px;
    padding: 24px;
    border: 1px solid rgba(156, 92, 203, 0.25);
}
.card-title {
    display: block;
    margin-bottom: 20px;
    padding-bottom: 12px;
    position: relative;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    font-size: 13px;
    font-weight: 600;
}
.card-title span {
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.card-title::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 1px;
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
}

.avatar-title {
    font-size: 20px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.5);
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.avatar-img {
    width: 80px;
    height: 80px;
}

.field-box {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-box label {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.4);
    letter-spacing: 1px;
    text-transform: uppercase;
}

.input {
    width: 100%;
    background: #0e0e22;
    border: 1px solid rgba(129, 138, 200, 0.3);
    color: white;
    padding: 9px 12px;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.input:focus {
    outline: none;
    border-color: #9C5CCB;
    box-shadow: 0 0 0 2px rgba(156, 92, 203, 0.15);
}

.input.disabled {
    opacity: 0.35;
    cursor: not-allowed;
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
    cursor: pointer;
}

:deep(.p-fileupload-choose-button):hover, :deep(.p-fileupload-choose-button) {
    display: inline-block;
    padding: 12px 35px;
    border-radius: 50px;
    text-decoration: none;
    color: white;
    font-weight: bold;
    text-transform: uppercase;
    background: linear-gradient(90deg, #9C5CCB, #818AC8, #3BC3DB) !important;
    border: none;
    cursor: pointer;
}


</style>