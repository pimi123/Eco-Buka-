import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { apiGet, hasLaravelApiConfig } from '../lib/api';
import { demoHomepageShowcase, demoProducts } from '../lib/demoData';
import type { HomepageBanner, HomepageNavigationCard, HomepagePromoCard, HomepageSection, HomepageSectionProduct } from '../types/homepage';
import type { Product } from '../types/product';

const DEFAULT_SECTION_KEY = 'solar_system_showcase';
const DEFAULT_PROMO_SECTION_KEY = 'new_products';

function bySortOrder<T extends { sort_order: number }>(items: T[]) {
  return [...items].sort((a, b) => a.sort_order - b.sort_order);
}

export const useHomepageStore = defineStore('homepage', () => {
  const section = ref<HomepageSection | null>(null);
  const productLinks = ref<HomepageSectionProduct[]>([]);
  const navigationCards = ref<HomepageNavigationCard[]>([]);
  const banners = ref<HomepageBanner[]>([]);
  const promoCards = ref<HomepagePromoCard[]>([]);
  const loading = ref(false);
  const promoCardsLoading = ref(false);
  const promoCardsError = ref<string | null>(null);

  const activeProductLinks = computed(() => bySortOrder(productLinks.value.filter((item) => item.active)));
  const showcaseProducts = computed(() => activeProductLinks.value.map((item) => item.product).filter(Boolean) as Product[]);
  const activeNavigationCards = computed(() => bySortOrder(navigationCards.value.filter((card) => card.active)));
  const activeBanners = computed(() => bySortOrder(banners.value.filter((banner) => banner.active)));
  const activePromoCards = computed(() => bySortOrder(promoCards.value.filter((card) => card.active)));
  const primaryBanner = computed(() => activeBanners.value[0] || null);

  function setDemoData() {
    section.value = demoHomepageShowcase.section as HomepageSection;
    productLinks.value = demoHomepageShowcase.productLinks as HomepageSectionProduct[];
    navigationCards.value = demoHomepageShowcase.navigationCards as HomepageNavigationCard[];
    banners.value = demoHomepageShowcase.banners as HomepageBanner[];
  }

  function ensureLocalSection() {
    if (!section.value) section.value = demoHomepageShowcase.section as HomepageSection;
    return section.value;
  }

  function mapPromoCard(card: HomepagePromoCard): HomepagePromoCard {
    return {
      ...card,
      id: card.id,
      background_image_url: card.background_image_url || (card as any).background_image || null,
      mobile_background_image_url: card.mobile_background_image_url || (card as any).mobile_background_image || null,
      text_color: card.text_color || 'light',
      active: card.active !== false,
      sort_order: Number(card.sort_order || 0),
    };
  }

  async function fetchPromoCards(sectionKey = DEFAULT_PROMO_SECTION_KEY) {
    promoCardsLoading.value = true;
    promoCardsError.value = null;

    if (hasLaravelApiConfig) {
      try {
        const data = await apiGet<HomepagePromoCard[]>(`/home/promo-cards/${sectionKey}`);
        promoCards.value = data.map(mapPromoCard);
        promoCardsLoading.value = false;
        return;
      } catch (error) {
        promoCardsError.value = error instanceof Error ? error.message : 'Promo cards could not be loaded.';
      }
    }

    promoCards.value = [];
    promoCardsLoading.value = false;
  }

  async function fetchHomepageShowcase(sectionKey = DEFAULT_SECTION_KEY) {
    loading.value = true;

    if (hasLaravelApiConfig) {
      try {
        const [showcase, cards, featureBanners] = await Promise.all([
          apiGet<{ section: HomepageSection; products: Product[] }>(`/home/showcase/${sectionKey}`),
          apiGet<HomepageNavigationCard[]>(`/home/navigation-cards/${sectionKey}`),
          apiGet<HomepageBanner[]>(`/home/feature-banners/${sectionKey}`),
        ]);

        section.value = showcase.section;
        productLinks.value = showcase.products.map((product, index) => ({
          id: `api-product-${product.id}`,
          section_id: showcase.section.id,
          product_id: String(product.id),
          product: {
            ...product,
            id: String(product.id),
            image_url: (product as any).image_url || (product as any).main_image_url || null,
            category: typeof (product as any).category === 'object' ? (product as any).category.name : (product as any).category,
          },
          sort_order: index + 1,
          active: true,
        }));
        navigationCards.value = cards.map((card) => ({ ...card, image_url: card.image_url || (card as any).image || null }));
        banners.value = featureBanners.map((banner) => ({
          ...banner,
          background_image_url: banner.background_image_url || (banner as any).background_image || null,
          mobile_background_image_url: banner.mobile_background_image_url || (banner as any).mobile_background_image || null,
        }));
        loading.value = false;
        return;
      } catch {
        // Fall through to demo content so the storefront stays usable offline.
      }
    }

    setDemoData();
    loading.value = false;
  }

  async function saveSection(payload: Partial<HomepageSection>) {
    const current = ensureLocalSection();
    section.value = { ...current, ...payload, section_key: payload.section_key || current.section_key || DEFAULT_SECTION_KEY } as HomepageSection;
    return section.value;
  }

  async function saveSectionProduct(payload: Partial<HomepageSectionProduct>) {
    const current = ensureLocalSection();
    const id = payload.id || crypto.randomUUID();
    const product = demoProducts.find((item) => item.id === payload.product_id) || payload.product;
    const next = { active: true, sort_order: productLinks.value.length + 1, ...payload, id, section_id: current.id, product } as HomepageSectionProduct;
    productLinks.value = productLinks.value.some((item) => item.id === id)
      ? productLinks.value.map((item) => (item.id === id ? next : item))
      : [...productLinks.value, next];
    return next;
  }

  async function deleteSectionProduct(id: string) {
    productLinks.value = productLinks.value.filter((item) => item.id !== id);
  }

  async function saveNavigationCard(payload: Partial<HomepageNavigationCard>) {
    const current = ensureLocalSection();
    const id = payload.id || crypto.randomUUID();
    const next = { title: '', link: '/', image_url: '', active: true, sort_order: navigationCards.value.length + 1, ...payload, id, section_id: current.id } as HomepageNavigationCard;
    navigationCards.value = navigationCards.value.some((item) => item.id === id)
      ? navigationCards.value.map((item) => (item.id === id ? next : item))
      : [...navigationCards.value, next];
    return next;
  }

  async function deleteNavigationCard(id: string) {
    navigationCards.value = navigationCards.value.filter((item) => item.id !== id);
  }

  async function saveBanner(payload: Partial<HomepageBanner>) {
    const current = ensureLocalSection();
    const id = payload.id || crypto.randomUUID();
    const next = {
      title: '',
      text_color: 'light',
      text_alignment: 'left',
      active: true,
      sort_order: banners.value.length + 1,
      ...payload,
      id,
      section_id: current.id,
    } as HomepageBanner;
    banners.value = banners.value.some((item) => item.id === id)
      ? banners.value.map((item) => (item.id === id ? next : item))
      : [...banners.value, next];
    return next;
  }

  async function deleteBanner(id: string) {
    banners.value = banners.value.filter((item) => item.id !== id);
  }

  async function savePromoCard(payload: Partial<HomepagePromoCard>) {
    const id = payload.id || crypto.randomUUID();
    const next = {
      section_key: DEFAULT_PROMO_SECTION_KEY,
      title: '',
      text_color: 'light',
      active: true,
      sort_order: promoCards.value.length + 1,
      ...payload,
      id,
    } as HomepagePromoCard;
    promoCards.value = promoCards.value.some((item) => item.id === id)
      ? promoCards.value.map((item) => (item.id === id ? next : item))
      : [...promoCards.value, next];
    return next;
  }

  async function deletePromoCard(id: string | number) {
    promoCards.value = promoCards.value.filter((item) => item.id !== id);
  }

  return {
    section,
    productLinks,
    navigationCards,
    banners,
    promoCards,
    loading,
    promoCardsLoading,
    promoCardsError,
    showcaseProducts,
    activeNavigationCards,
    activeBanners,
    activePromoCards,
    primaryBanner,
    fetchPromoCards,
    fetchHomepageShowcase,
    saveSection,
    saveSectionProduct,
    deleteSectionProduct,
    saveNavigationCard,
    deleteNavigationCard,
    saveBanner,
    deleteBanner,
    savePromoCard,
    deletePromoCard,
  };
});
