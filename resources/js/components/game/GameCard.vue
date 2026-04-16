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
      <!-- Card back with skin image -->
      <img
        v-if="skinImageUrl"
        :src="skinImageUrl"
        style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;"
      />
      <!-- Card back fallback -->
      <div v-else class="absolute inset-0 flex items-center justify-center">
        <div
          class="border-2 border-blue-400 rounded"
          style="width:78%;height:78%;display:flex;align-items:center;justify-content:center;"
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
  sm: { width: '52px',  height: '74px'  },
  md: { width: '68px',  height: '96px'  },
  lg: { width: '90px',  height: '128px' },
};

const cardStyle = computed(() => {
  const base = {
    width:  CARD_SIZES[props.size]?.width  ?? '56px',
    height: CARD_SIZES[props.size]?.height ?? '80px',
  };
  if (props.faceDown && props.skinImageUrl) {
    return {
      ...base,
      backgroundImage:    `url('${props.skinImageUrl}')`,
      backgroundSize:     'cover',
      backgroundPosition: 'center',
      backgroundRepeat:   'no-repeat',
    };
  }
  if (props.faceDown) {
    return {
      ...base,
      backgroundColor: '#1e3a8a',
    };
  }
  return {
    ...base,
    backgroundColor: 'white',
    border: '1.5px solid rgba(0,0,0,0.18)',
  };
});

const rankClass = computed(
  () => ({ sm: 'text-xs', md: 'text-sm', lg: 'text-base' }[props.size] ?? 'text-sm'),
);

const centerClass = computed(
  () => ({ sm: 'text-2xl', md: 'text-3xl', lg: 'text-4xl' }[props.size] ?? 'text-3xl'),
);
</script>
