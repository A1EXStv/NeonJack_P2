<template>
  <div class="gt-root">

    <!-- LOADING -->
    <div v-if="isLoading" class="gt-overlay">
      <i class="pi pi-spin pi-spinner" style="font-size:2rem;color:rgba(255,255,255,0.5)"></i>
    </div>

    <!-- ERROR -->
    <div v-else-if="error" class="gt-overlay" style="flex-direction:column;gap:12px;padding:24px;text-align:center;">
      <i class="pi pi-exclamation-triangle" style="font-size:2.5rem;color:#3BC3DB"></i>
      <p style="color:white;font-size:15px;">{{ error }}</p>
      <button class="gt-btn-sec" @click="leaveSala">← Volver al lobby</button>
    </div>

    <!-- GAME -->
    <template v-else>
      <div class="gt-felt">

        <!-- HUD -->
        <div class="gt-hud">
          <button class="gt-hud-exit" @click="leaveSala">
            <i class="pi pi-sign-out" style="font-size:11px"></i> Salir
          </button>
          <span class="gt-hud-code">{{ salaCode }}</span>
          <div class="gt-hud-wallet">
            <i class="pi pi-database" style="font-size:11px"></i>
            {{ wallet }}
          </div>
        </div>

        <!-- ── DEALER ZONE ── -->
        <div class="gt-dealer-zone">
          <span class="gt-label">BANCA</span>
          <div class="gt-cards-row" style="min-height:80px;margin-top:6px;">
            <template v-if="dealerCards.length">
              <div v-for="(card, i) in dealerCards" :key="`d${i}${card.valor}${card.palo}`"
                   :class="dealerNewIdx.has(i) ? 'card-deal-up' : ''">
                <GameCard :card="card"
                          :face-down="isDealerCardHidden(i) || dealerNewIdx.has(i)"
                          :skin-image-url="mySkinUrl" size="md" />
              </div>
            </template>
            <span v-else class="gt-empty">Sin cartas</span>
          </div>
          <span v-if="dealerCards.length" class="gt-score"
                :style="{ color: dealerBusted ? '#f87171' : '#3BC3DB' }">
            <template v-if="!showAllDealerCards">{{ dealerVisibleScore }} + ?</template>
            <template v-else>{{ dealerScore }}<template v-if="dealerBusted"> — Bust!</template></template>
          </span>
        </div>

        <!-- ── CENTER DECK ── -->
        <div class="gt-center-deck-row">
          <div class="gt-center-deck" :class="{ 'gt-deck-dealing': dealerCards.length || myHand.length }">
            <div v-for="n in 5" :key="n" class="gt-deck-card"
                 :style="{ transform: `translate(${(5-n)*-1.2}px, ${(5-n)*-1.2}px)`, zIndex: n }">
              <img v-if="mySkinUrl" :src="mySkinUrl"
                   style="width:100%;height:100%;object-fit:cover;border-radius:4px;" />
              <div v-else class="gt-deck-default"></div>
            </div>
          </div>
        </div>

        <!-- ── SIDE PLAYERS + MY ZONE ── -->
        <div class="gt-main-row">

          <!-- Left player -->
          <div class="gt-side-seat">
            <template v-if="leftPlayer">
              <PlayerSeatComp :pu="leftPlayer" :active="isPlayerTurn(leftPlayer)" />
            </template>
            <div v-else class="gt-empty-seat">
              <div class="gt-seat-circle"></div>
              <span style="color:rgba(59,195,219,0.5);font-size:10px;">Libre</span>
            </div>
          </div>

          <!-- My zone (center) -->
          <div class="gt-my-zone">

            <!-- Bet ring -->
            <div class="gt-bet-ring" :class="{ 'gt-bet-ring-glow': betAmount > 0 }">
              <template v-if="betAmount > 0">
                <div class="gt-bet-chips">
                  <div v-for="(v, i) in betHistory.slice(-5)" :key="i"
                       class="gt-bet-chip-mini"
                       :style="{ background: chipMeta[v]?.bg ?? '#555', bottom: `${i * 3}px`, zIndex: i }">
                  </div>
                </div>
                <span class="gt-bet-amount">{{ betAmount }}</span>
              </template>
              <span v-else class="gt-bet-placeholder">APUESTA</span>
            </div>

            <!-- Result banner -->
            <Transition enter-active-class="gt-pop-in" leave-active-class="gt-pop-out">
              <div v-if="myResult" class="gt-result-banner" :style="resultBannerStyle">
                {{ resultText }}
                <span v-if="myPU?.balance_resultado" style="margin-left:8px;opacity:0.8;font-size:13px;">
                  ({{ myPU.balance_resultado > 0 ? '+' : '' }}{{ myPU.balance_resultado }})
                </span>
              </div>
            </Transition>

            <!-- My cards -->
            <div class="gt-cards-row" style="min-height:108px;margin-top:20px;">
              <template v-if="myHand.length">
                <div v-for="(card, i) in myHand" :key="`p${i}${card.valor}${card.palo}`"
                     :class="myNewIdx.has(i) ? 'card-deal-down' : ''">
                  <GameCard :card="card" :face-down="myNewIdx.has(i)"
                            :skin-image-url="mySkinUrl" size="lg" />
                </div>
              </template>
              <span v-else class="gt-empty">
                {{ myPU?.estado === 'apostando' ? 'Haz tu apuesta' : 'Sin cartas' }}
              </span>
            </div>

            <!-- My score -->
            <div v-if="myHand.length" style="display:flex;gap:8px;align-items:center;margin-top:4px;">
              <span :style="{ color: myBusted ? '#f87171' : '#fff', fontWeight:'bold', fontSize:'16px' }">
                {{ myScore }}<span v-if="myBusted" style="color:#f87171;"> — Bust!</span>
              </span>
              <span v-if="myPU?.apuesta_total" style="color:#3BC3DB;font-size:12px;opacity:0.8;">
                · Apuesta: {{ myPU.apuesta_total }}
              </span>
            </div>

          </div><!-- /my-zone -->

          <!-- Right player -->
          <div class="gt-side-seat">
            <template v-if="rightPlayer">
              <PlayerSeatComp :pu="rightPlayer" :active="isPlayerTurn(rightPlayer)" />
            </template>
            <div v-else class="gt-empty-seat">
              <div class="gt-seat-circle"></div>
              <span style="color:rgba(59,195,219,0.5);font-size:10px;">Libre</span>
            </div>
          </div>

        </div><!-- /main-row -->

        <!-- ── CONTROLES (borde inferior del tapete) ── -->
        <div class="gt-controls">

          <!-- BETTING PHASE -->
          <template v-if="myPU?.estado === 'apostando'">
            <div class="gt-bet-row">
              <div class="gt-chips-row">
                <button v-for="chip in CHIPS" :key="chip.value"
                        class="gt-chip"
                        :style="{
                          background: chip.bg,
                          color: chip.color,
                          border: `2px solid ${chip.border}`,
                          boxShadow:`0 4px 0 ${chip.shadow}, inset 0 1px 0 rgba(255,255,255,0.18)`,
                        }"
                        @click="addChip(chip.value)">
                  <span class="gt-chip-notch gt-chip-notch-t" :style="{background:chip.notch}"></span>
                  <span class="gt-chip-notch gt-chip-notch-b" :style="{background:chip.notch}"></span>
                  <span class="gt-chip-notch gt-chip-notch-l" :style="{background:chip.notch}"></span>
                  <span class="gt-chip-notch gt-chip-notch-r" :style="{background:chip.notch}"></span>
                  <span class="gt-chip-label">{{ chip.label }}</span>
                </button>
              </div>
              <div class="gt-bet-controls">
                <div class="gt-timer-wrap">
                  <svg width="48" height="48" viewBox="0 0 44 44" style="transform:rotate(-90deg)">
                    <circle cx="22" cy="22" r="18" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="3"/>
                    <circle cx="22" cy="22" r="18" fill="none"
                            :stroke="timerColor(bettingTimer, BETTING_TIME)"
                            stroke-width="3" stroke-linecap="round"
                            stroke-dasharray="113.1"
                            :stroke-dashoffset="ringOffset(bettingTimer, BETTING_TIME)"
                            style="transition:stroke-dashoffset 0.9s linear,stroke 0.5s;" />
                  </svg>
                  <span class="gt-timer-num" :style="{ color: timerColor(bettingTimer, BETTING_TIME) }">{{ bettingTimer }}</span>
                </div>
                <div class="gt-bet-display">
                  <span style="color:rgba(255,255,255,0.35);font-size:10px;text-transform:uppercase;letter-spacing:1px;">Apuesta</span>
                  <span style="color:#3BC3DB;font-size:22px;font-weight:bold;line-height:1;">{{ betAmount }}</span>
                </div>
                <div style="display:flex;gap:6px;">
                  <button v-if="betHistory.length" class="gt-btn-sec" style="padding:6px 10px;font-size:13px;" @click="undoChip" title="Deshacer">↩</button>
                  <button v-if="betHistory.length" class="gt-btn-sec" style="padding:6px 10px;font-size:13px;" @click="clearBet" title="Limpiar">✕</button>
                  <button class="gt-btn-confirm" :disabled="betAmount < 10 || isActing" @click="placeBet">
                    <i v-if="isActing" class="pi pi-spin pi-spinner" style="font-size:11px;margin-right:4px;"></i>
                    APOSTAR
                  </button>
                </div>
                <div v-if="betError" style="color:#f87171;font-size:11px;text-align:center;">{{ betError }}</div>
              </div>
            </div>
          </template>

          <!-- WAITING BET -->
          <div v-else-if="myPU?.estado === 'esperando'" class="gt-status-msg">
            <i class="pi pi-spin pi-spinner" style="font-size:12px;"></i>
            Esperando que los demás apuesten...
          </div>

          <!-- PLAYING -->
          <div v-else-if="myPU?.estado === 'jugando'" class="gt-actions-row">
            <div class="gt-timer-wrap" style="margin-right:4px;">
              <svg width="44" height="44" viewBox="0 0 44 44" style="transform:rotate(-90deg)">
                <circle cx="22" cy="22" r="18" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="3"/>
                <circle cx="22" cy="22" r="18" fill="none"
                        :stroke="timerColor(turnTimer, TURN_TIME)"
                        stroke-width="3" stroke-linecap="round"
                        stroke-dasharray="113.1"
                        :stroke-dashoffset="ringOffset(turnTimer, TURN_TIME)"
                        style="transition:stroke-dashoffset 0.9s linear,stroke 0.5s;" />
              </svg>
              <span class="gt-timer-num" :style="{ color: timerColor(turnTimer, TURN_TIME) }">{{ turnTimer }}</span>
            </div>
            <button class="gt-action-btn gt-btn-hit"    :disabled="isActing" @click="doHit">
              <i class="pi pi-plus"      style="font-size:11px;margin-right:4px;"></i>PEDIR
            </button>
            <button class="gt-action-btn gt-btn-stand"  :disabled="isActing" @click="doStand">
              <i class="pi pi-hand"      style="font-size:11px;margin-right:4px;"></i>PLANTARSE
            </button>
            <button v-if="canDouble" class="gt-action-btn gt-btn-double" :disabled="isActing" @click="doDouble">
              <i class="pi pi-arrow-up"  style="font-size:11px;margin-right:4px;"></i>DOBLAR
            </button>
            <button v-if="canSplit"  class="gt-action-btn gt-btn-split"  :disabled="isActing" @click="doSplit">
              <i class="pi pi-arrows-h"  style="font-size:11px;margin-right:4px;"></i>DIVIDIR
            </button>
          </div>

          <!-- WAITING OTHERS -->
          <div v-else-if="['plantado','reventado','doblado','dividido'].includes(myPU?.estado)"
               class="gt-status-msg">
            <i class="pi pi-clock" style="font-size:11px;"></i>
            Esperando a los demás jugadores...
          </div>

          <!-- ROUND OVER -->
          <div v-else-if="gameState?.estado === 'finalizada'" class="gt-status-msg">
            <div class="gt-timer-wrap" style="margin-right:8px;">
              <svg width="40" height="40" viewBox="0 0 44 44" style="transform:rotate(-90deg)">
                <circle cx="22" cy="22" r="18" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="3"/>
                <circle cx="22" cy="22" r="18" fill="none"
                        stroke="#9C5CCB" stroke-width="3" stroke-linecap="round"
                        stroke-dasharray="113.1"
                        :stroke-dashoffset="ringOffset(restartTimer, RESTART_TIME)"
                        style="transition:stroke-dashoffset 0.9s linear;" />
              </svg>
              <span class="gt-timer-num" style="color:#9C5CCB;font-size:10px;">{{ restartTimer }}</span>
            </div>
            Nueva ronda en {{ restartTimer }}s...
          </div>

          <!-- NOT PARTICIPANT -->
          <div v-else-if="!myPU" class="gt-status-msg" style="color:rgba(255,255,255,0.2);">
            No participas en esta partida
          </div>

        </div><!-- /controles -->

      </div><!-- /felt -->
    </template>
  </div>
