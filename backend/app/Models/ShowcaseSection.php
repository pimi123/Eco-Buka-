<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShowcaseSection extends Model
{
    protected $fillable = [
        'section_key',
        'title',
        'subtitle',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'showcase_section_products')
            ->withPivot(['sort_order', 'active'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}
