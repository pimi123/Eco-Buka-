const optimizedPromoImages: Record<string, { mobile: string; desktop: string }> = {
  '/promo/delta-classic.png': {
    mobile: '/promo/optimized/delta-classic-768.jpg',
    desktop: '/promo/optimized/delta-classic-1280.jpg',
  },
  '/promo/delta-max-series.png': {
    mobile: '/promo/optimized/delta-max-series-768.jpg',
    desktop: '/promo/optimized/delta-max-series-1280.jpg',
  },
  '/promo/summer-sale.png': {
    mobile: '/promo/optimized/summer-sale-768.jpg',
    desktop: '/promo/optimized/summer-sale-1280.jpg',
  },
};

export function optimizedImageUrl(url?: string | null, size: 'mobile' | 'desktop' = 'desktop') {
  if (!url) return url;
  const pathname = url.startsWith('http') ? new URL(url).pathname : url;
  const optimized = optimizedPromoImages[pathname]?.[size];
  if (optimized) return optimized.replace(/\.jpg$/, '.webp');
  if (/^\/promo\/optimized\/(delta-classic|delta-max-series|summer-sale)-(768|1280)\.jpg$/.test(pathname)) return pathname.replace(/\.jpg$/, '.webp');
  return url;
}

export function responsiveImageSet(url?: string | null) {
  const image = optimizedImageUrl(url);
  if (!image) return undefined;
  const match = image.match(/^(\/promo\/optimized\/(?:delta-classic|delta-max-series|summer-sale))-(?:768|1280)\.webp$/);
  return match ? `${match[1]}-768.webp 768w, ${match[1]}-1280.webp 1280w` : undefined;
}
