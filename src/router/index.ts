import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/website/HomeView.vue';
import { useAuthStore } from '../stores/authStore';

const ProductsView = () => import('../views/website/ProductsView.vue');
const CategoryView = () => import('../views/website/CategoryView.vue');
const CollectionView = () => import('../views/website/CollectionView.vue');
const ProductDetailView = () => import('../views/website/ProductDetailView.vue');
const SearchView = () => import('../views/website/SearchView.vue');
const AboutView = () => import('../views/website/AboutView.vue');
const ContactView = () => import('../views/website/ContactView.vue');
const CompanyInformationView = () => import('../views/website/CompanyInformationView.vue');
const ReturnPolicyView = () => import('../views/website/ReturnPolicyView.vue');
const PrivacyPolicyView = () => import('../views/website/PrivacyPolicyView.vue');
const CookiePolicyView = () => import('../views/website/CookiePolicyView.vue');
const PowerOceanView = () => import('../views/website/PowerOceanView.vue');
const CartView = () => import('../views/website/CartView.vue');
const CheckoutView = () => import('../views/website/CheckoutView.vue');
const OrderSuccessView = () => import('../views/website/OrderSuccessView.vue');
const PaymentFailedView = () => import('../views/website/PaymentFailedView.vue');
const TrackOrderView = () => import('../views/website/TrackOrderView.vue');
const LoginView = () => import('../views/website/LoginView.vue');
const RegisterView = () => import('../views/website/RegisterView.vue');
const ForgotPasswordView = () => import('../views/website/ForgotPasswordView.vue');
const ResetPasswordView = () => import('../views/website/ResetPasswordView.vue');
const AccountView = () => import('../views/website/AccountView.vue');

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
    { path: '/checkout', name: 'checkout', component: CheckoutView, meta: { requiresAuth: true, requiresVerifiedEmail: true } },
    { path: '/order-success', name: 'order-success', component: OrderSuccessView },
    { path: '/payment-failed', name: 'payment-failed', component: PaymentFailedView },
    { path: '/track-order', name: 'track-order', component: TrackOrderView },
    { path: '/login', name: 'login', component: LoginView },
    { path: '/register', name: 'register', component: RegisterView },
    { path: '/forgot-password', name: 'forgot-password', component: ForgotPasswordView },
    { path: '/reset-password', name: 'reset-password', component: ResetPasswordView },
    { path: '/account', name: 'account', component: AccountView, meta: { requiresAuth: true } },
    { path: '/about', name: 'about', component: AboutView },
    { path: '/company-information', name: 'company-information', component: CompanyInformationView },
    { path: '/politika-e-kthimit', name: 'return-policy', component: ReturnPolicyView },
    { path: '/return-policy', redirect: '/politika-e-kthimit' },
    { path: '/politika-e-privatesise', name: 'privacy-policy', component: PrivacyPolicyView },
    { path: '/privacy-policy', redirect: '/politika-e-privatesise' },
    { path: '/politika-e-cookies', name: 'cookie-policy', component: CookiePolicyView },
    { path: '/cookie-policy', redirect: '/politika-e-cookies' },
    { path: '/solutions/powerocean', name: 'powerocean', component: PowerOceanView },
    { path: '/contact', name: 'contact', component: ContactView },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
});

router.beforeEach(async (to) => {
  const authStore = useAuthStore();

  if (authStore.token && !authStore.user) {
    await authStore.fetchUser().catch(() => null);
  }

  if ((to.name === 'login' || to.name === 'register') && authStore.isAuthenticated) {
    return '/account';
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return {
      path: '/login',
      query: { redirect: to.fullPath },
    };
  }

  if (to.meta.requiresVerifiedEmail && authStore.isAuthenticated && !authStore.isEmailVerified) {
    return {
      path: '/account',
      query: { verify_email: '1', redirect: to.fullPath },
    };
  }

  return true;
});

export default router;
