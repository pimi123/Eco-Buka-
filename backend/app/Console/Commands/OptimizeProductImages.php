<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductImageVariants;
use Illuminate\Console\Command;

class OptimizeProductImages extends Command
{
    protected $signature = 'media:optimize-products';
    protected $description = 'Generate WebP product thumbnails without changing originals';

    public function handle(ProductImageVariants $variants): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('PHP GD with WebP support is required.');
            return self::FAILURE;
        }
        $count = 0;
        foreach (Product::where('active', true)->select(['id', 'main_image'])->lazyById() as $product) {
            $count += $variants->generate($product->main_image);
        }
        $this->info('Generated '.$count.' product thumbnails. Original images and product data are unchanged.');

        return self::SUCCESS;
    }
}
