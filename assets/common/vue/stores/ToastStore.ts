import { ref } from 'vue';
import { defineStore } from 'pinia';
import { i18n } from '../plugins/i18n';

const defaultTimeout = 6000;

export type ToastType = 'info' | 'primary' | 'success' | 'error';

export interface Toast {
    id: string;
    message: string;
    type: ToastType;
    validUntil: number;
    displayAfterRefresh: boolean;
}

interface ShowToastOptions {
    type?: ToastType;
    timeout?: number;
    displayAfterRefresh?: boolean;
}

export const useToastStore = defineStore('ToastStore', () => {
  const toasts = ref<Toast[]>([]);

  const showToast = (
    message: string,
    { type = 'info', timeout = defaultTimeout, displayAfterRefresh = false }: ShowToastOptions = {},
  ): void => {
    const id = Math.random().toFixed(48).split('.')[1];
    const validUntil = new Date().getTime() + timeout;
    toasts.value.push({ id,
      message,
      type,
      validUntil,
      displayAfterRefresh });
  };

  const removeToast = (toast: Toast): void => {
    const index = toasts.value.findIndex((t) => t.id === toast.id);
    if (index !== -1) toasts.value.splice(index, 1);
  };

  const removeAllToasts = (): void => {
    toasts.value.length = 0;
  };

  const validateToasts = (): void => {
    const now = new Date().getTime();
    for (const toast of [ ...toasts.value ]) {
      if (toast.validUntil < now) removeToast(toast);
    }
  };

  const displayAllToasts = (): void => {
    for (const toast of toasts.value) {
      toast.displayAfterRefresh = false;
    }
  };

  const showSuccess = (message: string, options?: ShowToastOptions): void => {
    showToast(message, { type: 'success',
      ...options });
  };

  const showError = (error: unknown = null, options?: ShowToastOptions): void => {
    const err = error as Record<string, unknown> | null;
    const message = (err?.['response'] as Record<string, unknown>)?.['data'] as Record<string, string> | null;
    const resolved =
            message?.['hydra:description'] ??
            (err?.['message'] as string | undefined) ??
            (typeof error === 'string' ? error : null) ??
            i18n.global.t('common.anErrorOccurred');
    showToast(resolved as string, { type: 'error',
      ...options });
  };

  const showInfo = (message: string, options?: ShowToastOptions): void => {
    showToast(message, { type: 'info',
      ...options });
  };

  return {
    toasts,
    showToast,
    removeToast,
    removeAllToasts,
    validateToasts,
    showSuccess,
    showError,
    showInfo,
    displayAllToasts,
  };
}, {
  persist: {
    key: 'toasts',
    storage: localStorage,
  },
});
