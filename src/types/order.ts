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
  delivery_address: string;
  city: string;
  delivery_details?: string;
  customer_note?: string;
  items: Array<{
    product_id: number;
    quantity: number;
    selected_options?: Record<string, string>;
  }>;
}

export interface OrderResponse {
  message: string;
  order_number: string;
  status: string;
  total: string | number;
  currency: string;
}
