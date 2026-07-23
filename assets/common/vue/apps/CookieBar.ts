import { createApp } from 'vue';
import type { Pinia } from 'pinia';
import type { AppI18n } from '../plugins/i18n';
import CookieBar from './CookieBar.vue';

export function createCookieBar(pinia: Pinia, i18n: AppI18n): void {
  const el = document.querySelector('#vue-cookie-bar-app');
  if (!el) return;
  const app = createApp(CookieBar);
  app.use(pinia);
  app.use(i18n);
  app.mount(el);
}
