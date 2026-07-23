import { createApp } from 'vue';
import type { Pinia } from 'pinia';
import type { AppI18n } from '../plugins/i18n';
import ToastBar from './ToastBar.vue';

export function createToastBar(pinia: Pinia, i18n: AppI18n): void {
  const el = document.querySelector('#vue-toast-bar-app');
  if (!el) return;
  const app = createApp(ToastBar);
  app.use(pinia);
  app.use(i18n);
  app.mount(el);
}
