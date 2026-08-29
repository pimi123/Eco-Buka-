<?php

namespace App\Models;

use App\Models\Concerns\HasPublicImageUrls;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    use HasPublicImageUrls;
    use HasUniqueSlug;

    public const TYPES = [
        'solution',
        'campaign',
        'merchandising',
        'featured',
    ];

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'image',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['sort_order', 'active'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->publicUrl($this->image);
    }
}
