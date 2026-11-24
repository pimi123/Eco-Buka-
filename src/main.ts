import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import './style.css';
import { initializeTracking, trackPageView } from './lib/metaPixel';

initializeTracking();
router.afterEach(() => trackPageView());

createApp(App).use(createPinia()).use(router).mount('#app');
