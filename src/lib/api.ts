const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;
export const AUTH_TOKEN_KEY = 'eco-buka-auth-token';

export const hasLaravelApiConfig = Boolean(apiBaseUrl);

if (hasLaravelApiConfig && typeof document !== 'undefined') {
  const origin = new URL(String(apiBaseUrl)).origin;
  if (!document.head.querySelector(`link[rel="preconnect"][href="${origin}"]`)) {
    const link = document.createElement('link');
    link.rel = 'preconnect';
    link.href = origin;
    link.crossOrigin = 'anonymous';
    document.head.appendChild(link);
  }
}

function authHeaders(): Record<string, string> {
  if (typeof localStorage === 'undefined') return {};

  const token = localStorage.getItem(AUTH_TOKEN_KEY);
  return token ? { Authorization: `Bearer ${token}` } : {};
}

async function parseResponse<T>(response: Response): Promise<T> {
  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(`API request failed: ${response.status}`);
    (error as Error & { response?: unknown; status?: number }).response = data;
    (error as Error & { response?: unknown; status?: number }).status = response.status;
    throw error;
  }

  return data as T;
}

export async function apiGet<T>(path: string): Promise<T> {
  const base = String(apiBaseUrl).replace(/\/$/, '');
  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  const response = await fetch(`${base}${cleanPath}`, {
    headers: {
      Accept: 'application/json',
      ...authHeaders(),
    } satisfies HeadersInit,
  });

  return parseResponse<T>(response);
}

export async function apiPost<T>(path: string, payload: unknown): Promise<T> {
  const base = String(apiBaseUrl).replace(/\/$/, '');
  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  const response = await fetch(`${base}${cleanPath}`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...authHeaders(),
    } satisfies HeadersInit,
    body: JSON.stringify(payload),
  });

  return parseResponse<T>(response);
}