</template>

<script setup>
import { computed, defineComponent, h } from 'vue';
import GameCard from '@/components/game/GameCard.vue';
import { useGame } from '@/composables/useGame';

const {
  gameState, isLoading, isActing, error, mySkinUrl, salaCode,
  betAmount, betHistory, betError, addChip, undoChip, clearBet, placeBet,
  myPU, leftPlayer, rightPlayer, myHand, myScore, myBusted,
  dealerCards, dealerScore, dealerVisibleScore, dealerBusted,
  isDealerCardHidden, canDouble, canSplit, isPlayerTurn,
  myResult, resultText, resultBannerStyle, wallet,
  myNewIdx, dealerNewIdx,
  BETTING_TIME, TURN_TIME, RESTART_TIME,
  bettingTimer, turnTimer, restartTimer,
  timerColor, ringOffset,
  doHit, doStand, doDouble, doSplit, leaveSala,
} = useGame();

// ── Chips ─────────────────────────────────────────────────────
const CHIPS = [
  { value:5,   label:'5',   bg:'#e5e7eb', color:'#1f2937', border:'#9ca3af', shadow:'#6b7280', notch:'rgba(31,41,55,0.2)' },
  { value:10,  label:'10',  bg:'#2563eb', color:'#fff',    border:'#1d4ed8', shadow:'#1e3a8a', notch:'rgba(255,255,255,0.3)' },
  { value:25,  label:'25',  bg:'#16a34a', color:'#fff',    border:'#15803d', shadow:'#14532d', notch:'rgba(255,255,255,0.3)' },
  { value:50,  label:'50',  bg:'#dc2626', color:'#fff',    border:'#b91c1c', shadow:'#7f1d1d', notch:'rgba(255,255,255,0.3)' },
  { value:100, label:'100', bg:'#111827', color:'#fbbf24', border:'#c9a227', shadow:'#78350f', notch:'rgba(251,191,36,0.35)' },
  { value:500, label:'500', bg:'#7c3aed', color:'#fff',    border:'#6d28d9', shadow:'#3b0764', notch:'rgba(255,255,255,0.3)' },
];
const chipMeta = Object.fromEntries(CHIPS.map(c => [c.value, { bg: c.bg, color: c.color }]));

