import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/website/HomeView.vue';
import ProductsView from '../views/website/ProductsView.vue';
import CategoryView from '../views/website/CategoryView.vue';
import ProductDetailView from '../views/website/ProductDetailView.vue';
import SearchView from '../views/website/SearchView.vue';
import AboutView from '../views/website/AboutView.vue';
import ContactView from '../views/website/ContactView.vue';

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/products', name: 'products', component: ProductsView },
    { path: '/categories/:slug', name: 'category', component: CategoryView },
    { path: '/category/:slug', name: 'category-short', component: CategoryView },
    { path: '/products/:slug', name: 'product-detail', component: ProductDetailView },
    { path: '/search', name: 'search', component: SearchView },
    { path: '/about', name: 'about', component: AboutView },
    { path: '/contact', name: 'contact', component: ContactView },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
});

export default router;
