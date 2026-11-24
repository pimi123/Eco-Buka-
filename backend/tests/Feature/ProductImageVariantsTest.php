<?php

namespace Tests\Feature;

use App\Services\ProductImageVariants;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageVariantsTest extends TestCase
{
    public function test_thumbnails_preserve_original_and_are_reused(): void
    {
        Storage::fake('public');
        $image = imagecreatetruecolor(1000, 600);
        ob_start();
        imagepng($image);
        $original = ob_get_clean();
        imagedestroy($image);
        Storage::disk('public')->put('products/test.png', $original);
        $variants = new ProductImageVariants;
        $this->assertNull($variants->srcset('products/test.png'));
        $this->assertSame(3, $variants->generate('products/test.png'));
        $this->assertSame(0, $variants->generate('products/test.png'));
        $this->assertStringContainsString('320w', $variants->srcset('products/test.png'));
        $this->assertStringContainsString('960w', $variants->srcset('products/test.png'));
        $this->assertSame($original, Storage::disk('public')->get('products/test.png'));
        foreach (Storage::disk('public')->files('product-thumbnails') as $file) {
            $size = getimagesizefromstring(Storage::disk('public')->get($file));
            $width = (int) substr(pathinfo($file, PATHINFO_FILENAME), -3);
            $this->assertSame($width, $size[0]);
            $this->assertSame((int) round($width * 0.6), $size[1]);
        }
    }

    public function test_external_and_missing_images_do_not_trigger_remote_fetches(): void
    {
        Storage::fake('public');
        $variants = new ProductImageVariants;
        foreach ([null, '/promo/image.jpg', 'https://example.com/image.jpg', '../private.png', 'missing.png'] as $source) {
            $this->assertNull($variants->srcset($source));
            $this->assertSame(0, $variants->generate($source));
        }
    }
}
