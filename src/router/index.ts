import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/website/HomeView.vue';

const ProductsView = () => import('../views/website/ProductsView.vue');
const CategoryView = () => import('../views/website/CategoryView.vue');
const CollectionView = () => import('../views/website/CollectionView.vue');
const ProductDetailView = () => import('../views/website/ProductDetailView.vue');
const SearchView = () => import('../views/website/SearchView.vue');
const AboutView = () => import('../views/website/AboutView.vue');
const ContactView = () => import('../views/website/ContactView.vue');
const CompanyInformationView = () => import('../views/website/CompanyInformationView.vue');
const CartView = () => import('../views/website/CartView.vue');
const CheckoutView = () => import('../views/website/CheckoutView.vue');
const OrderSuccessView = () => import('../views/website/OrderSuccessView.vue');

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/products', name: 'products', component: ProductsView },
    { path: '/categories/:slug', name: 'category', component: CategoryView },
    { path: '/category/:slug', name: 'category-short', component: CategoryView },
    { path: '/collections/:slug', name: 'collection', component: CollectionView },
    { path: '/collection/:slug', name: 'collection-short', component: CollectionView },
    { path: '/products/:slug', name: 'product-detail', component: ProductDetailView },
    { path: '/search', name: 'search', component: SearchView },
    { path: '/cart', name: 'cart', component: CartView },
    { path: '/checkout', name: 'checkout', component: CheckoutView },
    { path: '/order-success', name: 'order-success', component: OrderSuccessView },
    { path: '/about', name: 'about', component: AboutView },
    { path: '/company-information', name: 'company-information', component: CompanyInformationView },
    { path: '/contact', name: 'contact', component: ContactView },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
});

export default router;
