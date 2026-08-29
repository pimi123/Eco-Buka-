<?php

namespace App\Models;

use App\Models\Concerns\HasPublicImageUrls;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasPublicImageUrls;
    use HasUniqueSlug;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'old_price',
        'badge',
        'in_stock',
        'main_image',
        'gallery_images',
        'specs',
        'included_items',
        'downloads',
        'featured',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'gallery_images' => 'array',
        'specs' => 'array',
        'included_items' => 'array',
        'downloads' => 'array',
        'in_stock' => 'boolean',
        'featured' => 'boolean',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['main_image_url', 'image_url', 'gallery_image_urls'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withPivot(['sort_order', 'active'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class)
            ->withPivot(['sort_order', 'active'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function showcaseSections(): BelongsToMany
    {
        return $this->belongsToMany(ShowcaseSection::class, 'showcase_section_products')
            ->withPivot(['sort_order', 'active'])
            ->withTimestamps();
    }

    public function getMainImageUrlAttribute(): ?string
    {
        return $this->publicUrl($this->main_image);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->main_image_url;
    }

    public function getGalleryImageUrlsAttribute(): array
    {
        return collect($this->gallery_images ?? [])
            ->map(fn (?string $path) => $this->publicUrl($path))
            ->filter()
            ->values()
            ->all();
    }
}
