import { ref } from 'vue';
import { defineStore } from 'pinia';

interface CookiePreferences {
    evaluated: boolean;
    analytics: boolean;
    marketing: boolean;
    version: number;
    response: unknown | null;
}

export const useCookieStore = defineStore('CookieStore', () => {
  const preferences = ref<CookiePreferences>({
    evaluated: false,
    analytics: false,
    marketing: false,
    version: 1,
    response: null,
  });

  return { preferences };
}, {
  persist: {
    key: 'CookiePreferences',
    storage: localStorage,
  },
});
