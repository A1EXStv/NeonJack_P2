<template>
  <div class="p-4 max-w-5xl mx-auto">
    <!-- Error toast -->
    <div
      v-if="actionError"
      class="mb-3 flex items-center gap-3 bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl px-4 py-3 text-sm"
    >
      <i class="pi pi-exclamation-triangle shrink-0"></i>
      <span class="flex-1">{{ actionError }}</span>
      <button class="pi pi-times opacity-60 hover:opacity-100" @click="actionError = null"></button>
    </div>

    <Card>
      <template #title>
        <div class="flex items-center justify-between w-full">
          <span class="flex items-center gap-2">
            <i class="pi pi-table text-green-600"></i> Salas de Blackjack
          </span>
          <div class="flex gap-2">
            <Button
              label="Actualizar"
              icon="pi pi-refresh"
              size="small"
              severity="secondary"
              outlined
              :loading="isLoading"
              @click="loadSalas"
            />
            <Button
              label="Crear sala"
              icon="pi pi-plus"
              size="small"
              class="btn-brand"
              @click="showCreateDialog = true"
            />
          </div>
        </div>
      </template>

      <template #content>
        <div v-if="isLoading && salas.length === 0" class="flex justify-center py-12">
          <ProgressSpinner />
        </div>

        <div v-else-if="salas.length === 0" class="text-center py-12 text-surface-400">
          <i class="pi pi-inbox text-4xl block mb-3 opacity-40"></i>
          No hay salas disponibles. ¡Crea una!
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="sala in salas"
            :key="sala.id"
            class="sala-card border rounded-xl p-4 flex flex-col gap-3 transition-all"
            :class="isPlayerInSala(sala) ? 'sala-card-mine' : ''"
          >
            <!-- Sala header -->
            <div class="flex items-start justify-between gap-2">
              <div>
                <h3 class="font-bold text-surface-800 dark:text-surface-100 leading-tight">
                  {{ sala.nombre_sala }}
                </h3>
                <span class="font-mono text-xs text-surface-400 mt-0.5 block">{{ sala.code }}</span>
              </div>
              <Tag
                :value="sala.status === 'waiting' ? 'Esperando' : 'En juego'"
                :severity="sala.status === 'waiting' ? 'success' : 'warn'"
                class="shrink-0"
              />
            </div>

            <!-- Players count -->
            <div class="flex items-center gap-1.5 text-xs text-surface-500">
              <i class="pi pi-users"></i>
              <span>{{ (sala.players ?? []).length }} / {{ sala.max_players }} jugadores</span>
              <span v-if="isPlayerInSala(sala)" class="text-green-600 font-medium ml-1">(estás dentro)</span>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 mt-auto pt-2 border-t border-surface-100 dark:border-surface-700">
              <!-- Ya estoy en esta sala y está jugando → Entrar -->
              <Button
                v-if="sala.status === 'playing' && isPlayerInSala(sala)"
                label="Entrar al juego"
                icon="pi pi-play"
                size="small"
                class="flex-1 btn-brand"
                :loading="enteringId === sala.id"
                @click="enterGame(sala)"
              />

              <!-- Ya estoy en esta sala, esperando -->
              <template v-else-if="sala.status === 'waiting' && isPlayerInSala(sala)">
                <Button
                  v-if="sala.owner_id === authUser.id"
                  label="Iniciar partida"
                  icon="pi pi-play"
                  size="small"
                  class="flex-1 btn-brand"
                  :loading="startingId === sala.id"
                  @click="startGame(sala)"
                />
                <Button
                  v-else
                  label="Esperando inicio..."
                  icon="pi pi-clock"
                  size="small"
                  severity="secondary"
                  class="flex-1"
                  disabled
                />
              </template>

              <!-- No estoy en la sala — unirse (waiting o playing en fase apuesta) -->
              <Button
                v-else-if="!isPlayerInSala(sala) && !sala.isFull"
                :label="sala.status === 'playing' ? 'Unirse (apostando)' : 'Unirse'"
                :icon="sala.status === 'playing' ? 'pi pi-bolt' : 'pi pi-user-plus'"
                size="small"
                class="flex-1 btn-brand"
                :loading="joiningId === sala.id"
                @click="joinSala(sala)"
              />

              <!-- Sala llena o en curso sin hueco -->
              <Button
                v-else
                :label="sala.status === 'playing' ? 'En curso' : 'Sala llena'"
                icon="pi pi-lock"
                size="small"
                severity="secondary"
                class="flex-1"
                disabled
              />
            </div>
          </div>
        </div>
      </template>
    </Card>

    <!-- Create sala dialog -->
    <Dialog
      v-model:visible="showCreateDialog"
      header="Nueva sala"
      :modal="true"
      :draggable="false"
      :style="{ width: '360px' }"
    >
      <div class="flex flex-col gap-4 pt-2">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium">Nombre de la sala</label>
          <InputText
            v-model="newSala.nombre_sala"
            placeholder="Mi sala de Blackjack"
            class="w-full"
            autofocus
          />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium">Jugadores máximos</label>
          <div class="flex gap-2">
            <button
              v-for="n in [1, 2, 3]"
              :key="n"
              class="flex-1 py-2 rounded-lg border text-sm font-bold transition-all"
              :class="newSala.max_players === n
                ? 'bg-green-600 border-green-600 text-white'
                : 'border-surface-300 dark:border-surface-600 hover:border-green-400'"
              @click="newSala.max_players = n"
            >
              {{ n }}
            </button>
          </div>
        </div>
      </div>
      <template #footer>
        <Button label="Cancelar" severity="secondary" outlined @click="showCreateDialog = false" />
        <Button
          label="Crear"
          icon="pi pi-check"
          class="btn-brand"
          :loading="creating"
          :disabled="!newSala.nombre_sala.trim()"
          @click="createSala"
        />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { authStore } from '@/store/auth';

