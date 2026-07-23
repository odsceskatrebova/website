import { createI18n } from 'vue-i18n';
import en from '../translations/en';
import cs from '../translations/cs';
import de from '../translations/de';
import sq from '../translations/sq';

export const i18n = createI18n({
  legacy: false,
  locale: document.documentElement.lang || 'en',
  fallbackLocale: 'en',
  messages: { en,
    cs,
    de,
    sq },
});

export type AppI18n = typeof i18n;
