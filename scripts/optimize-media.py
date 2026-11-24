"""Regenerate local WebP assets with Pillow; original source images are retained."""
from pathlib import Path
from PIL import Image, ImageOps

root = Path(__file__).resolve().parents[1]
sources = list((root / 'public/promo/optimized').glob('*.jpg'))
sources += list((root / 'public/images/powerocean').glob('*.jpg'))
sources += [root / 'public/images/payments/star-card.png']
for source in sources:
    with Image.open(source) as image:
        image = ImageOps.exif_transpose(image).convert('RGB')
        image.thumbnail((1600, 1600) if source.stem != 'star-card' else (240, 240))
        destination = source.with_suffix('.webp')
        image.save(destination, 'WEBP', quality=82, method=6)
        print(f'{source.name}: {source.stat().st_size} -> {destination.stat().st_size} bytes')

destination = root / 'public/images/social/eco-buka.jpg'
destination.parent.mkdir(parents=True, exist_ok=True)
with Image.open(root / 'src/assets/heroes/hero-portable-energy.png') as image:
    image = ImageOps.fit(ImageOps.exif_transpose(image).convert('RGB'), (1200, 630))
    image.save(destination, 'JPEG', quality=85, optimize=True)
print(f'Social image: {destination.stat().st_size} bytes')
