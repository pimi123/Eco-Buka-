export interface Category {
  id: string;
  name: string;
  slug: string;
  description?: string | null;
  image_url?: string | null;
  is_new?: boolean;
  active: boolean;
  created_at?: string;
}
