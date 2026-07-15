<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasPublicImageUrls
{
    protected function publicUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $this->optimizedPublicAsset($path) ?? $path;
        }

        return url(Storage::disk('public')->url($path));
    }

    protected function optimizedPublicAsset(string $path): ?string
    {
        $optimized = [
            '/promo/delta-classic.png' => '/promo/optimized/delta-classic-1280.jpg',
            '/promo/delta-max-series.png' => '/promo/optimized/delta-max-series-1280.jpg',
            '/promo/summer-sale.png' => '/promo/optimized/summer-sale-1280.jpg',
        ];

        return $optimized[$path] ?? null;
    }
}
