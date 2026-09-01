export type ContactPurpose = 'offer' | 'return' | 'support' | 'general';

export interface ContactMessagePayload {
  purpose: ContactPurpose;
  name: string;
  phone: string;
  email?: string;
  subject?: string;
  message: string;
  source_path?: string;
}

export interface ContactMessageResponse {
  message: string;
  id: number;
  status: string;
}
