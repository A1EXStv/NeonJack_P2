import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { authStore } from '@/store/auth';
import { useWalletStore } from '@/store/wallet';

export function useGame() {
  const route      = useRoute();
  const router     = useRouter();
  const auth       = authStore();
  const authUser   = auth.user;
  const walletStore = useWalletStore();

  // ── State ────────────────────────────────────────────────────
  const gameState  = ref(null);
  const isLoading  = ref(true);
  const isActing   = ref(false);
  const error      = ref(null);
  const mySkinUrl  = ref(null);
  const salaCode   = ref('');

  // ── Bet state ────────────────────────────────────────────────
  const betAmount  = ref(0);
  const betHistory = ref([]);
  const betError   = ref(null);

  const addChip  = (v) => { betAmount.value += v; betHistory.value.push(v); betError.value = null; };
  const undoChip = () => { if (!betHistory.value.length) return; betAmount.value -= betHistory.value.pop(); };
  const clearBet = () => { betAmount.value = 0; betHistory.value = []; betError.value = null; };

  // ── Deal animation ───────────────────────────────────────────
  const myNewIdx     = ref(new Set());
  const dealerNewIdx = ref(new Set());
  const prevMyLen    = ref(0);
  const prevDlrLen   = ref(0);

  // ── Timers ───────────────────────────────────────────────────
  const BETTING_TIME = 60;
  const TURN_TIME    = 25;
  const RESTART_TIME = 10;
  const bettingTimer = ref(0);
  const turnTimer    = ref(0);
  const restartTimer = ref(0);

  let pollTimer       = null;
  let bettingInterval = null;
  let turnInterval    = null;
  let restartInterval = null;

  // ── Score ────────────────────────────────────────────────────
  const calcScore = (cards) => {
    if (!cards?.length) return 0;
    let t = 0, a = 0;
    for (const c of cards) {
      if (['J','Q','K'].includes(c.valor)) t += 10;
      else if (c.valor === 'A') { t += 11; a++; }
      else t += parseInt(c.valor);
    }
    while (t > 21 && a-- > 0) t -= 10;
    return t;
  };

  // ── Computed ─────────────────────────────────────────────────
  const allPlayers   = computed(() => gameState.value?.partida_usuarios ?? []);
  // Tras un split hay dos registros del mismo usuario: uno 'dividido' y otro 'jugando'.
  // Siempre priorizamos la mano activa ('jugando') sobre las finalizadas.
  const myPU = computed(() => {
    const mine = allPlayers.value.filter(p => p.user_id === authUser.id);
    return mine.find(p => p.estado === 'jugando')
        ?? mine.find(p => p.estado === 'apostando')
        ?? mine[0]
        ?? null;
  });
  const otherPlayers = computed(() => allPlayers.value.filter(p => p.user_id !== authUser.id));
  const leftPlayer   = computed(() => otherPlayers.value[0] ?? null);
  const rightPlayer  = computed(() => otherPlayers.value[1] ?? null);

  const myHand   = computed(() => myPU.value?.mano_usuario ?? []);
  const myScore  = computed(() => calcScore(myHand.value));
  const myBusted = computed(() => myScore.value > 21);

  const dealerCards = computed(() => gameState.value?.mano_dealer ?? []);
  const showDeck    = computed(() => !dealerCards.value.length && gameState.value?.estado !== 'finalizada');

  const showAllDealerCards = computed(() => {
    if (!gameState.value) return false;
    if (gameState.value.estado === 'finalizada') return true;
    return !allPlayers.value.some(p => ['apostando', 'esperando', 'jugando'].includes(p.estado));
  });
  const isDealerCardHidden = (i) => i === 1 && !showAllDealerCards.value;
  const dealerVisibleScore = computed(() => calcScore(dealerCards.value.filter((_, i) => !isDealerCardHidden(i))));
  const dealerScore        = computed(() => calcScore(dealerCards.value));
  const dealerBusted       = computed(() => showAllDealerCards.value && dealerScore.value > 21);

  const canDouble    = computed(() => myPU.value?.estado === 'jugando' && myHand.value.length === 2);
  const canSplit     = computed(() => canDouble.value && myHand.value[0]?.valor === myHand.value[1]?.valor);
  const isPlayerTurn = (pu) => pu?.estado === 'jugando';

  const myResult          = computed(() => myPU.value?.resultado ?? null);
  const resultText        = computed(() => ({ blackjack: '🃏 ¡BLACKJACK!', gana: '¡GANASTE!', empata: 'EMPATE', pierde: 'PERDISTE' }[myResult.value] ?? ''));
  const resultBannerStyle = computed(() => ({
    blackjack: 'background:#f0c040;color:#1a0800;box-shadow:0 0 30px rgba(240,192,64,0.8)',
    gana:      'background:#16a34a;color:white;box-shadow:0 0 20px rgba(22,163,74,0.6)',
    empata:    'background:#4b5563;color:white',
    pierde:    'background:#b91c1c;color:white',
  }[myResult.value] ?? 'background:rgba(255,255,255,0.15);color:white'));

  // Wallet live: el polling actualiza el walletStore, todos los componentes reaccionan
  const wallet = computed(() => walletStore.balance);
  watch(() => myPU.value?.usuario?.wallet, (newWallet) => {
    walletStore.syncFromGame(newWallet);
  });

  // ── Timer helpers ─────────────────────────────────────────────
  const timerColor = (val, max) => {
    const pct = val / max;
    if (pct > 0.5)  return '#4ade80';
    if (pct > 0.25) return '#facc15';
    return '#f87171';
  };
  const ringOffset = (val, max) => (113.1 * (1 - val / max)).toFixed(2);

  // ── Deal animation ───────────────────────────────────────────
  const animNew = (idxSet, prev, next) => {
    for (let i = prev; i < next; i++) {
      idxSet.value = new Set([...idxSet.value, i]);
      const idx = i;
      setTimeout(() => { idxSet.value = new Set([...idxSet.value].filter(x => x !== idx)); }, 800);
    }
  };
  watch(myHand,      cards => { if (cards.length > prevMyLen.value)  animNew(myNewIdx,     prevMyLen.value,  cards.length); prevMyLen.value  = cards.length; }, { deep: true });
  watch(dealerCards, cards => { if (cards.length > prevDlrLen.value) animNew(dealerNewIdx, prevDlrLen.value, cards.length); prevDlrLen.value = cards.length; }, { deep: true });

  // ── Estado timers ─────────────────────────────────────────────
  watch(() => myPU.value?.estado, (newState) => {
    clearInterval(bettingInterval);
    clearInterval(turnInterval);
    bettingTimer.value = 0;
    turnTimer.value    = 0;

    if (newState === 'apostando') {
      bettingTimer.value = BETTING_TIME;
      bettingInterval = setInterval(() => {
        if (bettingTimer.value > 1) { bettingTimer.value--; return; }
        clearInterval(bettingInterval);
        if (!isActing.value) {
          if (betAmount.value < 10) addChip(10);
          placeBet();
        }
      }, 1000);
    } else if (newState === 'jugando') {
      turnTimer.value = TURN_TIME;
      turnInterval = setInterval(() => {
        if (turnTimer.value > 1) { turnTimer.value--; return; }
        clearInterval(turnInterval);
        if (!isActing.value) doStand();
      }, 1000);
    }
  }, { immediate: true });

  // ── Restart timer ─────────────────────────────────────────────
  const startRestartCountdown = () => {
    clearInterval(restartInterval);
    restartTimer.value = RESTART_TIME;
    restartInterval = setInterval(() => {
      if (restartTimer.value > 1) { restartTimer.value--; return; }
      clearInterval(restartInterval);
      restartTimer.value = 0;
      autoRestart();
    }, 1000);
  };

  const autoRestart = async () => {
    // El owner lanza la nueva partida; el resto recibirá 403 (ignorado)
    try { await axios.post(`/api/salas/${route.params.salaId}/iniciar`); } catch { /* ok */ }
    // Pausa para que la nueva partida se cree antes de consultarla
    await new Promise(r => setTimeout(r, 1200));
    try {
      const res = await axios.get(`/api/salas/${route.params.salaId}`);
      const newPartida = res.data?.partidas?.[0];
      if (newPartida?.id && String(newPartida.id) !== String(route.params.partidaId)) {
        router.push({ name: 'game.table', params: { salaId: route.params.salaId, partidaId: newPartida.id } });
        return; // navegamos, fin
      }
    } catch { /* ignore */ }
    // No se encontró nueva partida (owner ausente, sala sin jugadores…) → reintentar
    startRestartCountdown();
  };

  watch(() => gameState.value?.estado, (newState) => {
    if (newState !== 'finalizada') {
      clearInterval(restartInterval);
      restartTimer.value = 0;
      return;
    }
    if (restartTimer.value > 0) return; // ya corriendo
    startRestartCountdown();
  });

  // ── API ──────────────────────────────────────────────────────
  const fetchGameState = async () => {
    try {
      const res = await axios.get(`/api/partidas/${route.params.partidaId}/estado`);
      gameState.value = res.data;
    } catch (e) {
      error.value = e?.response?.data?.message ?? 'No se pudo cargar la partida.';
    } finally { isLoading.value = false; }
  };

  const fetchSkinUrl = async () => {
    if (!authUser.active_skin_id) return;
    try {
      const res = await axios.get(`/api/skins/${authUser.active_skin_id}`);
      mySkinUrl.value = res.data?.data?.skin ?? res.data?.skin ?? null;
    } catch { /* default */ }
  };

  const fetchSalaCode = async () => {
    if (!route.params.salaId) return;
    try {
      const res = await axios.get(`/api/salas/${route.params.salaId}`);
      salaCode.value = res.data?.code ?? '';
    } catch { /* ignore */ }
  };

  const placeBet = async () => {
    if (betAmount.value < 10) return;
    isActing.value = true; betError.value = null;
    try {
      await axios.post(`/api/partidas/${route.params.partidaId}/apostar`, { apuesta: betAmount.value });
      clearBet();
      await fetchGameState();
    } catch (e) { betError.value = e?.response?.data?.message ?? 'Error al apostar.'; }
    finally { isActing.value = false; }
  };

  const doAction = async (endpoint) => {
    isActing.value = true;
    try { await axios.post(`/api/partidas/${route.params.partidaId}/${endpoint}`); await fetchGameState(); }
    catch (e) { console.error(e?.response?.data?.message ?? e); }
    finally { isActing.value = false; }
  };

  const doHit    = () => doAction('hit');
  const doStand  = () => doAction('stand');
  const doDouble = () => doAction('doblar');
  const doSplit  = () => doAction('dividir');

  const leaveSala = async () => {
    const salaId = route.params.salaId;
    console.log('[leaveSala] salaId =', salaId);
    if (salaId) {
      try {
        const res = await axios.delete(`/api/salas/${salaId}/leave`);
        console.log('[leaveSala] respuesta:', res.data);
      } catch (e) {
        console.warn('[leaveSala] error:', e?.response?.status, e?.response?.data?.message ?? e);
      }
    }
    router.push({ name: 'game.lobby' });
  };

  // ── Lifecycle ─────────────────────────────────────────────────
  onMounted(async () => {
    await Promise.all([fetchGameState(), fetchSkinUrl(), fetchSalaCode()]);
    pollTimer = setInterval(fetchGameState, 2000);
  });

  onUnmounted(() => {
    clearInterval(pollTimer);
    clearInterval(bettingInterval);
    clearInterval(turnInterval);
    clearInterval(restartInterval);
  });

  return {
    // state
    gameState, isLoading, isActing, error, mySkinUrl, salaCode,
    // bet
    betAmount, betHistory, betError, addChip, undoChip, clearBet, placeBet,
    // computed
    myPU, leftPlayer, rightPlayer, myHand, myScore, myBusted,
    dealerCards, showDeck, dealerScore, dealerVisibleScore, dealerBusted,
    isDealerCardHidden, canDouble, canSplit, isPlayerTurn,
    myResult, resultText, resultBannerStyle, wallet,
    // animation
    myNewIdx, dealerNewIdx,
    // timers
    BETTING_TIME, TURN_TIME, RESTART_TIME,
    bettingTimer, turnTimer, restartTimer,
    timerColor, ringOffset,
    // actions
    doHit, doStand, doDouble, doSplit, leaveSala,
  };
}
