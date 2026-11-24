import type { Product } from '../types/product';

export function productStructuredData(product: Product, origin: string): Record<string, unknown> {
  const url = new URL(`/products/${encodeURIComponent(product.slug)}`, origin).href;
  const images = [product.image_url, ...(product.gallery || [])].filter(Boolean).map(image => new URL(image!, origin).href);
  const price = Number(product.price);
  return {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'Product', '@id': `${url}#product`, url,
        name: product.name, description: product.short_description || product.description,
        sku: String(product.id), image: images,
        ...(product.price != null && Number.isFinite(price) && price > 0 ? {
          offers: { '@type': 'Offer', url, price, priceCurrency: 'EUR',
            availability: `https://schema.org/${product.in_stock ? 'InStock' : 'OutOfStock'}`,
            seller: { '@type': 'Organization', name: 'EcoBuka', url: origin },
          },
        } : {}),
      },
      { '@type': 'BreadcrumbList', itemListElement: [
        { '@type': 'ListItem', position: 1, name: 'Ballina', item: new URL('/', origin).href },
        { '@type': 'ListItem', position: 2, name: 'Produktet', item: new URL('/products', origin).href },
        { '@type': 'ListItem', position: 3, name: product.name, item: url },
      ] },
    ],
  };
}
