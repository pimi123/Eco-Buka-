import type { Product } from './product';

export interface CartItem {
  key: string;
  product: Product;
  quantity: number;
  selected_options?: Record<string, string>;
}

export interface CheckoutPayload {
  customer_name: string;
  customer_phone: string;
  customer_email?: string;
  country: string;
  municipality: string;
  delivery_address: string;
  city: string;
  postal_code?: string;
  delivery_details?: string;
  customer_note?: string;
  policy_accepted: boolean;
  items: Array<{
    product_id: number;
    quantity: number;
    selected_options?: Record<string, string>;
  }>;
}

export interface OrderResponse {
  message: string;
  id: number;
  order_number: string;
  status: string;
  total: string | number;
  currency: string;
}

export interface NestPayInitiationResponse {
  gatewayUrl?: string;
  gateway_url?: string;
  parameters: Record<string, string | number | boolean | null | undefined>;
}

export interface PublicOrderItem {
  name: string;
  quantity: number;
  unit_price: string | number;
  line_total: string | number;
  image_url?: string | null;
  category?: string | null;
  selected_options?: Record<string, string> | null;
}

export interface PublicOrder {
  order_number: string;
  status: string;
  status_label: string;
  payment_status?: string | null;
  payment_status_label: string;
  total: string | number;
  currency: string;
  created_at?: string | null;
  customer_name?: string | null;
  delivery_area?: string | null;
  items: PublicOrderItem[];
}
