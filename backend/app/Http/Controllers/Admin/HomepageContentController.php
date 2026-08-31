<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\FeatureBanner;
use App\Models\HeroBanner;
use App\Models\NavigationCard;
use App\Models\PromoCard;
use App\Models\Product;
use App\Models\ShowcaseSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HomepageContentController extends Controller
{
    public function index()
    {
        $sections = ShowcaseSection::query()
            ->withCount(['products', 'promoCards'])
            ->whereIn('section_type', ShowcaseSection::SECTION_TYPES)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(function (ShowcaseSection $section) {
                $sectionKeys = [$section->section_key];

                return [
                    'section' => $section,
                    'source_products_count' => $this->countProductsForSource($section),
                    'navigation_cards_count' => NavigationCard::query()->whereIn('section_key', $sectionKeys)->count(),
                    'feature_banners_count' => FeatureBanner::query()->whereIn('section_key', $sectionKeys)->count(),
                    'fallback_promo_cards_count' => PromoCard::query()
                        ->whereNull('homepage_section_id')
                        ->where('section_key', $section->section_key)
                        ->count(),
                ];
            });

        return view('admin.homepage-content.index', [
            'sections' => $sections,
            'heroBannerCount' => HeroBanner::query()->where('active', true)->count(),
            'categoryCarouselLabel' => 'Dynamic category carousel',
        ]);
    }

    public function updateOrder(Request $request)
    {
        $data = $request->validate([
            'sections' => ['nullable', 'array'],
            'sections.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'sections.*.active' => ['nullable', 'boolean'],
        ]);

        foreach ($data['sections'] ?? [] as $sectionId => $settings) {
            ShowcaseSection::query()
                ->whereKey($sectionId)
                ->update([
                    'sort_order' => (int) ($settings['sort_order'] ?? 0),
                    'active' => (bool) ($settings['active'] ?? false),
                ]);
        }

        return back()->with('status', 'Homepage display order updated.');
    }

    public function editPromoCards(ShowcaseSection $section)
    {
        abort_unless($section->section_type === 'promo_cards', 404);

        $cards = PromoCard::query()
            ->where(function ($query) use ($section): void {
                $query->where('homepage_section_id', $section->id)
                    ->orWhere(function ($fallback) use ($section): void {
                        $fallback->whereNull('homepage_section_id')
                            ->where('section_key', $section->section_key);
                    });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.homepage-content.promo-cards', [
            'section' => $section,
            'cards' => $cards,
        ]);
    }

    public function updatePromoCards(Request $request, ShowcaseSection $section)
    {
        abort_unless($section->section_type === 'promo_cards', 404);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'layout_variant' => ['nullable', Rule::in(['single_banner', 'two_cards', 'grid', 'carousel'])],
            'display_limit' => ['nullable', 'integer', 'min:1', 'max:24'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
            'cards' => ['nullable', 'array'],
            'cards.*.id' => ['nullable', 'integer', Rule::exists('promo_cards', 'id')],
            'cards.*.label' => ['nullable', 'string', 'max:255'],
            'cards.*.title' => ['nullable', 'string', 'max:255'],
            'cards.*.subtitle' => ['nullable', 'string'],
            'cards.*.button_text' => ['nullable', 'string', 'max:255'],
            'cards.*.button_link' => ['nullable', 'string', 'max:255'],
            'cards.*.category_slug' => ['nullable', 'string', 'max:255'],
            'cards.*.text_color' => ['nullable', Rule::in(['light', 'dark'])],
            'cards.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'cards.*.active' => ['nullable', 'boolean'],
            'cards.*._delete' => ['nullable', 'boolean'],
            'cards.*.background_image' => ['nullable', 'image', 'max:8192'],
            'cards.*.mobile_background_image' => ['nullable', 'image', 'max:8192'],
            'cards.*.background_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:51200'],
            'cards.*.background_image_remove' => ['nullable', 'boolean'],
            'cards.*.mobile_background_image_remove' => ['nullable', 'boolean'],
            'cards.*.background_video_remove' => ['nullable', 'boolean'],
        ], $this->uploadValidationMessages());

        $uploadedFiles = [];
        $filesToDelete = [];

        try {
            DB::transaction(function () use ($request, $section, $data, &$uploadedFiles, &$filesToDelete): void {
                $section->update([
                    'title' => $data['title'] ?? null,
                    'subtitle' => $data['subtitle'] ?? null,
                    'display_limit' => (int) ($data['display_limit'] ?? 6),
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                    'active' => $request->boolean('active'),
                    'source_type' => 'manual_cards',
                    'layout_variant' => $data['layout_variant'] ?? 'carousel',
                ]);

                foreach ($data['cards'] ?? [] as $index => $cardData) {
                    $card = null;
                    if (! empty($cardData['id'])) {
                        $card = PromoCard::query()
                            ->where(function ($query) use ($section): void {
                                $query->where('homepage_section_id', $section->id)
                                    ->orWhere(function ($fallback) use ($section): void {
                                        $fallback->whereNull('homepage_section_id')
                                            ->where('section_key', $section->section_key);
                                    });
                            })
                            ->find($cardData['id']);
                    }

                    if (($cardData['_delete'] ?? false) && $card) {
                        $this->queuePromoCardFilesForDeletion($card, $filesToDelete);
                        $card->delete();
                        continue;
                    }

                    if (($cardData['_delete'] ?? false) || blank($cardData['title'] ?? null)) {
                        continue;
                    }

                    $card ??= new PromoCard();
                    $card->fill([
                        'homepage_section_id' => $section->id,
                        'section_key' => $section->section_key,
                        'label' => $cardData['label'] ?? null,
                        'title' => $cardData['title'],
                        'subtitle' => $cardData['subtitle'] ?? null,
                        'button_text' => $cardData['button_text'] ?? null,
                        'button_link' => $cardData['button_link'] ?? null,
                        'category_slug' => $cardData['category_slug'] ?? null,
                        'text_color' => $cardData['text_color'] ?? 'light',
                        'sort_order' => (int) ($cardData['sort_order'] ?? ($index + 1)),
                        'active' => (bool) ($cardData['active'] ?? false),
                    ]);

                    foreach (['background_image', 'mobile_background_image', 'background_video'] as $fileField) {
                        if ($request->boolean("cards.{$index}.{$fileField}_remove") && $card->{$fileField}) {
                            $filesToDelete[] = $card->{$fileField};
                            $card->{$fileField} = null;
                        }

                        if ($request->hasFile("cards.{$index}.{$fileField}")) {
                            if ($card->{$fileField}) {
                                $filesToDelete[] = $card->{$fileField};
                            }

                            $folder = $fileField === 'background_video' ? 'promo-cards/videos' : 'promo-cards';
                            $path = $request->file("cards.{$index}.{$fileField}")->store($folder, 'public');
                            $uploadedFiles[] = $path;
                            $card->{$fileField} = $path;
                        }
                    }

                    $card->save();
                }
            });
        } catch (\Throwable $exception) {
            foreach ($uploadedFiles as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        foreach (array_unique(array_filter($filesToDelete)) as $path) {
            if (! $this->promoImageIsUsedElsewhere($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        return back()->with('status', 'Promo card section updated.');
    }

    public function editFeaturedProducts(ShowcaseSection $section)
    {
        abort_unless(in_array($section->section_type, ['product_grid', 'featured_category'], true), 404);

        return view('admin.homepage-content.featured-products', [
            'section' => $section,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'collections' => Collection::query()->where('active', true)->orderBy('type')->orderBy('name')->get(['id', 'name', 'slug', 'type']),
        ]);
    }

    public function updateFeaturedProducts(Request $request, ShowcaseSection $section)
    {
        abort_unless(in_array($section->section_type, ['product_grid', 'featured_category'], true), 404);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'banner_title' => ['nullable', 'string', 'max:255'],
            'banner_subtitle' => ['nullable', 'string'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_link' => ['nullable', 'string', 'max:255'],
            'source_type' => ['required', Rule::in(['category', 'collection'])],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'collection_id' => ['nullable', 'integer', 'exists:collections,id'],
            'display_limit' => ['nullable', 'integer', 'min:1', 'max:24'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
            'banner_image' => ['nullable', 'image', 'max:8192'],
            'mobile_banner_image' => ['nullable', 'image', 'max:8192'],
            'background_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:51200'],
            'banner_image_remove' => ['nullable', 'boolean'],
            'mobile_banner_image_remove' => ['nullable', 'boolean'],
            'background_video_remove' => ['nullable', 'boolean'],
        ], $this->uploadValidationMessages());

        $source = $this->resolveFeaturedSource($data);
        if (! $source) {
            return back()
                ->withErrors(['source_type' => 'Choose a valid category or collection for this section.'])
                ->withInput();
        }

        $uploadedFiles = [];
        $filesToDelete = [];

        try {
            DB::transaction(function () use ($request, $section, $data, $source, &$uploadedFiles, &$filesToDelete): void {
                $payload = [
                    'title' => $data['title'] ?? null,
                    'subtitle' => $data['subtitle'] ?? null,
                    'eyebrow' => $data['eyebrow'] ?? null,
                    'banner_title' => $data['banner_title'] ?? null,
                    'banner_subtitle' => $data['banner_subtitle'] ?? null,
                    'button_text' => $data['button_text'] ?? null,
                    'button_link' => $data['button_link'] ?? null,
                    'source_type' => $data['source_type'],
                    'source_id' => $source->id,
                    'source_slug' => $source->slug,
                    'display_limit' => (int) ($data['display_limit'] ?? 8),
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                    'active' => $request->boolean('active'),
                ];

                foreach (['banner_image', 'mobile_banner_image', 'background_video'] as $fileField) {
                    if ($request->boolean($fileField.'_remove') && $section->{$fileField}) {
                        $filesToDelete[] = $section->{$fileField};
                        $payload[$fileField] = null;
                    }

                    if ($request->hasFile($fileField)) {
                        if ($section->{$fileField}) {
                            $filesToDelete[] = $section->{$fileField};
                        }

                        $path = $request->file($fileField)->store('showcase-sections', 'public');
                        $uploadedFiles[] = $path;
                        $payload[$fileField] = $path;
                    }
                }

                $section->update($payload);
            });
        } catch (\Throwable $exception) {
            foreach ($uploadedFiles as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        foreach (array_unique(array_filter($filesToDelete)) as $path) {
            if (! $this->showcaseFileIsUsedElsewhere($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        return back()->with('status', 'Featured product section updated.');
    }

    public function editMixedShowcase(ShowcaseSection $section)
    {
        abort_unless($section->section_type === 'mixed_showcase', 404);

        $section->load('products.category');

        return view('admin.homepage-content.mixed-showcase', [
            'section' => $section,
            'categories' => Category::query()->where('active', true)->orderBy('name')->get(['id', 'name', 'slug']),
            'collections' => Collection::query()->where('active', true)->orderBy('type')->orderBy('name')->get(['id', 'name', 'slug', 'type']),
            'selectedProducts' => $section->products,
            'navigationCards' => NavigationCard::query()
                ->where('section_key', $section->section_key)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
            'featureBanner' => FeatureBanner::query()
                ->where('section_key', $section->section_key)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first(),
        ]);
    }

    public function updateMixedShowcase(Request $request, ShowcaseSection $section)
    {
        abort_unless($section->section_type === 'mixed_showcase', 404);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'source_type' => ['required', Rule::in(['manual_products', 'category', 'collection'])],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'collection_id' => ['nullable', 'integer', 'exists:collections,id'],
            'display_limit' => ['nullable', 'integer', 'min:1', 'max:24'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
            'products' => ['nullable', 'array'],
            'products.*.id' => ['required_with:products', 'integer', 'exists:products,id'],
            'products.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'products.*.active' => ['nullable', 'boolean'],
            'products.*._delete' => ['nullable', 'boolean'],
            'cards' => ['nullable', 'array'],
            'cards.*.id' => ['nullable', 'integer', Rule::exists('navigation_cards', 'id')],
            'cards.*.title' => ['nullable', 'string', 'max:255'],
            'cards.*.link' => ['nullable', 'string', 'max:255'],
            'cards.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'cards.*.active' => ['nullable', 'boolean'],
            'cards.*._delete' => ['nullable', 'boolean'],
            'cards.*.image' => ['nullable', 'image', 'max:8192'],
            'cards.*.image_remove' => ['nullable', 'boolean'],
            'banner.id' => ['nullable', 'integer', Rule::exists('feature_banners', 'id')],
            'banner.section_heading' => ['nullable', 'string', 'max:255'],
            'banner.eyebrow' => ['nullable', 'string', 'max:255'],
            'banner.title' => ['nullable', 'string', 'max:255'],
            'banner.subtitle' => ['nullable', 'string'],
            'banner.price_text' => ['nullable', 'string', 'max:255'],
            'banner.button_text' => ['nullable', 'string', 'max:255'],
            'banner.button_link' => ['nullable', 'string', 'max:255'],
            'banner.text_color' => ['nullable', Rule::in(['light', 'dark'])],
            'banner.text_alignment' => ['nullable', Rule::in(['left', 'center', 'right'])],
            'banner.sort_order' => ['nullable', 'integer', 'min:0'],
            'banner.active' => ['nullable', 'boolean'],
            'banner.background_image' => ['nullable', 'image', 'max:8192'],
            'banner.mobile_background_image' => ['nullable', 'image', 'max:8192'],
            'banner.background_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:51200'],
            'banner.background_image_remove' => ['nullable', 'boolean'],
            'banner.mobile_background_image_remove' => ['nullable', 'boolean'],
            'banner.background_video_remove' => ['nullable', 'boolean'],
        ], $this->uploadValidationMessages());

        $source = $this->resolveMixedSource($data);
        if (in_array($data['source_type'], ['category', 'collection'], true) && ! $source) {
            return back()
                ->withErrors(['source_type' => 'Choose a valid category or collection, or switch to manual products.'])
                ->withInput();
        }

        $uploadedFiles = [];
        $filesToDelete = [];

        try {
            DB::transaction(function () use ($request, $section, $data, $source, &$uploadedFiles, &$filesToDelete): void {
                $section->update([
                    'title' => $data['title'] ?? null,
                    'subtitle' => $data['subtitle'] ?? null,
                    'source_type' => $data['source_type'],
                    'source_id' => $source?->id,
                    'source_slug' => $source?->slug,
                    'display_limit' => (int) ($data['display_limit'] ?? 4),
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                    'active' => $request->boolean('active'),
                ]);

                if ($data['source_type'] === 'manual_products') {
                    $sync = [];
                    foreach ($data['products'] ?? [] as $index => $productData) {
                        if (($productData['_delete'] ?? false) || empty($productData['id'])) {
                            continue;
                        }

                        $sync[(int) $productData['id']] = [
                            'sort_order' => (int) ($productData['sort_order'] ?? ($index + 1)),
                            'active' => (bool) ($productData['active'] ?? false),
                        ];
                    }

                    $section->products()->sync($sync);
                }

                foreach ($data['cards'] ?? [] as $index => $cardData) {
                    $card = null;
                    if (! empty($cardData['id'])) {
                        $card = NavigationCard::query()
                            ->where('section_key', $section->section_key)
                            ->find($cardData['id']);
                    }

                    if (($cardData['_delete'] ?? false) && $card) {
                        if ($card->image) {
                            $filesToDelete[] = $card->image;
                        }
                        $card->delete();
                        continue;
                    }

                    if (($cardData['_delete'] ?? false) || blank($cardData['title'] ?? null)) {
                        continue;
                    }

                    $card ??= new NavigationCard();
                    $card->fill([
                        'section_key' => $section->section_key,
                        'title' => $cardData['title'],
                        'link' => $cardData['link'] ?? null,
                        'sort_order' => (int) ($cardData['sort_order'] ?? ($index + 1)),
                        'active' => (bool) ($cardData['active'] ?? false),
                    ]);

                    if (($cardData['image_remove'] ?? false) && $card->image) {
                        $filesToDelete[] = $card->image;
                        $card->image = null;
                    }

                    if ($request->hasFile("cards.{$index}.image")) {
                        if ($card->image) {
                            $filesToDelete[] = $card->image;
                        }

                        $path = $request->file("cards.{$index}.image")->store('navigation-cards', 'public');
                        $uploadedFiles[] = $path;
                        $card->image = $path;
                    }

                    $card->save();
                }

                $bannerData = $data['banner'] ?? [];
                $hasBannerContent = ! empty($bannerData['id'])
                    || collect([
                        'section_heading',
                        'eyebrow',
                        'title',
                        'subtitle',
                        'price_text',
                        'button_text',
                        'button_link',
                    ])->contains(fn (string $field) => filled($bannerData[$field] ?? null))
                    || $request->hasFile('banner.background_image')
                    || $request->hasFile('banner.mobile_background_image')
                    || $request->hasFile('banner.background_video');

                if ($hasBannerContent) {
                    $banner = null;
                    if (! empty($bannerData['id'])) {
                        $banner = FeatureBanner::query()
                            ->where('section_key', $section->section_key)
                            ->find($bannerData['id']);
                    }

                    $banner ??= new FeatureBanner();
                    $banner->fill([
                        'section_key' => $section->section_key,
                        'section_heading' => $bannerData['section_heading'] ?? null,
                        'eyebrow' => $bannerData['eyebrow'] ?? null,
                        'title' => $bannerData['title'] ?? null,
                        'subtitle' => $bannerData['subtitle'] ?? null,
                        'price_text' => $bannerData['price_text'] ?? null,
                        'button_text' => $bannerData['button_text'] ?? null,
                        'button_link' => $bannerData['button_link'] ?? null,
                        'text_color' => $bannerData['text_color'] ?? 'light',
                        'text_alignment' => $bannerData['text_alignment'] ?? 'left',
                        'sort_order' => (int) ($bannerData['sort_order'] ?? 0),
                        'active' => (bool) ($bannerData['active'] ?? false),
                    ]);

                    foreach (['background_image', 'mobile_background_image', 'background_video'] as $fileField) {
                        if (($bannerData[$fileField.'_remove'] ?? false) && $banner->{$fileField}) {
                            $filesToDelete[] = $banner->{$fileField};
                            $banner->{$fileField} = null;
                        }

                        if ($request->hasFile("banner.{$fileField}")) {
                            if ($banner->{$fileField}) {
                                $filesToDelete[] = $banner->{$fileField};
                            }

                            $folder = $fileField === 'background_video' ? 'feature-banners/videos' : 'feature-banners';
                            $path = $request->file("banner.{$fileField}")->store($folder, 'public');
                            $uploadedFiles[] = $path;
                            $banner->{$fileField} = $path;
                        }
                    }

                    $banner->save();
                }
            });
        } catch (\Throwable $exception) {
            foreach ($uploadedFiles as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        foreach (array_unique(array_filter($filesToDelete)) as $path) {
            if (! $this->homepageMediaIsUsedElsewhere($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        return back()->with('status', 'Mixed showcase section updated.');
    }

    public function editVideoBanner(ShowcaseSection $section)
    {
        abort_unless($section->section_type === 'video_banner', 404);

        $banner = FeatureBanner::query()
            ->where('section_key', $section->section_key)
            ->orderByDesc('active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return view('admin.homepage-content.video-banner', [
            'section' => $section,
            'banner' => $banner,
        ]);
    }

    public function updateVideoBanner(Request $request, ShowcaseSection $section)
    {
        abort_unless($section->section_type === 'video_banner', 404);

        $data = $request->validate([
            'section_title' => ['nullable', 'string', 'max:255'],
            'section_subtitle' => ['nullable', 'string'],
            'section_heading' => ['nullable', 'string', 'max:255'],
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'price_text' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_link' => ['nullable', 'string', 'max:255'],
            'text_color' => ['nullable', Rule::in(['light', 'dark'])],
            'text_alignment' => ['nullable', Rule::in(['left', 'center', 'right'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
            'background_image' => ['nullable', 'image', 'max:8192'],
            'mobile_background_image' => ['nullable', 'image', 'max:8192'],
            'background_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:51200'],
            'background_image_remove' => ['nullable', 'boolean'],
            'mobile_background_image_remove' => ['nullable', 'boolean'],
            'background_video_remove' => ['nullable', 'boolean'],
        ], $this->uploadValidationMessages());

        $banner = FeatureBanner::query()
            ->where('section_key', $section->section_key)
            ->orderByDesc('active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        $uploadedFiles = [];
        $filesToDelete = [];

        try {
            DB::transaction(function () use ($request, $section, $data, &$banner, &$uploadedFiles, &$filesToDelete): void {
                $section->update([
                    'title' => $data['section_title'] ?? null,
                    'subtitle' => $data['section_subtitle'] ?? null,
                    'source_type' => 'manual_cards',
                    'source_id' => null,
                    'source_slug' => null,
                    'display_limit' => 1,
                    'layout_variant' => 'wide_video_banner',
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                    'active' => $request->boolean('active'),
                ]);

                $banner ??= new FeatureBanner();
                $banner->fill([
                    'section_key' => $section->section_key,
                    'section_heading' => $data['section_heading'] ?? null,
                    'eyebrow' => $data['eyebrow'] ?? null,
                    'title' => ($data['title'] ?? null) ?: ($data['section_title'] ?? 'Featured Promo'),
                    'subtitle' => $data['subtitle'] ?? null,
                    'price_text' => $data['price_text'] ?? null,
                    'button_text' => $data['button_text'] ?? null,
                    'button_link' => $data['button_link'] ?? null,
                    'text_color' => $data['text_color'] ?? 'light',
                    'text_alignment' => $data['text_alignment'] ?? 'left',
                    'sort_order' => 1,
                    'active' => $request->boolean('active'),
                ]);

                foreach (['background_image', 'mobile_background_image', 'background_video'] as $fileField) {
                    if ($request->boolean($fileField.'_remove') && $banner->{$fileField}) {
                        $filesToDelete[] = $banner->{$fileField};
                        $banner->{$fileField} = null;
                    }

                    if ($request->hasFile($fileField)) {
                        if ($banner->{$fileField}) {
                            $filesToDelete[] = $banner->{$fileField};
                        }

                        $folder = $fileField === 'background_video' ? 'feature-banners/videos' : 'feature-banners';
                        $path = $request->file($fileField)->store($folder, 'public');
                        $uploadedFiles[] = $path;
                        $banner->{$fileField} = $path;
                    }
                }

                $banner->save();

                FeatureBanner::query()
                    ->where('section_key', $section->section_key)
                    ->whereKeyNot($banner->id)
                    ->update(['active' => false]);
            });
        } catch (\Throwable $exception) {
            foreach ($uploadedFiles as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        foreach (array_unique(array_filter($filesToDelete)) as $path) {
            if (! $this->homepageMediaIsUsedElsewhere($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        return back()->with('status', 'Video banner section updated.');
    }

    private function resolveMixedSource(array $data): Category|Collection|null
    {
        if (($data['source_type'] ?? null) === 'category' && ! empty($data['category_id'])) {
            return Category::query()->find($data['category_id']);
        }

        if (($data['source_type'] ?? null) === 'collection' && ! empty($data['collection_id'])) {
            return Collection::query()->find($data['collection_id']);
        }

        return null;
    }

    private function resolveFeaturedSource(array $data): Category|Collection|null
    {
        if (($data['source_type'] ?? null) === 'category' && ! empty($data['category_id'])) {
            return Category::query()->find($data['category_id']);
        }

        if (($data['source_type'] ?? null) === 'collection' && ! empty($data['collection_id'])) {
            return Collection::query()->find($data['collection_id']);
        }

        return null;
    }

    private function countProductsForSource(ShowcaseSection $section): int
    {
        if ($section->source_type === 'category') {
            $category = Category::query()
                ->where('active', true)
                ->where(function ($query) use ($section): void {
                    $query->when($section->source_id, fn ($inner) => $inner->orWhere('id', $section->source_id))
                        ->when($section->source_slug, fn ($inner) => $inner->orWhere('slug', $section->source_slug));
                })
                ->first();

            if (! $category) {
                return 0;
            }

            return Product::query()
                ->where('active', true)
                ->where(function ($query) use ($category): void {
                    $query->where('category_id', $category->id)
                        ->orWhereHas('categories', fn ($inner) => $inner
                            ->where('categories.id', $category->id)
                            ->where('category_product.active', true));
                })
                ->count();
        }

        if ($section->source_type === 'collection') {
            $collection = Collection::query()
                ->where('active', true)
                ->where(function ($query) use ($section): void {
                    $query->when($section->source_id, fn ($inner) => $inner->orWhere('id', $section->source_id))
                        ->when($section->source_slug, fn ($inner) => $inner->orWhere('slug', $section->source_slug));
                })
                ->first();

            if (! $collection) {
                return 0;
            }

            return $collection->products()
                ->where('products.active', true)
                ->wherePivot('active', true)
                ->count();
        }

        return $section->products()
            ->where('products.active', true)
            ->wherePivot('active', true)
            ->count();
    }

    private function queuePromoCardFilesForDeletion(PromoCard $card, array &$filesToDelete): void
    {
        foreach (['background_image', 'mobile_background_image', 'background_video'] as $field) {
            if ($card->{$field}) {
                $filesToDelete[] = $card->{$field};
            }
        }
    }

    private function uploadValidationMessages(): array
    {
        return [
            'background_image.max' => 'File size is too large. Maximum image size is 8 MB.',
            'mobile_background_image.max' => 'File size is too large. Maximum image size is 8 MB.',
            'banner_image.max' => 'File size is too large. Maximum image size is 8 MB.',
            'mobile_banner_image.max' => 'File size is too large. Maximum image size is 8 MB.',
            'background_video.max' => 'File size is too large. Maximum video size is 50 MB. Please upload an optimized MP4, WebM, or Ogg video.',
            'banner.background_image.max' => 'File size is too large. Maximum image size is 8 MB.',
            'banner.mobile_background_image.max' => 'File size is too large. Maximum image size is 8 MB.',
            'banner.background_video.max' => 'File size is too large. Maximum video size is 50 MB. Please upload an optimized MP4, WebM, or Ogg video.',
            'cards.*.image.max' => 'File size is too large. Maximum image size is 8 MB.',
            'cards.*.background_image.max' => 'File size is too large. Maximum image size is 8 MB.',
            'cards.*.mobile_background_image.max' => 'File size is too large. Maximum image size is 8 MB.',
            'cards.*.background_video.max' => 'File size is too large. Maximum video size is 50 MB. Please upload an optimized MP4, WebM, or Ogg video.',
        ];
    }

    private function promoImageIsUsedElsewhere(string $path): bool
    {
        return PromoCard::query()
            ->where(function ($query) use ($path): void {
                $query->where('background_image', $path)
                    ->orWhere('mobile_background_image', $path)
                    ->orWhere('background_video', $path);
            })
            ->exists();
    }

    private function showcaseFileIsUsedElsewhere(string $path): bool
    {
        return ShowcaseSection::query()
            ->where(function ($query) use ($path): void {
                $query->where('banner_image', $path)
                    ->orWhere('mobile_banner_image', $path)
                    ->orWhere('background_video', $path);
            })
            ->exists();
    }

    private function homepageMediaIsUsedElsewhere(string $path): bool
    {
        return $this->showcaseFileIsUsedElsewhere($path)
            || PromoCard::query()
                ->where(fn ($query) => $query
                    ->where('background_image', $path)
                    ->orWhere('mobile_background_image', $path)
                    ->orWhere('background_video', $path))
                ->exists()
            || NavigationCard::query()->where('image', $path)->exists()
            || FeatureBanner::query()
                ->where(fn ($query) => $query
                    ->where('background_image', $path)
                    ->orWhere('mobile_background_image', $path)
                    ->orWhere('background_video', $path))
                ->exists()
            || HeroBanner::query()
                ->where(fn ($query) => $query
                    ->where('background_image', $path)
                    ->orWhere('mobile_background_image', $path))
                ->exists();
    }
}
