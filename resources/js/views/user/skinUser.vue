<template>
    <div class="skins-container">

        <div class="datos-card">
            <h3 class="card-title"><span>SKINS</span></h3>

            <!-- SKIN ACTIVA -->
            <div class="active-skin">
                <h4>Skin equipada</h4>

                <div v-if="activeSkin">
                    <img :src="activeSkin.image" class="active-img" />
                    <p>{{ activeSkin.nombre }}</p>
                </div>

                <p v-else>No tienes ninguna skin equipada</p>
            </div>

            <hr />

            <!-- LISTA DE SKINS -->
            <div class="skins-grid">

                <div v-for="skin in skins" :key="skin.id" class="skin-card">

                    <img :src="skin.image" class="skin-img" />

                    <p class="skin-name">{{ skin.nombre }}</p>

                    <button
                        v-if="activeSkin?.id !== skin.id"
                        class="btn-primary"
                        @click="activateSkin(skin.id)"
                    >
                        Activar
                    </button>

                    <button v-else disabled class="btn-active">
                        Equipada
                    </button>

                </div>

            </div>

        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { authStore } from "@/store/auth";

const auth = authStore();

const skins = ref([]);
const activeSkin = ref(null);

// Cargar skins
const getSkins = async () => {
    const res = await fetch("/api/skins");
    const data = await res.json();
    skins.value = data.data ?? data;
};

// Skin activa del usuario
const getActiveSkin = async () => {
    const res = await fetch("/api/users/" + auth.user.id);
    const data = await res.json();
    activeSkin.value = data.data.active_skin;
};

// Activar skin
const activateSkin = async (id) => {
    await fetch(`/api/skins/${id}/activate`, {
        method: "POST",
        headers: {
            "Authorization": `Bearer ${auth.token}`,
            "Content-Type": "application/json"
        }
    });

    await getActiveSkin();
};

onMounted(async () => {
    await getSkins();
    await getActiveSkin();
});
</script>

<style scoped>
.skins-container {
    padding-left: 50px;
    padding-right: 20px;
}

.datos-card {
    background: #12122a;
    border-radius: 10px;
    padding: 24px;
    border: 1px solid rgba(156, 92, 203, 0.25);
}

.card-title {
    margin-bottom: 20px;
    text-transform: uppercase;
    font-size: 13px;
}

.card-title span {
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.active-skin {
    text-align: center;
    margin-bottom: 20px;
}

.active-img {
    width: 120px;
    border-radius: 10px;
}

.skins-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.skin-card {
    width: 150px;
    padding: 10px;
    background: #0e0e22;
    border: 1px solid rgba(129, 138, 200, 0.3);
    border-radius: 10px;
    text-align: center;
}

.skin-img {
    width: 100px;
}

.skin-name {
    font-size: 12px;
    color: white;
    margin: 5px 0;
}

.btn-primary {
    padding: 8px 15px;
    border-radius: 20px;
    border: none;
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    color: white;
    cursor: pointer;
}

.btn-active {
    padding: 8px 15px;
    border-radius: 20px;
    border: none;
    background: green;
    color: white;
}
</style>