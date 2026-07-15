import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/website/HomeView.vue';

const ProductsView = () => import('../views/website/ProductsView.vue');
const CategoryView = () => import('../views/website/CategoryView.vue');
const ProductDetailView = () => import('../views/website/ProductDetailView.vue');
const SearchView = () => import('../views/website/SearchView.vue');
const AboutView = () => import('../views/website/AboutView.vue');
const ContactView = () => import('../views/website/ContactView.vue');

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