// ── PlayerSeatComp ─────────────────────────────────────────────
const PlayerSeatComp = defineComponent({
  props: { pu: Object, active: Boolean },
  setup(props) {
    const calc = (cards) => {
      if (!cards?.length) return 0;
      let t = 0, a = 0;
      for (const c of cards) {
        if (['J','Q','K'].includes(c.valor)) t += 10;
        else if (c.valor === 'A') { t += 11; a++; }
        else t += parseInt(c.valor);
      }
      while (t > 21 && a > 0) { t -= 10; a--; }
      return t;
    };
    const score   = computed(() => calc(props.pu?.mano_usuario));
    const busted  = computed(() => score.value > 21);
    const STATE_LABELS = {
      apostando: ['APUESTA','color:#3BC3DB;background:rgba(59,195,219,0.15);border:1px solid rgba(59,195,219,0.3)'],
      esperando: ['ESPERA', 'color:rgba(255,255,255,0.4);background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1)'],
      jugando:   ['TURNO',  'color:#fff;background:rgba(156,92,203,0.35);border:1px solid rgba(156,92,203,0.6)'],
      plantado:  ['PLANTA', 'color:rgba(255,255,255,0.5);background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1)'],
      reventado: ['BUST',   'color:#f87171;background:rgba(248,113,113,0.15);border:1px solid rgba(248,113,113,0.3)'],
      doblado:   ['DOBLÓ',  'color:#a78bfa;background:rgba(167,139,250,0.15);border:1px solid rgba(167,139,250,0.3)'],
      dividido:  ['DIVIDIÓ','color:#60a5fa;background:rgba(96,165,250,0.15);border:1px solid rgba(96,165,250,0.3)'],
      finalizado:['FIN',    'color:rgba(255,255,255,0.3);background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08)'],
    };
    const badgeStyle = computed(() => (STATE_LABELS[props.pu?.estado] ?? STATE_LABELS.esperando)[1]);
    const label      = computed(() => (STATE_LABELS[props.pu?.estado] ?? STATE_LABELS.esperando)[0]);
    return () => h('div', {
      style: `display:flex;flex-direction:column;align-items:center;gap:4px;
              padding:6px 4px;border-radius:8px;
              background:${props.active ? 'rgba(156,92,203,0.15)' : 'transparent'};
              border:1px solid ${props.active ? 'rgba(156,92,203,0.4)' : 'transparent'};
              transition:background 0.3s,border 0.3s;min-width:64px;`,
    }, [
      h('span', { style: 'font-size:9px;color:rgba(255,255,255,0.45);letter-spacing:1px;' },
        props.pu?.usuario?.name ?? '—'),
      h('div', { style: 'display:flex;flex-wrap:wrap;gap:3px;justify-content:center;' },
        (props.pu?.mano_usuario ?? []).length
          ? (props.pu.mano_usuario ?? []).map((c, i) => h(GameCard, { key: i, card: c, faceDown: false, size: 'sm' }))
          : [h('span', { style: 'color:rgba(255,255,255,0.18);font-size:10px;font-style:italic;' }, 'Sin cartas')]),
      h('div', { style: 'display:flex;gap:6px;font-size:10px;' }, [
        (props.pu?.mano_usuario ?? []).length
          ? h('span', { style: `color:${busted.value ? '#f87171' : 'rgba(255,255,255,0.55)'}` }, String(score.value))
          : null,
        props.pu?.apuesta_total
          ? h('span', { style: 'color:#3BC3DB;opacity:0.7;' }, `${props.pu.apuesta_total}✦`)
          : null,
      ]),
      h('span', { style: `font-size:9px;padding:1px 6px;border-radius:20px;${badgeStyle.value}` }, label.value),
    ]);
  },
});
</script>

