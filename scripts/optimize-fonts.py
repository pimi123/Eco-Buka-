"""Generate WOFF2 from the existing fonts. Requires fonttools and brotli."""
from pathlib import Path
import sys

root = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(root / '.seo-tools'))
from fontTools.ttLib import TTFont

for source in (root / 'src/assets/fonts').glob('*.otf'):
    font = TTFont(source)
    font.flavor = 'woff2'
    destination = source.with_suffix('.woff2')
    font.save(destination)
    font.close()
    print(f'{source.name}: {source.stat().st_size} -> {destination.stat().st_size} bytes')
