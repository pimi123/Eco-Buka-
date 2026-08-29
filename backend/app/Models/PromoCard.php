<?php

namespace App\Models;

use App\Models\Concerns\HasPublicImageUrls;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoCard extends Model
{
    use HasPublicImageUrls;

    protected $fillable = [
        'homepage_section_id',
        'section_key',
        'label',
        'title',
        'subtitle',
        'button_text',
        'button_link',
        'category_slug',
        'background_image',
        'mobile_background_image',
        'background_video',
        'text_color',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'homepage_section_id' => 'integer',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['background_image_url', 'mobile_background_image_url', 'background_video_url'];

    public function homepageSection(): BelongsTo
    {
        return $this->belongsTo(ShowcaseSection::class, 'homepage_section_id');
    }

    public function getBackgroundImageUrlAttribute(): ?string
    {
        return $this->publicUrl($this->background_image);
    }

    public function getMobileBackgroundImageUrlAttribute(): ?string
    {
        return $this->publicUrl($this->mobile_background_image);
    }

    public function getBackgroundVideoUrlAttribute(): ?string
    {
        return $this->publicUrl($this->background_video);
    }
}
