const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;

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

export async function apiGet<T>(path: string): Promise<T> {
  const base = String(apiBaseUrl).replace(/\/$/, '');
  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  const response = await fetch(`${base}${cleanPath}`, {
    headers: { Accept: 'application/json' },
  });

  if (!response.ok) {
    throw new Error(`API request failed: ${response.status}`);
  }

  return response.json() as Promise<T>;
}

export async function apiPost<T>(path: string, payload: unknown): Promise<T> {
  const base = String(apiBaseUrl).replace(/\/$/, '');
  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  const response = await fetch(`${base}${cleanPath}`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(`API request failed: ${response.status}`);
    (error as Error & { response?: unknown }).response = data;
    throw error;
  }

  return data as T;
}
