<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\FeatureBanner;
use App\Models\HeroBanner;
use App\Models\NavigationCard;
use App\Models\Product;
use App\Models\PromoCard;
use App\Models\ShowcaseSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ContentController extends Controller
{
    private array $pendingDeletedFiles = [];
    private array $pendingUploadedFiles = [];

    public function index(string $resource)
    {
        $config = $this->config($resource);
        $items = $config['model']::query()
            ->when($resource === 'products', fn ($query) => $query->with(['category', 'categories', 'collections']))
            ->when($resource === 'collections', fn ($query) => $query->withCount('products'))
            ->when($resource === 'promo-cards', fn ($query) => $query->with('homepageSection'))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(20);

        return view('admin.content.index', compact('resource', 'config', 'items'));
    }

    public function create(string $resource)
    {
        $config = $this->config($resource);
        $item = new $config['model']();

        return view('admin.content.form', [
            'resource' => $resource,
            'config' => $config,
            'item' => $item,
            'options' => $this->options($resource),
        ]);
    }

    public function store(Request $request, string $resource)
    {
        $config = $this->config($resource);
        $this->pendingUploadedFiles = [];
        $data = $this->validatedData($request, $resource, $config);

        try {
            $item = DB::transaction(function () use ($request, $resource, $config, $data) {
                $item = $config['model']::create($data);
                $this->syncProductRelations($request, $resource, $item);
                $this->syncCollectionProducts($request, $resource, $item);
                $this->syncShowcaseProducts($request, $resource, $item);

                return $item;
            });
            $this->pendingUploadedFiles = [];
        } catch (\Throwable $exception) {
            $this->deleteUploadedFiles();
            throw $exception;
        }

        return redirect()->route('admin.content.edit', [$resource, $item])->with('status', "{$config['label']} saved.");
    }

    public function edit(string $resource, int $id)
    {
        $config = $this->config($resource);
        $item = $config['model']::findOrFail($id);
        if ($resource === 'showcase-sections') {
            $item->load('products');
        }
        if ($resource === 'products') {
            $item->load(['categories', 'collections']);
        }
        if ($resource === 'collections') {
            $item->load('products.category');
        }
        if ($resource === 'promo-cards') {
            $item->load('homepageSection');
        }

        return view('admin.content.form', [
            'resource' => $resource,
            'config' => $config,
            'item' => $item,
            'options' => $this->options($resource),
        ]);
    }

    public function productPicker(Request $request)
    {
        $query = trim((string) $request->query('query', ''));
        $selectedIds = collect(explode(',', (string) $request->query('selected', '')))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $products = Product::query()
            ->with('category:id,name,slug')
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($inner) use ($query): void {
                    $inner->where('name', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%")
                        ->orWhere('short_description', 'like', "%{$query}%");
                });
            })
            ->when($query === '' && $selectedIds->isNotEmpty(), fn ($builder) => $builder->whereIn('id', $selectedIds))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'category_id', 'name', 'slug', 'price', 'active'])
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'active' => $product->active,
                'category' => $product->category?->name,
            ]);

        return response()->json($products);
    }

    public function update(Request $request, string $resource, int $id)
    {
        $config = $this->config($resource);
        $item = $config['model']::findOrFail($id);
        $this->pendingDeletedFiles = [];
        $this->pendingUploadedFiles = [];
        $data = $this->validatedData($request, $resource, $config, $item);

        try {
            DB::transaction(function () use ($request, $resource, $item, $data): void {
                $item->update($data);
                $this->syncProductRelations($request, $resource, $item);
                $this->syncCollectionProducts($request, $resource, $item);
                $this->syncShowcaseProducts($request, $resource, $item);
            });
            $this->pendingUploadedFiles = [];
        } catch (\Throwable $exception) {
            $this->deleteUploadedFiles();
            throw $exception;
        }

        $this->deletePendingFiles();

        return back()->with('status', "{$config['label']} updated.");
    }

    public function destroy(string $resource, int $id)
    {
        $config = $this->config($resource);
        $config['model']::findOrFail($id)->delete();

        return redirect()->route('admin.content.index', $resource)->with('status', "{$config['label']} deleted.");
    }

    private function validatedData(Request $request, string $resource, array $config, ?Model $item = null): array
    {
        $rules = [];
        foreach ($config['fields'] as $field => $type) {
            $rules[$field] = match ($type) {
                'required' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:255', Rule::unique($item?->getTable() ?? $config['table'], 'slug')->ignore($item?->id)],
                'price' => ['nullable', 'numeric', 'min:0'],
                'number' => ['nullable', 'integer', 'min:0'],
                'boolean' => ['nullable', 'boolean'],
                'image' => ['nullable', 'image', 'max:8192'],
                'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:51200'],
                'gallery' => ['nullable', 'array'],
                'category' => ['nullable', 'integer', 'exists:categories,id'],
                'category_multi' => ['nullable', 'array'],
                'collection_multi' => ['nullable', 'array'],
                'product_multi' => ['nullable', 'array'],
                'homepage_section' => ['nullable', 'integer', 'exists:showcase_sections,id'],
                'collection_type' => ['nullable', Rule::in(Collection::TYPES)],
                'section_type' => ['nullable', Rule::in(['product_grid', 'product_carousel', 'promo_cards', 'category_carousel', 'hero_banner', 'video_banner', 'mixed_showcase'])],
                'source_type' => ['nullable', Rule::in(['manual_products', 'category', 'collection', 'manual_cards'])],
                'json' => ['nullable', 'string'],
                default => ['nullable', 'string'],
            };

            if ($type === 'category_multi') {
                $rules[$field.'.*'] = ['integer', 'exists:categories,id'];
            }

            if ($type === 'collection_multi') {
                $rules[$field.'.*'] = ['integer', 'exists:collections,id'];
            }

            if ($type === 'product_multi') {
                $rules[$field.'.*'] = ['integer', 'exists:products,id'];
            }

            if ($type === 'gallery') {
                $rules[$field.'.*'] = ['image', 'max:8192'];
                $rules[$field.'_existing'] = ['nullable', 'array'];
                $rules[$field.'_existing.*'] = ['string'];
                $rules[$field.'_remove'] = ['nullable', 'array'];
                $rules[$field.'_remove.*'] = ['string'];
            }

            if ($type === 'image') {
                $rules[$field.'_remove'] = ['nullable', 'boolean'];
            }

            if ($type === 'video') {
                $rules[$field.'_remove'] = ['nullable', 'boolean'];
            }
        }

        $data = $request->validate($rules);

        foreach ($config['fields'] as $field => $type) {
            if ($type === 'boolean') {
                $data[$field] = $request->boolean($field);
            }

            if ($type === 'number') {
                if ($field === 'source_id' && (($data[$field] ?? null) === null || ($data[$field] ?? null) === '')) {
                    $data[$field] = null;
                } else {
                    $data[$field] = (int) ($data[$field] ?? 0);
                }
            }

            if ($type === 'price' && (($data[$field] ?? null) === null || ($data[$field] ?? null) === '')) {
                $data[$field] = null;
            }

            if ($type === 'category' && (($data[$field] ?? null) === null || ($data[$field] ?? null) === '')) {
                $data[$field] = null;
            }

            if ($type === 'homepage_section') {
                $sectionId = $data[$field] ?? null;
                $data[$field] = $sectionId ?: null;

                if ($resource === 'promo-cards' && $sectionId) {
                    $section = ShowcaseSection::query()->find($sectionId);
                    if ($section) {
                        $data['section_key'] = $section->section_key;
                    }
                }
            }

            if (in_array($type, ['category_multi', 'collection_multi', 'product_multi'], true)) {
                unset($data[$field]);
            }

            if ($type === 'image') {
                $currentImage = $item?->{$field};
                $removeImage = $request->boolean($field.'_remove');

                if ($request->hasFile($field)) {
                    $data[$field] = $this->storeOptimizedImage($request->file($field), $config['folder']);
                    if ($currentImage) {
                        $this->pendingDeletedFiles[] = $currentImage;
                    }
                } elseif ($removeImage) {
                    $data[$field] = null;
                    if ($currentImage) {
                        $this->pendingDeletedFiles[] = $currentImage;
                    }
                }

                unset($data[$field.'_remove']);
            }

            if ($type === 'video') {
                $currentVideo = $item?->{$field};
                $removeVideo = $request->boolean($field.'_remove');

                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)->store($config['folder'].'/videos', 'public');
                    $this->pendingUploadedFiles[] = $data[$field];

                    if ($currentVideo) {
                        $this->pendingDeletedFiles[] = $currentVideo;
                    }
                } elseif ($removeVideo) {
                    $data[$field] = null;

                    if ($currentVideo) {
                        $this->pendingDeletedFiles[] = $currentVideo;
                    }
                }

                unset($data[$field.'_remove']);
            }

            if ($type === 'gallery') {
                $existing = collect($request->input($field.'_existing', $item?->{$field} ?? []))
                    ->filter(fn ($path) => is_string($path) && $path !== '')
                    ->values();
                $remove = collect($request->input($field.'_remove', []))
                    ->filter(fn ($path) => is_string($path) && $path !== '')
                    ->values();
                $kept = $existing
                    ->reject(fn (string $path) => $remove->contains($path))
                    ->values()
                    ->all();
                $uploaded = collect($request->file($field, []))
                    ->map(fn (UploadedFile $file) => $this->storeOptimizedImage($file, $config['folder'].'/gallery'))
                    ->all();
                $data[$field] = array_values(array_filter([...$kept, ...$uploaded]));
                $this->pendingDeletedFiles = [
                    ...$this->pendingDeletedFiles,
                    ...$remove->intersect($existing)->diff($kept)->values()->all(),
                ];
                unset($data[$field.'_existing'], $data[$field.'_remove']);
            }

            if ($type === 'json') {
                $data[$field] = $this->parseStructuredText($request->input($field));
            }
        }

        return $data;
    }

    private function deletePendingFiles(): void
    {
        foreach (array_unique($this->pendingDeletedFiles) as $path) {
            if (! $this->isPublicFileUsed($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $this->pendingDeletedFiles = [];
    }

    private function deleteUploadedFiles(): void
    {
        foreach (array_unique($this->pendingUploadedFiles) as $path) {
            Storage::disk('public')->delete($path);
        }

        $this->pendingUploadedFiles = [];
    }

    private function isPublicFileUsed(string $path): bool
    {
        foreach ($this->configs() as $config) {
            foreach ($config['fields'] as $field => $type) {
                if (in_array($type, ['image', 'video'], true)) {
                    if ($config['model']::query()->where($field, $path)->exists()) {
                        return true;
                    }
                }

                if ($type === 'gallery') {
                    if ($config['model']::query()->whereJsonContains($field, $path)->exists()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function parseStructuredText(?string $value): array
    {
        $value = trim((string) $value);

        if ($value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $specs = [];
        foreach (preg_split('/\r\n|\r|\n/', $value) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (str_contains($line, ':')) {
                [$key, $specValue] = array_map('trim', explode(':', $line, 2));
                if ($key !== '' && $specValue !== '') {
                    $specs[$key] = $specValue;
                    continue;
                }
            }

            $specs[] = $line;
        }

        return $specs;
    }

    private function storeOptimizedImage(UploadedFile $file, string $folder): string
    {
        if (! function_exists('imagewebp')) {
            $path = $file->store($folder, 'public');
            $this->pendingUploadedFiles[] = $path;

            return $path;
        }

        $source = match ($file->getMimeType()) {
            'image/jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png' => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => @imagecreatefromwebp($file->getRealPath()),
            default => false,
        };

        if (! $source) {
            $path = $file->store($folder, 'public');
            $this->pendingUploadedFiles[] = $path;

            return $path;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $maxWidth = 1800;
        $ratio = min(1, $maxWidth / max(1, $sourceWidth));
        $targetWidth = max(1, (int) round($sourceWidth * $ratio));
        $targetHeight = max(1, (int) round($sourceHeight * $ratio));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        $filename = trim($folder, '/').'/'.uniqid('image_', true).'.webp';
        $tempPath = tempnam(sys_get_temp_dir(), 'eco-buka-image');
        imagewebp($target, $tempPath, 78);
        Storage::disk('public')->put($filename, file_get_contents($tempPath));
        $this->pendingUploadedFiles[] = $filename;

        @unlink($tempPath);
        imagedestroy($source);
        imagedestroy($target);

        return $filename;
    }

    private function syncShowcaseProducts(Request $request, string $resource, Model $item): void
    {
        if ($resource !== 'showcase-sections') {
            return;
        }

        $sync = [];
        foreach ($request->input('products', []) as $productId => $settings) {
            $sync[$productId] = [
                'sort_order' => (int) ($settings['sort_order'] ?? 0),
                'active' => isset($settings['active']),
            ];
        }

        $item->products()->sync($sync);
    }

    private function syncProductRelations(Request $request, string $resource, Model $item): void
    {
        if ($resource !== 'products') {
            return;
        }

        $categoryIds = collect($request->input('category_ids', []))
            ->push($item->category_id)
            ->filter()
            ->unique()
            ->values();

        $categorySync = [];
        foreach ($categoryIds as $index => $categoryId) {
            $categorySync[$categoryId] = ['sort_order' => $index + 1, 'active' => true];
        }

        $collectionSync = [];
        foreach (collect($request->input('collection_ids', []))->filter()->unique()->values() as $index => $collectionId) {
            $collectionSync[$collectionId] = ['sort_order' => $index + 1, 'active' => true];
        }

        $item->categories()->sync($categorySync);
        $item->collections()->sync($collectionSync);
    }

    private function syncCollectionProducts(Request $request, string $resource, Model $item): void
    {
        if ($resource !== 'collections') {
            return;
        }

        $sync = [];
        foreach (collect($request->input('product_ids', []))->filter()->unique()->values() as $index => $productId) {
            $sync[$productId] = ['sort_order' => $index + 1, 'active' => true];
        }

        $item->products()->sync($sync);
    }

    private function options(string $resource): array
    {
        $options = [
            'categories' => Category::orderBy('name')->pluck('name', 'id'),
            'collections' => Collection::orderBy('type')->orderBy('name')->get(),
            'homepageSections' => ShowcaseSection::orderBy('sort_order')->orderBy('title')->get(),
        ];

        if ($resource === 'showcase-sections') {
            $options['products'] = Product::orderBy('name')->get();
        }

        return $options;
    }

    private function config(string $resource): array
    {
        $configs = $this->configs();

        abort_unless(isset($configs[$resource]), 404);

        return $configs[$resource];
    }

    private function configs(): array
    {
        return [
            'categories' => [
                'label' => 'Category',
                'model' => Category::class,
                'table' => 'categories',
                'folder' => 'categories',
                'description' => 'Technical product grouping: what the product is, such as Power Stations, Solar Panels, DELTA Series, or Accessories.',
                'fields' => ['name' => 'required', 'slug' => 'slug', 'description' => 'textarea', 'image' => 'image', 'active' => 'boolean', 'sort_order' => 'number'],
            ],
            'collections' => [
                'label' => 'Collection',
                'model' => Collection::class,
                'table' => 'collections',
                'folder' => 'collections',
                'description' => 'Flexible marketing or solution group: Home Backup, Summer Sale, New Products, Popular Products, or Business Solutions.',
                'fields' => ['name' => 'required', 'slug' => 'slug', 'type' => 'collection_type', 'description' => 'textarea', 'image' => 'image', 'product_ids' => 'product_multi', 'active' => 'boolean', 'sort_order' => 'number'],
            ],
            'products' => [
                'label' => 'Product',
                'model' => Product::class,
                'table' => 'products',
                'folder' => 'products',
                'description' => 'The sellable item. Assign one primary category, optional extra technical categories, and any collections/campaigns where this product should appear.',
                'fields' => ['category_id' => 'category', 'category_ids' => 'category_multi', 'collection_ids' => 'collection_multi', 'name' => 'required', 'slug' => 'slug', 'short_description' => 'textarea', 'description' => 'textarea', 'price' => 'price', 'old_price' => 'price', 'badge' => 'text', 'main_image' => 'image', 'gallery_images' => 'gallery', 'specs' => 'json', 'included_items' => 'json', 'downloads' => 'json', 'featured' => 'boolean', 'active' => 'boolean', 'sort_order' => 'number'],
            ],
            'hero-banners' => [
                'label' => 'Hero Banner',
                'model' => HeroBanner::class,
                'table' => 'hero_banners',
                'folder' => 'hero-banners',
                'fields' => ['eyebrow' => 'text', 'title' => 'required', 'subtitle' => 'textarea', 'button_text' => 'text', 'button_link' => 'text', 'second_button_text' => 'text', 'second_button_link' => 'text', 'background_image' => 'image', 'mobile_background_image' => 'image', 'text_color' => 'text', 'text_alignment' => 'text', 'active' => 'boolean', 'sort_order' => 'number'],
            ],
                'promo-cards' => [
                'label' => 'Promo Card',
                'model' => PromoCard::class,
                'table' => 'promo_cards',
                'folder' => 'promo-cards',
                'description' => 'Marketing card, not necessarily a product. Use this for campaign cards, sale cards, or visual links into categories/collections.',
                'fields' => ['homepage_section_id' => 'homepage_section', 'label' => 'text', 'title' => 'required', 'subtitle' => 'textarea', 'button_text' => 'text', 'button_link' => 'text', 'category_slug' => 'text', 'background_image' => 'image', 'mobile_background_image' => 'image', 'background_video' => 'video', 'text_color' => 'text', 'active' => 'boolean', 'sort_order' => 'number'],
            ],
            'showcase-sections' => [
                'label' => 'Homepage Section',
                'model' => ShowcaseSection::class,
                'table' => 'showcase_sections',
                'folder' => 'showcase-sections',
                'description' => 'Homepage display block. Choose how it should look and whether products come from manual selection, a category, or a collection.',
                'fields' => ['section_key' => 'required', 'title' => 'text', 'subtitle' => 'textarea', 'section_type' => 'section_type', 'source_type' => 'source_type', 'source_id' => 'number', 'source_slug' => 'text', 'display_limit' => 'number', 'layout_variant' => 'text', 'banner_image' => 'image', 'mobile_banner_image' => 'image', 'button_text' => 'text', 'button_link' => 'text', 'active' => 'boolean', 'sort_order' => 'number'],
            ],
            'navigation-cards' => [
                'label' => 'Navigation Card',
                'model' => NavigationCard::class,
                'table' => 'navigation_cards',
                'folder' => 'navigation-cards',
                'fields' => ['section_key' => 'text', 'title' => 'required', 'link' => 'text', 'image' => 'image', 'active' => 'boolean', 'sort_order' => 'number'],
            ],
            'feature-banners' => [
                'label' => 'Feature Banner',
                'model' => FeatureBanner::class,
                'table' => 'feature_banners',
                'folder' => 'feature-banners',
                'fields' => ['section_key' => 'text', 'section_heading' => 'text', 'eyebrow' => 'text', 'title' => 'required', 'subtitle' => 'textarea', 'price_text' => 'text', 'button_text' => 'text', 'button_link' => 'text', 'background_video' => 'video', 'background_image' => 'image', 'mobile_background_image' => 'image', 'text_color' => 'text', 'text_alignment' => 'text', 'active' => 'boolean', 'sort_order' => 'number'],
            ],
        ];
    }
}
