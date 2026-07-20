<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FeatureBanner;
use App\Models\HeroBanner;
use App\Models\NavigationCard;
use App\Models\Product;
use App\Models\PromoCard;
use App\Models\ShowcaseSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ContentController extends Controller
{
    public function index(string $resource)
    {
        $config = $this->config($resource);
        $items = $config['model']::query()
            ->when($resource === 'products', fn ($query) => $query->with('category'))
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
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request, string $resource)
    {
        $config = $this->config($resource);
        $data = $this->validatedData($request, $resource, $config);
        $item = $config['model']::create($data);
        $this->syncShowcaseProducts($request, $resource, $item);

        return redirect()->route('admin.content.edit', [$resource, $item])->with('status', "{$config['label']} saved.");
    }

    public function edit(string $resource, int $id)
    {
        $config = $this->config($resource);
        $item = $config['model']::findOrFail($id);
        if ($resource === 'showcase-sections') {
            $item->load('products');
        }

        return view('admin.content.form', [
            'resource' => $resource,
            'config' => $config,
            'item' => $item,
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, string $resource, int $id)
    {
        $config = $this->config($resource);
        $item = $config['model']::findOrFail($id);
        $item->update($this->validatedData($request, $resource, $config, $item));
        $this->syncShowcaseProducts($request, $resource, $item);

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
                'json' => ['nullable', 'string'],
                default => ['nullable', 'string'],
            };
        }

        $data = $request->validate($rules);

        foreach ($config['fields'] as $field => $type) {
            if ($type === 'boolean') {
                $data[$field] = $request->boolean($field);
            }

            if ($type === 'number') {
                $data[$field] = (int) ($data[$field] ?? 0);
            }

            if ($type === 'price' && ($data[$field] ?? null) === null) {
                $data[$field] = null;
            }

            if ($type === 'image' && $request->hasFile($field)) {
                $data[$field] = $this->storeOptimizedImage($request->file($field), $config['folder']);
            }

            if ($type === 'video' && $request->hasFile($field)) {
                $data[$field] = $request->file($field)->store($config['folder'].'/videos', 'public');
            }

            if ($type === 'gallery') {
                $existing = $item?->{$field} ?? [];
                $uploaded = collect($request->file($field, []))
                    ->map(fn (UploadedFile $file) => $this->storeOptimizedImage($file, $config['folder'].'/gallery'))
                    ->all();
                $data[$field] = array_values(array_filter([...$existing, ...$uploaded]));
            }

            if ($type === 'json') {
                $data[$field] = $this->parseStructuredText($request->input($field));
            }
        }

        return $data;
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
            return $file->store($folder, 'public');
        }

        $source = match ($file->getMimeType()) {
            'image/jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png' => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => @imagecreatefromwebp($file->getRealPath()),
            default => false,
        };

        if (! $source) {
            return $file->store($folder, 'public');
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

    private function options(): array
    {
        return [
            'categories' => Category::orderBy('name')->pluck('name', 'id'),
            'products' => Product::orderBy('name')->get(),
        ];
    }

    private function config(string $resource): array
    {
        $configs = [
            'categories' => [
                'label' => 'Category',
                'model' => Category::class,
                'table' => 'categories',
                'folder' => 'categories',
                'fields' => ['name' => 'required', 'slug' => 'slug', 'description' => 'textarea', 'image' => 'image', 'active' => 'boolean', 'sort_order' => 'number'],
            ],
            'products' => [
                'label' => 'Product',
                'model' => Product::class,
                'table' => 'products',
                'folder' => 'products',
                'fields' => ['category_id' => 'category', 'name' => 'required', 'slug' => 'slug', 'short_description' => 'textarea', 'description' => 'textarea', 'price' => 'price', 'old_price' => 'price', 'badge' => 'text', 'main_image' => 'image', 'gallery_images' => 'gallery', 'specs' => 'json', 'included_items' => 'json', 'downloads' => 'json', 'featured' => 'boolean', 'active' => 'boolean', 'sort_order' => 'number'],
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
                'fields' => ['section_key' => 'text', 'label' => 'text', 'title' => 'required', 'subtitle' => 'textarea', 'button_text' => 'text', 'button_link' => 'text', 'category_slug' => 'text', 'background_image' => 'image', 'mobile_background_image' => 'image', 'text_color' => 'text', 'active' => 'boolean', 'sort_order' => 'number'],
            ],
            'showcase-sections' => [
                'label' => 'Showcase Section',
                'model' => ShowcaseSection::class,
                'table' => 'showcase_sections',
                'folder' => 'showcase-sections',
                'fields' => ['section_key' => 'required', 'title' => 'text', 'subtitle' => 'textarea', 'active' => 'boolean', 'sort_order' => 'number'],
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

        abort_unless(isset($configs[$resource]), 404);

        return $configs[$resource];
    }
}
