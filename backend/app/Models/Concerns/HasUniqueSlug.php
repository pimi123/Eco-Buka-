<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::saving(function ($model): void {
            if (! empty($model->slug)) {
                $model->slug = Str::slug($model->slug);
                return;
            }

            $source = $model->name ?? $model->title ?? 'item';
            $base = Str::slug($source);
            $slug = $base;
            $counter = 2;

            while (static::where('slug', $slug)->whereKeyNot($model->getKey())->exists()) {
                $slug = "{$base}-{$counter}";
                $counter++;
            }

            $model->slug = $slug;
        });
    }
}
