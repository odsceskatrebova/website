<script setup lang="ts">
import {ref} from 'vue';
import {storeToRefs} from 'pinia';
import {useCookieStore} from '../stores/CookieStore';
import {useCookieConsentResource} from '../helper/CookieConsentResource';

const cookieStore = useCookieStore();
const {preferences} = storeToRefs(cookieStore);
const cookieConsentResource = useCookieConsentResource();
const loading = ref(false);

const getPreferenceArray = (analytics: boolean, marketing: boolean): string[] => {
  const result: string[] = [];
  if (analytics) result.push('analytics');
  if (marketing) result.push('marketing');
  return result;
};

const save = (): void => {
  preferences.value.evaluated = true;
  try {
    loading.value = true;
    const prefs = getPreferenceArray(preferences.value.analytics, preferences.value.marketing);
    cookieConsentResource.send(prefs);
    updateConsentPreferencesInGtm(prefs);
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const saveNoCookies = (): void => {
  preferences.value.analytics = false;
  preferences.value.marketing = false;
  save();
};

const saveAnalyticsCookies = (): void => {
  preferences.value.analytics = true;
  preferences.value.marketing = false;
  save();
};

const saveAllCookies = (): void => {
  preferences.value.analytics = true;
  preferences.value.marketing = true;
  save();
};

const updateConsentPreferencesInGtm = (types: string[]): void => {
  (window as unknown as { dataLayer?: unknown[] }).dataLayer?.push({
    event: 'cookie_consent',
    type: types.join(','),
  });
};
</script>

<template>
  <div
    v-if="!preferences.evaluated"
    :class="$style.bar"
  >
    <div :class="$style.msg">
      {{ $t('cookieBar.cookiesMessage') }}
    </div>
    <div :class="$style.actions">
      <div
        class="btn primary outline small"
        @click="saveAnalyticsCookies"
      >
        <div class="content">
          {{ $t('cookieBar.buttonAllowAnalytics') }}
        </div>
      </div>
      <div
        class="btn primary outline small"
        @click="saveNoCookies"
      >
        <div class="content">
          {{ $t('cookieBar.buttonNoCookies') }}
        </div>
      </div>
      <div
        class="btn primary fill small"
        @click="saveAllCookies"
      >
        <div class="content">
          {{ $t('cookieBar.buttonAllowAll') }}
        </div>
      </div>
    </div>
  </div>
</template>

<style module lang="scss">
@use '../../styles/base/variables' as vars;

.bar {
  position: fixed;
  left: 50%;
  bottom: 5rem;
  transform: translateX(-50%);
  z-index: 75000;
  background: vars.$accentColor;
  color: vars.$contrastToAccentColor;
  width: 120%;
  max-width: min(60rem, calc(100vw - 2rem));
  padding: 2rem 4rem 2rem 2rem;
  border-radius: vars.$borderRadius;
  box-shadow: 0 0 2rem #aaaaaa;
  align-items: center;
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  justify-content: center;
}

.msg {
  flex: 0 0 auto;
  max-width: 80%;
  text-wrap: wrap;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  flex: 0 1 auto;
  gap: 1rem;
  justify-content: center;
  justify-self: flex-end;
  font-family: vars.$defaultFont;
  max-width: vars.$textMaxWidth;
  font-size: 0.625rem;
  line-height: 100%;
  font-weight: 900;
}

@media (min-width: vars.$tablet) {
  .bar {
    width: 100%;
    bottom: 5rem;
  }

  .msg {
    max-width: 50%;
  }
}

@media (min-width: vars.$desktop) {
  .msg {
    max-width: 50%;
  }
}
</style>
