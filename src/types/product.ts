export interface Product {
  id: string;
  category_id?: string | null;
  name: string;
  slug: string;
  category?: string;
  short_description?: string | null;
  description?: string | null;
  price?: number | null;
  old_price?: number | null;
  image_url?: string | null;
  gallery?: string[];
  specs?: Record<string, string>;
  badge?: string | null;
  featured: boolean;
  active: boolean;
  sort_order?: number | null;
  created_at?: string;
  updated_at?: string;
}
