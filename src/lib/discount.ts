export function discountPercent(price?: number | null, oldPrice?: number | null): number {
  const current = Number(price);
  const previous = Number(oldPrice);
  if (!Number.isFinite(current) || !Number.isFinite(previous) || current <= 0 || previous <= current) return 0;
  return Math.floor(((previous - current) / previous) * 100);
}
export function discountsFirst<T extends { price?: number | null; old_price?: number | null }>(products: T[]): T[] {
  const discounted = (p: T) => Number(p.price) > 0 && Number(p.old_price) > Number(p.price);
  return [...products].sort((a, b) => Number(discounted(b)) - Number(discounted(a)));
}

export function isProductInStock(product: { in_stock?: unknown }): boolean {
  return product.in_stock !== false && product.in_stock !== 0 && product.in_stock !== '0';
}

