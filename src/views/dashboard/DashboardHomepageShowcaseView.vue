<script setup lang="ts">
import { computed, onMounted, reactive, watch } from 'vue';
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import DataTable from '../../components/dashboard/DataTable.vue';
import ImageUploadField from '../../components/dashboard/ImageUploadField.vue';
import { useHomepageStore } from '../../stores/homepageStore';
import { useProductStore } from '../../stores/productStore';
import type { HomepageBanner, HomepageNavigationCard, HomepageSectionProduct } from '../../types/homepage';

const homepageStore = useHomepageStore();
const productStore = useProductStore();

const sectionForm = reactive({
  title: '',
  subtitle: '',
  active: true,
  sort_order: 10,
});

const productForm = reactive({
  product_id: '',
  sort_order: 1,
  active: true,
});

const cardForm = reactive({
  title: '',
  link: '/products',
  image_url: '',
  sort_order: 1,
  active: true,
});

const bannerForm = reactive({
  section_heading: '',
  eyebrow: '',
  title: '',
  subtitle: '',
  button_text: 'Buy Now',
  button_link: '/products',
  background_image_url: '',
  mobile_background_image_url: '',
  text_color: 'light',
  text_alignment: 'left',
  sort_order: 1,
  active: true,
});

const availableProducts = computed(() => productStore.products);

watch(
  () => homepageStore.section,
  (section) => {
    if (!section) return;
    sectionForm.title = section.title || '';
    sectionForm.subtitle = section.subtitle || '';
    sectionForm.active = section.active;
    sectionForm.sort_order = section.sort_order || 10;
  },
  { immediate: true },
);

onMounted(async () => {
  await Promise.all([productStore.fetchProducts(true), homepageStore.fetchHomepageShowcase('solar_system_showcase', true)]);
});

async function saveSection() {
  await homepageStore.saveSection({
    ...homepageStore.section,
    section_key: 'solar_system_showcase',
    layout_type: 'products_with_navigation_and_banner',
    title: sectionForm.title,
    subtitle: sectionForm.subtitle,
    active: sectionForm.active,
    sort_order: sectionForm.sort_order,
  });
}

async function addProduct() {
  if (!productForm.product_id) return;
  const product = availableProducts.value.find((item) => item.id === productForm.product_id);
  await homepageStore.saveSectionProduct({ ...productForm, product });
  productForm.product_id = '';
  productForm.sort_order += 1;
}

async function saveProductLink(link: HomepageSectionProduct) {
  await homepageStore.saveSectionProduct(link);
}

async function addCard() {
  if (!cardForm.title) return;
  await homepageStore.saveNavigationCard({ ...cardForm });
  cardForm.title = '';
  cardForm.link = '/products';
  cardForm.image_url = '';
  cardForm.sort_order += 1;
}

async function saveCard(card: HomepageNavigationCard) {
  await homepageStore.saveNavigationCard(card);
}

async function addBanner() {
  if (!bannerForm.title) return;
  await homepageStore.saveBanner({ ...bannerForm });
  bannerForm.title = '';
  bannerForm.section_heading = '';
  bannerForm.eyebrow = '';
  bannerForm.subtitle = '';
  bannerForm.background_image_url = '';
  bannerForm.mobile_background_image_url = '';
  bannerForm.sort_order += 1;
}

async function saveBanner(banner: HomepageBanner) {
  await homepageStore.saveBanner(banner);
}
</script>

