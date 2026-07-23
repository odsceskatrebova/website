<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import { useCookieStore } from '../stores/CookieStore';
import { useCookieConsentResource } from '../helper/CookieConsentResource';
import { useToastStore } from '../stores/ToastStore';

const { t } = useI18n();
const cookieStore = useCookieStore();
const { preferences } = storeToRefs(cookieStore);
const cookieConsentResource = useCookieConsentResource();
const toastStore = useToastStore();
const { showSuccess, showError } = toastStore;

const isOpen = ref(false);
const analytics = ref(preferences.value.analytics);
const marketing = ref(preferences.value.marketing);
const loading = ref(false);

const handleTriggerClick = (e: Event): void => {
  e.preventDefault();
  analytics.value = preferences.value.analytics;
  marketing.value = preferences.value.marketing;
  isOpen.value = true;
};

onMounted((): void => {
  document.querySelectorAll<HTMLElement>('[data-cookie-settings-trigger]').forEach((el) => {
    el.addEventListener('click', handleTriggerClick);
  });
});

onUnmounted((): void => {
  document.querySelectorAll<HTMLElement>('[data-cookie-settings-trigger]').forEach((el) => {
    el.removeEventListener('click', handleTriggerClick);
  });
});

const getPreferenceArray = (a: boolean, m: boolean): string[] => {
  const result: string[] = [];
  if (a) result.push('analytics');
  if (m) result.push('marketing');
  return result;
};

const updateConsentPreferencesInGtm = (types: string[]): void => {
  (window as unknown as { dataLayer?: unknown[] }).dataLayer?.push({
    event: 'cookie_consent',
    type: types.join(','),
  });
};

const save = async (): Promise<void> => {
  try {
    loading.value = true;
    preferences.value.analytics = analytics.value;
    preferences.value.marketing = marketing.value;
    preferences.value.evaluated = true;
    const prefs = getPreferenceArray(analytics.value, marketing.value);
    await cookieConsentResource.send(prefs);
    updateConsentPreferencesInGtm(prefs);
    showSuccess(t('cookieSettings.savedSuccess'));
    isOpen.value = false;
  } catch (error) {
    showError(error);
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <!-- attach="body" works around a Vuetify 4.0.3 bug where useTeleport calls
       document.querySelector('') when v-dialog has no activator slot -->
  <v-dialog v-model="isOpen" max-width="600" attach="body">
    <v-card :class="$style.dialog">
      <v-card-title :class="$style.head">
        <span :class="$style.title">{{ $t('cookieSettings.title') }}</span>
        <button :class="$style.close" @click="isOpen = false" type="button">✕</button>
      </v-card-title>
      <v-card-text :class="$style.body">
        <p :class="$style.intro">{{ $t('cookieSettings.intro') }}</p>
        <p :class="$style.intro">{{ $t('cookieSettings.introWhy') }}</p>
        <div :class="$style.row">
          <v-checkbox
            :model-value="true"
            :label="$t('cookieSettings.necessaryTitle')"
            disabled
            hide-details
          />
          <p :class="$style.desc">{{ $t('cookieSettings.necessaryDescription') }}</p>
        </div>
        <div :class="$style.row">
          <v-checkbox
            v-model="analytics"
            :label="$t('cookieSettings.analyticsTitle')"
            hide-details
          />
          <p :class="$style.desc">{{ $t('cookieSettings.analyticsDescription') }}</p>
        </div>
        <div :class="$style.row">
          <v-checkbox
            v-model="marketing"
            :label="$t('cookieSettings.marketingTitle')"
            hide-details
          />
          <p :class="$style.desc">{{ $t('cookieSettings.marketingDescription') }}</p>
        </div>
      </v-card-text>
      <v-card-actions :class="$style.foot">
        <div
          class="btn primary fill"
          :class="{ [$style.loading]: loading }"
          @click="save"
        >
          <div class="content">{{ $t('cookieSettings.saveButton') }}</div>
        </div>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style module lang="scss">
@use '../../styles/base/variables' as vars;

.dialog {
  border-radius: vars.$btnBorderRadius !important;
  overflow: hidden;
}

.head {
  display: flex !important;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem !important;
  border-bottom: 1px solid vars.$lineColor;
}

.title {
  font-family: vars.$defaultFont;
  font-size: 1.125rem !important;
  font-weight: 700;
}

.close {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.25rem;
  line-height: 1;
  color: vars.$grey;
  padding: 0.25rem;

  &:hover {
    color: vars.$textHighlightColor;
  }
}

.body {
  padding: 1.25rem 1.5rem !important;
}

.intro {
  margin: 0 0 0.75rem;
  font-size: 0.9375rem;
  line-height: 1.6;
  color: vars.$textColor;
}

.row {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid vars.$lineColor;

  &:last-child {
    border-bottom: none;
  }
}

.desc {
  margin: 0 0 0.5rem 2.5rem;
  font-size: 0.875rem;
  color: vars.$grey;
  line-height: 1.5;
}

.foot {
  padding: 1rem 1.5rem !important;
  border-top: 1px solid vars.$lineColor;
}

.loading {
  opacity: 0.6;
  pointer-events: none;
}
</style>
