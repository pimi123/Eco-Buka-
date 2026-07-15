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
  return optimizedPromoImages[pathname]?.[size] || url;
}
