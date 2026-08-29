import type { Product } from './product';

export interface Collection {
  id: string;
  name: string;
  slug: string;
  type: 'solution' | 'campaign' | 'merchandising' | 'featured' | string;
  description?: string | null;
  image_url?: string | null;
  active: boolean;
  sort_order?: number | null;
  products?: Product[];
  created_at?: string;
  updated_at?: string;
}
