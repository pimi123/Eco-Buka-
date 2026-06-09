<?php

namespace App\Models;

use App\Models\Concerns\HasPublicImageUrls;
use Illuminate\Database\Eloquent\Model;

class FeatureBanner extends Model
{
    use HasPublicImageUrls;

    protected $fillable = [
        'section_key',
        'section_heading',
        'eyebrow',
        'title',
        'subtitle',
        'price_text',
        'button_text',
        'button_link',
        'background_video',
        'background_image',
        'mobile_background_image',
        'text_color',
        'text_alignment',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['background_video_url', 'background_image_url', 'mobile_background_image_url'];

    public function getBackgroundVideoUrlAttribute(): ?string
    {
        return $this->publicUrl($this->background_video);
    }

    public function getBackgroundImageUrlAttribute(): ?string
    {
        return $this->publicUrl($this->background_image);
    }

    public function getMobileBackgroundImageUrlAttribute(): ?string
    {
        return $this->publicUrl($this->mobile_background_image);
    }
}
