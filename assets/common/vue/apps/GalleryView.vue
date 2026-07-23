<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref } from 'vue';

interface GalleryImage {
  src: string;
  alt: string;
}

const display = ref(false);
const images = ref<GalleryImage[]>([]);
const showButtonsState = ref(false);
const showButtonsTimeout = ref<ReturnType<typeof setTimeout> | null>(null);
const imageCarousel = ref<HTMLElement | null>(null);
const galleryEl = ref<HTMLElement | null>(null);
let sourceEl: HTMLElement | null = null;

const getIndex = (): number => {
  const left = imageCarousel.value?.scrollLeft ?? 0;
  return Math.round(left / window.innerWidth);
};

const goToSlide = (i: number): void => {
  imageCarousel.value?.scrollTo({ left: i * window.innerWidth, behavior: 'smooth' });
};

const open = async (i: number, trigger: HTMLElement): Promise<void> => {
  sourceEl = trigger;
  display.value = true;
  await nextTick();
  imageCarousel.value?.scrollTo({ left: i * window.innerWidth, behavior: 'instant' });
  galleryEl.value?.focus();
};

const close = (): void => {
  display.value = false;
  sourceEl?.focus();
  sourceEl = null;
};

const nextSlide = (): void => {
  const i = getIndex();
  goToSlide(i + 1 === images.value.length ? 0 : i + 1);
};

const prevSlide = (): void => {
  const i = getIndex();
  goToSlide(i === 0 ? images.value.length - 1 : i - 1);
};

const showButtons = (): void => {
  if (showButtonsTimeout.value) clearTimeout(showButtonsTimeout.value);
  showButtonsState.value = true;
  showButtonsTimeout.value = setTimeout(() => { showButtonsState.value = false; }, 2000);
};

const onKeydown = (e: KeyboardEvent): void => {
  if (!display.value) return;
  if (e.key === 'ArrowRight') { e.preventDefault(); nextSlide(); }
  if (e.key === 'ArrowLeft') { e.preventDefault(); prevSlide(); }
  if (e.key === 'Escape') close();
};

onMounted(() => {
  document.querySelectorAll<HTMLElement>('[data-gallery]').forEach((el, i) => {
    images.value.push({
      src: el.getAttribute('data-gallery') ?? '',
      alt: el.getAttribute('alt') ?? '',
    });
    el.addEventListener('click', () => open(i, el));
  });

  window.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
  if (showButtonsTimeout.value) clearTimeout(showButtonsTimeout.value);
  window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
  <div
    v-if="display"
    ref="galleryEl"
    class="fullscreen-gallery"
    tabindex="-1"
    role="dialog"
    aria-modal="true"
    aria-label="Image gallery"
    @mousemove="showButtons"
  >
      <div
        ref="imageCarousel"
        class="image-carousel"
        @click="close"
      >
        <div
          v-for="(image, i) in images"
          :key="i"
          class="slide"
        >
          <img
            :src="image.src"
            :alt="image.alt"
            @click.stop
          >
        </div>
      </div>

      <button
        class="gbtn close"
        :class="{ showButtons: showButtonsState }"
        aria-label="Close gallery"
        @click="close"
      >
        <div class="line one" />
        <div class="line two" />
      </button>
      <button
        class="gbtn left"
        :class="{ showButtons: showButtonsState }"
        aria-label="Previous image"
        @click="prevSlide"
      >
        <div class="line one" />
        <div class="line two" />
      </button>
      <button
        class="gbtn right"
        :class="{ showButtons: showButtonsState }"
        aria-label="Next image"
        @click="nextSlide"
      >
        <div class="line one" />
        <div class="line two" />
      </button>
  </div>
</template>

<style lang="scss">
.fullscreen-gallery {
  position: fixed;
  inset: 0;
  background: #000000aa;
  backdrop-filter: blur(.5rem) saturate(180%);
  -webkit-backdrop-filter: blur(.5rem) saturate(180%);
  z-index: 662607015;
  display: grid;
  padding: 1rem;
  grid-template-columns: 4rem 1fr 4rem;
  grid-template-rows: 4rem 1fr 4rem;
  outline: none;

  .gbtn {
    position: relative;
    margin: .5rem;
    height: 3rem;
    width: 3rem;
    padding: 0;
    color: #ffffff;
    border: none;
    border-radius: 3rem;
    background: #00000088;
    backdrop-filter: blur(.5rem) saturate(180%);
    -webkit-backdrop-filter: blur(.5rem) saturate(180%);
    z-index: 1;
    align-self: center;
    justify-self: center;
    cursor: pointer;
    opacity: 1;
    transition: opacity 1s;

    &:focus-visible {
      outline: 2px solid #ffffff;
      outline-offset: 3px;
      opacity: 1;
    }

    .line {
      height: 2px;
      background: #ffffff;
      position: absolute;
      width: 2rem;
      top: calc(1.5rem - 1px);
      left: .5rem;

      &.one { transform: rotate(45deg); }
      &.two { transform: rotate(-45deg); }
    }

    &:hover {
      background: #ffffff;
      opacity: 1;

      .line { background: #000000; }
    }

    &.close {
      grid-column: 3 / 4;
      grid-row: 1 / 2;
    }

    &.left {
      grid-column: 1 / 2;
      grid-row: 2 / 3;

      .line {
        width: 1rem;
        left: .9rem;

        &.two { top: 1.05rem; }
        &.one { top: 1.75rem; }
      }
    }

    &.right {
      grid-column: 3 / 4;
      grid-row: 2 / 3;

      .line {
        width: 1rem;
        left: 1.1rem;

        &.one { top: 1.05rem; }
        &.two { top: 1.75rem; }
      }
    }
  }

  .image-carousel {
    position: absolute;
    inset: 0;
    display: flex;
    flex-flow: row nowrap;
    overflow-x: scroll;
    overscroll-behavior-x: none;
    scroll-snap-type: x mandatory;
    -ms-overflow-style: none;
    scrollbar-width: none;

    &::-webkit-scrollbar { display: none; }

    .slide {
      scroll-snap-align: center;
      width: 100vw;
      height: 100vh;
      flex: none;
      padding: 2rem;
      display: flex;
      justify-content: center;
      align-items: center;

      img {
        max-width: calc(100% - 4rem);
        max-height: calc(100% - 4rem);
        width: auto;
        height: auto;
        border-radius: .5rem;
        user-select: none;
        -webkit-user-drag: none;
      }
    }
  }

  @media (min-width: 60rem) {
    .gbtn {
      opacity: 0;

      &.showButtons {
        opacity: 1;
        transition: opacity .3s;
      }
    }
  }
}
</style>
