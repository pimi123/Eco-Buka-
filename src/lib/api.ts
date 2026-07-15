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
