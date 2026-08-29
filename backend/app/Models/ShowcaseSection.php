<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Concerns\HasPublicImageUrls;

class ShowcaseSection extends Model
{
    use HasPublicImageUrls;

    protected $fillable = [
        'section_key',
        'title',
        'subtitle',
        'eyebrow',
        'banner_title',
        'banner_subtitle',
        'section_type',
        'source_type',
        'source_id',
        'source_slug',
        'display_limit',
        'layout_variant',
        'banner_image',
        'mobile_banner_image',
        'button_text',
        'button_link',
        'background_video',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
        'source_id' => 'integer',
        'display_limit' => 'integer',
    ];

    protected $appends = ['banner_image_url', 'mobile_banner_image_url', 'background_video_url'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'showcase_section_products')
            ->withPivot(['sort_order', 'active'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function promoCards(): HasMany
    {
        return $this->hasMany(PromoCard::class, 'homepage_section_id')->orderBy('sort_order');
    }

    public function getBannerImageUrlAttribute(): ?string
    {
        return $this->publicUrl($this->banner_image);
    }

    public function getMobileBannerImageUrlAttribute(): ?string
    {
        return $this->publicUrl($this->mobile_banner_image);
    }

    public function getBackgroundVideoUrlAttribute(): ?string
    {
        return $this->publicUrl($this->background_video);
    }
}
