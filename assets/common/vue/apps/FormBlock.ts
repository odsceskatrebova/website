import { createApp } from 'vue';
import FormBlock from './FormBlock.vue';
import { vuetify } from '@common/vue/plugins/vuetify';
import { pinia } from '@common/vue/plugins/pinia';
import { i18n } from '@common/vue/plugins/i18n';
import type { FormBlockConfig } from '@common/vue/types/form';

export function createFormBlock(): void {
  document.querySelectorAll<HTMLElement>('.vue-form-block').forEach((el) => {
    const raw = el.querySelector<HTMLElement>('.config')?.textContent?.trim() ?? '{}';
    let config: FormBlockConfig;
    try {
      config = JSON.parse(raw) as FormBlockConfig;
    } catch {
      console.error('FormBlock: failed to parse config', raw);
      return;
    }
    createApp(FormBlock, { config }).use(vuetify).use(pinia).use(i18n).mount(el);
  });
}
