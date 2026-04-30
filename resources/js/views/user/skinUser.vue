<template>
    <div class="skins-container">
        <div class="titulo-skins text-center">
            <h2 class="pt-4 pb-3">Colección de Skins</h2>
            <p class="text-white pb-5">
            Explora tu colección de skins y elige tu apariencia favorita para destacar en el casino. Activa la skin que más te guste y haz que cada partida sea única.
            </p>
        </div>
        <div v-if="loading" class="text-center text-white">
            Cargando skins...
        </div>

        <!-- SKINS -->
        <div v-else class="section2Cartas d-flex justify-content-center align-items-center gap-5 flex-wrap mb-4 ">

            <div v-for="skin in skins" :key="skin.id" class="card-container"
                :class="{ active: activeSkin?.id === skin.id }">
                <div class="card-inner">

                    <!-- FRONT -->
                    <div class="page page1">
                        <img :src="getImage(skin)" alt="">
                    </div>

                    <!-- BACK -->
                    <div class="page page2">

                        <img src="/images/dado.svg" class="dado dado-tl">
                        <img src="/images/dado.svg" class="dado dado-br">

                        <div class="contenido">
                            <h2 class="fw-bold titulo-carta">{{ skin.nombre }}</h2>
                            <p>Skin exclusiva</p>
                            <button v-if="activeSkin?.id !== skin.id" class="btn-primary"
                                @click="activateSkin(skin.id)">
                                Activar
                            </button>

                            <button v-else class="btn-active">Equipada </button>
                        </div>
                    </div>
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
const loading = ref(true);

const getImage = (skin) => {
    if (skin.media && skin.media.length > 0) {
        return skin.media[0].original_url
    }
    return '/images/blacjackFondo.png'
}

const getSkins = async () => {
    const res = await fetch("/api/user/skins", {
        headers: {
            "Authorization": `Bearer ${auth.token}`
        }
    });

    const data = await res.json();
    skins.value = data.data ?? data;
    console.log("Skins recibidas:", skins.value);
};

const getActiveSkin = async () => {
    const res = await fetch("/api/users/" + auth.user.id);
    const data = await res.json();

    const activeId = data.data.active_skin_id; 

    if (activeId) {
        activeSkin.value = skins.value.find(s => Number(s.id) === Number(activeId));
    }
};

const activateSkin = async (id) => {
    try {
        const res = await fetch(`/api/skins/${id}/activate`, {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${auth.token}`,
                "Content-Type": "application/json"
            }
        });

        if (res.ok) {
            activeSkin.value = skins.value.find(s => Number(s.id) === Number(id));
            
            await auth.getUser(); 
        }
    } catch (error) {
        console.error("Error al activar la skin:", error);
    }
};

onMounted(async () => {
    await getSkins();
    await getActiveSkin();
    loading.value = false;
});
</script>

<style scoped>
.skins-container {
    min-height: 800px;
}
.titulo-skins {
    justify-content: center;
}

.titulo-skins h2 {
    font-weight: bold;
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
    text-align: center;
}

.section2Cartas {
    perspective: 1000px;
}

.card-container {
    width: 248px;
    height: 367px;
}

.card-inner {
    width: 100%;
    height: 100%;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.6s;
}

.card-container:hover .card-inner {
    transform: rotateY(180deg);
}

.page {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 15px;
    backface-visibility: hidden;
}

.page1 img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 15px;

}

.page2 {
    background: white;
    transform: rotateY(180deg);
}

.contenido {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    color: black;
}

.titulo-carta {
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
}

.dado {
    width: 50px;
    height: 50px;
    position: absolute;
}

.dado-tl {
    top: 10px;
    left: 10px;
}

.dado-br {
    bottom: 10px;
    right: 10px;
}

.btn-primary {
    margin-top: 10px;
    padding: 8px 18px;
    border-radius: 20px;
    border: none;
    background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
    color: white;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.btn-primary:hover {
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(156, 92, 203, 0.6);
}

.card-container.active .card-inner {
    box-shadow: 0 0 50px #9C5CCB;
    border-radius: 15px;
}

.btn-active {
    margin-top: 10px;
    padding: 8px 18px;
    border-radius: 20px;
    border: 2px solid #9C5CCB;
    background: transparent;
    color: #9C5CCB;
    font-weight: bold;
    cursor: default;
}
</style>