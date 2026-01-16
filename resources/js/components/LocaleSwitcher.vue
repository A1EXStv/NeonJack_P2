<template>
    <Select v-model="locale" :options="locales" placeholder="Select a Country" class="w-full md:w-28" @change="setLocale">
        <template #value="slotProps">
            <div v-if="slotProps.value" class="flex items-center">
                <img :alt="slotProps.value" src="https://primefaces.org/cdn/primevue/images/flag/flag_placeholder.png" :class="`mr-2 flag flag-${getFlag(slotProps.value)}`" style="width: 18px"/>
                <div>{{ slotProps.value }}</div>
            </div>
            <span v-else>
                {{slotProps}} {{ slotProps.placeholder }}
            </span>

        </template>
        <template #option="slotProps">
            <div class="flex items-center">
                <img :alt="slotProps.option" src="https://primefaces.org/cdn/primevue/images/flag/flag_placeholder.png" :class="`mr-2 flag flag-${getFlag(slotProps.option)}`" style="width: 18px" />
                <div>{{ slotProps.option }}</div>
            </div>
        </template>
    </Select>
</template>

<script setup>
import {computed, ref} from "vue";
import { langStore } from "@/store/lang";
import { useI18n } from 'vue-i18n'
import { loadMessages } from '@/plugins/i18n'


const i18n = useI18n({useScope: "global"});

const locale = ref(langStore().locale);
const locales = ref( Object.keys(langStore().locales))

const flags = {
    en: 'us',
    es: 'es',
    fr: 'fr',
    pt: 'br',
    zh: 'cn'
}

function getFlag(locale) {
    return flags[locale.toLowerCase()] || 'us';
}

function setLocale() {
    if (i18n.locale !== locale.value) {
        langStore().setLocale(locale.value);
    }
}

</script>

<style scoped>

</style>