const router = useRouter();
const auth = authStore();
const authUser = auth.user;

const salas = ref([]);
const isLoading = ref(false);
const joiningId = ref(null);
const startingId = ref(null);
const enteringId = ref(null);
const showCreateDialog = ref(false);
const creating = ref(false);
const newSala = ref({ nombre_sala: '', max_players: 3 });
const actionError = ref(null);

let pollTimer = null;

const loadSalas = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get('/api/salas');
    salas.value = Array.isArray(res.data) ? res.data : (res.data.data ?? []);
  } catch (e) {
    console.error('Error cargando salas:', e);
  } finally {
    isLoading.value = false;
  }
};

const isPlayerInSala = (sala) =>
  (sala.players ?? []).some((p) => p.id === authUser.id);

const joinSala = async (sala) => {
  joiningId.value = sala.id;
  actionError.value = null;
  try {
    await axios.post(`/api/salas/${sala.id}/join`);
    if (sala.status === 'playing') {
      // Entrar directamente a la partida activa
      const res = await axios.get(`/api/salas/${sala.id}`);
      const partida = (res.data?.partidas ?? [])[0];
      if (partida) {
        router.push({ name: 'game.table', params: { salaId: sala.id, partidaId: partida.id } });
        return;
      }
    }
    await loadSalas();
  } catch (e) {
    actionError.value = e?.response?.data?.message ?? 'Error al unirse a la sala.';
  } finally {
    joiningId.value = null;
  }
};

const startGame = async (sala) => {
  startingId.value = sala.id;
  actionError.value = null;
  try {
    const res = await axios.post(`/api/salas/${sala.id}/iniciar`);
    const partida = res.data;
    router.push({ name: 'game.table', params: { salaId: sala.id, partidaId: partida.id } });
  } catch (e) {
    actionError.value = e?.response?.data?.message ?? `Error al iniciar partida (${e?.response?.status ?? 'sin respuesta'}).`;
  } finally {
    startingId.value = null;
  }
};

const enterGame = async (sala) => {
  enteringId.value = sala.id;
  try {
    const res = await axios.get(`/api/salas/${sala.id}`);
    const salaData = res.data;
    const activePartida = (salaData.partidas ?? [])[0];
    if (activePartida) {
      router.push({
        name: 'game.table',
        params: { salaId: sala.id, partidaId: activePartida.id },
      });
    }
  } catch (e) {
    console.error('Error entrando al juego:', e);
  } finally {
    enteringId.value = null;
  }
};

const createSala = async () => {
  if (!newSala.value.nombre_sala.trim()) return;
  creating.value = true;
  try {
    await axios.post('/api/salas', newSala.value);
    showCreateDialog.value = false;
    newSala.value = { nombre_sala: '', max_players: 3 };
    await loadSalas();
  } catch (e) {
    console.error('Error creando sala:', e?.response?.data?.message ?? e);
  } finally {
    creating.value = false;
  }
};

onMounted(() => {
  loadSalas();
  // Poll every 5s so players see when the owner starts the game
  pollTimer = setInterval(loadSalas, 5000);
});

onUnmounted(() => {
  clearInterval(pollTimer);
});
</script>

<style scoped>
/* ── Card principal ───────────────────────────────── */
:deep(.p-card) {
  background: #150f2d !important;
  border: 1px solid rgba(255, 255, 255, 0.07) !important;
  border-radius: 14px !important;
  color: rgba(255, 255, 255, 0.85) !important;
}
:deep(.p-card-title) {
  color: rgba(255, 255, 255, 0.9) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  padding-bottom: 0.75rem;
}
:deep(.p-card-body) {
  padding: 1.25rem !important;
}

/* ── Cards de sala ────────────────────────────────── */
.sala-card {
  background: #0f0c1e;
  border-color: rgba(255, 255, 255, 0.08) !important;
  color: rgba(255, 255, 255, 0.85);
}
.sala-card:hover {
  border-color: rgba(156, 92, 203, 0.35) !important;
  box-shadow: 0 4px 20px rgba(156, 92, 203, 0.1);
}
.sala-card-mine {
  border-color: rgba(156, 92, 203, 0.4) !important;
  background: rgba(156, 92, 203, 0.06) !important;
}
:deep(.sala-card .font-bold) {
  color: #fff !important;
}
:deep(.sala-card .text-surface-400),
:deep(.sala-card .text-surface-500) {
  color: rgba(255, 255, 255, 0.4) !important;
}
:deep(.sala-card .border-t) {
  border-color: rgba(255, 255, 255, 0.07) !important;
}

/* ── Botones de acción principales ───────────────── */
:deep(.btn-brand.p-button) {
  background: linear-gradient(90deg, #9C5CCB, #818AC8, #3BC3DB) !important;
  border: none !important;
  color: #fff !important;
}
:deep(.btn-brand.p-button:hover) {
  opacity: 0.88;
  background: linear-gradient(90deg, #9C5CCB, #818AC8, #3BC3DB) !important;
}
</style>
