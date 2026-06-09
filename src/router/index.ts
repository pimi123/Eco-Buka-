import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/website/HomeView.vue';
import ProductsView from '../views/website/ProductsView.vue';
import CategoryView from '../views/website/CategoryView.vue';
import ProductDetailView from '../views/website/ProductDetailView.vue';
import SearchView from '../views/website/SearchView.vue';
import AboutView from '../views/website/AboutView.vue';
import ContactView from '../views/website/ContactView.vue';
import DashboardView from '../views/dashboard/DashboardView.vue';
import DashboardProductsView from '../views/dashboard/DashboardProductsView.vue';
import DashboardCategoriesView from '../views/dashboard/DashboardCategoriesView.vue';
import DashboardHomepageShowcaseView from '../views/dashboard/DashboardHomepageShowcaseView.vue';
import ProductCreateView from '../views/dashboard/ProductCreateView.vue';
import ProductEditView from '../views/dashboard/ProductEditView.vue';
import CategoryCreateView from '../views/dashboard/CategoryCreateView.vue';
import SettingsView from '../views/dashboard/SettingsView.vue';

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
    { path: '/dashboard', name: 'dashboard', component: DashboardView },
    { path: '/dashboard/products', name: 'dashboard-products', component: DashboardProductsView },
    { path: '/dashboard/products/new', name: 'product-create', component: ProductCreateView },
    { path: '/dashboard/products/:id/edit', name: 'product-edit', component: ProductEditView },
    { path: '/dashboard/categories', name: 'dashboard-categories', component: DashboardCategoriesView },
    { path: '/dashboard/categories/new', name: 'category-create', component: CategoryCreateView },
    { path: '/dashboard/homepage-showcase', name: 'dashboard-homepage-showcase', component: DashboardHomepageShowcaseView },
    { path: '/dashboard/settings', name: 'settings', component: SettingsView },
  ],
});

export default router;
