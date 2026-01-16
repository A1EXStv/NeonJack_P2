import { ref } from "vue";
import { defineStore } from "pinia";

export const
    styleStore = defineStore("styleStore", () => {

        let darkTheme = ref(false);

        function setDarkTheme(is_dark) {
            console.log('styleStore Change',darkTheme.value);
            darkTheme.value = is_dark;
        }
        function getDarkTheme() {
            console.log('styleStore Get',darkTheme.value);
            return darkTheme.value;
        }

        return { darkTheme, setDarkTheme,getDarkTheme};
    }, {persist: true});