<style>
/* ══ ROOT ══════════════════════════════════════════════════ */
.gt-root {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #0d0a1a;
  padding: 10px;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  user-select: none;
}

/* ══ FELT ══════════════════════════════════════════════════ */
.gt-felt {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding-bottom: 0;
  border-radius: 14px;
  border: 3px solid transparent;
  background-image:
    linear-gradient(#110c22, #110c22),
    linear-gradient(135deg, #9C5CCB, #3BC3DB, #9C5CCB);
  background-origin: border-box;
  background-clip: padding-box, border-box;
  overflow: hidden;
}

/* ══ HUD ═══════════════════════════════════════════════════ */
.gt-hud {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px 8px;
  background: rgba(0,0,0,0.4);
  border-bottom: 1px solid rgba(59,195,219,0.12);
}
.gt-hud-exit {
  background: none; border: none; cursor: pointer;
  color: rgba(255,255,255,0.35); font-size: 12px;
  display: flex; align-items: center; gap: 5px;
  transition: color 0.15s;
}
.gt-hud-exit:hover { color: #f87171; }
.gt-hud-code {
  font-family: monospace; font-size: 11px; letter-spacing: 1px;
  color: rgba(255,255,255,0.3); background: rgba(255,255,255,0.05);
  padding: 2px 8px; border-radius: 4px;
  border: 1px solid rgba(255,255,255,0.08);
}
.gt-hud-wallet {
  display: flex; align-items: center; gap: 4px;
  padding: 3px 10px; border-radius: 20px;
  background: rgba(59,195,219,0.1);
  border: 1px solid rgba(59,195,219,0.25);
  color: #3BC3DB; font-size: 12px; font-weight: bold;
}

/* ══ DEALER ZONE ═══════════════════════════════════════════ */
.gt-dealer-zone {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 14px 16px 10px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.gt-label {
  color: #3BC3DB;
  font-size: 9px;
  font-weight: bold;
  letter-spacing: 4px;
  text-transform: uppercase;
  opacity: 0.7;
}
.gt-score {
  font-size: 13px;
  font-weight: bold;
  margin-top: 4px;
}

/* ══ MAIN ROW ══════════════════════════════════════════════ */
.gt-main-row {
  flex: 1;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 12px 8px;
  gap: 8px;
}

/* ══ SIDE SEAT ═════════════════════════════════════════════ */
.gt-side-seat {
  width: 90px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding-top: 8px;
}
.gt-empty-seat {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}
.gt-seat-circle {
  width: 44px; height: 44px;
  border-radius: 50%;
  border: 2px dashed rgba(59,195,219,0.45);
  background: rgba(59,195,219,0.05);
}

/* ══ MY ZONE ═══════════════════════════════════════════════ */
.gt-my-zone {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  max-width: 480px;
}

/* Bet ring */
.gt-bet-ring {
  width: 72px; height: 72px;
  border-radius: 50%;
  border: 2px dashed rgba(255,255,255,0.15);
  background: rgba(0,0,0,0.25);
  display: flex; align-items: center; justify-content: center;
  position: relative;
  transition: border-color 0.2s, box-shadow 0.2s;
  flex-shrink: 0;
}
.gt-bet-ring-glow {
  border-color: rgba(59,195,219,0.55);
  box-shadow: 0 0 16px rgba(59,195,219,0.2);
}
.gt-bet-chips {
  position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%);
}
.gt-bet-chip-mini {
  position: absolute;
  width: 28px; height: 8px;
  border-radius: 4px;
  left: 0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.4);
}
.gt-bet-amount {
  color: #3BC3DB; font-size: 15px; font-weight: bold; z-index: 1;
}
.gt-bet-placeholder {
  color: rgba(255,255,255,0.15); font-size: 9px; letter-spacing: 2px;
}

/* Result banner */
.gt-result-banner {
  padding: 8px 20px;
  border-radius: 8px;
  font-size: 17px;
  font-weight: bold;
  letter-spacing: 1px;
  text-align: center;
}
.gt-pop-in  { animation: gt-pop 0.3s ease-out; }
.gt-pop-out { animation: gt-pop 0.2s ease-in reverse; }
@keyframes gt-pop {
  0%   { transform: scale(0.6); opacity: 0; }
  100% { transform: scale(1);   opacity: 1; }
}

/* Cards row */
.gt-cards-row {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  justify-content: center;
  align-items: center;
}
.gt-empty {
  color: rgba(255,255,255,0.2);
  font-size: 12px;
  font-style: italic;
}

/* ══ CONTROLES (dentro del tapete) ════════════════════════ */
.gt-controls {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 16px;
  gap: 8px;
  background: linear-gradient(to bottom, #110c22 0%, #1e0d35 60%, #2a0d40 100%);
  border-top: 1px solid rgba(156,92,203,0.35);
}

/* Bet row */
.gt-bet-row {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  width: 100%;
}
.gt-chips-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: center;
}
.gt-bet-controls {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: center;
}
.gt-bet-display {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 50px;
}

/* Actions row */
.gt-actions-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
}

/* Status message */
.gt-status-msg {
  color: rgba(255,255,255,0.4);
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 6px;
}

/* ══ OVERLAYS ══════════════════════════════════════════════ */
.gt-overlay {
  position: fixed; inset: 0;
  display: flex; align-items: center; justify-content: center;
  background: rgba(0,0,0,0.75);
  z-index: 100;
}

/* ══ CHIPS ═════════════════════════════════════════════════ */
.gt-chip {
  position: relative;
  width: 50px; height: 50px;
  border-radius: 50%;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: transform 0.1s, filter 0.1s;
  overflow: hidden;
}
.gt-chip:hover  { transform: translateY(-3px) scale(1.05); filter: brightness(1.1); }
.gt-chip:active { transform: translateY(0)    scale(0.97); }
.gt-chip-label  { font-size: 12px; font-weight: bold; z-index: 1; }
.gt-chip-notch {
  position: absolute; border-radius: 3px; width: 6px; height: 6px; opacity: 0.8;
}
.gt-chip-notch-t { top: 4px;    left: 50%; transform: translateX(-50%); }
.gt-chip-notch-b { bottom: 4px; left: 50%; transform: translateX(-50%); }
.gt-chip-notch-l { left: 4px;   top: 50%;  transform: translateY(-50%); }
.gt-chip-notch-r { right: 4px;  top: 50%;  transform: translateY(-50%); }

/* ══ BUTTONS ═══════════════════════════════════════════════ */
.gt-btn-sec {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.12);
  color: rgba(255,255,255,0.55);
  border-radius: 6px; cursor: pointer;
  padding: 6px 12px; font-size: 13px;
  transition: background 0.15s;
}
.gt-btn-sec:hover { background: rgba(255,255,255,0.1); }

