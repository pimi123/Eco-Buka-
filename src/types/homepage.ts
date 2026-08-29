import type { Product } from './product';

export interface HomepageSection {
  id: string;
  section_key: string;
  title?: string | null;
  subtitle?: string | null;
  eyebrow?: string | null;
  banner_title?: string | null;
  banner_subtitle?: string | null;
  layout_type?: string | null;
  section_type?: string | null;
  source_type?: string | null;
  source_id?: string | number | null;
  source_slug?: string | null;
  display_limit?: number | null;
  layout_variant?: string | null;
  banner_image_url?: string | null;
  mobile_banner_image_url?: string | null;
  background_video_url?: string | null;
  button_text?: string | null;
  button_link?: string | null;
  active: boolean;
  sort_order: number;
  created_at?: string;
  updated_at?: string;
}

export interface HomepageSectionProduct {
  id: string;
  section_id: string;
  product_id: string;
  product?: Product;
  sort_order: number;
  active: boolean;
  created_at?: string;
}

export interface HomepageNavigationCard {
  id: string;
  section_id: string;
  title: string;
  link?: string | null;
  image_url?: string | null;
  active: boolean;
  sort_order: number;
  created_at?: string;
  updated_at?: string;
}

export interface HomepageBanner {
  id: string;
  section_id: string;
  section_heading?: string | null;
  eyebrow?: string | null;
  title: string;
  subtitle?: string | null;
  price_text?: string | null;
  button_text?: string | null;
  button_link?: string | null;
  second_button_text?: string | null;
  second_button_link?: string | null;
  background_video_url?: string | null;
  background_image_url?: string | null;
  mobile_background_image_url?: string | null;
  text_color: 'light' | 'dark' | string;
  text_alignment: 'left' | 'center' | 'right' | string;
  active: boolean;
  sort_order: number;
  created_at?: string;
  updated_at?: string;
}

export interface HomepagePromoCard {
  id: string | number;
  section_key: string;
  label?: string | null;
  title: string;
  subtitle?: string | null;
  button_text?: string | null;
  button_link?: string | null;
  category_slug?: string | null;
  background_image_url?: string | null;
  mobile_background_image_url?: string | null;
  background_video_url?: string | null;
  text_color: 'light' | 'dark' | string;
  active: boolean;
  sort_order: number;
  created_at?: string;
  updated_at?: string;
}
