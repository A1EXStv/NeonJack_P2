import { createI18n } from 'vue-i18n'
import { langStore } from "@/store/lang";

let i18n;

export async function installI18n(app) {
    const store = langStore();
    const i18na = createI18n({
        legacy: false,
        globalInjection: true,
        locale: store.locale, // set locale
        fallbackLocale: 'es', // set fallback locale
        messages: {}
    });
    i18n = i18na;
    app.use(i18n);
    await loadMessages(store.locale);
    return i18n;
}

/**
 * @param {String} locale
 */
export async function loadMessages(locale) {
    if (!i18n) return;

    // If messages are already loaded for this locale, just set it
    if (Object.keys(i18n.global.getLocaleMessage(locale)).length > 0) {
        if (i18n.global.locale.value !== locale) {
            i18n.global.locale.value = locale;
            document.querySelector('html').setAttribute('lang', locale);
        }
        return;
    }

    try {
        const messages = await import(`../lang/${locale}.json`);
        i18n.global.setLocaleMessage(locale, messages.default || messages);

        if (i18n.global.locale.value !== locale) {
            i18n.global.locale.value = locale;
            document.querySelector('html').setAttribute('lang', locale);
        }
    } catch (e) {
        console.error(`Could not load language file for locale: ${locale}`, e);
        // Fallback to 'es' if loading fails and it's not the current one
        if (locale !== 'es') {
            await loadMessages('es');
        }
    }
};

export default i18n;