.gt-btn-confirm {
  background: linear-gradient(90deg, #9C5CCB, #3BC3DB);
  border: none; color: white; border-radius: 6px; cursor: pointer;
  padding: 8px 18px; font-size: 13px; font-weight: bold; letter-spacing: 1px;
  box-shadow: 0 4px 0 rgba(0,0,0,0.35);
  transition: filter 0.1s, transform 0.1s;
}
.gt-btn-confirm:hover:not(:disabled)  { filter: brightness(1.1); }
.gt-btn-confirm:active:not(:disabled) { transform: translateY(2px); box-shadow: 0 2px 0 rgba(0,0,0,0.35); }
.gt-btn-confirm:disabled { opacity: 0.4; cursor: not-allowed; }

.gt-action-btn {
  border: none; color: white; border-radius: 8px; cursor: pointer;
  padding: 10px 18px; font-size: 13px; font-weight: bold; letter-spacing: 1px;
  transition: filter 0.1s, transform 0.1s;
  display: flex; align-items: center;
}
.gt-action-btn:hover:not(:disabled)  { filter: brightness(1.12); }
.gt-action-btn:active:not(:disabled) { transform: translateY(2px); }
.gt-action-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.gt-btn-hit    { background:#16a34a; box-shadow:0 4px 0 #14532d; }
.gt-btn-stand  { background:#b91c1c; box-shadow:0 4px 0 #7f1d1d; }
.gt-btn-double { background:#9C5CCB; box-shadow:0 4px 0 #5b2d7a; }
.gt-btn-split  { background:#3BC3DB; box-shadow:0 4px 0 #1a7a8a; color:#0d0a1a; }

/* ══ CENTER DECK ═══════════════════════════════════════════ */
.gt-center-deck-row {
  display: flex; justify-content: center; align-items: center; padding: 6px 0;
}
.gt-center-deck {
  position: relative; width: 44px; height: 62px;
  transition: transform 0.4s ease, opacity 0.4s ease;
}
.gt-center-deck.gt-deck-dealing { transform: scale(0.88); opacity: 0.6; }
.gt-deck-card {
  position: absolute; inset: 0;
  border-radius: 4px;
  border: 1px solid rgba(59,195,219,0.2);
  overflow: hidden;
  box-shadow: 0 2px 6px rgba(0,0,0,0.5);
}
.gt-deck-default {
  width: 100%; height: 100%;
  background: linear-gradient(135deg, #2d1a4a 0%, #110c22 100%);
}

/* ══ TIMERS ════════════════════════════════════════════════ */
.gt-timer-wrap {
  position: relative; display: flex;
  align-items: center; justify-content: center; flex-shrink: 0;
}
.gt-timer-num {
  position: absolute; font-size: 12px; font-weight: bold;
  font-family: monospace; line-height: 1;
}

/* ══ DEAL ANIMATIONS ═══════════════════════════════════════ */
.card-deal-up   { animation: card-deal-up   0.65s ease-out forwards; }
.card-deal-down { animation: card-deal-down 0.65s ease-out forwards; }

@keyframes card-deal-up {
  0%   { transform: translateY(70px)  scale(0.35) rotateY(0deg);  opacity: 0; }
  30%  { transform: translateY(-6px)  scale(1.06) rotateY(0deg);  opacity: 1; }
  50%  { transform: translateY(0)     scale(1)    rotateY(90deg); opacity: 1; }
  100% { transform: translateY(0)     scale(1)    rotateY(0deg);  opacity: 1; }
}
@keyframes card-deal-down {
  0%   { transform: translateY(-70px) scale(0.35) rotateY(0deg);  opacity: 0; }
  30%  { transform: translateY(6px)   scale(1.06) rotateY(0deg);  opacity: 1; }
  50%  { transform: translateY(0)     scale(1)    rotateY(90deg); opacity: 1; }
  100% { transform: translateY(0)     scale(1)    rotateY(0deg);  opacity: 1; }
}
</style>
