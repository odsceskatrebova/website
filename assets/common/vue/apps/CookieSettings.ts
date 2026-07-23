import { createApp } from 'vue';
import type { Pinia } from 'pinia';
import type { AppI18n } from '../plugins/i18n';
import { vuetify } from '../plugins/vuetify';
import CookieSettings from './CookieSettings.vue';

export function createCookieSettings(pinia: Pinia, i18n: AppI18n): void {
  const el = document.querySelector('.js-cookie-settings-app');
  if (!el) return;
  const app = createApp(CookieSettings);
  app.use(pinia);
  app.use(i18n);
  app.use(vuetify);
  app.mount(el);
}
