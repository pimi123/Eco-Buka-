<?php

namespace App\Models;

use App\Models\Concerns\HasPublicImageUrls;
use Illuminate\Database\Eloquent\Model;

class NavigationCard extends Model
{
    use HasPublicImageUrls;

    protected $fillable = [
        'section_key',
        'title',
        'link',
        'image',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->publicUrl($this->image);
    }
}
