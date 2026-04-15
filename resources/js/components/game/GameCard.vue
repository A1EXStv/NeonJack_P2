<template>
  <div
    class="game-card rounded-lg shadow-md relative overflow-hidden flex-shrink-0 select-none"
    :style="cardStyle"
  >
    <template v-if="!faceDown && card">
      <!-- Top-left corner -->
      <div
        class="absolute top-1 left-1.5 flex flex-col items-center leading-none"
        :class="textColor"
      >
        <span class="font-bold" :class="rankClass">{{ card.valor }}</span>
        <span :class="rankClass">{{ suitSymbol }}</span>
      </div>

      <!-- Center suit (decorative, faded) -->
      <div class="absolute inset-0 flex items-center justify-center" :class="textColor">
        <span :class="centerClass" style="opacity: 0.12">{{ suitSymbol }}</span>
      </div>

      <!-- Bottom-right corner (rotated 180°) -->
      <div
        class="absolute bottom-1 right-1.5 flex flex-col items-center leading-none rotate-180"
        :class="textColor"
      >
        <span class="font-bold" :class="rankClass">{{ card.valor }}</span>
        <span :class="rankClass">{{ suitSymbol }}</span>
      </div>
    </template>

    <template v-else>
      <!-- Card back: skin image or default pattern -->
      <img
        v-if="skinImageUrl"
        :src="skinImageUrl"
        class="w-full h-full object-cover"
        alt="Dorso"
      />
      <div v-else class="w-full h-full bg-blue-900 flex items-center justify-center p-1.5">
        <div
          class="border-2 border-blue-400 rounded w-full h-full flex items-center justify-center"
        >
          <span class="text-blue-300 font-bold" :class="rankClass">♠</span>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** { palo: 'hearts'|'diamonds'|'clubs'|'spades', valor: '2'-'10'|'J'|'Q'|'K'|'A' } */
  card: { type: Object, default: null },
  faceDown: { type: Boolean, default: false },
  skinImageUrl: { type: String, default: null },
  /** 'sm' | 'md' | 'lg' */
  size: { type: String, default: 'md' },
});

const SUIT_SYMBOLS = { hearts: '♥', diamonds: '♦', clubs: '♣', spades: '♠' };

const suitSymbol = computed(() => SUIT_SYMBOLS[props.card?.palo] ?? '?');

const isRed = computed(() => ['hearts', 'diamonds'].includes(props.card?.palo));
const textColor = computed(() => (isRed.value ? 'text-red-600' : 'text-gray-900'));

const CARD_SIZES = {
  sm: { width: '42px', height: '60px' },
  md: { width: '56px', height: '80px' },
  lg: { width: '72px', height: '102px' },
};

const cardStyle = computed(() => ({
  width: CARD_SIZES[props.size]?.width ?? '56px',
  height: CARD_SIZES[props.size]?.height ?? '80px',
  backgroundColor: props.faceDown ? 'transparent' : 'white',
  border: '1.5px solid rgba(0,0,0,0.18)',
}));

const rankClass = computed(
  () => ({ sm: 'text-xs', md: 'text-sm', lg: 'text-base' }[props.size] ?? 'text-sm'),
);

const centerClass = computed(
  () => ({ sm: 'text-2xl', md: 'text-3xl', lg: 'text-4xl' }[props.size] ?? 'text-3xl'),
);
</script>
