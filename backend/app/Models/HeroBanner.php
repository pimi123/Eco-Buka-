<?php

namespace App\Models;

use App\Models\Concerns\HasPublicImageUrls;
use Illuminate\Database\Eloquent\Model;

class HeroBanner extends Model
{
    use HasPublicImageUrls;

    protected $fillable = [
        'eyebrow',
        'title',
        'subtitle',
        'button_text',
        'button_link',
        'second_button_text',
        'second_button_link',
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

    protected $appends = ['background_image_url', 'mobile_background_image_url'];

    public function getBackgroundImageUrlAttribute(): ?string
    {
        return $this->publicUrl($this->background_image);
    }

    public function getMobileBackgroundImageUrlAttribute(): ?string
    {
        return $this->publicUrl($this->mobile_background_image);
    }
}
