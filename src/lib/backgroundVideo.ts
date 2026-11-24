import { computed, onBeforeUnmount, onMounted, ref, type Ref } from 'vue';

export function useBackgroundVideo(target: Ref<HTMLElement | undefined>) {
  const allowed = ref(false);
  const visible = ref(false);
  let observer: IntersectionObserver | undefined;
  let media: MediaQueryList | undefined;
  const connection = (navigator as Navigator & { connection?: EventTarget & { saveData?: boolean } }).connection;
  function update() {
    allowed.value = Boolean(media?.matches) && !connection?.saveData && !document.hidden;
  }
  onMounted(() => {
    media = window.matchMedia('(min-width: 768px) and (prefers-reduced-motion: no-preference)');
    update();
    media.addEventListener('change', update);
    connection?.addEventListener('change', update);
    document.addEventListener('visibilitychange', update);
    if (target.value && 'IntersectionObserver' in window) {
      observer = new IntersectionObserver(entries => { visible.value = entries.some(entry => entry.isIntersecting); }, { rootMargin: '150px' });
      observer.observe(target.value);
    }
  });
  onBeforeUnmount(() => {
    observer?.disconnect();
    media?.removeEventListener('change', update);
    connection?.removeEventListener('change', update);
    document.removeEventListener('visibilitychange', update);
  });
  return computed(() => allowed.value && visible.value);
}
