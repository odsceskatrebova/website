import { createApp } from 'vue';
import GalleryView from './GalleryView.vue';

export function createGalleryView(): void {
  const el = document.querySelector('#vue-gallery-app');
  if (!el) return;
  createApp(GalleryView).mount(el);
}
