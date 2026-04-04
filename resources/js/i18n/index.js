import { createI18n } from 'vue-i18n';
import pl from './pl.json';
import en from './en.json';

const savedLocale = typeof localStorage !== 'undefined'
    ? localStorage.getItem('stayflow_locale') || 'pl'
    : 'pl';

const i18n = createI18n({
    legacy: false,
    locale: savedLocale,
    fallbackLocale: 'pl',
    messages: { pl, en },
});

export default i18n;
