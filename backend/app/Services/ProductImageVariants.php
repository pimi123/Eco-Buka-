<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ProductImageVariants
{
    public const WIDTHS = [320, 640, 960];

    private function prefix(?string $source): ?string
    {
        if (! $source || str_starts_with($source, '/') || str_contains($source, '://') || str_contains($source, '..')) {
            return null;
        }
        $disk = Storage::disk('public');
        if (! $disk->exists($source)) {
            return null;
        }

        return 'product-thumbnails/'.sha1($source.'|'.$disk->lastModified($source).'|'.$disk->size($source));
    }

    public function srcset(?string $source): ?string
    {
        $prefix = $this->prefix($source);
        if (! $prefix) {
            return null;
        }
        $disk = Storage::disk('public');
        $variants = [];
        foreach (self::WIDTHS as $width) {
            $file = $prefix.'-'.$width.'.webp';
            if ($disk->exists($file)) {
                $variants[] = url($disk->url($file)).' '.$width.'w';
            }
        }

        return $variants ? implode(', ', $variants) : null;
    }

    public function generate(?string $source): int
    {
        $prefix = $this->prefix($source);
        if (! $prefix) {
            return 0;
        }
        $disk = Storage::disk('public');
        $bytes = $disk->get($source);
        $size = @getimagesizefromstring($bytes);
        if (! $size || $size[0] * $size[1] > 40000000) {
            return 0;
        }
        $image = @imagecreatefromstring($bytes);
        if (! $image) {
            return 0;
        }
        $count = 0;
        try {
            foreach (self::WIDTHS as $width) {
                $file = $prefix.'-'.$width.'.webp';
                if ($width > $size[0] || $disk->exists($file)) {
                    continue;
                }
                $height = max(1, (int) round($size[1] * $width / $size[0]));
                $thumbnail = imagecreatetruecolor($width, $height);
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                imagefilledrectangle($thumbnail, 0, 0, $width, $height, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
                imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
                ob_start();
                imagewebp($thumbnail, null, 82);
                $output = ob_get_clean();
                imagedestroy($thumbnail);
                if (! $output || ! $disk->put($file, $output)) {
                    throw new \RuntimeException('Could not write thumbnail: '.$file);
                }
                $count++;
            }
        } finally {
            imagedestroy($image);
        }

        return $count;
    }
}