<template>
  <DashboardLayout>
    <div class="mb-5">
      <h1 class="text-xl font-black sm:text-2xl">Homepage Showcase</h1>
      <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
        Manage the dynamic product row, navigation cards, and promotional banner shown on the homepage.
      </p>
    </div>

    <div class="grid gap-5">
      <section class="rounded-lg border border-line bg-white p-4 shadow-sm sm:p-5">
        <h2 class="text-lg font-black">Section Settings</h2>
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
          <label class="grid gap-2"><span class="label">Title</span><input v-model="sectionForm.title" class="input-field" /></label>
          <label class="grid gap-2"><span class="label">Sort order</span><input v-model.number="sectionForm.sort_order" type="number" class="input-field" /></label>
          <label class="grid gap-2 lg:col-span-2"><span class="label">Subtitle</span><textarea v-model="sectionForm.subtitle" class="input-field min-h-24" /></label>
        </div>
        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="sectionForm.active" type="checkbox" /> Active on homepage</label>
          <button class="btn-primary w-full sm:w-auto" @click="saveSection">Save Section</button>
        </div>
      </section>

      <section class="rounded-lg border border-line bg-white p-4 shadow-sm sm:p-5">
        <h2 class="text-lg font-black">Showcase Products</h2>
        <div class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,1fr)_140px_120px_140px]">
          <select v-model="productForm.product_id" class="input-field">
            <option value="">Select product</option>
            <option v-for="product in availableProducts" :key="product.id" :value="product.id">{{ product.name }}</option>
          </select>
          <input v-model.number="productForm.sort_order" type="number" class="input-field" placeholder="Sort" />
          <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="productForm.active" type="checkbox" /> Active</label>
          <button class="btn-primary w-full" @click="addProduct">Add Product</button>
        </div>

        <div class="mt-5">
          <DataTable>
            <thead class="bg-mist text-xs uppercase text-slate-500">
              <tr><th class="px-4 py-3">Product</th><th class="px-4 py-3">Sort</th><th class="px-4 py-3">Active</th><th class="px-4 py-3">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-line">
              <tr v-for="link in homepageStore.productLinks" :key="link.id">
                <td class="px-4 py-3 font-semibold">{{ link.product?.name || link.product_id }}</td>
                <td class="px-4 py-3"><input v-model.number="link.sort_order" type="number" class="input-field max-w-24" /></td>
                <td class="px-4 py-3"><input v-model="link.active" type="checkbox" /></td>
                <td class="px-4 py-3">
                  <div class="flex flex-wrap gap-2">
                    <button class="btn-secondary min-h-9 px-3 py-1.5" @click="saveProductLink(link)">Save</button>
                    <button class="btn-secondary min-h-9 px-3 py-1.5" @click="homepageStore.deleteSectionProduct(link.id)">Remove</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </DataTable>
        </div>
      </section>

      <section class="rounded-lg border border-line bg-white p-4 shadow-sm sm:p-5">
        <h2 class="text-lg font-black">Navigation Cards</h2>
        <div class="mt-4 grid gap-3 lg:grid-cols-[1fr_1fr_110px_120px_130px]">
          <input v-model="cardForm.title" class="input-field" placeholder="Title" />
          <input v-model="cardForm.link" class="input-field" placeholder="/categories/solar-panels" />
          <input v-model.number="cardForm.sort_order" type="number" class="input-field" placeholder="Sort" />
          <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="cardForm.active" type="checkbox" /> Active</label>
          <button class="btn-primary w-full" @click="addCard">Add Card</button>
        </div>
        <div class="mt-3">
          <ImageUploadField v-model="cardForm.image_url" label="New navigation card image" folder="homepage/navigation-cards" />
        </div>

        <div class="mt-5 grid gap-4">
          <div v-for="card in homepageStore.navigationCards" :key="card.id" class="grid gap-3 rounded-lg border border-line p-4 lg:grid-cols-[1fr_1fr_1fr_90px_90px_160px]">
            <input v-model="card.title" class="input-field" />
            <input v-model="card.link" class="input-field" />
            <input v-model.number="card.sort_order" type="number" class="input-field" />
            <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="card.active" type="checkbox" /> Active</label>
            <div class="flex flex-wrap gap-2">
              <button class="btn-secondary min-h-9 px-3 py-1.5" @click="saveCard(card)">Save</button>
              <button class="btn-secondary min-h-9 px-3 py-1.5" @click="homepageStore.deleteNavigationCard(card.id)">Delete</button>
            </div>
            <div class="lg:col-span-6">
              <ImageUploadField v-model="card.image_url" label="Navigation card image" folder="homepage/navigation-cards" />
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-lg border border-line bg-white p-4 shadow-sm sm:p-5">
        <h2 class="text-lg font-black">Promotional Banners</h2>
        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
          <input v-model="bannerForm.section_heading" class="input-field" placeholder="Section heading" />
          <input v-model="bannerForm.eyebrow" class="input-field" placeholder="Eyebrow" />
          <input v-model="bannerForm.title" class="input-field" placeholder="Title" />
          <input v-model="bannerForm.subtitle" class="input-field" placeholder="Subtitle" />
          <input v-model="bannerForm.button_text" class="input-field" placeholder="Button text" />
          <input v-model="bannerForm.button_link" class="input-field" placeholder="Button link" />
          <select v-model="bannerForm.text_color" class="input-field"><option value="light">Light text</option><option value="dark">Dark text</option></select>
          <select v-model="bannerForm.text_alignment" class="input-field"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select>
          <input v-model.number="bannerForm.sort_order" type="number" class="input-field" placeholder="Sort" />
          <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="bannerForm.active" type="checkbox" /> Active</label>
        </div>
        <div class="mt-3 grid gap-3 lg:grid-cols-2">
          <ImageUploadField v-model="bannerForm.background_image_url" label="New banner background image" folder="homepage/banners" />
          <ImageUploadField v-model="bannerForm.mobile_background_image_url" label="New banner mobile image" folder="homepage/banners/mobile" />
        </div>
        <button class="btn-primary mt-4 w-full sm:w-auto" @click="addBanner">Add Banner</button>

        <div class="mt-5 grid gap-4">
          <div v-for="banner in homepageStore.banners" :key="banner.id" class="grid gap-3 rounded-lg border border-line p-4">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
              <input v-model="banner.section_heading" class="input-field" />
              <input v-model="banner.eyebrow" class="input-field" />
              <input v-model="banner.title" class="input-field" />
              <input v-model="banner.subtitle" class="input-field" />
              <input v-model="banner.button_text" class="input-field" />
              <input v-model="banner.button_link" class="input-field" />
              <select v-model="banner.text_color" class="input-field"><option value="light">Light text</option><option value="dark">Dark text</option></select>
              <select v-model="banner.text_alignment" class="input-field"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select>
              <input v-model.number="banner.sort_order" type="number" class="input-field" />
              <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="banner.active" type="checkbox" /> Active</label>
            </div>
            <div class="grid gap-3 lg:grid-cols-2">
              <ImageUploadField v-model="banner.background_image_url" label="Banner background image" folder="homepage/banners" />
              <ImageUploadField v-model="banner.mobile_background_image_url" label="Banner mobile image" folder="homepage/banners/mobile" />
            </div>
            <div class="flex flex-wrap gap-2">
              <button class="btn-secondary min-h-9 px-3 py-1.5" @click="saveBanner(banner)">Save</button>
              <button class="btn-secondary min-h-9 px-3 py-1.5" @click="homepageStore.deleteBanner(banner.id)">Delete</button>
            </div>
          </div>
        </div>
      </section>
    </div>
  </DashboardLayout>
</template>
